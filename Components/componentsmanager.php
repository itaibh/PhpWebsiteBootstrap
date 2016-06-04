<?php

interface IComponent {
    public function Init();
    public function TryHandleRequest();
}

abstract class ComponentBase implements IComponent{
    public abstract function Init();

    public function TryHandleRequest()
    {
        return false;
    }

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
        }
        return $instance;
    }

    public function Init()
    {
        $this->RegisterComponents(['MySqlDB','AccountManager','OAuth2']);
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
        foreach ($this->components as $name => $container)
        {
            $container->TryInit();
            if ($container->component->TryHandleRequest())
            {
                break;
            }
        }
    }
}

?>
