<?php

namespace MediaWiki\Extension\WikiAnalytics;

use DatabaseUpdater;

class Hooks {

    public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
        die( 'WikiAnalytics LoadExtensionSchemaUpdates hook FIRED' );

        $dir = realpath( __DIR__ . '/../sql' );

        error_log( 'WikiAnalytics schema dir: ' . var_export( $dir, true ) );
        error_log( 'SQL file exists? ' . (
            $dir && file_exists( $dir . '/persisted_monthly_analytics.sql' )
                ? 'yes'
                : 'no'
        ) );

        if ( !$dir ) {
            throw new \RuntimeException( 'WikiAnalytics sql directory not found' );
        }

        $updater->addExtensionTable(
            'persisted_monthly_analytics',
            $dir . '/persisted_monthly_analytics.sql'
        );
    }
}
