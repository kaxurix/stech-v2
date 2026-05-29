<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    // ── Dashboard: list semua pendaftar ───────────────────────────────────────

    public function index(Request $request): Response
    {
        $query = Registration::with(['user', 'payment'])
            ->latest();

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by name / team / email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('team_name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                                                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $registrations = $query->paginate(15)->through(fn ($reg) => [
            'id'           => $reg->id,
            'team_name'    => $reg->team_name,
            'competition'  => $reg->competition,
            'member_count' => $reg->member_count,
            'institution'  => $reg->institution,
            'category'     => $reg->category,
            'status'       => $reg->status,
            'status_label' => $reg->statusLabel(),
            'status_color' => $reg->statusColor(),
            'created_at'   => $reg->created_at->format('d M Y'),
            'user' => [
                'name'  => $reg->user->name,
                'email' => $reg->user->email,
            ],
            'payment' => $reg->payment ? [
                'status'     => $reg->payment->status,
                'uploaded_at' => $reg->payment->uploaded_at?->format('d M Y, H:i'),
            ] : null,
        ]);

        // Stats
        $stats = [
            'total'                => Registration::count(),
            'pending_payment'      => Registration::where('status', 'pending_payment')->count(),
            'pending_verification' => Registration::where('status', 'pending_verification')->count(),
            'verified'             => Registration::where('status', 'verified')->count(),
            'rejected'             => Registration::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/Index', [
            'registrations' => $registrations,
            'stats'         => $stats,
            'filters'       => $request->only(['status', 'search']),
        ]);
    }

    // ── Show detail peserta + bukti ────────────────────────────────────────────

    public function show(int $id): Response
    {
        $registration = Registration::with(['user', 'payment.verifier'])->findOrFail($id);

        return Inertia::render('Admin/Show', [
            'registration' => [
                'id'           => $registration->id,
                'team_name'    => $registration->team_name,
                'competition'  => $registration->competition,
                'member_count' => $registration->member_count,
                'institution'  => $registration->institution,
                'phone'        => $registration->phone,
                'category'     => $registration->category,
                'status'       => $registration->status,
                'status_label' => $registration->statusLabel(),
                'status_color' => $registration->statusColor(),
                'created_at'   => $registration->created_at->format('d M Y, H:i'),
            ],
            'user' => [
                'id'    => $registration->user->id,
                'name'  => $registration->user->name,
                'email' => $registration->user->email,
            ],
            'payment' => $registration->payment ? [
                'id'                => $registration->payment->id,
                'original_filename' => $registration->payment->original_filename,
                'file_size'         => $registration->payment->formattedSize(),
                'status'            => $registration->payment->status,
                'uploaded_at'       => $registration->payment->uploaded_at?->format('d M Y, H:i'),
                'verified_at'       => $registration->payment->verified_at?->format('d M Y, H:i'),
                'admin_notes'       => $registration->payment->admin_notes,
                'is_image'          => $registration->payment->isImage(),
                'verifier_name'     => $registration->payment->verifier?->name,
            ] : null,
        ]);
    }

    // ── Approve pembayaran ─────────────────────────────────────────────────────

    public function approve(Request $request, int $id): RedirectResponse
    {
        $registration = Registration::with('payment')->findOrFail($id);

        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if (! $registration->payment) {
            return back()->withErrors(['error' => 'Bukti pembayaran belum ada.']);
        }

        $registration->payment->update([
            'status'      => 'approved',
            'verified_at' => now(),
            'verified_by' => Auth::id(),
            'admin_notes' => $request->notes,
        ]);

        $registration->update(['status' => 'verified']);

        return redirect()->route('admin.show', $id)->with('flash', [
            'type'    => 'success',
            'message' => "Pembayaran {$registration->team_name} berhasil diverifikasi!",
        ]);
    }

    // ── Reject pembayaran ──────────────────────────────────────────────────────

    public function reject(Request $request, int $id): RedirectResponse
    {
        $registration = Registration::with('payment')->findOrFail($id);

        $request->validate([
            'notes' => ['required', 'string', 'max:500'],
        ], [
            'notes.required' => 'Alasan penolakan wajib diisi.',
        ]);

        if (! $registration->payment) {
            return back()->withErrors(['error' => 'Bukti pembayaran belum ada.']);
        }

        $registration->payment->update([
            'status'      => 'rejected',
            'verified_at' => now(),
            'verified_by' => Auth::id(),
            'admin_notes' => $request->notes,
        ]);

        $registration->update(['status' => 'rejected']);

        return redirect()->route('admin.show', $id)->with('flash', [
            'type'    => 'error',
            'message' => "Pembayaran {$registration->team_name} ditolak.",
        ]);
    }
}
