<?php
    include_once './settings.php';
    include_once './components.php';
    require_once './Components/componentsmanager.php';

    try {
        InitWebsite();

        ComponentsManager::Instance()->HandleRequest();
    } catch (Exception $ex) {
        include 'error.php';
        die;
    }
    include 'main.php';
?>
