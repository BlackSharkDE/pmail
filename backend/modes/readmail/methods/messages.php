<?php
/**
 * Verschiedene Funktionen für IMAP-Streams.
 */

//================================================================================================================
//-- Funktionen dieses Moduls --

ReadingOrder::addToAvailableMethods(
    [
        "getMessageCount"    => [0,0],
        "getMessagesSummary" => [0,0],
        
        "setSeen"   => [1,1],
        "setUnseen" => [1,1]
    ]
);

//================================================================================================================

/**
 * Gibt die Anzahl der Nachrichten im Postfach aus (egal ob schon gelesen oder nicht).
 * @param IMAP\Connection Ein IMAP-Stream
 * @return Anzahl der Nachrichten (Integer) / -1 bei Fehlschlag
 */
function getMessageCount($imapStream) {
    $messagesSummary = getMessagesSummary($imapStream);

    if(is_array($messagesSummary)) {
        return sizeof($messagesSummary);
    }

    return -1;
}

/**
 * Liefert eine Zusammenfassung aller Nachrichtenköpfe eines IMAP-Stream.
 * @param IMAP\Connection Ein IMAP-Stream
 * @return array          Array bestehend aus Strings, die jeweils den Nachrichtenkopf zusammenfassen / False bei Fehlschlag
 */
function getMessagesSummary($imapStream) {
    $messageHeaders = imap_headers($imapStream);

    if(!is_array($messageHeaders)) {
        return false;
    }

    return $messageHeaders;
}

//================================================================================================================
//-- Attribute von Nachrichten --

/**
 * Setzt das "gelesen"-Flag einer Nachricht --> Nachricht gilt dann als gelesen.
 * @param IMAP\Connection Ein IMAP-Stream
 * @param int             Nummer der Nachricht in dem IMAP-Stream 
 * @return bool           Bei Erfolg True / Im Fehlerfall False
 */
function setSeen($imapStream,int $messageNumber) {
    return imap_setflag_full($imapStream,strval($messageNumber),'\Seen');
}

/**
 * Entfernt das "gelesen"-Flag einer Nachricht --> Nachricht gilt dann als ungelesen.
 * @param IMAP\Connection Ein IMAP-Stream
 * @param int             Nummer der Nachricht in dem IMAP-Stream 
 * @return bool           Bei Erfolg True / Im Fehlerfall False
 */
function setUnseen($imapStream,int $messageNumber) {
    return imap_clearflag_full($imapStream,$messageNumber,'\Seen');
}