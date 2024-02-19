<?php

/**
 * Klasse für abstrakten Zugriff auf PDO
 */
class PDOInterface {
    
    //================================================================================================================================================
    //-- Attribute --

    //Für PDO-Konstruktor
    private string $dsn;      //bzw. Data Source Name
    private string $username; //Benutzer für Datenbank (OPTIONAL für manche PDO-Driver)
    private string $password; //Passwort für Datenbank-User (OPTIONAL für manche PDO-Driver)
    private array  $options;  //Optionen für Driver (OPTIONAL)

    //Direkt für diese Klasse
    private bool $enableErrorOutput; //Ob Fehler ausgegeben werden sollen

    //================================================================================================================================================
    //-- Konstruktor --
    
    /**
     * -- Konstruktor --
     * @param bool   Siehe "Direkt für diese Klasse"
     * @param string Siehe "Für PDO-Konstruktor"
     * @param string Siehe "Für PDO-Konstruktor"
     * @param array  Siehe "Für PDO-Konstruktor"
     */
    public function __construct(bool $enableErrorOutput, string $username = "", string $password = "", array $options = array()) {
        //--Parameter speichern --

        //Für diese Klasse
        $this->enableErrorOutput = $enableErrorOutput;

        //Für PDO-Konstruktor
        $this->username = $username;
        $this->password = $password;
        $this->options  = $options;
    }
    
    //================================================================================================================================================
    //-- Verbindungseinstellungen --

    /**
     * Setzt das DSN für MySQL-Verbindungen (void - Funktion)
     * @param string IP/DNS des MySQL-Host
     * @param string Datenbank, mit der kommuniziert werden soll
     * @param int    MySQL-Port auf dem Host (OPTIONAL)
     * @param string Zeichensatz (OPTIONAL)
     */
    public function setMySQLConnection(string $host, string $dbname, int $port = 3306, string $charset = "utf8") {
        $this->dsn = 'mysql:host=' . $host . ';dbname=' . $dbname . ';port=' . $port . ';charset=' . $charset;
    }

    /**
     * Setzt das DSN für PostgreSQL-Verbindungen (void - Funktion)
     * @param string IP/DNS des PostgreSQL-Host
     * @param string Datenbank, mit der kommuniziert werden soll
     * @param int    PostgreSQL-Port auf dem Host (OPTIONAL)
     */
    public function setPostgreSQLConnection(string $host, string $dbname, int $port = 5432) {
        $this->dsn = 'pgsql:host=' . $host . ';dbname=' . $dbname . ';port=' . $port;
    }

    /**
     * Setzt das DSN für SQLite-Verbindungen (void - Funktion)
     * @param string Pfad der Datenbankdatei
     */
    public function setSQLiteConnection(string $databasePath) {
        $this->dsn = 'sqlite:' . $databasePath;
    }

    //================================================================================================================================================
    //-- Interaktion mit der Datenbank --

    /**
     * Verbindung zur Datenbank.
     * --> Allgemeine Methode um mit der Datenbank zu kommunizieren
     * --> FÜR QUERIES MIT RÜCKGABEWERTEN
     * @param string Ein SQL-Statement.
     *               --> Wenn eines mit User-Eingaben, muss dieses mit Fragezeichenparametern befüllt werden
     *               --> Dieses Statement wird in der Funktion immer "prepared"
     * @param array  Ein Array, welches die Parameter für die Fragezeichen (sofern benötigt) enthält
     *               --> OPTIONAL, da nicht immer User-Eingaben benutzt werden müssen
     *               --> Einträge im Array müssen in richtiger Reihenfolge sein
     * @return array Rückgabe des Datenbankservers
     *               --> Bei Erfolg: Ein Array bestehend aus Objekten, dessen Eigenschaften einfach ausgelesen werden können
     *               --> Bei Misslingen: Leeres Array
     */
    public function queryDB(string $sqlStatement, array $parameterArray = array()) {
        try {
            //PDO-Instanz bzw. die Verbindung mit Standard-Attributen für die Verbindung
            $connection = new PDO($this->dsn, $this->username, $this->password, $this->options);
            
            //Benutze Objekte für die Anfragen bzw. als Antworten vom Server
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            
            //Setze PDO Error-Modus auf Exception => Es werden Exceptions geworfen
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            //Benutze Prepared Statements --> Statements werden fertig übergeben bzw. der String ist das
            $statement = $connection->prepare($sqlStatement); //Bereitet (prepare) das SQL-Statement vor
            $statement->execute($parameterArray); //Führe das prepared-Statement aus
            $result = $statement->fetchAll(); //Fange alle Resultate ab
            
            //Gebe das Array, welches aus den Rückgabeobjekten der Datenbank besteht, zurück
            return $result;
        }
        //Fange auftretende Fehler auf
        catch(PDOException $e){
            if($this->enableErrorOutput) {
                echo "Kommunikation mit Datenbank beinhaltet Fehler: " . $e->getMessage();
            }
        }

        //Leeres Array, da es Probleme gab
        return array();
    }

    /**
     * Gleiche Funktion wie "queryDB", nur werden keine Resultate abgefangen, sondern nur der Erfolg der Query.
     * --> Für "INSERT", "UPDATE" und "DELETE" Statements gedacht.
     * @return bool True, wenn alles Ok ist / False, bei einem Fehler
     */
    public function queryDBNoFetch(string $sqlStatement, array $parameterArray = array()) {
        try {
            //PDO-Instanz bzw. die Verbindung mit Standard-Attributen für die Verbindung
            $connection = new PDO($this->dsn, $this->username, $this->password, $this->options);
            
            //Benutze Objekte für die Anfragen bzw. als Antworten vom Server
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            
            //Setze PDO Error-Modus auf Exception => Es werden Exceptions geworfen
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            //Benutze Prepared Statements --> Statements werden fertig übergeben bzw. der String ist das
            $statement  = $connection->prepare($sqlStatement); //Bereitet (prepare) das SQL-Statement vor
            $successful = $statement->execute($parameterArray); //Führe das prepared-Statement aus
            return $successful; //Gibt auskunft, ob Operation gelungen ist
        }
        //Fange auftretende Fehler auf
        catch(PDOException $e){
            if($this->enableErrorOutput) {
                echo "Kommunikation mit Datenbank beinhaltet Fehler: " . $e->getMessage();
            }
        }

        //False, da es Probleme gab
        return false;
    }
}