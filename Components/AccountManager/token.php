<?php

    class Token {

        /**
        * user
        * @persist
        * @type User
        * @primary-key
        */
        private $user;

        /**
        * purpose
        * @persist
        * @type string:50
        * @primary-key
        */
        private $purpose;

        /**
        * token
        * @persist
        * @type string
        */
        private $token;

        /**
        * creation_date
        * @persist
        * @type TIMESTAMP
        * @mandatory
        * @default NOW
        */
        private $creation_date;

        public function __construct($user, $purpose, $token)
        {
            $this->user = $user;
            $this->purpose = $purpose;
            $this->token = $token;
        }

        public function GetToken() { return $this->token; }
        public function GetCreationDate() { return $this->creation_date; }
    }

?>
