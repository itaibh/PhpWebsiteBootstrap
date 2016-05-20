<?php

function getDbSettings()
{
    if ($_SERVER['HTTP_HOST']=='127.0.0.1' || $_SERVER['HTTP_HOST']=='localhost' || $_SERVER['HTTP_HOST']=='10.0.0.1')
    {
        return [
            "db_host" => "localhost:3306", //MySQL55
            "db_username" => "root",
            "db_password" => "root",
            "db_name" => "PhpWebsiteBootstrapDemo",
            "db_prefix" => ""
        ];
    }
    else
    {
        return [
            "db_host" => "localhost:3306", //MySQL55
            "db_username" => "root",
            "db_password" => "root",
            "db_name" => "PhpWebsiteBootstrapDemo",
            "db_prefix" => ""
        ];
    }
}
?>
