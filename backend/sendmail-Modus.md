# sendmail - Modus

Dieser Modus dient zum Verschicken von E-Mails.

## Felder der JSON-Anfrage

| JSON - Feldname | Datentyp | Pflichtangabe | Anmerkung |
| --------------- | -------- | ------------- | --------- |
| recipients  | Array | Ja | Dies ist ein Array mit Empfängeradressen (E-Mail-Adressen). Sollte im Array eine ungültige Adresse enthalten sein, wird ein Fehler ausgegeben. |
| subject     | String | Ja | Dies ist der Betreff der E-Mail, die man verschicken möchte. |
| body        | String  | Ja | Dies ist der Nachrichteninhalt. Abhängig vom `ishtml`-Feld, kann dies HTML enthalten oder nur Plain-Text. |
| ishtml      | Boolean | Ja | Bestimmt, ob die zu verschickende E-Mail HTML benutzt oder nicht. Sollte HTML in einer Plain-Text-E-Mail enthalten sein, werden die HTML-Tags als Plain-Text ausgegeben. |
| cc          | Array | Nein | Genau wie das `recipients`-Feld, jedoch für CC (Carbon-Copy) der E-Mail. |
| bcc         | Array | Nein | Genau wie das `recipients`-Feld, jedoch für BCC-Adressen (Blind-Carbon-Copy). |
| attachments | Array | Nein | Dateianhänge, die in *Base64* kodiert sind (siehe Beispiel). |
| footer      | String | Nein | Bestimmt, welcher Footer in der E-Mail zu sehen sein soll (Auswahl auf der Seite *Footer-Preview*). Hierbei muss allerdings beachtet werden, dass NON-HTML-Footer nicht in HTML-Nachrichten und HTML-Footer nicht in NON-HTML-Nachrichten angezeigt werden, bzw. ein Fehler der API ausgegeben wird, sollte dies passieren. |
| encryption  | String | Nein | Bestimmt Verschlüsselung während der Verbindung zum SMTP-Server. Entweder ` ` (keine), `ssl`, `tls` (standardmäßig). Ungültige String-Werte werden ignoriert. |

## Beispielaufruf

Dieses Beispiel zeigt ein JSON-Objekt mit allen möglichen Parametern.

```json
{
    "apikey" : "-- Schlüssel --",
    "apimode": "sendmail",

    "recipients": ["irgendjemand@irgendwas.irgendwo","jemand.anderes@etwas.irgendwie"],
    "subject"   : "Eine ganz wichtige E-Mail",
    "body"      : "Hier stehen alle wichtigen Dinge. Alles andere im Anhang enthalten.\n\nMfG",
    "ishtml"    : false,

    "cc" : ["unbekannt@unknown.de"],
    "bcc": ["mister_myself@myown.com"],
    "attachments": [
        {
            "name"     :"caribbean_beach.png",
            "content"  : "__base64-String__",
            "mime_type": "image/png"
        },
        {
            "name"     :"angebot_reise.pdf",
            "content"  : "__base64-String__",
            "mime_type": "application/pdf"
        }
    ],
    "footer": "Name_des_Footers_aus_der_PMail_Datenbank",
    "encryption": "ssl"
}
```