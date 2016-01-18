<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

require_once realpath(__DIR__ . '/../vendor/autoload.php');

require $_tests_dir . '/includes/bootstrap.php';
