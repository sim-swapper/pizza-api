<?php
namespace Pizza\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class IndexController {

    public function get(Request $request, Response $response, $args = []) {
        $response->getBody()->write("Pizza Dilivery API v1.0");

        return $response;
     }
 
}