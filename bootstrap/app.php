<?php

use App\Auth\Auth;
use App\Lib\Logger;
use Delight\Auth\Role;
use Delight\Auth\Status;
use DI\Container;
use Slim\Csrf\Guard;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Tuupola\Middleware\CorsMiddleware;
use Twig\TwigFunction;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/helper.php';

try {
    Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    //
}

//Enable error display in details when APP_ENV=local
if (envi('APP_ENV') == 'local') {
    Logger::systemLogs(true);
} else {
    Logger::systemLogs(false);
}

$container = new Container();
// Set container to create App with on AppFactory
AppFactory::setContainer($container);

$app = AppFactory::create();

$responseFactory = $app->getResponseFactory();

$routeCollector = $app->getRouteCollector();
$routeCollector->setDefaultInvocationStrategy(new RequestResponseArgs());
$routeParser = $app->getRouteCollector()->getRouteParser();

require_once __DIR__ . '/database.php';

$container->set('router', function () use ($routeParser) {
    return $routeParser;
});

$container->set('db', function () use ($capsule) {
    return $capsule;
});

$container->set('pdo', function () use ($pdo) {
    return $pdo;
});

$container->set('auth', function () {
    return new Auth;
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages;
});

$container->set('lang', function () {
    $lang = new \App\Lib\Language();
    return $lang->output();
});

$container->set('view', function ($container) {
    $view = Twig::create(__DIR__ . '/../resources/views', [
        'cache' => false, //__DIR__ . '/../logs/cache'
    ]);
    $roles = [
        'admin' => Role::ADMIN,
        'super_admin' => Role::SUPER_ADMIN,
        'user' => Role::SUBSCRIBER,
        'author' => Role::AUTHOR
    ];

    $status = [
        'normal' => Status::NORMAL,
        'banned' => Status::BANNED,
        'locked' => Status::LOCKED,
        'suspended' => Status::SUSPENDED
    ];

    $view->getEnvironment()->addGlobal('auth', [
        'isLogin' => $container->get('auth')->isLogin(),
        'user' => $container->get('auth')->user(),
    ]);

    //flash
    $view->getEnvironment()->addGlobal('flash', $container->get('flash'));

    //Language
    $view->getEnvironment()->addGlobal('lang', $container->get('lang'));

    //Auth Roles
    $view->getEnvironment()->addGlobal('role', $roles);

    //Auth Status
    $view->getEnvironment()->addGlobal('status', $status);

    //route
    $route = new TwigFunction('route', function ($name, $params1 = [], $params2 = []) {
        return route($name, $params1, $params2);
    });
    $view->getEnvironment()->addFunction($route);

    //assets
    $assets = new TwigFunction('assets', function ($location) {
        return assets($location);
    });
    $view->getEnvironment()->addFunction($assets);

    //url
    $url = new TwigFunction('url', function () {
        return base_url();
    });
    $view->getEnvironment()->addFunction($url);

    //file exist checker
    $fileExist = new TwigFunction('fileExist', function ($file) {
        if (file_exists(base_path($file))) {
            return true;
        }
        return false;
    });
    $view->getEnvironment()->addFunction($fileExist);

    //hasRole
    $hasRole = new TwigFunction('hasRole', function ($role) {
        return Auth::hasRole($role);
    });
    $view->getEnvironment()->addFunction($hasRole);

    //hasAnyRole
    $hasAnyRole = new TwigFunction('hasAnyRole', function ($roles) {
        return Auth::hasAnyRole($roles);
    });
    $view->getEnvironment()->addFunction($hasAnyRole);

    //Get file extension
    $fileExt = new TwigFunction('image', function ($filepath) {
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        if (in_array($ext, ['png', 'jpg', 'jpeg', 'jif'])) {
            return true;
        } else {
            return false;
        }
    });
    $view->getEnvironment()->addFunction($fileExt);

    //Pagination
    $pagination = new TwigFunction("links", function ($object) {
    });
    $view->getEnvironment()->addFunction($pagination);

    return $view;
});
$app->add(TwigMiddleware::createFromContainer($app));

$container->set('validator', function ($container) {
    return new App\Lib\Validator;
});

// Hapus atau komentar bagian ini
// $container->set('csrf', function ($container) use ($responseFactory) {
//     return new Guard($responseFactory);
// });

$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));

// Hapus atau komentar bagian ini
// $app->add(new \App\Middleware\CsrfViewMiddleware($container));
// $app->add(new \App\Middleware\CsrfTokenMiddleware($container));

// Hapus atau komentar bagian ini
// $app->add('csrf');

// Tambahkan Middleware CORS
$app->add(new CorsMiddleware([
    "origin" => ["*",'http://localhost:517'],
    "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
    "headers.allow" => ["Authorization", "Content-Type", "Accept", "Origin"],
    "headers.expose" => ["Authorization", "Content-Type", "Accept", "Origin"],
    "credentials" => true,
    "cache" => 0,
]));

$app->setBasePath(routePath());

if (file_exists(base_path('routes/generator.php'))) {
    require base_path('routes/generator.php');
}
require __DIR__ . '/../routes/system.php';
foreach (glob(__DIR__ . '/../routes/web/*.php') as $filename) {
    require $filename;
}
