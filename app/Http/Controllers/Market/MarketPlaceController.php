<?php

namespace App\Http\Controllers\Market;

use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use App\Models\Extension;
use App\Models\SettingTwo;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarketPlaceController extends Controller
{

    public function updateDatabase()
    {
        $client = new Client();
        $settings = SettingTwo::first();

        try {
            $response = $client->request('POST', 'https://portal.liquid-themes.com/api/extensions/all', []);

            $responseData = json_decode($response->getBody(), true);

            Log::info($response->getBody());

            foreach ($responseData['extensions'] as $extensionData) {

                $extension = Extension::where('slug', $extensionData['slug'])->first();
                if ($extension == null) {
                    $extension = new Extension();
                }

                $extension->slug = $extensionData['slug'];
                $extension->name = $extensionData['name'];
                $extension->review = $extensionData['review'];
                $extension->description = $extensionData['description'];
                $extension->category = $extensionData['category'];
                $extension->badge = $extensionData['badge'];
                $extension->zip_url = $extensionData['zip_url'];
                $extension->image_url = $extensionData['image_url'];
                $extension->detail = $extensionData['detail'];
                $extension->price_id = $extensionData['price_id'];
                $extension->price = $extensionData['price'];
                // $extension->version = $extensionData['version'];
                $extension->licensed = true;

                $extension->save();
            }

            $response = $client->request('POST', 'https://portal.liquid-themes.com/api/extensions/licensed', [
                'json' => [
                    'licenseKey' => $settings->liquid_license_domain_key,
                ],
            ]);

            $responseData = json_decode($response->getBody(), true);

            // Loop through the extensions and log the extensionSlug field
            foreach ($responseData['extensions'] as $extensionData) {
                $extensionSlug = $extensionData['extensionSlug'];
                $extension = Extension::where('slug', $extensionSlug)->first();
                Log::info($extensionSlug);
                $extension->licensed = true;
                $extension->save();
            }
        } catch (Exception $e) {
        }
    }

    public function index()
    {
        // $jsonFile = base_path('addons.json');
        // $addonsData = File::get($jsonFile);
        // $addons = json_decode($addonsData);

        $this->updateDatabase();

        $extensions = Extension::all();

        return view('panel.admin.market.index', compact('extensions'));
    }

    public function extension($slug)
    {
        $extension = Extension::where('slug', $slug)->first();

        $client = new Client();
        $response = $client->request('GET', "https://portal.liquid-themes.com/api/extensions/qa?slug=$extension->slug");

        $responseData = json_decode($response->getBody(), true);
        $extensionQAs = $responseData['extensionQAs'];

        return view('panel.admin.market.extension', compact('extension', 'extensionQAs'));
    }

    public function licensedExtension()
    {
        $extensions = Extension::where('licensed', 1)->get();

        return view('panel.admin.market.liextension', compact('extensions'));
    }

    public function buyExtension($slug)
    {
        $extension = Extension::query()->where('slug', $slug)->first();

        $token = Helper::generatePaymentToken($slug);


        return view('panel.admin.market.buyextension', compact('extension', 'token'));
    }

    public function buy($slug)
    {

        $stripe = new \Stripe\StripeClient(env('EXTENSION_STRIPE_PRIVATE_KEY'));

        $client = new Client();
        $settings = SettingTwo::first();
        $licenseKey = $settings->liquid_license_domain_key;
        $response = $client->request('GET', "https://portal.liquid-themes.com/api/license/$licenseKey");

        $email = json_decode($response->getBody(), true)['owner']['email'];

        $extension = Extension::where('slug', $slug)->first();

        $session = $stripe->checkout->sessions->create([
            'customer_email' => $email,
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price' => $extension->price_id,
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'allow_promotion_codes' => true,
            'success_url' => route('dashboard.admin.marketplace.index'),
            'cancel_url' => route('dashboard.admin.marketplace.index'),
            'metadata' => [
                'licenseKey' => $settings->liquid_license_domain_key,
                'slug' => $slug,
                'email' => $email,
            ],
        ]);

        return $session;
    }

    public function extensionActivate(Request $request, string $token)
    {
        $data = $this->decode($token);

        $slug = data_get($data, 'extension');

        $extension = Extension::query()->where('slug', $slug)->firstOrFail();

        if (
            $data['license'] == Helper::settingTwo('liquid_license_domain_key')
            && $data['domain'] == request()->getHost()

        ) {
            $extension->update([
                'licensed' => true
            ]);
        }

        return view('panel.admin.market.extensionactivate', [
            'extension' => $extension,
            'token' => $token,
            'success' => $request->get('redirect_status') == 'succeeded',
        ]);
    }

    public function decode(string $token)
    {
        $base64 = base64_decode($token);

        $data = explode(':', $base64);

        [$domain,  $extension, $license] = $data;

        return compact('domain', 'extension', 'license');
    }
}
