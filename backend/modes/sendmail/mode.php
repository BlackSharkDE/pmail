<?php
/**
 * Hauptmodul für "sendmail"-Modus der API
 */

//================================================================================================================

require 'phpmailer_interface.php';
require_once dirname(dirname(__DIR__)) . "/users/user.php";
require_once dirname(__DIR__) . "/response.php";
require_once dirname(dirname(__DIR__)) . "/database/connection.php";

//================================================================================================================
//-- Hilfsfunktionen --

/**
 * Checkt, ob alle nötigen API-Parameter für den "sendmail"-Modus übergeben wurden (nicht ob sie stimmen) und ob deren Datentypen stimmen
 * @param array Ein Array mit den Daten, die an die API geschickt wurden
 * @return bool True wenn ok / False wenn nicht
 */
function sendmail_essentialParametersComplete(array $postDataToCheck) {
    return (
        array_key_exists('recipients',$postDataToCheck) && !is_null($postDataToCheck['recipients']) && is_array($postDataToCheck['recipients']) && count($postDataToCheck['recipients']) > 0
        &&
        array_key_exists('subject',$postDataToCheck) && !is_null($postDataToCheck['subject']) && is_string($postDataToCheck['subject']) && strlen($postDataToCheck['subject']) > 0
        &&
        array_key_exists('body',$postDataToCheck) && !is_null($postDataToCheck['body']) && is_string($postDataToCheck['body']) && strlen($postDataToCheck['body']) > 0
        &&
        array_key_exists('ishtml',$postDataToCheck) && !is_null($postDataToCheck['ishtml']) && is_bool($postDataToCheck['ishtml'])
    );
}

//================================================================================================================
//-- Hauptfunktion --

/**
 * Der "sendmail"-Modus
 * @param array          Ein Array mit den Daten, die an die API geschickt wurden
 * @param PMailUser      Ein authentifizierter User mit entschlüsselten SMTP-Verbindungsdaten
 * @return PMailResponse Ein PMailResponse-Objekt
 */
function sendmail(array $postDataArray, PMailUser $authenticatedUser) {

    //Prüfe, ob alle Parameter für den Modus angegeben wurden
    if(sendmail_essentialParametersComplete($postDataArray)) {

        //Die Empfängerliste verarbeiten
        $recipientsCount = count($postDataArray['recipients']); //Anzahl vor der Reinigung speichern
        $recipientsArray = MailingOrder::removeInvalidAddresses($postDataArray['recipients']); //reinigen

        //Nur fortfahren, wenn das gereinigte Empfänger-Array unverändert ist
        if(count($recipientsArray) === $recipientsCount) {

            //Neues MailingOrder-Objekt initialisieren
            $mailingOrder = new MailingOrder($authenticatedUser,$recipientsArray,$postDataArray["subject"],$postDataArray["body"],$postDataArray["ishtml"]);

            //ggf. CC-Empfänger anhängen
            if(array_key_exists('cc',$postDataArray)) {

                $failureMsg = new PMailResponse(203,["error" => "Es sind fehlerhafte Empfaenger in der CC-Empfaengerliste!"]);

                if(!is_null($postDataArray['cc']) && is_array($postDataArray['cc'])) {

                    $ccCount = count($postDataArray['cc']);
                    $ccArray = MailingOrder::removeInvalidAddresses($postDataArray['cc']);

                    if(count($ccArray) === $ccCount && $ccCount > 0) {
                        $mailingOrder->setCC($ccArray);
                    } else {
                        return $failureMsg;
                    }

                } else {
                    return $failureMsg;
                }
            }

            //ggf. BCC-Empfänger anhängen
            if(array_key_exists('bcc',$postDataArray)) {

                $failureMsg = new PMailResponse(204,["error" => "Es sind fehlerhafte Empfaenger in der BCC-Empfaengerliste!"]);

                if(!is_null($postDataArray['bcc']) && is_array($postDataArray['bcc'])) {

                    $bccCount = count($postDataArray['bcc']);
                    $bccArray = MailingOrder::removeInvalidAddresses($postDataArray['bcc']);

                    if(count($bccArray) === $bccCount && $bccCount > 0) {
                        $mailingOrder->setBCC($bccArray);
                    } else {
                        return $failureMsg;
                    }

                } else {
                    return $failureMsg;
                }
            }

            //ggf. Anhänge anhängen
            if(array_key_exists('attachments',$postDataArray)) {

                $failureMsg = new PMailResponse(205,["error" => "Es wurden fehlerhafte Anhaenge angegeben!"]);

                if(!is_null($postDataArray['attachments']) && is_array($postDataArray['attachments'])) {

                    $attachmentsCount = count($postDataArray['attachments']);
                    $attachmentsArray = MailingOrder::removeInvalidAttachments($postDataArray['attachments']);

                    if(count($attachmentsArray) === $attachmentsCount && $attachmentsCount > 0) {
                        $mailingOrder->setAttachments($attachmentsArray);
                    } else {
                        return $failureMsg;
                    }

                } else {
                    return $failureMsg;
                }
            }

            //ggf. Footer an Body anhängen (wird durch Name in Datenbank identifiziert)
            if(array_key_exists('footer',$postDataArray)) {
                
                //Footer-Name zwischenspeichern
                $footerName = $postDataArray['footer'];

                if(!is_null($postDataArray['footer']) && is_string($postDataArray['footer'])) {
                    global $dbCon;

                    //Versuche Footer aus Datenbank abzufragen
                    $footer = $dbCon->queryDB("SELECT `ishtml`, `content` FROM `footer` WHERE `name` = ?",[$postDataArray['footer']]);

                    if(count($footer) === 1) {
                        $footer = json_decode(json_encode($footer[0]),true); //stdClass-Objekt zu Array (assoc)

                        //Prüfe, ob das ishtml-Attribut vom Footer und von der zu verschickenden E-Mail übereinstimmen
                        if($mailingOrder->getIsHtml() === boolval($footer["ishtml"])) {
                            $mailingOrder->appendToBody($footer["content"]);
                        } else {
                            return new PMailResponse(208,["error" => "Footer- und E-Mail-'ishtml' stimmen nicht ueberein!"]);
                        }
                        
                    } else {
                        return new PMailResponse(207,["error" => "Footer mit dem Namen " . $footerName . " konnte nicht gefunden werden!"]);
                    }

                } else {
                    return new PMailResponse(206,["error" => "Footer-Name ist kein String bzw. ist ungueltig!"]);
                }
            }

            //ggf. Verschlüsselung festlegen bzw. Standardwert überschreiben
            if(array_key_exists('encryption',$postDataArray)) {
                
                $failureMsg = new PMailResponse(209,["error" => "Die encryption muss ein String ('','tls','ssl') sein!"]);

                if(!is_null($postDataArray['encryption']) && is_string($postDataArray['encryption'])) {
                    $mailingOrder->setEncryption($postDataArray['encryption']);
                } else {
                    return $failureMsg;
                }
            }

            //PHPMailer ausführen und Resultat aus dem Interface abspeichern
            $mailerResult = runPHPMailer($mailingOrder);

            if(strcmp($mailerResult,"E-Mail wurde verschickt") === 0) {
                return new PMailResponse(200,["message" => "E-Mail wurde erfolgreich verschickt!"]);
            } else {
                return new PMailResponse(210,["error" => $mailerResult]);
            }

        } else {
            return new PMailResponse(202,["error" => "Es sind fehlerhafte Empfaenger in der Empfaengerliste!"]);
        }

    } else {
        return new PMailResponse(201,["error" => "Angaben fuer sendmail-Modus unvollstaendig oder falsch!"]);
    }
}