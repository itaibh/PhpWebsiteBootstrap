<?php

require_once __DIR__.'/../Logger/init.php';
require_once __DIR__.'/dbconfig.php';

class Database extends ComponentBase {

    private $dbh;
    private $settings;

    public $prefix;

    protected function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    public function Init()
    {
        $this->settings = getDbSettings();
        $this->prefix = $this->settings['db_prefix'];
        $this->ConnectToDatabase();
    }

    public function ConnectToDatabase(){
        try
        {
            $this->doConnectToDatabase();
        }
        catch(PDOException $e)
        {
            self::getLogger()->log_error('Could not connect: ' . $this->settings['db_host'] . ': ' . $e->getMessage());
            $this->CreateDatabase();
        }
    }

    private function doConnectToDatabase()
    {
        self::getLogger()->log_info('connecting to database');
        $this->dbh = new PDO('mysql:host='.$this->settings['db_host'].';dbname='.$this->settings['db_name'],
                            $this->settings['db_username'], $this->settings['db_password']);

        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function CreateDatabase()
    {
        try
        {
            $this->doCreateDatabase();
            $this->doConnectToDatabase();
        }
        catch(PDOException $e)
        {
            self::getLogger()->log_error('Could not connect: ' . $this->settings['db_host'] . ': ' . $e->getMessage());
            throw $e;
        }
    }

    private function doCreateDatabase()
    {
        self::getLogger()->log_info('creating database');
        $this->dbh = new PDO('mysql:host='.$this->settings['db_host'],
                            $this->settings['db_username'], $this->settings['db_password']);

        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $db_name = $this->settings['db_name'];
        $this->dbh->exec("DROP DATABASE IF EXISTS $db_name; CREATE DATABASE $db_name;");
    }

    public function GetDb()
	{
		return $this->dbh;
	}

	public function CloseDb()
	{
		$this->dbh = null;
	}

    public function ExecuteNonQuery($sql, $parameters = null)
    {
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($parameters);
    }

    public function QuerySingleRow($sql, $parameters = null)
    {
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($parameters);
        $row = $stmt->fetch();
        return $row;
    }
}

?>
