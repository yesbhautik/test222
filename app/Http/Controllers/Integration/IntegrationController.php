<?php

namespace App\Http\Controllers\Integration;

use App\Models\Integration\Integration;
use App\Models\Integration\UserIntegration;
use App\Models\UserOpenai;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class IntegrationController extends Controller
{
    public function index()
    {
        return view('panel.user.integration.index', [
            'items' => Integration::query()->with('extension')->get()
        ]);
    }

    public function edit(Integration $integration)
    {
        $class = $integration->getFormClassName();

        if (! class_exists($class)) {
            abort(404);
        }

        return view('panel.user.integration.edit', [
            'item' => $integration,
            'userItem' => UserIntegration::query()
                ->firstOrCreate([
                    'user_id' => Auth::id(),
                    'integration_id' => $integration->getAttribute('id')
                ], [
                    'credentials' => $class::form()
                ])
        ]);
    }

    public function update(Request $request, Integration $integration)
    {
        $class = $integration->getFormClassName();

        if (! class_exists($class)) {
            abort(404);
        }

        $userIntegration = UserIntegration::query()
            ->where('user_id', Auth::id())
            ->where('integration_id', $integration->getAttribute('id'))
            ->first();

        $userIntegration->update([
            'credentials' => $class::form($request->all())
        ]);

        return redirect()->route('dashboard.user.integration.index')->with('success', 'Integration updated successfully');
    }

    public function workbook(UserIntegration $userIntegration, UserOpenai $userOpenai)
    {
        $openai = $userOpenai->generator;

        $integration = $userIntegration->integration;



        return view('panel.user.integration.documents_workbook', [
            'workbook' => $userOpenai,
            'openai' => $openai,
            'userIntegration' => $userIntegration,
            'title' => trans('Share to ') . $integration->getAttribute('app')
        ]);
    }

    public function storeWorkbook(Request $request, UserIntegration $userIntegration, UserOpenai $userOpenai)
    {
        $request->validate([
            'title' => 'required|string',
            'workbook_text' => 'required|string',
        ]);

        $class = $userIntegration->integration->getFormClassName();

        if (! class_exists($class)) {
            abort(404);
        }

        $service = new $class($userIntegration);

        $service->create([
            'title' => $request->get('title'),
            'content' => $request->get('workbook_text'),
            'status' => 'publish'
        ]);


        return redirect()->back()->with('success', trans('Document created successfully'));
    }
}