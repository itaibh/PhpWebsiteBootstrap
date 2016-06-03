<?php

class User{

    private $user_id;
    private $email;
    private $username;
    private $roles;

	public function __construct($user_id, $username, $email)
	{
        $this->user_id = $user_id;
        $this->username = $username;
        $this->email = $email;
    }

    public function GetId() { return $this->user_id; }
    public function GetUsername() { return $this->username; }
    public function GetEmail() { return $this->email; }

}
?>
