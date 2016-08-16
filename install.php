<?php

error_reporting(E_ALL ^ E_NOTICE);

require 'db_install.php';
require_once 'php/crypt.php';
require_once 'php/functions.php';

$db = new Db;

if(count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "options" )) == 0){
	
	set_option('admin_username', 'admin');
	
	set_option('admin_password', encode_password('pass'));
	
	set_option('admin_email', WEBMASTER_EMAIL);
	
	set_option('site_name', 'Help & Support');
	
	set_option('copyright', '&copy; Help & Support 2012');
	
	set_option('use_sef_urls', 'n');
	
	set_option('time_zone', 'UTC');
	
	set_option('date_format', 1);
	
	set_option('rows_per_page', 10);
	
	if(preg_match("/(.*)\/install\.php/",$_SERVER['SCRIPT_FILENAME'],$matches)){
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
	if(preg_match("/(.*)\/install\.php/",$pageURL,$matches)){
		set_option('base_url', $matches[1]);
	}
	
	echo '<p>Database has been installed.</p>';
	echo '<p>Setting have been initialized.</p>';
	echo '<p>Signin with username: <b>admin</b> and password: <b>pass</b></p>';
	echo '<p>After you <a href="admin/signin.php">signin</a>, please click on the Settings menu and make the changes you need.</p>';
	
}else{
	echo '<p>Database updated.</p>';
}
