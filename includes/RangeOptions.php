<?php

namespace MediaWiki\Extension\WikiAnalytics;

class RangeOptions {

    public const DEFAULT_RANGE = 'last30';

    public const RANGES = [
        'today'     => 'Today',
        'yesterday' => 'Yesterday',
        'last7'     => 'Last 7 days',
        'last30'    => 'Last 30 days',
        'thisMonth' => 'This month',
        'thisYear'  => 'This year',
        'all'       => 'All time',
        'custom'    => 'Custom',
    ];
}
