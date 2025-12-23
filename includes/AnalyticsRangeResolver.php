<?php

namespace MediaWiki\Extension\WikiAnalytics;

class AnalyticsRangeResolver {

    private const ALLOWED_SCOPES = [
        'current',
        'last12',
        'year',
        'range',
        'all',
    ];

    /**
     * Normalize request params into a safe date range
     */
    public static function resolve( array $params ): array {

        $scope = $params['scope'] ?? 'last12';

        if ( !in_array( $scope, self::ALLOWED_SCOPES, true ) ) {
            $scope = 'last12';
        }

        $now = new \DateTime();

        switch ( $scope ) {

            case 'current':
                return [
                    'startYear'  => (int)$now->format('Y'),
                    'startMonth' => (int)$now->format('n'),
                    'endYear'    => (int)$now->format('Y'),
                    'endMonth'   => (int)$now->format('n'),
                ];

            case 'year':
                $year = isset( $params['year'] )
                    ? (int)$params['year']
                    : null;

                if ( !$year ) {
                    return self::last12( $now );
                }

                return [
                    'startYear'  => $year,
                    'startMonth' => 1,
                    'endYear'    => $year,
                    'endMonth'   => 12,
                ];

            case 'range':
                return self::resolveCustomRange( $params, $now );

            case 'all':
                return [
                    'startYear'  => null,
                    'startMonth' => null,
                    'endYear'    => null,
                    'endMonth'   => null,
                ];

            case 'last12':
            default:
                return self::last12( $now );
        }
    }

    private static function last12( \DateTime $now ): array {
        $start = (clone $now)->modify('-11 months');

        return [
            'startYear'  => (int)$start->format('Y'),
            'startMonth' => (int)$start->format('n'),
            'endYear'    => (int)$now->format('Y'),
            'endMonth'   => (int)$now->format('n'),
        ];
    }

    private static function resolveCustomRange(
        array $params,
        \DateTime $now
    ): array {

        $sy = isset( $params['startYear'] ) ? (int)$params['startYear'] : null;
        $sm = isset( $params['startMonth'] ) ? (int)$params['startMonth'] : null;
        $ey = isset( $params['endYear'] ) ? (int)$params['endYear'] : null;
        $em = isset( $params['endMonth'] ) ? (int)$params['endMonth'] : null;

        if (
            !$sy || !$sm || !$ey || !$em ||
            $sm < 1 || $sm > 12 ||
            $em < 1 || $em > 12 ||
            ($ey < $sy) ||
            ($ey === $sy && $em < $sm)
        ) {
            return self::last12( $now );
        }

        return [
            'startYear'  => $sy,
            'startMonth' => $sm,
            'endYear'    => $ey,
            'endMonth'   => $em,
        ];
    }
}
