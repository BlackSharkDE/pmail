# backend

## Voraussetzungen

* Apache 2.4.43
* PHP mindestens in der Version 7.4.29
* PHP-Erweiterung `imap` muss aktiviert sein
* Datenbankserver (MySQL / MariaDB)

Verwendete PHP-Module:

* **php_pdointerface** --> Git-Submodule
* **PHPMailer** --> Git-Submodule

## Sicherheitskonzept

Ein API-Schlüssel ist immer 50 Zeichen lang, z.B. `woUAQZwEcAVngSLqzYvmb0fPH5FbwY7IEYHcOfezheSFvlfjCB`, und besteht aus 2 Teilen:
1. Die ersten 10 Zeichen `woUAQZwEcA` sind die User-ID. Diese ist API-Intern und identifiziert den User eindeutig.
2. Die restlichen 40 Zeichen sind einfach Zeichen um die Länge aufzufüllen und die Schlüssel einzigartig zu machen.

Der API-Schlüssel ist in seiner Gesamtheit eindeutig und kann nicht wiederhergestellt werden.

### Registrierung

1. User gibt die Verbindungsdaten (SMTP-User, SMTP-Passwort, SMTP-Server, SMTP-Port, IMAP-User, IMAP-Passwort, IMAP-Server, IMAP-Port) im Registrierungsformular an.
2. Es wird die User-ID generiert. Diese wird unverschlüsselt in die Datenbank gespeichert.
3. Der Klartext-API-Schlüssel (wofür die User-ID benötigt wird) wird generiert.
4. Die Verbindungsdaten werden mittels Klartext-API-Schlüssel symmetrisch verschlüsselt und dann in der Datenbank gespeichert.
5. Der Klartext-API-Schlüssel wird mittels einer One-Way-Hash-Methode gehasht und dann in der Datenbank gespeichert.
6. User bekommt Klartext-API-Schlüssel ausgegeben (dieser muss sich den merken).

### Authentifizierung

1. Der User gibt seinen Klartext-API-Schlüssel bei der API-Verbindung an.
2. Die User-ID wird extrahiert, sodass der gehashte API-Schlüssel aus der Datenbank abgefragt wird.
3. Der Klartext-API-Schlüssel aus dem Verbindungsaufbau wird mit dem gehashten API-Schlüssel aus der Datenbank verglichen.
4. Wenn die Schlüssel übereinstimmen, wird der Klartext-API-Schlüssel benutzt, um die Verbindungsdaten zu entschlüsseln.

### Sicherheit

* Der in der Datenbank gespeicherte API-Schlüssel ist an sich unbrauchbar um die Verbindungsdaten zu entschlüsseln.
* Die Verbindungsdaten in der Datenbank sind ohne Klartext-API-Schlüssel unbrauchbar.
* Der Klartext-API-Schlüssel ist an sich auch unbrauchbar, wenn man keinen Zugriff auf die Datenbank hat (Schlüssel enthält selbst keine sensiblen Daten).
* Da jeder User einen eigenen unwiederherstellbaren API-Schlüssel in der Datenbank liegen hat, würde selbst das Knacken eines API-Accounts (durch Abfangen des Gesamtschlüssels beispielsweise) nicht die anderen betreffen.

## Grundlegendes

Da die API mit JSON funktioniert, wird die Anfrage an die API in JSON formuliert, sowie das Ergebnis der API bzw. des Aufrufs als JSON dargestellt.

Um mit der API überhaupt interagieren zu können, werden 2 wesentliche JSON-Felder benötigt: `apikey` und `apimode`.

* `apikey` ist ein String und beinhaltet den API-Schlüssel, den man von der Accountregistrierung bekommen hat. Wenn dieser nicht richtig angegeben oder falsch ist, kann man die API nicht benutzen.
* `apimode` ist ebenfalls ein String und kann den Wert `sendmail` oder `readmail` enthalten. Dies ist der API-Modus, der benutzt werden soll.

Zusätzlich müssen zu dem jeweiligen API-Modus auch die weiteren JSON-Felder angegeben werden (siehe für den jeweiligen Modus).

### Weitere Erklärungen zu den API-Modi

Siehe `sendmail-Modus.md` und `readmail-Modus.md`.