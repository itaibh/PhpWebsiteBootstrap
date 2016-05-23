<?php
require_once __DIR__ . '/oauthmanager.php';

$provider = OAuthManager::Instance()->GetProvider('google');
$provider->HandleRequest();
?>
