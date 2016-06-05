<?php

require_once __DIR__ . '/../ioauthprovider.php';

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
        $ep = $this->GetDiscoveryDocument()->authorization_endpoint;
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

    private function validateRequest()
    {
        if (isset($_GET["error"]))
        {
            $error = $_GET["error"];
            throw new Exception("Google OAuth Error: " . $error);
        }
    }

    private function parseGoogleOAuthResponse($resp)
    {
        //echo $resp;
        $respJson = json_decode($resp);
        $idTokenParts = explode('.',$respJson->id_token);
        $idTokenParsed = base64_decode($idTokenParts[1]);
        //echo $idTokenParsed;
        $idTokenJson = json_decode($idTokenParsed);
        return $idTokenJson;
    }

    private function getOAuthTokenFromGoogle($code)
    {
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
        $idTokenJson = $this->parseGoogleOAuthResponse($resp);
        return $idTokenJson;
    }

    private function extractOAuthDataFromRequest()
    {
        $this->validateRequest();

        if (isset($_GET["code"]))
        {
            $code = $_GET["code"];
        }

        $idTokenJson = $this->getOAuthTokenFromGoogle($code);
        return $idTokenJson;
    }

    public function GetOAuthDataFromRequest()
    {
        $idTokenJson = $this->extractOAuthDataFromRequest();
        return new OAuthData($idTokenJson->sub, $idTokenJson->email, $idTokenJson->email_verified);
    }

    public function HandleRequest()
    {
        $idTokenJson = $this->extractOAuthDataFromRequest();

        // TODO - 1. find user in DB by google oauth user token.
        // TODO - 2. if no user found:
        // TODO - 2.1. check if user exists in DB by its email (don't forget to check if it is verified)
        // TODO - 2.2. if user found:
        // TODO - 2.2.1. insert oauth data that matches the user.
        // TODO - 2.3. else:
        // TODO - 2.3.1. insert a new user with no username and no password to DB.
        // TODO - 2.3.2. insert oauth data that matches the user.
        // TODO - 3. else:
        // TODO - 3.1. load the user (?)
        /*if ($row == null)
        {
            if (isset($idTokenJson->email) && $idTokenJson->email_verified === true)
            {
                if ($stmt->rowCount() == 0)
                {
                    $apiKey = $this->api_key;
                    $handle = fopen("https://www.googleapis.com/plus/v1/people/{$idTokenJson->sub}/openIdConnect?key={$apiKey}", "rb");
                    $contents = stream_get_contents($handle);
                    $personData = json_decode($contents);
                }
            }
        }
        else
        {
            //$user = User::CreateFromMapArray($row);
        }*/

        $state = $_GET['state'];
        parse_str($state, $stateArgs);
        $url = $stateArgs['url'];

        $_SESSION['LoggedInUser'] = $user;
        header("Location: " . host() . $url);
    }
}
?>
