<?php

require_once './Components/componentsmanager.php';
require_once './Components/Logger/logger.php';
require_once './Components/Database/db.php';
require_once './Components/Accounts/accountmanager.php';

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
    DB::Instance()->CreateDatabase();
}

?>
