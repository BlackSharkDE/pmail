<?php
    //Schutz vor Direktzugriff
    if(!defined('PMAIL_SESSION')) {
        die('Direct access not permitted');
    }
?>
<h2>Speicherung der Änderungen des Users (<?php echo($details); ?>)</h2>
<?php

    //Benötige die "Edit"-Funktion aus dem Backend
    require_once BACKEND_DIR . "users/edit.php";

    //Edit ausführen
    $editSuccessful = editExistingUser($apiUser,$edit,$plainApiKey);

    if($editSuccessful) {
        echo('<p class="APP__SUCCESS">Änderungen wurden gespeichert.</p>');
        SessionManager::logAction("Änderungen der Verbindungsdaten des Users mit der ID '" . $apiUser->getUserID() . "' wurden gespeichert.");
    } else {
        echo('<p class="APP__WARNING">Änderungen konnten nicht gespeichert werden!</p>');
        SessionManager::logAction("Änderungen der Verbindungsdaten des Users mit der ID '" . $apiUser->getUserID() . "' konnten nicht gespeichert werden!");
    }

    echo('<br><a class="LINK_BUTTON" href="accounts.php">Zurück zur Übersicht</a>');
?>