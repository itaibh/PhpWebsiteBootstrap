<?php
require_once __DIR__.'/componentbase.php';
require_once __DIR__.'/commoninterfaces.php';
require_once __DIR__.'/componentcontainer.php';

class ComponentsManager {

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    private $componentsPerCaller = array();
    private $defaultComponents = array();

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

    private function isInterfaceDataValid($interface_name, $interface_data){
        if(!interface_exists($interface_name)) {
            self::getLogger()->log_error("didn't find interface '{$interface_name}'");
            return false;
        }

        if (!isset($interface_data['RealizeAs'])) {
            self::getLogger()->log_error("didn't find 'RealizeAs' configuration block for interface '{$interface_name}'");
            return false;
        }

        $className = $interface_data['RealizeAs'];
        if (!class_exists($className)) {
            self::getLogger()->log_error("didn't find class {$className}, defined as implementation class for interface '{$interface_name}'");
            return false;
        }

        $reflrector = new ReflectionClass($className);
        if (!$reflrector->implementsInterface($interface_name)) {
            self::getLogger()->log_error("Class {$className}, defined as implementation class for interface '{$interface_name}', does not implement it.");
            return false;
        }

        return true;
    }

    public function Init()
    {
        $data = GetComponentsSettings();
        if (isset($data['default'])){
            $defaultData = $data['default'];
            foreach ($defaultData as $interface_name => $interface_data) {
                if(!$this->isInterfaceDataValid($interface_name, $interface_data)) {
                    continue;
                }

                $component_name = $interface_data['RealizeAs'];
                $config = $interface_data['Config'];
                $this->RegisterDefaultComponent($interface_name, $component_name, $config);
            }
        }
        //$this->RegisterComponents(['MySqlDB','AccountManager','OAuth2']);
    }

    private static function getLogger() {
        static $logger;
		if ($logger === null) {
			$logger = new Logger(__CLASS__);
        }
		return $logger;
	}

    /*private function RegisterComponents($component_names_array)
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
    }*/

    public function RegisterDefaultComponent($component_interface, $component_name, $init_data)
    {
        $reflrector = new ReflectionClass($component_name);
        if (!$reflrector->implementsInterface($interface_name)) {
            throw new Exception("component '$component_name' does not implement interface '$component_interface'");
        }

        self::getLogger()->log_info("loading component {$component_name}");
        include __DIR__ . "/{$component_name}/init.php";
        $component = call_user_func($component_name. '::Instance');

        $this->defaultComponents[$component_interface] = new ComponentContainer($component, $init_data);
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
