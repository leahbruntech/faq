<?php

error_reporting(E_ALL ^ E_NOTICE);

require 'db_install.php';
require_once 'php/crypt.php';
require_once 'php/functions.php';

if(preg_match("/(.*)\/update_path\.php/",$_SERVER['SCRIPT_FILENAME'],$matches)){
	$server_path=$matches[1];
	set_option('base_path', $matches[1]);
}

$pageURL = 'http';
if($_SERVER["HTTPS"] == "on"){
	$pageURL .= "s";
}
$pageURL .= "://";
if($_SERVER["SERVER_PORT"] != "80"){
	$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
}else{
	$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
}
if(preg_match("/(.*)\/update_path\.php/",$pageURL,$matches)){
	set_option('base_url', $matches[1]);
}