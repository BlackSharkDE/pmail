<?php
    //Schutz vor Direktzugriff
    if(!defined('PMAIL_SESSION')) {
        die('Direct access not permitted');
    }

    //Werte für die Registrierung (SMTP- und IMAP-Daten)
    $registerValues = [];

    //Assoziatives Array mit Fehlermeldungen zu den einzelnen Registrierungsparametern
    $errorArray = [];

    //-- Sofern die Registrierungsparameter angegeben wurden, diese speichern --
    if(
        isset($_POST["register_smtpUser"]) && isset($_POST["register_smtpPassword"]) && isset($_POST["register_smtpServer"]) && isset($_POST["register_smtpPort"])
        &&
        isset($_POST["register_imapUser"]) && isset($_POST["register_imapPassword"]) && isset($_POST["register_imapServer"]) && isset($_POST["register_imapPort"])
    ) {
        $registerValues = [
           strval($_POST["register_smtpUser"]),strval($_POST["register_smtpPassword"]),strval($_POST["register_smtpServer"]),intval($_POST["register_smtpPort"]),
           strval($_POST["register_imapUser"]),strval($_POST["register_imapPassword"]),strval($_POST["register_imapServer"]),intval($_POST["register_imapPort"])
        ];
    }

    //================================================================================================================
    //-- Hilfsfunktionen --

    /**
     * Prüft, ob ein vom Formular übergeber User oder ein Passwort valide ist (nicht ob er richtig ist).
     * @param string String aus Formular-Übergabevariablen
     * @return bool  True, wenn ja / False, wenn nein
     */
    function checkUserOrPassword(string $formularString) {
        if(strlen($formularString) > 255 || strlen($formularString) <= 1) {
            return False;
        }
        return True;
    }

    /**
     * Prüft, ob eine vom Formular übergebene Server-Adresse valide ist (nicht ob sie richtig ist).
     * @param string String aus Formular-Übergabevariablen
     * @return bool  True, wenn ja / False, wenn nein
     */
    function checkServerAddress(string $serverAddress) {
        //Wenn die Server-Adresse weder IPv4 noch IPv6 ist UND >3 oder <255 Zeichen ist, ist sie ungültig
        if(
            (filter_var($serverAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) == False && filter_var($serverAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) == False)
            && 
            (strlen($serverAddress) < 3 || strlen($serverAddress) > 255)
        ) {
            return False;
        }
        return True;
    }

    /**
     * Prüft, ob ein vom Formular übergebener Port valide ist (nicht ob er richtig ist).
     * @param int   Port aus Formular-Übergabevariablen
     * @return bool True, wenn ja / False, wenn nein
     */
    function checkPort(int $port) {
        if(!is_numeric($port) || $port <= 0 || $port > 65535) {
            return False;
        }
        return True;
    }
    
    //================================================================================================================
    //-- Validierung der angegeben Werte --
    if(count($registerValues) === 8) {

        //Muss hier auf "true" gesetzt werden, da Grundvoraussetzung erfüllt ist und damit die weiteren Tests positiv bleiben können
        $readyToRegister = true;

        //"register_smtpUser"
        if(checkUserOrPassword($registerValues[0]) === False) {
            $readyToRegister = False;
            $errorArray["register_smtpUser"] = '<p class="APP__WARNING">SMTP-User ungültig!</p>';
        }

        //"register_smtpPassword"
        if(checkUserOrPassword($registerValues[1]) === False) {
            $readyToRegister = False;
            $errorArray["register_smtpPassword"] = '<p class="APP__WARNING">SMTP-User-Passwort ungültig!</p>';
        }

        //"register_smtpServer"
        if(checkServerAddress($registerValues[2]) === False) {
            $readyToRegister = False;
            $errorArray["register_smtpServer"] = '<p class="APP__WARNING">SMTP-Server Adresse ist ungültig,da weder gültige<br>IPv4 noch IPv6 noch gültige DNS-Adresse!</p>';
        }

        //"register_smtpPort"
        if(checkPort($registerValues[3]) === False) {
            $readyToRegister = False;
            $errorArray["register_smtpPort"] = '<p class="APP__WARNING">SMTP-Server Port ist kein gültiger,<br>numerischer Wert!</p>';
        }

        //"register_imapUser"
        if(checkUserOrPassword($registerValues[4]) === False) {
            $readyToRegister = False;
            $errorArray["register_imapUser"] = '<p class="APP__WARNING">IMAP-User ungültig!</p>';
        }

        //"register_imapPassword"
        if(checkUserOrPassword($registerValues[5]) === False) {
            $readyToRegister = False;
            $errorArray["register_imapPassword"] = '<p class="APP__WARNING">IMAP-User-Passwort ungültig!</p>';
        }

        //"register_imapServer"
        if(checkServerAddress($registerValues[6]) === False) {
            $readyToRegister = False;
            $errorArray["register_imapServer"] = '<p class="APP__WARNING">IMAP-Server Adresse ist ungültig,da weder gültige<br>IPv4 noch IPv6 noch gültige DNS-Adresse!</p>';
        }

        //"register_imapPort"
        if(checkPort($registerValues[7]) === False) {
            $readyToRegister = False;
            $errorArray["register_imapPort"] = '<p class="APP__WARNING">IMAP-Server Port ist kein gültiger,<br>numerischer Wert!</p>';
        }

        //Wenn alle Werte in Ordnung sind
        if($readyToRegister === True) {

            //Benötige die "Register"-Funktion aus dem Backend
            require_once BACKEND_DIR . "users/register.php";

            //Neues User-Objekt erstellen, welches für die Registrierung genutzt wird
            $userToRegister = new PmailUser();
            $userToRegister->setSmtpAttributes($registerValues[0],$registerValues[1],$registerValues[2],$registerValues[3]);
            $userToRegister->setImapAttributes($registerValues[4],$registerValues[5],$registerValues[6],$registerValues[7]);

            //Registrierung ausführen
            $registerSuccessful = registerNewUser($userToRegister);

            if(strlen($registerSuccessful) === 50) {
                echo('<p class="APP__SUCCESS">User wurde erfolgreich registriert.<br><br>API-Schlüssel: ' . $registerSuccessful . '</p>');
                echo("<p><b>Hinweis: Es gibt keine Möglichkeit diesen wiederherzustellen oder erneut abzufragen!</p></b>");
                SessionManager::logAction("User wurde erfolgreich registriert.");
            } else {
                echo('<p class="APP__WARNING">User konnte nicht registriert werden!</p>');
                SessionManager::logAction("User konnte nicht registriert werden!");
            }
    
            echo('<br><a class="LINK_BUTTON" href="accounts.php">Zurück zur Übersicht</a><br><br><hr>');
        }
    }
?>

<h2>Neuen User anlegen</h2>

<form action="accounts.php?register" method="post">
    <table style="margin: 0 auto;">
        <tr>
            <td width="30%">SMTP-User</td>
            <td>
                <?php echo($errorArray["register_smtpUser"] ?? "<br>"); ?>
                <input type="text" value="<?php echo($registerValues[0] ?? "") ?>" name="register_smtpUser">
            </td>
        </tr>
        <tr>
            <td>SMTP-User-Passwort</td>
            <td>
                <?php echo($errorArray["register_smtpPassword"] ?? "<br>"); ?>
                <input type="password" value="<?php echo($registerValues[1] ?? "") ?>" name="register_smtpPassword" id="show_password_smtp">
            </td>
        </tr>
        <tr>
            <td>SMTP-Server</td>
            <td>
                <?php echo($errorArray["register_smtpServer"] ?? "<br>"); ?>
                <input type="text" value="<?php echo($registerValues[2] ?? "") ?>" name="register_smtpServer">
            </td>
        </tr>
        <tr>
            <td>SMTP-Port</td>
            <td>
                <?php echo($errorArray["register_smtpPort"] ?? "<br>"); ?>
                <input type="text" value="<?php echo($registerValues[3] ?? "") ?>" name="register_smtpPort">
            </td>
        </tr>
        <tr>
            <td>IMAP-User</td>
            <td>
                <?php echo($errorArray["register_imapUser"] ?? "<br>"); ?>
                <input type="text" value="<?php echo($registerValues[4] ?? "") ?>" name="register_imapUser">
            </td>
        </tr>
        <tr>
            <td>IMAP-User-Passwort</td>
            <td>
                <?php echo($errorArray["register_imapPassword"] ?? "<br>"); ?>
                <input type="password" value="<?php echo($registerValues[5] ?? "") ?>" name="register_imapPassword" id="show_password_imap">
            </td>
        </tr>
        <tr>
            <td>IMAP-Server</td>
            <td>
                <?php echo($errorArray["register_imapServer"] ?? "<br>"); ?>
                <input type="text" value="<?php echo($registerValues[6] ?? "") ?>" name="register_imapServer">
            </td>
        </tr>
        <tr>
            <td>IMAP-Port</td>
            <td>
                <?php echo($errorArray["register_imapPort"] ?? "<br>"); ?>
                <input type="text" value="<?php echo($registerValues[7] ?? "") ?>" name="register_imapPort">
            </td>
        </tr>
    </table>

    <br>
    <input type="submit" value="Neuen User erstellen">
</form>

<br><a class="LINK_BUTTON" href="accounts.php">Zurück zur Übersicht</a>

<script type="text/javascript">
    addShowPassword("show_password_smtp");
    addShowPassword("show_password_imap");
</script>