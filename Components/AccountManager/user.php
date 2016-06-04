<?php

class User{

    /**
    * user_id
    * @persist
    * @type int
    * @mandatory
    * @primary-key
    * @default AUTO-INCREMENT
    */
    private $user_id;

    /**
    * email
    * @persist
    * @type string:100
    * @unique-index
    */
    private $email;

    /**
    * username
    * @persist
    * @type string:100
    * @unique-index
    */
    private $username;

    /**
    * roles
    * @persist
    * @type Role
    * @multiplicity Many-to-Many
    */
    private $roles;

    /**
    * password_hash
    * @persist
    * @type string:128
    */
    private $password_hash;

    /**
    * password_salt
    * @persist
    * @type string:128
    */
    private $password_salt;

    /**
    * creation_date
    * @persist
    * @type TIMESTAMP
    * @mandatory
    * @default NOW
    */
    private $creation_date;

    /**
    * last_login
    * @persist
    * @type @TIMESTAMP
    */
    private $last_login;

	public function __construct($user_id, $username, $email)
	{
        $this->user_id = $user_id;
        $this->username = $username;
        $this->email = $email;
    }

    public function GetId() { return $this->user_id; }
    public function GetUsername() { return $this->username; }
    public function GetEmail() { return $this->email; }
    public function GetPasswordHash() { return $this->password_hash; }
    public function GetPasswordSalt() { return $this->password_salt; }
    public function GetCreationDate() { return $this->creation_date; }
    public function GetLastLogin() { return $this->last_login; }

}
?>
