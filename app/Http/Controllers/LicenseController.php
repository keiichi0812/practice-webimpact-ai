<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\LicenseAssignment;
use App\Services\LicenseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function __construct(private readonly LicenseService $service) {}

    public function index(Request $request): View|JsonResponse
    {
        $validated = $request->validate([
            'expiring_within' => ['nullable', 'integer', 'min:0', 'max:3650'],
        ]);

        $licenses = License::query()
            ->when(isset($validated['expiring_within']), fn ($q) => $q->expiringWithin((int) $validated['expiring_within']))
            ->orderBy('expires_on')
            ->get();

        if ($request->wantsJson()) {
            return response()->json(['data' => $licenses->map(fn (License $l) => $this->present($l))]);
        }

        return view('licenses.index', [
            'licenses' => $licenses,
            'expiringWithin' => $validated['expiring_within'] ?? null,
        ]);
    }

    public function show(Request $request, License $license): View|JsonResponse
    {
        $license->load('assignments');

        if ($request->wantsJson()) {
            return response()->json(['data' => $this->present($license, withAssignments: true)]);
        }

        return view('licenses.show', ['license' => $license]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'product_code' => ['required', 'string', 'max:32'],
            'seats' => ['required', 'integer', 'min:1', 'max:10000'],
            'starts_on' => ['required', 'date'],
            'expires_on' => ['required', 'date', 'after_or_equal:starts_on'],
        ]);

        $license = License::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['data' => $this->present($license)], 201);
        }

        return redirect()->route('licenses.show', $license);
    }

    public function renew(Request $request, License $license): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'months' => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        $this->service->renew($license, (int) $validated['months']);

        if ($request->wantsJson()) {
            return response()->json(['data' => $this->present($license->refresh())]);
        }

        return redirect()->route('licenses.show', $license)->with('status', '有効期限を更新しました。');
    }

    public function assign(Request $request, License $license): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'user_email' => ['required', 'email', 'max:255'],
        ]);

        $assignment = $this->service->assignSeat($license, $validated['user_email']);

        if ($request->wantsJson()) {
            return response()->json(['data' => $assignment], 201);
        }

        return redirect()->route('licenses.show', $license)->with('status', '座席を割り当てました。');
    }

    public function release(Request $request, License $license, int $assignmentId): RedirectResponse|JsonResponse
    {
        $assignment = LicenseAssignment::findOrFail($assignmentId);

        $this->service->releaseSeat($license, $assignment);

        if ($request->wantsJson()) {
            return response()->json(['data' => $this->present($license->refresh())]);
        }

        return redirect()->route('licenses.show', $license)->with('status', '割り当てを解除しました。');
    }

    /** @return array<string, mixed> */
    private function present(License $license, bool $withAssignments = false): array
    {
        $data = [
            'id' => $license->id,
            'customer_name' => $license->customer_name,
            'product_code' => $license->product_code,
            'seats' => $license->seats,
            'seats_used' => $license->seats_used,
            'remaining_seats' => $license->remainingSeats(),
            'starts_on' => $license->starts_on->toDateString(),
            'expires_on' => $license->expires_on->toDateString(),
            'status' => $license->status,
            'is_expired' => $license->isExpired(),
        ];

        if ($withAssignments) {
            $data['assignments'] = $license->assignments->map(fn ($a) => [
                'id' => $a->id,
                'user_email' => $a->user_email,
                'assigned_at' => $a->assigned_at->toIso8601String(),
                'released_at' => $a->released_at?->toIso8601String(),
            ])->values();
        }

        return $data;
    }
}
