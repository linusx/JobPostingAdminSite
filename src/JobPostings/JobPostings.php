<?php

namespace JobPostings;

use Twig_Extension_Debug;
use JobPostings\Models\Model;

class JobPostings {

	public $db;
	public $view;
	public $page = 1;
	public $limit = 20;
	public $start = 0;
	public $current_user;

	public static function getInstance( ) {
		static $inst = null;
		if ($inst === null) {
			$inst = new JobPostings();
		}
		return $inst;
	}

	public function __construct() {

		$dotenv = new \Dotenv\Dotenv(__DIR__ . '/../../');
		$dotenv->load();

		$this->db = new \PDO(
			'mysql:host=' . getenv('DBHOST') . ';dbname=' . getenv('DBNAME'),
			getenv('DBUSER'),
			getenv('DBPASS')
		);

		$this->current_user = !empty( $_SERVER['PHP_AUTH_USER'] ) ? $this->getUserByEmail( $_SERVER['PHP_AUTH_USER'] ) : [];

		$this->db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, FALSE);

		$this->page  = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT, [ 'options' => [ 'default' => 1, 'min_range' => 0 ] ]);
		$this->limit = filter_input( INPUT_GET, 'limit', FILTER_SANITIZE_NUMBER_INT, [ 'options' => [ 'default' => 20, 'min_range' => 0 ] ]);
		$this->start = ($this->page - 1) * $this->limit;

		$this->view = new \Slim\Views\Twig( dirname( __FILE__) . '/../../views', []);
		$this->view->addExtension(new Twig_Extension_Debug());

	}

	public function generateSlug() {
		return uniqid();
	}

	public function sendFailure( $message ) {
		echo json_encode( ['success' => false, 'message' => $message] );
	}

	public function sendSuccess( $message, $data = [] ) {
		echo json_encode( ['success' => true, 'data' => $data, 'message' => $message] );
	}

	public function getUserByEmail($email) {
		$sql = 'SELECT id, firstname, lastname, email, role, created_at FROM users WHERE email = ?';
		$statement = $this->db->prepare( $sql );
		$statement->execute( [ $email ] );
		return $statement->fetch();
	}

	public function install() {
		Model::getInstance('Install')->install();
		Header("Location: /");
	}

	public function getStates() {
		return [
			'AL'=>'ALABAMA',
			'AK'=>'ALASKA',
			'AS'=>'AMERICAN SAMOA',
			'AZ'=>'ARIZONA',
			'AR'=>'ARKANSAS',
			'CA'=>'CALIFORNIA',
			'CO'=>'COLORADO',
			'CT'=>'CONNECTICUT',
			'DE'=>'DELAWARE',
			'DC'=>'DISTRICT OF COLUMBIA',
			'FM'=>'FEDERATED STATES OF MICRONESIA',
			'FL'=>'FLORIDA',
			'GA'=>'GEORGIA',
			'GU'=>'GUAM GU',
			'HI'=>'HAWAII',
			'ID'=>'IDAHO',
			'IL'=>'ILLINOIS',
			'IN'=>'INDIANA',
			'IA'=>'IOWA',
			'KS'=>'KANSAS',
			'KY'=>'KENTUCKY',
			'LA'=>'LOUISIANA',
			'ME'=>'MAINE',
			'MH'=>'MARSHALL ISLANDS',
			'MD'=>'MARYLAND',
			'MA'=>'MASSACHUSETTS',
			'MI'=>'MICHIGAN',
			'MN'=>'MINNESOTA',
			'MS'=>'MISSISSIPPI',
			'MO'=>'MISSOURI',
			'MT'=>'MONTANA',
			'NE'=>'NEBRASKA',
			'NV'=>'NEVADA',
			'NH'=>'NEW HAMPSHIRE',
			'NJ'=>'NEW JERSEY',
			'NM'=>'NEW MEXICO',
			'NY'=>'NEW YORK',
			'NC'=>'NORTH CAROLINA',
			'ND'=>'NORTH DAKOTA',
			'MP'=>'NORTHERN MARIANA ISLANDS',
			'OH'=>'OHIO',
			'OK'=>'OKLAHOMA',
			'OR'=>'OREGON',
			'PW'=>'PALAU',
			'PA'=>'PENNSYLVANIA',
			'PR'=>'PUERTO RICO',
			'RI'=>'RHODE ISLAND',
			'SC'=>'SOUTH CAROLINA',
			'SD'=>'SOUTH DAKOTA',
			'TN'=>'TENNESSEE',
			'TX'=>'TEXAS',
			'UT'=>'UTAH',
			'VT'=>'VERMONT',
			'VI'=>'VIRGIN ISLANDS',
			'VA'=>'VIRGINIA',
			'WA'=>'WASHINGTON',
			'WV'=>'WEST VIRGINIA',
			'WI'=>'WISCONSIN',
			'WY'=>'WYOMING',
			'AE'=>'ARMED FORCES AFRICA \ CANADA \ EUROPE \ MIDDLE EAST',
			'AA'=>'ARMED FORCES AMERICA (EXCEPT CANADA)',
			'AP'=>'ARMED FORCES PACIFIC'
		];
	}
}