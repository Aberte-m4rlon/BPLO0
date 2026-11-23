<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicationRequest;
use App\Models\Application;
use App\Models\ApplicationRequirement;
use App\Models\RequirementType;
use App\Services\ApplicationWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function create(Request $request): View
    {
        $user = $request->user();
        $previousApplication = $user?->applications()->latest('id')->first();

        return view('applicant.create', [
            'requirementTypes' => RequirementType::where('is_active', true)->orderBy('sort_order')->get(),
            'previousApplication' => $previousApplication,
            'suggestedBarangay' => $previousApplication?->barangay ?? $this->extractBarangay($user?->address),
        ]);
    }

    public function store(ApplicationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $application = new Application($validated);
        $application->user()->associate($user);
        $application->status = Application::STATUS_DRAFT;
        $application->save();

        // Sync missing profile fields back onto the user so subsequent applications auto-fill cleanly.
        $profileDirty = false;
        if (blank($user->contact_number) && filled($validated['contact_number'] ?? null)) {
            $user->contact_number = $validated['contact_number'];
            $profileDirty = true;
        }
        if (blank($user->address) && filled($validated['business_address'] ?? null)) {
            $user->address = $validated['business_address'];
            $profileDirty = true;
        }
        if ($profileDirty) {
            $user->save();
        }

        return redirect()
            ->route('applications.edit', $application)
            ->with('success', 'Draft application created. Upload your requirements, then review and submit.');
    }

    private function extractBarangay(?string $address): ?string
    {
        if (blank($address)) {
            return null;
        }

        if (preg_match('/(?:brgy\.?|barangay)\s+([^,]+)/i', $address, $matches)) {
            return trim($matches[1]);
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $address))));
        $municipality = trim((string) config('permit.municipality', 'Bansud'));
        $shortMunicipality = trim(str_ireplace('Municipality of', '', $municipality));
        $province = trim((string) config('permit.province', 'Oriental Mindoro'));

        foreach (array_reverse($parts) as $part) {
            $lower = strtolower($part);
            if ($lower === '' || str_contains($lower, strtolower($shortMunicipality)) || str_contains($lower, strtolower($province))) {
                continue;
            }

            return $part;
        }

        return null;
    }

    public function edit(Application $application): View
    {
        $this->authorize('update', $application);

        $application->load('requirements.requirementType');

        return view('applicant.edit', [
            'application' => $application,
            'requirementTypes' => RequirementType::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function update(ApplicationRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $application->fill($request->validated());
        $application->save();

        return redirect()
            ->route('applications.edit', $application)
            ->with('success', 'Application details saved.');
    }

    public function summary(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load('requirements.requirementType');

        return view('applicant.summary', [
            'application' => $application,
            'requirementTypes' => RequirementType::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function submit(Application $application, ApplicationWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('submit', $application);

        // Guard: every required requirement slot must have a completed upload.
        $requiredCodes = RequirementType::where('is_active', true)
            ->where('is_required', true)
            ->pluck('id');

        $completed = $application->requirements()
            ->where('checklist_status', ApplicationRequirement::CHECK_COMPLETE)
            ->pluck('requirement_type_id');

        $missing = $requiredCodes->diff($completed);

        if ($missing->isNotEmpty()) {
            return redirect()
                ->route('applications.summary', $application)
                ->with('error', 'Please upload all required documents before submitting.');
        }

        $workflow->submit($application, $application->user);

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Application submitted. Your tracking number is '.$application->tracking_number.'.');
    }

    public function show(Application $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'requirements.requirementType',
            'statusHistories' => fn ($q) => $q->orderByDesc('created_at'),
            'remarks.author',
            'payments',
            'permit',
        ]);

        return view('applicant.show', [
            'application' => $application,
        ]);
    }
}
