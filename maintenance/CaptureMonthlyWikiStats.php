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
        $year      = (int)date( 'Y' );
        $month     = (int)date( 'n' );
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

        $namespaceMetrics = $collector->collectNamespaceMetrics();
        foreach ( $namespaceMetrics as $metric ) {
            $dbManager->upsertMonthlyNamespaceMetric(
                $year,
                $month,
                $metric['namespace_id'],
                $metric['page_count'],
                $metric['edit_count']
            );
        }

        $filetypeMetrics = $collector->collectFiletypeMetrics();
        foreach ( $filetypeMetrics as $metric ) {
            $dbManager->upsertMonthlyFiletypeMetric(
                $year,
                $month,
                $metric['file_type'],
                $metric['upload_count'],
                $metric['total_bytes']
            );
        }

        $topPages = $collector->collectTopPages( $year, $month, 10 );
        foreach ( $topPages as $row ) {
            $dbManager->insertMonthlyTopPage(
                $year,
                $month,
                $row['page_id'],
                $row['page_title'],
                $row['edit_count'],
                $row['rank']
            );
        }

        // Top Users
        $topUsers = $collector->collectTopUsers( $year, $month, 10 );
        foreach ( $topUsers as $row ) {
            $dbManager->insertMonthlyTopUser(
                $year,
                $month,
                $row['user_id'],
                $row['user_name'],
                $row['edit_count'],
                $row['rank']
            );
        }

        $this->output(
            "Monthly wiki analytics captured for {$year}-{$month}\n"
        );
    }
}

$maintClass = CaptureMonthlyWikiStats::class;
require_once RUN_MAINTENANCE_IF_MAIN;
