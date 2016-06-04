<?php
require_once __DIR__.'/../componentsmanager.php';
require_once __DIR__.'/../Logger/init.php';
require_once __DIR__.'/ioauthprovider.php';
require_once __DIR__.'/oauthuserdata.php';

class OAuth2 extends ComponentBase
{
    private $providers = array();
    private $db;
    private $accountManager;

    protected function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    public function Init()
    {
        $this->db = ComponentsManager::Instance()->GetComponent('MySqlDB');
        $this->accountManager = ComponentsManager::Instance()->GetComponent('AccountManager');

        self::getLogger()->log_info("creating oauth-users tokens table");
        $this->db->CreateTable('OAuthUserData');
        //$this->db->ExecuteNonQuery(self::GetOAuthUserTokensTableSQL($this->db->prefix));
    }

    /*private static function GetOAuthUserTokensTableSQL($db_prefix)
	{
		$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}oauth_user_tokens` (
                `user_id` INT NOT NULL,
                `service` VARCHAR(20) NOT NULL,
        		`token` TEXT NOT NULL,
        		PRIMARY KEY (`user_id`,`service`)
        		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";

		return $sql;
	}*/

    public function RegisterProvider($provider)
    {
        $this->providers[$provider->GetName()] = $provider;
    }

    public function GetProvider($provider_name)
    {
        return $this->providers[$provider_name];
    }

    public function TryHandleRequest()
    {
        $reqUri = $_SERVER['REQUEST_URI'];
        $reqUriParts =  explode('?', $reqUri);
        $requestURI = explode('/', $reqUriParts[0]);

        if ($requestURI[1] != 'oauth')
        {
            return false;
        }

        $lastPart = explode('?',$requestURI[2]);
        $provider = $this->providers[$lastPart];
        $this->HandleRequest($provider);

        return true;
    }

    private function HandleRequest($provider)
    {
        $oauthdata = $provider->GetOAuthDataFromRequest();
        $token = $oauthdata->GetToken();
        $user = $this->findUserByOAuthToken($token, $provider);
        if ($user === null)
        {
            $email = $oauthdata->GetEmail();
            if (isset($email) && $oauthdata->IsEmailVerified() === true)
            {
                $user = $this->updateOAuthTokenByEmail($oauthdata->GetEmail(), $provider);
            }
        }
    }

    private function findUserByOAuthToken($token, $provider)
    {
        $db_prefix = $this->db->prefix;
        $sql = "SELECT * FROM `{$db_prefix}oauth_user_tokens` WHERE token = :token & service = :service";
        $row = $this->db->QuerySingleRow($sql, array(':token'=>$token, ':service'=>$provider->GetName()));
        if ($row != null)
        {
            //$user = get user from $row;
            return $user;
        }
        return null;
    }

    private function updateOAuthTokenByEmail($email, $provider)
    {
        $user = $this->accountManager->GetUserByEmail($email);
        $db_prefix = $this->db->prefix;
        if ($user === null)
        {
            $this->accountManager->CreateAccount(null, null, $email);
        }

        $oauth_user_data = new OAuthUserData($user->GetId(), $provider->GetName(), $token);
        $this->db->InsertNewItem($oauth_user_data);

        //$sql = "INSERT INTO `{$db_prefix}oauth_user_tokens` (user_id, service, token) VALUES(:user_id, :service, :token)";
        //$this->db->ExecuteNonQuery($sql, array('user_id'=>$user->GetId(), ':token'=>$token, ':service'=>$provider->GetName()));

        return $user;
    }
}

?>
