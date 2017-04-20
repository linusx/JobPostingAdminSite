<?php
/**
 * Applicants Model
 *
 * @package JobPostings
 * @author Bill Van Pelt <linusx@gmail.com>
 */

namespace JobPostings\Models;

use PDO;
use PDOException;
use JobPostings\JobPostings;

class Applicants extends Model {

	/**
	 * Get all users.
	 *
	 * @param null $id
	 * @param null $role
	 * @return array|mixed
	 */
	public function get( $slug = null, $role = null ) {
		$slug = filter_var( $slug, FILTER_SANITIZE_STRING );
		$role = filter_var( $role, FILTER_SANITIZE_STRING );

		$sql = 'SELECT
 				(SELECT COUNT(id) FROM applications WHERE applicant_id = users.id) as application_count, 
				id, 
				slug,
				firstname, 
				lastname, 
				email,
				slug,
				role,
				created_at 
			FROM 
				users
			WHERE 1=1';

		if ( ! empty( $role ) ) {
			$sql .= ' AND role = ' . $this->db->quote( $role );
		}

		if ( ! empty( $slug ) ) {
			$sql .= ' AND slug = ' . $this->db->quote( $slug );
		}

		try {
			$statement = $this->db->prepare( $sql );
			$statement->execute();

			if ( ! empty( $slug ) ) {
				$user = $statement->fetch(PDO::FETCH_ASSOC);
			} else {
				$user = $statement->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch(PDOException $e) {
			return false;
		}

		return $user;
	}

	/**
	 * Add new user.
	 *
	 * @param array $args
	 * @return array|mixed|string
	 */
	public function add( $args = [] ) {
		$args['slug'] = JobPostings::getInstance()->generateSlug();
		$args['password'] = password_hash($args['password'], PASSWORD_DEFAULT);

		$sql = 'INSERT INTO users (
			slug,
			firstname,
			lastname,
			email,
			password,
			role,
			created_at
		) VALUES (
			:slug,
			:firstname,
			:lastname,
			:email,
			:password,
			:role,
			NOW()
		)';
		try {
			$statement = $this->db->prepare( $sql );
			$statement->execute( $args );
		} catch(PDOException $e) {
			return $e->getMessage();
		}

		return $this->get( $args['slug'], $args['role'] );
	}

	/**
	 * Add a new job history.
	 *
	 * @param array $args
	 * @return array|string
	 */
	public function addJob( $args = [] ) {
		$sql = 'INSERT INTO applicant_history (
			user_id,
			start_date,
			end_date,
			title,
			description,
			company_name,
			company_city,
			company_state,
			created_at
		) VALUES (
			:user_id,
			:start_date,
			:end_date,
			:title,
			:description,
			:company_name,
			:company_city,
			:company_state,
			NOW()
		)';
		try {
			$statement = $this->db->prepare( $sql );
			$statement->execute( $args );
			$new_id = $this->db->lastInsertId();
		} catch(PDOException $e) {
			return $e->getMessage();
		}

		return [ 'id' => $new_id ];
	}

	/**
	 * Delete user.
	 *
	 * @param int $id
	 * @return array
	 */
	public function delete( $id ) {
		$id = filter_var( $id, FILTER_SANITIZE_NUMBER_INT );

		$sql = "DELETE FROM users WHERE id = :id";

		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
		} catch(PDOException $e) {
			return [ 'status' => false, 'message' => $e->getMessage() ];
		}

		return [ 'status' => true, 'message' => '' ];
	}

	/**
	 * Get user.
	 *
	 * @param $applicant_slug
	 * @return mixed
	 */
	public function getApplicant( $applicant_slug ) {
		$applicant_slug = filter_var( $applicant_slug, FILTER_SANITIZE_STRING );

		$applicant = $this->get( $applicant_slug );
		if ( empty( $applicant ) ) {
			return false;
		}

		$applicant['applied_to'] = $this->getApplications( $applicant['id'] );
		if ( 'applicant' === strtolower( $applicant['role'] ) ) {
			$applicant['job_history'] = $this->getJobHistory($applicant['id']);
		}

		return $applicant;
	}

	/**
	 * Get users posts thet they applied to.
	 *
	 * @param $applicant_id
	 * @return array|bool
	 */
	public function getApplications( $applicant_id ) {
		$applicant_id = filter_var( $applicant_id, FILTER_SANITIZE_NUMBER_INT );

		$post_sql = 'SELECT
			a.created_at as applied_date,
		    b.id, 
			b.posted_by,
			b.slug,
			b.short_description,
			b.details, 
			b.created_at,
			b.views,
			c.slug as client_slug
			FROM applications a 
			LEFT OUTER JOIN posts b ON a.posting_id = b.id
			LEFT OUTER JOIN clients c ON b.client_id = c.id
		WHERE a.applicant_id = :id';

		try {
			$post_statement = $this->db->prepare( $post_sql );
			$post_statement->execute([ 'id' => $applicant_id ]);

			return $post_statement->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			return false;
		}
	}

	public function getJobHistory( $id ) {
		$id = filter_var( $id, FILTER_SANITIZE_NUMBER_INT );

		$post_sql = 'SELECT
			start_date,
			end_date,
			title,
			description,
			company_name,
			company_city,
			company_state,
			created_at
		FROM applicant_history
		WHERE user_id = :id
		ORDER BY start_date DESC';

		try {
			$post_statement = $this->db->prepare( $post_sql );
			$post_statement->execute([ 'id' => $id ]);
			return $post_statement->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			return $e->getMessage();
		}
	}

}