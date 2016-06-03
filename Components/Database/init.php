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

    private function convertType($typestr)
    {
        $parts = explode(':', $typestr);
        $type = $parts[0];
        if ($type == 'string')
        {
            if (count($parts) == 1) {
                $type = 'TEXT';
            }
            else
            {
                $type = 'NVARCHAR('.$parts[1].')';
            }
        }

        return $type;
    }

    private function convertDefault($default_value){
        self::getLogger()->log_info("convertDefault - value: $default_value");
        if ($default_value == 'NOW') {
            return 'DEFAULT CURRENT_TIMESTAMP';
        }
        else if ($default_value == 'AUTO-INCREMENT') {
            return 'AUTO_INCREMENT';
        }

        return '';
    }

    private function createGetterCreateFieldSQL($method, &$primary_keys, &$unique_indices) {
        $comment = $method->getDocComment();
        if (preg_match('/@return\s+(?P<type>[\w\[\]\:]+)/', $comment, $matches) === 0) {
            return null;
        }

        $field_type = $this->convertType($matches['type']);
        $field_name = substr($method->name, 3);

        $mandatory = '';
        $default = '';
        if (preg_match('/@mandatory\b/', $comment) > 0) {
            $mandatory = 'NOT NULL';
        }

        if (preg_match('/@primary-key\b/', $comment) > 0) {
            $primary_keys[] = $field_name;
        }

        if (preg_match('/@unique-index\b/', $comment) > 0) {
            $unique_indices[] = $field_name;
        }

        if (preg_match('/@default\s+(?P<value>.+)/', $comment, $matches) > 0) {
            $default = $this->convertDefault($matches['value']);
        }

        $sql = "`{$field_name}` {$field_type} {$mandatory} {$default}";

        return $sql;
    }

    private function getPublicGetters($object){
        $reflector = new ReflectionClass($object);
        $class_methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
        $methods = array();
        foreach ($class_methods as $method) {
            if (substr_compare($method->name, 'Get', 0, 3) === 0)
            {
                $methods[] = $method;
            }
        }

        return $methods;
    }

    public function CreateTable($typename)
    {
        $getters = $this->getPublicGetters($typename);

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}{$typename}` (";
        $primary_keys = array();
        $unique_indices = array();
        $statements = array();
        foreach ($getters as $getter) {
            $method_sql = $this->createGetterCreateFieldSQL($getter, $primary_keys, $unique_indices);
            if ($method_sql !== null) {
                $statements[] = $method_sql;
            }
        }

        if (count($primary_keys) > 0) {
            $statements[] = 'PRIMARY KEY (`' . implode('`,`', $primary_keys) . '`)';
        }

        foreach ($unique_indices as $unique_index) {
            $statements[]  = "UNIQUE INDEX `{$unique_index}_UNIQUE` (`{$unique_index}` ASC)";
        }

        $sql .= "\n" . implode(",\n", $statements) . "\n";

        $sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1';

        self::getLogger()->log_info("CreateTable - sql: \n$sql");
        $stmt = $this->dbh->exec($sql);
    }

    public function InsertNewItem($item)
    {
        $typename = get_class($item);
        $getters = $this->getPublicGetters($typename);
        $names = array_map(function($item) { return substr($item->name, 3); }, $getters);

        $sql = "INSERT INTO `{$this->prefix}$typename` (`';
        $sql .= implode('`,`', $names);
        $sql .= ') VALUES(:';
        $sql .= implode(',:', $names);
        $sql .= ')";

        $stmt = $this->dbh->prepare($sql);

        $parameters = array();
        foreach ($getters as $getter) {
            $value = $getter->invoke();
            $parameters[':' . substr($item->name, 3)] = $value;
        }

        $stmt->execute($parameters);
    }
}

?>
