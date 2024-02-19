<?php
/**
 * Funktionen in Bezug auf das Anlysieren einer bestimmten Nachricht.
 */

//================================================================================================================
//-- Funktionen dieses Moduls --

ReadingOrder::addToAvailableMethods(
    [
        "getMessageBody"      => [0,1],
        "getTransmissionDate" => [0,1],
        "getSubject"          => [0,1],
        "getReplyTo"          => [0,1],
        "getReplyToUnmasked"  => [0,1],
        "getAttachments"      => [0,1]
    ]
);

//================================================================================================================
//-- Hilfsfunktion => Nicht über API erreichbar! --

/** 
 * Gibt den IMAP-Header einer Nachricht zurück.
 * @param IMAP\Connection Ein IMAP-Stream
 * @param int             Nummer der Nachricht in dem IMAP-Stream 
 * @return stdClass/false Bei Erfolg: Ein Objekt (stdClass) / Im Fehlerfall: False
 */
function getHeaderInfo($imapStream,int $messageNumber) {
    return imap_headerinfo($imapStream,$messageNumber);
}

//================================================================================================================

/**
 * Gibt den Nachrichtenbody aus.
 * @param IMAP\Connection IMAP-Stream
 * @param int             Nummer der Nachricht im Postfach
 * @return string         Body der Nachricht / False bei Fehler
 */
function getMessageBody($imapStream,int $messageNumber) {
    return imap_body($imapStream,$messageNumber);
}

/**
 * Gibt das Sendedatum laut Headerdaten zurück.
 * 
 * Parameter und Rückgabe: Siehe "getHeaderInfo"
 */
function getTransmissionDate($imapStream,int $messageNumber) {
    $headerInfo = getHeaderInfo($imapStream,$messageNumber);
    if($headerInfo != False) {
        return $headerInfo->date;
    }
    return $headerInfo;
}

/**
 * Gibt den Betreff einer Nachricht zurück.
 * 
 * Parameter und Rückgabe: Siehe "getHeaderInfo"
 */
function getSubject($imapStream,int $messageNumber) {
    $headerInfo = getHeaderInfo($imapStream,$messageNumber);
    if($headerInfo != False) {
        return $headerInfo->subject;
    }
    return $headerInfo;
}

/**
 * Gibt die Adresse aus, an die Antworten geschickt werden sollen. HINWEIS: Dies kann durchaus eine maskierte Adresse sein!
 * 
 * Parameter und Rückgabe: Siehe "getHeaderInfo"
 */
function getReplyTo($imapStream,int $messageNumber) {
    $headerInfo = getHeaderInfo($imapStream,$messageNumber);
    if($headerInfo != False) {
        return $headerInfo->reply_toaddress;
    }
    return $headerInfo;
}

/**
 * Gleich wie "getReplyTo", diese Adresse ist aber definitiv unmaskiert.
 * 
 * Parameter und Rückgabe: Siehe "getHeaderInfo"
 */
function getReplyToUnmasked($imapStream,int $messageNumber) {
    $headerInfo = getHeaderInfo($imapStream,$messageNumber);
    if($headerInfo != False) {
        $replyTo = $headerInfo->reply_toaddress;

        //-- Demaskieren (sofern maskiert) --
        $posStart = strpos($replyTo,'<'); //Suche nach Maskierungssymbol <
        $posEnd = strpos($replyTo,'>'); //Suche nach Maskierungssymbol >

        //Wenn die Sonderzeichen gefunden wurden (werden eigentlich nur bei / zur Maskierung zugelassen)
        if($posStart !== False && $posEnd !== False) {
            $replyTo = substr($replyTo,($posStart + 1),(($posEnd - 1) - $posStart)); //+1 / -1 um die Zeichen selbst nicht im String zu haben
        }

        return $replyTo;
    }
    return $headerInfo;
}

/**
 * Gibt die Anhänge (genau wie im "sendmail"-Modus) als Base64-Kodierter String aus mitsamt Dateiname und MIME-Type
 * @param IMAP\Connection Ein IMAP-Stream
 * @param int             Nummer der Nachricht in dem IMAP-Stream
 * @return array/string   Abhängig vom Erfolg:
 *                        -> Erfolg: Assoziatives Array mit den Anhängen: [["name" => "Dateiname", "content" => "__base64-String__", "mime_type" => "a/b"] ...]
 *                        -> Fehlschlag: String mit Fehlermeldung
 */
function getAttachments($imapStream,int $messageNumber) {

    //============================================================================================================
    //-- DEBUG --

    /**
     * Gibt ein Attachment in eine Datei aus. (void - Funktion)
     * @param string Der Anhang als Base64-String
     * @param string Dateiname mit Dateiendung
     */
    function debug_writeFileToDisk(string $base64AttachmentData,string $filenameToSaveTo) {
        
        //Ausgabepfad der Datei
        $outputDir  = "C:/Users/XYZ/Desktop"; //Verzeichnis für die Attachments => Ohne "/" am Ende!
        $outputPath = $outputDir . "/" . $filenameToSaveTo;
        
        //Datei ausgeben
        $filePointer = fopen($outputPath, "w+");
        fwrite($filePointer,base64_decode($base64AttachmentData));
        fclose($filePointer);

        echo("Saved attachment to: " . $outputPath . "<br>\n");
    }

    //============================================================================================================

    //Struktur der Mail (Rückgabe = stdClass)
    $fetch = imap_fetchstructure($imapStream,$messageNumber);

    //Wenn kein Fehler beim Fetch passiert ist
    if($fetch !== false) {

        //Kategorisierte Parts
        $attachments = [];

        //Wenn keine "Parts" vorhanden (keine Multi-Part-Mail), können auch keine Anhänge vorhanden sein
        if(property_exists($fetch,"parts") && count($fetch->parts) > 0) {

            //Gehe jeden "Part" der E-Mail durch und suche nach Dateien
            for($i = 0; $i < count($fetch->parts); $i++) {

                //Aktueller Part aus der Mail
                $fetchPart = $fetch->parts[$i];

                //Aktueller Status über den Part
                $fetchedPart =  [
                    'isAttachment' => false, #Ob Part ein Attachment ist
                    'name'         => '',    #Der Dateiname (wenn Attachment ; sollte Dateiendung beinhalten)
                    'content'      => ''     #Inhalt des Attachments als Base64-String
                ];

                //-- Prüfe verschiedene Parametergruppen ab, da verschiedene Provider/E-Mail-Clients teilweise verschiedene nutzen) --

                //Wenn Mail "dparameters" hat
                if($fetchPart->ifdparameters) {

                    //Gehe jeden "dparameter" ab (wenn "filename" angegeben (kann nur einmal angegeben werden), ist es ein Anhang)
                    foreach($fetchPart->dparameters as $object) {
                        if(strcmp(strtolower($object->attribute),'filename') === 0) {
                            $fetchedPart['isAttachment'] = true;
                            $fetchedPart['name']         = $object->value; //Nutze kein Extra-"filename"-Attribut, sondern "name"
                        }
                    }
                }

                //Wenn Mail "parameter" hat
                if($fetchPart->ifparameters) {
                    
                    //Gehe jeden "parameter" ab (wenn "name" angegeben (kann nur einmal angegeben werden), ist es ein Anhang)
                    foreach($fetchPart->parameters as $object) {
                        if(strtolower($object->attribute) == 'name') {
                            $fetchedPart['isAttachment'] = true;
                            $fetchedPart['name']         = $object->value;
                        }
                    }
                }

                //-- Wenn aktueller Part ein Attachment ist --
                if($fetchedPart['isAttachment']) {

                    //Attachment vom Server holen (Rohdaten)
                    $attachmentData = imap_fetchbody($imapStream,$messageNumber,$i+1);

                    //Je nach encoding, decoden und in Base64 encoden
                    switch($fetchPart->encoding) {
                        case 0:
                            //7bit => Sollte nicht passieren (gilt eigentlich nur für Text, nicht Daten direkt)
                            $fetchedPart['content'] = $attachmentData; //Gebe Daten so mit (wenn man damit etwas anfangen kann)
                            break;
                        case 1:
                            //8bit => Dekodiert hier direkt nach Base64
                            $fetchedPart['content'] = imap_binary($attachmentData);
                            break;
                        case 2:
                            //Binary => Kann direkt in Base64 gespeichert werden
                            $fetchedPart['content'] = base64_encode($attachmentData);
                            break;
                        case 3:
                            //Base64 => einfach speichern
                            $fetchedPart['content'] = $attachmentData;
                            break;
                        case 4:
                            //Quoted-Printable => Nach Base64 umwandeln
                            $decoded = quoted_printable_decode($attachmentData);
                            $fetchedPart['content'] = base64_encode($decoded);
                            break;
                        case 5:
                            //OTHER => Sollte nicht passieren
                            $fetchedPart['content'] = $attachmentData; //Gebe Daten so mit (wenn man damit etwas anfangen kann)
                            break;

                    }

                    //DEBUG
                    //debug_writeFileToDisk($fetchedPart['content'],$fetchedPart['name']);

                    //Da es sich um ein Attachment handelt => speichern
                    array_push($attachments,$fetchedPart);
                }
            }

            //Letzte Arbeiten für Rückgabe
            array_walk($attachments,function(&$x) {
                //Alle "isAttachment"-Einträge entfernen (wird nicht mehr benötigt)
                unset($x["isAttachment"]);
                
                //Finde den MIME-Type des Attachments heraus und füge ihn an das aktuelle Attachment an
                $f = finfo_open();
                $x["mime_type"] = finfo_buffer($f,base64_decode($x["content"]), FILEINFO_MIME_TYPE);
                finfo_close($f);
            });

            return $attachments;

        } else {
            //-- Keine Anhänge --
            return "E-Mail hat keine Anhaenge!";
        }
    }

    return "Problem beim Fetchen der Mail-Struktur!";
}