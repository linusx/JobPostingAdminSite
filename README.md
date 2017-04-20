# Job Posting Site Demo

## Installation:
1. Clone repository
2. Add a virtual host and point the doc root to the web folder.
3. Copy `env.example` to `.env` and edit file for your settings.
4. Run `composer install`
5. Run `npm install`
6. Run `gulp`
7. Visit your site at /install to install the DB and test data.
8. Visit your site at /dashboard to view the dashboard.

Username/Password is linusx@gmail.com/password.

I setup a demo site so you can see it.
Dashboard: http://jobs.vp2k.com/dashboard

Sample Post: http://jobs.vp2k.com/job/58f8d64dbe528

**Some Notes:**

This was done pretty quickly. So it is missing some validation and some pages, such as login and registration.
I took the easy way out for login because of time constraints. 

I used Slim as my router because it's quick to implement and get something up fast.
I used Twig for my templating engine.
I hope you like it and it's up to your standards. Time was an issue so I'm sure I forgot some 
stuff.


**Users Table:**

User information. Uses role to determin if the user is a client/admin/applicant. 
```
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
```

**Clients Table:**

Client information. Address and stuff. References user table for point of contact.

```
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
```

**Job Post Table:**

Job post information. Details allows html.
When a client is deleted, it will remove the posts associated with the client.

```
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
```

**Post Applications Table:**

Post application. Reference table for user and job post.
When a user or post is deleted it will remove the application.

```
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
```

**Users Job History**

Something to build there resume.

```
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```
