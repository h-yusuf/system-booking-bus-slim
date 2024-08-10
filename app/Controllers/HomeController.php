<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * HomeController
 * @author    Hezekiah O. <support@hezecom.com>
 */
class HomeController extends Controller
{
    public function index(Request $request, Response $response)
    {
        return view($response, 'index.twig');
    }

    public function success(Request $request, Response $response)
    {
        print("<center><h1>Payment Success</h1></center>");
        die;
    }

    public function failure(Request $request, Response $response)
    {
        print("<center><h1>Payment Failed</h1></center>");
        die;
    }

    public function dashboard(Request $request, Response $response)
    {
        return view($response, 'dashboard/index.twig');
    }
}
