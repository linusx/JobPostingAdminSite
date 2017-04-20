<?php
/**
 * Job Post Model
 *
 * @package JobPostings
 * @author Bill Van Pelt <linusx@gmail.com>
 */

namespace JobPostings\Models;

use JobPostings\JobPostings;
use PDO;
use PDOException;

class Posts extends Model {

	private $cookie_length = '';
	private $cookie_str = 'viewed';

	public function __construct() {
		$number_of_days = 365;
		$this->cookie_length = time() + 60 * 60 * 24 * $number_of_days;

		parent::__construct();
	}

	/**
	 * Get all posts.
	 *
	 * @param string $slug
	 * @param string $order_by
	 * @param string $order_dir
	 * @return array|mixed
	 */
	public function get( $slug = null, $order_by = 'created_at', $order_dir = 'ASC' ) {
		$slug = filter_var( $slug, FILTER_SANITIZE_STRING );
		$order_by = filter_var( $order_by, FILTER_SANITIZE_STRING );
		$order_dir = filter_var( $order_dir, FILTER_SANITIZE_STRING );

		/**
		 * Retrieve all the posts, including how many applicants applied for the position.
		 * Also get the client name and contact information.
		 */
		$sql = 'SELECT
					(SELECT COUNT(id) FROM applications WHERE posting_id = a.id) as application_count, 
					a.id, 
					a.posted_by,
					a.slug,
					a.short_description,
					a.details, 
					a.created_at,
					a.views,
					b.firstname as posted_by_firstname,
					b.lastname as posted_by_lastname,
					b.email as posted_by_email,
					b.created_at as posted_by_created_at,
					c.name as client_name, 
					c.slug as client_slug,
					c.address as client_address,
					c.address2 as client_address2,
					c.city as client_city,
					c.zipcode as client_zipcode
				FROM 
					posts a
				LEFT OUTER JOIN users b ON a.posted_by = b.id
				LEFT OUTER JOIN clients c ON a.client_id = c.id';

		if ( !empty( $slug ) ) {
			$sql .= ' WHERE a.slug = ' . $this->db->quote( $slug );
		}

		if( ! empty( $order_by ) ) {
			$sql .= ' ORDER BY ' . $this->db->quote( 'a.' . $order_by ) . ' ' . $order_dir;
		}

		$statement = $this->db->prepare( $sql );
		$statement->execute();

		if ( ! empty( $slug ) ) {
			$posts = $statement->fetch(PDO::FETCH_ASSOC);
		} else {
			$posts = $statement->fetchAll(PDO::FETCH_ASSOC);
		}

		return $posts;
	}

	/**
	 * Add a new post
	 *
	 * @param array $args
	 * @return array|mixed
	 */
	public function add( $args = [] ) {
		$args['slug'] = JobPostings::getInstance()->generateSlug();
		$args['posted_by'] = JobPostings::getInstance()->current_user['id'];

		/**
		 * Insert a new post
		 */
		$sql = 'INSERT INTO posts (
			slug,
			posted_by,
			short_description,
			details,
			client_id,
			created_at
		) VALUES (
			:slug,
			:posted_by,
			:short_description,
			:details,
			:client_id,
			NOW()
		)';
		try {
			$statement = $this->db->prepare( $sql );
			$statement->execute( $args );
		} catch(PDOException $e) {
			return $e->getMessage();
		}

		return $this->get( $args['slug'] );
	}

	/**
	 * Delete a post
	 *
	 * @param int $id
	 * @return array
	 */
	public function delete( $id ) {
		$id = filter_var( $id, FILTER_SANITIZE_NUMBER_INT );

		$sql = "DELETE FROM posts WHERE id = :id";

		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute(['id' => $id]);
		} catch(PDOException $e) {
			return [ 'status' => false, 'message' => $e->getMessage() ];
		}

		return [ 'status' => true, 'message' => '' ];
	}

	/**
	 * Get a single post
	 *
	 * @param $post_slug
	 * @return array|mixed
	 */
	public function getPost( $post_slug ) {
		$post_slug = filter_var( $post_slug, FILTER_SANITIZE_STRING );

		$post = $this->get( $post_slug );
		if ( empty( $post ) ) {
			return false;
		}

		$post['applicants'] = $this->getPostApplicants( $post['id'] );

		return $post;
	}

	/**
	 * Get users that applied to a post
	 *
	 * @param $post_id
	 * @return array|bool
	 */
	public function getPostApplicants( $post_id  ) {
		$post_id = filter_var( $post_id, FILTER_SANITIZE_NUMBER_INT );

		$applicant_sql = 'SELECT 
				b.id,
				b.slug,
				b.firstname,
				b.lastname,
				b.email,
				b.created_at
			FROM 
				applications a 
			LEFT OUTER JOIN users b ON a.applicant_id = b.id
			WHERE 
				a.posting_id = :post_id';

		try {
			$applicants_statement = $this->db->prepare( $applicant_sql );
			$applicants_statement->execute( ['post_id' => $post_id] );
			return $applicants_statement->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			return false;
		}
	}

	/**
	 * Apply to a post
	 *
	 * @param $post_id
	 * @param $user_id
	 * @return bool
	 */
	public function apply( $post_id, $user_id ) {
		$post_id = filter_var( $post_id, FILTER_SANITIZE_NUMBER_INT );
		$user_id = filter_var( $user_id, FILTER_SANITIZE_NUMBER_INT );

		$sql = 'INSERT INTO applications (
			applicant_id,
			posting_id,
			created_at
		) VALUES (
			:user_id,
			:post_id,
			NOW()
		)';

		try {
			$st = $this->db->prepare( $sql );
			$st->execute(['post_id' => $post_id, 'user_id' => $user_id]);
		} catch(PDOException $e) {
			return false;
		}
		
		return true;
	}

	/**
	 * Mark post as viewed
	 *
	 * @param $post_id
	 * @return bool
	 */
	public function view( $post_id ) {
		$post_id = filter_var( $post_id, FILTER_SANITIZE_NUMBER_INT );

		$post_cookie = [];

		if ( ! empty( $_COOKIE[$this->cookie_str] ) ) {
			$post_cookie = unserialize($_COOKIE[$this->cookie_str] );
			if ( ! empty( $post_cookie[$post_id])) {
				return true;
			}
		}

		$sql = 'UPDATE posts SET views = views + 1 WHERE id = :post_id';

		try {
			$st = $this->db->prepare( $sql );
			$st->execute(['post_id' => $post_id]);
			$applied = $st->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			return false;
		}

		$post_cookie[$post_id] = 1;
		setcookie( $this->cookie_str, serialize($post_cookie), $this->cookie_length, "/" ) ;

		return true;
	}

	/**
	 * Check to see if a user has already applied to a post
	 *
	 * @param $post_id
	 * @param $user_id
	 * @return bool
	 */
	public function applied( $post_id, $user_id ) {
		$post_id = filter_var( $post_id, FILTER_SANITIZE_NUMBER_INT );
		$user_id = filter_var( $user_id, FILTER_SANITIZE_NUMBER_INT );

		$sql = 'SELECT id FROM applications WHERE posting_id = :post_id AND applicant_id = :user_id';

		try {
			$st = $this->db->prepare( $sql );
			$st->execute(['post_id' => $post_id, 'user_id' => $user_id]);
			$applied = $st->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			return false;
		}

		return empty( $applied ) ? false : true;
	}
}