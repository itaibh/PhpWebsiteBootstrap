<?php

require_once __DIR__.'/../Logger/init.php';
require_once __DIR__.'/dbconfig.php';

class MySqlDB extends ComponentBase {

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

    private function convertTypeName($typeRegexMatches)
    {
        if (!isset($typeRegexMatches['type'])) {
            return null;
        }
        $typestr = $typeRegexMatches['type'];
        $type = $typestr;
        if ($typestr == 'string') {
            $type = 'TEXT';
        }
        if (isset($typeRegexMatches['length'])) {
            $length = $typeRegexMatches['length'];
            if ($typestr == 'string') {
                $type = "NVARCHAR($length)";
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

    private function getClassPrimaryKeys($typename) {
        $reflector = new ReflectionClass($typename);
        $properties = $reflector->getProperties();
        $primary_keys = array();
        foreach ($properties as $property) {
            $comment = $property->getDocComment();
            if (preg_match('/@persist\b/', $comment) > 0 && preg_match('/@primary-key\b/', $comment) > 0) {
                $primary_keys[] = $property;
            }
        }
        return $primary_keys;
    }

    private function getPropertyFieldType($property) {
        $comment = $property->getDocComment();
        if (preg_match('/@type\s+(?P<type>\w+)(\:(?<length>\d+))?/', $comment, $matches) === 0) {
            return null;
        }

        $field_type = $this->convertTypeName($matches);
        return $field_type;
    }

    private function createRelationTable($property, $primary_keys){
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}{$property->class}_{$property->name}` (";

        $comment = $property->getDocComment();

        if (preg_match('/@multiplicity\s+(?P<multiplicity>\S+)/', $comment, $matches) > 0) {
            if ($matches['multiplicity'] == 'Many-to-Many') {
                if (preg_match('/@type\s+(?P<type>\w+)(\:(?<length>\d+))?/', $comment, $matches) === 0) {
                    return null;
                }
                $extra_keys = $this->getClassPrimaryKeys($matches['type']);
                $primary_keys = array_merge($primary_keys, $extra_keys);
            } else if($matches['multiplicity'] == 'One-to-Many') {
                // keep primary_keys as it is
            } else {
                self::getLogger()->log_error('createRelationTable - shouldn\'t have got here.');
                return; // Should never get here.
            }
        }
        $statements = array();
        foreach ($primary_keys as $primary_key) {
            $field_type = $this->getPropertyFieldType($primary_key);
            $statements[] = "`{$primary_key->name}` {$field_type} NOT NULL";
        }
        $primary_key_names = array_map(function($item) { return $item->name; }, $primary_keys);
        $statements[] = 'PRIMARY KEY (`' . implode('`,`', $primary_key_names) . '`)';

        // TODO - add references:
        /*
        FOREIGN KEY (product_category, product_id)
            REFERENCES product(category, id)
            ON UPDATE CASCADE ON DELETE RESTRICT,

        FOREIGN KEY (customer_id)
            REFERENCES customer(id)
        */

        $sql .= "\n" . implode(",\n", $statements) . "\n";

        $sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1';

        self::getLogger()->log_info("createRelationTable - sql: \n$sql");
        $stmt = $this->dbh->exec($sql);

        return true;
    }

    private function createSqlStatementForProperty($property, &$primary_keys, &$unique_indices, &$foreign_keys) {
        $comment = $property->getDocComment();

        if (preg_match('/@multiplicity\s+(?P<multiplicity>\S+)/', $comment, $matches) > 0) {
            $multiplicity = $matches['multiplicity'];
            self::getLogger()->log_info("Property {$property->name} has multiplicity defined: {$multiplicity}");
            if ($multiplicity == 'Many-to-Many' || $multiplicity == 'One-to-Many') {
                self::getLogger()->log_info("Adding {$property->name} to foreign keys list.");
                $foreign_keys[] = $property;
                return null;
            }
        }

        if (preg_match('/@type\s+(?P<type>\w+)(\:(?<length>\d+))?/', $comment, $matches) === 0) {
            return null;
        }

        $field_type = $this->convertTypeName($matches);
        if ($field_type === null) {
            return null;
        }

        $field_name = $property->name;

        $mandatory = '';
        $default = '';
        if (preg_match('/@mandatory\b/', $comment) > 0) {
            $mandatory = 'NOT NULL';
        }

        if (preg_match('/@primary-key\b/', $comment) > 0) {
            $primary_keys[] = $property;
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

    private function isPropertyPersistent($property)
    {
        $comment = $property->getDocComment();
        return (preg_match('/@persist\b/', $comment) > 0);
    }

    public function CreateTable($typename)
    {
        $reflector = new ReflectionClass($typename);
        $properties = array_filter($reflector->getProperties(), array($this, 'isPropertyPersistent'));

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->prefix}{$typename}` (";
        $primary_keys = array();
        $unique_indices = array();
        $foreign_keys = array();
        $statements = array();
        foreach ($properties as $property) {
            $method_sql = $this->createSqlStatementForProperty($property, $primary_keys, $unique_indices, $foreign_keys);
            if ($method_sql !== null) {
                $statements[] = $method_sql;
            }
        }

        foreach ($foreign_keys as $property) {
            $this->createRelationTable($property, $primary_keys);
        }

        if (count($primary_keys) > 0) {
            $primary_key_names = array_map(function($item) { return $item->name; }, $primary_keys);
            $statements[] = 'PRIMARY KEY (`' . implode('`,`', $primary_key_names) . '`)';
        }

        foreach ($unique_indices as $unique_index) {
            $statements[]  = "UNIQUE INDEX `{$unique_index}_UNIQUE` (`{$unique_index}` ASC)";
        }

        $sql .= "\n" . implode(",\n", $statements) . "\n";

        $sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1';

        //self::getLogger()->log_info("CreateTable - sql: \n$sql");
        $stmt = $this->dbh->exec($sql);
    }

    private function getItemInsertionData($item)
    {
        $typename = get_class($item);
        $reflector = new ReflectionClass($typename);
        $properties = $reflector->getProperties();

        $parameters = array();
        foreach ($properties as $property) {
            $comment = $property->getDocComment();
            if (preg_match('/@persist\b/', $comment) == 0) {
                continue;
            }
            $property->setAccessible(true);
            $value = $property->getValue($item);
            if ($value !== null) {
                if (preg_match('/@multiplicity\s+(?P<multiplicity>\S+)/', $comment, $matches) > 0) {
                    if ($matches['multiplicity'] == 'Many-to-Many') {
                        continue; // TODO - handle foreign keys on insertion
                    } else if ($matches['multiplicity'] == 'One-to-Many') {
                        continue; // TODO - handle foreign keys on insertion
                    }
                }
                $parameters[':' . $property->name] = $value;
            }
        }
        return $parameters;
    }

    public function InsertNewItem($item)
    {
        $parameters = $this->getItemInsertionData($item);

        $names = array();
        foreach ($parameters as $key => $value) {
            $names[] = substr($key, 1);
        }
        $typename = get_class($item);
        $sql = "INSERT INTO `{$this->prefix}$typename` (`";
        $sql .= implode('`,`', $names);
        $sql .= '`) VALUES(:';
        $sql .= implode(',:', $names);
        $sql .= ')';

        self::getLogger()->log_info("InsertNewItem - sql: \n$sql");

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($parameters);
    }

    public function FindFirst($typename, $parameters)
    {
        //TODO - build SQL correctly.
        $sql = "SELECT * FROM {$this->prefix}$typename WHERE ";

        $statements = array();
        $params = array();
        foreach ($parameters as $key => $value) {
            $statements[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $sql .= implode(' AND ', $statements) . ' LIMIT 1';

        self::getLogger()->log_info("FindFirst - sql: \n$sql");
        self::getLogger()->log_info("FindFirst - params: \n" . var_export($params, true));

        $stmt = $this->dbh->prepare($sql);

        $stmt->execute($params);
        $row = $stmt->fetch();

        //TODO - create the return value correctly.
        return null;
    }

    public function DeleteItem($item) {
        //TODO - build SQL correctly - get primary keys and use them as parameters.
        $sql = "DELETE FROM {$this->prefix}roles WHERE role_name = :role";
        $this->db->ExecuteNonQuery($sql, array(':role'=>$role_name));
    }

}

?>
