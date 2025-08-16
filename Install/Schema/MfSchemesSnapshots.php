<?php

namespace Apps\Fintech\Packages\Mf\Schemes\Install\Schema;

use Phalcon\Db\Column;
use Phalcon\Db\Index;

class MfSchemesSnapshots
{
    public function columns()
    {
        return
        [
           'columns' => [
                new Column(
                    'id',
                    [
                        'type'          => Column::TYPE_INTEGER,
                        'notNull'       => true,
                        'autoIncrement' => true,
                        'primary'       => true,
                    ]
                ),
                new Column(
                    'date',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 15,
                        'notNull'       => true,
                    ]
                ),
                new Column(
                    'day_trajectory',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 5,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'day_cagr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'year_cagr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'two_year_cagr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'three_year_cagr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'five_year_cagr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'seven_year_cagr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'ten_year_cagr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'fifteen_year_cagr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'year_rr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'two_year_rr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'three_year_rr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'five_year_rr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'seven_year_rr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'ten_year_rr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'fifteen_year_rr',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'latest_nav',
                    [
                        'type'          => Column::TYPE_FLOAT,
                        'notNull'       => true,
                    ]
                )
            ],
            'options' => [
                'TABLE_COLLATION' => 'utf8mb4_general_ci'
            ]
        ];
    }
}
