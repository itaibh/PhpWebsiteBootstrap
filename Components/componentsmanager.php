<?php

interface IComponent {
    public function Init();
}

abstract class ComponentBase implements IComponent{
    public abstract function Init();

    public static function Instance()
    {
        static $instance = null;
        if ($instance === null)
        {
            $instance = new static();
        }
        return $instance;
    }

    protected static function getLogger() {
        static $logger;
        if ($logger === null) {
            $logger = new Logger(get_called_class());
        }
        return $logger;
    }
}

class ComponentContainer {
    private static function getLogger() {
        static $logger;
        if ($logger === null) {
            $logger = new Logger(__CLASS__);
        }
        return $logger;
    }

    public $component = null;
    public $initialized = false;
    public function __construct($component)
    {
        $this->component = $component;
    }

    public function TryInit(){
        if (!$this->initialized) {
            self::getLogger()->log_info('initializing component ' . get_class($this->component));
            $this->component->Init();
            $this->initialized = true;
        }
    }
}

class ComponentsManager {

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    private $components = array();

    public static function Instance()
    {
        static $instance = null;
        if ($instance === null)
        {
            self::getLogger()->log_info("creating components manager");
            $instance = new static();
            $instance->RegisterComponents(['Database','AccountManager','OAuth2']);
        }
        return $instance;
    }

    private static function getLogger() {
        static $logger;
		if ($logger === null) {
			$logger = new Logger(__CLASS__);
        }
		return $logger;
	}

    private function RegisterComponents($component_names_array)
    {
        foreach ($component_names_array as $component_name)
        {
            self::getLogger()->log_info("loading component {$component_name}");
            include __DIR__ . "/{$component_name}/init.php";
            $component = call_user_func($component_name. '::Instance');
            $this->RegisterComponent($component);
        }

        foreach ($this->components as $name => $container) {
            $container->TryInit();
        }
    }

    public function RegisterComponent($component)
    {
        $this->components[get_class($component)] = new ComponentContainer($component);
    }

    public function GetComponent($component_name)
    {
        $container = $this->components[$component_name];
        $container->TryInit();
        return $container->component;
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
