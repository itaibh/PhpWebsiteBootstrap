<?php
class Logger{
    function __construct($prefix){
        $this->m_prefix = $prefix;
    }

    private $m_prefix;
    public function log_info($message){
        error_log('[INFO] ' . $this->m_prefix. ' - ' . $message);
    }

    public function log_error($message) {
        error_log('[ERROR] ' . $this->m_prefix . ' - ' . $message);
    }
}
?>
