<?php
/**
 * Alle Tests für den readmail-Modus
 */

require "_base.php";

//================================================================================================================
//-- Einstellungen --

//Für Verbindungsaufbau
$base = [
    "apikey"  => ApiConnect::$key,
    "apimode" => "readmail"
];

//Ordnerpfad im Postfach
define('FOLDERPATH_TO_TEST',"-- ORDNERPFAD --");

//Funktionierende Verbindungseinstellungen, die für den IMAP-Server, der zum Testen benutzt wird, gelten
define('WORKING_IMAP_CONNECTION',["secure_password" => false,"encryption" => "ssl"]);

//================================================================================================================
//-- Allgemeine Tests --

//Weder "method" noch "connection" angegeben
function test_missingMethodAndConnection() {
    return [];
}

//"method" aber keine "connection" angegeben
function test_methodButNoConnection() {
    return ["method" => METHOD_FOR_CONNECTION_TESTS];
}

//"connection" aber keine "method" angegeben
function test_connectionButNoMethod() {
    return ["connection" => CONNECTION_FOR_METHOD_TESTS];
}

//"method" aber leere "connection"
function test_methodButEmptyConnection() {
    return array_merge(test_methodButNoConnection(), ["connection" => []]);
}

//Leere "method" aber "connection"
function test_connectionButEmptyMethod() {
    return array_merge(["method" => []],test_connectionButNoMethod());
}

//"method" gültig, "connection" ungültig
function test_correctMethodWrongConnection() {
    return array_merge(test_methodButNoConnection(),["connection" => ""]);
}

//"method" ungültig, "correction" gültig
function test_correctConnectionWrongMethod() {
    return array_merge(["method" => ""],test_connectionButNoMethod());
}

//================================================================================================================
//-- "method"-Abteil ("connection" ist gültig) --

define("CONNECTION_FOR_METHOD_TESTS",WORKING_IMAP_CONNECTION);

//Angegeben, aber leeres Array
function test_emptyMethod() {
    $t = [
        "method" => [],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Angegeben, aber kein Array
function test_invalidMethod() {
    $t = [
        "method" => "",

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Kein Parameter, obwol einer benötigt wird (int)
function test_ParamMissing_int() {
    $t = [
        "method" => [
            "name"       => "getMessageBody",
            "folderpath" => FOLDERPATH_TO_TEST,
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Kein Parameter, obwol einer benötigt wird (String)
function test_ParamMissing_string() {
    $t = [
        "method" => [
            "name"       => "getAllWithSubject",
            "folderpath" => FOLDERPATH_TO_TEST
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Falscher Parameter (String statt Int)
function test_wrongParam_StringInsteadOfInt() {
    $t = [
        "method" => [
            "name"       => "getMessageBody",
            "parameter"  => "",
            "folderpath" => FOLDERPATH_TO_TEST
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Falscher Parameter (Int statt String)
function test_wrongParam_IntInsteadOfString() {
    $t = [
        "method" => [
            "name"       => "getAllUnseenFrom",
            "parameter"  => 0,
            "folderpath" => FOLDERPATH_TO_TEST
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Parameter, obwohl keiner benötigt wird
function test_notNeededParam() {
    $t = [
        "method" => [
            "name"       => "getMessageCount",
            "parameter"  => 0,
            "folderpath" => FOLDERPATH_TO_TEST
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Falscher Datentyp für Parameter (weder Int noch String) #1
function test_notSupportedParam_array() {
    $t = [
        "method" => [
            "name"       => "getMessageBody",
            "parameter"  => array(),
            "folderpath" => FOLDERPATH_TO_TEST
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Falscher Datentyp für Parameter (weder Int noch String) #2
function test_notSupportedParam_null() {
    $t = [
        "method" => [
            "name"       => "getMessageBody",
            "parameter"  => null,
            "folderpath" => FOLDERPATH_TO_TEST
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Unbekannte Methode
function test_unknownFunction() {
    $t = [
        "method" => [
            "name"       => "__UNKNOWN__"
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Falscher Name einer bekannten Funktion
function test_wrongFunctionName() {
    $t = [
        "method" => [
            "name" => "getMessageBod"
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Funktionsname ist kein String
function test_wrongFunctionNameType() {
    $t = [
        "method" => [
            "name" => null
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Funktionsname ist einer der Funktionen, die aber nicht von außen erreichbar ist
function test_otherFunctionName() {
    $t = [
        "method" => [
            "name" => "getHeaderInfo"
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Ungültige MessageNumber (int zu groß)
function test_messageNumberToHigh() {
    $t = [
        "method" => [
            "name"       => "getMessageBody",
            "parameter"  => 999,
            "folderpath" => FOLDERPATH_TO_TEST
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Ungültige MessageNumber (int zu klein)
function test_messageNumberToLow() {
    $t = [
        "method" => [
            "name"       => "getMessageBody",
            "parameter"  => -1,
            "folderpath" => FOLDERPATH_TO_TEST
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Ungültige MessageNumber (Float)
function test_messageNumberFloat() {
    $t = [
        "method" => [
            "name"       => "getMessageBody",
            "parameter"  => 1.5,
            "folderpath" => FOLDERPATH_TO_TEST
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//String-Parameter enthält PHP Code
function test_invalidStringParam_code() {
    $t = [
        "method" => [
            "name"       => "getAllWithSubject",
            "parameter"  => ";echo('Hi');",
            "folderpath" => FOLDERPATH_TO_TEST
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//String-Parameter enthält Zeichen, die den String insofern erweitern, als das er mehr kann, als er sollte
function test_invalidStringParam_methodExtension() {
    $t = [
        "method" => [
            "name"       => "getAllWithSubject",          //Siehe Code zur Method
            "parameter"  => ' " FROM "some@anything.com', //Beende mit ' "' den Subject-String und füge ein weiteres Keyword (suche nach einer Adresse) hinzu
            "folderpath" => "" //Ordner soll Inbox sein (da liegen viele E-Mails)
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//"folderpath" ist kein String
function test_folderpathNotString() {
    $t = [
        "method" => [
            "name"       => "getMessageBody",
            "parameter"  => 1,
            "folderpath" => 0
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}



//Gültige Methode, gültiger Parameter (int)
function test_validMethod_int() {
    $t = [
        "method" => [
            "name"       => "getMessageBody",
            "parameter"  => 1,
            "folderpath" => FOLDERPATH_TO_TEST
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Gültige Methode, gültiger Parameter (String)
function test_validMethod_string() {
    $t = [
        "method" => [
            "name"       => "getAllWithSubject",
            "parameter"  => "Friday",
            "folderpath" => FOLDERPATH_TO_TEST
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Gültige Methode, gültiger Parameter (keiner)
function test_validMethod_noParam() {
    $t = [
        "method" => [
            "name"       => "getMessageCount",
            "folderpath" => FOLDERPATH_TO_TEST
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//Gültige Methode, gültiger Parameter (keiner), kein "folderpath"
function test_validMethod_noParamNoFolderpath() {
    $t = [
        "method" => [
            "name"       => "getMessageCount"
        ],

        "connection" => CONNECTION_FOR_METHOD_TESTS
    ];
    return $t;
}

//================================================================================================================
//-- "connection"-Abteil ("method" ist gültig) --

define("METHOD_FOR_CONNECTION_TESTS",["name" => "getMessageCount","folderpath" => FOLDERPATH_TO_TEST]);

//Angegeben, aber leeres Array
function test_emptyConnection(){
    $t = [
        "method" => METHOD_FOR_CONNECTION_TESTS,

        "connection" => []
    ];
    return $t;
}

//Angegeben, aber kein Array
function test_invalidConnection() {
    $t = [
        "method" => METHOD_FOR_CONNECTION_TESTS,

        "connection" => ""
    ];
    return $t;
}

//"secure_password" ist Int
function test_SecurePassword_int() {
    $t = [
        "method" => METHOD_FOR_CONNECTION_TESTS,

        "connection" => [
            "secure_password" => 1,
            "encryption"      => WORKING_IMAP_CONNECTION["encryption"]
        ]
    ];
    return $t;
}

//"secure_password" ist String
function test_SecurePassword_string() {
    $t = [
        "method" => METHOD_FOR_CONNECTION_TESTS,

        "connection" => [
            "secure_password" => "",
            "encryption"      => WORKING_IMAP_CONNECTION["encryption"]
        ]
    ];
    return $t;
}

//"encryption" ist Int
function test_Encryption_int() {
    $t = [
        "method" => METHOD_FOR_CONNECTION_TESTS,

        "connection" => [
            "secure_password" => WORKING_IMAP_CONNECTION["secure_password"],
            "encryption"      => 1
        ]
    ];
    return $t;
}

//"encryption" ist kein gültiger Wert
function test_Encryption_wrongValue() {
    $t = [
        "method" => METHOD_FOR_CONNECTION_TESTS,

        "connection" => [
            "secure_password" => WORKING_IMAP_CONNECTION["secure_password"],
            "encryption"      => "_ssl"
        ]
    ];
    return $t;
}

//"secure_password", kein "encryption"
function test_securePasswordNoEncryption() {
    $t = [
        "method" => METHOD_FOR_CONNECTION_TESTS,

        "connection" => [
            "secure_password" => WORKING_IMAP_CONNECTION["secure_password"]
        ]
    ];
    return $t;
}

//"encryption", kein "secure_password"
function test_encryptionNoSecurePassword() {
    $t = [
        "method" => METHOD_FOR_CONNECTION_TESTS,

        "connection" => [
            "encryption" => WORKING_IMAP_CONNECTION["encryption"]
        ]
    ];
    return $t;
}

//================================================================================================================
//-- Definition eines Tests (damit kein Overload des IMAP-Servers) --

//Was getestet wird
$testContent = test_securePasswordNoEncryption();

//================================================================================================================
//-- Ausführung --

//Anfrage-Inhalt
$requestContent = array_merge($base,$testContent);

//Ausgabe der Anfrage zu Debug-Zwecken
echo("<hr>");
echo("<h2>Request-Daten</h2>");
var_dump($requestContent);
echo("<hr><h2>API-Antwort</h2>");

//Request
ApiConnect::request($requestContent);