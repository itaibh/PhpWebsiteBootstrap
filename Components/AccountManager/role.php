<?php

    class Role {

        /**
        * role_id
        * @persist
        * @type int
        * @primary-key
        * @default AUTO-INCREMENT
        */
        private $role_id;

        /**
        * role_name
        * @persist
        * @type string:100
        * @unique-index
        */
        private $role_name;

        public function __construct($role_id, $role_name)
        {
            $this->role_id = $role_id;
            $this->role_name = $role_name;
        }

        public function GetRoleId() { return $this->role_id; }
        public function GetRoleName() { return $this->role_name; }
    }

?>
