<?php
/**
 * Führt das Datenbank-Setup aus
 */

//================================================================================================================
//-- Datenbankverbindung erstellen (extra - nicht für Produktivbetrieb!) --

require __DIR__ . "/php_pdointerface/src.php";
$dbCon = new PDOInterface(true,"","");  //Neues PDOInterface
$dbCon->setMySQLConnection("",""); //MySQL-Verbindung einstellen

//================================================================================================================
//-- Das Setup --

//Überschrift
echo("<h1>Datenbank - Setup</h1>");

//Die "pmail"-Datenbank erstellen
$tS = $dbCon->queryDBNoFetch("
    CREATE SCHEMA `pmail` DEFAULT CHARACTER SET utf8
");
echo("<p>Erstelle `pmail`-Datenbank => " . (($tS) ? 'true' : 'false') . "</p>");

//Die "users"-Tabelle erstellen
$tS = $dbCon->queryDBNoFetch("
    CREATE TABLE `pmail`.`users` (
        `userID`         CHAR(10) NOT NULL UNIQUE, #userID ist 10 Zeichen lang und muss eindeutig sein
        `hashedApiKey`   VARCHAR(255) NOT NULL,    #Empfohlene Länge für PHP-password_hash()-Funktion mit 'PASSWORD_DEFAULT'-Flag
        `lastAccessTime` TIMESTAMP NOT NULL,       #Letzte Zugriffszeit im Format YYYY-MM-DD HH:MM:SS (also beispielsweise: 2013-07-22 12:50:05)
        PRIMARY KEY (`userID`)
    )
");
echo("<p>Erstelle `users`-Tabelle => " . (($tS) ? 'true' : 'false') . "</p>");

//Die "smtpdata"-Tabelle erstellen
$tS = $dbCon->queryDBNoFetch("
    CREATE TABLE `pmail`.`smtpdata` (
        `userID`       CHAR(10) NOT NULL,     #REFERENZ zur `users`-Tabelle
        `smtpUser`     VARCHAR(255) NOT NULL, #SMTP-User (verschlüsselt)
        `smtpPassword` VARCHAR(255) NOT NULL, #SMTP-User-Passwort (verschlüsselt)
        `smtpServer`   VARCHAR(255) NOT NULL, #SMTP-Server-Adresse (verschlüsselt)
        `smtpPort`     VARCHAR(255) NOT NULL, #SMTP-Server-Port (verschlüsselt)
        FOREIGN KEY (`userID`) REFERENCES `pmail`.`users`(`userID`) ON UPDATE CASCADE ON DELETE CASCADE #Bei Updates / Delete auch updaten oder löschen
    )
");
echo("<p>Erstelle `smtpdata`-Tabelle => " . (($tS) ? 'true' : 'false') . "</p>");

//Die "imapdata"-Tabelle erstellen
$tS = $dbCon->queryDBNoFetch("
    CREATE TABLE `pmail`.`imapdata` (
        `userID`       CHAR(10) NOT NULL,     #REFERENZ zur `users`-Tabelle
        `imapUser`     VARCHAR(255) NOT NULL, #IMAP-User (verschlüsselt)
        `imapPassword` VARCHAR(255) NOT NULL, #IMAP-User-Passwort (verschlüsselt)
        `imapServer`   VARCHAR(255) NOT NULL, #IMAP-Server-Adresse (verschlüsselt)
        `imapPort`     VARCHAR(255) NOT NULL, #IMAP-Server-Port (verschlüsselt)
        FOREIGN KEY (`userID`) REFERENCES `pmail`.`users`(`userID`) ON UPDATE CASCADE ON DELETE CASCADE #Bei Updates / Delete auch updaten oder löschen
    )
");
echo("<p>Erstelle `imapdata`-Tabelle => " . (($tS) ? 'true' : 'false') . "</p>");

//Die "footer"-Tabelle erstellen
$tS = $dbCon->queryDBNoFetch("
    CREATE TABLE `pmail`.`footer` (
        `name` VARCHAR(255) NOT NULL UNIQUE, #Name des Footer (muss eindeutig sein)
        `ishtml` BOOL NOT NULL,              #Ob der Footer für HTML-E-Mails geeignet ist (false = Nicht-HTML-E-Mails)
        `content` TEXT NOT NULL,             #Inhalt des Footers
        PRIMARY KEY (`name`)
    )
");
echo("<p>Erstelle `footer`-Tabelle => " . (($tS) ? 'true' : 'false') . "</p>");

//Die "footer"-Tabelle mit vorgefertigten Footern füllen
$tS = $dbCon->queryDBNoFetch("
    INSERT INTO `pmail`.`footer` VALUES
    ('standard_plain',false,'\n\n----------------------------------------------------------------\nSent via PMail'),
    ('standard_html',true,'<br><br><hr style=\"height: 0.5px; background-color: #b4b4b4;\"><center><small>Sent via PMail</small></center>')
");
echo("<p>Fülle `footer`-Tabelle => " . (($tS) ? 'true' : 'false') . "</p>");

//Die "logs"-Tabelle erstellen
$tsS = $dbCon->queryDBNoFetch("
    CREATE TABLE `pmail`.`logs` (
        `timestamp` TIMESTAMP(6) NOT NULL, #Datum des Log-Eintrags (mit 6 Stellen für Millisekunden)
        `statusCode` INT NOT NULL,         #Status-Code der API
        `value` MEDIUMTEXT NOT NULL,       #Rückgabe der API
        `postdata` MEDIUMTEXT NOT NULL,    #Die Daten, die per HTTP-POST an die API geschickt wurden (decoded)
        PRIMARY KEY (`timestamp`)
    )
");
echo("<p>Erstelle `logs`-Tabelle => " . (($tS) ? 'true' : 'false') . "</p>");

echo("<b><p>Wenn alle Tabellen 'true' zeigen, war das Setup erfolgreich. Bei Erfolg diese Setup-Datei löschen!</p></b>");
echo("<b><p>Außerdem ist anzuraten, dieses Verzeichnis nicht über den Webbrowser aufrufbar zu machen!</p></b>");