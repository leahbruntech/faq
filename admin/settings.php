<?php

require 'includes.php';

if(!IsSignedIn()){
	header('Location: '.BASE_URL.'admin/signin.php');
	exit;
}

$layout = AdminPageLayout('admin-settings');
$layout->AddContentById('header_title', '{{ST:settings}}');

if(is_array($date_format)){
	$dt_str = '';
	foreach($date_format as $k => $v){
		$dt_str .= '<option value="'.$k.'" {{ID:date_format_'.$k.'}}>'.$v['js'].'</option>';
	}
	$layout->AddContentById('dates', $dt_str);
}

if(isset($_POST['submit'])){
	$errors = false;
	$error_msg = '';
	
	if(isset($_POST['admin_username']) AND $_POST['admin_username'] != ''){
		set_option('admin_username', $_POST['admin_username']);
		$layout->AddContentById('admin_username', $_POST['admin_username']);
	}else{
		$errors = true;
		$error_msg .= '{{ST:admin_username_required}} ';
	}
	
	if(isset($_POST['admin_email']) AND $_POST['admin_email'] != ''){
		set_option('admin_email', $_POST['admin_email']);
		$layout->AddContentById('admin_email', $_POST['admin_email']);
	}else{
		$errors = true;
		$error_msg .= '{{ST:admin_email_required}} ';
	}
	
	if(isset($_POST['site_name']) AND $_POST['site_name'] != ''){
		set_option('site_name', $_POST['site_name']);
		$layout->AddContentById('site_name', $_POST['site_name']);
	}else{
		$errors = true;
		$error_msg .= '{{ST:site_name_required}} ';
	}
	
	if(isset($_POST['copyright']) AND $_POST['copyright'] != ''){
		set_option('copyright', $_POST['copyright']);
		$layout->AddContentById('copyright', $_POST['copyright']);
	}else{
		$errors = true;
		$error_msg .= '{{ST:copyright_required}} ';
	}
	
	if(isset($_POST['use_sef_urls']) AND $_POST['use_sef_urls'] != ''){
		set_option('use_sef_urls', 'y');
		$layout->AddContentById('use_sef_urls_state', 'checked="checked"');
	}else{
		set_option('use_sef_urls','n');
	}
	
	if(isset($_POST['time_zone']) AND $_POST['time_zone'] != ''){
		set_option('time_zone', $_POST['time_zone']);
		$layout->AddContentById('time_zone', $_POST['time_zone']);
	}else{
		$errors = true;
		$error_msg .= '{{ST:time_zone_required}} ';
	}
	
	if(isset($_POST['rows_per_page']) AND $_POST['rows_per_page'] != ''){
		set_option('rows_per_page', $_POST['rows_per_page']);
		$layout->AddContentById('rows_per_page', $_POST['rows_per_page']);
	}else{
		$errors = true;
		$error_msg .= '{{ST:rows_per_page_required}} ';
	}
	
	if(isset($_POST['base_path']) AND $_POST['base_path'] != ''){
		set_option('base_path', $_POST['base_path']);
		$layout->AddContentById('base_path', $_POST['base_path']);
	}else{
		$errors = true;
		$error_msg .= '{{ST:base_path_required}} ';
	}
	
	if(isset($_POST['base_url']) AND $_POST['base_url'] != ''){
		set_option('base_url', $_POST['base_url']);
		$layout->AddContentById('base_url', $_POST['base_url']);
	}else{
		$errors = true;
		$error_msg .= '{{ST:base_url_required}} ';
	}
	
	if(isset($_POST['date_format']) AND $_POST['date_format'] != ''){
		set_option('date_format', $_POST['date_format']);
		$layout->AddContentById('date_format_' .$_POST['date_format'], 'selected');
	}else{
		$errors = true;
		$error_msg .= '{{ST:date_format_required}} ';
	}
	
	if(isset($_POST['admin_password']) AND $_POST['admin_password'] != ''){
		if($_POST['admin_password'] != $_POST['admin_password_r']){
			$errors = true;
			$error_msg .= '{{ST:password_not_confirmed}} ';
		}else{
			set_option('admin_password', encode_password($_POST['admin_password']));
		}
	}
	
	if(!$errors){
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:settings_updated}}');
	}else{
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', $error_msg);
	}
}else{
	$layout->AddContentById('admin_username', get_option('admin_username'));
	$layout->AddContentById('admin_email', get_option('admin_email'));
	$layout->AddContentById('site_name', get_option('site_name'));
	$layout->AddContentById('copyright', get_option('copyright'));
	$layout->AddContentById('time_zone', get_option('time_zone'));
	$layout->AddContentById('rows_per_page', get_option('rows_per_page'));
	$layout->AddContentById('base_path', get_option('base_path'));
	$layout->AddContentById('base_url', get_option('base_url'));
	if($dt_option = get_option('date_format')){
		$layout->AddContentById('date_format_' .$dt_option, 'selected');
	}
	if($use_sef_urls = get_option('use_sef_urls')){
		if($use_sef_urls == 'y'){
			$layout->AddContentById('use_sef_urls_state', 'checked="checked"');
		}
	}
}

$layout->RenderViewAndExit();
