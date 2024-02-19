<?php
    require "../php/session.php";
    require "../php/pagebuild.php";

    PageBuild::outputHead("Log anzeigen");

    //Benötige die Datenbankanbindung aus dem Backend
    require_once BACKEND_DIR . "database/connection.php";

    //Logs aus der Datenbank abfragen
    $logs = $dbCon->queryDB("SELECT * FROM pmail.logs");
    $logCount = count($logs);
?>

    <div id="app">

        <br>

        <div class="APP__CONTAINER APP__BIG">
            <h2>Anzahl der Logeinträge: <?php echo($logCount); ?></h2>

            <?php echo(($logCount< 0) ? "<p>Es wurden keine Logs gefunden!</p>" : ""); ?>

            <table class="APP__CONTAINER__TABLE APP__CONTAINER__TABLE__WITH_BG">
                <tr>
                    <th>timestamp</th>
                    <th>statusCode</th>
                    <th>value</th>
                    <th>postData</th>
                </tr>
                <?php
                    //Wenn Log-Einträge vorhanden sind
                    if($logCount > 0) {

                        //Tabellenzeilen ausgeben
                        foreach($logs as $logEntry) {
                            echo("<tr>");
                            echo("<td>" . $logEntry->timestamp . "</td>");
                            echo("<td>" . $logEntry->statusCode . "</td>");
                            echo("<td>" . $logEntry->value . "</td>");
                            echo("<td>" . nl2br(htmlspecialchars($logEntry->postdata)) . "</td>");
                            echo("</tr>\n");
                        }
                    }
                ?>
            </table>

        </div>

        <br>

    </div>

<?php
    PageBuild::outputFooter();
?>