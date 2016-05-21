<?php

require_once './Components/Logger/logger.php';
require_once './Components/Database/db.php';
require_once './Components/Accounts/accountmanager.php';

function InitWebsite(){
    try
    {
        DB::GetInstance()->ConnectToDatabase();
    }
    catch(Exception $e)
    {
        InstallWebsite();
    }
}

function InstallWebsite(){
    DB::GetInstance()->CreateDatabase();
}

?>
