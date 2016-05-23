<?php

require_once './Components/Logger/logger.php';
require_once './Components/Database/db.php';
require_once './Components/Accounts/accountmanager.php';

function InitWebsite(){
    try
    {
        DB::Instance()->ConnectToDatabase();
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
