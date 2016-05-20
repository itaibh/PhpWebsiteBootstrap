<?php

require_once __DIR__ . '/dbconfig.php';

class DB {

    private $dbh;
    private $settings;

    public $prefix;

    public function __construct() {
        $this->connectToDatabase();
        $this->settings = getDbSettings();
        $this->prefix = $settings['db_prefix'];
    }

    private function connectToDatabase(){
        try{
    		$this->dbh = new PDO('mysql:host='.$this->settings['db_host'].';dbname='.$this->settings['db_name'],
                                $this->settings['db_username'], $this->settings['db_password']);

    		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            die('Could not connect: ' . $this->settings['db_host'] . ': ' . $e->getMessage());
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
}

?>
