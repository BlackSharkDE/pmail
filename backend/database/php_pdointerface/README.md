# php_pdointerface

Ein PHP-Modul für Datenbankverbindungen. Stellt abstrakten Zugriff zu **PDO** und Prepared Statements.

## Beispiel

```php
//Einbinden
require 'php_pdointerface/src.php';

//PDOInterface-Objekt erstellen
$dbCon = new PDOInterface(true,"root","123456");

//Verbindung definieren (hier für MySQL)
$dbCon->setMySQLConnection("localhost","testdata");

//Mit der Datenbank arbeiten
$dbCon->queryDB("SELECT * FROM `traffic`");
```