<?php
/**
 * Hauptmodul für "readmail"-Modus der API
 */

//================================================================================================================

require 'reading_order.php';
require_once dirname(dirname(__DIR__)) . "/users/user.php";
require_once dirname(__DIR__) . "/response.php";
require_once dirname(dirname(__DIR__)) . "/database/connection.php";

//================================================================================================================
//-- Hilfsfunktionen  --

/**
 * Checkt, ob alle nötigen API-Parameter für den "readmail"-Modus übergeben wurden (nicht ob sie stimmen) und ob deren Datentypen stimmen
 * @param array Ein Array mit den Daten, die an die API geschickt wurden
 * @return bool True wenn ok / False wenn nicht
 */
function readmail_essentialParametersComplete(array $postDataToCheck) {

    //Rückgabe
    $complete = false;

    //-- "method"-Abteil (muss angegeben worden sein) --
    if(array_key_exists('method',$postDataToCheck) && !is_null($postDataToCheck['method']) && is_array($postDataToCheck['method'])) {

        //Muss hier auf "true" gesetzt werden, da Grundvoraussetzung erfüllt ist und damit die weiteren Tests positiv bleiben können
        $complete = true;

        //Überprüfe "namen"-Attribut
        $complete = $complete && (
            array_key_exists('name',$postDataToCheck['method'])
            && !is_null($postDataToCheck['method']['name'])
            && is_string($postDataToCheck['method']['name'])
        );

        //Wenn "parameter" angegeben wurde, diesen mit überprüfen
        if(array_key_exists('parameter',$postDataToCheck["method"])) {
            $complete = $complete && (
                !is_null($postDataToCheck['method']['parameter'])
                && (is_int($postDataToCheck['method']['parameter']) || is_string($postDataToCheck['method']['parameter']))
            );
        }

        //Wenn "folderpath" angegeben wurde, diesen mit überprüfen
        if(array_key_exists('folderpath',$postDataToCheck["method"])) {
            $complete = $complete && (
                !is_null($postDataToCheck['method']['folderpath'])
                && is_string($postDataToCheck['method']['folderpath'])
            );
        }

        //-- "connection"-Abteil (sofern angegeben) --
        if(array_key_exists('connection',$postDataToCheck) && !is_null($postDataToCheck['connection']) && is_array($postDataToCheck['connection'])) {

            //Wenn "secure_password" angegeben wurde, diesen mit überprüfen
            if(array_key_exists('secure_password',$postDataToCheck["connection"])) {
                $complete = $complete && (
                    !is_null($postDataToCheck['connection']['secure_password'])
                    && is_bool($postDataToCheck['connection']['secure_password'])
                );
            }

            //Wenn "encryption" angegeben wurde, diesen mit überprüfen
            if(array_key_exists('encryption',$postDataToCheck["connection"])) {
                $complete = $complete && (
                    !is_null($postDataToCheck['connection']['encryption'])
                    && is_string($postDataToCheck['connection']['encryption'])
                );
            }
        } else if(array_key_exists('connection',$postDataToCheck) && !is_null($postDataToCheck['connection']) && !is_array($postDataToCheck['connection'])) {
            //"connection" zwar angegeben, aber falsch
            $complete = false;
        }
    }

    return $complete;
}

//================================================================================================================
//-- Hauptfunktion --

/**
 * Der "readmail"-Modus
 * @param array          Ein Array mit den Daten, die an die API geschickt wurden
 * @param PMailUser      Ein authentifizierter User mit entschlüsselten IMAP-Verbindungsdaten
 * @return PMailResponse Ein PMailResponse-Objekt
 */
function readmail(array $postDataArray, PMailUser $authenticatedUser) {

    //Prüfe, ob alle Parameter für den Modus angegeben wurden
    if(readmail_essentialParametersComplete($postDataArray)) {

        //Prüfe, ob angegebener Method-Name gültig ist
        if(ReadingOrder::isValidMethodName($postDataArray['method']['name'])) {

            //Neues ReadingOrder-Objekt initialisieren
            try {
                $readingOrder = new ReadingOrder($authenticatedUser,$postDataArray['method'],($postDataArray['connection'] ?? array()));
            } catch (Exception $e) {
                return new PMailResponse(304,["error" => $e->getMessage()]);
            }

            //Teste, ob die "maxMessageNumber" richtig gesetzt wurde
            if($readingOrder->getMaxMessageNumber() !== -1) {

                //Angegebene, valide Method ausführen und Ergebnis abfangen
                try {
                    $methodResult = $readingOrder->runMethod();
                } catch (Exception $e) {
                    $readingOrder->closeConnection();
                    return new PMailResponse(305,["error" => $e->getMessage()]);
                }

                //IMAP-Verbindung beenden
                $readingOrder->closeConnection();

                //Ergebnis der Method ausgeben
                return new PMailResponse(300,["result" => $methodResult]);

            } else {
                $readingOrder->closeConnection();
                return new PMailResponse(303,["error" => "Interner 'maxMessageNumber'-Parameter konnte nicht gesetzt werden!"]);
            }

        } else {
            return new PMailResponse(302,["error" => "Ungueltiger Method-Name '" . $postDataArray['method']['name'] . "'!"]);
        }

    } else {
        return new PMailResponse(301,["error" => "Angaben fuer readmail-Modus unvollstaendig oder falsch!"]);
    }
}