<?php
use Pizza\Controller;

require_once "Rest.php";
require_once "config/config.php";
require_once "controllers/IndexController.php";
require_once "controllers/OrdersController.php";
require_once "controllers/AuthController.php";
require 'vendor/autoload.php';
$app = new \Slim\App;
$rest = new Rest($config["database_host"], $config["database_name"], $config["database_username"], $config["database_password"]);

$app->get('/', [new Controller\IndexController, 'get']);

$app->group('/api', function() use ($app) {
    $app->group('/v1', function() use ($app) {
        $app->get('/',                  [new Controller\IndexController, 'get']);
        $app->get('/orders',            [new Controller\OrdersController, 'get']);
        $app->post('/orders',           [new Controller\OrdersController, 'create']);
        $app->put('/orders/{id}',       [new Controller\OrdersController, 'update']);
        $app->delete('/orders/{id}',    [new Controller\OrdersController, 'delete']);
        $app->get('/orders/history',    [new Controller\OrdersController, 'history']);

        $app->post('/auth/login',       [new Controller\AuthController, 'login']);
        $app->post('/auth/register',    [new Controller\AuthController, 'register']);
        $app->post('/auth/refresh',     [new Controller\AuthController, 'refresh']);
        $app->post('/auth/profile',     [new Controller\AuthController, 'profile']);
    });
});

$app->run();
