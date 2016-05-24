<?php

require_once './Components/componentsmanager.php';
require_once './Components/Logger/init.php';

require_once './Components/OAuth2/Providers/google.php';

function InitWebsite(){
    $logger = new Logger(__FUNCTION__);
    try
    {
        $componentsManager = ComponentsManager::Instance();
        $componentsManager->Init(); // initializes all components

        $oauthManager = $componentsManager->GetComponent('OAuth2');
        $oauthManager->RegisterProvider(new GoogleOAuthProvider("","",""));
    }
    catch(Exception $e)
    {
        $logger->log_error($e);
    }
}

?>
