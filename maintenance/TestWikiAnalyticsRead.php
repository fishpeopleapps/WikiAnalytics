<?php

require_once __DIR__ . '/../../../maintenance/Maintenance.php';

use MediaWiki\Extension\WikiAnalytics\WikiAnalyticsDBManager;

class TestWikiAnalyticsRead extends Maintenance {

    public function __construct() {
        parent::__construct();
        $this->addDescription(
            'Test read access for WikiAnalytics monthly statistics'
        );
    }

    public function execute() {
        $dbManager = new WikiAnalyticsDBManager();

        $this->output( "Fetching ALL monthly analytics...\n\n" );

        $rows = $dbManager->getMonthlyStatsInRange(
            null,
            null,
            null,
            null
        );

        if ( empty( $rows ) ) {
            $this->output( "No analytics data found.\n" );
            return;
        }

        foreach ( $rows as $row ) {
            $this->output(
                sprintf(
                    "%d-%02d | pages: %d | articles: %d | edits: %d | users: %d | active: %d\n",
                    $row['year'],
                    $row['month'],
                    $row['page_count'],
                    $row['article_count'],
                    $row['edit_count'],
                    $row['user_count'],
                    $row['active_user_count']
                )
            );
        }

        $this->output( "\nTest complete.\n" );
    }
}

$maintClass = TestWikiAnalyticsRead::class;
require_once RUN_MAINTENANCE_IF_MAIN;
