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

        private function __construct() { }

        public static function CreateWithId($role_id, $role_name)
        {
            $role = new Role();
            $role->role_id = $role_id;
            $role->role_name = $role_name;
            return $role;
        }

        public function Create($role_name)
        {
            $role = new Role();
            $role->role_name = $role_name;
            return $role;
        }

        public function GetRoleId() { return $this->role_id; }
        public function GetRoleName() { return $this->role_name; }
    }

?>
