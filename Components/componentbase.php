<?php

require_once __DIR__.'/actionresult.php';

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
        $method = $_SERVER['REQUEST_METHOD'];

        $function_name = "{$method}_{$action}";

        if (!method_exists($this, $function_name)) {
            $function_name = $action;
        }

        if (!method_exists($this, $function_name)) {
            return false;
        }

        $return_value = $this->$function_name($path, $query);
        if (isset($return_value)) {
            if ($return_value instanceof ActionResult) {
                $return_value->Output();
            }
        }
        return true;
    }

    protected function View($view_name) {
        include(ROOTPATH."/Views/$view_name.php");
    }
}

?>
