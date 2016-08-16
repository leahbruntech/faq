<?php

require 'includes.php';

if(!IsSignedIn()){
	header('Location: '.BASE_URL.'admin/signin.php');
	exit;
}

$layout = AdminPageLayout('admin-articles-edit');
$layout->AddContentById('header_title', '{{ST:edit_article}}');

if(isset($_GET['id'])){
	$id = intval($_GET['id']);
}else{
	Leave('articles.php');
}

$post = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = $id ORDER BY id DESC LIMIT 0,1");
$layout->AddContentById('id', $post->id);

if(isset($_POST['delete'])){
	$db->query("DELETE FROM " . TABLES_PREFIX . "posts WHERE id = " . $id);
	Leave('articles.php?message=deleted');
}


$categoryQuery = get_nested_categories();
$categories = '';
if($categoryQuery){
	foreach($categoryQuery as $u){
		$categories .= '<option {{ID:selected_category_' . $u->id . '}} value="' . $u->id . '">' . stripslashes($u->name) . '</option>';
	}
	$layout->AddContentById('categories', $categories);
}

$categories2 = '';
if($categoryQuery){
	foreach($categoryQuery as $u){
		$categories2 .= '<lable><input type="checkbox" value="'.$u->id.'" name="related_categories[]" {{ID:related_categories_state_'.$u->id.'}}> '.stripslashes($u->name).'</lable><br/>';
	}
	$layout->AddContentById('related_categories', $categories2);
}

$postsQuery = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts");
$related_articles = '';
if($postsQuery){
	foreach($postsQuery as $u){
		if($u->id != $id){
			$related_articles .= '<lable><input type="checkbox" value="'.$u->id.'" name="related_articles[]" {{ID:related_articles_state_'.$u->id.'}}> '.stripslashes($u->title).'</lable><br/>';
		}
	}
	$layout->AddContentById('related_articles', $related_articles);
}

if(isset($_POST['submit'])){
	$errors = false;
	$values = array();
	$format = array();
	$error_msg = '';
	
	if(isset($_POST['title']) AND $_POST['title'] != ''){
		$layout->AddContentById('title', stripslashes($_POST['title']));
		$values['title'] = $_POST['title'];
		$format[] = "%s";
		
		if($_POST['title'] != $post->title){
			$values['url'] = PostUrlGen($_POST['title']);
			$format[] = "%s";
		}
	}else{
		$errors = true;
		$error_msg .= '{{ST:title_required}} ';
	}
		
	if(isset($_POST['category']) AND $_POST['category'] != ''){
		$layout->AddContentById('selected_category_' .$_POST['category'], 'selected');
		$values['category_id'] = $_POST['category'];
		$format[] = "%d";
	}else{
		$errors = true;
		$error_msg .= '{{ST:category_required}} ';
	}
	
	if(isset($_POST['body']) AND $_POST['body'] != ''){
		$layout->AddContentById('body', stripslashes($_POST['body']));
		$values['body'] = $_POST['body'];
		$format[] = "%s";
	}else{
		$errors = true;
		$error_msg .= '{{ST:body_required}} ';
	}
	
	if(isset($_POST['publish'])){
		$layout->AddContentById('publish_state', 'checked="checked"');
		$values['published'] = 'y';
		$format[] = "%s";
	}else{
		$values['published'] = 'n';
		$format[] = "%s";
	}
	
	if(isset($_POST['related_categories']) AND count($_POST['related_categories']) > 0){
		$values['related_categories'] = serialize($_POST['related_categories']);
		$format[] = "%s";
		foreach($_POST['related_categories'] as $a){
			$layout->AddContentById('related_categories_state_'.$a, 'checked="checked"');
		}
	}else{
		$values['related_categories'] = "";
		$format[] = "%s";
	}
	
	if(isset($_POST['related_articles']) AND count($_POST['related_articles']) > 0){
		$values['related_articles'] = serialize($_POST['related_articles']);
		$format[] = "%s";
		foreach($_POST['related_articles'] as $a){
			$layout->AddContentById('related_articles_state_'.$a, 'checked="checked"');
		}
	}else{
		$values['related_articles'] = "";
		$format[] = "%s";
	}
	
	if(!$errors){
		$db->update(TABLES_PREFIX . "posts", $values, array('id'=>$id), $format);
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_item_has_been_saved}}');
	}else{
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', $error_msg);
	}

}else{
	$layout->AddContentById('title', stripslashes($post->title));
	$layout->AddContentById('body', stripslashes($post->body));
	$layout->AddContentById('selected_category_' . $post->category_id, 'selected="selected"');
	if($post->published == 'y'){
		$layout->AddContentById('publish_state', 'checked="checked"');
	}
	
	if($post->related_categories){
		$related_categories_array =  unserialize($post->related_categories);
		foreach($related_categories_array as $a){
			$layout->AddContentById('related_categories_state_'.$a, 'checked="checked"');
		}
	}
	
	if($post->related_articles){
		$related_articles_array =  unserialize($post->related_articles);
		foreach($related_articles_array as $a){
			$layout->AddContentById('related_articles_state_'.$a, 'checked="checked"');
		}
	}
	
}

$layout->RenderViewAndExit();
