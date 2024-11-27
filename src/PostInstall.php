<?php

namespace WPMedia\PluginFamily;

use Composer\Script\Event;

class PostInstall {
    private static $files = [
        'PluginFamily',
        'wp_media_plugins',
    ];

    public static function update_model_data( Event $event ) {

        $output = $event->getIO();
        $composer = $event->getComposer();
        $extra = $composer->getPackage()->getExtra();
        $error_message = 'Post-package installation script failed.';

        if ( ! isset( $extra['plugin_domain'] ) ) {
            $output->writeError( 'Plugin domain is not set in the composer extra configuration.' );
            return;
        }

        foreach ( self::$files as $file ) {
            $path = __DIR__ . '/Model/' . $file . '.php';

            if ( ! file_exists( $path ) ) {
                $output->writeError( 'Could not find file: ' . $path . ', Does it exist?' );
                return;
            }

            $content = file_get_contents( $path );

            if ( false === $content ) {
                $output->writeError( 'Failed to read the file: ' . $path );
                return;
            }

            $updated_content = str_replace( '%domain%', $extra['plugin_domain'], $content );
            $result = file_put_contents( $path, $updated_content );

            if ( false === $result ) {
                $output->writeError( 'Failed to write the updated content to the file: ' . $path );
                return;
            }

            $io->write( 'Text domain has been updated.' );
        }
    }
}