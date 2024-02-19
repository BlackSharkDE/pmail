<?php
    require "php/session.php";
    require "php/pagebuild.php";

    //Ob eine Warnung im Login-View angezeigt werden soll
    $showLoginWarning = false;

    //Ob der Logout-Hinweis angezeigt werden soll
    $showLogoutHint = false;

    //Prüfe, ob Logout ausgeführt werden soll
    if(SessionManager::isLoggedIn() && isset($_GET["logout"])) {
        //-- Logout --
        SessionManager::logoutUser();
        $showLogoutHint = true;
    } else {
        //-- Prüfe, ob Login ausgeführt werden soll --

        //Wenn Formular per Post Daten schickt UND noch kein User eingeloggt ist
        if((isset($_POST["login_username"]) && isset($_POST["login_password"])) && !SessionManager::isLoggedIn()) {
            
            //Überprüfe Datentypen (falsche werden ignoriert)
            if(is_string($_POST["login_username"]) && is_string($_POST["login_password"])) {
                $u = strval($_POST["login_username"]);
                $p = strval($_POST["login_password"]);

                //Überprüfe, ob Login fehlgeschlagen => Login-Fehlschlag anzeigen
                if(!SessionManager::loginUser($u,$p)) {
                    $showLoginWarning = true;
                }
            }
        }

        //Überprüfe, ob User jetzt eingeloggt ist => ggf. Weiterleitung
        if(SessionManager::isLoggedIn()) {
            //-- Zum Dashboard weiterleiten --
            header("Location: views/dashboard.php");
        }
    }

    PageBuild::outputHead("Index");
?>

    <div id="app">
        <div class="APP__CONTAINER APP__LOGIN">
            <h2>Anmelden</h2>
            <?php
                if($showLoginWarning && !$showLogoutHint) {
                    echo('<p class="APP__WARNING">User oder Passwort ist falsch!</p>');
                } else if($showLogoutHint && !$showLoginWarning) {
                    echo('<p class="APP__SUCCESS">Logout erfolgreich</p>');
                }
            ?>
            <form action="index.php" method="post">
                <input type="text" name="login_username" placeholder="Benutzername">
                <input type="password" name="login_password" placeholder="Passwort">
                <br>
                <input type="submit" value="Login">
            </form>
        </div>
    </div>

<?php
    PageBuild::outputFooter();
?>