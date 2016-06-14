<?php
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
    private $init_data = null;
    public function __construct($component, $init_data)
    {
        $this->component = $component;
        $this->init_data = $init_data;
    }

    public function TryInit(){
        if (!$this->initialized) {
            self::getLogger()->log_info('initializing component ' . get_class($this->component));
            $this->component->Init($this->init_data);
            $this->initialized = true;
        }
    }
}
?>
