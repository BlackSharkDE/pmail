<?php
/**
 * Dieses Modul bietet viele vorgefertigte Varianten der "imap_search()" - Funktion.
 * => ACHTUNG: Nicht jeder IMAP-Server unterstützt zwingend alle Suchwörter <=
 *
 * Sucht nach Nachrichten in einem IMAP-Stream, die einem Kriterium entsprechen.
 *
 * Mindestübergabe bei allen:
 * @param IMAP\Connection Ein IMAP-Stream
 *
 * Rückgabe aller Methoden:
 * @return array/false
 * --> Bei Erfolg: Ein Array mit den Nachrichtennummern, die zum Suchergebnis passen.
 * --> Bei Fehlschlag: False (Boolean)
 */

//================================================================================================================
//-- Funktionen dieses Moduls --

ReadingOrder::addToAvailableMethods(
    [
        "getAllUnimportant" => [0,0],
        "getAllImportant"   => [0,0],
        "getAllSeen"        => [0,0],
        "getAllUnseen"      => [0,0],
        "getAllUnseenFrom"  => [0,0],
        "getAllSeenFrom"    => [0,0],

        "getAllFrom"          => [0,0],
        "getAllWithCC"        => [0,0],
        "getAllWithBCC"       => [0,0],
        "getAllWithRecipient" => [0,0],

        "getAllFromToday"     => [0,0],
        "getAllFromYesterday" => [0,0],
        "getAllFromTimestamp" => [0,0],
        
        "getAllWithSubject"     => [0,0],
        "getAllWithBodyContent" => [0,0]
    ]
);

//================================================================================================================
//-- Attributssuchen --

/**
 * Alle unwichtigen Nachrichten (nicht als WICHTIG markiert).
 */
function getAllUnimportant($imapStream) {
    return imap_search($imapStream,'ALL UNFLAGGED');
}

/**
 * Alle wichtigen Nachrichten (als WICHTIG markiert).
 */
function getAllImportant($imapStream) {
    return imap_search($imapStream,'ALL FLAGGED');
}

/**
 * Alle gelesenen Nachrichten.
 */
function getAllSeen($imapStream) {
    return imap_search($imapStream,'ALL SEEN');
}

/**
 * Alle ungelesenen Nachrichten.
 */
function getAllUnseen($imapStream) {
    return imap_search($imapStream,'ALL UNSEEN');
}

/**
 * Alle ungelesenen Nachrichten von $fromAddress.
 */
function getAllUnseenFrom($imapStream,string $fromAddress) {
    return imap_search($imapStream,'ALL UNSEEN FROM "' . $fromAddress . '"');
}

/**
 * Alle gelesenen Nachrichten von $fromAddress.
 */
function getAllSeenFrom($imapStream,string $fromAddress) {
    return imap_search($imapStream,'ALL SEEN FROM "' . $fromAddress . '"');
}

//================================================================================================================
//-- Absender, CC und BCC und Empfänger --

/**
 * Alle Nachrichten von $fromAddress.
 */
function getAllFrom($imapStream,string $fromAddress) {
    return imap_search($imapStream,'ALL FROM "' . $fromAddress . '"');
}

/**
 * Alle Nachrichten mit CC an $ccAddress.
 */
function getAllWithCC($imapStream,string $ccAddress) {
    return imap_search($imapStream,'ALL CC "' . $ccAddress . '"');
}

/**
 * Alle Nachrichten mit BCC an $bccAddress.
 */
function getAllWithBCC($imapStream,string $bccAddress) {
    return imap_search($imapStream,'ALL BCC "' . $bccAddress . '"');
}

/**
 * Alle Nachrichten mit der Empfängeradresse $recipientAddress (sinnvoll, wenn eine E-Mail mehr als nur einen Empfänger hat).
 */
function getAllWithRecipient($imapStream,string $recipientAddress) {
    return imap_search($imapStream,'ALL TO "' . $recipientAddress . '"');
}

//================================================================================================================
//-- Datumssuchen --

/**
 * Alle Nachrichten von heute.
 */
function getAllFromToday($imapStream) {
    $dateString = date("j F Y");
    return imap_search($imapStream,'ALL ON "' . $dateString . '"');
}

/**
 * Alle Nachrichten von gestern.
 */
function getAllFromYesterday($imapStream) {
    $dateString = date("j F Y",strtotime("yesterday"));
    return imap_search($imapStream,'ALL ON "' . $dateString . '"');
}

/**
 * Alle Nachrichten von einem bestimmten Tag (Unix-Timestamp liegt innerhalb des Tages, der gesucht wird).
 */
function getAllFromTimestamp($imapStream,int $unixTimestamp) {
    $dateString = date("j F Y",$unixTimestamp);
    return imap_search($imapStream,'ALL ON "' . $dateString . '"');
}

//================================================================================================================
//-- Inhaltssuchen --

/**
 * Alle Nachrichten, bei denen $subject im Betreff vorkommt.
 */
function getAllWithSubject($imapStream,string $subject) {
    return imap_search($imapStream,'ALL SUBJECT "' . $subject . '"');
}

/**
 * Alle Nachrichten, bei denen $text im Body vorkommt (auch sinnvoll für Suchen, die auf Quelltext-Ebene der Nachricht sind).
 */
function getAllWithBodyContent($imapStream,string $text) {
    return imap_search($imapStream,'ALL BODY "' . $text . '"');
}