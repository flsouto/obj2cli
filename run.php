<?php

if(empty($argv[1])||empty($argv[2])){
	die("Usage: \n  command <file> <class> [args...]\n\n");
}

if(!file_exists($argv[1])){
	die("Include file not found: $argv[1]\n");
}

require_once($argv[1]);

if(!class_exists($argv[2])){
	die("Class not found: $argv[2]\n");
}

$args = array_slice($argv,3);

$reflect = new ReflectionClass($argv[2]);
$object = $reflect->newInstanceArgs($args);

require_once(__DIR__.'/obj2cli.php');

obj2cli($object);


