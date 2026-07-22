<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeamMemberController extends Controller
{
    // ── Simpan biodata seluruh anggota tim (replace-all) ──────────────────────

    public function upload(Request $request): RedirectResponse
    {
        $user         = Auth::user();
        $registration = $user->registration;

        if (! $registration || $registration->status !== 'verified') {
            return back()->with('flash', [
                'type'    => 'error',
                'message' => 'Pendaftaran belum diverifikasi, biodata tim belum bisa diisi.',
            ]);
        }

        $data = $request->validate([
            'members'                    => ['required', 'array', 'size:' . $registration->member_count],
            'members.*.full_name'        => ['required', 'string', 'max:150'],
            'members.*.identity_number'  => ['required', 'string', 'max:50'],
            'members.*.institution'      => ['required', 'string', 'max:150'],
            'members.*.major'            => ['nullable', 'string', 'max:100'],
            'members.*.batch'            => ['nullable', 'string', 'max:50'],
            'members.*.phone'            => ['required', 'string', 'max:20'],
            'members.*.email'            => ['nullable', 'email', 'max:150'],
        ], [
            'members.size'                    => 'Jumlah anggota yang diisi harus sesuai jumlah anggota tim (:size orang).',
            'members.*.full_name.required'     => 'Nama lengkap wajib diisi.',
            'members.*.identity_number.required' => 'NIM/NISN wajib diisi.',
            'members.*.institution.required'   => 'Asal sekolah/kampus wajib diisi.',
            'members.*.phone.required'         => 'Nomor WA wajib diisi.',
        ]);

        DB::transaction(function () use ($registration, $data) {
            $registration->teamMembers()->delete();

            foreach ($data['members'] as $i => $member) {
                $registration->teamMembers()->create([
                    'position'         => $i + 1,
                    'is_leader'        => $i === 0,
                    'full_name'        => $member['full_name'],
                    'identity_number'  => $member['identity_number'],
                    'institution'      => $member['institution'],
                    'major'            => $member['major'] ?? null,
                    'batch'            => $member['batch'] ?? null,
                    'phone'            => $member['phone'],
                    'email'            => $member['email'] ?? null,
                ]);
            }
        });

        return back()->with('flash', [
            'type'    => 'success',
            'message' => 'Biodata tim berhasil disimpan.',
        ]);
    }
}
