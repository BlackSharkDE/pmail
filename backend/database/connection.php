<?php
/**
 * Verbindungseinstellungen für die Datenbank
 */

require __DIR__ . "/php_pdointerface/src.php";

//Neues PDOInterface
$dbCon = new PDOInterface(true,"","");

//MySQL-Verbindung einstellen
$dbCon->setMySQLConnection("","pmail");