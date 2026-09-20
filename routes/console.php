<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('taj:about', function () {
    $this->info('TAJ Limited Inventory + POS + Accounting ERP');
})->purpose('Show TAJ ERP information');
