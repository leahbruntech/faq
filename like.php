<?php

require 'includes.php';
$liked = array();
if(Cookie::Exists(TABLES_PREFIX . "likes")){
	$liked = unserialize(stripcslashes(Cookie::Get(TABLES_PREFIX . "likes")));
	if(in_array($_GET['id'], $liked)){
		exit();
	}
}

if(isset($_GET['id'])){
	$id = intval($_GET['id']);
	
	$post = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = $id ORDER BY id DESC LIMIT 0,1");
	if($post){
		$db->update(TABLES_PREFIX . "posts", array('likes'=>(1+$post->likes)), array('id'=>$id), array("%d"));
		
		$liked[] = $id;
		
		Cookie::Set(TABLES_PREFIX . "likes", addslashes(serialize($liked)));
		
		$likes =  (1+$post->likes);
		if($likes > 1){
			echo NiceNumber($likes) . ' ' . $tmpl_strings->Get('people_found_this_helpful');
		}elseif($likes == 1){
			echo '1 ' . $tmpl_strings->Get('person_found_this_helpful');
		}
	}
}
exit();
