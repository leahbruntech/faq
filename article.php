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

$layout->RenderViewAndExit(false);
?>

<script>
$(document).ready(function(){
	
	var form = $("#contactForm");
	var $id_value = 2;
	
	form.submit(function(){
		if($("#name").val().length < 1 || $("#email").val().length < 1 || $("#subject").val().length < 1 || $("#message").val().length < 1){
			$("#contactMsg").empty();
			$("#contactMsg").append('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>{{ST:error}}!</strong> {{ST:all_field_required}}</div>');
			return false;
		}
		
		var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
		if(!filter.test($("#email").val())){
			$("#contactMsg").empty();
			$("#contactMsg").append('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>{{ST:error}}!</strong> {{ST:invalid_email}}</div>');
			return false;
		}
		
		$('#submit').button('loading');
		$.ajax({type:'POST', url: 'send.php?id='+ $id_value, data:form.serialize(), dataType: "json", success: function(res) {
    				$('#submit').button('reset');
    				if(res.status == 1){
    					$("#contactMsg").empty();
					$("#contactMsg").append('<div class="alert alert-success"><a class="close" data-dismiss="alert">×</a><strong>{{ST:success}}!</strong> '+ res.error +'</div>');
				}else{
    					$("#contactMsg").empty();
					$("#contactMsg").append('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>{{ST:error}}!</strong> '+ res.error +'</div>');
				}
		}});
		return false
	});
	
	var form2 = $("#contactForm2");
	
	form2.submit(function(){
		if($("#name2").val().length < 1 || $("#email2").val().length < 1 || $("#subject2").val().length < 1 || $("#message2").val().length < 1){
			$("#contactMsg2").empty();
			$("#contactMsg2").append('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>{{ST:error}}!</strong> {{ST:all_field_required}}</div>');
			return false;
		}
		
		var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
		if(!filter.test($("#email2").val())){
			$("#contactMsg2").empty();
			$("#contactMsg2").append('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>{{ST:error}}!</strong> {{ST:invalid_email}}</div>');
			return false;
		}
		
		$('#submit2').button('loading');
		$.ajax({type:'POST', url: 'send.php?id='+ $id_value, data:form2.serialize(), dataType: "json", success: function(res) {
    				$('#submit2').button('reset');
    				if(res.status == 1){
    					$("#contactMsg2").empty();
					$("#contactMsg2").append('<div class="alert alert-success"><a class="close" data-dismiss="alert">×</a><strong>{{ST:success}}!</strong> '+ res.error +'</div>');
				}else{
    					$("#contactMsg2").empty();
					$("#contactMsg2").append('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>{{ST:error}}!</strong> '+ res.error +'</div>');
				}
		}});
		return false
	});
	
	$.get("token.php",function(txt){
		form.append('<input type="hidden" name="ts" value="'+txt+'">');
		form2.append('<input type="hidden" name="ts" value="'+txt+'">');
	});
	
	
	
	
	$("#likeButton").click(function(event) {
  		event.preventDefault();
  		$.get("like.php?id="+$id_value,function(txt){
			if(txt.length > 1){
				$("#found_this_helpful").empty();
				$("#found_this_helpful").append(txt);
			}
		});
	});
});
</script>

<?php exit(); ?>


