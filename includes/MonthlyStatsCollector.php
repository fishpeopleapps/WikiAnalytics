<?php

namespace MediaWiki\Extension\WikiAnalytics;

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\IDatabase;
// use Wikimedia\Rdbms\Database;

class MonthlyStatsCollector {
    private IDatabase $dbr;

    public function __construct() {
        $this->dbr = MediaWikiServices::getInstance()
            ->getDBLoadBalancer()
            ->getConnection( DB_REPLICA );
    }

    /**
     * Collect all core wiki stats
     */
    public function collect(): array {
        return [
            'page_count'        => $this->getTotalPages(),
            'article_count'     => $this->getArticleCount(),
            'edit_count'        => $this->getEditCount(),
            'user_count'        => $this->getUserCount(),
            'active_user_count' => $this->getActiveUserCount(),
            'file_count'        => $this->getNamespaceCount( NS_FILE ),
            'category_count'    => $this->getNamespaceCount( NS_CATEGORY ),
            'template_count'    => $this->getNamespaceCount( NS_TEMPLATE ),
            'page_views'        => $this->getPageViews(),
            'upload_bytes'      => $this->getUploadBytes(),
            'content_bytes'     => $this->getContentBytes(),
            'word_count'        => $this->getWordCount(),

            'pages_created'     => $this->getPagesCreatedThisMonth(),
            'edits_this_month'  => $this->getEditsThisMonth(),
            'uploads_this_month'=> $this->getUploadsThisMonth(),
            'upload_bytes_this_month' => $this->getUploadBytesThisMonth(),

            'new_users'         => $this->getNewUsersThisMonth(),
            'returning_users'   => $this->getReturningUsersThisMonth(),
            'active_editors'    => $this->getActiveEditorsThisMonth(),
        ];
    }

    private function getTotalPages(): int {
        return (int)$this->getSiteStat( 'ss_total_pages' );
    }

    private function getArticleCount(): int {
        return (int)$this->getSiteStat( 'ss_good_articles' );
    }

    private function getEditCount(): int {
        return (int)$this->getSiteStat( 'ss_total_edits' );
    }

    private function getUserCount(): int {
        return (int)$this->getSiteStat( 'ss_users' );
    }

    private function getSiteStat( string $field ): int {
        return (int)$this->dbr->selectField(
            'site_stats',
            $field,
            [],
            __METHOD__
        );
    }

    private function getUploadBytes(): int {
        return (int)$this->dbr->selectField(
            'image',
            'SUM(img_size)',
            [],
            __METHOD__
        );
    }

    private function getContentBytes(): int {
        return (int)$this->dbr->selectField(
            'page',
            'SUM(page_len)',
            [],
            __METHOD__
        );
    }


    private function getPageViews(): int {
        if ( !$this->dbr->tableExists( 'hit_counter', __METHOD__ ) ) {
            return 0;
        }
        return (int)$this->dbr->selectField(
            'hit_counter',
            'SUM(page_counter)',
            [],
            __METHOD__
        );
    }

    private function getActiveUserCount(): int {
        $cutoff = $this->dbr->timestamp( time() - 30 * 24 * 60 * 60 );

        return (int)$this->dbr->selectField(
            'user',
            'COUNT(*)',
            [
                'user_touched >= ' . $this->dbr->addQuotes( $cutoff )
            ],
            __METHOD__
        );
    }

    private function getNamespaceCount( int $namespace ): int {
        return (int)$this->dbr->selectField(
            'page',
            'COUNT(*)',
            [
                'page_namespace' => $namespace,
                'page_is_redirect' => 0
            ],
            __METHOD__
        );
    }

    private function getWordCount() {
        // do something
        return 0;
    }
    private function getPagesCreatedThisMonth(): int {
        $start = $this->dbr->timestamp( strtotime( 'first day of this month 00:00:00' ) );
        $end   = $this->dbr->timestamp( strtotime( 'first day of next month 00:00:00' ) );

        return (int)$this->dbr->selectField(
            [ 'revision', 'page' ],
            'COUNT(DISTINCT page_id)',
            [
                'rev_page = page_id',
                'rev_parent_id = 0', // first revision = page creation
                'rev_timestamp >= ' . $this->dbr->addQuotes( $start ),
                'rev_timestamp < ' . $this->dbr->addQuotes( $end ),
                'page_is_redirect' => 0
            ],
            __METHOD__
        );
    }
    private function getEditsThisMonth(): int {
        $start = $this->dbr->timestamp( strtotime( 'first day of this month 00:00:00' ) );
        $end   = $this->dbr->timestamp( strtotime( 'first day of next month 00:00:00' ) );

        return (int)$this->dbr->selectField(
            'revision',
            'COUNT(*)',
            [
                'rev_timestamp >= ' . $this->dbr->addQuotes( $start ),
                'rev_timestamp < ' . $this->dbr->addQuotes( $end ),
            ],
            __METHOD__
        );
    }
    private function getUploadsThisMonth(): int {
        $start = $this->dbr->timestamp( strtotime( 'first day of this month 00:00:00' ) );
        $end   = $this->dbr->timestamp( strtotime( 'first day of next month 00:00:00' ) );

        return (int)$this->dbr->selectField(
            'image',
            'COUNT(*)',
            [
                'img_timestamp >= ' . $this->dbr->addQuotes( $start ),
                'img_timestamp < ' . $this->dbr->addQuotes( $end ),
            ],
            __METHOD__
        );
    }
    private function getUploadBytesThisMonth(): int {
        $start = $this->dbr->timestamp( strtotime( 'first day of this month 00:00:00' ) );
        $end   = $this->dbr->timestamp( strtotime( 'first day of next month 00:00:00' ) );

        return (int)$this->dbr->selectField(
            'image',
            'SUM(img_size)',
            [
                'img_timestamp >= ' . $this->dbr->addQuotes( $start ),
                'img_timestamp < ' . $this->dbr->addQuotes( $end ),
            ],
            __METHOD__
        );
    }
    private function getNewUsersThisMonth(): int {
        $start = $this->dbr->timestamp( strtotime( 'first day of this month 00:00:00' ) );
        $end   = $this->dbr->timestamp( strtotime( 'first day of next month 00:00:00' ) );

        return (int)$this->dbr->selectField(
            'user',
            'COUNT(*)',
            [
                'user_registration >= ' . $this->dbr->addQuotes( $start ),
                'user_registration < ' . $this->dbr->addQuotes( $end ),
            ],
            __METHOD__
        );
    }
    private function getReturningUsersThisMonth(): int {
        $start = $this->dbr->timestamp( strtotime( 'first day of this month 00:00:00' ) );
        $end   = $this->dbr->timestamp( strtotime( 'first day of next month 00:00:00' ) );

        return (int)$this->dbr->selectField(
            [ 'revision', 'actor', 'user' ],
            'COUNT(DISTINCT user_id)',
            [
                'rev_actor = actor_id',
                'actor_user = user_id',
                'actor_user IS NOT NULL',
                'rev_timestamp >= ' . $this->dbr->addQuotes( $start ),
                'rev_timestamp < ' . $this->dbr->addQuotes( $end ),
                'user_registration < ' . $this->dbr->addQuotes( $start ),
            ],
            __METHOD__
        );
    }
    private function getActiveEditorsThisMonth(): int {
        $start = $this->dbr->timestamp( strtotime( 'first day of this month 00:00:00' ) );
        $end   = $this->dbr->timestamp( strtotime( 'first day of next month 00:00:00' ) );

        return (int)$this->dbr->selectField(
            [ 'revision', 'actor', 'user' ],
            'COUNT(DISTINCT user_id)',
            [
                'rev_actor = actor_id',
                'actor_user = user_id',
                'actor_user IS NOT NULL',
                'rev_timestamp >= ' . $this->dbr->addQuotes( $start ),
                'rev_timestamp < ' . $this->dbr->addQuotes( $end ),
            ],
            __METHOD__
        );
    }

    public function collectNamespaceMetrics(): array {

        $res = $this->dbr->select(
            [ 'page', 'revision' ],
            [
                'namespace_id' => 'page_namespace',
                'page_count' => 'COUNT(DISTINCT page_id)',
                'edit_count' => 'COUNT(rev_id)'
            ],
            [
                'rev_page = page_id'
            ],
            __METHOD__,
            [
                'GROUP BY' => 'page_namespace'
            ]
        );

        $rows = [];

        foreach ( $res as $row ) {
            $rows[] = [
                'namespace_id' => (int)$row->namespace_id,
                'page_count' => (int)$row->page_count,
                'edit_count' => (int)$row->edit_count
            ];
        }

        return $rows;
    }

    public function collectFiletypeMetrics(): array {
        $res = $this->dbr->select(
            'image',
            [
                'file_type' => "
                    CASE
                        WHEN LOWER(substr(img_name, instr(img_name, '.') + 1)) IN ('gif','jpg','jpeg','png','svg')
                            THEN 'image'
                        WHEN LOWER(substr(img_name, instr(img_name, '.') + 1)) IN ('pdf','doc','docx','txt','ppt','pptx')
                            THEN 'document'
                        WHEN LOWER(substr(img_name, instr(img_name, '.') + 1)) = 'mp3'
                            THEN 'audio'
                        WHEN LOWER(substr(img_name, instr(img_name, '.') + 1)) = 'mp4'
                            THEN 'video'
                        ELSE 'other'
                    END
                ",   
                ######### MYSQL
                // 'file_type' => "
                //     CASE
                //         WHEN LOWER(SUBSTRING_INDEX(img_name, '.', -1)) IN ('gif','jpg','jpeg','png','svg')
                //             THEN 'image'
                //         WHEN LOWER(SUBSTRING_INDEX(img_name, '.', -1)) IN ('pdf','doc','docx','txt','ppt','pptx')
                //             THEN 'document'
                //         WHEN LOWER(SUBSTRING_INDEX(img_name, '.', -1)) = 'mp3'
                //             THEN 'audio'
                //         WHEN LOWER(SUBSTRING_INDEX(img_name, '.', -1)) = 'mp4'
                //             THEN 'video'
                //         ELSE 'other'
                //     END
                // ",
                'upload_count' => 'COUNT(*)',
                'total_bytes'  => 'SUM(img_size)'
            ],
            [],
            __METHOD__,
            [
                'GROUP BY' => 'file_type'
            ]
        );

        $rows = [];

        foreach ( $res as $row ) {
            $rows[] = [
                'file_type'    => $row->file_type,
                'upload_count' => (int)$row->upload_count,
                'total_bytes'  => (int)$row->total_bytes
            ];
        }

        return $rows;
    }

    public function collectTopPages( int $year, int $month, int $limit = 10 ): array {
        $start = $this->dbr->timestamp( sprintf( '%04d-%02d-01 00:00:00', $year, $month ) );
        $end   = $this->dbr->timestamp( strtotime( '+1 month', strtotime( $start ) ) );

        $res = $this->dbr->select(
            [ 'revision', 'page' ],
            [
                'page_id',
                'page_title',
                'edit_count' => 'COUNT(rev_id)'
            ],
            [
                'rev_page = page_id',
                'rev_timestamp >= ' . $this->dbr->addQuotes( $start ),
                'rev_timestamp < ' . $this->dbr->addQuotes( $end ),
            ],
            __METHOD__,
            [
                'GROUP BY' => 'page_id',
                'ORDER BY' => 'edit_count DESC, page_id ASC',
                'LIMIT' => $limit
            ]
        );

        $rows = [];
        foreach ( $res as $row ) {
            $rows[] = [
                'page_id' => (int)$row->page_id,
                'page_title' => $row->page_title,
                'edit_count' => (int)$row->edit_count,
            ];
        }
        $rank = 1;
        foreach ( $rows as &$row ) {
            $row['rank'] = $rank++;
        }
        unset( $row );

        return $rows;
    }

    public function collectTopUsers( int $year, int $month, int $limit = 10 ): array {
        $start = $this->dbr->timestamp( sprintf( '%04d-%02d-01 00:00:00', $year, $month ) );
        $end   = $this->dbr->timestamp( strtotime( '+1 month', strtotime( $start ) ) );

        $res = $this->dbr->select(
            [ 'revision', 'actor', 'user' ],
            [
                'user_id',
                'user_name',
                'edit_count' => 'COUNT(rev_id)'
            ],
            [
                'rev_actor = actor_id',
                'actor_user = user_id',
                'actor_user IS NOT NULL',
                'rev_timestamp >= ' . $this->dbr->addQuotes( $start ),
                'rev_timestamp < ' . $this->dbr->addQuotes( $end ),
            ],
            __METHOD__,
            [
                'GROUP BY' => 'user_id',
                'ORDER BY' => 'edit_count DESC, user_id ASC',
                'LIMIT' => $limit
            ]
        );

        $rows = [];
        foreach ( $res as $row ) {
            $rows[] = [
                'user_id' => (int)$row->user_id,
                'user_name' => $row->user_name,
                'edit_count' => (int)$row->edit_count,
            ];
        }
        $rank = 1;
        foreach ( $rows as &$row ) {
            $row['rank'] = $rank++;
        }
        unset( $row );

        return $rows;
    }
}

