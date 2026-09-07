<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Registration;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParticipantFlowTest extends TestCase
{
    use RefreshDatabase;

    private function registerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'team_name' => 'Tim Garuda',
            'competition' => 'web-development',
            'member_count' => 2,
            'institution' => 'Unsoed',
            'phone' => '08123456789',
            'category' => 'mahasiswa',
        ], $overrides);
    }

    private function verifiedParticipant(): User
    {
        $user = User::create([
            'name' => 'Peserta',
            'email' => 'peserta@example.com',
            'password' => Hash::make('password'),
            'role' => 'peserta',
        ]);

        Registration::create([
            'user_id' => $user->id,
            'team_name' => 'Tim Uji',
            'competition' => 'web-development',
            'member_count' => 2,
            'institution' => 'Unsoed',
            'phone' => '0812',
            'category' => 'mahasiswa',
            'status' => 'verified',
        ]);

        return $user;
    }

    public function test_participant_can_register_and_gets_a_pending_payment_registration(): void
    {
        $this->post('/register', $this->registerPayload())
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $user = User::where('email', 'budi@example.com')->first();
        $this->assertSame('peserta', $user->role);
        $this->assertSame('pending_payment', $user->registration->status);
        $this->assertSame('Tim Garuda', $user->registration->team_name);
    }

    /**
     * Lomba hanya menerima tim 2-3 orang. Aturan ini juga tampil di halaman
     * depan, jadi validasinya dikunci di sini supaya frontend dan backend
     * tidak berbeda kalau salah satunya diubah.
     */
    public function test_team_size_outside_two_to_three_is_rejected(): void
    {
        foreach ([1, 4] as $invalid) {
            $this->post('/register', $this->registerPayload([
                'email' => "tim{$invalid}@example.com",
                'member_count' => $invalid,
            ]))->assertSessionHasErrors('member_count');
        }

        $this->assertSame(0, User::where('role', 'peserta')->count());
    }

    public function test_team_size_two_and_three_are_accepted(): void
    {
        foreach ([2, 3] as $valid) {
            $this->post('/register', $this->registerPayload([
                'email' => "oke{$valid}@example.com",
                'member_count' => $valid,
            ]))->assertSessionHasNoErrors();

            $this->post('/logout');
        }

        $this->assertSame(2, User::where('role', 'peserta')->count());
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $this->post('/register', $this->registerPayload());
        $this->post('/logout');

        $this->post('/register', $this->registerPayload())
            ->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', 'budi@example.com')->count());
    }

    public function test_payment_upload_moves_registration_to_pending_verification(): void
    {
        Storage::fake('local');

        $this->post('/register', $this->registerPayload());
        $user = User::where('email', 'budi@example.com')->first();

        $this->actingAs($user)->post('/payment/upload', [
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $user->refresh();
        $this->assertSame('pending_verification', $user->registration->status);
        $this->assertNotNull($user->registration->payment);
        $this->assertSame('pending', $user->registration->payment->status);
        Storage::disk('local')->assertExists($user->registration->payment->proof_file_path);
    }

    public function test_payment_upload_rejects_disallowed_file_type(): void
    {
        Storage::fake('local');

        $this->post('/register', $this->registerPayload());
        $user = User::where('email', 'budi@example.com')->first();

        $this->actingAs($user)->post('/payment/upload', [
            'proof' => UploadedFile::fake()->create('virus.exe', 10),
        ])->assertSessionHasErrors('proof');

        $this->assertSame('pending_payment', $user->fresh()->registration->status);
    }

    public function test_submission_is_blocked_until_registration_is_verified(): void
    {
        $this->post('/register', $this->registerPayload());
        $user = User::where('email', 'budi@example.com')->first();

        $this->actingAs($user)->post('/submission/upload', [
            'project_title' => 'Proyek A',
            'github_url' => 'https://github.com/x/y',
            'description' => 'Deskripsi proyek',
        ]);

        $this->assertSame(0, Submission::count());
    }

    public function test_verified_participant_can_submit_and_update_their_work(): void
    {
        $user = $this->verifiedParticipant();

        $this->actingAs($user)->post('/submission/upload', [
            'project_title' => 'Proyek A',
            'github_url' => 'https://github.com/x/y',
            'drive_url' => 'https://drive.google.com/abc',
            'description' => 'Deskripsi proyek',
        ]);

        $submission = $user->registration->fresh()->submission;
        $this->assertNotNull($submission);
        $this->assertSame('Proyek A', $submission->project_title);

        // Re-submitting updates the same record rather than creating a duplicate.
        $this->actingAs($user)->post('/submission/upload', [
            'project_title' => 'Proyek B',
            'github_url' => 'https://github.com/x/z',
            'description' => 'Deskripsi baru',
        ]);

        $this->assertSame(1, Submission::count());
        $this->assertSame('Proyek B', $user->registration->fresh()->submission->project_title);
    }

    public function test_submission_requires_valid_github_url(): void
    {
        $user = $this->verifiedParticipant();

        $this->actingAs($user)->post('/submission/upload', [
            'project_title' => 'Proyek A',
            'github_url' => 'bukan-url',
            'description' => 'Deskripsi',
        ])->assertSessionHasErrors('github_url');

        $this->assertSame(0, Submission::count());
    }

    public function test_verified_participant_can_save_team_members(): void
    {
        $user = $this->verifiedParticipant();

        $this->actingAs($user)->post('/team-members/upload', [
            'members' => [
                ['full_name' => 'Ketua', 'identity_number' => 'H1', 'institution' => 'Unsoed', 'phone' => '0812'],
                ['full_name' => 'Anggota', 'identity_number' => 'H2', 'institution' => 'Unsoed', 'phone' => '0813'],
            ],
        ]);

        $members = $user->registration->fresh()->teamMembers;
        $this->assertCount(2, $members);
        $this->assertTrue($members->first()->is_leader);
        $this->assertSame('Ketua', $members->first()->full_name);
    }

    public function test_team_member_count_must_match_registration(): void
    {
        $user = $this->verifiedParticipant(); // member_count = 2

        $this->actingAs($user)->post('/team-members/upload', [
            'members' => [
                ['full_name' => 'Ketua', 'identity_number' => 'H1', 'institution' => 'Unsoed', 'phone' => '0812'],
            ],
        ])->assertSessionHasErrors('members');

        $this->assertCount(0, $user->registration->fresh()->teamMembers);
    }

    public function test_guest_cannot_reach_dashboard_or_upload_routes(): void
    {
        $this->get('/dashboard')->assertRedirect('/');
        $this->post('/submission/upload', [])->assertRedirect('/');
    }
}
