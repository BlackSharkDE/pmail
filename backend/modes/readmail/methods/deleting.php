<?php
/**
 * Funktionen zum Löschen von Nachrichten.
 */

//================================================================================================================
//-- Funktionen dieses Moduls --

ReadingOrder::addToAvailableMethods(
    [
        "markMessageForDeletion"   => [1,1],
        "unmarkMessageForDeletion" => [1,1],
        "deleteMarkedMessages"     => [1,0],
        "deleteMessages"           => [1,1]
    ]
);

//================================================================================================================

/**
 * Markiert eine Nachricht zur Löschung.
 * @param IMAP\Connection Ein IMAP-Stream
 * @param int             Nummer der Nachricht in dem IMAP-Stream
 * @return bool           True (in der Dokumentation steht nur True (also immer))
 */
function markMessageForDeletion($imapStream,int $messageNumber) {
    return imap_delete($imapStream,$messageNumber);
}

/**
 * Nimmt eine Löschmarkierung einer Nachricht zurück.
 * @param IMAP\Connection Ein IMAP-Stream
 * @param int             Nummer der Nachricht in dem IMAP-Stream
 * @return bool           Bei Erfolg True / Im Fehlerfall False
 */
function unmarkMessageForDeletion($imapStream,int $messageNumber) {
    return imap_undelete($imapStream,$messageNumber);
}

/**
 * Löscht alle Nachrichten mit Löschmarkierung.
 * @param IMAP\Connection Ein IMAP-Stream (openImapPath())
 * @return bool           True (in der Dokumentation steht nur True (also immer))
 */
function deleteMarkedMessages($imapStream) {
    return imap_expunge($imapStream);   
}

/**
 * Löscht eine Nachricht direkt (ACHTUNG: Andere zur Löschung markierten Nachrichten werden dann auch gelöscht! --> Daher der Name der Funktion).
 * @param IMAP\Connection Ein IMAP-Stream
 * @param int             Nummer der Nachricht in dem IMAP-Stream
 * @return bool           True (Steht für beide benutzten Methoden in der Dokumentation)
 */
function deleteMessages($imapStream,int $messageNumber) {
    markMessageForDeletion($imapStream,$messageNumber);
    return deleteMarkedMessages($imapStream,$messageNumber);
}