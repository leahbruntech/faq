<?php

require 'includes.php';

if(!IsSignedIn()){
	header('Location: '.BASE_URL.'admin/signin.php');
	exit;
}

$layout = AdminPageLayout('admin-categories');
$layout->AddContentById('header_title', '{{ST:categories}}');

if(isset($_GET['page'])){
	$page = intval($_GET['page']);
}else{
	$page = 1;
}
$rows = ROWS_PER_PAGE;

if(isset($_GET['message']) AND $_GET['message'] != ''){
	
	if($_GET['message'] == 'deleted'){
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_item_has_been_deleted}}');
	}
}


$offset = ($page - 1) * $rows;
$categories = get_nested_categories($offset, $rows);
$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "categories" ));
$number_of_pages = ceil( $number_of_records / $rows );

$rows_html = '';
if($categories){
	foreach($categories as $category){
		$row_layout = new Layout('../html/');
		$row_layout->SetContentView('admin-categories-rows');
		$row_layout->AddContentById('id', $category->id);
		$row_layout->AddContentById('name', stripslashes($category->name));
		if(intval($category->order_by) != 9999){
			$row_layout->AddContentById('order_by', intval($category->order_by));
		}
		
		$articles = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE category_id = " . intval($category->id) ));
		if($articles > 0){
			$row_layout->AddContentById('articles', '<a href="articles.php?category_id='.$category->id.'">(' . $articles . ')</a>');
		}else{
			$row_layout->AddContentById('articles', $articles);
		}
		
		$rows_html .= $row_layout-> ReturnView();
	}
	
	if($number_of_records>$rows){
		$pagination = Paginate('categories.php', $page, $number_of_pages, false, 3);
		$layout->AddContentById('pagination', $pagination);
	}
	
}else{
	$rows_html = '<tr><td colspan="4">{{ST:no_items}}</td></tr>';
}



$layout->AddContentById('rows', $rows_html);

$layout->RenderViewAndExit();
