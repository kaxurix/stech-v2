<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Registration;
use App\Models\Submission;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Data demo untuk mencoba panel admin: 4 tim yang masing-masing berhenti di
 * tahap berbeda, supaya semua aksi admin (verifikasi, tolak, loloskan finalis)
 * punya data untuk dicoba.
 *
 * Menjalankan seeder ini akan MENGHAPUS seluruh akun peserta yang ada beserta
 * pendaftaran/pembayaran/submission-nya. Akun admin tidak disentuh.
 *
 *   php artisan db:seed --class=DemoParticipantsSeeder
 */
class DemoParticipantsSeeder extends Seeder
{
    private const PASSWORD = 'password';

    public function run(): void
    {
        $this->wipeExistingParticipants();

        // 1. Baru daftar, belum upload bukti bayar.
        $this->makeTeam(
            name: 'Dimas Prayoga',
            email: 'dimas@example.com',
            teamName: 'Garuda Koding',
            institution: 'SMA Negeri 1 Purwokerto',
            category: 'sma',
            memberCount: 3,
            phone: '081234000001',
            status: 'pending_payment',
        );

        // 2. Sudah upload bukti, menunggu diverifikasi admin.
        //    Pakai tim ini untuk mencoba tombol "Verifikasi Pembayaran" / "Tolak Pembayaran".
        $this->makeTeam(
            name: 'Anisa Rahmawati',
            email: 'anisa@example.com',
            teamName: 'Sandi Nusantara',
            institution: 'Universitas Jenderal Soedirman',
            category: 'mahasiswa',
            memberCount: 2,
            phone: '081234000002',
            status: 'pending_verification',
            paymentStatus: 'pending',
            members: [
                ['Anisa Rahmawati', 'H1D022001', 'Informatika', '2022', '081234000002'],
                ['Bagas Setiawan', 'H1D022002', 'Informatika', '2022', '081234000012'],
            ],
        );

        // 3. Sudah diverifikasi dan sudah mengumpulkan karya.
        //    Pakai tim ini untuk mencoba tombol "Loloskan ke Final".
        $this->makeTeam(
            name: 'Rizky Ramadhan',
            email: 'rizky@example.com',
            teamName: 'Rimba Digital',
            institution: 'Universitas Gadjah Mada',
            category: 'mahasiswa',
            memberCount: 4,
            phone: '081234000003',
            status: 'verified',
            paymentStatus: 'approved',
            adminNotes: 'Bukti transfer sesuai nominal.',
            members: [
                ['Rizky Ramadhan', '21/480001/PA/001', 'Ilmu Komputer', '2021', '081234000003'],
                ['Salsabila Putri', '21/480002/PA/002', 'Ilmu Komputer', '2021', '081234000013'],
                ['Fajar Nugroho', '21/480003/PA/003', 'Elektronika', '2021', '081234000023'],
                ['Intan Permata', '21/480004/PA/004', 'Ilmu Komputer', '2022', '081234000033'],
            ],
            submission: [
                'title' => 'SIGAP — Sistem Informasi Gawat Darurat Puskesmas',
                'github' => 'https://github.com/contoh/sigap',
                'drive' => 'https://drive.google.com/file/d/contoh-sigap',
                'description' => 'Aplikasi web untuk memantau ketersediaan kamar dan antrean gawat darurat '
                    .'puskesmas secara real-time, dilengkapi notifikasi untuk petugas jaga.',
            ],
        );

        // 4. Bukti bayar ditolak admin.
        $this->makeTeam(
            name: 'Yoga Pratama',
            email: 'yoga@example.com',
            teamName: 'Bumi Siber',
            institution: 'Komunitas Dev Purwokerto',
            category: 'umum',
            memberCount: 2,
            phone: '081234000004',
            status: 'rejected',
            paymentStatus: 'rejected',
            adminNotes: 'Nominal transfer kurang dari biaya pendaftaran. Mohon unggah ulang bukti yang benar.',
            members: [
                ['Yoga Pratama', '3302010101010001', null, null, '081234000004'],
                ['Nadia Safitri', '3302010101010002', null, null, '081234000014'],
            ],
        );

        $this->command->info('4 tim demo dibuat. Password semua akun: '.self::PASSWORD);
        $this->command->table(
            ['Tim', 'Email', 'Status'],
            Registration::with('user')->get()->map(fn ($r) => [
                $r->team_name, $r->user->email, $r->statusLabel(),
            ])->all()
        );
    }

    /**
     * Hapus peserta lama beserta seluruh data turunannya. Akun admin dibiarkan.
     */
    private function wipeExistingParticipants(): void
    {
        foreach (Registration::with('payment')->get() as $registration) {
            if ($path = $registration->payment?->proof_file_path) {
                Storage::disk('local')->delete($path);
            }
        }

        // Foreign key cascade menghapus registration → payment/submission/team_members.
        User::where('role', 'peserta')->get()->each->delete();

        // Sisa file bukti dari data yang sudah tidak punya baris di database.
        foreach (Storage::disk('local')->files('payments') as $orphan) {
            Storage::disk('local')->delete($orphan);
        }
    }

    private function makeTeam(
        string $name,
        string $email,
        string $teamName,
        string $institution,
        string $category,
        int $memberCount,
        string $phone,
        string $status,
        ?string $paymentStatus = null,
        ?string $adminNotes = null,
        array $members = [],
        ?array $submission = null,
    ): void {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(self::PASSWORD),
            'role' => 'peserta',
        ]);

        $registration = Registration::create([
            'user_id' => $user->id,
            'team_name' => $teamName,
            'competition' => 'web-development',
            'member_count' => $memberCount,
            'institution' => $institution,
            'phone' => $phone,
            'category' => $category,
            'status' => $status,
            'is_finalist' => false,
        ]);

        if ($paymentStatus !== null) {
            $this->attachPayment($registration, $teamName, $paymentStatus, $adminNotes);
        }

        foreach ($members as $i => [$fullName, $identity, $major, $batch, $memberPhone]) {
            TeamMember::create([
                'registration_id' => $registration->id,
                'position' => $i + 1,
                'is_leader' => $i === 0,
                'full_name' => $fullName,
                'identity_number' => $identity,
                'institution' => $institution,
                'major' => $major,
                'batch' => $batch,
                'phone' => $memberPhone,
                'email' => $i === 0 ? $email : null,
            ]);
        }

        if ($submission !== null) {
            Submission::create([
                'registration_id' => $registration->id,
                'project_title' => $submission['title'],
                'github_url' => $submission['github'],
                'drive_url' => $submission['drive'],
                'description' => $submission['description'],
            ]);
        }
    }

    private function attachPayment(
        Registration $registration,
        string $teamName,
        string $status,
        ?string $adminNotes,
    ): void {
        $filename = 'payment_demo_'.$registration->id.'.png';
        $path = 'payments/'.$filename;

        Storage::disk('local')->put($path, $this->fakeProofImage($teamName));

        $verified = in_array($status, ['approved', 'rejected'], true);

        Payment::create([
            'registration_id' => $registration->id,
            'proof_file_path' => $path,
            'original_filename' => 'bukti_transfer_'.str($teamName)->slug('_').'.png',
            'file_size' => Storage::disk('local')->size($path),
            'uploaded_at' => now()->subDays(2),
            'status' => $status,
            'verified_at' => $verified ? now()->subDay() : null,
            'verified_by' => $verified ? User::where('role', 'admin')->value('id') : null,
            'admin_notes' => $adminNotes,
        ]);
    }

    /**
     * Gambar "bukti transfer" sederhana supaya preview di panel admin ada isinya.
     */
    private function fakeProofImage(string $teamName): string
    {
        $image = imagecreatetruecolor(560, 320);

        $white = imagecolorallocate($image, 255, 255, 255);
        $navy = imagecolorallocate($image, 30, 58, 138);
        $slate = imagecolorallocate($image, 71, 85, 105);
        $amber = imagecolorallocate($image, 245, 158, 11);

        imagefilledrectangle($image, 0, 0, 560, 320, $white);
        imagefilledrectangle($image, 0, 0, 560, 64, $navy);
        imagestring($image, 5, 24, 24, 'BUKTI TRANSFER (DEMO)', $white);

        imagestring($image, 4, 24, 104, 'Tim    : '.$teamName, $slate);
        imagestring($image, 4, 24, 136, 'Bank   : BCA', $slate);
        imagestring($image, 4, 24, 168, 'Nominal: Rp 100.000', $slate);
        imagestring($image, 4, 24, 200, 'Tanggal: '.now()->subDays(2)->format('d/m/Y H:i'), $slate);
        imagestring($image, 3, 24, 250, 'Data contoh - bukan bukti pembayaran asli', $amber);
        imagefilledrectangle($image, 0, 300, 560, 320, $amber);

        ob_start();
        imagepng($image);
        $contents = ob_get_clean();
        imagedestroy($image);

        return $contents;
    }
}
