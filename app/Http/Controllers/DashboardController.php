<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user()->load(['registration.payment', 'registration.submission']);

        $registration = $user->registration;
        $payment      = $registration?->payment;
        $submission   = $registration?->submission;

        return Inertia::render('Dashboard', [
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'registration' => $registration ? [
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
                'created_at'   => $registration->created_at->format('d M Y'),
            ] : null,
            'payment' => $payment ? [
                'id'                => $payment->id,
                'original_filename' => $payment->original_filename,
                'file_size'         => $payment->formattedSize(),
                'status'            => $payment->status,
                'uploaded_at'       => $payment->uploaded_at?->format('d M Y, H:i'),
                'verified_at'       => $payment->verified_at?->format('d M Y, H:i'),
                'admin_notes'       => $payment->admin_notes,
                'is_image'          => $payment->isImage(),
            ] : null,
            'submission' => $submission ? [
                'id'            => $submission->id,
                'github_url'    => $submission->github_url,
                'drive_url'     => $submission->drive_url,
                'project_title' => $submission->project_title,
                'description'   => $submission->description,
                'submitted_at'  => $submission->created_at->format('d M Y, H:i'),
            ] : null,
        ]);
    }
}
