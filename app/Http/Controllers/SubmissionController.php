<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'project_title' => 'required|string|max:255',
            'github_url'    => 'required|url|max:255',
            'drive_url'     => 'nullable|url|max:255',
            'description'   => 'required|string',
        ]);

        $user = Auth::user();
        $registration = $user->registration;

        if (!$registration || $registration->status !== 'verified') {
            return back()->with('error', 'Pendaftaran Anda belum diverifikasi.');
        }

        $submission = $registration->submission()->updateOrCreate(
            ['registration_id' => $registration->id],
            [
                'project_title' => $request->project_title,
                'github_url'    => $request->github_url,
                'drive_url'     => $request->drive_url,
                'description'   => $request->description,
            ]
        );

        return back()->with('success', 'Karya berhasil disubmit.');
    }
}
