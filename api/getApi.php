<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$className = 'Api\\' . $_GET['class'];
$functionName = $_GET['function'];
$class = new $className($_GET);
$data = $class->$functionName();

echo $data;