<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\GatewaySelector;
use App\Models\Currency;
use App\Models\Gateways;
use App\Models\Setting;
use App\Models\User;
use App\Services\PaymentGateways\CoingateService;
use App\Services\PaymentGateways\PaddleService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

# Controls ALL Payment Gateway actions
class GatewayController extends Controller
{
    # Helper functions 
    public function gatewayCodesArray()
    {
        return array(
            'stripe',
            'paypal',
            'yokassa',
            // "twocheckout",
            'iyzico',
            'paystack',
            // "walletmaxpay",
            'banktransfer',
            'revenuecat',
            'coingate',
//            'paddle',
			// "razorpay",
			// "coinbase",
        );
    }
    public function defaultGatewayDefinitions()
    {
        $gateways = [
            [
                "code" => "stripe",
                "title" => "Stripe",
                "link" => "https://stripe.com/",
                "active" => 0,                      //if user activated this gateway - dynamically filled in main page
                "available" => 1,                   //if gateway is available to use
                "img" => "/assets/img/payments/stripe.svg",
                "whiteLogo" => 0,                   //if gateway logo is white
                "mode" => 1,                        // Option in settings - Automatically set according to the "Development" mode. "Development" ? sandbox : live (PAYPAL - 1)
                "sandbox_client_id" => 1,           // Option in settings 0-Hidden 1-Visible
                "sandbox_client_secret" => 1,       // Option in settings
                "sandbox_app_id" => 0,              // Option in settings
                "live_client_id" => 1,              // Option in settings
                "live_client_secret" => 1,          // Option in settings
                "live_app_id" => 0,                 // Option in settings
                "currency" => 1,                    // Option in settings
                "currency_locale" => 0,             // Option in settings
                "base_url" => 1,                    // Option in settings
                "sandbox_url" => 0,                 // Option in settings
                "locale" => 0,                      // Option in settings
                "validate_ssl" => 0,                // Option in settings
                "logger" => 0,                      // Option in settings
                "notify_url" => 0,                  // Gateway notification url at our side
                "webhook_secret" => 0,              // Option in settings
                "tax" => 1,              // Option in settings
                "bank_account_details" => 0,
                "bank_account_other" => 0,
            ],
            [
                "code" => "paypal",
                "title" => "PayPal",
                "link" => "https://www.paypal.com/",
                "active" => 0,
                "available" => 1,
                "img" => "/assets/img/payments/paypal.svg",
                "whiteLogo" => 0,
                "mode" => 1,
                "sandbox_client_id" => 1,
                "sandbox_client_secret" => 1,
                "sandbox_app_id" => 0,
                "live_client_id" => 1,
                "live_client_secret" => 1,
                "live_app_id" => 1,
                "currency" => 1,
                "currency_locale" => 1,
                "notify_url" => 0,
                "base_url" => 0,
                "sandbox_url" => 0,
                "locale" => 0,
                "validate_ssl" => 0,
                "webhook_secret" => 0,
                "logger" => 0,
                "tax" => 1,              // Option in settings
                "bank_account_details" => 0,
                "bank_account_other" => 0,
            ],
            [
                "code" => "yokassa",
                "title" => "Yokassa",
                "link" => "https://yokassa.ru/",
                "active" => 0,
                "available" => 1,
                "img" => "/assets/img/payments/yokassa.svg",
                "whiteLogo" => 0,
                "mode" => 1,
                "sandbox_client_id" => 1,
                "sandbox_client_secret" => 1,
                "sandbox_app_id" => 0,
                "live_client_id" => 1,
                "live_client_secret" => 1,
                "live_app_id" => 0,
                "currency" => 1,
                "currency_locale" => 0,
                "notify_url" => 0,
                "base_url" => 0,
                "sandbox_url" => 0,
                "locale" => 0,
                "validate_ssl" => 0,
                "webhook_secret" => 0,
                "logger" => 0,
                "tax" => 1,              // Option in settings
                "bank_account_details" => 0,
                "bank_account_other" => 0,
            ],
            [
                "code" => "iyzico",
                "title" => "iyzico",
                "link" => "https://www.iyzico.com/",
                "active" => 0,
                "available" => 1,
                "img" => "/assets/img/payments/iyzico.svg",
                "whiteLogo" => 0,
                "mode" => 1,
                "sandbox_client_id" => 1,
                "sandbox_client_secret" => 1,
                "sandbox_app_id" => 0,
                "live_client_id" => 1,
                "live_client_secret" => 1,
                "live_app_id" => 0,
                "currency" => 1,
                "currency_locale" => 0,
                "notify_url" => 0,
                "base_url" => 1,
                "sandbox_url" => 1,
                "locale" => 0,
                "validate_ssl" => 0,
                "webhook_secret" => 0,
                "logger" => 0,
                "tax" => 1,              // Option in settings
                "bank_account_details" => 0,
                "bank_account_other" => 0,
            ],
            [
                "code" => "banktransfer",
                "title" => "Bank Transfer",
                "link" => "",
                "active" => 0,                      //if user activated this gateway - dynamically filled in main page
                "available" => 1,                   //if gateway is available to use
                "img" => "/assets/img/payments/banktransfer.png",
                "whiteLogo" => 0,                   //if gateway logo is white
                "mode" => 0,                        // Option in settings - Automatically set according to the "Development" mode. "Development" ? sandbox : live (PAYPAL - 1)
                "sandbox_client_id" => 0,           // Option in settings 0-Hidden 1-Visible
                "sandbox_client_secret" => 0,       // Option in settings
                "sandbox_app_id" => 0,              // Option in settings
                "live_client_id" => 0,              // Option in settings
                "live_client_secret" => 0,          // Option in settings
                "live_app_id" => 0,                 // Option in settings
                "currency" => 1,                    // Option in settings
                "currency_locale" => 0,             // Option in settings
                "base_url" => 0,                    // Option in settings
                "sandbox_url" => 0,                 // Option in settings
                "locale" => 0,                      // Option in settings
                "validate_ssl" => 0,                // Option in settings
                "logger" => 0,                      // Option in settings
                "notify_url" => 0,                  // Gateway notification url at our side
                "webhook_secret" => 0,              // Option in settings
                "tax" => 1,              // Option in settings
                "bank_account_details" => 1,
                "bank_account_other" => 1,
            ],
            [
                "code" => "paystack",
                "title" => "Paystack",
                "link" => "https://paystack.com/",
                "active" => 0,
                "available" => 1,
                "img" => "/assets/img/payments/paystack-2.svg",
                "whiteLogo" => 0,
                "mode" => 1,
                "sandbox_client_id" => 1,
                "sandbox_client_secret" => 1,
                "sandbox_app_id" => 0,
                "live_client_id" => 1,
                "live_client_secret" => 1,
                "live_app_id" => 0,
                "currency" => 1,
                "currency_locale" => 0,
                "notify_url" => 0,
                "base_url" => 0,
                "sandbox_url" => 0,
                "locale" => 0,
                "validate_ssl" => 0,
                "webhook_secret" => 0,
                "logger" => 0,
                "tax" => 1,              // Option in settings
                "bank_account_details" => 0,
                "bank_account_other" => 0,
            ],			
			[
                "code" => "revenuecat",
                "title" => "RevenueCat",
                "link" => "https://www.revenuecat.com/",
                "active" => 1,
                "available" => 1,
                "img" => "/assets/img/payments/revenuecat.png",
                "whiteLogo" => 0,
                "mode" => 0,
                "sandbox_client_id" => 0,
                "sandbox_client_secret" => 0,
                "sandbox_app_id" => 0,
                "live_client_id" => 1,
                "live_client_secret" => 0,
                "live_app_id" => 0,
                "currency" => 0,
                "currency_locale" => 0,
                "notify_url" => 0,
                "base_url" => 0,
                "sandbox_url" => 0,
                "locale" => 0,
                "validate_ssl" => 0,
                "webhook_secret" => 0,
                "logger" => 0,
                "tax" => 1,              // Option in settings
                "bank_account_details" => 0,
                "bank_account_other" => 0,
            ],
            CoingateService::gatewayDefinitionArray(), // Coingate
//            PaddleService::gatewayDefinitionArray(), // Coingate


			// [
            //     "code" => "razorpay",
            //     "title" => "Razorpay",
            //     "link" => "https://razorpay.com/",
            //     "active" => 0,
            //     "available" => 1,
            //     "img" => "/assets/img/payments/razorpay.svg",
            //     "whiteLogo" => 0,
            //     "mode" => 1,
            //     "sandbox_client_id" => 1,
            //     "sandbox_client_secret" => 1,
            //     "sandbox_app_id" => 0,
            //     "live_client_id" => 1,
            //     "live_client_secret" => 1,
            //     "live_app_id" => 0,
            //     "currency" => 0,
            //     "currency_locale" => 0,
            //     "notify_url" => 0,
            //     "base_url" => 0,
            //     "sandbox_url" => 0,
            //     "locale" => 0,
            //     "validate_ssl" => 0,
            //     "webhook_secret" => 0,
            //     "logger" => 0,
            //     "tax" => 1,              // Option in settings
            //     "bank_account_details" => 0,
            //     "bank_account_other" => 0,
            // ],
			// [
            //     "code" => "coinbase",
            //     "title" => "Coinbase",
            //     "link" => "https://paystack.com/",
            //     "active" => 0,
            //     "available" => 1,
            //     "img" => "/assets/img/payments/coinbase.svg",
            //     "whiteLogo" => 0,
            //     "mode" => 1,
            //     "sandbox_client_id" => 1,
            //     "sandbox_client_secret" => 1,
            //     "sandbox_app_id" => 0,
            //     "live_client_id" => 1,
            //     "live_client_secret" => 1,
            //     "live_app_id" => 0,
            //     "currency" => 1,
            //     "currency_locale" => 0,
            //     "notify_url" => 0,
            //     "base_url" => 0,
            //     "sandbox_url" => 0,
            //     "locale" => 0,
            //     "validate_ssl" => 0,
            //     "webhook_secret" => 0,
            //     "logger" => 0,
            //     "tax" => 1,              // Option in settings
            //     "bank_account_details" => 0,
            //     "bank_account_other" => 0,
            // ],

			            // [
            //     "code" => "twocheckout",
            //     "title" => "TwoCheckout",
            //     "link" => "https://2checkout.com/",
            //     "active" => 0,
            //     "available" => 1,
            //     "img" => "/assets/img/payments/2checkout.svg",
            //     "whiteLogo" => 0,
            //     "mode" => 1,
            //     "sandbox_client_id" => 0,
            //     "sandbox_client_secret" => 0,
            //     "sandbox_app_id" => 0,
            //     "live_client_id" => 1,
            //     "live_client_secret" => 1,
            //     "live_app_id" => 0,
            //     "currency" => 1,
            //     "currency_locale" => 0,
            //     "notify_url" => 0,
            //     "base_url" => 0,
            //     "sandbox_url" => 0,
            //     "locale" => 0,
            //     "validate_ssl" => 0,
            //     "webhook_secret" => 0,
            //     "logger" => 0,
            //     "tax" => 1,              // Option in settings
            //     "bank_account_details" => 0,
            //     "bank_account_other" => 0,
            // ],
            // [   
            //     "code" => "walletmaxpay",
            //     "title" => "WalletMaxPay",
            //     "link" => "https://walletmaxpay.com/",
            //     "active" => 0,                      
            //     "available" => 1,                   
            //     "img" => "/assets/img/payments/walletmaxpay.png",
            //     "whiteLogo" => 0,                   //if gateway logo is white
            //     "mode" => 1,                        // Option in settings - Automatically set according to the "Development" mode. "Development" ? sandbox : live (PAYPAL - 1)
            //     "sandbox_client_id" => 0,           // Option in settings 0-Hidden 1-Visible
            //     "sandbox_client_secret" => 0,       // Option in settings
            //     "sandbox_app_id" => 0,              // Option in settings
            //     "live_client_id" => 1,              // Option in settings
            //     "live_client_secret" => 1,          // Option in settings
            //     "live_app_id" => 1,                 // Option in settings
            //     "currency" => 1,                    // Option in settings
            //     "currency_locale" => 0,             // Option in settings
            //     "base_url" => 0,                    // Option in settings
            //     "sandbox_url" => 0,                 // Option in settings
            //     "locale" => 0,                      // Option in settings
            //     "validate_ssl" => 0,                // Option in settings
            //     "logger" => 0,                      // Option in settings
            //     "notify_url" => 0,                  // Gateway notification url at our side
            //     "webhook_secret" => 0,              // Option in settings
            //     "tax" => 1,              // Option in settings
            //     "bank_account_details" => 0,
            //     "bank_account_other" => 0,
            // ],
        ];

        return $gateways;
    }
    public function readManageGatewaysPageData(){

        $defaultGateways = self::defaultGatewayDefinitions();
        $requiredGatewayData = [];

        $gatewayActiveData = [];
        $gatewaysData = Gateways::all();
        foreach($gatewaysData as $gw){
            array_push($gatewayActiveData, array(
                "code" => $gw->code,
                "is_active" => $gw->is_active
            ));
        }

        foreach ($defaultGateways as $gateway) {
            $code = $gateway['code'];
            $is_active = 0;
            foreach($gatewaysData as $gwdata){
                if($gwdata['code'] == $code){
                    $is_active = $gwdata['is_active'];
                    break;
                }
            }
            array_push($requiredGatewayData, array(
                "code" => $code,
                "title" => $gateway['title'],
                "link" => $gateway['link'],
                "available" => $gateway['available'],
                "img" => $gateway['img'],
                "whiteLogo" => $gateway['whiteLogo'],
                "active" => $is_active ?? 0,
            ));
        }

        return $requiredGatewayData;
    }
    function getCurrencyOptions($index){
        $returnText="";
        $currencies = Currency::all();
        foreach ($currencies as $currency) {
            $cindex = $currency->id;
            $country = self::appendNBSPtoString($currency->country, 41);
            $code = self::appendNBSPtoString($currency->code, 5);
            $text = $country.$code.$currency->symbol;
            $selected = (int)$index == (int)$cindex ? 'selected' : '';
            $returnText = $returnText.'<option value="'.$cindex.'" '.$selected.' style=\'font-family: "Courier New", Courier, monospace;\' >'.$text.'</option>';
        }
        return $returnText;
    }
    public function appendNBSPtoString($stringForAppend, $charCount){ # Fills given string with &nbsp; at the end. Used in Country select tag.
        $length = Str::length($stringForAppend);
        $remainingCharcount = $charCount - $length;
        if($remainingCharcount<1){
            return $stringForAppend;
        }else{
            $newString = $stringForAppend;
            for($i=1; $i <= $remainingCharcount; $i++){
                $newString = $newString.'&nbsp;';
            }
            return $newString;
        }
    }

    # Main functions
    public function paymentGateways(){ # Index page of Payment Gateways in Admin Panel
        $gateways = self::readManageGatewaysPageData();
        return view('panel.admin.finance.gateways.index', compact('gateways'));
    }

    # Settings page of gateways in Admin Panel
    public function gatewaySettings($code)
    {

        if(!in_array($code, self::gatewayCodesArray())){abort(404);}

        $settings = Gateways::where("code", $code)->first();
        if($settings != null){
        }else{
            $settings = new Gateways();
            $settings->code = $code;
            $settings->is_active = 0;
            $settings->currency = "124"; //Default currency for Stripe - USD
            $settings->save();
        }
        $currencies = self::getCurrencyOptions($settings->currency);
        $gateways = self::defaultGatewayDefinitions();
        $options = $gateways[0];
        foreach($gateways as $gateway){
            if($gateway['code'] == $code){
                $options = $gateway;
                break;
            }
        }
        return view('panel.admin.finance.gateways.settings', compact('settings', 'currencies', 'options'));
    }

    public function gatewaySettingsSave(Request $request){# Save settings of gateway in Admin Panel
        if($request->code != null){
            if(!in_array($request->code, self::gatewayCodesArray())){abort(404);}
        }else{
            abort(404);
        }
        # return 404 error if the system currency is not the same as the gateway currency
        if($request->code == "paystack" && $request->currency != currency()->id ){
            return back()->with(['message' => __("Paystack default currency not the same with the system default currency."), 'type' => 'error']);
        }
        
        DB::beginTransaction();
        $gw_settings = Gateways::where("code", $request->code)->first();
        if($gw_settings != null){
            if($request->is_active == "on"){
                $gw_settings->is_active = 1;
            }else{
                $gw_settings->is_active = 0;
            }
            $propertiesToUpdate = [
                'title', 'currency', 'currency_locale', 'live_client_id', 'live_client_secret',
                'live_app_id', 'sandbox_client_id', 'sandbox_client_secret', 'sandbox_app_id',
                'base_url', 'sandbox_url', 'mode', 'bank_account_other', 'bank_account_details'
            ];
            foreach ($propertiesToUpdate as $property) {
                if(isset($request->$property)){
                    $gw_settings->$property = $request->$property ?? $gw_settings->$property;
                }
            }
            $gw_settings->save();

            if($gw_settings->is_active == 1){
                try{ 
                    $temp = GatewaySelector::selectGateway($request->code)::saveAllProducts();# Update all product ids' and create new price ids'
                }catch(\Exception $ex){
                    DB::rollBack();
                    Log::error("GatewayController::gatewaySettingsSave()\n".$ex->getMessage());
                    return back()->with(['message' => $ex->getMessage(), 'type' => 'error']);
                }
            }
        }else{
            $settings = new Gateways();
            $settings->code = $request->code;
            $settings->is_active = 0;
            $settings->currency = "124"; //Default currency for Stripe - USD
            $settings->save();
        }
        DB::commit();
        return back()->with(['message' => __('Product ID and Price ID of all membership plans are generated.'), 'type' => 'success']);
    }

    public function gatewaySettingsTaxSave(Request $request){# Save settings of gateway in Admin Panel
        if($request->code != null){
            if(!in_array($request->code, self::gatewayCodesArray())){abort(404);}
        }else{
            abort(404);
        }
        # return 404 error if the system currency is not the same as the gateway currency
        if($request->code == "paystack" && $request->currency != currency()->id ){
            return back()->with(['message' => __("Paystack default currency not the same with the system default currency."), 'type' => 'error']);
        }
        
        DB::beginTransaction();
        $gw_settings = Gateways::where("code", $request->code)->first();
        if($gw_settings != null){
            $gw_settings->tax = $request->tax ?? $gw_settings->tax;
            $gw_settings->save();
        }
        DB::commit();
        return back()->with(['message' => __('Tax saved succesfully.'), 'type' => 'success']);
    }

    public function gatewayData($code){
        $gateways = self::defaultGatewayDefinitions();
        $options = $gateways[0]; 
        foreach($gateways as $gateway){
            if($gateway['code'] == $code){
                $options = $gateway;
                break;
            }
        }
        return $options;
    }
    public static function checkGatewayWebhooks() : void {
        $host = $_SERVER['HTTP_HOST'];
        if ($host !== 'localhost:8000' && $host !== '127.0.0.1:8000') {
            $gateways = Gateways::all();
            foreach($gateways as $gateway){
                if($gateway->webhook_id == null){
                    $tmp = GatewaySelector::selectGateway($gateway->code)::createWebhook();
                }
            }
            Log::info('All gateways are checked for webhooks.');
        }else{
            Log::info('Webhooks are not available on localhost. Skipping checkGatewayWebhooks()...');
        }
    }
    

}