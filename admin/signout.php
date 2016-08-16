<?php

require 'includes.php';

unset($_SESSION[TABLES_PREFIX.'logged_in']);

Cookie::Delete(TABLES_PREFIX . 'remember_me');

header('Location: '.BASE_URL);
exit;
