<?php
$emailing_script = true;

require 'includes.php';
$sent = array();
$output = array();
$output['status'] = 1;
$output['error'] = "";

if(isset($_GET['id'])){
	$id = intval($_GET['id']);
}else{
	$id = null;
}

if(defined('LIMIT_EMAIL') AND LIMIT_EMAIL !== false){
	$limit = intval(LIMIT_EMAIL);
}else{
	$limit = null;
}



if($limit != null AND $id != null){
	if(Cookie::Exists(TABLES_PREFIX . "sent")){
		$sent = unserialize(stripcslashes(Cookie::Get(TABLES_PREFIX . "sent")));
		if(isset($sent[$id]) AND intval($sent[$id]) >= $limit){
			$output['status'] = 0;
			$output['error'] = $tmpl_strings->Get('email_limit_reached');
		}
	}
}



if(!isset($_POST['name']) OR !isset($_POST['email']) OR !isset($_POST['subject']) OR !isset($_POST['message'])){
	$output['status'] = 0;
	$output['error'] = $tmpl_strings->Get('all_field_required');
}elseif(!ValidateEmail($_POST['email'])){
	$output['status'] = 0;
	$output['error'] = $tmpl_strings->Get('invalid_email');
}

if(!isset($_POST['ts']) OR !isset($_COOKIE['token']) OR $_COOKIE['token'] != md5('dfgkjsh456dsght4'.$_POST['ts'])){
	$output['status'] = 0;
	$output['error'] = $tmpl_strings->Get('invalid_form');
}

if($output['status'] == 1){
	
	$to = get_option('admin_email');
	$subject = "[" . $_SERVER['SERVER_NAME'] . "] ".SITE_NAME.":" . $tmpl_strings->Get('article_question');
	$message = $tmpl_strings->Get('article_link') . ": " . $_POST['link'] . "

".$tmpl_strings->Get('name').": " .  $_POST['name'] . "

".$tmpl_strings->Get('email').": " . $_POST['email'] . "

".$tmpl_strings->Get('subject').": " . $_POST['subject'] . "

".$tmpl_strings->Get('message').": " . $_POST['message'];
	
	
	$headers = "From: ".WEBMASTER_EMAIL."" . "\r\n" .
"Reply-To: " .$_POST['email']. "" . "\r\n" .
"X-Mailer: PHP/" . phpversion();
	mail($to,$subject,$message,$headers);
	
	$output['error'] = $tmpl_strings->Get('message_has_been_sent');
	
	if($limit != null AND $id != null){
		if(isset($sent[$id])){
			$sent[$id] = $sent[$id] + 1;
		}else{
			$sent[$id] = 1;
		}
		Cookie::Set(TABLES_PREFIX . "sent", addslashes(serialize($sent)));
	}
}


header("Content-type: text/plain;");
echo json_encode($output);
exit();
