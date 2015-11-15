<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

set_include_path(get_include_path() . PATH_SEPARATOR . getcwd() . '/classes');
set_include_path(get_include_path() . PATH_SEPARATOR . getcwd() . '/vendors');

include 'FileManager.php';

$manager = new FileManager('document');
$manager->Run();