<?php
namespace Pizza\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AuthController {

    public function login(Request $request, Response $response, array $args) {
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
    }

    public function register(Request $request, Response $response, array $args) {
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
    }

    public function refresh(Request $request, Response $response, array $args) {
        $response->getBody()->write("Orders Body");

        return $response;
    }

    public function profile(Request $request, Response $response, array $args) {
        $response->getBody()->write("Orders Body");

        return $response;
    }
    
}