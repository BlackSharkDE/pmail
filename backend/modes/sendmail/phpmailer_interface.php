<?php
/**
 * Das Interface zu "phpmailer"
 */

//================================================================================================================
//-- PMail-Dinge --

require_once dirname(dirname(__DIR__)) . "/users/user.php";

//================================================================================================================
//-- phpmailer-Dinge --

//Binde die nötigen PHPMailer-Skripte ein
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

//Importiere folgende Funktionen
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//================================================================================================================
//-- E-Mail-Versandtauftrag --

class MailingOrder {

    //-- Pflichtangaben zur E-Mail an sich --
    private PMailUser $user;   //Ein PMail-User mit entschlüsselten SMTP-Verbindungsdaten
    private array $recipients; //Array mit Empfängeradressen (Strings), z.B. ["hans@web.de", "franz@gmail.com" ...]
    private string $subject;   //Betreff der E-Mail
    private string $body;      //Inhalt der E-Mail
    private bool $ishtml;      //Ob die E-Mail als HTML-E-Mail verschickt werden soll

    //-- Optionales zur E-Mail --
    private array $cc          = []; //Adressen, die im CC stehen sollen (siehe $recipients)
    private array $bcc         = []; //Adressen, die im BCC stehen sollen (siehe $recipients)
    private array $attachments = []; //Array bestehend aus Dateianhängen. Die Dateianhänge sind wiederum assoziative Arrays mit dem Format:
                                     // [
                                     //   "name"      => "image.png", #Dateiname mit Dateiendung (kann frei gewählt werden)
                                     //   "content"   => "/9j/4R...", #Base64 kodierter Dateiinhalt
                                     //   "mime_type" => "image/png"  #MIME-Type der Datei
                                     // ]

    //-- Verbindungsparameter --
    private string $encryption = PHPMailer::ENCRYPTION_STARTTLS; //Standardmäßig TLS nutzen (breite Nutzbarkeit)

    /**
     * -- Konstruktor --
     * Siehe "Pflichtangaben"
     */
    public function __construct(PMailUser $user, array $recipients, string $subject, string $body, bool $ishtml) {
        $this->user       = $user;
        $this->recipients = $recipients;
        $this->subject    = $subject;
        $this->body       = $body;
        $this->ishtml     = $ishtml;
    }

    /**
     * Entfernt invalide E-Mail-Adressen aus einem Array mit E-Mail-Adressen
     * @param array  Siehe "recipients"-Attribut der Klasse
     * @return array Bereinigtes Array
     */
    public static function removeInvalidAddresses(array $addresses) {
        return array_filter($addresses, fn($a) => filter_var($a, FILTER_VALIDATE_EMAIL) ? $a : "" );
    }

    /**
     * Entfernt invalide Dateianhänge aus einem Array mit Dateianhängen
     * @param array  Siehe "attachments"-Attribut der Klasse
     * @return array Bereinigtes Array
     */
    public static function removeInvalidAttachments(array $attachments) {
        return array_filter($attachments, function($attachment) {

            //Prüfe, dass alle Attribute enthalten sind
            $attachmentKeys = array_keys($attachment);
            if(!in_array("name",$attachmentKeys) || !in_array("content",$attachmentKeys) || !in_array("mime_type",$attachmentKeys)) {
                return false;
            }

            //Prüfe, dass keines der Attribute leer ist
            if(strlen($attachment["name"]) === 0 || strlen($attachment["content"]) === 0 || strlen($attachment["mime_type"]) === 0) {
                return false;
            }

            return true;
        });
    }

    //-- Getter --

    public function getUser() { return $this->user; }
    public function getRecipients() { return $this->recipients; }
    public function getSubject() { return $this->subject; }
    public function getBody() { return $this->body; }
    public function getIsHtml() { return $this->ishtml; }

    public function getCC() { return $this->cc; }
    public function getBCC() { return $this->bcc; }
    public function getAttachments() { return $this->attachments; }

    public function getEncryption() { return $this->encryption; }

    //-- Setter --

    public function setCC(array $cc) {
        $this->cc = $cc;
    }

    public function setBCC(array $bcc) {
        $this->bcc = $bcc;
    }

    public function setAttachments(array $attachments) {
        $this->attachments = $attachments;
    }

    /**
     * Setzt die Verschlüsselung
     * @param string Verschlüsselung, Auswahl: "", "tls" oder "ssl"
     */
    public function setEncryption(string $encryption) {

        //Kleinschreibung (fehlersicher)
        $encryption = strtolower($encryption);

        switch($encryption) {

            //Keine Verschlüsselung (plain)
            case "":
                $this->encryption = "";
                break;

            //TLS
            case "tls":
                $this->encryption = PHPMailer::ENCRYPTION_STARTTLS;
                break;

            //SSL
            case "ssl":
                $this->encryption = PHPMailer::ENCRYPTION_SMTPS;
                break;

            //Wenn ungültige Angabe, dann TLS
            default:
                $this->encryption = PHPMailer::ENCRYPTION_STARTTLS;
                break;
        }
    }

    /**
     * Hängt etwas an den Body nachträglich an. (void - Funktion)
     * @param string Neuer Body
     */
    public function appendToBody(string $appendix) {
        $this->body = $this->body . $appendix;
    }
}

//================================================================================================================
//-- Versenden der E-Mails --

/**
 * Versendet E-Mails via PHPMailer
 * @param MailingOrder Ein MailingOrder-Objekt, welches alle notwendigen Daten enthält
 * @return string      Ein String, der den Status wiederspiegelt (Fehler / Erfolg)
 */
function runPHPMailer(MailingOrder $mailingOrder) {

    //Neue Instanz vom PHPMailer erstellen
    $mail = new PHPMailer(true); //true aktiviert Exceptions

    //SMTP-Attribute zwischenspeichern
    $smtpAttributes = $mailingOrder->getUser()->getSmtpAttributes();

    try {

        //-- Verbindungseinstellungen Einstellungen festlegen --
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER; //DEBUG
        $mail->isSMTP();           //Benutze das SMTP-Protokoll
        $mail->SMTPAuth    = true; //Benutze SMTP-Authentifizierung (wird eigentlich von jedem SMTP-Server benötigt)
        $mail->Username    = $smtpAttributes[0];
        $mail->Password    = $smtpAttributes[1];
        $mail->Host        = $smtpAttributes[2];
        $mail->Port        = $smtpAttributes[3];
        $mail->SMTPAutoTLS = false; //Nicht automatisch TLS benutzen
        $mail->SMTPSecure  = $mailingOrder->getEncryption(); //Verschlüsselung festlegen

        //Jede E-Mail, die mit PMail gesendet wird, hat UTF-8 als Encoding
        $mail->CharSet  = "UTF-8";
        $mail->Encoding = 'base64';

        //-- E-Mail selbst festlegen --

        //Absenderadresse festlegen (ist immer der SMTP-User)
        $mail->setFrom($smtpAttributes[0]);

        //Empfänger festlegen
        foreach($mailingOrder->getRecipients() as $recipient) {
            $mail->addAddress($recipient);
        }

        //ggf. CC-Empfänger hinzufügen
        if(count($mailingOrder->getCC()) > 0) {
            foreach($mailingOrder->getCC() as $cc) {
                $mail->addCC($cc);
            }
        }

        //ggf. BCC-Empfänger hinzufügen
        if(count($mailingOrder->getBCC()) > 0) {
            foreach($mailingOrder->getBCC() as $bcc) {
                $mail->addBCC($bcc);
            }
        }

        //ggf. Anhänge hinzufügen
        if(count($mailingOrder->getAttachments()) > 0) {
            foreach($mailingOrder->getAttachments() as $attachment) {
                $mail->addStringAttachment(
                    base64_decode($attachment["content"]), //Den base64-String aus dem Request wieder decoden
                    $attachment["name"],                   //Dateiname setzen
                    PHPMailer::ENCODING_BASE64,            //So wird übertragen
                    $attachment["mime_type"]               //Der MIME-Type
                );
            }
        }

        //E-Mail-Inhalte
        $mail->Subject = $mailingOrder->getSubject();
        $mail->Body    = $mailingOrder->getBody();
        if($mailingOrder->getIsHtml() === true) {
            $mail->AltBody = $mailingOrder->getBody(); //Alternative Nachricht ist das rohe HTML (für Clients ohne HTML Unterstützung)
            $mail->isHTML(true); //E-Mail ist HTML
        }

        //Versenden der E-Mail
        $mail->send();

        return "E-Mail wurde verschickt";

    } catch (Exception $e) {
        return "E-Mail konnte nicht verschickt werden - PHPMailer Error: {$mail->ErrorInfo}";
    }
}