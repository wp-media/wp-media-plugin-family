<?php

declare(strict_types=1);

namespace WPMedia\PluginFamily\Tests\Unit\Model\PluginFamily;

use Brain\Monkey\Functions;
use WPMedia\PluginFamily\Tests\Unit\TestCase;
use WPMedia\PluginFamily\Model\PluginFamily;

class TestFilterPluginsByActivation extends TestCase {

    /**
     * @var PluginFamily
     */
    protected $plugin_family;

    /**
     * @var array
     */
    protected $config = [];

    public function set_up() {
        parent::set_up();
        
        $this->plugin_family = new PluginFamily();

        $_SERVER = [
            'SERVER_PORT' => 80,
            'HTTP_HOST' => 'example.org',
            'REQUEST_URI' => '/wp-admin',
        ];
    }

    public function tear_down() {
        $_SERVER = [];

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
        $this->config = $config;

        if ( 'both' === $config['order'] ) {
            $this->assertSame( $expected, $this->get_filtered_plugins() );
            return;
        }

        Functions\when( 'is_plugin_active' )->alias( function( $plugin ) use ($config) {
            return $plugin === $config['active_plugin'];
        } );

        Functions\when( 'admin_url' )->alias(
			function ( $path ) {
				return "http://example.org/wp-admin/{$path}";
			}
		);
        Functions\when( 'wp_create_nonce' )->justReturn( '9a68f00b8d' );
        Functions\when( 'add_query_arg' )->alias( function( $args, $link ) {
            $url = '';
            
            foreach( $args as $param => $value ) {
                $url .= $param . '=' . $value . '&';
            }

            return $link . '?' . rtrim( $url, '&' );
        } );

        Functions\when( 'wp_unslash' )->returnArg();
        Functions\when( 'is_ssl' )->justReturn( true );

        if ( isset( $config['is_installed'] ) ) {
            Functions\when( 'file_exists' )->justReturn( $config['is_installed'] );
        }
		
        $this->assertSame( $expected, $this->get_filtered_plugins() );
	}

    private function get_filtered_plugins(): array {
        $filtered = $this->plugin_family
            ->filter_plugins_by_activation( $this->config['plugins'], $this->config['main_plugin'] );

        if ( 'both' !== $this->config['order'] ) {
            return $filtered[ $this->config['order'] ];
        }

        return $filtered;
    }
}