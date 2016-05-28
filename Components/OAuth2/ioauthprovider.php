<?php

interface IOAuthProvider
{
    public function GetName();
    public function GetLoginUrl($state);
    public function GetOAuthDataFromRequest();
}

class OAuthData
{
    public function __construct($token, $email, $email_verified) {
        $this->token = $token;
        $this->email = $email;
        $this->email_verified = $email_verified;
    }

    private $token;
    private $email;
    private $email_verified;

    public function GetToken() { return $this->token; }
    public function GetEmail() { return $this->email; }
    public function IsEmailVerified() { return $this->email_verified; }
}
?>
