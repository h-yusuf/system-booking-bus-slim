<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Twig\TwigFunction;

/**
 * CsrfViewMiddleware
 *
 * @author    
 */
class CsrfTokenMiddleware extends Middleware
{

    public function __invoke(Request $request, RequestHandler $handler)
    {
        $token1 = [
            'keys' => [
                'name' => $this->container->get('csrf')->getTokenNameKey(),
                'value' => $this->container->get('csrf')->getTokenValueKey()
            ],
            'name' => $this->container->get('csrf')->getTokenName(),
            'value' => $this->container->get('csrf')->getTokenValue()
        ];

        $token2 =[
            $this->container->get('csrf')->getTokenNameKey() =>$this->container->get('csrf')->getTokenName(),
            $this->container->get('csrf')->getTokenValueKey() =>$this->container->get('csrf')->getTokenValue()
        ];

        $tokenAccess = new TwigFunction('token', function () use($token2) {
            return json_encode($token2);
        });
        $this->container->get('view')->getEnvironment()->addFunction($tokenAccess);

        $this->container->get('view')->getEnvironment()->addGlobal('token', $token1);
        $response = $handler->handle($request);
        return $response;
    }

}
