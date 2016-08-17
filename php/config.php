<?php
if (!isset($_SESSION)) {
	session_start();
}

if (isset($_GET["lang"])) {
    if (!isset($_SESSION["lang"]) || (isset($_SESSION["lang"]) && $_SESSION["lang"] != $_GET["lang"])) {
        $_SESSION["lang"] = $_GET["lang"];
    }
}

if (!isset($_SESSION["lang"])) {
	if (!isset($_GET["lang"])) {
		header("location: index.php?lang=en");
	}
}


define('DB_NAME', 'help');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/** The Database Collate type. Don't change this if in doubt. */

$tbl_prefix = 'help_';

if ($_SESSION['lang'] == 'es') {
	$tbl_prefix = 'es_help_';
} elseif ($_SESSION['lang'] == 'pt') {
	$tbl_prefix = 'pt_help_';
}

define('TABLES_PREFIX', $tbl_prefix);

/** Set to false when in production to suppress errors for users. */
define('DEVELOPMENT', true);

/** Webmaster's email */
define('WEBMASTER_EMAIL', 'webmaster@email.com');

/** Limit the number of email sents through the contact forms. Leave as false for no limit */
define('LIMIT_EMAIL', false);