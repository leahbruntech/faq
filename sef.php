<?php

if(isset($_GET['values']) AND $_GET['values'] != ''){
	$requestURI = explode('/', $_GET['values']);
	
	if($requestURI[0] == 'c'){
		$_GET['c_url'] = $requestURI[1];
		require 'category.php';
	}elseif($requestURI[0] == 'a'){
		$_GET['a_url'] = $requestURI[1];
		require 'article.php';
	}else{
		require 'index.php';	
	}
}else{
	require 'index.php';
}

