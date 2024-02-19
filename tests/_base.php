<?php
/**
 * Gemeinsame Funktionen, die von den Skripten benutzt werden
 */

class ApiConnect {

    //PMail-API-URL
    public static string $url = "http://localhost/PMail/api.php";

    //PMail-API-Schlüssel
    public static string $key = "";

    /**
     * Führt einen API-Request aus
     * @param array   Assoziatives Array, welches als JSON-Payload benutzt wird
     * @param bool    Ob die Antwort direkt ausgegeben werden soll (OPTIONAL)
     * @return string Antwort der API als String
     */
    public static function request(array $jsonPostArray, bool $directOutput = true) {

        //Array zu JSON-String
        $jsonPostArray = json_encode($jsonPostArray);

        //Optionen für den Verbindungskontext
        $options = array(
            'http' => array(
            'method'  => 'POST',
            'content' => $jsonPostArray,
            'header'  => "Content-Type: application/json\r\n" . "Accept: application/json\r\n"
            )
        );

        $context = stream_context_create($options);
        $result  = file_get_contents(ApiConnect::$url, false, $context);

        //Wenn direkt ausgegeben werden soll
        if($directOutput) {
            echo($result . "\n\n<br><br>");
        }
        
        return $result;
    }
}