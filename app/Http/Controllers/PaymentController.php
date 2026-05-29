<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    // ── Upload bukti pembayaran ────────────────────────────────────────────────

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // max 5MB
        ], [
            'proof.required' => 'File bukti pembayaran wajib diunggah.',
            'proof.mimes'    => 'Format file harus JPG, PNG, atau PDF.',
            'proof.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        $user         = Auth::user();
        $registration = $user->registration;

        if (! $registration) {
            return back()->withErrors(['proof' => 'Data pendaftaran tidak ditemukan.']);
        }

        if (in_array($registration->status, ['verified'])) {
            return back()->withErrors(['proof' => 'Pembayaran sudah diverifikasi.']);
        }

        $file     = $request->file('proof');
        $filename = 'payment_' . $registration->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs('payments', $filename, 'local');

        // Delete old payment proof if exists
        if ($registration->payment) {
            $old = $registration->payment;
            Storage::disk('local')->delete($old->proof_file_path);
            $old->delete();
        }

        // Create new payment record
        Payment::create([
            'registration_id'   => $registration->id,
            'proof_file_path'   => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_size'         => $file->getSize(),
            'uploaded_at'       => now(),
            'status'            => 'pending',
        ]);

        // Update registration status
        $registration->update(['status' => 'pending_verification']);

        return back()->with('flash', [
            'type'    => 'success',
            'message' => 'Bukti pembayaran berhasil diunggah! Menunggu verifikasi admin.',
        ]);
    }

    // ── Download / view proof (admin only) ────────────────────────────────────

    public function download(int $paymentId)
    {
        $payment = Payment::with('registration.user')->findOrFail($paymentId);

        if (! Storage::disk('local')->exists($payment->proof_file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('local')->download(
            $payment->proof_file_path,
            $payment->original_filename
        );
    }

    // ── Inline view for image proofs (admin only) ─────────────────────────────

    public function view(int $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        if (! Storage::disk('local')->exists($payment->proof_file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $mime = Storage::disk('local')->mimeType($payment->proof_file_path);

        return response(
            Storage::disk('local')->get($payment->proof_file_path),
            200,
            ['Content-Type' => $mime, 'Content-Disposition' => 'inline']
        );
    }
}
