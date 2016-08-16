<?php

require 'includes.php';

if(IsSignedIn() == true){
	header('Location: '.BASE_URL.'admin/');
	exit;
}

$layout = new Layout('../html/');
$layout->SetContentView('admin-signin');
$layout->AddContentById('app_name', SITE_NAME);
$layout->AddContentById('footer_copy', COPYRIGHT);

if(isset($_POST['signin'])){
	if(Clean($_POST['username']) == get_option('admin_username') AND encode_password(Clean($_POST['password'])) == get_option('admin_password')){
		$_SESSION[TABLES_PREFIX.'logged_in'] = true;
		if(isset($_POST['remember_me'])){
			Cookie::Set(TABLES_PREFIX . 'remember_me', 'email='.$_POST['username'].'&hash='.encode_password($_POST['password']));
		}
		header('Location: '.BASE_URL.'admin/');
		exit;
	}else{
		$layout->AddContentById('error', $layout->GetContent('admin-signin-signin-error'));
	}
}

if(isset($_POST['forgot'])){
	if(Clean($_POST['email']) == get_option('admin_email')){
		$new_password = GeneratePassword();
		set_option('admin_password', encode_password($new_password));
		$to = get_option('admin_email');
		$subject = "[" . $_SERVER['SERVER_NAME'] . "] ".SITE_NAME.": " . $tmpl_strings->Get('newpassword');
		$message = $tmpl_strings->Get('your_new_pasword_is') . ": " . $new_password . "
		
" . BASE_URL . "admin/signin.php";
		$from = WEBMASTER_EMAIL;
		$headers = "From: $from";
		ini_set("sendmail_from", $from);	
		mail($to,$subject,$message,$headers);
		$layout->AddContentById('error', $layout->GetContent('admin-signin-forgot-success'));
	}else{
		$layout->AddContentById('error', $layout->GetContent('admin-signin-forgot-error'));
	}
}

$layout->RenderViewAndExit();
