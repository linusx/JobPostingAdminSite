<?php

namespace JobPostings\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use JobPostings\JobPostings;

class Dashboard extends JobPostings {

	public function __construct() {
		parent::__construct();
	}

	public function index( Request $request, Response $response, $args ) {
		return $this->view->render($response, 'dashboard/index.twig', [ 'user' => $this->current_user ]);
	}


}