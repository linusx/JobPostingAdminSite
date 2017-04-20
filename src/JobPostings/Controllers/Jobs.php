<?php
/**
 * Jobs Controller.
 *
 * @package JobPostings
 * @author Bill Van Pelt <linusx@gmail.com>
 */

namespace JobPostings\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use JobPostings\JobPostings;
use JobPostings\Models\Model;

class Jobs extends JobPostings {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * View a single job post.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 * @return Response
	 */
	public function job( Request $request, Response $response, $args ) {
		$post_slug = filter_var( $args['post_slug'], FILTER_SANITIZE_STRING );
		$post = Model::getInstance('Posts')->getPost( $post_slug );

		$applied = false;
		if ( ! empty( $this->current_user ) ) {
			$applied = Model::getInstance('Posts')->applied($post['id'], $this->current_user['id']);
		}

		Model::getInstance('Posts')->view( $post['id'] );

		return $this->view->render($response, 'public/post.twig', [ 'applied' => $applied, 'user' => $this->current_user, 'post' => $post ]);
	}

	/**
	 * Show jobs view.
	 * This will list all job posts.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 * @return Response
	 */
	public function jobs( Request $request, Response $response, $args ) {
		$posts = Model::getInstance('Posts')->get( null, 'created_at', 'DESC' );
		return $this->view->render($response, 'public/jobs.twig', [ 'user' => $this->current_user, 'posts' => $posts ]);
	}

	/**
	 * Apply to a job post.
	 * This should be access with an xhr request.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 */
	public function apply( Request $request, Response $response, $args ) {
		if ( ! $request->isXhr() ) {
			return false;
		}

		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
		$user_id = filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );

		$status = Model::getInstance('Posts')->apply( $post_id, $user_id );

		if ( true === $status ) {
			return $this->sendSuccess( 'Applied' );
		} else {
			return $this->sendFailure( 'Error applying' );
		}

	}

}