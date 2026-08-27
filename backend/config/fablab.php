<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Purchase Request window
    |--------------------------------------------------------------------------
    |
    | How many days a customer gets to file their Purchase Request with CSPC
    | procurement and bring back a PR number. The order waits, holding its
    | stock, until then; past it the order is closed and the stock released.
    |
    | The deadline is stamped onto each order at checkout, so changing this
    | only affects orders placed afterwards — it never moves the goalposts on
    | a customer who has already started.
    |
    */

    'pr_deadline_days' => (int) env('FABLAB_PR_DEADLINE_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Procurement contact
    |--------------------------------------------------------------------------
    |
    | Where customers are sent to file the Purchase Request. Shown at checkout
    | and on the order once it is waiting.
    |
    */

    'procurement_email' => env('FABLAB_PROCUREMENT_EMAIL', 'procurement@cspc.edu.ph'),

];
