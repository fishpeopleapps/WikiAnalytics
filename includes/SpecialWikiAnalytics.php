<?php

namespace MediaWiki\Extension\WikiAnalytics;

use Html;
use SpecialPage;

class SpecialWikiAnalytics extends SpecialPage {

	public function __construct() {
		parent::__construct( 'WikiAnalytics' );
	}

	public function execute( $subPage ) {
		$this->setHeaders();
		$out = $this->getOutput();

		$out->setPageTitle( 'Wiki Analytics' );

		// Load JS + CSS (single RL module)
		$out->addModuleStyles( 'ext.wikiAnalytics' );
		$out->addModules( [ 'ext.wikiAnalytics' ] );

		// Expose range options to JS
		$out->addJsConfigVars(
			'wgWikiAnalyticsRanges',
			RangeOptions::RANGES
		);

		// JS mount point — JS owns everything below this
		$out->addHTML(
			Html::rawElement(
				'div',
				[ 'id' => 'wiki-analytics-root' ],
				''
			)
		);
	}
}
