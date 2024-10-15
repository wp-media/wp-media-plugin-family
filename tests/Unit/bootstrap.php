<?php
/**
 * Initializes the wp-media/phpunit handler, which then calls the rocket unit test suite.
 */

define( 'WPMEDIA_PHPUNIT_ROOT_DIR', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WPMEDIA_PHPUNIT_ROOT_TEST_DIR', __DIR__ );
define( 'WP_PLUGIN_DIR', '/var/www/html/wordpress/wp-content/plugins' );

require_once WPMEDIA_PHPUNIT_ROOT_DIR . 'vendor/wp-media/phpunit/Unit/bootstrap.php';