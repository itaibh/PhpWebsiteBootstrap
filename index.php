<?php
    include_once('./settings.php');
    include_once('./components.php')
    require_once './Components/componentsmanager.php';

    InitWebsite();

    ComponentsManager::Instance()->HandleRequest();
?>
<h1><?= constant('website_title') ?></h1>
