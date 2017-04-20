<?php
/**
 * Clients Model
 *
 * @package JobPostings
 * @author Bill Van Pelt <linusx@gmail.com>
 */

namespace JobPostings\Models;

use JobPostings\JobPostings;
use PDO;
use PDOException;

class Clients extends Model {

	/**
	 * Get all clients.
	 *
	 * @param null $slug
	 * @return array|mixed|string
	 */
	public function get( $slug = null ) {
		$slug = filter_var( $slug, FILTER_SANITIZE_STRING );

		$sql = 'SELECT 
					a.id, 
					a.slug,
					a.name,
					a.address,
					a.address2,
					a.city,
					a.state,
					a.zipcode, 
					a.created_at,
					b.firstname as contact_firstname,
					b.lastname as contact_lastname,
					b.email as contact_email,
					b.created_at as contact_created_at
				FROM 
					clients a
				LEFT OUTER JOIN users b ON a.contact_id = b.id';

		if ( ! empty( $slug ) ) {
			$sql .= ' WHERE a.slug = ' . $this->db->quote( $slug );
		}

		try {
			$statement = $this->db->prepare( $sql );
			$statement->execute();
			if ( ! empty( $slug ) ) {
				$clients = $statement->fetch(PDO::FETCH_ASSOC);
			} else {
				$clients = $statement->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch(PDOException $e) {
			return $e->getMessage();
		}

		return $clients;
	}

	/**
	 * Add new client.
	 *
	 * @param array $args
	 * @return array|mixed|string
	 */
	public function add( $args = [] ) {

		$args['slug'] = JobPostings::getInstance()->generateSlug();

		$sql = 'INSERT INTO clients (
			slug,
			name,
			address,
			address2,
			city,
			state,
			zipcode,
			contact_id,
			created_at
		) VALUES (
			:slug,
			:name,
			:address,
			:address2,
			:city,
			:state,
			:zipcode,
			:contact_id,
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
	 * Delete client.
	 *
	 * @param int $id
	 * @return array
	 */
	public function delete( $id ) {
		$id = filter_var( $id, FILTER_SANITIZE_NUMBER_INT );

		$sql = "DELETE FROM clients WHERE id = :id";

		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
		} catch(PDOException $e) {
			return [ 'status' => false, 'message' => $e->getMessage() ];
		}

		$sql = "DELETE FROM posts WHERE client_id = :id";

		try {
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
		} catch(PDOException $e) {
			return ['status' => false, 'message' => $e->getMessage()];
		}

		return [ 'status' => true, 'message' => '' ];
	}

	/**
	 * Get a client.
	 *
	 * @param $client_slug
	 * @return array|mixed|string
	 */
	public function getClient( $client_slug ) {
		$client_slug = filter_var( $client_slug, FILTER_SANITIZE_STRING );

		$client = $this->get( $client_slug );
		if ( empty( $client ) ) {
			return false;
		}

		$client['posts'] = $this->getPosts( $client['id'] );

		return $client;
	}

	/**
	 * Get all posts by a client.
	 *
	 * @param $client_id
	 * @return array|bool
	 */
	public function getPosts( $client_id ) {
		$client_id = filter_var( $client_id, FILTER_SANITIZE_NUMBER_INT );

		$posts_sql = 'SELECT 
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
				b.created_at as posted_by_created_at
			FROM 
				posts a 
			LEFT OUTER JOIN users b ON a.posted_by = b.id
			WHERE 
				a.client_id = :client_id';

		try {
			$posts_statement = $this->db->prepare($posts_sql);
			$posts_statement->execute(['client_id' => $client_id]);
			return $posts_statement->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			return false;
		}

	}
}