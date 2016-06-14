<?php

require_once './Components/componentsmanager.php';
require_once './Components/Logger/init.php';

function InitWebsite(){
    $logger = new Logger(__FUNCTION__);
    try
    {
        $componentsManager = ComponentsManager::Instance();
        $componentsManager->Init(); // initializes all components
    }
    catch(Exception $e)
    {
        $logger->log_error($e);
    }
}

?>
