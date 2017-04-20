<?php

$app->get('/dashboard', 'JobPostings\Controllers\Dashboard:index')->setName('index');

$app->get('/dashboard/posts', 'JobPostings\Controllers\Posts:index')->setName('posts');
$app->get('/dashboard/post/{client_slug}/{post_slug}', 'JobPostings\Controllers\Posts:show')->setName('post');
$app->get('/dashboard/add/post', 'JobPostings\Controllers\Posts:showAdd')->setName('add-post');
$app->post('/dashboard/add/post', 'JobPostings\Controllers\Posts:add')->setName('add-post-post');
$app->delete('/dashboard/post/{id}', 'JobPostings\Controllers\Posts:delete')->setName('delete-post');

$app->get('/dashboard/users', 'JobPostings\Controllers\Applicants:index')->setName('applicants');
$app->get('/dashboard/user/{slug}', 'JobPostings\Controllers\Applicants:show')->setName('applicant');
$app->get('/dashboard/add/user', 'JobPostings\Controllers\Applicants:showAdd')->setName('add-applicant');
$app->post('/dashboard/add/user', 'JobPostings\Controllers\Applicants:add')->setName('add-applicant-post');

$app->post('/dashboard/add/user/job', 'JobPostings\Controllers\Applicants:addHistory')->setName('add-applicant-history');
$app->delete('/dashboard/user/{id}', 'JobPostings\Controllers\Applicants:delete')->setName('delete-applicant');

$app->get('/dashboard/clients', 'JobPostings\Controllers\Clients:index')->setName('clients');
$app->get('/dashboard/client/{slug}', 'JobPostings\Controllers\Clients:show')->setName('client');
$app->get('/dashboard/add/client', 'JobPostings\Controllers\Clients:showAdd')->setName('add-client');
$app->post('/dashboard/add/client', 'JobPostings\Controllers\Clients:add')->setName('add-client-post');
$app->delete('/dashboard/client/{id}', 'JobPostings\Controllers\Clients:delete')->setName('delete-client');


$app->get('/job/{post_slug}', 'JobPostings\Controllers\Jobs:job')->setName('show-job');
$app->get('/jobs', 'JobPostings\Controllers\Jobs:jobs')->setName('show-jobs');
$app->get('/', 'JobPostings\Controllers\Jobs:jobs')->setName('show-jobs');

$app->post('/apply', 'JobPostings\Controllers\Jobs:apply')->setName('apply');

$app->get('/install', 'JobPostings\JobPostings:install')->setName('init');