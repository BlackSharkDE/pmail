<?php
    require "../php/session.php";
    require "../php/pagebuild.php";

    PageBuild::outputHead("Dashboard");
?>

    <div id="app">

        <br>

        <div class="APP__CONTAINER APP__BIG">
            <h2>Dashboard</h2>

            <a class="APP_CONTAINER__LIST_ITEM" href="<?php echo(BASE_URL . "views/log.php"); ?>">
                <br>
                <i class="fa fa-list-alt"></i><br>Log<br>anzeigen
            </a>

            <a class="APP_CONTAINER__LIST_ITEM" href="<?php echo(BASE_URL . "views/accounts.php"); ?>">
                <br>    
                <i class="fa fa-user"></i><br>Accounts<br>verwalten
            </a>

            <a class="APP_CONTAINER__LIST_ITEM" href="<?php echo(BASE_URL . "views/footer.php"); ?>">
                <br>
                <i class="fa fa-envelope-o"></i><br>Footer<br>verwalten
            </a>
        </div>

        <br>

    </div>

<?php
    PageBuild::outputFooter();
?>