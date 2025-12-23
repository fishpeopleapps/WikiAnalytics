<?php

require_once __DIR__ . '/../../../maintenance/Maintenance.php';

use MediaWiki\Extension\WikiAnalytics\MonthlyStatsCollector;
use MediaWiki\Extension\WikiAnalytics\WikiAnalyticsDBManager;

class CaptureMonthlyWikiStats extends Maintenance {

    public function __construct() {
        parent::__construct();

        $this->addDescription(
            'Captures and persists core wiki statistics for the current month'
        );

        $this->addOption(
            'force',
            'Overwrite existing monthly stats if they already exist'
        );
    }

    public function execute() {
        $year  = (int)date( 'Y' );
        $month = (int)date( 'n' );

        $collector = new MonthlyStatsCollector();
        $stats     = $collector->collect();

        $dbManager = new WikiAnalyticsDBManager();

        if ( $dbManager->monthExists( $year, $month ) ) {
            if ( !$this->hasOption( 'force' ) ) {
                $this->fatalError(
                    "Monthly analytics already exist for {$year}-{$month}. " .
                    "Use --force to overwrite."
                );
            }

            // Future-safe: explicit overwrite path
            $this->output(
                "Overwriting existing monthly analytics for {$year}-{$month}\n"
            );

            // For now: delete + reinsert (explicit, auditable)
            $dbManager->deleteMonthlyStats( $year, $month );
        }

        $dbManager->insertMonthlyStats( $year, $month, $stats );

        $this->output(
            "Monthly wiki analytics captured for {$year}-{$month}\n"
        );
    }
}

$maintClass = CaptureMonthlyWikiStats::class;
require_once RUN_MAINTENANCE_IF_MAIN;
