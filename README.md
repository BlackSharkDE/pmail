# PMail

Eine in PHP geschriebene E-Mail API, die mit JSON-POST-Daten arbeitet

## Allgemeines

**PMail** steht für **P**HP und / oder **P**igeon (Taube) (E-)**Mail**.

Diese API bietet eine allgemeine Implementation des Verschickens und dem Lesen (abrufen) von E-Mails. Dadurch, dass Clients bzw. User der API lediglich ein paar JSON-Daten schicken müssen, bietet PMail einen einfachen Zugriff für jederart Software auf E-Mail-Funktionalitäten.

So muss die Funktionalität, mit E-Mails umzugehen, also der Implementation von SMTP/IMAP-Funktionen, nicht in jeder Software, die etwas mit Mailing zutun haben soll, immer wieder aufs Neue implementiert werden.

Jede Software, die mit E-Mails arbeiten soll, muss nur eine Standard-HTTP-Anfrage (gebündelt mit JSON-Daten (HTTP-POST)) an die API stellen, was weitaus einfacher und trivialer für viele Programmiersprachen und Softwares ist.

## Installation

1. In der Datei `backend/database/setup.php` einen User, Passwort und Datenbank-Host eintragen (Hinweis: Dieser User mitsamt Passwort sollte ein Admin sein, der nicht im Produktivbetrieb genutzt wird!).
2. Die Datei `backend/database/setup.php` im Browser aufrufen. Wenn das Setup erfolgreich war, die Datei löschen.
3. In der Datei `backend/database/connection.php` den User, Passwort und Datenbank-Host eintragen (Hinweis: Dies sollte ein User mitsamt Passwort sein, der nur auf der `pmail`-Datenbank arbeiten darf!).
4. Ordner, die nicht von Außen erreichbar sein sollen, absichern (z.B. mit `.htaccess`-Dateien):
   * `backend`
   * `frontend/php`
   * `frontend/views/subviews`
5. Einstellungen für das Frontend: Lediglich die Einstellungen in der Datei `php/options.php` müssen angepasst werden.
6. Optional, aber empfohlen für Deployments:
   * Die `README`-Dateien entfernen
   * Den Ordner `tests` entfernen
   * Die `.gitmodules`-Datei und den `.git`-Ordner entfernen

## Ein paar Gedanken zu Bots und Automatisierung

Dies betrifft hauptsächlich den `readmail`-Modus.

Sollte man die API in Verbindung mit Bots benutzen, sollte man einen Bot pro API-Account bzw. E-Mail Account einsetzen. Das hat den Hintergrund, dass ein Bot sich selbst besser verwalten kann, als wenn selbiger von der Arbeit von anderen Bots im gleichen Account irritiert wird. So geht beispielsweise eine Nachricht im Account ein, die Bot 1 liest und verarbeitet. Sollte Bot 2 keine Kontrollmechanismen besitzen, wie beispielsweise nur auf ungelesene Nachrichten zu reagieren, könnte unter Umständen ein "Auftrag" / Nachricht doppelt bearbeitet werden. Selbst wenn Bot 1 die Nachricht direkt löschen wurde, nachdem er diese verarbeitet hat, besteht immer ein Restrisiko, dass Bot 2 dazwischenfunkt, wodurch eine Nachricht beispielsweise nicht richtig gelöscht werden konnte.

Zudem sollte man immer die Menge der Anfragen pro Zeiteinheit im Auge behalten. Der API selbst machen die Anfragen nichts aus, da es sicher weniger aktive Nutzer gibt, als bei einem E-Mail-Provider. Der Provider hingegen wird die Verbindungen der API auf den Account aber sicher mitloggen und daher vielleicht, bei zu vielen Anfragen von einem DDoS-Versuch ausgehen. Daher sollte man nicht zu viele Verbindungen in einen Account pro Zeiteinheit haben. Ein vertretbarer Rahmen wäre, so denke ich, eine Anfrage pro 5 bis 10 Minuten. Bei 5 Minuten wären das in 24 Stunden ganze 288 $((24 \space Stunden \times 60 \space Minuten) \div 5)$ Anfragen, bei einem 10 Minuten-Intervall 144 Anfragen pro Tag.

E-Mail-Clients wie Thunderbird fragen die Server sicher öfter ab, sie loggen sich dabei aber dauerhaft auf den IMAP-Servern ein und müssen daher nicht andauerd den Login-Traffic erzeugen. Die Provider pushen die Nachrichten dann während der Session, sollten neue eintreffen. Dies ist mit der API nicht möglich.