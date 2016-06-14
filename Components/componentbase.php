<?php

interface IComponent {
    public function Init($init_data);
    public function TryHandleRequest();
}

abstract class ComponentBase implements IComponent{
    public abstract function Init($init_data);

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

?>
