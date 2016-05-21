<?php

require_once __DIR__.'/../Logger/logger.php';
require_once __DIR__.'/dbconfig.php';

class DB {

    private $dbh;
    private $settings;

    public $prefix;

    private static $instance;

    private static $s_Logger;
	private static function getLogger(){
		if (self::$s_Logger == null)
			self::$s_Logger = new Logger(__CLASS__);
		return self::$s_Logger;
	}

    private function __construct() {
        $this->settings = getDbSettings();
        $this->prefix = $this->settings['db_prefix'];
    }

    public static function GetInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function CreateDatabase(){
        try{
    		$this->dbh = new PDO('mysql:host='.$this->settings['db_host'],
                                $this->settings['db_username'], $this->settings['db_password']);

    		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            self::getLogger()->log_error('Could not connect: ' . $this->settings['db_host'] . ': ' . $e->getMessage());
            throw $e;
        }
    }

    public function ConnectToDatabase(){
        try{
    		$this->dbh = new PDO('mysql:host='.$this->settings['db_host'].';dbname='.$this->settings['db_name'],
                                $this->settings['db_username'], $this->settings['db_password']);

    		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            self::getLogger()->log_error('Could not connect: ' . $this->settings['db_host'] . ': ' . $e->getMessage());
            throw $e;
        }
    }

    public function GetDb()
	{
		return $this->dbh;
	}

	public function CloseDb()
	{
		$this->dbh = null;
	}

    public function CreateDb()
	{
		echo "<p>".var_export($this->dbh, true)."</p>";
		$db_name = $this->settings['db_name'];
		echo "<p>db name: $db_name</p>";

		$this->dbh->exec("DROP DATABASE $db_name; CREATE DATABASE $db_name;");
		echo "<p>db dropped then created.</p>";
		echo "<p>reinitializing db...</p>";
		$this->connectToDatabase();
		echo "<p>done creating db. time to fill with tables.</p>";
		return true;
	}

    public function executeNonQuery($sql, $parameters)
    {
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($parameters);
    }
}

?>
