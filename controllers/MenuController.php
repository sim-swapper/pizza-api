<?php
namespace Pizza\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class MenuController {

    public function get(Request $request, Response $response, array $args) {
        global $rest;

        if ($rest->checkKey($request->getHeaderLine("Authorization"), true)) {
            
        } else {
            $rest->error(401, "API key is not correct.");
        }
    }

    public function create(Request $request, Response $response, array $args) {
        global $rest;
        if ($rest->checkKey($request->getHeaderLine("Authorization"), true)) {
            if ($rest->isJson($request->getBody())) {
                $json = json_decode($request->getBody());
                if($rest->createMenuItem($json)) {
                    $item = [
                        "title"=>$json->title,
                        "price"=>$json->price,
                        "img"=>$json->img,
                        "description"=>$json->description
                    ];
                    $rest->respond(200, $item);
                } else {
                    $rest->error(400, "Could not create Menu Item");
                }
            } else {
                $rest->error(400, "Request Body was not JSON");
            }
        } else {
            $rest->error(401, "API key is not correct.");
        }
    }

    public function update(Request $request, Response $response, array $args) {
        global $rest;
        if($rest->checkKey($request->getHeaderLine("Authorization"), true)) {
            $item_id = $args["id"];
            if($rest->isMenuItem($item_id)) {
                if($rest->isJson($request->getBody())) {
                    $json = json_decode($request->getBody());
                    if($rest->updateMenuItem($item_id, $json)) {
                        $item = [
                            "title"=>$json->title,
                            "price"=>$json->price,
                            "img"=>$json->img,
                            "description"=>$json->description
                        ];
                        $rest->respond(200, $item);
                    } else {
                        $rest->error(400, "Could not update menu item.");
                    }
                } else {
                    $rest->error(400, "Request Body was not JSON");
                }
            } else {
                $rest->error(400, "Menu Item does not exist");
            }
        } else {
            $rest->error(401, "API key is not correct.");
        }
    }

    public function delete(Request $request, Response $response, array $args) {
        global $rest;

        if($rest->checkKey($request->getHeaderLine("Authorization"), true)) {
            $item_id = $args["id"];
            if($rest->isMenuItem($item_id)) {
                if($rest->deleteMenuItem($item_id)) {
                    $rest->respond(200, ["status"=>"OK", "message"=>"Menu Item has been deleted."]);
                } else {
                    $rest->error(400, "Could not delete menu item.");
                }
            } else {
                $rest->error(400, "Menu Item does not exist");
            }
        } else {
            $rest->error(401, "API key is not correct.");
        }
    }

    
}