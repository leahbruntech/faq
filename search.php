<?php

require 'includes.php';

$layout = FrontendPageLayout('frontend-search');

$layout->AddContentById('header_title', '{{ST:search}}: ' . $_GET['q']);
$layout->AddContentById('q', $_GET['q']);

$layout->AddContentById('breadcrumbs', ' <li><a href="'.BASE_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:search}}: '.$_GET['q'].'</li>');

if(isset($_GET['page'])){
	$page = intval($_GET['page']);
}else{
	$page = 1;
}
$rows = ROWS_PER_PAGE;


$offset = ($page - 1) * $rows;

$posts = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE published = 'y' AND ". CreateSearchQuery(clean($_GET['q']), array('title','body')) . " ORDER BY likes DESC, title ASC LIMIT $offset, $rows");
$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE published = 'y' AND " . CreateSearchQuery(clean($_GET['q']), array('title','body'))));

$number_of_pages = ceil( $number_of_records / $rows );

$rows_html = '';
if($posts){
	foreach($posts as $post){
		$row_layout = new Layout('html/');
		$row_layout->SetContentView('frontend-search-rows');
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
		$pagination = Paginate('search.php?q='.urlencode($_GET['q']), $page, $number_of_pages, true, 3);
		$layout->AddContentById('pagination', $pagination);
	}
	
}else{
	$rows_html = '<p>{{ST:no_items_match_search}}</p>';
}



$layout->AddContentById('rows', $rows_html);

$layout->RenderViewAndExit();
