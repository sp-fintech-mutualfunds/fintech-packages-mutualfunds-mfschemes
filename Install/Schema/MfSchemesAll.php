<?php

namespace Apps\Fintech\Packages\Mf\Schemes\Install\Schema;

use Phalcon\Db\Column;
use Phalcon\Db\Index;

class MfSchemesAll
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
                    'isin',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 50,
                        'notNull'       => true,
                    ]
                ),
                new Column(
                    'isin_reinvest',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 50,
                        'notNull'       => false
                    ]
                ),
                new Column(//Scheme Nav Name As per AMFI Data
                    'name',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 255,
                        'notNull'       => true,
                    ]
                ),
                new Column(//Scheme Name As per AMFI Data
                    'scheme_name',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 255,
                        'notNull'       => true,
                    ]
                ),
                new Column(
                    'scheme_type',//Open,closed
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 50,
                        'notNull'       => true,
                    ]
                ),
                new Column(
                    'expense_ratio_type',//Regular,Direct
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 50,
                        'notNull'       => true,
                    ]
                ),
                new Column(
                    'management_type',//Passive, Active
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 50,
                        'notNull'       => true,
                    ]
                ),
                new Column(
                    'plan_type',//Growth, dividend
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 50,
                        'notNull'       => true,
                    ]
                ),
                new Column(
                    'category_id',
                    [
                        'type'          => Column::TYPE_SMALLINTEGER,
                        'notNull'       => true,
                    ]
                ),
                new Column(
                    'amc_id',
                    [
                        'type'          => Column::TYPE_SMALLINTEGER,
                        'notNull'       => true,
                    ]
                ),
                new Column(
                    'closed',
                    [
                        'type'          => Column::TYPE_BOOLEAN,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'closed_date',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 15,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'minimum_amount',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 50,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'launch_date',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 15,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'start_date',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 15,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'crisil_rating',
                    [
                        'type'          => Column::TYPE_TINYINTEGER,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'scheme_md5',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 50,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'navs_last_updated',
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 15,
                        'notNull'       => false,
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
            'indexes' => [
                new Index(
                    'column_UNIQUE',
                    [
                        'name',
                        'id',
                    ],
                    'UNIQUE'
                )
            ],
            'options' => [
                'TABLE_COLLATION' => 'utf8mb4_general_ci'
            ]
        ];
    }

    public function indexes()
    {
        return
        [
            new Index(
                'column_INDEX',
                [
                    'isin',
                    'isin_reinvest',
                    'amc_id',
                    'name',
                    'scheme_type',
                    'expense_ratio_type',
                    'management_type',
                    'plan_type',
                    'category_id',
                    'crisil_rating',
                    'closed',
                    'day_trajectory',
                    'navs_last_updated'
                ],
                'INDEX'
            )
        ];
    }
}