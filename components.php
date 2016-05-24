<?php

require_once './Components/componentsmanager.php';
require_once './Components/Logger/logger.php';

function InitWebsite(){
    try
    {
        $db = ComponentsManager::Instance()->GetComponent('Database');
        $db->ConnectToDatabase();
    }
    catch(Exception $e)
    {
        InstallWebsite();
    }
}

function InstallWebsite(){
    $db = ComponentsManager::Instance()->GetComponent('Database');
    $db->CreateDatabase();
}

?>
