<?php

require __DIR__ . '/../vendor/autoload.php';

use \Slim\Middleware\HttpBasicAuthentication\PdoAuthenticator;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Load env
$dotenv = new \Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

// Setup DB managment
$pdo = new \PDO(
	'mysql:host=' . getenv('DBHOST') . ';dbname=' . getenv('DBNAME'),
	getenv('DBUSER'),
	getenv('DBPASS')
);

// Set config variables for slim
$config = [
	'debug' => true,
	'settings' => [
		'displayErrorDetails' => true,
		'determineRouteBeforeAppMiddleware' => true,
	],
];

$public_urls = [
	'/install'
];

// Setup slim router
$app = new Slim\App($config);

// Setup authentication middleware
$app->add(new \Slim\Middleware\HttpBasicAuthentication([
	"secure" => false,
	"path" => "/dashboard",
	"passthrough" => $public_urls,
	"realm" => "Job Postings",
	"authenticator" => new PdoAuthenticator([
		"pdo" => $pdo,
		"table" => "users",
		"user" => "email",
		"hash" => "password"
	]),
	"error" => function ($request, $response, $arguments) {
		$data = [];
		$data["status"] = "error";
		$data["message"] = $arguments["message"];
		return $response->write(json_encode($data, JSON_UNESCAPED_SLASHES));
	},
	"callback" => function ($request, $response, $arguments) use ($app) {
		$user = \JobPostings\JobPostings::getInstance()->getUserByEmail( $arguments['user']);

		if ( !empty( $user['role'] ) && 'admin' === strtolower( $user['role'] ) ) {
			return $response->withRedirect('/');
		}

		return false;
	}
]));

// Get container
$container = $app->getContainer();

// Register component on container
$container['view'] = function ($c) {
	$view = new \Slim\Views\Twig( dirname( __FILE__) . '/../views', [
		'cache' => false,
		'debug' => true
	]);

	$basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
	$view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));
	$view->addExtension(new Twig_Extension_Debug());

	return $view;
};

// Require routes file
require_once( dirname( __FILE__ ) . '/../routes.php' );

$app->run();
