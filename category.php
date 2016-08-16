<?php

require 'includes.php';

$layout = FrontendPageLayout('frontend-category');

if(isset($_GET['id'])){
	$id = intval($_GET['id']);
	$category = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "categories WHERE id = $id ORDER BY id DESC LIMIT 0,1");
}else{
	if(isset($_GET['c_url'])){
		$category = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "categories WHERE url = '".$_GET['c_url']."' ORDER BY id DESC LIMIT 0,1");
	}else{
		Leave(BASE_URL);
	}
}


if(!$category){
	Leave(BASE_URL);
}

$id = intval($category->id);


$children_ids = get_category_children_id($id);
$children_ids[] = $id;

$layout->AddContentById('header_title', stripslashes($category->name));

$breadcrumbs = '<li class="active">'.stripslashes($category->name).'</li>';
$end = false;
$loops = 1;
$next_cat = intval($category->parent);
while($end == false AND $loops < 10){
	$categoryx = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "categories WHERE id = $next_cat ORDER BY id DESC LIMIT 0,1");
	if($categoryx){
		$breadcrumbs = '<li><a href="'.Urls('category', $categoryx).'">'.stripslashes($categoryx->name).'</a> <span class="divider"></span> </li> ' . $breadcrumbs;
		$next_cat = intval($categoryx->parent);
		if(intval($categoryx->parent) == 0){
			$end = true;
		}
	}else{
		$end = true;
	}
	$loops = $loops + 1;
}

$layout->AddContentById('breadcrumbs', ' <li><a href="'.BASE_URL.'">{{ST:home}}</a> <span class="divider"></span> </li> ' . $breadcrumbs);

$categories = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "categories WHERE parent = $id ORDER BY order_by ASC, name ASC");

if($categories){
	$categories_html = '';
	foreach($categories as $c){
		$categories_html .= '<h4 style="float: left; width: 25%;"><a title="'.stripslashes($c->name).'" href="'.Urls('category', $c).'">'.TrimText(stripslashes($c->name), 20).'</a></h4>';	
	}
	$layout->AddContentById('subcategories', $categories_html);
}else{
	$layout->AddContentById('subcategories', '{{ST:no_subcategories}}');
}


if(isset($_GET['page'])){
	$page = intval($_GET['page']);
}else{
	$page = 1;
}
$rows = ROWS_PER_PAGE;


$offset = ($page - 1) * $rows;

$posts = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE published = 'y' AND category_id IN (".implode(",", $children_ids).") ORDER BY likes DESC, title ASC LIMIT $offset, $rows");
$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE published = 'y' AND category_id IN (".implode(",", $children_ids).")"));

$number_of_pages = ceil( $number_of_records / $rows );

$rows_html = '';
if($posts){
	foreach($posts as $post){
		$row_layout = new Layout('html/');
		$row_layout->SetContentView('frontend-category-rows');
		$row_layout->AddContentById('id', $post->id);
		$row_layout->AddContentById('title', TrimText(stripslashes($post->title), 40));
		
		$row_layout->AddContentById('body', TrimText(stripslashes($post->body), 300));
		
		if($post->likes > 1){
			$row_layout->AddContentById('found_this_helpful', NiceNumber($post->likes).' {{ST:people_found_this_helpful}}');
		}elseif($post->likes == 1){
			$row_layout->AddContentById('found_this_helpful', '1 {{ST:person_found_this_helpful}}');
		}
		
		$cat = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "categories WHERE id = " . $post->category_id . " ORDER BY id DESC LIMIT 0,1");
		if($cat){
			$row_layout->AddContentById('category_url', Urls('category', $cat));
			$row_layout->AddContentById('category_id', $post->category_id);
		
			$row_layout->AddContentById('category_name', stripslashes($cat->name));
		}
		$row_layout->AddContentById('article_url', Urls('article', $post));
		
		$rows_html .= $row_layout-> ReturnView();
	}
	
	if($number_of_records>$rows){
		$pagination = Paginate(Urls('category', $category), $page, $number_of_pages, (SEF_URLS === false), 3);
		$layout->AddContentById('pagination', $pagination);
	}
	
}else{
	$rows_html = '<p>{{ST:no_articles}}</p>';
}



$layout->AddContentById('rows', $rows_html);


$layout->RenderViewAndExit();
