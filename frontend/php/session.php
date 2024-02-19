<?php
/**
 * Verwaltung der User-Session
 */

require_once "options.php";

//Benötige ein paar Dinge aus dem Backend für die Methode "logAction"
require_once BACKEND_DIR . "database/connection.php";
require_once BACKEND_DIR . "modes/response.php";

//================================================================================================================
//-- Klasse zur Verwaltung der Session --

class SessionManager {

    /**
     * Setzt die Session-Variablen. (void - Funktion)
     */
    private function setSession(string $username) {
        $_SESSION["loggedInUser"] = $username;
    }

    /**
     * Prüft, ob in der Session bereits ein User eingeloggt ist
     * @return bool True, wenn ja / False, wenn nein
     */
    public function isLoggedIn() {
        return isset($_SESSION["loggedInUser"]);
    }

    /**
     * Versucht einen User einzuloggen
     * @return bool True, wenn erfolreich / False, wenn nicht
     */
    public function loginUser(string $username, string $password) {

        //Prüfe, ob valider Admin-User
        if(
            in_array($username,array_keys(ADMIN_USERS))
            && strcmp($password,ADMIN_USERS[$username]) === 0
        ) {
            SessionManager::setSession($username);
            return True;
        }

        return False;
    }

    /**
     * Loggt einen User aus (prüft nicht, ob User bereits angemeldet, da dies bereits anderswo passiert). (void - Funktion)
     */
    public function logoutUser() {
        
        //Session-Global leeren
        session_unset();

        //Löschen aller in Verbindung mit der aktuellen Session stehenden Daten
        session_destroy();
    }

    /**
     * Gibt den Namen des eingeloggten Users zurück
     * @return string Der Name des eingeloggten Users
     */
    public function getLoggedInUsername() {
        return $_SESSION["loggedInUser"];
    }

    /**
     * Schreibt eine Aktion des Frontends in das Log in der Datenbank. (void - Funktion)
     */
    public function logAction(string $logMessage) {
        global $dbCon;

        $dbCon->queryDBNoFetch("
        INSERT INTO `pmail`.`logs` VALUES (?,?,?,?)
        ",array(
            PMailResponse::createCurrentTimestamp(),
            0,           //Immer der selbe, da dieser das "Frontend" als Quelle angibt
            $logMessage, //Die Log-Nachricht
            ""           //Wird nicht benötigt
        ));
    }
}

//================================================================================================================
//-- Was immer passieren muss --

//Konstante, die verhindert, dass Includes direkt angesteuert werden können.
//=> Hier definiert, da diese Datei überall (außer bei Includes) inkludiert werden muss und die Konstante daher überall wo notwendig existiert.
define('PMAIL_SESSION', True);

//Session anfangen/fortsetzen
session_start();

//Automatische Umleitung, wenn kein User eingeloggt ist UND aktuell nicht die "index.php" angefordert wurde
if(!SessionManager::isLoggedIn() && strpos($_SERVER['REQUEST_URI'],"index.php") === false) {
    header("Location: " . BASE_URL . "index.php");
}