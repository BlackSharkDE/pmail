<?php
    require "../php/session.php";
    require "../php/pagebuild.php";

    PageBuild::outputHead("Preview der Footer");

    //Benötige die Datenbankanbindung aus dem Backend
    require_once BACKEND_DIR . "database/connection.php";

    //-- Footer aus der Datenbank abfragen --
    
    /**
     * Erstellt ein assoziatives Array mit den Footer-Namen als Keys
     * @return array Ein assoziatives Array
     */
    function deserializeFooters($footerObjs) {
        $arr = array();

        foreach($footerObjs as $footerObj) {
            $arr[$footerObj->name] = [
                "ishtml"  => $footerObj->ishtml,
                "content" => $footerObj->content
            ];
        }

        return $arr;
    }

    $htmlFooter      = deserializeFooters($dbCon->queryDB("SELECT * FROM pmail.footer WHERE `ishtml` = 1"));
    $htmlFooterCount = count($htmlFooter);

    $nonHtmlFooter      = deserializeFooters($dbCon->queryDB("SELECT * FROM pmail.footer WHERE `ishtml` = 0"));
    $nonHtmlFooterCount = count($nonHtmlFooter);

    $allFooter = array_merge($htmlFooter,$nonHtmlFooter);
    $allFooterNames = array_keys($allFooter);

    $edit   = $_GET["edit"] ?? NULL; //Name eines Footers als GET-Parameter
    $delete = $_GET["delete"] ?? ""; //Name eines Footers als GET-Parameter
?>

    <div id="app">

        <br>

        <div class="APP__CONTAINER APP__BIG">

        <?php
            if(!is_null($edit) && strcmp($delete,"") === 0) {
                //-- Wenn Footer-Name angegeben UND kein Delte => Editor --
                require "subviews/footer_edit.php";
            } else if(is_null($edit) && strlen($delete) > 0) {
                //-- Wenn kein Footer-Name angegeben UND Delete angegen => Löschen --
                require "subviews/footer_delete.php";
            } else {
                //-- Alle Footer anzeigen --
                require "subviews/footer_preview.php";
            }
        ?>

        </div>

        <br>

    </div>

<?php
    PageBuild::outputFooter();
?>