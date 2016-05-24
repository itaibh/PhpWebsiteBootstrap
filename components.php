<?php

require_once './Components/componentsmanager.php';
require_once './Components/Logger/init.php';

function InitWebsite(){
    $logger = new Logger(__FUNCTION__);
    try
    {
        $db = ComponentsManager::Instance()->GetComponent('Database');
    }
    catch(Exception $e)
    {
        $logger->log_error($e);
    }
}

?>
