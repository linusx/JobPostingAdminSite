<?php
/**
 * Slim Framework doesn't have a very good MVC setup.
 * So I decided to make my own.
 *
 * @package JobPostings
 * @author Bill Van Pelt <linusx@gmail.com>
 */

namespace JobPostings\Models;

use PDO;

abstract class Model {

	protected $db;

	/**
	 * Setup the DB from the .env file
	 */
	public function __construct() {

		$dotenv = new \Dotenv\Dotenv(__DIR__ . '/../../../');
		$dotenv->load();

		$this->db = new \PDO(
			'mysql:host=' . getenv('DBHOST') . ';dbname=' . getenv('DBNAME'), getenv('DBUSER'), getenv('DBPASS')
		);

		$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, FALSE);
	}

	/**
	 * Get items/item
	 *
	 * @param integer $id
	 * @return mixed
	 */
	abstract protected function get( $id = null );

	/**
	 * Add new item
	 *
	 * @param array $args
	 * @return mixed
	 */
	abstract protected function add( $args = [] );

	/**
	 * Delete an item
	 *
	 * @param integer $id
	 * @return mixed
	 */
	abstract protected function delete( $id );

	/**
	 * Method to check if slug exists
	 *
	 * @param $slug
	 * @return mixed
	 */
	public function slugExists( $slug, $table ) {
		$args = ['slug' => $slug];
		$sql = 'SELECT slug FROM ' . $table . ' WHERE slug = :slug';

		$st = $this->db->prepare( $sql );
		$st->execute( $args );
		$found = $st->fetch(PDO::FETCH_ASSOC);
		return ! empty( $found );
	}

	/**
	 * Had to work around Slims Inability to make an MVC
	 *
	 * @param string $class
	 * @return null
	 */
	public static function getInstance( $class = __CLASS__ ) {
		$class_name = 'JobPostings\Models\\' . $class;
		static $inst = null;
		if ($inst === null) {
			$inst = new $class_name;
		}
		return $inst;
	}
}