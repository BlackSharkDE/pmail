<?php
    //Schutz vor Direktzugriff
    if(!defined('PMAIL_SESSION')) {
        die('Direct access not permitted');
    }

    //Pfüfe, ob ein neuer Footer erstellt werden soll
    $new = $_GET["new"] ?? NULL; //Wenn NULL, dann wird bestehender bearbeitet, wenn nicht NULL, dann wird neuer erstellt

    /**
     * Gibt zurück, ob gerade ein neuer Footer erstellt wird
     * @return bool True, wenn ja / False, wenn nein
     */
    function editNewFooter() {
        global $new;
        return !is_null($new);
    }

    //Werte einer Bearbeitung
    $editValues = array();

    /**
     * Setzt Editierungsparameter standardisiert in das "editValues"-Array. (void - Funktion)
     */
    function setEditValues(bool $ishtml, string $name, string $content) {
        global $editValues;
        $editValues = [
            $ishtml,$name,$content
        ];
    }

    //Prüfe, ob die Daten über das Formular über POST geschickt wurden (True wenn ja / False, wenn nein)
    $postDataSet = isset($_POST["footer_ishtml"]) && isset($_POST["footer_name"]) && isset($_POST["footer_content"]);

    //-- Je nachdem, ob neuer oder bestehender Footer, Vorbereitungen treffen --
    if(editNewFooter()) {
        //-- Neuen erstellen --
        echo("<h2>Neuen Footer erstellen</h2>");
    } else {
        //-- Bestehenden editieren --

        if(!in_array($edit,$allFooterNames)) {
            //-- Footername gibt es nicht => Automatische Weiterleitung zur Übersichtsseite --
            header("Location: footer.php");
        } else {
            echo('<h2>Footer anpassen</h2>');

            if(!$postDataSet) {
                //Werte des bestehenden Footers laden, damit diese automatisch im Formular ausgegeben werden
                setEditValues($allFooter[$edit]["ishtml"],$edit,$allFooter[$edit]["content"]);
            }
        }
    }

    //-- Sofern die Editierungsparameter über das Formular angegeben wurden, diese speichern --
    if($postDataSet) {
        setEditValues(
            boolval($_POST["footer_ishtml"]),strval($_POST["footer_name"]),strval($_POST["footer_content"])
        );
    }

    //Assoziatives Array mit Fehlermeldungen zu den einzelnen Registrierungsparametern
    $errorArray = [];

    //-- Validierung der angegeben Werte --
    if(count($editValues) === 3 && $postDataSet) {

        //Muss hier auf "true" gesetzt werden, da Grundvoraussetzung erfüllt ist und damit die weiteren Tests positiv bleiben können
        $readyToSave = true;

        //"footer_name"
        if(strlen($editValues[1]) < 1) {
            $readyToSave = False;
            $errorArray["footer_name"] = '<p class="APP__WARNING">Name des Footers wurde nicht angegeben</p>';
        } else if(strlen($editValues[1]) > 255) {
            $readyToSave = False;
            $errorArray["footer_name"] = '<p class="APP__WARNING">Name des Footers ist zu lang!</p>';
        } else if(in_array($editValues[1],$allFooterNames) && editNewFooter()) {
            $readyToSave = False;
            $errorArray["footer_name"] = '<p class="APP__WARNING">Name des Footers ist bereits vergeben!</p>';
        } else if(preg_match("/[a-z]/i",$editValues[1]) === 0) {
            $readyToSave = False;
            $errorArray["footer_name"] = '<p class="APP__WARNING">Name des Footers muss Groß- oder Kleinbuchstaben enthalten!</p>';
        }

        //"footer_content"
        if(strlen($editValues[2]) < 1) {
            $readyToSave = False;
            $errorArray["footer_content"] = '<p class="APP__WARNING">Kein Footerinhalt angegeben</p>';
        } else if(strlen($editValues[2]) > 65535) {
            $readyToSave = False;
            $errorArray["footer_content"] = '<p class="APP__WARNING">Footerinhalt ist zu lang!</p>';
        }

        //Wenn alle Werte in Ordnung sind
        if($readyToSave === True) {

            global $dbCon;

            $dbRes = False;

            if(editNewFooter()) {
                //-- Neuen Footer speichern --

                $dbRes = $dbCon->queryDBNoFetch("
                INSERT INTO `pmail`.`footer` VALUES (?,?,?)
                ",array(
                    $editValues[1],
                    $editValues[0],
                    $editValues[2]
                ));

            } else {
                //-- Bestehenden ändern --

                $dbRes = $dbCon->queryDBNoFetch("
                UPDATE `pmail`.`footer` SET `ishtml`=?, `content`=? WHERE `name`=?
                ",array(
                    $editValues[0],
                    $editValues[2],
                    $edit
                ));
            }

            if($dbRes) {
                echo('<p class="APP__SUCCESS">Footer gespeichert.</p>');
                SessionManager::logAction("Footer mit dem Namen '" . $editValues[1] . "' gespeichert.");
            } else {
                echo('<p class="APP__WARNING">Footer konnte nicht gespeichert werden!</p>');
                SessionManager::logAction("Footer mit dem Namen '" . $editValues[1] . "' konnte nicht gespeichert werden!");
            }
        }
    }
?>

<form action="<?php echo((editNewFooter()) ? "footer.php?edit&new" : "footer.php?edit=" . urlencode($edit)) ?>" method="post">
    <p>Beinhaltet der Footer HTML?</p>
    <input type="radio" id="ishtml_no" name="footer_ishtml" value="0" <?php echo(isset($editValues[0]) && $editValues[0] === true ? "" : "checked"); ?>>
    <label for="ishtml_no">Nein</label>

    <input type="radio" id="ishtml_yes" name="footer_ishtml" value="1" <?php echo(isset($editValues[0]) && $editValues[0] === true ? "checked" : ""); ?>>
    <label for="ishtml_yes">Ja</label>

    <br><br>

    <?php echo($errorArray["footer_name"] ?? ""); ?>
    <label for="footer_name">Name des Footers:</label>
    <input type="text" value="<?php echo($editValues[1] ?? "") ?>" name="footer_name"
    <?php
        //Verhindere Namensänderung, wenn bestehender Footer bearbeitet wird
        echo((editNewFooter()) ? "" : "readonly");
    ?>>

    <br>

    <?php echo($errorArray["footer_content"] ?? ""); ?>
    <textarea name="footer_content" cols="100" rows="20" placeholder="Footer-Inhalt"><?php echo($editValues[2] ?? ""); ?></textarea>

    <br><br>
    <input type="submit" value="Footer speichern">
</form>

<?php
    echo('<br><a class="LINK_BUTTON" href="footer.php">Zurück zur Übersicht</a>');
?>