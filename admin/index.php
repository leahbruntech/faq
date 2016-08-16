<?php

require 'includes.php';

if(!IsSignedIn()){
	header('Location: '.BASE_URL.'admin/signin.php');
	exit;
}

$layout = AdminPageLayout('admin-home');
$layout->AddContentById('header_title', '{{ST:home}}');

$categories = get_nested_categories(0, 5);

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
	
}else{
	$rows_html = '<tr><td colspan="4">{{ST:no_items}}</td></tr>';
}

$layout->AddContentById('categories_rows', $rows_html);


$posts = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts ORDER BY id DESC LIMIT 0, 5");

$rows_html = '';
if($posts){
	foreach($posts as $post){
		$row_layout = new Layout('../html/');
		$row_layout->SetContentView('admin-articles-rows');
		$row_layout->AddContentById('id', $post->id);
		$row_layout->AddContentById('title', TrimText(stripslashes($post->title), 42));
		
		$row_layout->AddContentById('likes', NiceNumber($post->likes));
		$row_layout->AddContentById('views', NiceNumber($post->views));
		
		$row_layout->AddContentById('category_name', stripslashes($db->get_var("SELECT name FROM " . TABLES_PREFIX . "categories WHERE id = " . $post->category_id)));
		
		$rows_html .= $row_layout-> ReturnView();
	}
	
}

$layout->AddContentById('posts_rows', $rows_html);

$layout->RenderViewAndExit();
