<?php

class OAuthUserData {

    private $user_id;
    private $service;
    private $token;

    public function __construct ($user_id, $service, $token)
    {
        $this->user_id = $user_id;
        $this->service = $service;
        $this->token = $token;
    }

    /**
    * GetUserId
    * @return int
    * @mandatory
    * @primary-key
    */
    public function GetUserId() { return $this->user_id; }


    /**
    * GetService
    * @return string:20
    * @mandatory
    * @primary-key
    */
    public function GetService() { return $this->service; }

    /**
    * GetToken
    * @return string
    * @mandatory
    */
    public function GetToken() { return $this->token; }

}

?>
