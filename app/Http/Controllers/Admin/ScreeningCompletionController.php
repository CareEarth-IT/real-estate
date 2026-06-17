<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ScreeningCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScreeningCompletionController extends Controller
{
    public function index(): View
    {
        Application::query()
            ->where('screening_ok', true)
            ->each(fn (Application $application) => ScreeningCompletion::syncFromApplication($application));

        $screeningCompletions = ScreeningCompletion::query()
            ->with('application')
            ->whereHas('application', fn ($query) => $query->where('screening_ok', true))
            ->join('applications', 'screening_completions.application_id', '=', 'applications.id')
            ->orderByDesc('applications.created_at')
            ->select('screening_completions.*')
            ->get();

        return view('admin.screening-completions.index', compact('screeningCompletions'));
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
