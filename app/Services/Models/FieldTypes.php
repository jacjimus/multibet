<?php

namespace App\Services\Models;

class FieldTypes
{
    //primary key (id) type
    public const PRIMARY_KEY_TYPE = 'NCMS_FIELD_ID';

    //get field types
    public function getFieldTypes()
    {
        //field types buffer
        $buffer = [];

        //numeric
        $buffer += [
            //NCMS_FIELD_ID (id - primary key)
            self::PRIMARY_KEY_TYPE => [
                'field' => [
                    'primary' => true,
                    'guarded' => true,
                ],
                'table' => '$table->id()',
            ],

            //unsigned bigint
            'NCMS_FIELD_UNSIGNED_BIG_INT' => [
                'field' => [
                    'rules' => ['integer', 'min:0'],
                ],
                'table' => '$table->unsignedBigInteger(:column)',
            ],

            //unsigned int
            'NCMS_FIELD_UNSIGNED_INT' => [
                'field' => [
                    'rules' => ['integer', 'min:0'],
                ],
                'table' => '$table->unsignedInteger(:column)',
            ],

            //int
            'NCMS_FIELD_INT' => [
                'field' => [
                    'rules' => ['integer'],
                ],
                'table' => '$table->integer(:column)',
            ],

            //int - id
            'NCMS_FIELD_INT_ID' => [
                'field' => [
                    'rules' => ['integer', 'min:1'],
                ],
                'table' => '$table->unsignedBigInteger(:column)',
            ],

            //tiny int
            'NCMS_FIELD_TINY_INT' => [
                'field' => [
                    'rules' => ['integer'],
                ],
                'table' => '$table->tinyInteger(:column)',
            ],

            //unsigned tiny int
            'NCMS_FIELD_UNSIGNED_TINY_INT' => [
                'field' => [
                    'rules' => ['integer', 'min:0'],
                ],
                'table' => '$table->unsignedTinyInteger(:column)',
            ],

            //unsigned decimal
            'NCMS_FIELD_UNSIGNED_DECIMAL' => [
                'field' => [
                    'rules' => ['numeric', 'min:0'],
                ],
                'table' => '$table->unsignedDecimal(:column, $precision=8, $scale=2)',
            ],

            //decimal
            'NCMS_FIELD_DECIMAL' => [
                'field' => [
                    'rules' => ['numeric'],
                ],
                'table' => '$table->decimal(:column, $precision=8, $scale=2)',
            ],

            //double
            'NCMS_FIELD_DOUBLE' => [
                'field' => [
                    'rules' => ['numeric'],
                ],
                'table' => '$table->double(:column, $total_digits=8, $decimal_digits=2)',
            ],

            //float
            'NCMS_FIELD_FLOAT' => [
                'field' => [
                    'rules' => ['numeric'],
                ],
                'table' => '$table->float(:column, $total_digits=8, $decimal_digits=2)',
            ],
        ];

        //string
        $buffer += [
            //string
            'NCMS_FIELD_STRING' => [
                'field' => [
                    'rules' => ['string'],
                ],
                'table' => '$table->string(:column)',
            ],

            //string length 8
            'NCMS_FIELD_STRING_8' => [
                'field' => [
                    'rules' => ['string', 'max:8'],
                ],
                'table' => '$table->string(:column, 8)',
            ],

            //string length 16
            'NCMS_FIELD_STRING_16' => [
                'field' => [
                    'rules' => ['string', 'max:16'],
                ],
                'table' => '$table->string(:column, 16)',
            ],

            //string length 32
            'NCMS_FIELD_STRING_32' => [
                'field' => [
                    'rules' => ['string', 'max:32'],
                ],
                'table' => '$table->string(:column, 32)',
            ],

            //string length 64
            'NCMS_FIELD_STRING_64' => [
                'field' => [
                    'rules' => ['string', 'max:64'],
                ],
                'table' => '$table->string(:column, 64)',
            ],

            //string length 128
            'NCMS_FIELD_STRING_128' => [
                'field' => [
                    'rules' => ['string', 'max:128'],
                ],
                'table' => '$table->string(:column, 128)',
            ],

            //string length 256
            'NCMS_FIELD_STRING_256' => [
                'field' => [
                    'rules' => ['string', 'max:256'],
                ],
                'table' => '$table->string(:column, 256)',
            ],

            //string length 512
            'NCMS_FIELD_STRING_512' => [
                'field' => [
                    'rules' => ['string', 'max:512'],
                ],
                'table' => '$table->string(:column, 512)',
            ],
        ];

        //char
        $buffer += [
            //char length 2
            'NCMS_FIELD_CHAR_2' => [
                'field' => [
                    'rules' => ['string', 'size:2'],
                ],
                'table' => '$table->char(:column, 2)',
            ],
        ];

        //text
        $buffer += [
            'NCMS_FIELD_TEXT' => [
                'field' => [
                    'rules' => ['string'],
                ],
                'table' => '$table->text(:column)',
            ],
        ];

        //json
        $buffer += [
            'NCMS_FIELD_JSON' => [
                'field' => [
                    'rules' => ['json'],
                ],
                'table' => '$table->json(:column)',
            ],
        ];

        //boolean
        $buffer += [
            'NCMS_FIELD_BOOLEAN' => [
                'field' => [
                    'rules' => ['boolean'],
                ],
                'table' => '$table->boolean(:column)',
            ],
        ];

        //datetime
        $buffer += [
            //date
            'NCMS_FIELD_DATE' => [
                'table' => '$table->date(:column)',
            ],

            //datetime
            'NCMS_FIELD_DATETIME' => [
                'table' => '$table->dateTime(:column, $precision=0)',
            ],

            //timestamp
            'NCMS_FIELD_TIMESTAMP' => [
                'table' => '$table->timestamp(:column, $precision=0)',
            ],

            //timestamp - default current
            'NCMS_FIELD_TIMESTAMP_CURRENT' => [
                'field' => [
                    'default' => 'CURRENT_TIMESTAMP',
                ],
                'table' => '$table->timestamp(:column, $precision=0)',
            ],
        ];

        //timestamps
        $buffer += [
            'NCMS_FIELD_TIMESTAMPS' => [
                'system' => true,
                'replace' => true,
                'nullable' => true,
                'options' => [
                    'timestamps' => true,
                ],
                'replaces' => ['created_at', 'updated_at'],
                'table' => '$table->timestamps()',
            ],

            //timestamp user
            'NCMS_FIELD_TIMESTAMP_USER' => [
                'system' => true,
                'replace' => true,
                'nullable' => true,
                'replaces' => ['created_by', 'updated_by'],
                'options' => [
                    'timestamp_user' => true,
                ],
                'fields' => [
                    'created_by' => [
                        'foreign' => ['user', 'id'],
                    ],
                    'updated_by' => [
                        'foreign' => ['user', 'id'],
                    ],
                ],
            ],
        ];

        //softdeletes
        $buffer += [
            'NCMS_FIELD_SOFTDELETE' => [
                'system' => true,
                'replace' => true,
                'nullable' => true,
                'rename' => 'deleted_at',
                'options' => [
                    'softdeletes' => true,
                ],
                'traits' => [
                    'Illuminate\Database\Eloquent\SoftDeletes',
                ],
                'table' => '$table->softDeletes(:column, $precision=0)',
            ],

            //soft delete user
            'NCMS_FIELD_SOFTDELETE_USER' => [
                'system' => true,
                'replace' => true,
                'nullable' => true,
                'replaces' => ['deleted_by'],
                'options' => [
                    'softdelete_user' => true,
                ],
                'fields' => [
                    'deleted_by' => [
                        'foreign' => ['user', 'id'],
                    ],
                ],
            ],
        ];

        //morphable
        $buffer += [
            'NCMS_FIELD_MORPH' => [
                'system' => true,
                'replace' => true,
                'nullable' => true,
                'replaces' => ['{column}_id', '{column}_type'],
                'options' => [
                    'morphable' => true,
                ],
                'table' => '$table->uuidMorphs(:column)',
            ],
        ];

        //miscellaneous (ip, remember_token, ...)
        $buffer += [
            //ip address
            'NCMS_FIELD_IP_ADDRESS' => [
                'field' => [
                    'rules' => ['ip'],
                ],
                'table' => '$table->ipAddress(:column)',
            ],

            //remember token
            'NCMS_FIELD_REMEMBER_TOKEN' => [
                'field' => [
                    'nullable' => true,
                    'hidden' => true,
                ],
                'table' => '$table->rememberToken()',
            ],

            //avatar file
            /*
            'NCMS_FIELD_AVATAR' => [
                'replace' => true,
                'nullable' => true,
                'rename' => 'deleted_at',
                'options' => [
                    'softdeletes' => true,
                ],
                'traits' => [
                    'Illuminate\Database\Eloquent\SoftDeletes',
                ],
                'table' => '$table->softDeletes(:column, $precision=0)',

                'field' => [
                    'nullable' => true,
                    'hidden' => true,
                ],
                'table' => '$table->rememberToken()',
            ],
            */
        ];

        //result - field types buffer (assoc array)
        return $buffer;
    }
}
