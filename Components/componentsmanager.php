<?php

interface IComponent {
    public function GetName();
}

class ComponentsManager {

    private __construct() {
        $this->RegisterComponents(['Database','Accounts','OAuth2']);
    }

    private $components = array();

    public static function Instance()
    {
        static $instance = null;
        if ($instance === null)
        {
            $instance = new static();
        }
        return $instance;
    }

    private function RegisterComponents($component_names_array)
    {
        foreach ($component_names_array as $component_name)
        {
            include __DIR__ . "/Components/{$component_name}/init.php";
            $component_name
        }
    }

    public function RegisterComponent($component)
    {
        $this->components[$component->GetName()] = $component;

    }

    public function GetComponent($component_name)
    {
        return $this->components[$component_name];
    }

    public function HandleRequest()
    {
        $reqUri = $_SERVER['REQUEST_URI'];
        $reqUriParts =  explode('?', $reqUri);
        global $requestURI;
        $requestURI = explode('/', $reqUriParts[0]);

        if ($requestURI[1] == 'oauth')
        {
            $lastPart = explode('?',$requestURI[2]);
            include "./Components/OAuth2/Providers/{$lastPart[0]}.php";
            die;
        }
    }
}

?>
