<?php

require 'includes.php';

if(!IsSignedIn()){
	header('Location: '.BASE_URL.'admin/signin.php');
	exit;
}

$layout = AdminPageLayout('admin-categories-new');
$layout->AddContentById('header_title', '{{ST:add_new_category}}');

$categoryQuery = get_nested_categories();
$categories = '';
if($categoryQuery){
	foreach($categoryQuery as $u){
		$categories .= '<option {{ID:selected_category_' . $u->id . '}} value="' . $u->id . '">' . $u->name . '</option>';
	}
	$layout->AddContentById('categories', $categories);
}

if(isset($_GET['message']) AND $_GET['message'] != ''){
	
	if($_GET['message'] == 'deleted'){
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_item_has_been_deleted}}');
	}
	
	if($_GET['message'] == 'saved'){
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_item_has_been_saved}}');
	}
}

if(isset($_POST['submit'])){
	$errors = false;
	$values = array();
	$format = array();
	$error_msg = '';
	
	if(isset($_POST['name']) AND $_POST['name'] != ''){
		$layout->AddContentById('name', $_POST['name']);
		$check_name = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "categories WHERE name = '" . $_POST['name'] . "' ORDER BY id DESC LIMIT 0,1");
		if($check_name){
			$errors = true;
			$error_msg .=  '{{ST:name_already_in_use}} ';
		}else{
			$values['name'] = clean($_POST['name']);
			$format[] = "%s";
			
			$values['url'] = CategoryUrlGen($_POST['name']);
			$format[] = "%s";
		}
	}else{
		$errors = true;
		$error_msg .= '{{ST:name_required}} ';
	}
	
	if(isset($_POST['description']) AND $_POST['description'] != ''){
		$layout->AddContentById('description', $_POST['description']);
		$values['description'] =clean( $_POST['description']);
		$format[] = "%s";
	}else{
		$values['description'] = "";
		$format[] = "%s";
	}
	
	if(isset($_POST['parent']) AND $_POST['parent'] != ''){
		$layout->AddContentById('selected_category_' . $_POST['parent'], 'selected="selected"');
		$values['parent'] = $_POST['parent'];
		$format[] = "%s";
	}
	
	if(isset($_POST['order_by']) AND intval($_POST['order_by']) != 0){
		$layout->AddContentById('order_by', $_POST['order_by']);
		$values['order_by'] = intval($_POST['order_by']);
		$format[] = "%d";
	}else{
		$values['order_by'] = 9999;
		$format[] = "%d";
	}
	
	
	if(!$errors){
		
		if($db->insert(TABLES_PREFIX . "categories", $values, $format)){
			Leave('categories_new.php?message=saved');
		}else{
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-error');
			$layout->AddContentById('alert_heading', '{{ST:error}}!');
			$layout->AddContentById('alert_message', '{{ST:unknow_error_try_again}}');
		}
	}else{
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', $error_msg);
	}
}

$layout->RenderViewAndExit();
