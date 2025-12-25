<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Patient ID Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration determines how patient custom IDs are generated.
    | The custom ID format is: YYYY-SP-####-CC where:
    | - YYYY = Year of registration
    | - SP = System prefix (configurable)
    | - #### = Sequential number (resets yearly)
    | - CC = Cohort year (00 = pre-program, 01 = Year 1, etc.)
    |
    */

    'program_start_year' => env('PROGRAM_START_YEAR', 2025),

    'id_format' => [
        /*
        | System prefix for patient IDs
        | Example: 'SP' results in format 2025-SP-0001-01
        */
        'prefix' => 'SP',

        /*
        | Number of digits for sequential counter
        | Default: 4 (supports 0001 to 9999 patients per year)
        */
        'sequential_digits' => 4,

        /*
        | Number of digits for cohort year
        | Default: 2 (supports cohort 00 to 99)
        */
        'cohort_digits' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Timezone Configuration
    |--------------------------------------------------------------------------
    |
    | Timezone used for year calculation in patient ID generation.
    | Should match your application's timezone setting.
    |
    */
    'timezone' => env('APP_TIMEZONE', 'Asia/Manila'),

];
