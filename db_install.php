<?php

require_once 'php/config.php';
require_once "php/ezSQL/shared/ez_sql_core.php";
require_once "php/ezSQL/ez_sql_pdo.php";
require_once 'php/db.php';
require_once 'php/delta.php';

$sql = "CREATE TABLE " . TABLES_PREFIX . "options (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
option_key text,
option_value text,
PRIMARY KEY  (id)
);";
delta($sql);

$sql = "CREATE TABLE " . TABLES_PREFIX . "categories (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
name text,
description text,
order_by int(11) NOT NULL DEFAULT '9999',
parent bigint(20) unsigned NOT NULL DEFAULT '0',
url text,
PRIMARY KEY  (id)
);";
delta($sql);

$sql = "CREATE TABLE " . TABLES_PREFIX . "posts (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
title text,
body text NOT NULL,
category_id int(11) NOT NULL,
likes int(11) DEFAULT NULL,
date timestamp NULL DEFAULT NULL,
views bigint(20) NOT NULL DEFAULT '0',
published varchar(1) NOT NULL DEFAULT 'y',
related_categories text,
related_articles text,
url text,
PRIMARY KEY  (id)
);";
delta($sql);

$sql = "CREATE TABLE " . TABLES_PREFIX . "forums (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
body text NOT NULL,
category_id int(11) NOT NULL,
PRIMARY KEY  (id)
);";
delta($sql);
