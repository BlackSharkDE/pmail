<?php
    require "../php/session.php";
    require "../php/pagebuild.php";

    PageBuild::outputHead("Accounts verwalten");

    //Benötige die User-Klassen aus dem Backend
    require_once BACKEND_DIR . "users/user.php";
    
    //Benötige die "Auth"-Funktion aus dem Backend
    require_once BACKEND_DIR . "users/auth.php";

    //-- User aus der Datenbank abfragen --
    $pmailUsers      = PMailUsers::getUsers();
    $pmailUsersCount = count($pmailUsers);

    //-- Speichern der Standard-GET-Parameter --
    $details  = $_GET["details"] ?? "";   //User-ID als GET-Parameter
    $edit     = array();
    $delete   = $_GET["delete"] ?? "";    //User-ID als GET-Parameter
    $register = isset($_GET["register"]); //Boolscher Parameter

    //-- Sofern ein Plain-Api-Key angegeben wurde, versuche den User zu authentifizieren --
    $plainApiKey = $_GET["details_apikey"] ?? "";
    $apiUser     = NULL; //Kann nur NULL oder ein valider PMail-User sein
    if(is_string($plainApiKey) && strlen($plainApiKey) > 0) {

        //Versuche User zu authentifizieren
        $user = authenticateUser($plainApiKey);

        //Wenn User authentifiziert wurde UND der ist, der angegeben wurde (verhindere falschen, aber valider Key-Eingabe)
        if($user->isValidUser() && strcmp($user->getUserID(),$details) === 0) {
            $apiUser = $user;
        }
    }

    //-- Sofern die Editierungsparameter angegeben wurden, diese speichern --
    if(
        isset($_POST["edit_smtpUser"]) && isset($_POST["edit_smtpPassword"]) && isset($_POST["edit_smtpServer"]) && isset($_POST["edit_smtpPort"])
        &&
        isset($_POST["edit_imapUser"]) && isset($_POST["edit_imapPassword"]) && isset($_POST["edit_imapServer"]) && isset($_POST["edit_imapPort"])
    ) {
        $edit = [
           strval($_POST["edit_smtpUser"]),strval($_POST["edit_smtpPassword"]),strval($_POST["edit_smtpServer"]),intval($_POST["edit_smtpPort"]),
           strval($_POST["edit_imapUser"]),strval($_POST["edit_imapPassword"]),strval($_POST["edit_imapServer"]),intval($_POST["edit_imapPort"])
        ];
    }

    /**
     * Prüft, ob eine User-ID-Angabe valide ist
     * @param string Ein String, der als User-ID überprüft werden soll
     * @return bool  True, wenn ja / False, wenn nein
     */
    function validUserId(string $givenUserId) {
        return (
            (is_string($givenUserId) && strlen($givenUserId) === 10) //Muss passender String sein
            && PMailUsers::userIDExists($givenUserId) //Muss als ID existieren
        );
    }
?>

    <div id="app">

        <br>

        <div class="APP__CONTAINER APP__BIG">

        <?php
            if(validUserId($details) && count($edit) === 0 && strcmp($delete,"") === 0 && !$register) {
                //-- Bei valider User-Id UND keinem Edit UND keinem Delete --
                require "subviews/accounts_details.php";
            } else if(!is_null($apiUser) && count($edit) === 8 && strcmp($delete,"") === 0 && !$register) {
                //-- Bei authentifiziertem API-User UND dem passenden $edit-Array UND keinem Delete
                require "subviews/accounts_saveedit.php";
            } else if(strcmp($details,"") === 0 && count($edit) === 0 && validUserId($delete) && !$register) {
                require "subviews/accounts_delete.php";
            } else if(strcmp($details,"") === 0 && count($edit) === 0 && strcmp($delete,"") === 0 && $register) {
                require "subviews/accounts_register.php";
            } else {
                require "subviews/accounts_all.php";
            }
        ?>

        </div>

        <br>

    </div>

<?php
    PageBuild::outputFooter();
?>