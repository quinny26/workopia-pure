<?php 
session_start();
require __DIR__.'/../vendor/autoload.php';
require '../helpers.php';

use Framework\Router;

//Instantiate the router
$router = new Router ();

//get routes
$routes = require basePath('routes.php');

//get the current uri and http method
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


//route the request
$router->route($uri);

