<?php
require_once 'php/config.php';

if(defined('DEVELOPMENT') AND DEVELOPMENT == false){
	error_reporting(E_ALL ^ E_NOTICE);
}

session_start();

require_once "php/ezSQL/shared/ez_sql_core.php";
require_once "php/ezSQL/ez_sql_pdo.php";
require_once 'php/db.php';
require_once 'php/crypt.php';
require_once 'php/string.php';
require_once 'php/layout.php';
require_once 'php/phpmailer.php';
require_once 'php/cookie.php';
require_once 'php/functions.php';

$db = new Db;

LoadSettings();

date_default_timezone_set(TIME_ZONE);

$date_format = array();
$date_format[1] = array('js'=>'dd/mm/yyyy','php'=>'d/m/Y');
$date_format[2] = array('js'=>'dd.mm.yyyy','php'=>'d.m.Y');
$date_format[3] = array('js'=>'dd-mm-yy','php'=>'d-m-y');
$date_format[4] = array('js'=>'yyyy.mm.dd','php'=>'Y.m.d');
$date_format[5] = array('js'=>'d.m.yyyy','php'=>'j.n.Y');
$date_format[6] = array('js'=>'d. month yyyy','php'=>'j. F Y');
$date_format[7] = array('js'=>'d-m-yyyy','php'=>'j-n-Y');
$date_format[8] = array('js'=>'yyyy/mm/dd','php'=>'Y/m/d');
$date_format[9] = array('js'=>'mm/dd/yyyy','php'=>'m/d/Y');
$date_format[10] = array('js'=>'dd/mm yyyy','php'=>'d/m Y');


$tmpl_strings = new StringResource('str/');

if(!isset($emailing_script) OR $emailing_script === false){
	if(isset($_POST))
		$_POST = CleanXSS($_POST);
	if(isset($_GET))
		$_GET = CleanXSS($_GET);
}
