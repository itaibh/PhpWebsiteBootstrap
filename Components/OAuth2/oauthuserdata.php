<?php

class OAuthUserData {

    /**
    * user_id
    * @persist
    * @type int
    * @mandatory
    * @primary-key
    */
    private $user_id;

    /**
    * service
    * @persist
    * @type string:20
    * @mandatory
    * @primary-key
    */
    private $service;

    /**
    * token
    * @persist
    * @type string
    * @mandatory
    */
    private $token;

    public function __construct ($user_id, $service, $token)
    {
        $this->user_id = $user_id;
        $this->service = $service;
        $this->token = $token;
    }

    public function GetUserId() { return $this->user_id; }
    public function GetService() { return $this->service; }
    public function GetToken() { return $this->token; }

}

?>
