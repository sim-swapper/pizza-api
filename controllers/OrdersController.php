<?php
namespace Pizza\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class OrdersController {

    public function get(Request $request, Response $response, array $args) {
        global $rest;
        
        if ($rest->checkKey($request->getHeaderLine("Authorization"), true)) {
            echo $rest->getOrders();
        } else {
            $rest->error(401, "API key is not correct.");
        }
    }

    public function history(Request $request, Response $response, array $args) {
        $response->getBody()->write("Orders Body");

        return $response;
    }

    public function update(Request $request, Response $response, array $args) {
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
    }

    public function create(Request $request, Response $response, array $args) {
        global $rest;
        if ($rest->checkKey($request->getHeaderLine("Authorization"))) {
            if ($rest->isJson($request->getBody())) {
                $json = json_decode($request->getBody());
                $order_id = $rest->createOrder($json);
                $order = [
                    "order_id" =>    $order_id,
                    "address" =>     $json->address,
                    "phone_number" => $json->phone_number,
                    "first_name" =>  $json->first_name,
                    "last_name" =>   $json->last_name
                ];

                $rest->respond(200, $order);
            } else {
                $rest->error(400, "Request Body was not JSON");
            }
        } else {
            $rest->error(401, "API key is not correct.");
        }
    }

    public function delete(Request $request, Response $response, array $args) {
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
    }

}