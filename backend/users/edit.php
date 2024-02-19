<?php
/**
 * Alles zum Editieren eines Users
 */

require_once dirname(__DIR__) . "/database/connection.php";
require_once dirname(__DIR__) . "/ciphering.php";
require_once "user.php";

/**
 * Editiert einen bestehenden PMail-User (es werden keine weiteren Checks ausgeführt, das muss an anderer Stelle passieren!)
 * @param PMailUser Ein PMailUser mit ausgefüllten "userID"-Attribut
 * @param array     Ein Array mit den neuen/bearbeiteten und bestehenden Werten (8)
 *                  -> Reihenfolge: "smtpUser", "smtpPassword", "smtpServer", "smtpPort", "imapUser", "imapPassword", "imapServer", "imapPort"
 *                  -> Die Werte "userID", "plainApiKey", "hashedApiKey", "lastAccessTime" können nicht editiert werden!
 * @param string    Der API-Schlüssel des bestehenden Users
 * @return bool     Wenn alles in Ordnung True / Bei Fehler False
 */
function editExistingUser(PMailUser $existingUser, array $editedValues, string $apiKey) {

    global $dbCon;

    //Die bearbeiteten Werte aufteilen
    $smtpAttributes = array_slice($editedValues,0,4);
    $imapAttributes = array_slice($editedValues,4,7);

    //Neue SMTP-Attribute verschlüsseln
    $smtpAttributes = encryptArrayValues($smtpAttributes,$apiKey);

    //Neue IMAP-Attribute verschlüsseln
    $imapAttributes = encryptArrayValues($imapAttributes,$apiKey);

    //Neue Werte in Datenbank speichern (verschlüsselt)
    $result = $dbCon->queryDBNoFetch(
        "UPDATE `pmail`.`smtpdata` SET `smtpUser`=?, `smtpPassword`=?, `smtpServer`=?, `smtpPort`=? WHERE `userID`=?",
        array_merge($smtpAttributes,[$existingUser->getUserID()])
    );
    $result = $dbCon->queryDBNoFetch(
        "UPDATE `pmail`.`imapdata` SET `imapUser`=?, `imapPassword`=?, `imapServer`=?, `imapPort`=? WHERE `userID`=?",
        array_merge($imapAttributes,[$existingUser->getUserID()])
    );

    return $result;
}