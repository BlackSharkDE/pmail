<?php
    //Schutz vor Direktzugriff
    if(!defined('PMAIL_SESSION')) {
        die('Direct access not permitted');
    }
?>
<h2>Footer verwalten</h2>

<a href="footer.php?edit&new" class="LINK_BUTTON">Neuen Footer anlegen</a>
<br>

<br>
<hr class="APP_DIVIDER">

<h3>Alle HTML-Footer aus der Datenbank (<?php echo($htmlFooterCount); ?>)</h3>

<table class="APP__CONTAINER__TABLE" style="text-align: initial;">
    <tr>
        <th width="15%">Name</th>
        <th width="70%">Content</th>
        <th width="10%">Aktionen</th>
    </tr>
    <?php
        if($htmlFooterCount > 0) {
            foreach($htmlFooter as $aFooterName => $aHtmlFooter) {
                echo("<tr>");
                echo("<td>" . $aFooterName . "</td>");
                echo("<td>" . $aHtmlFooter["content"] . "</td>"); //HTML kann direkt ausgegeben werden
                echo('<td style="text-align: center;">');
                echo('<br><a href="footer.php?edit=' . urlencode($aFooterName) . '">BEARBEITEN</a><br><br>');
                echo('<a href="footer.php?delete=' . urlencode($aFooterName) . '">LÖSCHEN</a><br><br>');
                echo('</td>');
                echo("</tr>\n");
            }
        }
    ?>
</table>

<br>
<hr class="APP_DIVIDER">

<h3>Alle Nicht-HTML-Footer aus der Datenbank (<?php echo($nonHtmlFooterCount); ?>)</h3>

<table class="APP__CONTAINER__TABLE" style="text-align: initial;">
    <tr>
        <th width="15%">Name</th>
        <th width="70%">Content</th>
        <th width="10%">Aktionen</th>
    </tr>
    <?php
        if($nonHtmlFooterCount > 0) {
            foreach($nonHtmlFooter as $aFooterName => $aNonHtmlFooter) {
                echo("<tr>");
                echo("<td>" . $aFooterName . "</td>");
                echo("<td>" . nl2br($aNonHtmlFooter["content"]) . "</td>"); //Da es sich um Plain-Text handelt, müssen New-Lines umgewandelt werden
                echo('<td style="text-align: center;">');
                echo('<br><a href="footer.php?edit=' . urlencode($aFooterName) . '">BEARBEITEN</a><br><br>');
                echo('<a href="footer.php?delete=' . urlencode($aFooterName) . '">LÖSCHEN</a><br><br>');
                echo('</td>');
                echo("</tr>\n");
            }
        }
    ?>
</table>

<br>
<hr class="APP_DIVIDER">