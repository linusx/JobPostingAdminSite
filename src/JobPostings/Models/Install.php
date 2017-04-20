<?php

namespace JobPostings\Models;

use PDO;
use PDOException;

class Install extends Model {

	public function get( $id = null  ){ }
	public function add( $args = [] ){ }
	public function delete( $id ){ }

	/**
	 * Setup the DB.
	 *
	 * @return bool
	 */
	public function install() {
		$create_sql = "
		CREATE TABLE `users` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `slug` varchar(255) DEFAULT NULL,
		  `firstname` varchar(30) NOT NULL DEFAULT '',
		  `lastname` varchar(30) NOT NULL DEFAULT '',
		  `email` varchar(255) NOT NULL DEFAULT '',
		  `password` varchar(255) NOT NULL,
		  `role` varchar(20) DEFAULT NULL,
		  `created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

		CREATE TABLE `clients` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `slug` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `address` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `address2` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `city` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `state` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `zipcode` int(11) DEFAULT NULL,
		  `contact_id` int(11) DEFAULT NULL,
		  `created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

		CREATE TABLE `posts` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `posted_by` int(11) DEFAULT NULL,
		  `slug` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `short_description` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `details` text COLLATE utf8_unicode_ci,
		  `client_id` int(11) unsigned DEFAULT NULL,
		  `views` int(11) DEFAULT '0',
		  `created_at` timestamp NULL DEFAULT NULL,
		  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `Client` (`client_id`),
		  CONSTRAINT `Client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

		CREATE TABLE `applications` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `applicant_id` int(11) unsigned DEFAULT NULL,
		  `posting_id` int(11) unsigned DEFAULT NULL,
		  `created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `User` (`applicant_id`),
		  KEY `Post` (`posting_id`),
		  CONSTRAINT `Post` FOREIGN KEY (`posting_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
		  CONSTRAINT `User` FOREIGN KEY (`applicant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		
		CREATE TABLE `applicant_history` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `user_id` int(11) unsigned DEFAULT NULL,
		  `start_date` date DEFAULT NULL,
		  `end_date` date DEFAULT NULL,
		  `title` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `description` text COLLATE utf8_unicode_ci,
		  `company_name` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `company_city` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
		  `company_state` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `created_at` timestamp NULL DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `Applicant` (`user_id`),
		  CONSTRAINT `Applicant` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

		try {
			$this->db->exec($create_sql);
		} catch(PDOException $e) {}

		$hash = password_hash("password", PASSWORD_DEFAULT);
		$this->db->exec( "INSERT INTO users (slug, firstname, lastname, email, password, role, created_at) VALUES ('bill-van-pelt', 'Bill', 'Van Pelt', 'linusx@gmail.com', '" . $hash . "', 'admin', NOW())" );
		$this->db->exec( "INSERT INTO users (slug, firstname, lastname, email, password, role, created_at) VALUES ('mike-auteri', 'Mike', 'Auteri', 'linus.x@gmail.com', '" . $hash . "', 'client', NOW())" );
		$this->db->exec( "INSERT INTO users (slug, firstname, lastname, email, password, role, created_at) VALUES ('jared-kazimir', 'Jared', 'Kazimir', 'linu.sx@gmail.com', '" . $hash . "', 'applicant', NOW())" );
		$this->db->exec( "INSERT INTO users (slug, firstname, lastname, email, password, role, created_at) VALUES ('joe-choi', 'Joe', 'Choi', 'lin.usx@gmail.com', '" . $hash . "', 'applicant', NOW())" );

		// Add a client
		$this->db->exec( "INSERT INTO clients (slug, name, address, address2, city, state, zipcode, contact_id, created_at)
			VALUES 
				('58f8d3e27b6f5','Crowded','123 Main St.','','New York','NY',10012,2,'2017-04-20 15:29:38')" );

		$this->db->exec( "INSERT INTO posts (posted_by, slug, short_description, details, client_id, views, created_at, updated_at)
			VALUES 
				(1,'58f8d64dbe528','PHP Developer','Develop responsive, multiscreen enterprise web and mobile web applications and APIs using modern web technologies (HTML5, CSS3, jQuery, Backbone.js, RequireJS)
Project responsibilities include development, testing, documentation, implementation, and maintenance
Work closely with a cross-functional Scrum team to understand requirements and recommend appropriate solutions; including Project Managers/Scrum Masters, Product Owners, Designers, Backend Developers and QA Analysts
Actively participate in and contribute to global site architectural discussions and initiatives
Actively contribute to and enforce best practices, group standards
Mentor other developers, including peer reviewing code and pair programming (shadowing)
Proactively keep up-to-date on industry trends and emerging technologies; drive adoption of new technologies and best practices
Bachelor’s degree in Computer Science, Technology or a related field; or a total of at least 9 years’ experience in software development.
5+ years’ experience developing enterprise web applications
Advanced experience with object-oriented programming
Experience with API development (examples: REST/SOAP/JSON)
Demonstrated leadership as a senior/principal, subject matter expert, architect, team lead, ScrumMaster or equivalent role on past technical projects
Experience working with Javascript based Testing frameworks like Jasmine, mocha, webdriver.io
Working knowledge of​ XML/XSLT​
Code samples included with resume (examples: URLs with descriptions of which parts you developed, zip file, link to a com or equivalent profile)',1,0,'2017-04-20 15:39:57',NULL);" );

		$this->db->exec( "INSERT INTO applicant_history (user_id, start_date, end_date, title, description, company_name, company_city, company_state, created_at)
			VALUES
				(3,'2006-07-01','2008-06-01','Systems Administrator/PHP Developer','Developed intranet using object oriented PHP, MySQL, and AJAX for use with Billing, Invoicing, and Helpdesk operations. Developed PHP code to optimize NOC performance to show downtime of servers and add routing to VOIP switches.','NextCarrier Telecom Inc.','New York','NY',NULL),
				(3,'2008-06-01','2009-07-01','Backend Developer','Develop web application that students taking classes can log into and retrieve there class transcripts, certificates, and course material using object oriented PHP and MySQL. Created all front-end code using Smarty templates, javascript, HTML, and css.','Cheetah Learning','New York','NY',NULL),
				(3,'2009-07-01','2012-09-01','Backend Developer','Built Social Media enabled sites for clients such as The Nate Berkus Show (thenateshow.com), the Army, and the Food Network (Food2.com) using Joomla CMS and the KickApps Platform. Entrusted on projects for key corporate clients. Had to break down complex assignments with “impossible” requirements, figuring out a way to make them work. Sites were often utilized by company’s sales team as an example of what can be done with the KickApps/KIT Digital platform, to both potential and existing clients.','KIT Digital/KickApps','New York','NY',NULL),
				(3,'2012-10-01','2013-02-01','Backend Developer','Instrumental in releasing the a La Carte feature of eMusic. I maintained and refactored existing code and also added new features to the existing high traﬃc site. Working with WordPress to create custom plugins, themes, and custom post types.','eMusic','New York','NY',NULL),
				(3,'2013-04-01','2012-06-01','Backend Developer','Worked to rebuild there single page site from the ground up using WordPress to allow there editors easier access to publishing stories and advertisements. This included custom plugins and a custom single page theme.','Dujor Media','New York','NY',NULL),
				(3,'2013-06-01','2016-02-01','Backend Developer','Came back to rebuild the web development team and keep moving forward on there website changes. Maintained old code while trying to refactor some of the outdated code. Helped launch a new music editorial site using WordPress and full stack technologies called WonderingSound.com.','eMusic','New York','NY',NULL),
				(3,'2016-03-01','2016-12-01','Senior Backend Developer','Rebuilt two of there bigger sites ( forthepeople.com and whistleblowerattorneys.com ). Using Twig within WordPress to make WordPress more like an MVC. This helped cut down development time and kept better standards across the team. Built an internal attorney portal using Laravel that helped attorney\'s add leads on the fly from their mobile devices. Used pull requests and code reviews to also help maintain standards. Helped implement continuous integration using CircleCI. Mentored junior developers.','Morgan & Morgan','New York','NY',NULL);" );


		return true;
	}

}