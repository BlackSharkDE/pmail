<?php
    //Schutz vor Direktzugriff
    if(!defined('PMAIL_SESSION')) {
        die('Direct access not permitted');
    }
?>
<h2>User aus der Datenbank (<?php echo($pmailUsersCount); ?>)</h2>

<a href="accounts.php?register" class="LINK_BUTTON">Neuen User anlegen</a>
<br><br>

<table class="APP__CONTAINER__TABLE">
    <tr>
        <th width="10%">userID</th>
        <th width="60%">hashedApiKey</th>
        <th width="20%">lastAccessTime (API)</th>
        <th width="20%">Aktionen</th>
    </tr>
    <?php
        if($pmailUsersCount > 0) {
            foreach($pmailUsers as $pmailUser) {
                echo("<tr>");
                echo("<td>" . $pmailUser->getUserID() . "</td>");
                echo("<td>" . $pmailUser->getHashedApiKey() . "</td>");
                echo("<td>" . $pmailUser->getLastAccessTimeFormatted() . "</td>");
                echo('<td>');
                echo('<br><a href="accounts.php?details=' . $pmailUser->getUserID() . '">DETAILS</a><br><br>');
                echo('<a href="accounts.php?delete=' . $pmailUser->getUserID() . '">LÖSCHEN</a><br><br>');
                echo('</td>');
                echo("</tr>\n");
            }
        }
    ?>
</table>