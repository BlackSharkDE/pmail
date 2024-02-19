<?php
    //Schutz vor Direktzugriff
    if(!defined('PMAIL_SESSION')) {
        die('Direct access not permitted');
    }
?>
<h2>Details des Users (<?php echo($details); ?>)</h2>
<?php
    if(!is_null($apiUser)) {

        $smtpData = $user->getSmtpAttributes();
        $imapData = $user->getImapAttributes();

        echo(
        '
            <p class="APP__SUCCESS">User konnte erfolgreich authentifiziert werden.</p>
            <form action="accounts.php?details=' . $details . '&details_apikey=' . $plainApiKey . '" method="post">

                <table style="margin: 0 auto;">
                    <tr>
                        <td width="30%">SMTP-User</td>
                        <td><br><input type="text" value="' . $smtpData[0] . '" name="edit_smtpUser"></td>
                    </tr>
                    <tr>
                        <td>SMTP-User-Passwort</td>
                        <td><br><input type="password" value="' . $smtpData[1] . '" name="edit_smtpPassword" id="show_password_smtp"></td>
                    </tr>
                    <tr>
                        <td>SMTP-Server</td>
                        <td><br><input type="text" value="' . $smtpData[2] . '" name="edit_smtpServer"></td>
                    </tr>
                    <tr>
                        <td>SMTP-Port</td>
                        <td><br><input type="text" value="' . $smtpData[3] . '" name="edit_smtpPort"></td>
                    </tr>
                    <tr>
                        <td>IMAP-User</td>
                        <td><br><input type="text" value="' . $imapData[0] . '" name="edit_imapUser"></td>
                    </tr>
                    <tr>
                        <td>IMAP-User-Passwort</td>
                        <td><br><input type="password" value="' . $imapData[1] . '" name="edit_imapPassword" id="show_password_imap"></td>
                    </tr>
                    <tr>
                        <td>IMAP-Server</td>
                        <td><br><input type="text" value="' . $imapData[2] . '" name="edit_imapServer"></td>
                    </tr>
                    <tr>
                        <td>IMAP-Port</td>
                        <td><br><input type="text" value="' . $imapData[3] . '" name="edit_imapPort"></td>
                    </tr>
                </table>

                <br>
                <input type="submit" value="Speichern">
            </form>
            <br><a class="LINK_BUTTON" href="accounts.php">Zurück zur Übersicht</a>

            <script type="text/javascript">
                addShowPassword("show_password_smtp");
                addShowPassword("show_password_imap");
            </script>
        '
        );

    } else {

        if(strlen($plainApiKey) > 0) {
            //-- Da bereits ein API-Key angegeben wurde, und vorherigen IF nicht zutraf, ist die Authentifizierung des Users fehlgeschlagen --
            echo('<p class="APP__WARNING">User konnte nicht authentifiziert werden!</p>');
            echo('<br><a class="LINK_BUTTON" href="accounts.php">Zurück zur Übersicht</a>');
            SessionManager::logAction("User mit der ID '" . $details . "' konnte nicht authentifiziert werden!");
        } else {
            //-- Noch kein API-Key angegeben => Aufforderung zur Eingabe --
            echo(
            '
                <p>Bitte den kompletten API-Schlüssel für den User angeben:</p>
                <form action="accounts.php" method="get">
                    <input type="text" value="' . $details . '" name="details" style="display:none;">
                    <input type="text" placeholder="API-Schlüssel" name="details_apikey">
                    <br>
                    <input type="submit" value="Authentifizieren">
                </form>
            '
            );
            //Versteckter Input "details", damit die User-ID wieder mitgeschickt wird
        }

    }
?>