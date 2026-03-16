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
     * Insert monthly stats 
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
            'page_views'        => $stats['page_views'],
            'upload_bytes'      => $stats['upload_bytes'],
            'content_bytes'     => $stats['content_bytes'],
            'word_count'        => $stats['word_count'],

            'pages_created'     => $stats['pages_created'],
            'edits_this_month'  => $stats['edits_this_month'],
            'uploads_this_month' => $stats['uploads_this_month'],
            'upload_bytes_this_month' => $stats['upload_bytes_this_month'],

            'new_users'         => $stats['new_users'],
            'returning_users'   => $stats['returning_users'],
            'active_editors'    => $stats['active_editors'],
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
            'page_views'        => (int)$row->page_views,
            'upload_bytes'      => (int)$row->upload_bytes,
            'content_bytes'     => (int)$row->content_bytes,
            'word_count'        => (int)$row->word_count,
            'pages_created'     => (int)$row->pages_created,
            'edits_this_month'  => (int)$row->edits_this_month,
            'uploads_this_month' => (int)$row->uploads_this_month,
            'upload_bytes_this_month' => (int)$row->upload_bytes_this_month,
            'new_users'         => (int)$row->new_users,
            'returning_users'   => (int)$row->returning_users,
            'active_editors'    => (int)$row->active_editors,

        ];
    }

    public function getMonthlyAudioAnalytics(): array {

        $res = $this->db->select(
            'monthly_audio_analytics',
            '*',
            [],
            __METHOD__,
            [
                'ORDER BY' => 'maa_year ASC, maa_month ASC'
            ]
        );

        $rows = [];

        foreach ( $res as $row ) {
            $rows[] = [
                'year' => (int)$row->maa_year,
                'month' => (int)$row->maa_month,
                'total_listens' => (int)$row->total_listens,
                'total_seconds' => (int)$row->total_seconds,
                'completion_rate' => (float)$row->completion_rate
            ];
        }

        return $rows;
    }

    public function getMonthlyFiletypeBreakdown(): array {

    $res = $this->db->select(
        'monthly_filetype_breakdown',
        '*',
        [],
        __METHOD__,
        [
            'ORDER BY' => 'mfb_year ASC, mfb_month ASC, file_type ASC'
        ]
    );

    $rows = [];

    foreach ( $res as $row ) {
        $rows[] = [
            'year' => (int)$row->mfb_year,
            'month' => (int)$row->mfb_month,
            'file_type' => $row->file_type,
            'upload_count' => (int)$row->upload_count,
            'total_bytes' => (int)$row->total_bytes
        ];
    }

    return $rows;
}

public function getMonthlyNamespaceBreakdown(): array {

    $res = $this->db->select(
        'monthly_namespace_breakdown',
        '*',
        [],
        __METHOD__,
        [
            'ORDER BY' => 'mnb_year ASC, mnb_month ASC, namespace_id ASC'
        ]
    );

    $rows = [];

    foreach ( $res as $row ) {
        $rows[] = [
            'year' => (int)$row->mnb_year,
            'month' => (int)$row->mnb_month,
            'namespace_id' => (int)$row->namespace_id,
            'page_count' => (int)$row->page_count,
            'edit_count' => (int)$row->edit_count
        ];
    }

    return $rows;
}

public function getMonthlyTopAudio(): array {

    $res = $this->db->select(
        'monthly_top_audio',
        '*',
        [],
        __METHOD__,
        [
            'ORDER BY' => 'mta_year ASC, mta_month ASC, rank ASC'
        ]
    );

    $rows = [];

    foreach ( $res as $row ) {
        $rows[] = [
            'year' => (int)$row->mta_year,
            'month' => (int)$row->mta_month,
            'file_id' => (int)$row->file_id,
            'listens' => (int)$row->listens,
            'seconds_listened' => (int)$row->seconds_listened,
            'rank' => (int)$row->rank
        ];
    }

    return $rows;
}

public function getMonthlyTopPages(): array {

    $res = $this->db->select(
        'monthly_top_pages',
        '*',
        [],
        __METHOD__,
        [
            'ORDER BY' => 'mtp_year ASC, mtp_month ASC, rank ASC'
        ]
    );

    $rows = [];

    foreach ( $res as $row ) {
        $rows[] = [
            'year' => (int)$row->mtp_year,
            'month' => (int)$row->mtp_month,
            'page_id' => (int)$row->page_id,
            'page_title' => $row->page_title,
            'edit_count' => (int)$row->edit_count,
            'rank' => (int)$row->rank
        ];
    }

    return $rows;
}

public function getMonthlyTopUsers(): array {

    $res = $this->db->select(
        'monthly_top_users',
        '*',
        [],
        __METHOD__,
        [
            'ORDER BY' => 'mtu_year ASC, mtu_month ASC, rank ASC'
        ]
    );

    $rows = [];

    foreach ( $res as $row ) {
        $rows[] = [
            'year' => (int)$row->mtu_year,
            'month' => (int)$row->mtu_month,
            'user_id' => (int)$row->user_id,
            'user_name' => $row->user_name,
            'edit_count' => (int)$row->edit_count,
            'rank' => (int)$row->rank
        ];
    }

    return $rows;
}

public function upsertMonthlyNamespaceMetric(
    int $year,
    int $month,
    int $namespaceId,
    int $pageCount,
    int $editCount
): void {

    $this->db->upsert(
        'monthly_namespace_breakdown',
        [
            'mnb_year' => $year,
            'mnb_month' => $month,
            'namespace_id' => $namespaceId,
            'page_count' => $pageCount,
            'edit_count' => $editCount
        ],
        [ [ 'mnb_year', 'mnb_month', 'namespace_id' ] ],
        [
            'page_count' => $pageCount,
            'edit_count' => $editCount
        ],
        __METHOD__
    );
}

    /**
     * ==============================
     * AUDIO PLAY EVENT TABLE
     * ==============================
     */
    public function insertAudioPlayEvent(
        ?int $userId,
        int $fileId,
        int $secondsListened,
        bool $completed
    ): void {

    $this->db->insert(
        'audio_play_events',
        [
            'ape_user_id' => $userId,
            'ape_file_id' => $fileId,
            'ape_timestamp' => $this->db->timestamp(),
            'ape_seconds_listened' => $secondsListened,
            'ape_completed' => $completed ? 1 : 0
        ],
        __METHOD__
    );
}


}
