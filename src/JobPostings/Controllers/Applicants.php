<?php
/**
 * Applicant/User Controller.
 *
 * @package JobPostings
 * @author Bill Van Pelt <linusx@gmail.com>
 */

namespace JobPostings\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use JobPostings\JobPostings;
use JobPostings\Models\Model;

class Applicants extends JobPostings {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Show all users.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 * @return Response
	 */
	public function index( Request $request, Response $response, $args ) {
		$applicants = Model::getInstance('Applicants')->get();
		return $this->view->render($response, 'dashboard/applicants/index.twig', [ 'applicants' => $applicants ]);
	}

	/**
	 * Show single user.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 * @return Response
	 */
	public function show( Request $request, Response $response, $args ) {
		$error = filter_input(INPUT_GET, 'error', FILTER_UNSAFE_RAW);
		$applicant_slug = filter_var( $args['slug'], FILTER_SANITIZE_STRING );
		$states = JobPostings::getInstance()->getStates();

		$applicant = Model::getInstance('Applicants')->getApplicant( $applicant_slug );

		return $this->view->render($response, 'dashboard/applicants/applicant.twig', [ 'error' => $error, 'states' => $states, 'applicant' => $applicant ]);
	}

	/**
	 * Delete user.
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

		$status = Model::getInstance('Applicants')->delete( $id );
		if ( false === $status['status'] ) {
			return $this->sendFailure( $status['message'] );
		}

		return $this->sendSuccess( 'client deleted' );
	}

	/**
	 * Add a user.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 * @return mixed
	 */
	public function add( Request $request, Response $response, $args ) {
		$client['firstname'] = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
		$client['lastname'] = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
		$client['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
		$client['role'] = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
		$passwd = filter_input(INPUT_POST, 'passwd', FILTER_SANITIZE_STRING);
		$confirm_passwd = filter_input(INPUT_POST, 'passwd_confirm', FILTER_SANITIZE_STRING);

		if ( $passwd !== $confirm_passwd ) {
			return $response->withRedirect('/dashboard/add/client');
		}

		$client['password'] = $passwd;

		$new_client = Model::getInstance('Applicants')->add( $client );

		if ( empty( $new_client['slug'] ) ) {
			return $response->withRedirect('/dashboard/add/user?error=' . $new_client);
		}

		return $response->withRedirect('/dashboard/user/' . $new_client['slug']);

	}

	public function addHistory( Request $request, Response $response, $args ) {
		$error_qry = '';
		$user_slug = filter_input(INPUT_POST, 'user_slug', FILTER_SANITIZE_STRING);
		$arg['user_id'] = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
		$arg['title'] = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
		$arg['start_date'] = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
		$arg['end_date'] = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
		$arg['company_name'] = filter_input(INPUT_POST, 'company_name', FILTER_SANITIZE_STRING);
		$arg['company_city'] = filter_input(INPUT_POST, 'company_city', FILTER_SANITIZE_STRING);
		$arg['company_state'] = filter_input(INPUT_POST, 'company_state', FILTER_SANITIZE_STRING);
		$arg['description'] = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

		$new_job = Model::getInstance('Applicants')->addJob( $arg );
		if ( empty( $new_job['id'] ) ) {
			$error_qry = '?error=' . $new_job;
		}

		return $response->withRedirect('/dashboard/user/' . $user_slug . $error_qry);
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
		return $this->view->render($response, 'dashboard/applicants/add.twig', [ 'error' => $error ]);
	}
}