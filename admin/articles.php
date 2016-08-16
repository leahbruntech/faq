<?php

require 'includes.php';

if(!IsSignedIn()){
	header('Location: '.BASE_URL.'admin/signin.php');
	exit;
}

$layout = AdminPageLayout('admin-articles');

if(isset($_GET['q']) AND $_GET['q']  != ''){
	$layout->AddContentById('header_title', '{{ST:search}}: ' . $_GET['q']);
	$layout->AddContentById('q', $_GET['q']);
}elseif(isset($_GET['category_id']) AND $_GET['category_id']  != ''){
	$layout->AddContentById('header_title', '{{ST:articles_in_category}}: ' . stripslashes($db->get_var("SELECT name FROM " . TABLES_PREFIX . "categories WHERE id = " . intval($_GET['category_id']))));
}else{
	$layout->AddContentById('header_title', '{{ST:articles}}');
}

if(isset($_GET['page'])){
	$page = intval($_GET['page']);
}else{
	$page = 1;
}
$rows = ROWS_PER_PAGE;

if(isset($_GET['delete'])){
	$db->query("DELETE FROM " . TABLES_PREFIX . "posts WHERE id = " . intval($_GET['delete']));
	Leave('articles.php?message=deleted');
}

if(isset($_GET['message']) AND $_GET['message'] != ''){
	
	if($_GET['message'] == 'deleted'){
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_item_has_been_deleted}}');
	}
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$offset = ($page - 1) * $rows;
if(isset($_GET['q']) AND $_GET['q']  != ''){
	$posts = $conn->query("SELECT * FROM " . TABLES_PREFIX . "posts WHERE ". CreateSearchQuery(clean($_GET['q']), array('title','body')) . " ORDER BY id DESC LIMIT $offset, $rows");
	$number_of_records = count($conn->query("SELECT * FROM " . TABLES_PREFIX . "posts WHERE " . CreateSearchQuery(clean($_GET['q']), array('title','body'))));
}elseif(isset($_GET['category_id']) AND $_GET['category_id']  != ''){
	$posts = $conn->query("SELECT * FROM " . TABLES_PREFIX . "posts WHERE category_id = ".intval($_GET['category_id'])." ORDER BY id DESC LIMIT $offset, $rows");
	$number_of_records = count($conn->query("SELECT * FROM " . TABLES_PREFIX . "posts WHERE category_id = ".intval($_GET['category_id'])."" ));
}else{
	$posts = $conn->query("SELECT * FROM " . TABLES_PREFIX . "posts ORDER BY id DESC LIMIT $offset, $rows");
	$number_of_records = count($conn->query("SELECT * FROM " . TABLES_PREFIX . "posts" ));
}
$number_of_pages = ceil( $number_of_records / $rows );

$rows_html = '';
if($number_of_records > 0){
	while($row = $posts->fetch_assoc()) {
		$row_layout = new Layout('../html/');
		$row_layout->SetContentView('admin-articles-rows');
		$row_layout->AddContentById('id', $row["id"]);
		$row_layout->AddContentById('title', TrimText(stripslashes($row["title"]), 42));
		
		$row_layout->AddContentById('likes', NiceNumber($row["likes"]));
		$row_layout->AddContentById('views', NiceNumber($row["views"]));
		
		$row_layout->AddContentById('category_name', stripslashes($db->get_var("SELECT name FROM " . TABLES_PREFIX . "categories WHERE id = " . $row["category_id"])));
		
		$rows_html .= $row_layout-> ReturnView();
	}
	
	if($number_of_records>$rows){
		if(isset($_GET['q']) AND $_GET['q']  != ''){
			$pagination = Paginate('articles.php?q='.urlencode($_GET['q']), $page, $number_of_pages, true, 3);
		}elseif(isset($_GET['category_id']) AND $_GET['category_id']  != ''){
			$pagination = Paginate('articles.php?category_id='.$_GET['category_id'], $page, $number_of_pages, true, 3);
		}else{
			$pagination = Paginate('articles.php', $page, $number_of_pages, false, 3);
		}
		$layout->AddContentById('pagination', $pagination);
	}
	
}else{
	if(isset($_GET['q']) AND $_GET['q']  != ''){
		$rows_html = '<tr><td colspan="5">{{ST:no_items_match_search}}</td></tr>';
	}else{
		$rows_html = '<tr><td colspan="5">{{ST:no_items}}</td></tr>';
	}
}



$layout->AddContentById('rows', $rows_html);

$layout->RenderViewAndExit();
