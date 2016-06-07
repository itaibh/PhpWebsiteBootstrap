<?php
    interface IAccountManager {

        public function CreateAccount($username, $password, $email);
        public function CreateRole($role_name);
        public function DeleteRole($role_name);
        //public function RenameRole($current_role_name, $new_role_name);
        //public function AddUserRole($username, $role_name);
        //public function RemoveUserRole($username, $role);
        public function GetUserByEmail($email);
        public function ValidateAccount($username, $password);
        public function GenerateToken($username, $purpose);
        public function ValidateToken($username, $purpose, $token);
        
    }
?>
