<?php
/**
 * Dinge zur Generierung & Kodierung
 */

//================================================================================================================
//-- Symmetrische Ver- und Entschlüsselung --

//Methode / Algorithmus zum ver- und entschlüsseln
$cipherMethod = "aes-256-ecb";

/**
 * Verschlüsselt Strings mit einem Schlüssel
 * @param string  Daten, die verschlüsselt werden sollen
 * @param string  Schlüssel, der für die Verschlüsselung benutzt werden soll
 * @return string Verschlüsselter String
 */
function encryptString(string $data, string $encryptionKey) {
    global $cipherMethod;
    return openssl_encrypt($data,$cipherMethod,$encryptionKey);
}

/**
 * Entschlüsselt Strings, die von der "encryptString"-Methode verschlüsselt wurden
 * @param string  Verschlüsselte Daten
 * @param string  Schlüssel, mit dem die Daten entschlüsselt werden können
 * @return string Entschlüsselter String
 */
function decryptString(string $encryptedData, string $decryptionKey) {
    global $cipherMethod;
    return openssl_decrypt($encryptedData,$cipherMethod,$decryptionKey);
}

/**
 * (DEBUG) Beispiel für symmetrische Ver- und Entschlüsselung. (void - Funktion)
 */
function debug_demonstrateSymmetricEncryption() {
    
    //Benötigte Komponenten
    $key  = "aadid9013nj32m09sdkA";
    $data = "Bob is a cool dude";

    //Verschlüsselung
    $enc = encryptString($data,$key);
    var_dump($enc);

    //Entschlüsselung
    $dec = decryptString($enc,$key);
    var_dump($dec);
}

/**
 * Verschlüsselt die Werte in einem ein Array symmetrisch
 * @param array  Ein Array mit Werten (eindimensional)
 * @param string String, der zur Verschlüsselung benutzt werden soll
 */
function encryptArrayValues(array $valuesArray,string $key) {
    for($i = 0; $i < 4; $i++) {
        $valuesArray[$i] = encryptString($valuesArray[$i],$key);
    }
    return $valuesArray;
}

//================================================================================================================
//-- API-Keys (generieren und vergleichen) --

/**
 * Generiert neue Klartext-API-Schlüssel (sind immer 50 Zeichen lang).
 * @param string  Eine User-ID (10 Zeichen)
 * @return string Klartext-API-Schlüssel (10 Zeichen + 40 Zeichen = 50 Zeichen)
 */
function generatePlainApiKey(string $userID) {
    return $userID . generateRandomString(40);
}

/**
 * Hasht einen API-Key mittels One-Way-Hash-Methode
 * @param string  Ein Klartext-API-Schlüssel
 * @return string Gehashter API-Schlüssel
 */
function hashApiKey(string $plainApiKey) {
    return password_hash($plainApiKey,PASSWORD_DEFAULT);
}

/**
 * Verifiziert einen Klartext-API-Schlüssel mit einem durch "hashApiKey" gehashten API-Schlüssel
 * @param string Klartext-API-Schlüssel
 * @param string Gehashter API-Schlüssel
 * @return bool  True, wenn Schlüssel übereinstimmen / False, wenn nicht
 */
function verifyApiKey(string $unhashedApiKey, string $hashedApiKey) {
    return password_verify($unhashedApiKey,$hashedApiKey);
}

/**
 * Prüft, ob Klartext-API-Schlüssel valides Format hat
 * @param string Ein Klartext-API-Schlüssel
 * @return bool  True, wenn ja / False, wenn nein
 */
function isValidPlainApiKey(string $plainApiKey) {
    
    //Nur valide wenn
    if(
        strlen($plainApiKey) === 50 && //Valide Länge
        ctype_alnum($plainApiKey)      //Nur alphanumersiche Zeichen
    ) {
        return True;
    }

    return False;
}

//================================================================================================================
//-- Sonstiges --

/**
 * Generiert einen zufäligen alphanumerischen String
 * @param int     Länge des Strings
 * @return string Zufälliger String
 */
function generateRandomString(int $length) {

    //Rückgabe
    $randomString = "";

    //Valide Zeichen
    $validChars = array(
        '0','1','2','3','4','5','6','7','8','9',
        'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
        'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
    );

    //Maximaler Int für Zufall
    $maxRandomInt = sizeof($validChars) - 1;

    //Zufallsstring generieren
    for($i = 0; $i < $length; $i++) {
        $randomString .= $validChars[random_int(0,$maxRandomInt)];
    }

    return $randomString;
}