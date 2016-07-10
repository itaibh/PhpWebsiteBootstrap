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
    private $routeHandlers = array();

    public static function Instance()
    {
        static $instance = null;
        if ($instance === null)
        {
            self::getLogger()->log_debug("creating components manager");
            $instance = new static();
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

    private function isInterfaceDataValid($interface_name, $interface_data){
        if(!interface_exists($interface_name)) {
            self::getLogger()->log_error("didn't find interface '{$interface_name}'");
            return false;
        }

        if (!isset($interface_data['RealizeAs'])) {
            self::getLogger()->log_error("didn't find 'RealizeAs' configuration block for interface '{$interface_name}'");
            return false;
        }

        $component_name = $interface_data['RealizeAs'];
        if (!class_exists($component_name)) {
            self::getLogger()->log_debug("loading component {$component_name}");
            include __DIR__ . "/{$component_name}/init.php";
            if (!class_exists($component_name)) {
                self::getLogger()->log_error("didn't find class {$component_name}, defined as implementation class for interface '{$interface_name}'");
                return false;
            }
        }

        $reflrector = new ReflectionClass($component_name);
        if (!$reflrector->implementsInterface($interface_name)) {
            self::getLogger()->log_error("Class {$component_name}, defined as implementation class for interface '{$interface_name}', does not implement it.");
            return false;
        }

        return true;
    }

    private function initBySettingsBlock($settingsBlock){
        foreach ($settingsBlock as $interface_name => $interface_data) {
            if (!$this->isInterfaceDataValid($interface_name, $interface_data)) {
                continue;
            }

            $component_name = $interface_data['RealizeAs'];
            if (isset($interface_data['Config'])) {
                $config = $interface_data['Config'];
            } else {
                $config = null;
            }
            $this->RegisterDefaultComponent($interface_name, $component_name, $config);
        }
    }

    public function Init()
    {
        $settings = GetComponentsSettings();

        if (function_exists('GetComponentsSettings_Local')){
            $local_settings = GetComponentsSettings_Local();
            $settings = array_replace_recursive($settings, $local_settings);
        }

        if (isset($settings['default'])){
            $this->initBySettingsBlock($settings['default']);
        }

        foreach ($this->defaultComponents as $name => $container) {
            $container->TryInit();
        }
    }

    public function RegisterDefaultComponent($component_interface, $component_name, $init_data)
    {
        $reflrector = new ReflectionClass($component_name);
        if (!$reflrector->implementsInterface($component_interface)) {
            throw new Exception("component '$component_name' does not implement interface '$component_interface'");
        }

        if (!class_exists($component_name)) {
            self::getLogger()->log_info("loading component {$component_name}");
            include __DIR__ . "/{$component_name}/init.php";
        }

        $component = call_user_func($component_name. '::Instance');

        $container = new ComponentContainer($component, $init_data);
        $this->defaultComponents[$component_interface] = $container;

        if ($component instanceof IControllerComponent) {
            $this->routeHandlers[$component->GetRouteName()] = $container;
        }
    }

    public function GetComponent($component_name)
    {
        $container = $this->defaultComponents[$component_name];
        $container->TryInit();
        return $container->component;
    }

    private function getRequestQueryItems($queryString){
        $query = array();
        $queryParts = explode('&', $queryString);
        foreach ($queryParts as $queryItem) {
            $queryItemParts = explode('=',$queryItem);
            if (isset($queryItemParts[1])){
                $query[$queryItemParts[0]] = $queryItemParts[1];
            } else {
                $query[] = $queryItemParts[0];
            }
        }
        return $query;
    }

    public function HandleRequest()
    {
        $reqUri = $_SERVER['REQUEST_URI'];
        $reqUriParts = explode('?', $reqUri);

        if (isset($reqUriParts[1])) {
            $query = $this->getRequestQueryItems($reqUriParts[1]);
        } else {
            $query = array();
        }
        $requestURI = explode('/', $reqUriParts[0]);
        $handlerKey = $requestURI[1];

        if (!isset($this->routeHandlers[$handlerKey])) {
            $handlerKey = null;
        }
       
        $this->routeHandlers[$handlerKey]->component->HandleRequest($requestURI, $query);
    }
}

?>
