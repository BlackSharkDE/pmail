# frontend

## Voraussetzungen

* Apache 2.4.43
* PHP mindestens in der Version 7.4.29
* **Font Awesome 4**-Bezugsquelle

## Informationen

Dies ist das Admin-Panel von PMail. Hier können alle administrativen Aufgaben in Bezug auf PMail erledigt werden.

Ein nicht öffentliches Frontend hat den Vorteil, dass es einfach sicherer und passender für diese Art der Applikation ist:
* Man benötigt einen Admin-Account, um überhaupt mit dem Frontend zu interagieren.
* Keine CAPTCHAs um Brute-Forcing für einen einzelnen PMail-Account zu verhindern (die API gibt ja keine Benutzerdaten aus bzw. kann nicht auf die Accounts einwirken).
* Anlegen/Löschen bzw. das Managen von Accounts ohne Kenntnisnahme ist damit auch unmöglich.
* Auch per Definition nicht für die Öffentlichkeit gedachte Dinge können angesehen und angepasst werden (z.B. Logging).