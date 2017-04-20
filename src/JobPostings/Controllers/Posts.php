<?php
/**
 * Job Posting Controller
 *
 * @package JobPostings
 * @author Bill Van Pelt <linusx@gmail.com>
 */

namespace JobPostings\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use JobPostings\JobPostings;
use JobPostings\Models\Model;

class Posts extends JobPostings {

	/**
	 * Allowed html tags in the details section.
	 *
	 * @var array
	 */
	private $allowed_html_tags = [
		'<a>', '<p>', '<strong>', '<em>', '<u>', '<table>', '<tbody>', '<thead>', '<tfooter>', '<tr>', '<td>'
	];

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Show all job posts.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 * @return Response
	 */
	public function index( Request $request, Response $response, $args ) {
		$posts = Model::getInstance('Posts')->get();
		return $this->view->render($response, 'dashboard/posts/index.twig', [ 'posts' => $posts ]);
	}

	/**
	 * Show single post.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 * @return Response
	 */
	public function show( Request $request, Response $response, $args ) {
		$post_slug = filter_var( $args['post_slug'], FILTER_SANITIZE_STRING );

		$post = Model::getInstance('Posts')->getPost( $post_slug );

		return $this->view->render($response, 'dashboard/posts/post.twig', [ 'post' => $post ]);
	}

	/**
	 * Add new post.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param $args
	 * @return mixed
	 */
	public function add( Request $request, Response $response, $args ){
		$post['client_id'] = filter_input(INPUT_POST, 'client_id', FILTER_SANITIZE_NUMBER_INT);
		$post['short_description'] = filter_input(INPUT_POST, 'short_description', FILTER_SANITIZE_STRING);
		$post['details'] = strip_tags( $_POST['details'], implode(', ', $this->allowed_html_tags ) );

		if ( empty( $post['client_id'] ) ) {
			return $response->withRedirect('/dashboard/add/post');
		}

		$new_post = Model::getInstance('Posts')->add( $post );

		if ( empty( $new_post['client_slug'] ) ) {
			return $response->withRedirect('/dashboard/add/post?error=' . $new_post);
		}

		return $response->withRedirect('/dashboard/post/' . $new_post['client_slug'] . '/' . $new_post['slug']);
	}

	/**
	 * Delete post.
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

		$status = Model::getInstance('Posts')->delete( $id );
		if ( false === $status['status'] ) {
			return $this->sendFailure( $status['message'] );
		}

		return $this->sendSuccess( 'post deleted' );
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
		$client_id = filter_input(INPUT_GET, 'client_id', FILTER_SANITIZE_NUMBER_INT);
		$clients = Model::getInstance('Clients')->get();

		return $this->view->render($response, 'dashboard/posts/add.twig', [ 'client_id' => $client_id, 'clients' => $clients, 'error' => $error ]);
	}
}