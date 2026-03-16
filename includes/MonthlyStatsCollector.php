<?php

namespace MediaWiki\Extension\WikiAnalytics;

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\IDatabase;
// use Wikimedia\Rdbms\Database;

class MonthlyStatsCollector {

    // private Database $dbr;
    private IDatabase $dbr;

    public function __construct() {
        $this->dbr = MediaWikiServices::getInstance()
            ->getDBLoadBalancer()
            ->getConnection( DB_REPLICA );
    }

//     public function __construct() {
//     $db = MediaWikiServices::getInstance()
//         ->getDBLoadBalancer()
//         ->getConnection( DB_REPLICA );

//     if ( !$db instanceof Database ) {
//         throw new \RuntimeException( 'Failed to acquire database connection' );
//     }

//     $this->dbr = $db;
// }


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
    private function getPagesCreatedThisMonth() {
        // do something
        return 0;
    }
    private function getEditsThisMonth() {
        // do something
        return 0;
    }
    private function getUploadsThisMonth() {
        // do something
        return 0;
    }
    private function getUploadBytesThisMonth() {
        // do something
        return 0;
    }
    private function getNewUsersThisMonth() {
        // do something
        return 0;
    }
    private function getReturningUsersThisMonth() {
        // do something
        return 0;
    }
    private function getActiveEditorsThisMonth() {
        // do something
        return 0;
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
}

