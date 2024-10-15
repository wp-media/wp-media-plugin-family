<?php

declare(strict_types=1);

namespace WPMediaPluginFamily\Tests\Unit\Model\PluginFamily;

use Brain\Monkey\Functions;
use WPMediaPluginFamily\Tests\Unit\TestCase;
use WPMediaPluginFamily\Controller\PluginFamily;

class TestGetPostInstallEvent extends TestCase {

    public function set_up() {
        parent::set_up();
    }

    public function tear_down() {
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
        if ( ! empty( $config['get_params'] ) ) {

            foreach ( $config['get_params'] as $param => $value ) {
                $_GET[ $param ] = $value;
            }

            Functions\when( 'sanitize_text_field' )->justReturn( $_GET['action'] );
            Functions\when( 'wp_unslash' )->justReturn( $_GET['action'] );
        }    
        
        $this->assertSame( $expected, PluginFamily::get_post_install_event() );
	}
}