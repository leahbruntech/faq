<?php

require 'includes.php';

$layout = FrontendPageLayout('frontend-article');

if(isset($_GET['id'])){
	$id = intval($_GET['id']);
	$post = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE published = 'y' AND id = $id ORDER BY id DESC LIMIT 0,1");
}else{
	if(isset($_GET['a_url'])){
		$post = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE published = 'y' AND url = '".$_GET['a_url']."' ORDER BY id DESC LIMIT 0,1");
	}else{
		Leave(BASE_URL);
	}
}

if(!$post){
	Leave('index.php');
}

$id = intval($post->id);

$layout->AddContentById('header_title', stripslashes($post->title));

$breadcrumbs = '<li class="active">'.stripslashes($post->title).'</li>';
$end = false;
$loops = 1;
$next_cat = intval($post->category_id);
while($end == false AND $loops < 10){
	$category = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "categories WHERE id = $next_cat ORDER BY id DESC LIMIT 0,1");
	if($category){
		$breadcrumbs = '<li><a href="'.Urls('category', $category).'">'.stripslashes($category->name).'</a> <span class="divider">/</span> </li> ' . $breadcrumbs;
		$next_cat = intval($category->parent);
		if(intval($category->parent) == 0){
			$end = true;
		}
	}else{
		$end = true;
	}
	$loops = $loops + 1;
}

$layout->AddContentById('breadcrumbs', ' <li><a href="'.BASE_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li> ' . $breadcrumbs);

$layout->AddContentById('id', $post->id);
$layout->AddContentById('body', make_clickable(stripslashes($post->body)));
$layout->AddContentById('date', date($date_format[DATE_FORMAT]['php'], strtotime($post->date)));

if($post->likes > 1){
	$layout->AddContentById('found_this_helpful', '<span id="found_this_helpful">'.NiceNumber($post->likes).' {{ST:people_found_this_helpful}}</span>');
}elseif($post->likes == 1){
	$layout->AddContentById('found_this_helpful', '<span id="found_this_helpful">1 {{ST:person_found_this_helpful}}</span>');
}else{
	$layout->AddContentById('found_this_helpful', '<span id="found_this_helpful"></span>');
}

if($post->related_categories){
	$related_categories_html = '';
	$related_categories_array =  unserialize($post->related_categories);
	foreach($related_categories_array as $a){
		
		$cat = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "categories WHERE id = $a ORDER BY id DESC LIMIT 0,1");
		if($cat){
			$related_categories_html .= '<p><a href="'.Urls('category', $cat).'">'.stripslashes($cat->name).'</a></p>';
		}
	}
	if($related_categories_html == ''){
		$layout->AddContentById('related_categories_state', 'display:none;');
	}else{
		$layout->AddContentById('related_categories', $related_categories_html);
		$layout->AddContentById('related_articles_offset', ' offset1');
	}
}else{
	$layout->AddContentById('related_categories_state', 'display:none;');
}
	
if($post->related_articles){
	$related_articles_html = '';
	$related_articles_array =  unserialize($post->related_articles);
	foreach($related_articles_array as $a){
		$cat = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = $a ORDER BY id DESC LIMIT 0,1");
		if($cat){
			$related_articles_html .= '<p><a href="'.Urls('article', $cat).'">'.stripslashes($cat->title).'</a></p>';
		}
	}
	if($related_articles_html == ''){
		$layout->AddContentById('related_articles_state', 'display:none;');
	}else{
		$layout->AddContentById('related_articles', $related_articles_html);
	}
}else{
	$layout->AddContentById('related_articles_state', 'display:none;');
}

$layout->AddContentById('article_url', Urls('article', $post));
$layout->AddContentById('share', urlencode(Urls('article', $post)));

$db->update(TABLES_PREFIX . "posts", array('views'=>(1+$post->views)), array('id'=>$id), array("%d"));

$layout->RenderViewAndExit();
