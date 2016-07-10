<?php

abstract class ActionResult {
    public abstract function Output();
}

class JsonResult extends ActionResult{
    private $data;
    public function __construct($data) {
        $this->data = $data;
    }

    public function Output() {
        $json = json_encode($this->data);
        error_log("JsonResult - setting content type to application/json and returning $json");
        header('Content-type: application/json');
        echo $json;
    }
}

?>
