<?php
/**
 * API-Endpunkt für alle Interaktionen von Außen
 */

//================================================================================================================

//Modi der API
require __DIR__ . "/backend/modes/readmail/mode.php";
require __DIR__ . "/backend/modes/sendmail/mode.php";

//Weiteres
require_once __DIR__ . "/backend/modes/response.php";
require __DIR__ . "/backend/users/auth.php";

//================================================================================================================
//-- Hilfsfunktionen --

/**
 * Checkt, ob alle nötigen API-Parameter übergeben wurden (nicht ob sie stimmen) und ob deren Datentypen stimmen
 * @param array Ein Array mit den Daten, die an die API geschickt wurden
 * @return bool True wenn ok / False wenn nicht
 */
function apiParametersComplete(array $postDataToCheck) {
    return (
        array_key_exists('apikey',$postDataToCheck) && !is_null($postDataToCheck['apikey']) && is_string($postDataToCheck['apikey'])
        &&
        array_key_exists('apimode',$postDataToCheck) && !is_null($postDataToCheck['apimode']) && is_string($postDataToCheck['apimode'])
    );
}

//================================================================================================================
//-- Hauptroutine --

//"php://input" für Post-Input und True, damit das JSON als assoziatives Array zurückgegeben wird
//Sollte das JSON nicht dekodieren werden können, wird $postData zu NULL
$postData = json_decode(file_get_contents('php://input'), True);

//Rückgabearray (assoziativ)
$response = ["DEBUG" => "DUMMY"];

if(!is_null($postData) && apiParametersComplete($postData)) {

    //PMail-API-Key lesen
    $apikey = $postData['apikey'];

    //PMail-API-Modus lesen (kleingeschrieben, damit besser vergleichbar)
    $apimode = strtolower($postData['apimode']);

    //Versuche User zu authentifizieren
    $user = authenticateUser($apikey);

    //Wenn User authentifiziert wurde
    if($user->isValidUser()) {

        //API-Key entfernen (wird ab hier für nichts mehr benötigt)
        unset($postData["apikey"]);

        //Prüfe, ob gültiger Modus angegeben wurde
        if(strcmp($apimode,"sendmail") === 0) {

            $response = sendmail($postData,$user);

        } else if(strcmp($apimode,"readmail") === 0) {

            $response = readmail($postData,$user);

        } else {
            $response = new PMailResponse(103,["error" => "API-Modus " . $apimode . " ist ungueltig"]);
        }

    } else {
        $response = new PMailResponse(102,["error" => "User konnte nicht mit dem API-Key " . $apikey . " authentifiziert werden"]);    
    }

} else {
    $response = new PMailResponse(101,["error" => "API-Angaben unvollstaendig oder fehlerhaft"]);
}

//Antwort als Array
$response = $response->toArray();

//Sollte die Antwort einen Error beinhalten
if($response["statusCode"] !== 200 || $response["statusCode"] !== 300) {
    require_once "backend/database/connection.php";

    //Sollten Anhänge angebeben worden sein, diese herausfiltern
    //--> Diese gehören definitiv nicht die DB (Datenschutz sozusagen)
    //--> Außerdem verbrauchen diese ggf. sehr viel unnötigen Speicherplatz
    if(isset($postData["attachments"])) {
        $postData["attachments"] = "-- PURGED --";
    }

    //In die Datenbank loggen
    $dbCon->queryDBNoFetch("
        INSERT INTO `pmail`.`logs` VALUES (?,?,?,?)
    ",array(
        $response["timestamp"],
        $response["statusCode"],
        implode($response["value"]), //Array zu String (ist immer eine "error"-Eintrag)
        var_export($postData,true)   //Array zu String (ist gegebenenfalls verschachtelt)
    ));
}

//Die Ausgabe / API-Antwort
header('Content-Type: application/json'); //Content-Type angeben
echo(json_encode($response)); //Ausgabe der API, bzw. dies wird an den Client zurückgesendet