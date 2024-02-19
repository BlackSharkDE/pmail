<?php
/**
 * Stellt einen Auftrag dar, der innerhalb des readmail-Modus bearbeitet wird
 */

//================================================================================================================
//-- Verfügbare Module (enthalten Methods) --

require "methods/deleting.php";
require "methods/message.php";
require "methods/messages.php";
require "methods/searching.php";

//================================================================================================================
//-- Klasse --

class ReadingOrder {

    /**
     * Array, welches die verfügbaren Methods enthält (werden durch Module automatisch hinzugefügt).
     * 
     * Assoziatives Array mit folgendem Format:
     * [
     *   "Methoden-Funktionsname" => [OPTIONEN]
     *   "a_methodname" => [0,1],
     *   "new_method"   => [0,0],
     *   "other_method" => [1,0]
     * ]
     * 
     * Options-Array für Method:
     * [
     *   Write-Access,       #0 = Read-Only-Verbindung ; 1 = Write-Verbindung wird benötigt (Hinweis: Die Werte sind so gewählt, damit es ein Opt-In ist, Write-Verbindungen zu verlangen)
     *   Check-messageNumber #0 = kein messageNumber-Check ; 1 = messageNumber-Check ausführen (Gilt eigentlich nur für Methods mit einem Int als zweitem Funktionsparameter)
     * ]
     */
    private static array $availableMethods = [];

    //-- Objekt-Internes --
    private PMailUser $user;            //Ein PMail-User mit entschlüsselten IMAP-Verbindungsdaten
    private IMAP\Connection $conection; //Rückgabe von "openImapPath" bzw. die IMAP-Verbindung
    private int $maxMessageNumber;      //Maximale E-Mail-Nummer (bei z.B. 12 E-Mails im Imap-Path ist diese Zahl 12)

    //-- Request-Method-Abteil --
    private string $methodName; //Method, die ausgeführt werden soll
    private $methodParameter;   //Parameter für die Method (entweder Int ODER String)
    private string $folderPath; //Pfad innerhalb des Postfachs

    //-- Request-Connection-Abteil (OPTIONAL) --
    private bool $securePassword = true;  //Entscheidet, ob "/secure" für Verbindung genutzt wird (keine Klartext-Passwörter)
    private string $encryption   = "ssl"; //Entscheidet, ob "/ssl", "/tls" oder "/notls" für Verbindung genutzt wird (Verbindungsverschlüsselung)

    //-- Verbindungsaufbau --

    /**
     * Baut einen Connection-String zu einem IMAP-Server.
     * @param bool    Ob eine Read-Only-Verbindung genutzt wird (keine Schreibaktionen im Postfach)
     * @return string Ein Connection-String, z.B.: {imap.iwas.de:993/ssl/readonly}
     */
    private function getImapConnectionString(bool $readOnlyConnection) {

        //String zusammensetzen
        $connectionString = '{' . $this->user->getImapAttributes()[2] . ':' . $this->user->getImapAttributes()[3] . '/' . $this->encryption;

        //Wenn eine Read-Only-Verbindung
        if($readOnlyConnection) {
            $connectionString .= '/readonly';
        }

        //Wenn "securePasswort" genutzt wird
        if($this->securePassword) {
            $connectionString .= '/secure';
        }

        //Connection-String abschließen
        $connectionString .= '}';
        
        return $connectionString;
    }

    /**
     * Öffnet eine IMAP-Verbindung (wird direkt in einen Ordner im IMAP-Account gelegt).
     * @param string           Connection-String (siehe "getImapConnectionString")
     * @return IMAP\Connection IMAP-Stream
     */
    private function openImapPath(string $imapServerConnectionString) {

        //Pfad für IMAP-Stream
        $path = $imapServerConnectionString;

        //Sofern ein Unterordner angegeben wurde
        if(strlen($this->folderPath) > 0) {
            $path .= $this->folderPath; //Es entsteht der String {IMAP-VERBINDUNG}ORDNER_IM_IMAP_ACCOUNT
        }

        //Liefert False bei Fehlschlag (funktioniert aktuell nicht wirklich, da eher ein Warning von PHP ausgegeben wird, statt einfach False)
        return imap_open($path,$this->user->getImapAttributes()[0],$this->user->getImapAttributes()[1]);
    }

    //-- Generelles --

    /**
     * -- Konstruktor --
     * @param PMailUser Ein authentifizierter User mit entschlüsselten IMAP-Verbindungsdaten
     * @param array     "method"-Abschnitt des Request
     * @param array     "connection"-Abschnitt des Request
     */
    public function __construct(PMailUser $user,array $methodPart,array $connectionPart) {
        $this->user = $user;

        //"method"-Part
        $this->methodName      = $methodPart["name"];
        $this->methodParameter = $methodPart["parameter"] ?? NULL;
        $this->folderPath      = strval($methodPart["folderpath"] ?? "");

        //"connection"-Part (sofern angegeben, ansonsten mit Standardwerten fortfahren)
        $this->setSecurePassword($connectionPart["secure_password"] ?? $this->securePassword);
        $this->setEncryption($connectionPart["encryption"] ?? $this->encryption);

        //Herausfinden, ob Verbindung Read-Only oder Write ist => Anhand Funktion
        $needsWriteAccess = boolval(self::$availableMethods[$this->methodName][0]); //Werte aus, ob Verbindung Write-Access braucht
        $readOnlyAccess   = !($needsWriteAccess); //Invers von Voraussetzung von Write-Access ist gleichbedeutent mit Read-Only

        //IMAP-Connection-String
        $imapConnectionString = $this->getImapConnectionString($readOnlyAccess);
        
        //Verbindung zu/in einen IMAP-Account aufbauen
        $this->connection = $this->openImapPath($imapConnectionString);
        
        //Sollte keine Verbindung hergestellt worden sein, beenden bzw. Exception werfen
        if($this->connection === false) {
            throw new Exception("Konnte keine Verbindung zum IMAP-Server herstellen!");
        }

        //"maxMessageNumber" setzen
        $this->setMaxMessageNumber();
    }

    /**
     * Schließt den IMAP-Stream. (void - Funktion)
     */
    public function closeConnection() {
        imap_close($this->connection);
    }

    //-- Setter --

    /**
     * Setzt das Attribut "securePassword"
     */
    private function setSecurePassword(bool $securePassword) {
        if($securePassword) {
            $this->securePassword = true;
        } else {
            $this->securePassword = false;
        }
    }

    /**
     * Setzt das Attribut "encryption"
     * @param string Verschlüsselung, Auswahl siehe Attribut 
     */
    private function setEncryption(string $encryption) {

        //Kleinschreibung (fehlersicher)
        $encryption = strtolower($encryption);

        switch($encryption) {
            case "ssl":
                $this->encryption = "ssl";
                break;
            case "tls":
                $this->encryption = "tls";
                break;
            case "notls":
                $this->encryption = "notls";
                break;
            
            //Wenn ungültige Angabe, dann SSL
            default:
                $this->encryption = "ssl";
                break;
        }
    }

    /**
     * Setzt das Attribut "maxMessageNumber"
     * 
     * Sollte es zu Problemen kommen, wird das Attribut auf -1 gesetzt
     */
    private function setMaxMessageNumber() {

        //Die aktuellste Nachrichtnummer im IMAP-Stream (egal ob diese schon gelesen wurde oder nicht).
        $messageHeaders = imap_headers($this->connection);

        if($messageHeaders === false) {
            //Sollte ein Fehler passiert sein
            $this->maxMessageNumber = -1;
        } else {
            //Wenn alles Ok ist
            $this->maxMessageNumber = sizeof($messageHeaders);
        }
    }

    //-- Getter --

    public function getMaxMessageNumber() { return $this->maxMessageNumber; }

    //-- Validierung --

    /**
     * Prüft, ob der übergebene Method-Name valide ist
     * @return bool True wenn ja, False wenn nein
     */
    public static function isValidMethodName(string $requestedMethod) {
        return array_key_exists($requestedMethod,self::$availableMethods);
    }

    /**
     * Prüft, ob übergebene Message-Number valide ist
     * @return bool True wenn ja, False wenn nein
     */
    public function isValidMessageNumber(int $givenMessageNumber) {

        //Zahl muss zwischen 1 und maximaler Nummer liegen
        if($givenMessageNumber > 0 && $givenMessageNumber <= $this->maxMessageNumber) {
            return true;
        }

        return false;
    }

    //-- Method hinzufügen, finden, ausführen --

    /**
     * Fügt zum Attribut "availableMethods" weitere Method hinzu
     * @param array Ein Array mit dem gleichen Format, wie "availableMethods" selbst (siehe Definition)
     */
    public static function addToAvailableMethods(array $newMethodsToAdd) {
        self::$availableMethods = array_merge(self::$availableMethods,$newMethodsToAdd);
    }

    /**
     * Führt die gültige Method aus, die vom User angegeben wurde
     * @return MISC Siehe jeweilige Method
     */
    public function runMethod() {

        //Finde heraus, welcher Datentyp für den Parameter der Method genutzt werden muss
        $reflectionFunction = new ReflectionFunction($this->methodName);
        $reflecionParameter = $reflectionFunction->getParameters();
        if(count($reflecionParameter) > 1) {
            $reflecionParameter = $reflecionParameter[1];
            $reflecionParameter = $reflecionParameter->getType()->getName();
        } else {
            $reflecionParameter = "";
        }

        //Rückgabe der ausgewählten Method und damit dieser Methode
        $methodReturn = NULL;

        //Je nachdem, was der Parameter für die Method ist
        if(strcmp($reflecionParameter,"int") === 0) {
            //-- Method hat Int als Parameter --

            if(is_int($this->methodParameter)) {

                //Prüfe, ob übergebener Parameter in der Method als MessageNumber interpretiert werden wird und daher vorher überprüft werden soll
                if(boolval(self::$availableMethods[$this->methodName][1])) {
                    if(!$this->isValidMessageNumber($this->methodParameter)) {
                        throw new Exception("Angegebener 'messageNumber' (Parameter) ist ungueltig!");
                    }
                }

                $methodReturn = call_user_func($this->methodName,$this->connection,$this->methodParameter);
            } else if(is_null($this->methodParameter)) {
                //Parameter wurde im Konstruktor auf NULL gesetzt, da er nicht angegeben wurde => Exception werfen
                throw new Exception("Kein Parameter angegeben, obwohl einer benoetigt wird!");
            } else {
                //PROBLEM mit Parameter => Exception werfen
                throw new Exception("Angegebener Parameter ist ungueltig, da er kein Int ist!");
            }

        } else if(strcmp($reflecionParameter,"string") === 0) {
            //-- Method hat String als Parameter --

            if(is_string($this->methodParameter)) {
                $methodReturn = call_user_func($this->methodName,$this->connection,$this->methodParameter);
            } else if(is_null($this->methodParameter)) {
                //Parameter wurde im Konstruktor auf NULL gesetzt, da er nicht angegeben wurde => Exception werfen
                throw new Exception("Kein Parameter angegeben, obwohl einer benoetigt wird!");
            } else {
                //PROBLEM mit Parameter => Exception werfen
                throw new Exception("Angegebener Parameter ist ungueltig, da er kein String ist!");
            }

        } else {
            //-- Method hat keinen Parameter --
            $methodReturn = call_user_func($this->methodName,$this->connection);
        }

        return $methodReturn;
    }
}