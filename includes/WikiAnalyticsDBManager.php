<?php

namespace MediaWiki\Extension\WikiAnalytics;

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\IDatabase;

class WikiAnalyticsDBManager {

    private IDatabase $db;

    public function __construct() {
        $this->db = MediaWikiServices::getInstance()
            ->getDBLoadBalancer()
            ->getConnection( DB_PRIMARY );
    }

    /**
     * Check if stats already exist for a given month
     */
    public function monthExists( int $year, int $month ): bool {
        return (bool)$this->db->selectField(
            'persisted_monthly_analytics',
            'pma_id',
            [
                'pma_year'  => $year,
                'pma_month' => $month
            ],
            __METHOD__
        );
    }

    /**
     * Insert monthly stats (no overwrite)
     *
     * @throws \RuntimeException if month already exists
     */
    public function insertMonthlyStats(
        int $year,
        int $month,
        array $stats
    ): void {

        if ( $this->monthExists( $year, $month ) ) {
            throw new \RuntimeException(
                "Monthly analytics already exist for {$year}-{$month}"
            );
        }

        $row = [
            'pma_year'          => $year,
            'pma_month'         => $month,
            'pma_timestamp'     => $this->db->timestamp(),

            'page_count'        => $stats['page_count'],
            'article_count'     => $stats['article_count'],
            'edit_count'        => $stats['edit_count'],
            'user_count'        => $stats['user_count'],
            'active_user_count' => $stats['active_user_count'],
            'file_count'        => $stats['file_count'],
            'category_count'    => $stats['category_count'],
            'template_count'    => $stats['template_count'],
        ];

        $this->db->insert(
            'persisted_monthly_analytics',
            $row,
            __METHOD__
        );
    }

        public function deleteMonthlyStats(
        int $year,
        int $month
    ): void {

        $this->db->delete(
            'persisted_monthly_analytics',
            [
                'pma_year'  => $year,
                'pma_month' => $month,
            ],
            __METHOD__
        );
    }

    /**
     * Get stats for a specific year/month
    */
    public function getMonthlyStats(
        int $year,
        int $month
    ): ?array {

        $row = $this->db->selectRow(
            'persisted_monthly_analytics',
            '*',
            [
                'pma_year'  => $year,
                'pma_month' => $month,
            ],
            __METHOD__
        );

        if ( !$row ) {
            return null;
        }

        return $this->normalizeRow( $row );
    }

    /**
     * Get monthly stats within an optional year/month range
     *
     * Any parameter may be null to indicate an open range.
     */
    public function getMonthlyStatsInRange(
        ?int $startYear,
        ?int $startMonth,
        ?int $endYear,
        ?int $endMonth
    ): array {

        $conds = [];

        if ( $startYear !== null && $startMonth !== null ) {
            $conds[] = sprintf(
                '(pma_year > %d OR (pma_year = %d AND pma_month >= %d))',
                $startYear,
                $startYear,
                $startMonth
            );
        }

        if ( $endYear !== null && $endMonth !== null ) {
            $conds[] = sprintf(
                '(pma_year < %d OR (pma_year = %d AND pma_month <= %d))',
                $endYear,
                $endYear,
                $endMonth
            );
        }

        $res = $this->db->select(
            'persisted_monthly_analytics',
            '*',
            $conds,
            __METHOD__,
            [
                'ORDER BY' => 'pma_year ASC, pma_month ASC',
            ]
        );

        $rows = [];
        foreach ( $res as $row ) {
            $rows[] = $this->normalizeRow( $row );
        }

        return $rows;
    }

    /**
     * Normalize DB row into a clean array
     */
    private function normalizeRow( $row ): array {
        return [
            'year'              => (int)$row->pma_year,
            'month'             => (int)$row->pma_month,
            'timestamp'         => $row->pma_timestamp,

            'page_count'        => (int)$row->page_count,
            'article_count'     => (int)$row->article_count,
            'edit_count'        => (int)$row->edit_count,
            'user_count'        => (int)$row->user_count,
            'active_user_count' => (int)$row->active_user_count,
            'file_count'        => (int)$row->file_count,
            'category_count'    => (int)$row->category_count,
            'template_count'    => (int)$row->template_count,
        ];
    }


}
