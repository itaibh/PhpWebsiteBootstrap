<?php

interface IDatabase {
    public function CreateTable($typename);
    public function InsertNewItem($item);
    public function FindFirst($typename, $parameters);
    public function DeleteItem($item);
}

interface IAccountManager {
    public function CreateAccount($username, $password, $email);
    public function CreateRole($role_name);
    public function DeleteRole($role_name);
    //public function RenameRole($current_role_name, $new_role_name);
    //public function AddUserRole($username, $role_name);
    //public function RemoveUserRole($username, $role);
    public function GetUserById($id);
    public function GetUserByEmail($email);
    public function ValidateAccount($username, $password);
    public function GenerateToken($username, $purpose);
    public function ValidateToken($username, $purpose, $token);
}

interface IOAuth2
{
    public function RegisterProvider($provider);
    public function GetProvider($provider_name);
}

?>
