<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Finance\PaymentProcessController;
use App\Jobs\SendInviteEmail;
use App\Models\Folders;
use App\Models\Gateways;
use App\Models\Integration\Integration;
use App\Models\OpenAIGenerator;
use App\Models\OpenaiGeneratorChatCategory;
use App\Models\OpenaiGeneratorFilter;
use App\Models\PaymentPlans;
use App\Models\Setting;
use App\Models\SettingTwo;
use App\Models\User;
use App\Models\UserAffiliate;
use App\Models\UserFavorite;
use App\Models\UserOpenai;
use App\Models\UserOpenaiChat;
use App\Models\UserOrder;
use App\Models\Voice\ElevenlabVoice;
use App\Services\GatewaySelector;
use enshrined\svgSanitize\Sanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Cashier\Payment;

class UserController extends Controller
{
    public function redirect(Request $request)
    {
        $route = 'dashboard.user.index';

        if ($request->user()->isAdmin()) {
            $route = 'dashboard.admin.index';
        }

        return to_route($route);
    }

    public function index()
    {
        $ongoingPayments = null;
        // $ongoingPayments = self::prepareOngoingPaymentsWarning();
        // $user = Auth::user();
        $tmp = PaymentProcessController::checkUnmatchingSubscriptions();

        return view('panel.user.dashboard', compact('ongoingPayments')); //
    }

    public function prepareOngoingPaymentsWarning()
    {
        $ongoingPayments = PaymentProcessController::checkForOngoingPayments();

        if ($ongoingPayments) {
            return $ongoingPayments;
        }

        return null;
    }

    public function openAIList()
    {
        abort_if(Helper::setting('feature_ai_writer') == 0, 404);

        return view('panel.user.openai.list', [
            'list' => OpenAIGenerator::query()->where('active', true)->get(),
            'filters' => OpenaiGeneratorFilter::get(),
        ]);
    }

    public function openAIFavoritesList()
    {
        return view('panel.user.openai.list_favorites');
    }

    public function openAIFavorite(Request $request)
    {
        $exists = isFavorited($request->id);
        if ($exists) {
            $favorite = UserFavorite::where('openai_id', $request->id)->where('user_id', Auth::id())->first();
            $favorite->delete();
            $action = 'unfavorite';
        } else {
            $favorite = new UserFavorite();
            $favorite->user_id = Auth::id();
            $favorite->openai_id = $request->id;
            $favorite->save();
            $action = 'favorite';
        }

        return response()->json(compact('html'));
    }

    public function openAIGenerator(Request $request, $slug)
    {
        $openai = OpenAIGenerator::whereSlug($slug)->firstOrFail();

        $userOpenai = $this->openai($request, null)
            ->where('openai_id', $openai->id)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $elevenlabs = ElevenlabVoice::query()
            ->select('voice_id', 'name')
            ->where('status', 1)
            ->where('user_id', Auth::id())
            ->whereNotNull('voice_id')
            ->get();

        return view(
            'panel.user.openai.generator',
            compact('openai', 'userOpenai', 'elevenlabs')
        );
    }

    public function openAIGeneratorWorkbook($slug)
    {
        $openai = OpenAIGenerator::whereSlug($slug)->firstOrFail();

        $settings = Setting::first();
        $settings2 = SettingTwo::first();
        // Fetch the Site Settings object with openai_api_secret
        if ($settings?->user_api_option) {
            $apiKeys = explode(',', auth()->user()?->api_keys);
        } else {
            $apiKeys = explode(',', $settings?->openai_api_secret);
        }
        $apiKey = $apiKeys[array_rand($apiKeys)];

        $len = strlen($apiKey);
        $parts[] = substr($apiKey, 0, $l[] = rand(1, $len - 5));
        $parts[] = substr($apiKey, $l[0], $l[] = rand(1, $len - $l[0] - 3));
        $parts[] = substr($apiKey, array_sum($l));
        $apikeyPart1 = base64_encode($parts[0]);
        $apikeyPart2 = base64_encode($parts[1]);
        $apikeyPart3 = base64_encode($parts[2]);
        $apiUrl = base64_encode('https://api.openai.com/v1/chat/completions');

        $apiSearch = base64_encode('https://google.serper.dev/search');
        $apiSearchId = base64_encode($settings2->serper_api_key);

        if ($slug == 'ai_vision' || $slug == 'ai_pdf' || $slug == 'ai_chat_image') {

            $isPaid = false;
            $userId = Auth::user()->id;

            $activeSub = getCurrentActiveSubscription($userId);
            if ($activeSub != null) {
                $gateway = $activeSub->paid_with;
            } else {
                $activeSubY = getCurrentActiveSubscriptionYokkasa($userId);
                if ($activeSubY != null) {
                    $gateway = $activeSubY->paid_with;
                }
            }

            try {
                $isPaid = GatewaySelector::selectGateway($gateway)::getSubscriptionStatus();
            } catch (\Exception $e) {
                $isPaid = false;
            }

            $category = OpenaiGeneratorChatCategory::whereSlug($slug)->firstOrFail();

            if ($isPaid == false && $category->plan == 'premium' && auth()->user()->type !== 'admin') {
                return redirect()->back()->with(['message' => __('Needs a Premium access'), 'type' => 'error']);
            }

            $list = UserOpenaiChat::where('user_id', Auth::id())->where('openai_chat_category_id', $category->id)->orderBy('updated_at', 'desc');
            $list = $list->get();
            $chat = $list->first();
            $aiList = OpenaiGeneratorChatCategory::all();
            $streamUrl = route('dashboard.user.openai.chat.stream');
            $lastThreeMessage = null;
            $chat_completions = null;
            if ($chat != null) {
                $lastThreeMessageQuery = $chat->messages()->whereNot('input', null)->orderBy('created_at', 'desc')->take(2);
                $lastThreeMessage = $lastThreeMessageQuery->get()->reverse();
                $category = OpenaiGeneratorChatCategory::where('id', $chat->openai_chat_category_id)->first();
                $chat_completions = str_replace(["\r", "\n"], '', $category->chat_completions) ?? null;

                if ($chat_completions != null) {
                    $chat_completions = json_decode($chat_completions, true);
                }
            }

            return view('panel.user.openai_chat.chat', compact(
                'category',
                'apiSearch',
                'apiSearchId',
                'list',
                'chat',
                'aiList',
                'apikeyPart1',
                'apikeyPart2',
                'apikeyPart3',
                'apiUrl',
                'lastThreeMessage',
                'chat_completions',
                'streamUrl'
            ));
        }

        $view = 'panel.user.openai.generator_workbook';

        return view($view, compact(
            'openai',
            'apiSearch',
            'apiSearchId',
            'apikeyPart1',
            'apikeyPart2',
            'apikeyPart3',
            'apiUrl',
        ));
    }

    public function openAIGeneratorWorkbookSave(Request $request)
    {
        $workbook = UserOpenai::where('slug', $request->workbook_slug)->firstOrFail();
        $workbook->output = $request->workbook_text;
        $workbook->title = $request->workbook_title;
        $workbook->save();

        return response()->json([], 200);
    }

    //Chat
    public function openAIChat()
    {
        $chat = Auth::user()->openaiChat;

        return view('panel.user.openai.chat', compact('chat'));
    }

    public static function sanitizeSVG($uploadedSVG)
    {

        $sanitizer = new Sanitizer();
        $content = file_get_contents($uploadedSVG);
        $cleanedData = $sanitizer->sanitize($content);
        $added = file_put_contents($uploadedSVG, $cleanedData);

        return $uploadedSVG;
    }

    //Profile user settings
    public function userSettings()
    {
        $user = Auth::user();

        return view('panel.user.settings.index', compact('user'));
    }

    public function userSettingsSave(Request $request)
    {
        $user = Auth::user();
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->phone = $request->phone;
        $user->country = $request->country;

        if ($request->old_password != null) {
            $validated = $request->validateWithBag('updatePassword', [
                'old_password' => ['required', 'current_password'],
                'new_password' => ['required', Password::defaults(), 'confirmed'],
            ]);

            $user->password = Hash::make($request->new_password);
        }

        if ($request->hasFile('avatar')) {
            $path = 'upload/images/avatar/';
            $image = $request->file('avatar');

            if ($image->getClientOriginalExtension() == 'svg') {
                $image = self::sanitizeSVG($request->file('avatar'));
            }

            $image_name = Str::random(4).'-'.Str::slug($user->fullName()).'-avatar.'.$image->getClientOriginalExtension();

            //Image extension check
            $imageTypes = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
            if (! in_array(Str::lower($image->getClientOriginalExtension()), $imageTypes)) {
                $data = [
                    'errors' => ['The file extension must be jpg, jpeg, png, webp or svg.'],
                ];

                return response()->json($data, 419);
            }

            $image->move($path, $image_name);

            $user->avatar = $path.$image_name;
        }

        createActivity($user->id, 'Updated', 'Profile Information', null);
        $user->save();
    }

    //Purchase
    public function subscriptionPlans()
    {

        //check if any payment gateway enabled
        $activeGateways = Gateways::where('is_active', 1)->get();
        if ($activeGateways->count() > 0) {
            $is_active_gateway = 1;
        } else {
            $is_active_gateway = 0;
        }

        //check if any subscription is active
        $userId = Auth::user()->id;

        $activeSub = getCurrentActiveSubscription($userId);
        if ($activeSub != null) {
            $activesubid = $activeSub->plan_id;
        } else {
            $activeSub_yokassa = getCurrentActiveSubscriptionYokkasa($userId);
            if ($activeSub_yokassa != null) {
                $activesubid = $activeSub_yokassa->plan_id;
            } else {
                $activesubid = 0;
            }
        }

		$openAiList = OpenAIGenerator::query()->get();

        $plans = PaymentPlans::where('type', 'subscription')->where('active', 1)->get();
        $prepaidplans = PaymentPlans::where('type', 'prepaid')->where('active', 1)->get();
        $view = 'panel.user.finance.subscriptionPlans';

        return view($view, compact('plans', 'prepaidplans', 'openAiList','is_active_gateway', 'activeGateways', 'activesubid'));
    }

    //Invoice - Billing
    public function invoiceList()
    {
        $user = Auth::user();
        $list = $user->orders;

        return view('panel.user.orders.index', compact('list'));
    }

    public function invoiceSingle($order_id)
    {
        $invoice = UserOrder::where('order_id', $order_id)->firstOrFail();

        return view('panel.user.orders.invoice', compact('invoice'));
    }

    public function documentsAll(Request $request, $folderID = null)
    {
        $DOCS_PER_PAGE = 20;

        $listOnly = $request->listOnly;
        $filter = $request->filter ?? 'all';
        $sort = $request->sort ?? 'created_at';
        $sortAscDesc = $request->sortAscDesc ?? 'desc';

        $items = $this->openai($request, $folderID)
            ->where('folder_id', $folderID)
            ->orderBy($sort, $sortAscDesc)
            ->paginate(20);

        if ($folderID !== null) {
            $currfolder = Folders::query()
                ->where(function (Builder $query) {
                    $query
                        ->where('created_by', auth()->id())
                        ->orWhere('team_id', auth()->user()->team_id);
                })
                ->findOrFail($folderID);
        } else {
            $currfolder = null;
        }

        // if(($items->total() == 0) && $folderID !== null){
        //     $items = $this->openai($request, $folderID)
        //     ->where('folder_id', null)
        //     ->orderBy('created_at', 'desc')->paginate(20);
        //     $currfolder = null;
        // }else{
        //     if ($folderID !== null) {
        //         $currfolder = Folders::query()
        //             ->where(function (Builder $query) {
        //                 $query
        //                     ->where('created_by', auth()->id())
        //                     ->orWhere('team_id', auth()->user()->team_id);
        //             })
        //             ->findOrFail($folderID);
        //     } else {
        //         $currfolder = null;
        //     }
        // }

        if ($listOnly) {
            return view('panel.user.openai.documents_container', compact('items', 'currfolder', 'filter'))->render();
        }

        return view('panel.user.openai.documents', compact('items', 'currfolder', 'filter'));
    }

    protected function openai(Request $request, $folderID = null)
    {
        $team = $request->user()->getAttribute('team');

        $myCreatedTeam = $request->user()->getAttribute('myCreatedTeam');

        return UserOpenai::query()
            ->where(function (Builder $query) use ($team, $myCreatedTeam) {
                $query->where('user_id', auth()->id())
                    ->when($team || $myCreatedTeam, function ($query) use ($team, $myCreatedTeam) {
                        if ($team && $team?->is_shared) {
                            $query->orWhere('team_id', $team->id);
                        }
                        if ($myCreatedTeam) {
                            $query->orWhere('team_id', $myCreatedTeam->id);
                        }
                    });
            });
    }

    public function updateFolder(Request $request, $folder)
    {
        $request->validate([
            'newFolderName' => 'required|string|max:255',
        ]);

        $folder = Folders::findOrFail($folder);
        $folder->name = $request->input('newFolderName');
        $folder->save();

        return response()->json(['message' => __('Folder name updated successfully')]);
    }

    public function updateFile(Request $request, $slug)
    {
        $request->validate([
            'newFileName' => 'required|string|max:255',
        ]);

        $file = UserOpenai::where('slug', $slug)->first();
        $file->generator->title = $request->input('newFileName');
        $file->generator->save();

        return response()->json(['message' => __('File name updated successfully')]);
    }

    public function deleteFolder(Request $request, $folder)
    {
        $folder = Folders::findOrFail($folder);
        $all = $request->all;
        if ($all) {
            foreach ($folder->userOpenais as $userOpenai) {
                $userOpenai->delete();
            }
        }
        $folder->delete();

        return response()->json(['message' => __('Folder deleted successfully')]);
    }

    public function newFolder(Request $request)
    {
        $request->validate([
            'newFolderName' => 'required|string|max:255',
        ]);

        $newFolder = new Folders();
        $newFolder->name = $request->newFolderName;
        $newFolder->created_by = auth()->user()->id;
        $newFolder->save();

        return back()->with(['message' => 'Added successfuly', 'type' => 'success']);
    }

    public function moveToFolder(Request $request)
    {
        $folderID = $request->selectedFolderId;
        $fileSlug = $request->fileslug;

        $workbook = UserOpenai::where('slug', $fileSlug)->first();
        $workbook->folder_id = $folderID;
        $workbook->save();

        return back()->with(['message' => 'Moved successfuly', 'type' => 'success']);
    }

    public function documentsSingle($slug)
    {
        $workbook = UserOpenai::where('slug', $slug)->first();

        $openai = $workbook->generator;

        $integrations = Auth::user()->getAttribute('integrations');

        $checkIntegration = Integration::query()->whereHas('hasExtension')->count();

        return view('panel.user.openai.documents_workbook', compact('checkIntegration', 'workbook', 'openai', 'integrations'));
    }

    public function documentsDelete($slug)
    {
        $workbook = UserOpenai::where('slug', $slug)->first();
        $image = $workbook->response;
        $storage = $workbook->storage;
        try {
            if ($storage == 's3') {
                Storage::disk('s3')->delete(basename($image));
            } else {

                unlink(substr($image, 1));

            }
        } catch (\Throwable $th) {
            unlink(substr($image, 1));
        }
        $workbook->delete();

        return redirect()->route('dashboard.user.openai.documents.all')->with(['message' => 'Document deleted successfuly', 'type' => 'success']);
    }

    public function documentsImageDelete($slug)
    {
        $workbook = UserOpenai::where('slug', $slug)->first();
        if ($workbook->storage == UserOpenai::STORAGE_LOCAL) {
            $file = str_replace('/uploads/', '', $workbook->output);
            Storage::disk('public')->delete($file);
        } elseif ($workbook->storage == UserOpenai::STORAGE_AWS) {
            $file = str_replace('/', '', parse_url($workbook->output)['path']);
            Storage::disk('s3')->delete($file);
        } else {

            // Manual deleting depends on response
            if (str_contains($workbook->output, 'https://')) {
                // AWS Storage
                $file = str_replace('/', '', parse_url($workbook->output)['path']);
                Storage::disk('s3')->delete($file);
            } else {
                $file = str_replace('/uploads/', '', $workbook->output);
                Storage::disk('public')->delete($file);
            }

        }
        $workbook->delete();

        return back()->with(['message' => 'Deleted successfuly', 'type' => 'success']);
    }

    //Affiliates
    public function affiliatesList()
    {
        abort_if(Helper::setting('feature_affilates') == 0, 404);

        $user = Auth::user();
        $list = $user->affiliates;
        $list2 = $user->withdrawals;
        $totalEarnings = 0;
        foreach ($list as $affOrders) {
            $totalEarnings += $affOrders->orders->sum('affiliate_earnings');
        }
        $totalWithdrawal = 0;
        foreach ($list2 as $affWithdrawal) {
            $totalWithdrawal += $affWithdrawal->amount;
        }

        return view('panel.user.affiliate.index', compact('list', 'list2', 'totalEarnings', 'totalWithdrawal'));
    }

	public function affiliatesUsers(Request $request)
	{
		$userIds = User::where("affiliate_id", auth()->user()->id)->pluck('id');
		$query = UserAffiliate::whereIn("user_id", $userIds)->with('user');

		if ($request->has('search')) {
			$searchTerm = $request->input('search');
			$query->whereHas('user', function($q) use ($searchTerm) {
				$q->where('name', 'like', '%' . $searchTerm . '%');
			});
		}

		if ($request->has('startDate') && $request->input('startDate')) {
			$query->whereDate('created_at', '>=', $request->input('startDate'));
		}

		if ($request->has('endDate') && $request->input('endDate')) {
			$query->whereDate('created_at', '<=', $request->input('endDate'));
		}

		$list = $query->paginate(10);

		return view('panel.user.affiliate.users', compact('list'));
	}

    public function affiliatesListSendInvitation(Request $request)
    {
        $user = Auth::user();

        $sendTo = $request->to_mail;

        dispatch(new SendInviteEmail($user, $sendTo));

        return response()->json([], 200);
    }

    public function affiliatesListSendRequest(Request $request)
    {
        $user = Auth::user();
        $list = $user->affiliates;
        $list2 = $user->withdrawals;

        $totalEarnings = 0;
        foreach ($list as $affOrders) {
            $totalEarnings += $affOrders->orders->sum('affiliate_earnings');
        }
        $totalWithdrawal = 0;
        foreach ($list2 as $affWithdrawal) {
            $totalWithdrawal += $affWithdrawal->amount;
        }
        if ($totalEarnings - $totalWithdrawal >= $request->amount) {
            $user->affiliate_bank_account = $request->affiliate_bank_account;
            $user->save();
            $withdrawalReq = new UserAffiliate();
            $withdrawalReq->user_id = Auth::id();
            $withdrawalReq->amount = $request->amount;
            $withdrawalReq->save();

            createActivity($user->id, 'Sent', 'Affiliate Withdraw Request', route('dashboard.admin.affiliates.index'));
        } else {
            return response()->json(['error' => 'ERROR'], 411);
        }
    }

    public function apiKeysList()
    {
        abort_if(! Helper::appIsDemo() && Helper::setting('user_api_option') == 0, 404);
        $user = Auth::user();
        $list = $user->api_keys;

        return view('panel.user.apiKeys.index', compact('list'));
    }

    public function apiKeysSave(Request $request)
    {
        if (Helper::appIsDemo()) {
            return back()->with(['message' => __('This feature is disabled in Demo version.'), 'type' => 'error']);
        }
        $user = Auth::user();
        $user->api_keys = $request->api_keys;
        $user->save();

        return redirect()->back();
    }
}