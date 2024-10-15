<?php

namespace WPMediaFamilyPlugin\Controller;

defined( 'ABSPATH' ) || exit;

class PluginFamily implements PluginFamilyInterface {

    /**
     * Error transient.
     *
     * @var string
     */
    protected $error_transient = 'plugin_family_error';

     /**
	 * Returns an array of events this subscriber listens to
	 *
	 * @return array
	 */
    public static function get_subscribed_events (): array {
        $events = self::get_post_install_event();
        $events['admin_notices'] = 'display_error_notice';

        return $events;
    }

    /**
     * Set post install event.
     *
     * @return array
     */
    private static function get_post_install_event(): array {
        $allowed_plugin = [
            'uk-cookie-consent',
            'backwpup',
            'imagify-plugin',
            'seo-by-rank-math',
        ];

        if ( ! isset( $_GET['action'], $_GET['_wpnonce'], $_GET['plugin_to_install'] ) ) {
            return [];
        }

        $plugin = str_replace( 'plugin_family_install_', '', sanitize_text_field( $_GET['action'] ) );
        
        if ( ! in_array( $plugin, $allowed_plugin ) ) {
            return [];
        }

        return [
            'admin_post_plugin_family_install_' . $plugin => 'install_activate',
        ];
    }

    /**
     * Process to install and activate plugin.
     *
     * @return void
     */
    public function install_activate() {
        if ( ! $this->is_allowed() ) {
            wp_die(
                'Plugin Installation is not allowed.',
                '',
                [ 'back_link' => true ]
            );
        }

        // Install plugin.
        $this->install();

        // Activate plugin
        $result = activate_plugin( $this->get_plugin(), '', is_multisite() );

        if ( is_wp_error( $result ) ) {
            $this->set_error( $result );
        }

        wp_safe_redirect( wp_get_referer() );
        exit;
    }

    /**
     * Install plugin.
     *
     * @return void
     */
    private function install() {
        if ( $this->is_installed() ) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
        $result   = $upgrader->install( $this->get_download_url() );

        if ( is_wp_error( $result ) ) {
            $this->set_error( $result );
        }

        clearstatcache();
    }

    /**
     * Check if plugin is installed.
     *
     * @return boolean
     */
    private function is_installed(): bool {
        return file_exists( WP_PLUGIN_DIR . '/'. $this->get_plugin() );
    }

    /**
     * Check if installation is allowed.
     *
     * @return boolean
     */
    private function is_allowed(): bool {
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'plugin_family_install_' . $this->get_slug() ) ) {
            return false;
        }

		if ( ! current_user_can( is_multisite() ? 'manage_network_plugins' : 'install_plugins' ) ) {
            return false;
        }

        return true;
    }

    /**
     * Get plugin slug.
     *
     * @return string
     */
    private function get_slug(): string {
        return dirname( sanitize_text_field( urldecode( $_GET['plugin_to_install'] ) ) );
    }

    /**
     * Get plugin identifier.
     *
     * @return void
     */
    private function get_plugin() {
        return sanitize_text_field( urldecode( $_GET['plugin_to_install'] ) ) . '.php';
    }

    /**
     * Get plugin download url.
     *
     * @return void
     */
    private function get_download_url() {
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        $data = [
            'slug'   => $this->get_slug(),
            'fields' => [
                'download_link'     => true,
                'short_description' => false,
                'sections'          => false,
                'rating'            => false,
                'ratings'           => false,
                'downloaded'        => false,
                'last_updated'      => false,
                'added'             => false,
                'tags'              => false,
                'homepage'          => false,
                'donate_link'       => false,
            ]
        ];

        // Get Plugin Infos.
        $plugin_info = plugins_api( 'plugin_information', $data );

        if ( is_wp_error( $plugin_info ) ) {
            $this->set_error( $plugin_info );
        }

        return $plugin_info->download_link;
    }

    /**
     * Maybe display error notice.
     *
     * @return void
     */
    public function display_error_notice() {
        $errors = get_transient( $this->error_transient );

        if ( ! $errors ) {
            return;
        }

        if ( ! is_wp_error( $errors ) ) {
            delete_transient( $this->error_transient );
            return;
        }

        $errors = $errors->get_error_messages();

        if ( ! $errors ) {
            $errors[] = 'Installation process failed';
        }

        echo '<div class="error notice is-dismissible"><p>' . implode( '<br/>', $errors ) . '</p></div>';

        // Remove transient after displaying notice.
        delete_transient( $this->error_transient );
    }

    /**
     * Store an error message in a transient then redirect.
     *
     * @param object $error A WP_Error object.
     * @return void
     */
    private function set_error( $error ) {
        set_transient( $this->error_transient, $error, 30 );

        wp_safe_redirect( wp_get_referer() );
        exit;
    }
}