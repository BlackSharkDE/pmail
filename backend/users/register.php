<?php
/**
 * Alles zum Registrieren eines Users
 */

require_once dirname(__DIR__) . "/database/connection.php";
require_once dirname(__DIR__) . "/ciphering.php";
require_once "user.php";

/**
 * Registriert einen neuen PMail-User (es werden keine weiteren Checks ausgeführt, das muss an anderer Stelle passieren!)
 * @param PMailUser Ein PMailUser mit ausgefüllten SMTP- und IMAP-Attributen
 * @return string   Klartext-API-Schlüssel für den User / leerer String bei Fehler
 */
function registerNewUser(PMailUser $newUser) {

    global $dbCon;

    //-- Generelles --
    $newUser->setUserID(PMailUsers::generateUserID());                    //Neue ID zuweisen
    $newUser->setPlainApiKey(generatePlainApiKey($newUser->getUserID())); //Neuen Klartext-API-Schlüssel generieren
    $newUser->setHashedApiKey(hashApiKey($newUser->getPlainApiKey()));    //Klartext-API-Schlüssel hashen
    $newUser->setLastAccessTime(time());                                  //Letzte Zugriffszeit setzen (jetzt)

    //SMTP- und IMAP-Attribute zwischenspeichern (unverschlüsselt)
    $smtpAttributes = $newUser->getSmtpAttributes();
    $imapAttributes = $newUser->getImapAttributes();

    //SMTP-Attribute verschlüsseln
    $smtpAttributes = encryptArrayValues($smtpAttributes,$newUser->getPlainApiKey());

    //IMAP-Attribute verschlüsseln
    $imapAttributes = encryptArrayValues($imapAttributes,$newUser->getPlainApiKey());

    //Neuen User in Datenbank speichern (verschlüsselt)
    $result = $dbCon->queryDBNoFetch("INSERT INTO `pmail`.`users` VALUES(?,?,CURRENT_TIMESTAMP)",[$newUser->getUserID(),$newUser->getHashedApiKey()]);
    $result = $dbCon->queryDBNoFetch("INSERT INTO `pmail`.`smtpdata` VALUES(?,?,?,?,?)",array_merge([$newUser->getUserID()],$smtpAttributes));
    $result = $dbCon->queryDBNoFetch("INSERT INTO `pmail`.`imapdata` VALUES(?,?,?,?,?)",array_merge([$newUser->getUserID()],$imapAttributes));

    //Wenn Datenbankverbindung Fehler enthält
    if(!$result) {
        return "";
    }

    //Wenn User erfolgreich registriert ist
    return $newUser->getPlainApiKey();
}