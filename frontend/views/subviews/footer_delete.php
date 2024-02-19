<?php
    //Schutz vor Direktzugriff
    if(!defined('PMAIL_SESSION')) {
        die('Direct access not permitted');
    }
?>
<h2>Löschung des Footers (<?php echo($delete); ?>)</h2>
<?php

    if(isset($_GET["deleteconfirmation"]) && strcmp($_GET["deleteconfirmation"],"yes") === 0) {

        //Löschung durchführen
        $deleteSuccessful = $dbCon->queryDBNoFetch("
        DELETE FROM `pmail`.`footer` WHERE `name`=?
        ",array(
            $delete,
        ));

        if($deleteSuccessful) {
            echo('<p class="APP__SUCCESS">Löschung wurde erfolgreich durchgeführt.</p>');
            SessionManager::logAction("Löschung des Footers mit dem Namen '" . $delete . "' wurde erfolgreich durchgeführt.");
        } else {
            echo('<p class="APP__WARNING">Löschung konnte nicht durchgeführt werden!</p>');
            SessionManager::logAction("Löschung des Footers mit dem Namen '" . $delete . "' konnte nicht durchgeführt werden.");
        }

        echo('<br><a class="LINK_BUTTON" href="footer.php">Zurück zur Übersicht</a>');
    } else {

        if(isset($_GET["deleteconfirmation"]) && strcmp($_GET["deleteconfirmation"],"no") === 0) {
            //-- Da bereits ein "deleteconfirmation" angegeben wurde, der Wert von selbigem "no" ist, auf Footer-Seite umleiten --
            header("Location: footer.php");
        } else {
            //-- Noch keine "deleteconfirmation" angegeben => Bestätigung anfordern --
            echo(
            '
                <p>Soll der Footer wirklich gelöscht werden?</p>
                <form action="footer.php" method="get">
                    <input type="text" value="' . $delete . '" name="delete" style="display:none;">
                    
                    <input type="radio" id="choice_no" name="deleteconfirmation" value="no" checked>
                    <label for="choice_no">Nein</label>
                    
                    <input type="radio" id="choice_yes" name="deleteconfirmation" value="yes">
                    <label for="choice_yes">Ja</label>

                    <br><br>
                    <input type="submit" value="Bestädigen">
                </form>
            '
            );
            //Versteckter Input "delete", damit der Footername wieder mitgeschickt wird
        }

    }
?>