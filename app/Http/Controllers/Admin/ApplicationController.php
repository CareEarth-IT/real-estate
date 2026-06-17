<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ScreeningCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(): View
    {
        $applications = Application::query()
            ->orderByDesc('created_at')
            ->get();

        return view('admin.applications.index', compact('applications'));
    }

    public function updateFlags(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'field' => ['required', 'in:sales_action_required,screening_ok,is_cancelled'],
            'value' => ['required', 'boolean'],
        ]);

        if ($validated['value']) {
            if ($validated['field'] === 'screening_ok' && $application->is_cancelled) {
                return response()->json([
                    'message' => 'キャンセルが選択されているため、審査ＯＫは設定できません。先にキャンセルのチェックを外してください。',
                ], 422);
            }

            if ($validated['field'] === 'is_cancelled' && $application->screening_ok) {
                return response()->json([
                    'message' => '審査ＯＫが選択されているため、キャンセルは設定できません。先に審査ＯＫのチェックを外してください。',
                ], 422);
            }
        }

        $application->update([
            $validated['field'] => $validated['value'],
        ]);

        if ($validated['field'] === 'screening_ok') {
            ScreeningCompletion::syncFromApplication($application->fresh());
        }

        return response()->json([
            'success' => true,
            'field' => $validated['field'],
            'value' => $application->{$validated['field']},
            'screening_ok' => $application->screening_ok,
            'is_cancelled' => $application->is_cancelled,
        ]);
    }
}
