<?php
require_once __DIR__.'/../componentsmanager.php';
require_once __DIR__.'/user.php';
require_once __DIR__.'/role.php';

class AccountManager extends ComponentBase {

    private $db;

    protected function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    public function Init()
    {
        $this->db = ComponentsManager::Instance()->GetComponent('MySqlDB');

        self::getLogger()->log_info("creating roles table");
        $this->db->CreateTable('Role');

        self::getLogger()->log_info("creating users table");
        $this->db->CreateTable('User');
    }

    public function CreateAccount($username, $password, $email)
    {
        $this->validateAccountUniqueness($username, $email);

        if ($password != null)
        {
            $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
            $password_hash = hash('sha256', $password . $salt);
        }
        else
        {
            $salt = null;
            $password_hash = null;
        }

        $new_user = new User($username, $email, $password_hash, $salt);
        $this->db->InsertNewItem($new_user);
    }

    private function validateAccountUniqueness($username, $email)
    {
        $db_prefix = $this->db->prefix;

        if ($username != null)
        {
            $user = $this->db->FindFirst('User', array('username'=>$username));
            /*$sql = "SELECT TOP 1 1 FROM {$db_prefix}users WHERE username = :username";
            $row = $this->db->QuerySingleRow($sql, array(':username'=>$username));*/
            if ($user){
                throw new Exception("Username already in use");
            }
        }

        $sql = "SELECT TOP 1 1 FROM {$db_prefix}users WHERE email = :email";
        $row = $db->QuerySingleRow($sql, array(':email'=>$email));
        if ($row){
            throw new Exception("Email already in use");
        }
    }

    public function CreateRole($role_name)
    {
        $db_prefix = $this->db->prefix;
        $sql = "INSERT INTO {$db_prefix}roles (role_name) VALUES (:role)";
        $this->db->ExecuteNonQuery($sql, array(':role'=>$role_name));
    }

    public function DeleteRole($role_name)
    {
        $db_prefix = $this->db->prefix;
        $sql = "DELETE FROM {$db_prefix}roles WHERE role_name = :role";
        $this->db->ExecuteNonQuery($sql, array(':role'=>$role_name));
    }

    public function RenameRole($current_role_name, $new_role_name)
    {
        $db_prefix = $this->db->prefix;
        $sql = "UPDATE TABLE {$db_prefix}roles SET role_name = :newrole WHERE role_name = :oldrole";
        $this->db->ExecuteNonQuery($sql, array(':newrole'=>$new_role_name, ':oldrole'=>$old_role_name));
    }

    public function AddUserRole($username, $role_name)
    {
        $role_id = validateRoleExistance($role_name);
        $user = validateUserExistance($username);
        $user_id = $user['user_id'];
        $db_prefix = $this->db->prefix;
        $sql = "INSERT INTO {$db_prefix}user_roles (role_id, user_id) VALUES (:role, :user)";
        $this->db->ExecuteNonQuery($sql, array(':role'=>$role_id, ':user'=>$user_id));
    }

    public function RemoveUserRole($username, $role)
    {
        $role_id = validateRoleExistance($role_name);
        $user = validateUserExistance($username);
        $user_id = $user['user_id'];
        $db_prefix = $this->db->prefix;
        $sql = "DELETE FROM {$db_prefix}user_roles WHERE role_id = :role AND user_id = :user";
        $this->db->ExecuteNonQuery($sql, array(':role'=>$role_id, ':user'=>$user_id));
    }

    private function validateRoleExistance($role_name)
    {
        $db_prefix = $this->db->prefix;
        $sql = "SELECT TOP 1 role_id FROM {$db_prefix}roles WHERE role_name = :role";
        $row = $this->db->QuerySingleRow($sql, array(':role'=>$role_name));
        if (!$row){
            throw new Exception("No role found by that name");
        }

        return $row['role_id'];
    }

    public function GetUserByEmail($email)
    {
        $db_prefix = $this->db->prefix;
        $sql = "SELECT TOP 1 user_id FROM {$db_prefix}users WHERE email = :email";
        $row = $this->db->QuerySingleRow($sql, array(':email'=>$email));
        if (!$row) {
            return null;
        }

        return $this->createUserFromUserDbRow($row);
    }

    private function validateUserExistance($username)
    {
        $db_prefix = $this->db->prefix;
        $sql = "SELECT TOP 1 user_id FROM {$db_prefix}users WHERE username = :username";
        $row = $this->db->QuerySingleRow($sql, array(':username'=>$username));
        if (!$row){
            throw new Exception("No user found with that username");
        }

        return $row;
    }

    public function ValidateAccount($username, $password)
    {
        $user_row = validateUserExistance($username);
        $salt = $user_row['password_salt'];
        $password_hash = hash('sha256', $password . $salt);

        if ($password_hash == $user['password_hash']) {
            $user = $this->createUserFromUserDbRow($user_row);
            return $user;
        }

        return null;
    }

    public function GenerateToken($username, $purpose)
    {
        $user_row = validateUserExistance($username);
        $token = bin2hex(random_bytes($length));

        $db_prefix = $this->db->prefix;

        $sql = "INSERT INTO {$db_prefix}user_tokens (user_id, token, purpose)
                VALUES (:user, :token, :purpose)";

        $this->db->ExecuteNonQuery($sql,
                        array(':user'=>$user_row['user_id'],
                            ':token'=>$token,
                            ':purpose'=>$purpose));

        return $token;
    }

    public function ValidateToken($username, $purpose, $token)
    {
        $user_row = validateUserExistance($username);
        $sql = "SELECT FROM {$db_prefix}user_tokens
                WHERE user_id = :user AND purpose = :purpose AND token = :token";

        $row = $this->db->QuerySingleRow($sql,
                        array(':user'=>$user_row['user_id'],
                            ':token'=>$token,
                            ':purpose'=>$purpose));

        if (!$row) return false;

        $date1 = $row['creation_date'];
        $date2 = date();
        $secondsDiff = (int)($date2-$date1);

        return ($secondsDiff < 15*60);
    }

    private function createUserFromUserDbRow($row)
    {
        $user = new User($row['user_id'], $row['username'], $row['email']);
        return $user;
    }
}

 ?>
