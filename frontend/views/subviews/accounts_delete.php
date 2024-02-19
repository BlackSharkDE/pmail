<?php
    //Schutz vor Direktzugriff
    if(!defined('PMAIL_SESSION')) {
        die('Direct access not permitted');
    }
?>
<h2>Löschung des Users (<?php echo($delete); ?>)</h2>
<?php

    if(isset($_GET["deleteconfirmation"]) && strcmp($_GET["deleteconfirmation"],"yes") === 0) {

        //Löschung durchführen
        $deleteSuccessful = PMailUsers::deleteUser($delete);

        if($deleteSuccessful) {
            echo('<p class="APP__SUCCESS">Löschung wurde erfolgreich durchgeführt.</p>');
            SessionManager::logAction("Löschung des Users mit der ID '" . $delete . "' wurde erfolgreich durchgeführt.");
        } else {
            echo('<p class="APP__WARNING">Löschung konnte nicht durchgeführt werden!</p>');
            SessionManager::logAction("Löschung des Users mit der ID '" . $delete . "' konnte nicht durchgeführt werden.");
        }

        echo('<br><a class="LINK_BUTTON" href="accounts.php">Zurück zur Übersicht</a>');
    } else {

        if(isset($_GET["deleteconfirmation"]) && strcmp($_GET["deleteconfirmation"],"no") === 0) {
            //-- Da bereits ein "deleteconfirmation" angegeben wurde, der Wert von selbigem "no" ist, auf Accounts-Seite umleiten --
            header("Location: accounts.php");
        } else {
            //-- Noch keine "deleteconfirmation" angegeben => Bestätigung anfordern --
            echo(
            '
                <p>Soll der User wirklich gelöscht werden?</p>
                <form action="accounts.php" method="get">
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
            //Versteckter Input "delete", damit die User-ID wieder mitgeschickt wird
        }

    }
?>