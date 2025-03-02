<?php

namespace Apps\Fintech\Packages\Mf\Schemes\Install\Schema;

use Phalcon\Db\Column;
use Phalcon\Db\Index;

class MfSchemes
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
                new Column(
                    'amfi_code',
                    [
                        'type'          => Column::TYPE_INTEGER,
                        'notNull'       => true,
                    ]
                ),
                new Column(
                    'vendor_code',//Like Kuvera/Value Research
                    [
                        'type'          => Column::TYPE_VARCHAR,
                        'size'          => 50,
                        'notNull'       => false,
                    ]
                ),
                new Column(
                    'name',
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


                // new Column(
                //     'lump_available',
                //     [
                //         'type'          => Column::TYPE_BOOLEAN,
                //         'notNull'       => false,
                //     ]
                // ),
                // new Column(
                //     'lump_min',
                //     [
                //         'type'          => Column::TYPE_FLOAT,
                //         'notNull'       => false,
                //     ]
                // ),
                // new Column(
                //     'lump_min_additional',
                //     [
                //         'type'          => Column::TYPE_FLOAT,
                //         'notNull'       => false,
                //     ]
                // ),
                // new Column(
                //     'lump_max',
                //     [
                //         'type'          => Column::TYPE_FLOAT,
                //         'notNull'       => false,
                //     ]
                // ),
                // new Column(
                //     'lump_multiplier',
                //     [
                //         'type'          => Column::TYPE_FLOAT,
                //         'notNull'       => false,
                //     ]
                // ),

                // new Column(
                //     'sip_available',
                //     [
                //         'type'          => Column::TYPE_BOOLEAN,
                //         'notNull'       => false,
                //     ]
                // ),
                // new Column(
                //     'sip_min',
                //     [
                //         'type'          => Column::TYPE_FLOAT,
                //         'notNull'       => false,
                //     ]
                // ),
                // new Column(
                //     'sip_max',
                //     [
                //         'type'          => Column::TYPE_FLOAT,
                //         'notNull'       => false,
                //     ]
                // ),
                // new Column(
                //     'sip_multiplier',
                //     [
                //         'type'          => Column::TYPE_FLOAT,
                //         'notNull'       => false,
                //     ]
                // ),
                // new Column(
                //     'sip_maximum_gap',
                //     [
                //         'type'          => Column::TYPE_SMALLINTEGER,
                //         'notNull'       => false,
                //     ]
                // ),

                // new Column(
                //     'redemption_allowed',
                //     [
                //         'type'          => Column::TYPE_BOOLEAN,
                //         'notNull'       => true,
                //     ]
                // ),
                // new Column(
                //     'redemption_amount_multiple',
                //     [
                //         'type'          => Column::TYPE_FLOAT,
                //         'notNull'       => false,
                //     ]
                // ),
                // new Column(
                //     'redemption_amount_minimum',
                //     [
                //         'type'          => Column::TYPE_FLOAT,
                //         'notNull'       => false,
                //     ]
                // ),
                // new Column(
                //     'redemption_quantity_multiple',
                //     [
                //         'type'          => Column::TYPE_FLOAT,
                //         'notNull'       => false,
                //     ]
                // ),
                // new Column(
                //     'redemption_quantity_minimum',
                //     [
                //         'type'          => Column::TYPE_FLOAT,
                //         'notNull'       => false,
                //     ]
                // ),

            ],
            'indexes' => [
                new Index(
                    'column_UNIQUE',
                    [
                        'isin',
                        'amfi_code'
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
                    'amfi_code',
                    'name',
                    'scheme_type',
                    'expense_ratio_type',
                    'management_type',
                    'plan_type',
                    'category_id',
                    'amc_id'
                ],
                'INDEX'
            )
        ];
    }
}
