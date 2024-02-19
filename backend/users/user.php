<?php

//================================================================================================================
//-- Klasse für einen User (Datenhaltung) --

class PMailUser {

    //Generelles
    private string $userID = "";
    private string $plainApiKey = "";
    private string $hashedApiKey = "";
    private int $lastAccessTime;

    //SMTP-Verbindungsdaten (unverschlüsselt)
    private string $smtpUser;
    private string $smtpPassword;
    private string $smtpServer;
    private int $smtpPort;

    //IMAP-Verbindungsdaten (unverschlüsselt)
    private string $imapUser;
    private string $imapPassword;
    private string $imapServer;
    private int $imapPort;

    //-- Konstruktor --
    public function __construct() {}

    //-- Setter --

    public function setUserID(string $id) {
        //Verhindere, dass die ID mehr als einmal gesetzt wird UND das ID falsche Länge hat
        if(strlen($this->userID) === 0 && strlen($id) === 10) {
            $this->userID = $id;
        }
    }

    public function setPlainApiKey(string $key) {
        $this->plainApiKey = $key;
    }

    public function setHashedApiKey(string $key) {
        $this->hashedApiKey = $key;
    }

    public function setLastAccessTime(int $time) {
        $this->lastAccessTime = $time;
    }
    
    public function setSmtpAttributes(string $user, string $password, string $server, int $port) {
        $this->smtpUser     = $user;
        $this->smtpPassword = $password;
        $this->smtpServer   = $server;
        $this->smtpPort     = $port;
    }

    public function setImapAttributes(string $user, string $password, string $server, int $port) {
        $this->imapUser     = $user;
        $this->imapPassword = $password;
        $this->imapServer   = $server;
        $this->imapPort     = $port;
    }

    //-- Getter --

    public function getUserID() {
        return $this->userID;
    }

    public function getPlainApiKey() {
        return $this->plainApiKey;
    }

    public function getHashedApiKey() {
        return $this->hashedApiKey;
    }

    public function getLastAccessTime() {
        return $this->lastAccessTime;
    }

    /**
     * Alternativer Getter für das "lastAccessTime"-Attribut
     * @return string Das "lastAccessTime"-Attribut als formatierter String
     */
    public function getLastAccessTimeFormatted() {
        return date("Y-m-d H:i:s",$this->getLastAccessTime());
    }

    public function getSmtpAttributes() {
        return [$this->smtpUser,$this->smtpPassword,$this->smtpServer,$this->smtpPort];
    }

    public function getImapAttributes() {
        return [$this->imapUser,$this->imapPassword,$this->imapServer,$this->imapPort];
    }

    //-- Sonstiges --

    /**
     * Prüft, ob Objekt ein valider User ist
     * @return bool True wenn ja / False, wenn nein
     */
    public function isValidUser() {
        return (
            strlen($this->getUserID()) === 10
            &&
            strlen($this->getHashedApiKey()) > 0
            &&
            isset($this->smtpUser) && isset($this->smtpPassword) && isset($this->smtpServer) && isset($this->smtpPort)
            &&
            isset($this->imapUser) && isset($this->imapPassword) && isset($this->imapServer) && isset($this->imapPort)
        );
    }
}

//================================================================================================================
//-- Klasse zum Steuern aller User --

require_once dirname(__DIR__) . "/database/connection.php";
require_once dirname(__DIR__) . "/ciphering.php";

class PMailUsers {

    /**
     * Prüft, ob die angegebene User-ID in der Datenbank existiert
     * @param string User-ID nach der in der Datenbank gesucht werden soll
     * @return bool  Gibt True zurück, wenn die User-ID existiert / False, wenn sie nicht existiert
     */
    public static function userIDExists(string $userIDToCheck) {

        global $dbCon;

        //Datenbankabfrage
        $result = $dbCon->queryDB("SELECT * FROM `users` WHERE `userID` = ?",[$userIDToCheck]);

        if(count($result) != 0) {
            //User-ID existiert
            return True;
        }

        //User-ID existiert nicht
        return False;
    }

    /**
     * Methode zum Generieren von User-IDs
     * @return string Neue, eindeutige und 10 stellige User-ID
     */
    public static function generateUserID() {

        //Gibt an, dass noch keine User-ID generiert wurde
	    $keepGenerating = True;

        //Neue User-ID
        $newUserID = "";

        while($keepGenerating) {
            $newUserID = generateRandomString(10);
            
            //Checke, ob diese User-ID schon in der Datenbank existiert
            if(PMailUsers::userIDExists($newUserID) === False) {
                $keepGenerating = False;
            }
        }

        return $newUserID;
    }

    /**
     * Extrahiert die User-ID aus einem validen Klartext-API-Schlüssel
     * @param string  Valider Klartext-API-Schlüssel (muss an andere Stelle gecheckt werden)
     * @return string User-ID
     */
    public static function extractUserID(string $plainApiKey) {
        return substr($plainApiKey,0,10);
    }

    /**
     * Fragt den gehashten API-Schlüssel für eine User-ID ab
     * @param string  Die User-ID
     * @return string Gehashter API-Schlüssel
     */
    public static function getHashedApiKeyForUser(string $userID) {

        global $dbCon;

        //Datenbank abfragen
        $hashedApiKey = $dbCon->queryDB("SELECT `hashedApiKey` FROM `users` WHERE `userID` = ?",[$userID]);
        
        //Wenn nur ein Resultat zurückgegeben wird
        if(count($hashedApiKey) === 1) {
            return $hashedApiKey[0]->hashedApiKey;
        }

        //Fehler
        return "";
    }

    /**
     * Updated die letzte Zugriffszeit für einen User in der Datenbank. (void - Funktion)
     * @param string  Die User-ID
     * @param int     Unix-Zeitstempel
     */
    public static function updateLastAccessTime(string $userID, int $time) {
        global $dbCon;
        $dbCon->queryDBNoFetch('UPDATE `users` SET `lastAccessTime` = ? WHERE `userID` = ?',[$userID,$time]);
    }

    /**
     * Gibt die Verbindungsdaten eines Users (verschlüsselt) zurück
     * @param string Die User-ID
     * @return array Array mit zwei möglichen Aufbauten:
     *               - Erfolg: [[SMTP-User, SMTP-Passwort, SMTP-Server, SMTP-Port],[IMAP-User, IMAP-Passwort, IMAP-Server, IMAP-Port]]
     *               - Fehler: []
     */
    public static function getConnectionData(string $userID) {

        global $dbCon;

        //SMTP-Daten abfragen und in Array (assoziativ) konvertieren
        $smtpData = $dbCon->queryDB("SELECT `smtpUser`, `smtpPassword`, `smtpServer`, `smtpPort` FROM `smtpdata` WHERE `userID` = ?",[$userID]);
        if(count($smtpData) === 1) {
            $smtpData = json_decode(json_encode($smtpData[0]),True);
        }

        //IMAP-Daten abfragen und in Array (assoziativ) konvertieren
        $imapData = $dbCon->queryDB("SELECT `imapUser`, `imapPassword`, `imapServer`, `imapPort` FROM `imapdata` WHERE `userID` = ?",[$userID]);
        if(count($imapData) === 1) {
            $imapData = json_decode(json_encode($imapData[0]),True);
        }

        //Wenn sowohl SMTP- als auch IMAP-Daten richtig abgefragt wurden
        if(count($smtpData) === 4 && count($imapData) === 4) {
            //Entferne die Array-Keys
            return [array_values($smtpData),array_values($imapData)];
        }

        return [];
    }

    /**
     * Gibt ein Array mit allen Usern aus
     * @return array Ein Array mit PMailUser-Objekten / leeres Array, wenn keine User vorhanden sind
     */
    public static function getUsers() {

        global $dbCon;

        //Rückgabe
        $pmailUsers = array();

        //User abfragen
        $users = $dbCon->queryDB("SELECT * FROM pmail.users");

        //Jeden User in ein PMailUser-Objekt konvertieren
        foreach($users as $user) {
            $pmailUser = new PMailUser();
            $pmailUser->setUserID($user->userID);
            $pmailUser->setHashedApiKey($user->hashedApiKey);
            $pmailUser->setLastAccessTime(strtotime($user->lastAccessTime));
            array_push($pmailUsers,$pmailUser);
        }

        return $pmailUsers;
    }

    /**
     * Löscht einen User aus der Datenbank
     * @param string Die User-ID
     * @return bool  True bei Erfolg / False bei Fehler
     */
    public static function deleteUser(string $userID) {

        global $dbCon;

        //Wenn die User-ID existiert
        if(PMailUsers::userIDExists($userID)) {

            //Aus der User-Tabelle löschen (wegen der Constraints werden auch die anderen Tabellen angepasst)
            return $dbCon->queryDBNoFetch('DELETE FROM `users` WHERE `userID` = ?',[$userID]);
        }

        return False;
    }
}