<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ScreeningCompletion;
use App\Support\AdminListSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScreeningCompletionController extends Controller
{
    public function index(Request $request): View
    {
        $search = AdminListSearch::term($request->input('search'));

        Application::query()
            ->where('screening_ok', true)
            ->each(fn (Application $application) => ScreeningCompletion::syncFromApplication($application));

        $screeningCompletions = ScreeningCompletion::query()
            ->with('application')
            ->whereHas('application', fn ($query) => $query->where('screening_ok', true))
            ->join('applications', 'screening_completions.application_id', '=', 'applications.id')
            ->tap(fn ($query) => AdminListSearch::applyToScreeningCompletion($query, $search))
            ->orderByDesc('applications.created_at')
            ->select('screening_completions.*')
            ->paginate(10)
            ->withQueryString();

        return view('admin.screening-completions.index', compact('screeningCompletions', 'search'));
    }

    public function updateFlowTransition(Request $request, ScreeningCompletion $screeningCompletion): JsonResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'boolean'],
        ]);

        $screeningCompletion->update([
            'flow_management_transition' => $validated['value'],
        ]);

        ScreeningCompletion::syncFromScreeningCompletion($screeningCompletion->fresh());

        return response()->json([
            'success' => true,
            'value' => $screeningCompletion->flow_management_transition,
        ]);
    }
}
