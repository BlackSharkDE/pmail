<?php
/**
 * Alles zum Authentifizieren eines Users
 */

require_once dirname(__DIR__) . "/database/connection.php";
require_once dirname(__DIR__) . "/ciphering.php";
require_once "user.php";

//================================================================================================================

/**
 * Authentifiziert PMail-User
 * @param string     Der Klartext-API-Schlüssel, der beim API-Aufruf mit übergeben wurde
 * @return PMailUser Authentifizierter API-User mit unverschlüsselten SMTP- und IMAP-Verbindungsdaten / leerer User bei Fehlschlag
 */
function authenticateUser(string $givenPlainApiKey) {

    //Wenn Klartext-API-Schlüssel valdies Format hat
    if(isValidPlainApiKey($givenPlainApiKey)) {

        //User-ID extrahieren
        $extractedUserID = PMailUsers::extractUserID($givenPlainApiKey);

        //Wenn die User-ID existiert
        if(PMailUsers::userIDExists($extractedUserID)) {

            //Neues User-Objekt erstellen
            $authenticatedPMailUser = new PMailUser();
            $authenticatedPMailUser->setUserID($extractedUserID);       //User-ID zuweisen
            $authenticatedPMailUser->setPlainApiKey($givenPlainApiKey); //Klartext-API-Schlüssel zuweisen
            $authenticatedPMailUser->setLastAccessTime(time());         //Letzte Zugriffszeit zuweisen

            //Letzte Zugriffszeit für den User in der Datenbank aktualisieren
            PMailUsers::updateLastAccessTime($authenticatedPMailUser->getUserID(),$authenticatedPMailUser->getLastAccessTime());

            //Gehashten API-Schlüssel aus Datenbank abfragen und im User speichern
            $authenticatedPMailUser->setHashedApiKey(PMailUsers::getHashedApiKeyForUser($authenticatedPMailUser->getUserID()));

            //Wenn der Klartext-API-Schlüssel für den User richtig ist
            if(verifyApiKey($authenticatedPMailUser->getPlainApiKey(),$authenticatedPMailUser->getHashedApiKey())) {

                //Die Verbindungsdaten aus der Datenbank abfragen (verschlüsselt)
                $encryptedConnectionData = PMailUsers::getConnectionData($authenticatedPMailUser->getUserID());

                //Die Verbindungsdaten mittels Klartext-API-Schlüssel entschlüsseln und dem User zuweisen
                $authenticatedPMailUser->setSmtpAttributes(
                    decryptString($encryptedConnectionData[0][0],$authenticatedPMailUser->getPlainApiKey()),
                    decryptString($encryptedConnectionData[0][1],$authenticatedPMailUser->getPlainApiKey()),
                    decryptString($encryptedConnectionData[0][2],$authenticatedPMailUser->getPlainApiKey()),
                    decryptString($encryptedConnectionData[0][3],$authenticatedPMailUser->getPlainApiKey())
                );
                $authenticatedPMailUser->setImapAttributes(
                    decryptString($encryptedConnectionData[1][0],$authenticatedPMailUser->getPlainApiKey()),
                    decryptString($encryptedConnectionData[1][1],$authenticatedPMailUser->getPlainApiKey()),
                    decryptString($encryptedConnectionData[1][2],$authenticatedPMailUser->getPlainApiKey()),
                    decryptString($encryptedConnectionData[1][3],$authenticatedPMailUser->getPlainApiKey())
                );

                //Den Klartext-API-Schlüssel aus dem User-Objekt entfernen (aus Sicherheitsgründen)
                $authenticatedPMailUser->setPlainApiKey("");

                //Den User zurückgeben
                return $authenticatedPMailUser;
            }
        }
    }

    return new PMailUser();
}