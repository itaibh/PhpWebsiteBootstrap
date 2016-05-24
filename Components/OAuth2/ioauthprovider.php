<?php

interface IOAuthProvider
{
    public function GetName();
    public function GetLoginUrl($state);
}

?>
