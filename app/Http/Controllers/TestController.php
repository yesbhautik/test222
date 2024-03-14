<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenAI;
use App\Models\Gateways;
// use App\Models\Subscriptions;
use Laravel\Cashier\Subscription;
use App\Models\Currency;
use Illuminate\Support\Facades\File;
use GuzzleHttp\Client;
use Datlechin\GoogleTranslate\Facades\GoogleTranslate;
use Laravel\Cashier\Cashier;
use Stripe\StripeClient;
use Stripe\Stripe;

class TestController extends Controller
{  
    public function test(){

    }
}
