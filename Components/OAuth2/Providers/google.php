<?php

require_once __DIR__ . '/../oauthmanager.php';

class GoogleOAuthProvider implements IOAuthProvider
{
    public function __construct($client_id, $client_secret, $api_key) {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->api_key = $api_key;
    }

    private $discovery_document = null;
    private $client_id = '';
    private $client_secret = '';
    private $api_key = '';

    function GetName() { return "google"; }

    function GetLoginUrl($state){
        $ep = this->GetDiscoveryDocument()->authorization_endpoint;
        $redirect_url = 'http://'.$_SERVER['SERVER_NAME'].'/oauth/google';
        $url = "{$ep}?scope=profile%20email&state={$state}&redirect_uri={$redirect_url}&response_type=code&client_id={$this->client_id}";
        return $url;
    }

    private function GetDiscoveryDocument(){
        if ($this->discovery_document == null)
        {
            $this->UpdateDiscoveryDocument();
        }
        return $this->discovery_document;
    }

    private function UpdateDiscoveryDocument(){
        $handle = fopen("https://accounts.google.com/.well-known/openid-configuration", "rb");
        $contents = stream_get_contents($handle);
        $this->discovery_document = json_decode($contents);
        fclose($handle);
    }

    public function HandleRequest()
    {
        $state = $_GET["state"];
        parse_str($state, $stateArgs);

        if (isset($_GET["error"]))
        {
            $error = $_GET["error"];
            die;
        }

        if (isset($_GET["code"]))
        {
            $code = $_GET["code"];
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->GetDiscoveryDocument()->token_endpoint,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS =>
                'code='.$code.
                '&client_id='.$this->client_id.
                '&client_secret='.$this->client_secret.
                '&redirect_uri=http://'.$_SERVER['SERVER_NAME'].'/oauth/google'.
                '&grant_type=authorization_code'
            ));
        $resp = curl_exec($curl);
        curl_close($curl);
        //echo $resp;
        $respJson = json_decode($resp);
        $idTokenParts = explode('.',$respJson->id_token);
        $idTokenParsed = base64_decode($idTokenParts[1]);
        //echo $idTokenParsed;
        $idTokenJson = json_decode($idTokenParsed);

        $dbh = get_db();
        $db_prefix = constant('db_prefix');
        $stmt = $dbh->prepare("SELECT * FROM `{$db_prefix}Users` AS Users WHERE Users.`Google ID Token` = ?");
        $stmt->execute(array($idTokenJson->sub));
        $row = $stmt->fetch();

        if ($row == null)
        {
            if (isset($idTokenJson->email) && $idTokenJson->email_verified === true)
            {
                //$user = User::QueryByEmail($idTokenJson->email);
                //$stmt = $dbh->prepare("UPDATE `{$db_prefix}Users` SET `Google ID Token`=? WHERE `User Email`=?");
                //$stmt->execute(array($idTokenJson->sub, $idTokenJson->email));
                if ($stmt->rowCount() == 0)
                {
                    $apiKey = $this->api_key;
                    $handle = fopen("https://www.googleapis.com/plus/v1/people/{$idTokenJson->sub}/openIdConnect?key={$apiKey}", "rb");
                    $contents = stream_get_contents($handle);
                    $personData = json_decode($contents);

                    /*$user = User::CreateOnDB($idTokenJson->email,
                                     $personData->given_name,
                                     $personData->family_name,
                                     $personData->name,
                                     str_replace('sz=50','sz=32', $personData->picture),
                                     str_replace('sz=50','sz=128',$personData->picture),
                                     $idTokenJson->sub);
                    */
                    $_SESSION['LoggedInUser'] = $user;
                    header("Location: " . host() . $stateArgs["url"]);
                }
                else
                {
                    $_SESSION['LoggedInUser'] = $user;
                    header("Location: " . host() . $stateArgs["url"]);
                }
            }
        }
        else
        {
            $user = User::CreateFromMapArray($row);
            $_SESSION['LoggedInUser'] = $user;
            header("Location: " . host() . $stateArgs["url"]);
        }
    }
}
?>
