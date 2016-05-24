<?php
require_once __DIR__.'/../componentsmanager.php';
require_once __DIR__.'/../Logger/init.php';

interface IOAuthProvider
{
    public function GetName();
    public function GetLoginUrl($state);
}

class OAuth2 extends ComponentBase
{
    private $providers = array();
    private $db;

    protected function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    public function Init()
    {
        $this->db = ComponentsManager::Instance()->GetComponent('Database');

        self::getLogger()->log_info("creating oauth-users tokens table");
        $this->db->ExecuteNonQuery(self::GetOAuthUserTokensTableSQL($this->db->prefix));
    }

    private static function GetOAuthUserTokensTableSQL($db_prefix)
	{
		$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}oauth_user_tokens` (
                `user_id` INT NOT NULL,
                `service` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL,
        		`token` TEXT COLLATE utf8_unicode_ci NOT NULL,
        		PRIMARY KEY (`user_id`,`service`)
        		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";

		return $sql;
	}

    public function RegisterProvider($provider)
    {
        $this->providers[$provider->GetName()] = $provider;
    }

    public function GetProvider($provider_name)
    {
        return $this->providers[$provider_name];
    }

    public function HandleRequest()
    {
        $provider = $this->providers[($_GET['oauth_provider'])];
        $provider->HandleRequest();
    }
}

?>
