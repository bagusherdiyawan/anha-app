<?php

use App\Http\Controllers\Api\V1\user\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
	echo date('Y-m-d H:i:s');
});
