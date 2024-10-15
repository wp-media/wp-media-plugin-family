<?php

declare(strict_types=1);

namespace WPMediaPluginFamily\Tests\Unit\Model\PluginFamily;

use Brain\Monkey\Functions;
use WPMediaPluginFamily\Tests\Unit\TestCase;
use WPMediaPluginFamily\Model\PluginFamily;

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

        $this->plugin_family = $this->getMockBuilder(PluginFamily::class)
                                ->setMethods( ['get_current_url'] )
                                ->getMock();
    }

    public function tear_down() {
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

        Functions\when( 'admin_url' )->justReturn( 'http://example.org/wp-admin/admin-post.php' );
        Functions\when( 'wp_create_nonce' )->justReturn( '9a68f00b8d' );
        Functions\when( 'add_query_arg' )->alias( function( $args, $link ) {
            $url = '';
            
            foreach( $args as $param => $value ) {
                $url .= $param . '=' . $value . '&';
            }

            return $link . '?' . rtrim( $url, '&' );
        } );

        $this->plugin_family->method('get_current_url')->willReturn( 'http%3A%2F%2Fexample.org%2Fwp-admin' );

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