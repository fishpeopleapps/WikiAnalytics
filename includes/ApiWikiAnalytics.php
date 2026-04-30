<?php

namespace MediaWiki\Extension\WikiAnalytics;

use ApiBase;
use Wikimedia\ParamValidator\ParamValidator;

class ApiWikiAnalytics extends ApiBase {

	public function execute() {
		$params = $this->extractRequestParams();

		// Resolve range into normalized year/month bounds
		$range = AnalyticsRangeResolver::resolve( $params );

		$dbManager = new WikiAnalyticsDBManager();

		$rows = $dbManager->getMonthlyStatsInRange(
			$range['startYear'],
			$range['startMonth'],
			$range['endYear'],
			$range['endMonth']
		);

		$namespaceBreakdown = $dbManager->getMonthlyNamespaceBreakdown(
			$range['startYear'],
			$range['startMonth'],
			$range['endYear'],
			$range['endMonth']
		);

		$filetypeBreakdown = $dbManager->getMonthlyFiletypeBreakdown(
			$range['startYear'],
			$range['startMonth'],
			$range['endYear'],
			$range['endMonth']
		);
		
		$monthlyTopPages = $dbManager->getMonthlyTopPagesInRange(
			$range['startYear'],
			$range['startMonth'],
			$range['endYear'],
			$range['endMonth']
		);

		$monthlyTopUsers = $dbManager->getMonthlyTopUsersInRange(
			$range['startYear'],
			$range['startMonth'],
			$range['endYear'],
			$range['endMonth']
		);

		// Compute totals (simple and explicit on purpose)
		$totals = $this->calculateTotals( $rows );

		$result = [
			'range'  => $range,
			'months' => $rows,
			'totals' => $totals,
			'namespaces' => $namespaceBreakdown,
			'filetypes' => $filetypeBreakdown,
			'top_pages' => $monthlyTopPages,
			'top_users' => $monthlyTopUsers,
		];

		$this->getResult()->addValue(
			null,
			$this->getModuleName(),
			$result
		);
	}

	/**
	 * Sum all numeric metrics across months
	 */
	private function calculateTotals( array $rows ): array {
		$totals = [];

		foreach ( $rows as $row ) {
			foreach ( $row as $key => $value ) {

				// Skip non-metric fields
				if ( in_array( $key, [ 'year', 'month', 'timestamp' ], true ) ) {
					continue;
				}

				if ( !isset( $totals[$key] ) ) {
					$totals[$key] = 0;
				}

				$totals[$key] += $value;
			}
		}

		return $totals;
	}

    public function getAllowedParams() {
        return [
            'scope' => [
                ParamValidator::PARAM_TYPE => [
                    'current',
                    'last12',
                    'year',
                    'range',
                    'all',
                ],
                ParamValidator::PARAM_DEFAULT => 'last12',
            ],
            'year' => [
                ParamValidator::PARAM_TYPE => 'integer',
            ],
            'startYear' => [
                ParamValidator::PARAM_TYPE => 'integer',
            ],
            'startMonth' => [
                ParamValidator::PARAM_TYPE => 'integer',
            ],
            'endYear' => [
                ParamValidator::PARAM_TYPE => 'integer',
            ],
            'endMonth' => [
                ParamValidator::PARAM_TYPE => 'integer',
            ],
        ];
    }

	public function isInternal() {
		return false;
	}

	// TODO: Add these to i18n and update to applicable messages
	public function getExamplesMessages() {
		return [
			'action=wikianalytics&scope=last12'
				=> 'apihelp-wikianalytics-example-last12',
			'action=wikianalytics&scope=year&year=2024'
				=> 'apihelp-wikianalytics-example-year',
			'action=wikianalytics&scope=range&startYear=2024&startMonth=1&endYear=2024&endMonth=12'
				=> 'apihelp-wikianalytics-example-range',
		];
	}
}
