<?php

namespace App\Services\PaymentGateways;
use App\Models\Currency;
use App\Models\Gateways;
use App\Models\User;
use App\Models\UserOrder;
use App\Models\Setting;
use App\Models\Coupon;
use App\Models\PaymentPlans;
use App\Models\GatewayProducts;
use App\Models\OldGatewayProducts;
use App\Job\ProcessRazorpayCustomerJob;
use App\Services\Contracts\BaseGatewayService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Razorpay\Api\Api;
use Laravel\Cashier\Subscription as Subscriptions;

/**
 * Base functions foreach payment gateway
 * @param saveAllProducts
 * @param saveProduct ($plan)
 * @param subscribe ($plan)
 * @param subscribeCheckout (Request $request, $referral= null)
 * @param prepaid ($plan)
 * @param prepaidCheckout (Request $request, $referral= null)
 * @param getSubscriptionStatus ($incomingUserId = null)
 * @param getSubscriptionDaysLeft
 * @param subscribeCancel
 * @param checkIfTrial
 * @param getSubscriptionRenewDate
 * @param cancelSubscribedPlan ($subscription)
 */
class RazorpayService implements BaseGatewayService
{
    public static $gateway;

    protected static $GATEWAY_CODE      = "razorpay";

    protected static $GATEWAY_NAME      = "Razorpay";

 	# payment functions
    public static function saveAllProducts()
    {

        try{
            $gateway = self::geteway();

            if($gateway == null) {
                return back()->with(['message' => __('Please enable coingate'), 'type' => 'error']);
            }

            $plans = PaymentPlans::query()->where('active', 1)->get();

            foreach ($plans as $plan) {
                self::saveProduct($plan);
            }

        }catch (Exception $ex) {
            Log::error(self::$GATEWAY_CODE."-> saveAllProducts(): " . $ex->getMessage());
            return back()->with(['message' => $ex->getMessage(), 'type' => 'error']);
        }
    }

 	public static function saveProduct($plan)
    {
        $gateway = self::geteway();

        if($gateway == null) {
            return back()->with(['message' => __('Please enable coingate'), 'type' => 'error']);
        }

		try {
            DB::beginTransaction();

//	 		$price = (int) (((float) $plan->price) * 100); # Must be in cents level for stripe

			DB::commit();

            return true;

        } catch (\Exception $ex) {
//            DB::rollBack();
            Log::error(self::$GATEWAY_CODE."-> saveProduct():\n" . $ex->getMessage());

            return back()->with(['message' => $ex->getMessage(), 'type' => 'error']);
        }
	}

    public static function client(): Api
    {
        $gateway = self::geteway();

        $apiKey = $gateway->getAttribute('mode') == 'sandbox'
            ? $gateway->getAttribute('sandbox_client_id')
            : $gateway->getAttribute('live_client_id');

        $secret = $gateway->getAttribute('mode') !== 'sandbox'
            ? $gateway->getAttribute('sandbox_client_secret')
            : $gateway->getAttribute('live_client_secret');

        return new Api($apiKey, $secret);
    }

    public static function geteway(): Model|Builder|null
    {
        if (self::$gateway) {
            return self::$gateway;
        }

        self::$gateway = Gateways::where('code', self::$GATEWAY_CODE)->first();

        return self::$gateway;
    }

    public static function objectToArray($request)
    {
        if (is_object($request)) {
            if (property_exists($request, 'id')) {
                $request = json_decode(json_encode($request), true);
            }
        }

        return $request;
    }

    public static function gatewayDefinitionArray(): array
    {
        return [
            "code" => "razorpay",
            "title" => "Razorpay",
            "link" => "https://razorpay.com/",
            "active" => 0,
            "available" => 1,
            "img" => "/assets/img/payments/razorpay.svg",
            "whiteLogo" => 0,
            "mode" => 1,
            "sandbox_client_id" => 1,
            "sandbox_client_secret" => 1,
            "sandbox_app_id" => 0,
            "live_client_id" => 1,
            "live_client_secret" => 1,
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
        ];
    }

    public static function subscribe($plan)
    {
        // TODO: Implement subscribe() method.
    }

    public static function subscribeCheckout(Request $request, $referral = null)
    {
        // TODO: Implement subscribeCheckout() method.
    }

    public static function prepaidCheckout(Request $request, $referral = null)
    {
        // TODO: Implement prepaidCheckout() method.
    }

    public static function prepaid($plan)
    {
        // TODO: Implement prepaid() method.
    }

    public static function subscribeCancel(?User $internalUser = null)
    {
        // TODO: Implement subscribeCancel() method.
    }

    public static function cancelSubscribedPlan($subscription, $planId)
    {
        // TODO: Implement cancelSubscribedPlan() method.
    }

    public static function checkIfTrial()
    {
        // TODO: Implement checkIfTrial() method.
    }

    public static function getSubscriptionRenewDate()
    {
        // TODO: Implement getSubscriptionRenewDate() method.
    }

    public static function getSubscriptionStatus($incomingUserId = null)
    {
        // TODO: Implement getSubscriptionStatus() method.
    }

    public static function getSubscriptionDaysLeft()
    {
        // TODO: Implement getSubscriptionDaysLeft() method.
    }

    public static function handleWebhook(Request $request)
    {
        // TODO: Implement handleWebhook() method.
    }
}