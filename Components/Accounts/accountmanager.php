<?php

    class AccountManager {
        private $db;

        public function __construct($db) {
            $this->db = $db;
            $this->init();
        }

        private function init()
        {
            $this->db->GetDb()->
        }

        private static function GetCreateUsersTableSQL()
    	{
    		$db_prefix = constant("db_prefix");
    		$sql = "CREATE  TABLE IF NOT EXISTS `{$this->db->prefix}Users` (
    		`User ID` INT NOT NULL AUTO_INCREMENT ,
    		`User Email` VARCHAR(45) NOT NULL ,
    		`PasswordHash` VARCHAR(128) NULL ,
            `PasswordSalt` VARCHAR(128) NULL ,
            `Creation Date` TIMESTAMP NULL ,
    		`Last Login` TIMESTAMP NULL ,
            `Status` TINYINT(1) NOT NULL DEFAULT 0,
    		PRIMARY KEY (`User ID`) ,
    		UNIQUE INDEX `User Email_UNIQUE` (`User Email` ASC),
    		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

    		return $sql;
    	}

        public function CreateAccount($username, $password, $email)
        {

        }

        public function AddUserRole($user, $role)
        {

        }

        public function RemoveUserRole($user, $role)
        {

        }

        public function ValidateAccount($username, $password)
        {

        }

        public function GenerateToken($user, $purpose)
        {

        }

        public function ValidateToken($user, $purpose, $token)
        {

        }

        public function CreateRole($role)
        {

        }
    }

 ?>
