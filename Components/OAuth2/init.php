<?php
require_once __DIR__.'/../componentsmanager.php';
require_once __DIR__.'/../Logger/logger.php';
require_once __DIR__.'/../Database/db.php';

interface IOAuthProvider
{
    public function GetName();
    public function GetLoginUrl($state);
}

class OAuthManager implements IComponent
{
    private $providers = array();
    private $db;

    private function __construct() {
        $this->init();
    }

    private function init()
    {
        $this->db = ComponentsManager::Instance()->GetComponent('Database');
        $this->db->ExecuteNonQuery(self::GetOAuthUserTokensTableSQL());
    }

    public static function Instance()
    {
        static $instance = null;
        if ($instance === null)
        {
            $instance = new static();
        }
        return $instance;
    }

    public function GetName() { return "OAuthManager"; }

    private static function GetOAuthUserTokensTableSQL()
	{
		$db_prefix = $this->db->prefix;
		$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}oauth_user_tokens` (
                `user_id` INT NOT NULL,
                `service` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL,
        		`token` TEXT COLLATE utf8_unicode_ci NOT NULL,
        		PRIMARY KEY (`user_id`,`service`),
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
