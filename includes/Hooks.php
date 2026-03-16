<?php

namespace MediaWiki\Extension\WikiAnalytics;

use DatabaseUpdater;

class Hooks {
    
    public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
        echo "WikiAnalytics schema hook executing\n";
        $dir = realpath( __DIR__ . '/../sql' );

        if ( !$dir ) {
            throw new \RuntimeException( 'WikiAnalytics sql directory not found' );
        }

        $updater->addExtensionTable(
            'audio_play_events',
            $dir . '/audio_play_events.sql'
        );
                $updater->addExtensionTable(
            'monthly_audio_analytics',
            $dir . '/monthly_audio_analytics.sql'
        );

        $updater->addExtensionTable(
            'monthly_filetype_breakdown',
            $dir . '/monthly_filetype_breakdown.sql'
        );
                $updater->addExtensionTable(
            'monthly_namespace_breakdown',
            $dir . '/monthly_namespace_breakdown.sql'
        );

        $updater->addExtensionTable(
            'monthly_top_audio',
            $dir . '/monthly_top_audio.sql'
        );
                $updater->addExtensionTable(
            'monthly_top_pages',
            $dir . '/monthly_top_pages.sql'
        );

        $updater->addExtensionTable(
            'monthly_top_users',
            $dir . '/monthly_top_users.sql'
        );
                $updater->addExtensionTable(
            'persisted_monthly_analytics',
            $dir . '/persisted_monthly_analytics.sql'
        );
        
    }
}
