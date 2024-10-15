<?php

namespace WPMediaFamilyPlugin\Controller;

defined( 'ABSPATH' ) || exit;

interface PluginFamilyInterface {
    /**
     * Process to install and activate plugin.
     *
     * @return void
     */
    public function install_activate();

    /**
     * Maybe display error notice.
     *
     * @return void
     */
    public function display_error_notice();
}