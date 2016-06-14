<?php
require_once __DIR__.'/../componentsmanager.php';
require_once __DIR__.'/user.php';
require_once __DIR__.'/role.php';

class AccountManager extends ComponentBase implements IAccountManager {

    private $db;

    protected function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    public function Init($init_data)
    {
        $this->db = ComponentsManager::Instance()->GetComponent('IDatabase');

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

        $new_user = User::CreateWithPasswordData($username, $email, $password_hash, $salt);
        $this->db->InsertNewItem($new_user);
    }

    private function validateAccountUniqueness($username, $email)
    {
        $db_prefix = $this->db->prefix;

        if ($username != null)
        {
            $user = $this->db->FindFirst('User', array('username'=>$username));
            if ($user){
                throw new Exception("Username already in use");
            }
        }
    }

    public function CreateRole($role_name)
    {
        $new_role = Role::Create($role_name);
        $this->db->InsertNewItem($new_role);
    }

    public function DeleteRole($role_name)
    {
        $role = $this->db->FindFirst('Role', array('role_name'=>$role_name));
        if ($role){
            $this->db->DeleteItem($role);
        }
    }
/*
    public function RenameRole($current_role_name, $new_role_name)
    {
        $db_prefix = $this->db->prefix;
        $sql = "UPDATE TABLE {$db_prefix}roles SET role_name = :newrole WHERE role_name = :oldrole";
        $this->db->ExecuteNonQuery($sql, array(':newrole'=>$new_role_name, ':oldrole'=>$old_role_name));
    }

    public function AddUserRole($username, $role_name)
    {
        $role_id = validateRoleExistance($role_name);
        $user = $this->validateUserExistance($username);
        $user_id = $user['user_id'];
        $db_prefix = $this->db->prefix;
        $sql = "INSERT INTO {$db_prefix}user_roles (role_id, user_id) VALUES (:role, :user)";
        $this->db->ExecuteNonQuery($sql, array(':role'=>$role_id, ':user'=>$user_id));
    }

    public function RemoveUserRole($username, $role)
    {
        $role_id = validateRoleExistance($role_name);
        $user = $this->validateUserExistance($username);
        $user_id = $user['user_id'];
        $db_prefix = $this->db->prefix;
        $sql = "DELETE FROM {$db_prefix}user_roles WHERE role_id = :role AND user_id = :user";
        $this->db->ExecuteNonQuery($sql, array(':role'=>$role_id, ':user'=>$user_id));
    }
*/
    private function validateRoleExistance($role_name)
    {
        $role = $this->db->FindFirst('Role', array('role_name'=>$role_name));
        if (!$role) {
            throw new Exception("No role found by that name");
        }

        return $role;
    }

    public function GetUserByEmail($email)
    {
        $user = $this->db->FindFirst('User', array('email'=>$email));
        return $user;
    }

    private function validateUserExistance($username)
    {
        $user = $this->db->FindFirst('User', array('username'=>$username));
        if (!$user){
            throw new Exception("No user found with that username");
        }

        return $user;
    }

    public function ValidateAccount($username, $password)
    {
        try {
            self::getLogger()->log_info("ValidateAccount - $username, $password");
            $user = $this->validateUserExistance($username);
            $salt = $user->GetPasswordSalt();
            $password_hash = hash('sha256', $password . $salt);

            self::getLogger()->log_info("hash: $password_hash");
            if ($password_hash == $user->GetPasswordHash()) {
                return $user;
            }
        } catch (Exception $ex) {
            self::getLogger()->log_error('ValidateAccount - ' . $ex->getMessage());
            return null;
        }
        return null;
    }

    public function GenerateToken($username, $purpose)
    {
        $user = $this->validateUserExistance($username);
        $token_data = bin2hex(random_bytes($length));

        $token = new Token($user, $purpose, $token_data);
        $this->db->InsertNewItem($token);

        return $token;
    }

    public function ValidateToken($username, $purpose, $token)
    {
        $user = $this->validateUserExistance($username);
        $token = $this->db->FindFirst('Token', array('user'=>$user, 'token'=>$token, 'purpose'=>$purpose));
        if (!$token) return false;

        $date1 = $token->GetCreationDate();
        $date2 = date();
        $secondsDiff = (int)($date2-$date1);

        return ($secondsDiff < 15*60);
    }

    public function TryHandleRequest()
    {
        $reqUri = $_SERVER['REQUEST_URI'];
        $reqUriParts =  explode('?', $reqUri);
        $requestURI = explode('/', $reqUriParts[0]);

        self::getLogger()->log_info("TryHandleRequest - " . var_export($requestURI, true));

        $action = $requestURI[1];
        if ($action != 'login' && $action != 'register')
        {
            return false;
        }
        $method = $_SERVER['REQUEST_METHOD'];

        $function_name = "{$action}_{$method}";

        if(method_exists($this, $function_name))
        {
            $this->$function_name();
            return true;
        }

        return false;
    }

    private function login_GET(){
        include (__DIR__.'/../LoginWidget/login.php');
    }

    private function login_POST(){
        //echo '<pre>' . var_export($_POST, true) . '</pre>';
        $user = $this->ValidateAccount($_POST['username'], $_POST['password']);
        $isAccountValid = ($user !== null) ? 'true' : 'false';
        echo "valid account: $isAccountValid<br>";
        die();
    }

    private function register_POST(){
        //echo '<pre>' . var_export($_POST, true) . '</pre>';
        $user = $this->CreateAccount($_POST['username'], $_POST['password'], $_POST['email']);
        $isAccountValid = ($user !== null) ? 'true' : 'false';
        echo "valid account: $isAccountValid<br>";
        die();
    }
}

 ?>
