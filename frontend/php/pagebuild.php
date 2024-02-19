<?php
/**
 * Klasse zur Verwaltung des HTML-Outputs
 */

require_once "session.php";
require_once "options.php";

class PageBuild {

    /**
     * Gibt HTML auf Seite aus. (void - Funktion)
     * @param string Was ausgegeben werden soll
     */
    private static function printToPage(string $outputString) {

        //-- Simples entfernen der Einrückungen, die im Code entstehen --
        $outputString = str_replace("    ","",$outputString);

        //Ausgabe
        echo($outputString);
    }

    /**
     * Gibt alles an Standard-HTML bis zum Top-Menü aus. (void - Funktion)
     */
    public static function outputHead(string $pageName) {

        $head = sprintf(
        '
            <!DOCTYPE html>
            <html>
            <head>
                <title>%s</title>
                <meta charset="UTF-8">
                <link rel="icon" href="' . BASE_URL . 'res/pmail_with_color.png">

                <!-- CSS -->
                <link rel="stylesheet" href="' . BASE_URL . 'css/style.css">
                <link rel="stylesheet" href="' . BASE_URL . 'css/elements.css">
                <link rel="stylesheet" href="' . BASE_URL . 'css/input.css">
                <link rel="stylesheet" href="%s">

                <!-- JS -->
                <script type="text/javascript" src="' . BASE_URL . 'res/misc.js"></script>
            </head>
            <body>

                <div id="homebar">
                    %s
                    <a id="HOMEBAR_HOME" href="' . BASE_URL . 'index.php"><img src="' . BASE_URL . 'res/pmail_with_color.png"></a>
                    %s
                </div>
        ',
            //Titel in Tab
            $pageName,

            //Quelle von Font-Awesome
            FONT_AWESOME_SOURCE,

            //User-Name mitsamt Icon, wenn angemeldet
            ((SessionManager::isLoggedIn()) ? '<i id="HOMEBAR_USER"><i class="fa fa-user-circle">&nbsp;' . SessionManager::getLoggedInUsername() . '</i></i>' : ""),

            //Logout-Button, wenn angemeldet
            ((SessionManager::isLoggedIn()) ? '<a id="HOMEBAR_SIGNOUT" href="' . BASE_URL . 'index.php?logout"><i class="fa fa-sign-out"></i></a>' : "")
        );

        PageBuild::printToPage($head);
    }

    /**
     * Gibt den Rest der Seite aus (schließt <body>). (void - Funktion)
     */
    public static function outputFooter() {

        $footer = sprintf(
            '
                    <div id="footer">
                        <p>&copy; %s - All rights reserved</p>
                    </div>
                </body>
                </html>
            ',
            date("Y")
        );

        PageBuild::printToPage($footer);
    }
}