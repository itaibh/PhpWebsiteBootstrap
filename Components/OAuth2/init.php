<?php
require_once __DIR__.'/../componentsmanager.php';
require_once __DIR__.'/../Logger/init.php';
require_once __DIR__.'/ioauthprovider.php';
require_once __DIR__.'/oauthuserdata.php';

class OAuth2 extends ControllerComponentBase implements IOAuth2
{
    private $providers = array();
    private $db;
    private $accountManager;

    protected function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    public function GetRouteName() {
        return "oauth";
    }

    public function Init($init_data)
    {
        $this->db = ComponentsManager::Instance()->GetComponent('IDatabase');
        $this->accountManager = ComponentsManager::Instance()->GetComponent('IAccountManager');

        self::getLogger()->log_info("creating oauth-users tokens table");
        $this->db->CreateTable('OAuthUserData');

        $this->registerProviders($init_data['Providers']);
    }

    private function registerProviders($providers_config) {
        foreach ($providers_config as $className => $config) {
            self::getLogger()->log_info("loading oauth2 provider {$className}");
            include __DIR__ . "/Providers/{$className}.php";
            $provider = call_user_func($className. '::CreateFromConfig', $config);
            $this->RegisterProvider($provider);
        }
    }

    public function RegisterProvider($provider)
    {
        $this->providers[$provider->GetName()] = $provider;
    }

    public function GetProvider($provider_name)
    {
        return $this->providers[$provider_name];
    }

    public function HandleRequest($path, $query)
    {
        $providerName = $path[2];
        $provider = $this->providers[$providerName];
        $this->doHandleRequest($provider);
    }

    private function doHandleRequest($provider)
    {
        $oauthdata = $provider->GetOAuthDataFromRequest();
        if ($oauthdata === null) {
            return null;
        }
        $token = $oauthdata->GetToken();
        $user = $this->findUserByOAuthToken($token, $provider);
        if ($user === null)
        {
            $email = $oauthdata->GetEmail();
            if (isset($email) && $oauthdata->IsEmailVerified() === true)
            {
                $user = $this->updateOAuthTokenByEmail($oauthdata->GetEmail(), $provider, $token);
            }
        }
    }

    private function findUserByOAuthToken($token, $provider)
    {
        $oauthUserData = $this->db->FindFirst('OAuthUserData', array('token'=>$token, 'service'=>$provider->GetName()));
        if ($oauthUserData){
            $user = $this->accountManager->GetUserById($oauthUserData->GetUserId());
            return $user;
        }
        return null;
    }

    private function updateOAuthTokenByEmail($email, $provider, $token)
    {
        $user = $this->accountManager->GetUserByEmail($email);
echo "<h3>update oauth token</h3>";
var_dump($user);
        if ($user === null)
        {
            $user = $this->accountManager->CreateAccount(null, null, $email);
        }

        $oauth_user_data = new OAuthUserData($user->GetId(), $provider->GetName(), $token);
        $this->db->InsertNewItem($oauth_user_data);

        return $user;
    }
}

?>
