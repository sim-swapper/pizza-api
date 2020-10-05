<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
require_once "Rest.php";
require_once "config/config.php";
require 'vendor/autoload.php';
$app = new \Slim\App;
$rest = new Rest($config["database_host"], $config["database_name"], $config["database_username"], $config["database_password"]);

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Pizza Dilivery");
    return $response;
});

$app->post('/auth/login', function (Request $request, Response $response) {
    global $rest;
    if($rest->isJson($request->getBody())) {
        $json = json_decode($request->getBody());
        if($rest->loginUser($json->email, $json->password)) {
            $user_id = $rest->getUser($json->email)["id"];
            $split = explode("|", $rest->createToken($user_id));
            $token = $split[0];
            $expires = $split[1];
            $rest->respond(200, ["status"=>"OK", "message"=>"Logged in.", "token"=>$token, "expires"=>$expires]);
        } else {
            $rest->error(401, "Could not login user.");
        }
    } else {
        $rest->error(400, "Request Body was not JSON");
    }
});

$app->post('/auth/register', function (Request $request, Response $response) {
    global $rest;
    if($rest->isJson($request->getBody())) {
        $json = json_decode($request->getBody());
        if($rest->isEmailUsed($json->email)) {
            $rest->error(400, "Email already used");
        }
        if($rest->createUser($json)) {
            $rest->respond(200, ["status"=>"OK", "message"=>"User successfully created."]);
        } else {
            $rest->error(400, "Could not create user.");
        }
    } else {
        $rest->error(400, "Request Body was not JSON");
    }
});

$app->get('/auth/refresh', function (Request $request, Response $response) {
        
});

$app->get('/auth/profile', function (Request $request, Response $response) {

});

$app->get('/orders', function (Request $request, Response $response) {
    global $rest;

    if ($rest->checkKey($request->getHeaderLine("Authorization"), true)) {
        echo $rest->getOrders();
    } else {
        $rest->error(401, "API key is not correct.");
    }
});

$app->put('/orders/{id}', function (Request $request, Response $response, array $args) {
    global $rest;
    if($rest->checkKey($request->getHeaderLine("Authorization"), true)) {
        $order_id = $args["id"];

        if($rest->doesOrderExist($order_id)) {
            if($rest->isJson($request->getBody())) {
                $json = json_decode($request->getBody());
                if($rest->updateStatus($order_id, $json->status)) {
                    $rest->respond(200, ["status"=>"OK", "message"=>"Status has been updated."]);
                } else {
                    $rest->error(400, "Could not update order.");
                }
            } else {
                $rest->error(400, "Request Body was not JSON");
            }
        } else {
            $rest->error(400, "Order does not exist");
        }
    } else {
        $rest->error(401, "API key is not correct.");
    }
});

$app->post('/orders', function (Request $request, Response $response) {
    global $rest;
    if($rest->checkKey($request->getHeaderLine("Authorization"))) {
        if($rest->isJson($request->getBody())) {
            $json = json_decode($request->getBody());
            $order_id = $rest->createOrder($json);
            $order = [
                "order_id"=>    $order_id,
                "address"=>     $json->address,
                "phone_number"=>$json->phone_number,
                "first_name"=>  $json->first_name,
                "last_name"=>   $json->last_name
            ];

            $rest->respond(200, $order);
        } else {
            $rest->error(400, "Request Body was not JSON");
        }
    } else {
        $rest->error(401, "API key is not correct.");
    }
});

$app->delete('/orders/{id}', function (Request $request, Response $response, array $args) {
    global $rest;
    if($rest->checkKey($request->getHeaderLine("Authorization"), true)) {
        $order_id = $args["id"];
        if($rest->doesOrderExist($order_id)) {
            if($rest->deleteOrder($order_id)) {
                $rest->respond(200, ["status"=>"OK", "message"=>"Order has been deleted."]);
            } else {
                $rest->error(400, "Could not delete order.");
            }
        } else {
            $rest->error(400, "Order does not exist");
        }
    } else {
        $rest->error(401, "API key is not correct.");
    }
});

$app->get('/orders/history', function (Request $request, Response $response) {

});

$app->run();
