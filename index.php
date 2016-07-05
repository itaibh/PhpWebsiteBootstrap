<?php
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    ini_set("error_log",__DIR__."/logs/error.log");

    define('ROOTPATH', __DIR__);

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
?>
