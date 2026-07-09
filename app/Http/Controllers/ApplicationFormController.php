<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Support\ApplicationCreator;
use App\Support\ManagementCompanySuggestions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationFormController extends Controller
{
    public function create(): View
    {
        return view('applications.create');
    }

    public function managementCompanySuggestions(Request $request): JsonResponse
    {
        $query = (string) $request->query('q', '');

        return response()->json(
            ManagementCompanySuggestions::search($query)
        );
    }

    public function store(StoreApplicationRequest $request): RedirectResponse
    {
        $application = ApplicationCreator::create($request->validated());

        return redirect()
            ->route('applications.complete', $application)
            ->with('success', '申込情報を登録しました。');
    }

    public function complete(Application $application): View
    {
        return view('applications.complete', compact('application'));
    }
}
