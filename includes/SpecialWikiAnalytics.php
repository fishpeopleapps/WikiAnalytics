<?php

namespace MediaWiki\Extension\WikiAnalytics;

use MediaWiki\Extension\WikiAnalytics\AnalyticsRangeResolver;
use MediaWiki\MediaWikiServices;
use Html;
use SpecialPage;
use OOUI\ButtonInputWidget;
use OOUI\DropdownInputWidget;
use OOUI\FieldsetLayout;
use OOUI\HorizontalLayout;
use OOUI\ToggleSwitchWidget;
use OOUI\LabelWidget;


class SpecialWikiAnalytics extends SpecialPage {

    public function __construct() {
        parent::__construct( 'WikiAnalytics' );
    }

    public function execute( $subPage ) {
        $this->setHeaders();

        $out = $this->getOutput();
        $out->enableOOUI();
        $out->addModuleStyles( 'ext.wikianalytics' );
        $request = $this->getRequest();

        $format = $request->getVal( 'format', 'html' );

        $params = [
            'scope' => $request->getVal( 'range', 'last30' ),
            'year'       => $request->getInt( 'year' ),
            'startYear'  => $request->getInt( 'startYear' ),
            'startMonth' => $request->getInt( 'startMonth' ),
            'endYear'    => $request->getInt( 'endYear' ),
            'endMonth'   => $request->getInt( 'endMonth' ),
        ];

        $resolvedRange = AnalyticsRangeResolver::resolve( $params );

        $collector = new MonthlyStatsCollector();

        //$data = $collector->collect( $resolvedRange );
        $data = $collector->collect();

        $payload = [
            'range' => $resolvedRange,
            'stats' => $data,
        ];

        if ( $format === 'json' ) {
            $this->outputJson( $payload );
            return;
        }

        $out->addHTML( $this->renderHtml( $payload ) );
    }

    private function outputJson( array $payload ): void {
        $out = $this->getOutput();
        $out->disable();
        header( 'Content-Type: application/json' );
        echo json_encode( $payload, JSON_PRETTY_PRINT );
    }

    private function renderHtml( array $payload ): string {
        $html = $this->renderRangeForm( $payload['range'] );

        $range = $payload['range'];

        $rangeLabel = sprintf(
            '%04d-%02d → %04d-%02d',
            $range['startYear'],
            $range['startMonth'],
            $range['endYear'],
            $range['endMonth']
        );

        $html .= Html::element( 'h2', [], 'Wiki Analytics' );
        $html .= Html::element(
            'p',
            [],
            "Range: $rangeLabel"
        );

        $html .= '<pre style="max-height:600px;overflow:auto">';
        $html .= htmlspecialchars(
            json_encode( $payload['stats'], JSON_PRETTY_PRINT )
        );
        $html .= '</pre>';

        return $html;
    }


    private function renderRangeForm( array $range ): string {
        
        $ranges = [
            'today'       => 'Today',
            'yesterday'   => 'Yesterday',
            'last7'       => 'Last 7 days',
            'last30'      => 'Last 30 days',
            'thisMonth'   => 'This month',
            'thisYear'    => 'This year',
            'all'         => 'All time',
            'custom'      => 'Custom',
        ];

        $request = $this->getRequest();

        $currentRange = $request->getVal( 'range', 'last30' );

        $rangeDropdown = new DropdownInputWidget( [
            'name' => 'range',
            'value' => $currentRange,
            'classes' => [ 'wa-range-dropdown' ],
            'options' => [
                [ 'data' => 'today',     'label' => 'Today' ],
                [ 'data' => 'yesterday', 'label' => 'Yesterday' ],
                [ 'data' => 'last7',     'label' => 'Last 7 days' ],
                [ 'data' => 'last30',    'label' => 'Last 30 days' ],
                [ 'data' => 'thisMonth', 'label' => 'This month' ],
                [ 'data' => 'thisYear',  'label' => 'This year' ],
                [ 'data' => 'all',       'label' => 'All time' ],
                [ 'data' => 'custom',    'label' => 'Custom' ],
            ],
        ] );

        $compareToggle = new HorizontalLayout( [
            'items' => [
                new ToggleSwitchWidget( [
                    'name' => 'compare',
                    'value' => (bool)$request->getVal( 'compare', false ),
                ] ),
                new LabelWidget( [
                    'label' => 'Compare to previous year',
                    'classes' => [ 'wa-compare-label' ],
                ] ),
            ],
        ] );


        // Custom date picker (..module I was hoping for is not available in PHP)


        $topRow = new HorizontalLayout( [
            'items' => [
                $rangeDropdown,
                $compareToggle,
            ],
        ] );

        $graphPlaceholder = new FieldsetLayout( [
            'label' => ' ',
            'items' => [],
            'classes' => [ 'wa-graph-placeholder' ],
        ] );

                $applyButton = new ButtonInputWidget( [
            'label' => 'Apply',
            'type' => 'submit',
            'flags' => [ 'primary', 'progressive' ],
        ] );


        $fieldset = new FieldsetLayout( [
            'label' => 'Analytics range',
            'items' => [
                $topRow,
                $graphPlaceholder,
                $applyButton,
            ],
        ] );

        return Html::rawElement(
            'form',
            [ 'method' => 'get', 'class' => 'wa-range-form' ],
            $fieldset
        );

        
    }




    // private function checkPermissions(): void {
    //     // Why we may need permissions later - 
    //     // Heavy queries, auto-refresh, compliance concerns on high side, 
    //     // more specific data on ind users
    // }
}
