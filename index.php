<?php

require 'includes.php';

$layout = FrontendPageLayout('frontend-home');
$layout->AddContentById('header_title', '{{ST:home}}');

$layout->AddContentById('breadcrumbs', ' <li class="active">{{ST:home}}</li>');

$categories = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "categories WHERE parent = 0 ORDER BY order_by ASC, name ASC");

if($categories){
	$rows_html = '';
	foreach($categories as $c){
		$row_layout = new Layout('html/');
		$row_layout->SetContentView('frontend-home-rows');
		$row_layout->AddContentById('id', $c->id);
		$row_layout->AddContentById('name', stripslashes($c->name));
		$row_layout->AddContentById('description', stripslashes($c->description));
		
		$row_layout->AddContentById('category_url', Urls('category', $c));
		
		$children = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "categories WHERE parent = ".$c->id." ORDER BY order_by ASC, name ASC LIMIT 0,12");
		$categories_html = '';
		if($children){
			foreach($children as $ch){
				$categories_html .= '<div class="span2"><p><a title="'.stripslashes($ch->name).'" href="'.Urls('category', $ch).'">'.TrimText(stripslashes($ch->name), 20).'</a></p></div>';	
			}
			$row_layout->AddContentById('articles_span', 'span5 ');
		}else{
			$row_layout->AddContentById('subcategories_state', 'display: none;');
			$row_layout->AddContentById('articles_span', 'span6 ');
		}
		$row_layout->AddContentById('subcategories', $categories_html);
		
		
		$children_ids = get_category_children_id($c->id);
		$children_ids[] = $c->id;
		$posts = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE published = 'y' AND category_id IN (".implode(",", $children_ids).") ORDER BY likes DESC, title ASC LIMIT 0, 4");
		$posts_html = '';
		if($posts){
			foreach($posts as $p){
				$posts_html .= '<p><a title="'.stripslashes($p->title).'" href="'.Urls('article', $p).'">'.TrimText(stripslashes($p->title),60).'</a></p>';
			}
			$row_layout->AddContentById('articles', $posts_html);
		}else{
			$row_layout->AddContentById('articles', '<p>{{ST:no_articles}}</p>');
		}
		
		
		$rows_html .= $row_layout->ReturnView();
	}
	
	$layout->AddContentById('rows', $rows_html);
	
}else{
	$layout->AddContentById('rows', '<p>{{ST:no_items}}</p>');
}

$layout->RenderViewAndExit();
