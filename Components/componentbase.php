<?php

interface IComponent {
    public function Init($init_data);
}

interface IControllerComponent extends IComponent {
    public function GetRouteName();
    public function HandleRequest($path, $query);
}

abstract class ComponentBase implements IComponent{
    public abstract function Init($init_data);

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

abstract class ControllerComponentBase extends ComponentBase implements IControllerComponent {
    public abstract function GetRouteName();
    public function HandleRequest($path, $query) {
        $action = $path[2];
        if (method_exists($this, $action)) {
            $this->$action();
            return;
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $function_name = "{$action}_{$method}";
        if (method_exists($this, $function_name)) {
            $this->$function_name();
            return;
        }
    }
}

?>
