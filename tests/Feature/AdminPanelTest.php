<?php

namespace Tests\Feature;

use App\Filament\Resources\Registrations\Pages\ListRegistrations;
use App\Filament\Resources\Registrations\Pages\ViewRegistration;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin S-Tech',
            'email' => 'admin@stech.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
    }

    private function peserta(string $email = 'peserta@example.com'): User
    {
        return User::create([
            'name' => 'Peserta',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'peserta',
        ]);
    }

    private function registrationFor(User $user, string $status = 'pending_verification'): Registration
    {
        $registration = Registration::create([
            'user_id' => $user->id,
            'team_name' => 'Tim Uji',
            'competition' => 'web-development',
            'member_count' => 2,
            'institution' => 'Unsoed',
            'phone' => '0812',
            'category' => 'mahasiswa',
            'status' => $status,
        ]);

        Payment::create([
            'registration_id' => $registration->id,
            'proof_file_path' => 'payments/x.jpg',
            'original_filename' => 'x.jpg',
            'file_size' => 100,
            'uploaded_at' => now(),
            'status' => 'pending',
        ]);

        return $registration;
    }

    public function test_admin_can_log_in_and_is_sent_to_the_filament_panel(): void
    {
        $this->admin();

        $response = $this->post('/login', [
            'email' => 'admin@stech.id',
            'password' => 'admin123',
        ]);

        $this->assertAuthenticated();
        // Inertia::location() issues a 409 with the target in X-Inertia-Location for
        // Inertia requests, and a plain redirect otherwise.
        $response->assertRedirect('/admin');
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $this->admin();

        $this->post('/login', [
            'email' => 'admin@stech.id',
            'password' => 'salah',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_peserta_is_sent_to_dashboard_not_admin(): void
    {
        $this->peserta();

        $this->post('/login', [
            'email' => 'peserta@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_peserta_cannot_access_admin_panel(): void
    {
        $this->actingAs($this->peserta())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_guest_cannot_access_admin_panel(): void
    {
        $this->get('/admin')->assertRedirect();
        $this->assertGuest();
    }

    public function test_admin_can_access_panel_and_see_registrations(): void
    {
        $admin = $this->admin();
        $this->registrationFor($this->peserta());

        $this->actingAs($admin)->get('/admin')->assertSuccessful();

        Livewire::actingAs($admin)
            ->test(ListRegistrations::class)
            ->assertCanSeeTableRecords(Registration::all());
    }

    public function test_admin_can_approve_a_payment(): void
    {
        $admin = $this->admin();
        $registration = $this->registrationFor($this->peserta());

        Livewire::actingAs($admin)
            ->test(ViewRegistration::class, ['record' => $registration->getKey()])
            ->callAction('approve', ['notes' => 'oke'])
            ->assertHasNoActionErrors();

        $registration->refresh();
        $this->assertSame('verified', $registration->status);
        $this->assertSame('approved', $registration->payment->status);
        $this->assertSame($admin->id, $registration->payment->verified_by);
        $this->assertSame('oke', $registration->payment->admin_notes);
    }

    public function test_admin_can_reject_a_payment_and_notes_are_required(): void
    {
        $admin = $this->admin();
        $registration = $this->registrationFor($this->peserta());

        Livewire::actingAs($admin)
            ->test(ViewRegistration::class, ['record' => $registration->getKey()])
            ->callAction('reject', ['notes' => ''])
            ->assertHasActionErrors(['notes']);

        $this->assertSame('pending_verification', $registration->fresh()->status);

        Livewire::actingAs($admin)
            ->test(ViewRegistration::class, ['record' => $registration->getKey()])
            ->callAction('reject', ['notes' => 'bukti tidak jelas'])
            ->assertHasNoActionErrors();

        $registration->refresh();
        $this->assertSame('rejected', $registration->status);
        $this->assertSame('rejected', $registration->payment->status);
        $this->assertSame('bukti tidak jelas', $registration->payment->admin_notes);
    }

    public function test_admin_can_toggle_finalist_on_a_verified_team(): void
    {
        $admin = $this->admin();
        $registration = $this->registrationFor($this->peserta(), 'verified');

        Livewire::actingAs($admin)
            ->test(ViewRegistration::class, ['record' => $registration->getKey()])
            ->callAction('toggleFinalist');

        $this->assertTrue($registration->fresh()->is_finalist);

        Livewire::actingAs($admin)
            ->test(ViewRegistration::class, ['record' => $registration->getKey()])
            ->callAction('toggleFinalist');

        $this->assertFalse($registration->fresh()->is_finalist);
    }

    public function test_finalist_action_is_hidden_for_unverified_team(): void
    {
        $admin = $this->admin();
        $registration = $this->registrationFor($this->peserta(), 'pending_verification');

        Livewire::actingAs($admin)
            ->test(ViewRegistration::class, ['record' => $registration->getKey()])
            ->assertActionHidden('toggleFinalist');
    }

    public function test_admin_can_edit_a_registration(): void
    {
        $admin = $this->admin();
        $registration = $this->registrationFor($this->peserta());

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\Registrations\Pages\EditRegistration::class, [
                'record' => $registration->getKey(),
            ])
            ->fillForm([
                'team_name' => 'Tim Baru',
                'institution' => 'UGM',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $registration->refresh();
        $this->assertSame('Tim Baru', $registration->team_name);
        $this->assertSame('UGM', $registration->institution);
    }

    public function test_table_can_filter_by_status(): void
    {
        $admin = $this->admin();
        $verified = $this->registrationFor($this->peserta('a@example.com'), 'verified');
        $pending = $this->registrationFor($this->peserta('b@example.com'), 'pending_verification');

        Livewire::actingAs($admin)
            ->test(ListRegistrations::class)
            ->filterTable('status', 'verified')
            ->assertCanSeeTableRecords([$verified])
            ->assertCanNotSeeTableRecords([$pending]);
    }

    public function test_table_can_search_by_team_name(): void
    {
        $admin = $this->admin();
        $a = $this->registrationFor($this->peserta('a@example.com'));
        $a->update(['team_name' => 'Garuda']);
        $b = $this->registrationFor($this->peserta('b@example.com'));
        $b->update(['team_name' => 'Rajawali']);

        Livewire::actingAs($admin)
            ->test(ListRegistrations::class)
            ->searchTable('Garuda')
            ->assertCanSeeTableRecords([$a])
            ->assertCanNotSeeTableRecords([$b]);
    }

    public function test_only_admin_can_download_payment_proof(): void
    {
        $registration = $this->registrationFor($this->peserta());
        $paymentId = $registration->payment->id;

        $this->actingAs($this->peserta('other@example.com'))
            ->get("/admin/payment/{$paymentId}/view")
            ->assertForbidden();
    }
}
