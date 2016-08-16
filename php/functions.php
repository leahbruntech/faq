<?php

function IsSignedIn(){
	if(isset($_SESSION[TABLES_PREFIX.'logged_in']) AND $_SESSION[TABLES_PREFIX.'logged_in'] == true){
		return true;
	}elseif(Cookie::Exists(TABLES_PREFIX . 'remember_me')){
		parse_str(Cookie::Get(TABLES_PREFIX . 'remember_me'));
		$user_details = clean(array('email'=>$email,'hash'=>$hash));
		if($user_details['email'] == get_option('admin_username') AND $user_details['hash'] == get_option('admin_password')){
			$_SESSION[TABLES_PREFIX.'logged_in'] = true;
			Cookie::Set(TABLES_PREFIX . 'remember_me', 'email='.$user_details['email'].'&hash='.$user_details['hash']);
			return true;
		}
	}
	return false;
}

function LoadSettings(){
	if($site_name = get_option('site_name')){
		define('SITE_NAME', $site_name);
	}else{
		define('SITE_NAME', 'Gallery Manager');
	}
	
	if($copyright = get_option('copyright')){
		define('COPYRIGHT', $copyright);
	}else{
		define('COPYRIGHT', '&copy; Gallery Manager 2012');
	}
	
	if($use_sef_urls = get_option('use_sef_urls')){
		if($use_sef_urls == 'y'){
			define('SEF_URLS', true);
		}else{
			define('SEF_URLS', false);
		}
	}else{
		define('SEF_URLS', false);
	}
	
	if($time_zone = get_option('time_zone')){
		define('TIME_ZONE', $time_zone);
	}else{
		define('TIME_ZONE', 'Europe/Berlin');
	}
	
	if($date_format = get_option('date_format')){
		define('DATE_FORMAT', intval($date_format));
	}else{
		define('DATE_FORMAT', 1);
	}
	
	if($rows_per_page = get_option('rows_per_page')){
		define('ROWS_PER_PAGE', $rows_per_page);
	}else{
		define('ROWS_PER_PAGE', 10);
	}
	
	if($base_path = get_option('base_path')){
		define('BASE_PATH', $base_path . '/');
	}
	
	if($base_url = get_option('base_url')){
		define('BASE_URL', $base_url . '/');
	}
}

function clean($str) {
	if(is_array($str)){
		$return = array();
		foreach($str as $k=>$v){
			$return[clean($k)] = clean($v);
		}
		return $return;
	}else{
		$str = @trim($str);
		if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}
		return mres($str);
	}
}

function cleanXSS($str) {
	if(is_array($str)){
		$return = array();
		foreach($str as $k=>$v){
			if($k != 'body'){
				$return[cleanXSS($k)] = cleanXSS($v);
			}else{
				$return[$k] = $v;
			}
		}
		return $return;
	}else{
		$str = @trim($str);
		$str = preg_replace('#<script(.*?)>(.*?)</script(.*?)>#is', '', $str);
		$str = preg_replace('#<style(.*?)>(.*?)</style(.*?)>#is', '', $str);
		if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}
		return mres($str);
	}
}

function mres($value)
{
    $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
    $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

    return str_replace($search, $replace, $value);
}

function Urls($type = 'article', $object){
	if($type == 'article'){
		if(SEF_URLS === true){
			return BASE_URL . 'a/' . $object->url . '/';
		}else{
			return BASE_URL . 'article.php?id=' . $object->id;
		}
	}else{
		if(SEF_URLS === true){
			return BASE_URL . 'c/' . $object->url . '/';
		}else{
			return BASE_URL . 'category.php?id=' . $object->id;
		}
	}
}

function get_option($key){
	global $db;
	$value = $db->get_var("SELECT option_value FROM " . TABLES_PREFIX . "options WHERE option_key = '" . $key."'");
	if($value){
		return stripslashes($value);
	}else{
		return "";
	}
}

function set_option($key, $value = null){
	global $db;
	
	if($value == null){
		$db->query("DELETE FROM " . TABLES_PREFIX . "options WHERE option_key = '" . $key ."'");
		return true;
	}
	
	$values = array('option_value'=>$value);
	if($db->get_row("SELECT * FROM " . TABLES_PREFIX . "options WHERE option_key = '" . $key ."' ORDER BY id DESC LIMIT 0,1")){
		$db->update(TABLES_PREFIX . "options", $values, array('option_key'=>$key), array("%s"));
	}else{
		$values['option_key'] = $key;
		$db->insert(TABLES_PREFIX . "options", $values, array("%s"));
	}
	return true;
}

function CategoryUrlGen($name){
	$db = new Db;
	
	$url = Slug($name);
	
	$check = $db->get_var("SELECT id FROM " . TABLES_PREFIX . "categories WHERE url = '$url'");
	if($check){
		for ($i = 2; $i < 300; $i++){			
			$url = $url . '-' . $i;
			if(!$db->get_var("SELECT id FROM " . TABLES_PREFIX . "categories WHERE url = '$url'")){
				break;
			}
		}
	}
	return $url;
}

function PostUrlGen($name){
	$db = new Db;
	
	$url = Slug($name);
	
	$check = $db->get_var("SELECT id FROM " . TABLES_PREFIX . "posts WHERE url = '$url'");
	if($check){
		for ($i = 2; $i < 300; $i++){			
			$url = $url . '-' . $i;
			if(!$db->get_var("SELECT id FROM " . TABLES_PREFIX . "posts WHERE url = '$url'")){
				break;
			}
		}
	}
	return $url;
}

function Slug($str){
	$str = strtolower(trim($str));
	$str = preg_replace('/[^a-z0-9-]/', '-', $str);
	$str = preg_replace('/-+/', "-", $str);
	return $str;
}

function NiceNumber($n) {
	$n = (0+str_replace(",","",$n));
	if(!is_numeric($n)) return false;
	if($n>1000000000000) return round(($n/1000000000000),1).'Tri';
	else if($n>1000000000) return round(($n/1000000000),1).' Bil';
	else if($n>1000000) return round(($n/1000000),1).'Mil';
	else if($n>1000) return round(($n/1000),1).'K';
	return number_format($n);
}

function get_nested_categories($offset = null, $rows = null){
	global $db;
	$categories = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "categories ORDER BY order_by ASC");
		
	$new_return = array();
	$i = 0;
	while($i < count($categories)){
		if(isset($categories[$i]->parent) AND intval($categories[$i]->parent) == 0){
			$new_return[] = $categories[$i];
			$children = parent_children($categories, $categories[$i]->id, "---");
			$results = $children['new'];
					
			if(count($children['children']) > 0){
				$new_return = array_merge($new_return, $children['children']);
						
			}
		}
		$i = $i + 1;
	}
	
	if($offset != null AND $rows != null){	
		$new_return = array_slice($new_return, $offset, $rows);
	}
		
	return $new_return;
}
	
function parent_children($categories, $parent, $level = ''){
	$new_return = array();
	$i = 0;
	while($i < count($categories)){
			
		if(isset($categories[$i]->parent) AND $categories[$i]->parent == $parent){
			$categories[$i]->name = $level . " " . $categories[$i]->name;
			$new_return[] = $categories[$i];
			$children = parent_children($categories, $categories[$i]->id, $level . " ---");
			$categories = $children['new'];
				
			if(count($children['children']) > 0){
				$new_return = array_merge($new_return, $children['children']);
			}
				
		}
		$i = $i + 1;
	}
		
	return array('children'=>$new_return,'new'=>$categories);
}

function get_category_children_id($parent){
	global $db;
	$categories = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "categories ORDER BY order_by ASC");
		
	$new_return = array();
	$i = 0;
	while($i < count($categories)){
		if(isset($categories[$i]->parent) AND intval($categories[$i]->parent) == $parent){
			$new_return[] = $categories[$i];
			$children = parent_children_ids($categories, $categories[$i]->id);
			$results = $children['new'];
					
			if(count($children['children']) > 0){
				$new_return = array_merge($new_return, $children['children']);
						
			}
		}
		$i = $i + 1;
	}
	
	$return = array();
	foreach($new_return as $n){
		$return[] = $n->id;
	}
		
	return $return;
}

function parent_children_ids($categories, $parent){
	$new_return = array();
	$i = 0;
	while($i < count($categories)){
			
		if(isset($categories[$i]->parent) AND $categories[$i]->parent == $parent){
			$new_return[] = $categories[$i];
			$children = parent_children($categories, $categories[$i]->id);
			$categories = $children['new'];
				
			if(count($children['children']) > 0){
				$new_return = array_merge($new_return, $children['children']);

			}
				
		}
		$i = $i + 1;
	}
		
	return array('children'=>$new_return,'new'=>$categories);
}

function AdminPageLayout($content_html_file){
	$layout = new Layout('../html/');
	$layout->SetContentView('admin-base');
	$layout->AddContentById('content', $layout->GetContent($content_html_file));
	$layout->AddContentById('app_name', SITE_NAME);
	$layout->AddContentById('footer_copy', COPYRIGHT);
	return $layout;
}

function FrontendPageLayout($content_html_file){
	$layout = new Layout('html/');
	$layout->SetContentView('frontend-base');
	$layout->AddContentById('content', $layout->GetContent($content_html_file));
	$layout->AddContentById('app_name', SITE_NAME);
	$layout->AddContentById('footer_copy', COPYRIGHT);
	return $layout;
}

function Paginate($url, $page, $total_pages, $already_has_query_str = false, $adjacents = 3) {
	
	$prevlabel = "&larr;";
	$nextlabel = "&rarr;";
	
	if($already_has_query_str == true){
		$start_with = '&';
	}else{
		$start_with = '?';
	}
	
	$out = '<div class="pagination pagination-centered"><ul>';
	
	// previous
	if($page == 1){
		$out.= '<li class="disabled"><a href="#">&larr;</a></li>';
	}else {
		$out.= '<li><a href="' . $url . $start_with . 'page=' . ($page-1) . '">&larr;</a></li>';
	}
	
	// first
	if($page > ($adjacents + 1)) {
		$out.= '<li><a href="' . $url . $start_with . 'page=' . 1 . '">1</a></li>';
	}
	
	// interval
	if($page > ($adjacents + 2)) {
		$out.= '<li class="disabled"><a href="#">...</a></li>';
	}
	
	// pages
	$pmin = ($page > $adjacents) ? ($page - $adjacents) : 1;
	$pmax = ($page < ($total_pages - $adjacents)) ? ($page + $adjacents) : $total_pages;
	for($i=$pmin; $i<=$pmax; $i++) {
		if($i==$page) {
			$out.= '<li class="disabled"><a href="#">' . $i . '</a></li>';
			
		}else{
			$out.= '<li><a href="' . $url . $start_with . 'page=' . $i . '">' . $i . '</a></li>';
		}
	}
	
	// interval
	if($page<($total_pages-$adjacents-1)) {
		$out.= '<li class="disabled"><a href="#">...</a></li>';
	}
	
	// last
	if($page<($total_pages-$adjacents)) {
		$out.= '<li><a href="' . $url . $start_with . 'page=' . $total_pages . '">' . $total_pages . '</a></li>';
	}
	
	// next
	if($page<$total_pages) {
		$out.= '<li><a href="' . $url . $start_with . 'page=' . ($page+1) . '">&rarr;</a></li>';
	}
	else {
		$out.= '<li class="disabled"><a href="#">&rarr;</a></li>';
	}
	
	$out.= '</ul></div>';
	
	return $out;
}

function Leave($url){
	header("Location: $url");
	exit;
}

function CreateSearchQuery($where, $columns){
	$terms = SearchSplitTerms($where);
	$terms_db = SearchDbEscapeTerms($terms);
		
	$sql_query = array();
	foreach($terms_db as $key=>$value){
		$column_list = $columns;
		$keywords = $value;
		$sql = array();
		for($i = 0; $i < count($column_list); $i++){
			$sql[] = '' . $column_list[$i] . ' RLIKE "' . $keywords . '"';
		}
		$sql_query = array_merge($sql_query, $sql);
			
	}
	return $sql_query = implode(' OR ', $sql_query);
}
	
function SearchSplitTerms($terms){

	$terms = preg_replace("/\"(.*?)\"/e", "SearchTransformTerm('\$1')", $terms);
	$terms = preg_split("/\s+|,/", $terms);

	$out = array();

	foreach($terms as $term){

		$term = preg_replace("/\{WHITESPACE-([0-9]+)\}/e", "chr(\$1)", $term);
		$term = preg_replace("/\{COMMA\}/", ",", $term);

		$out[] = $term;
	}

	return $out;
}
function SearchTransformTerm($term){
	$term = preg_replace("/(\s)/e", "'{WHITESPACE-'.ord('\$1').'}'", $term);
	$term = preg_replace("/,/", "{COMMA}", $term);
	return $term;
}
function SearchEscapeRlike($string){
	return preg_replace("/([.\[\]*^\$])/", '\\\$1', $string);
}
function SearchDbEscapeTerms($terms){
	$out = array();
	foreach($terms as $term){
		$out[] = '[[:<:]]'.AddSlashes(SearchEscapeRlike($term)).'[[:>:]]';
	}
	return $out;
}

function ValidateEmail($email){
   	if (preg_match("/[\\000-\\037]/",$email)) {
      		return false;
   	}
   	$pattern = "/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD";
   	if(!preg_match($pattern, $email)){
      		return false;
   	}
   	return true;
}

function ValidateUrl($text){
	return preg_match("#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie",$text);
}

function ConvertUrl2Link($text){
	return preg_replace("#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie","'<a href=\"$1\" target=\"_blank\">$3</a>$4'",$text);
}

function encode_password($input){
	$seed =  '$1$QnpRhvf|v$';
	$return = crypt($input,$seed);
	return $return;
}


function getNormalizedFILES(){
	$newfiles = array();
	if(isset($_FILES)){
		foreach($_FILES as $fieldname => $fieldvalue){
			foreach($fieldvalue as $paramname => $paramvalue){
				foreach((array)$paramvalue as $index => $value){
					$newfiles[$fieldname][$index][$paramname] = $value;
				}
			}
		}
	}
	return $newfiles;
}

function is_image_file($name){
	$img_extensions = 'gif|png|jpg|jpeg|jpe';
	return valid_file_extension($name, $img_extensions);
}

function valid_file_extension($name, $allowed_extensions){
	$allowed_extensions = explode('|', $allowed_extensions);
	$extension = strtolower(get_extension($name));
	if(in_array($extension, $allowed_extensions, TRUE)){
		return true;
	}else{
		return false;
	}	
	return true;
}
	
function get_extension($filename){
	$x = explode('.', $filename);
	return end($x);
}
	
function clean_file_name($filename){
	$invalid = array("<!--","-->","'","<",">",'"','&','$','=',';','?','/',"%20","%22","%3c","%253c","%3e","%0e","%28","%29","%2528","%26","%24","%3f","%3b", "%3d");		
	$filename = str_replace($invalid, '', $filename);
	$filename = preg_replace("/\s+/", "_", $filename);
	return stripslashes($filename);
}
	
function set_filename($path, $filename){
	$file_ext = get_extension($filename);
	if ( ! file_exists($path.$filename)){
		return $filename;
	}
	$new_filename = str_replace('.'.$file_ext, '', $filename);
	for ($i = 1; $i < 300; $i++){			
		if ( ! file_exists($path.$new_filename.'_'.$i.'.'.$file_ext)){
			$new_filename .= '_'.$i.'.'.$file_ext;
			break;
		}
	}
	return $new_filename;
}

function UploadError($code){
	$response = '';	
	switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            $response = '{{ST:UPLOAD_ERR_INI_SIZE}}';
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $response = '{{ST:UPLOAD_ERR_FORM_SIZE}}';
            break;
        case UPLOAD_ERR_PARTIAL:
            $response = '{{ST:UPLOAD_ERR_PARTIAL}}';
            break;
        case UPLOAD_ERR_NO_FILE:
            $response = '{{ST:UPLOAD_ERR_NO_FILE}}';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $response = '{{ST:UPLOAD_ERR_NO_TMP_DIR}}';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $response = '{{ST:UPLOAD_ERR_CANT_WRITE}}';
            break;
        case UPLOAD_ERR_EXTENSION:
            $response = '{{ST:UPLOAD_ERR_EXTENSION}}';
            break;
        default:
            $response = '{{ST:Unknown_error_file_error}}';
            break;
    }
 
    return $response;
}

function html2text($html){
	$text = $html;
	static $search = array(
		'@<script.+?</script>@usi',  // Strip out javascript content
		'@<style.+?</style>@usi',    // Strip style content
		'@<!--.+?-->@us',            // Strip multi-line comments including CDATA
		'@</?[a-z].*?\>@usi',         // Strip out HTML tags
	);
	$text = preg_replace($search, ' ', $text);
	// normalize common entities
	$text = normalizeEntities($text);
	// decode other entities
	$text = html_entity_decode($text, ENT_QUOTES, 'utf-8');
	// normalize possibly repeated newlines, tabs, spaces to spaces
	$text = preg_replace('/\s+/u', ' ', $text);
	$text = trim($text);
	// we must still run htmlentities on anything that comes out!
	// for instance:
	// <<a>script>alert('XSS')//<<a>/script>
	// will become
	// <script>alert('XSS')//</script>
	return $text;
} 

// replace encoded and double encoded entities to equivalent unicode character
// also see /app/bookmarkletPopup.js
function normalizeEntities($text) {
	static $find = array();
	static $repl = array();
	if (!count($find)) {
		// build $find and $replace from map one time
		$map = array(
			array('\'', 'apos', 39, 'x27'), // Apostrophe
			array('\'', '‘', 'lsquo', 8216, 'x2018'), // Open single quote
			array('\'', '’', 'rsquo', 8217, 'x2019'), // Close single quote
			array('"', '“', 'ldquo', 8220, 'x201C'), // Open double quotes
			array('"', '”', 'rdquo', 8221, 'x201D'), // Close double quotes
			array('\'', '‚', 'sbquo', 8218, 'x201A'), // Single low-9 quote
			array('"', '„', 'bdquo', 8222, 'x201E'), // Double low-9 quote
			array('\'', '′', 'prime', 8242, 'x2032'), // Prime/minutes/feet
			array('"', '″', 'Prime', 8243, 'x2033'), // Double prime/seconds/inches
			array(' ', 'nbsp', 160, 'xA0'), // Non-breaking space
			array('-', '‐', 8208, 'x2010'), // Hyphen
			array('-', '–', 'ndash', 8211, 150, 'x2013'), // En dash
			array('--', '—', 'mdash', 8212, 151, 'x2014'), // Em dash
			array(' ', ' ', 'ensp', 8194, 'x2002'), // En space
			array(' ', ' ', 'emsp', 8195, 'x2003'), // Em space
			array(' ', ' ', 'thinsp', 8201, 'x2009'), // Thin space
			array('*', '•', 'bull', 8226, 'x2022'), // Bullet
			array('*', '‣', 8227, 'x2023'), // Triangular bullet
			array('...', '…', 'hellip', 8230, 'x2026'), // Horizontal ellipsis
			array('°', 'deg', 176, 'xB0'), // Degree
			array('€', 'euro', 8364, 'x20AC'), // Euro
			array('¥', 'yen', 165, 'xA5'), // Yen
			array('£', 'pound', 163, 'xA3'), // British Pound
			array('©', 'copy', 169, 'xA9'), // Copyright Sign
			array('®', 'reg', 174, 'xAE'), // Registered Sign
			array('™', 'trade', 8482, 'x2122') // TM Sign
		);
		foreach ($map as $e) {
			for ($i = 1; $i < count($e); ++$i) {
				$code = $e[$i];
				if (is_int($code)) {
					// numeric entity
					$regex = "/&(amp;)?#0*$code;/";
				}
				elseif (preg_match('/^.$/u', $code)/* one unicode char*/) {
					// single character
					$regex = "/$code/u";
				}
				elseif (preg_match('/^x([0-9A-F]{2}){1,2}$/i', $code)) {
					// hex entity
					$regex = "/&(amp;)?#x0*" . substr($code, 1) . ";/i";
				}
				else {
					// named entity
					$regex = "/&(amp;)?$code;/";
				}
				$find[] = $regex;
				$repl[] = $e[0];
			}
		}
	} // end first time build
	return preg_replace($find, $repl, $text);	
}

function GeneratePassword($len = 8){
	if(!$len)
		$len = 8;	
	$pool = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$str = '';
	for ($i = 0; $i < $len; $i++){
		$str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
	}
	return $str;
}

function TrimText($input, $length) {
    $input = strip_tags($input);
    if (strlen($input) <= $length) {
        return $input;
    }
    $last_space = strrpos(substr($input, 0, $length), ' ');
    $trimmed_text = substr($input, 0, $last_space);
  
    $trimmed_text .= '&hellip;';
  
    return $trimmed_text;
}

function _make_url_clickable_cb($matches) {
	$ret = '';
	$url = $matches[2];
	if ( empty($url) )
		return $matches[0];
	// removed trailing [.,;:] from URL
	if ( in_array(substr($url, -1), array('.', ',', ';', ':')) === true ) {
		$ret = substr($url, -1);
		$url = substr($url, 0, strlen($url)-1);
	}
	return $matches[1] . "<a href=\"$url\" rel=\"nofollow\">$url</a>" . $ret;
}
function _make_web_ftp_clickable_cb($matches) {
	$ret = '';
	$dest = $matches[2];
	$dest = 'http://' . $dest;
	if ( empty($dest) )
		return $matches[0];
	// removed trailing [,;:] from URL
	if ( in_array(substr($dest, -1), array('.', ',', ';', ':')) === true ) {
		$ret = substr($dest, -1);
		$dest = substr($dest, 0, strlen($dest)-1);
	}
	return $matches[1] . "<a href=\"$dest\" rel=\"nofollow\">$dest</a>" . $ret;
}
function _make_email_clickable_cb($matches) {
	$email = $matches[2] . '@' . $matches[3];
	return $matches[1] . "<a href=\"mailto:$email\">$email</a>";
}
function make_clickable($ret) {
	$ret = ' ' . $ret;
	// in testing, using arrays here was found to be faster
	$ret = preg_replace_callback('#([\s>])([\w]+?://[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_url_clickable_cb', $ret);
	$ret = preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_web_ftp_clickable_cb', $ret);
	$ret = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', '_make_email_clickable_cb', $ret);
	// this one is not in an array because we need it to run last, for cleanup of accidental links within links
	$ret = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>", $ret);
	$ret = trim($ret);
	return $ret;
}
