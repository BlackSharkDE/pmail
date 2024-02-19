<?php
/**
 * Die API-Antworten
 * 
 * Generelles:
 * - Statuscodes mit 1xx sind vom API-Ablauf selbst
 * - Statuscodes mit 2xx sind vom Sendmail-Modus => 200 bedeutet, dass Modus OK
 * - Statuscodes mit 3xx sind vom Readmail-Modus => 300 bedeutet, dass Modus OK
 */

class PMailResponse {

    //-- Alles, was in der Response enthalten sein muss --
    private int $statusCode;   //Beliebige Statusnummer, die die Nachricht eindeutig identifiziert
    private array $value;      //Array (assoziativ), welches als Antwort von Methoden etc. ausgegeben wird (sozusagen ein JSON-Objekt)
    private string $timestamp; //Zeitstempel (wird automatisch gesetzt)

    /**
     * -- Konstruktor --
     * @param int   Siehe oben
     * @param array Siehe oben
     */
    public function __construct(int $sc, array $vl) {
        $this->statusCode = $sc;
        $this->value      = $vl;
        $this->timestamp = PMailResponse::createCurrentTimestamp();
    }

    /**
     * Gibt das Objekt als assoziatives Array zurück
     * @return array Objekt als Array
     */
    public function toArray() {
        return [
            "statusCode" => $this->statusCode,
            "value"      => $this->value,
            "timestamp"  => $this->timestamp
        ];
    }

    /**
     * Erstellt einen Zeitstempel-String mit 6 Millisekunden-Stellen (fest)
     * @return string Aktueller Zeitstempel im Format "Y-m-d H:i:s.u"
     */
    public static function createCurrentTimestamp() {
        $time  = microtime(true);
        $micro = sprintf("%06d",($time - floor($time)) * 1000000);
        $date  = new DateTime(date('Y-m-d H:i:s.' . $micro, $time));
        return $date->format("Y-m-d H:i:s.u");
    }
}