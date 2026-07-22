<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\CareEarthUser;
use App\Models\FlowManagement;
use App\Support\AdminListSearch;
use App\Support\ApplicationCreator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $search = AdminListSearch::term($request->input('search'));

        $applications = Application::query()
            ->with('customer')
            ->where('is_cancelled', false)
            ->where('screening_ok', false)
            ->tap(fn ($query) => AdminListSearch::applyToApplication($query, $search))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.applications.index', compact('applications', 'search'));
    }

    public function create(): View
    {
        $staffOptions = CareEarthUser::query()
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')
            ->pluck('name', 'name')
            ->all();

        return view('admin.applications.create', compact('staffOptions'));
    }

    public function store(StoreApplicationRequest $request): RedirectResponse
    {
        ApplicationCreator::create($request->validated());

        return redirect()
            ->route('admin.applications.index')
            ->with('success', '申込を登録しました。');
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

        $updates = [
            $validated['field'] => $validated['value'],
        ];

        if ($validated['field'] === 'screening_ok') {
            $updates['screening_ok_at'] = $validated['value'] ? now() : null;
        }

        $application->update($updates);

        if ($validated['field'] === 'screening_ok' && $validated['value']) {
            FlowManagement::syncFromApplication($application->fresh());
        }

        return response()->json([
            'success' => true,
            'field' => $validated['field'],
            'value' => $application->{$validated['field']},
            'screening_ok' => $application->screening_ok,
            'is_cancelled' => $application->is_cancelled,
            'remove_row' => $application->screening_ok || $application->is_cancelled,
        ]);
    }

    public function updateField(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'field' => ['required', 'in:memo,status,appliance_support_notes'],
            'value' => ['nullable', 'string', 'max:2000'],
        ]);

        $application->update([
            $validated['field'] => $validated['value'] !== '' ? $validated['value'] : null,
        ]);

        return response()->json([
            'success' => true,
            'field' => $validated['field'],
            'value' => $application->{$validated['field']},
        ]);
    }
}
