<?php

use AnnotationFinder\Finder\AnnotatedClassFinder;
use Symfony\Component\EventDispatcher\EventDispatcher;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once('Food.php');
require_once('Sunday.php');
require_once('Skate.php');
require_once('MilkShake.php');

$finder = new AnnotatedClassFinder(new EventDispatcher());

$finder->when("finds_@Food", function($event) {
    echo $event->getClass() . ' is a food' . PHP_EOL; 
});

$finder->find("@Food")->in(__DIR__);