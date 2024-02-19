<?php
/**
 * Generelle Tests für die API
 */

require "_base.php";

//================================================================================================================
//-- Keine / unvollständige Angaben --

$j = [
];
ApiConnect::request($j);

$j = [
    "apikey" => ""
];
ApiConnect::request($j);

$j = [
    "apimode" => ""
];
ApiConnect::request($j);

echo("<hr>");

//================================================================================================================
//-- "apikey" --

//Leerer Key
$j = [
    "apikey"  => "",
    "apimode" => ""
];
ApiConnect::request($j);

//Ungültiger Key (existiert nicht in Datenbank)
$j = [
    "apikey"  => "ENLB2DowZaPfW3J2KlH0DfCtmeQ00aKcQDdIgweMyeJSfkHTjr",
    "apimode" => ""
];
ApiConnect::request($j);

//Ungültiger Key (nur ID)
$j = [
    "apikey"  => "ENLB2DowZa",
    "apimode" => ""
];
ApiConnect::request($j);

//Gültiger Key (nur ID)
$j = [
    "apikey"  => "XLaPYHgjTX",
    "apimode" => ""
];
ApiConnect::request($j);

//Gültiger Key
$j = [
    "apikey"  => ApiConnect::$key,
    "apimode" => "-- Nicht beachten --"
];
ApiConnect::request($j);

echo("<hr>");

//================================================================================================================
//-- "apimode" --

//Kein Modus
$j = [
    "apikey"  => ApiConnect::$key,
    "apimode" => ""
];
ApiConnect::request($j);

//Ungültiger Modus
$j = [
    "apikey"  => ApiConnect::$key,
    "apimode" => "Schreibtisch"
];
ApiConnect::request($j);

//Sendmail-Modus
$j = [
    "apikey"  => ApiConnect::$key,
    "apimode" => "SeNDmail"
];
ApiConnect::request($j);

//Readmail-Modus
$j = [
    "apikey"  => ApiConnect::$key,
    "apimode" => "reADMail"
];
ApiConnect::request($j);

echo("<hr>");