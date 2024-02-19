<?php
/**
 * Alle Tests für den sendmail-Modus
 */

require "_base.php";

//================================================================================================================

//Für Verbindungsaufbau
$base = [
    "apikey"  => ApiConnect::$key,
    "apimode" => "sendmail"
];

//Komplette und richtige Angabe für den sendmail-Modus
$complete = [
    "recipients" => ["irgendjemand@irgendwas.irgendwo"],
    "subject"    => "Test",
    "body"       => "Test",
    "ishtml"     => false
];
$complete = array_merge($base,$complete);

$r = ["hans@web.de","franz-ober@web.de","hans@","hans@web","@web.de","@web","hans-ober@web.de","hans.ober@web.de"];

//================================================================================================================
//-- Richtig (nur Pflichtparameter) --

echo("<h2>Richtig (nur Pflichtparameter)</h2>");

ApiConnect::request($complete);

echo("<hr>");

//================================================================================================================
//-- Keine / unvollständige Angaben --

echo("<h2>Keine / unvollständige Angaben</h2>");

//Vollständig aber leer
$j = [
    "recipients" => [],
    "subject"    => "",
    "body"       => "",
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

//Keine "recipients"
$j = [
    "subject"    => "",
    "body"       => "",
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

//Kein "subject"
$j = [
    "recipients" => [],
    "body"       => "",
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

//Kein "body"
$j = [
    "recipients" => [],
    "subject"    => "",
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

//Kein "ishtml"
$j = [
    "recipients" => [],
    "subject"    => "",
    "body"       => "",
];
ApiConnect::request(array_merge($base,$j));

echo("<hr>");

//================================================================================================================
//-- "recipients" --

echo("<h2>recipients</h2>");

//Leer
$j = [
    "recipients" => [],
    "subject"    => "Test",
    "body"       => "Test",
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

//Falscher Datentyp
$j = [
    "recipients" => 0,
    "subject"    => "Test",
    "body"       => "Test",
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

//NULL
$j = [
    "recipients" => NULL,
    "subject"    => "Test",
    "body"       => "Test",
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

//Eine gültige, eine ungültige Adresse
$j = [
    "recipients" => ["irgendjemand@irgendwas.irgendwo","irgendjemand@irgendwas"],
    "subject"    => "Test",
    "body"       => "Test",
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

//Eine ungültige Adresse
$j = [
    "recipients" => ["irgendjemand@irgendwas"],
    "subject"    => "Test",
    "body"       => "Test",
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

echo("<hr>");

//================================================================================================================
//-- "subject" --

echo("<h2>subject</h2>");

//Leer
$j = [
    "recipients" => ["irgendjemand@irgendwas.irgendwo"],
    "subject"    => "",
    "body"       => "Test",
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

//Falscher Datentyp
$j = [
    "recipients" => ["irgendjemand@irgendwas.irgendwo"],
    "subject"    => 0,
    "body"       => "Test",
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

//NULL
$j = [
    "recipients" => ["irgendjemand@irgendwas.irgendwo"],
    "subject"    => NULL,
    "body"       => "Test",
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

echo("<hr>");

//================================================================================================================
//-- "body" --

echo("<h2>body</h2>");

//Leer
$j = [
    "recipients" => ["irgendjemand@irgendwas.irgendwo"],
    "subject"    => "Test",
    "body"       => "",
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

//Falscher Datentyp
$j = [
    "recipients" => ["irgendjemand@irgendwas.irgendwo"],
    "subject"    => "Test",
    "body"       => 0,
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

//NULL
$j = [
    "recipients" => ["irgendjemand@irgendwas.irgendwo"],
    "subject"    => "Test",
    "body"       => NULL,
    "ishtml"     => false
];
ApiConnect::request(array_merge($base,$j));

echo("<hr>");

//================================================================================================================
//-- "ishtml" --

echo("<h2>ishtml</h2>");

//Leer und Falscher Datentyp
$j = [
    "recipients" => ["irgendjemand@irgendwas.irgendwo"],
    "subject"    => "Test",
    "body"       => "Test",
    "ishtml"     => ""
];
ApiConnect::request(array_merge($base,$j));

//NULL
$j = [
    "recipients" => ["irgendjemand@irgendwas.irgendwo"],
    "subject"    => "Test",
    "body"       => "Test",
    "ishtml"     => NULL
];
ApiConnect::request(array_merge($base,$j));

echo("<hr>");

//================================================================================================================
//-- "cc" (OPTIONAL) --

echo("<h2>cc</h2>");

//Leer
$j = [
    "cc" => []
];
ApiConnect::request(array_merge($complete,$j));

//Falscher Datentyp
$j = [
    "cc" => 0
];
ApiConnect::request(array_merge($complete,$j));

//NULL
$j = [
    "cc" => NULL
];
ApiConnect::request(array_merge($complete,$j));

//Eine gültige, eine ungültige Adresse
$j = [
    "cc" => ["irgendjemand@irgendwas.irgendwo","irgendjemand@irgendwas"]
];
ApiConnect::request(array_merge($complete,$j));

//Eine ungültige Adresse
$j = [
    "cc" => ["irgendjemand@irgendwas"]
];
ApiConnect::request(array_merge($complete,$j));

echo("<hr>");

//================================================================================================================
//-- "bcc" (OPTIONAL) --

echo("<h2>bcc</h2>");

//Leer
$j = [
    "bcc" => []
];
ApiConnect::request(array_merge($complete,$j));

//Falscher Datentyp
$j = [
    "bcc" => 0
];
ApiConnect::request(array_merge($complete,$j));

//NULL
$j = [
    "bcc" => NULL
];
ApiConnect::request(array_merge($complete,$j));

//Eine gültige, eine ungültige Adresse
$j = [
    "bcc" => ["irgendjemand@irgendwas.irgendwo","irgendjemand@irgendwas"]
];
ApiConnect::request(array_merge($complete,$j));

//Eine ungültige Adresse
$j = [
    "bcc" => ["irgendjemand@irgendwas"]
];
ApiConnect::request(array_merge($complete,$j));

echo("<hr>");

//================================================================================================================
//-- "attachments" (OPTIONAL) --

echo("<h2>attachments</h2>");

//Leer
$j = [
    "attachments" => []
];
ApiConnect::request(array_merge($complete,$j));

//Falscher Datentyp
$j = [
    "attachments" => 0
];
ApiConnect::request(array_merge($complete,$j));

//NULL
$j = [
    "attachments" => NULL
];
ApiConnect::request(array_merge($complete,$j));

//1x richtig, 3x falsch
$j = [
    "attachments" => [
        [
            "name"      => "a_readme.md",
            "content"   => base64_encode(file_get_contents(__DIR__ . "/README.md")),
            "mime_type" => "text/plain"
        ],
        [
            "name"      => "emptyContent",
            "content"   => "",
            "mime_type" => "text/plain"
        ],
        [
            "name"      => "missingAttribute",
            "mime_type" => "text/plain"
        ],
        [
            "name"      => "wrongAttribute",
            "content"   => 0,
            "mime_type" => "text/plain"
        ]
    ]
];
ApiConnect::request(array_merge($complete,$j));

echo("<hr>");

//================================================================================================================
//-- "footer" (OPTIONAL) --

echo("<h2>footer</h2>");

//Leer
$j = [
    "footer" => []
];
ApiConnect::request(array_merge($complete,$j));

//Falscher Datentyp
$j = [
    "footer" => 0
];
ApiConnect::request(array_merge($complete,$j));

//NULL
$j = [
    "footer" => NULL
];
ApiConnect::request(array_merge($complete,$j));

//Ungültiger Name (nicht in Datenbank)
$j = [
    "footer" => "super_mega_heftiger_nicht_in_datenbank_seiender_footer"
];
ApiConnect::request(array_merge($complete,$j));

//Footer passt nicht zur E-Mail (ishtml-Angabe, gehe davon aus, dass Request mit ishtml = false ist)
$j = [
    "footer" => "standard_html"
];
ApiConnect::request(array_merge($complete,$j));

echo("<hr>");

//================================================================================================================
//-- "encryption" (OPTIONAL) --
//==> Hier kommt es auf den E-Mail-Account an, mit dem man den Verbindungsaufbau versucht

echo("<h2>encryption</h2>");

//Falscher Datentyp
$j = [
    "encryption" => 0
];
ApiConnect::request(array_merge($complete,$j));

//NULL
$j = [
    "encryption" => NULL
];
ApiConnect::request(array_merge($complete,$j));

//Leer (gültig)
$j = [
    "encryption" => ["-- ARRAY ZU STRING MACHEN UND HIER EINTRAGEN --"]
];
ApiConnect::request(array_merge($complete,$j));

//SSL (gültig, komische Schreibweise)
$j = [
    "encryption" => ["-- ARRAY ZU STRING MACHEN UND HIER EINTRAGEN --"]
];
ApiConnect::request(array_merge($complete,$j));

//TLS (gültig, komische Schreibweise)
$j = [
    "encryption" => ["-- ARRAY ZU STRING MACHEN UND HIER EINTRAGEN --"]
];
ApiConnect::request(array_merge($complete,$j));