<?php
/**
 * Client Controller
 *
 * @package JobPostings
 * @author Bill Van Pelt <linusx@gmail.com>
 */

namespace JobPostings\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use JobPostings\JobPostings;
use JobPostings\Models\Model;

class Clients extends JobPostings {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Show all clients.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 * @return Response
	 */
	public function index( Request $request, Response $response, $args ) {
		$clients = Model::getInstance('Clients')->get();
		return $this->view->render($response, 'dashboard/clients/index.twig', [ 'clients' => $clients ]);
	}

	/**
	 * Show single client.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 * @return Response
	 */
	public function show( Request $request, Response $response, $args ) {
		$client_slug = filter_var( $args['slug'], FILTER_SANITIZE_STRING );
		$client = Model::getInstance('Clients')->getClient( $client_slug );

		return $this->view->render($response, 'dashboard/clients/client.twig', [ 'client' => $client ]);
	}

	/**
	 * Add client.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 * @return mixed
	 */
	public function add( Request $request, Response $response, $args ) {
		$client['name'] = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
		$client['contact_id'] = filter_input(INPUT_POST, 'contact_id', FILTER_SANITIZE_NUMBER_INT);
		$client['address'] = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
		$client['address2'] = filter_input(INPUT_POST, 'address2', FILTER_SANITIZE_STRING);
		$client['city'] = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
		$client['state'] = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
		$client['zipcode'] = filter_input(INPUT_POST, 'zipcode', FILTER_SANITIZE_NUMBER_INT);

		$new_client = Model::getInstance('Clients')->add( $client );

		if ( empty( $new_client['slug'] ) ) {
			return $response->withRedirect('/dashboard/add/client?error=' . $new_client);
		}

		return $response->withRedirect('/dashboard/client/' . $new_client['slug']);

	}

	/**
	 * Delete client.
	 * This should be access with an xhr request.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 */
	public function delete( Request $request, Response $response, $args ) {
		if ( ! $request->isXhr() ) {
			return false;
		}

		$id = filter_var( $args['id'], FILTER_SANITIZE_NUMBER_INT );

		$status = Model::getInstance('Clients')->delete( $id );
		if ( false === $status['status'] ) {
			return $this->sendFailure( $status['message'] );
		}

		return $this->sendSuccess( 'client deleted' );
	}

	/**
	 * Show the add view.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 * @return Response
	 */
	public function showAdd( Request $request, Response $response, $args ) {
		$error = filter_input(INPUT_GET, 'error', FILTER_UNSAFE_RAW);
		$contacts = Model::getInstance('Applicants')->get( null, 'client' );
		$states = JobPostings::getInstance()->getStates();

		return $this->view->render($response, 'dashboard/clients/add.twig', [ 'states' => $states, 'contacts' => $contacts, 'error' => $error ]);
	}
}