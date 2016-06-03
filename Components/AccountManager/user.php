<?php

class User{

    private $user_id;
    private $email;
    private $username;
    private $roles;

    private $password_hash;
    private $password_salt;
    private $creation_date;
    private $last_login;

	public function __construct($user_id, $username, $email)
	{
        $this->user_id = $user_id;
        $this->username = $username;
        $this->email = $email;
    }

    /**
    * GetId
    * @return int
    * @mandatory
    * @primary-key
    * @default AUTO-INCREMENT
    */
    public function GetId() { return $this->user_id; }

    /**
    * GetUsername
    * @return string:100
    * @unique-index
    */
    public function GetUsername() { return $this->username; }

    /**
    * GetEmail
    * @return string:100
    * @unique-index
    */
    public function GetEmail() { return $this->email; }

    /**
    * GetPasswordHash
    * @return string:128
    */
    public function GetPasswordHash() { return $this->password_hash; }

    /**
    * GetPasswordSalt
    * @return string:128
    */
    public function GetPasswordSalt() { return $this->password_salt; }

    /**
    * GetCreationDate
    * @return TIMESTAMP
    * @mandatory
    * @default NOW
    */
    public function GetCreationDate() { return $this->creation_date; }

    /**
    * GetLastLogin
    * @return @TIMESTAMP
    */
    public function GetLastLogin() { return $this->last_login; }

}
?>
