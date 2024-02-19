# readmail - Modus

Dieser Modus dient zum Verschicken von E-Mails.

## Felder der JSON-Anfrage

| JSON - Feldname | Datentyp | Pflichtangabe | Anmerkung |
| --------------- | -------- | ------------- | --------- |
| method/name                | String | Ja | Name einer *Method*. Diese und die Parameter dazu sind in den Dateien im Pfad `modes/readmail/methods` zu finden | 
| method/parameter           | Int/String | Nein | Kann leer sein bzw. entweder ein *String* oder *Int* sein, je nachdem, was die angegebene Methode benötigt |
| method/folderpath          | String | Nein | Ohne Angabe wird die Inbox / der Posteingang angesteuert |
| | | | |
| connection/secure_password | Boolean | Nein | Übertragung von Klartext-Passwörtern wird verhindert (standarmäßig `true`) |
| connection/encryption      | String | Nein | Bestimmt Verschlüssung während der Verbindung zum IMAP-Server: `ssl` (standardmäßig), `tls`, `notls` (Klartext) |

## Beispielaufruf

Dieses Beispiel zeigt ein JSON-Objekt mit allen möglichen Parametern.

```json
{
    "apikey" : "-- Schlüssel --",
    "apimode": "readmail",

    "method": {
        "name": "A_Method_Name",
        "parameter": 1,
        "folderpath": "unknown/probably spam"
    },

    "connection": {
        "secure_password": true,
        "encryption": "tls"
    }
}
```