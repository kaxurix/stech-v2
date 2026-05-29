<script setup lang="ts">
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps<{
  registration: {
    id: number; team_name: string; competition: string
    member_count: number; institution: string; phone: string
    category: string; status: string; status_label: string
    status_color: string; created_at: string
  }
  user: { id: number; name: string; email: string }
  payment: {
    id: number; original_filename: string; file_size: string
    status: string; uploaded_at: string | null
    verified_at: string | null; admin_notes: string | null
    is_image: boolean; verifier_name: string | null
  } | null
}>()

const page = usePage<{ flash: { type?: string; message?: string }; auth: { user: { name: string } } }>()

// ── State ─────────────────────────────────────────────────────────────────────
const approveNotes    = ref('')
const rejectNotes     = ref('')
const rejectError     = ref('')
const showRejectForm  = ref(false)
const processing      = ref(false)
const toastVisible    = ref(false)
const toastMsg        = ref('')
const toastType       = ref<'success' | 'error'>('success')

// Show flash on mount
if (page.props.flash?.message) {
  toastVisible.value = true
  toastMsg.value     = page.props.flash.message
  toastType.value    = (page.props.flash.type as 'success' | 'error') ?? 'success'
  setTimeout(() => { toastVisible.value = false }, 4000)
}

// ── Approve ───────────────────────────────────────────────────────────────────
function approve() {
  processing.value = true
  router.post(`/admin/peserta/${props.registration.id}/approve`, { notes: approveNotes.value }, {
    onFinish: () => { processing.value = false },
  })
}

// ── Reject ────────────────────────────────────────────────────────────────────
function reject() {
  if (!rejectNotes.value.trim()) {
    rejectError.value = 'Alasan penolakan wajib diisi.'
    return
  }
  rejectError.value = ''
  processing.value  = true
  router.post(`/admin/peserta/${props.registration.id}/reject`, { notes: rejectNotes.value }, {
    onFinish: () => { processing.value = false },
  })
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function statusBadgeClass(color: string): string {
  const map: Record<string, string> = {
    amber:  'text-amber-400 border-amber-500/30 bg-amber-500/10',
    violet: 'text-violet-400 border-violet-500/30 bg-violet-500/10',
    green:  'text-green-400 border-green-500/30 bg-green-500/10',
    red:    'text-red-400 border-red-500/30 bg-red-500/10',
  }
  return map[color] ?? 'text-gray-400 border-gray-500/30 bg-gray-500/10'
}

function categoryLabel(cat: string): string {
  const m: Record<string, string> = { sma: 'Pelajar SMA/SMK', mahasiswa: 'Mahasiswa', umum: 'Masyarakat Umum' }
  return m[cat] ?? cat
}

function logout() {
  router.post('/logout')
}
</script>

<template>
  <div class="min-h-screen bg-[#0a0a0a] text-white font-['Inter',sans-serif]">

    <!-- Toast -->
    <Transition enter-active-class="transition-all duration-400 ease-out"
                enter-from-class="opacity-0 -translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-300"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="toastVisible"
           class="fixed top-6 right-6 z-50 px-5 py-3.5 rounded-xl shadow-2xl
                  flex items-center gap-3 text-sm font-medium"
           :class="toastType === 'success'
             ? 'bg-green-500/15 border border-green-500/30 text-green-300'
             : 'bg-red-500/15 border border-red-500/30 text-red-300'">
        <span>{{ toastType === 'success' ? '✓' : '✕' }}</span>
        {{ toastMsg }}
      </div>
    </Transition>

    <!-- Header -->
    <header class="sticky top-0 z-40 border-b border-white/[0.07] bg-[#0a0a0a]/90 backdrop-blur-xl">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <a href="/admin"
             class="flex items-center gap-2 text-white/50 hover:text-white transition-colors duration-200">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 12H5M12 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="text-sm">Kembali</span>
          </a>
          <div class="w-px h-5 bg-white/10"></div>
          <div class="flex items-center">
            <p class="text-sm font-semibold text-white">Detail Peserta</p>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <span class="text-xs text-white/40">{{ page.props.auth.user.name }}</span>
          <button @click="logout"
                  class="px-3 py-1.5 text-xs rounded-lg border border-white/10
                         text-white/50 hover:text-white hover:bg-white/5 transition-all duration-200">
            Keluar
          </button>
        </div>
      </div>
    </header>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

      <!-- Page header -->
      <div class="flex items-start justify-between mb-6 sm:mb-8 flex-col sm:flex-row gap-4">
        <div>
          <h1 class="text-2xl font-bold text-white">{{ props.registration.team_name }}</h1>
          <p class="text-sm text-white/40 mt-1">{{ props.user.name }} · {{ props.user.email }}</p>
        </div>
        <span :class="['px-3 py-1.5 rounded-full border text-xs font-semibold',
                       statusBadgeClass(props.registration.status_color)]">
          {{ props.registration.status_label }}
        </span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Info + Payment proof -->
        <div class="lg:col-span-2 space-y-5">

          <!-- Registration info -->
          <div class="p-6 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
            <p class="text-xs font-semibold text-white/40 uppercase tracking-wider mb-4">Data Pendaftaran</p>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-xs text-white/40 mb-1">Nama Ketua</p>
                <p class="text-sm font-medium text-white">{{ props.user.name }}</p>
              </div>
              <div>
                <p class="text-xs text-white/40 mb-1">Email</p>
                <p class="text-sm font-medium text-white">{{ props.user.email }}</p>
              </div>
              <div>
                <p class="text-xs text-white/40 mb-1">Nama Tim</p>
                <p class="text-sm font-medium text-white">{{ props.registration.team_name }}</p>
              </div>
              <div>
                <p class="text-xs text-white/40 mb-1">Jumlah Anggota</p>
                <p class="text-sm font-medium text-white">{{ props.registration.member_count }} orang</p>
              </div>
              <div>
                <p class="text-xs text-white/40 mb-1">Institusi</p>
                <p class="text-sm font-medium text-white">{{ props.registration.institution }}</p>
              </div>
              <div>
                <p class="text-xs text-white/40 mb-1">Kategori</p>
                <p class="text-sm font-medium text-white">{{ categoryLabel(props.registration.category) }}</p>
              </div>
              <div>
                <p class="text-xs text-white/40 mb-1">No. HP</p>
                <p class="text-sm font-medium text-white">{{ props.registration.phone }}</p>
              </div>
              <div>
                <p class="text-xs text-white/40 mb-1">Mendaftar</p>
                <p class="text-sm font-medium text-white">{{ props.registration.created_at }}</p>
              </div>
            </div>
          </div>

          <!-- Payment proof -->
          <div class="p-6 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
            <p class="text-xs font-semibold text-white/40 uppercase tracking-wider mb-4">Bukti Pembayaran</p>

            <div v-if="!props.payment" class="text-center py-12">
              <div class="w-12 h-12 mx-auto mb-3 rounded-xl border border-white/10 bg-white/5
                          flex items-center justify-center text-white/20 text-2xl">📎</div>
              <p class="text-sm text-white/30">Belum ada bukti yang diunggah</p>
            </div>

            <div v-else>
              <!-- File info -->
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl border border-violet-500/30 bg-violet-500/10
                              flex items-center justify-center text-lg">
                    {{ props.payment.is_image ? '🖼️' : '📄' }}
                  </div>
                  <div>
                    <p class="text-sm font-medium text-white">{{ props.payment.original_filename }}</p>
                    <p class="text-xs text-white/40">{{ props.payment.file_size }} · Diunggah {{ props.payment.uploaded_at }}</p>
                  </div>
                </div>
                <a :href="`/admin/payment/${props.payment.id}/download`"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium border border-white/10
                          text-white/60 hover:text-white hover:bg-white/5 transition-all duration-200">
                  ↓ Unduh
                </a>
              </div>

              <!-- Image preview -->
              <div v-if="props.payment.is_image"
                   class="rounded-xl border border-white/[0.07] overflow-hidden bg-black/20">
                <img :src="`/admin/payment/${props.payment.id}/view`"
                     :alt="props.payment.original_filename"
                     class="w-full max-h-96 object-contain" />
              </div>
              <div v-else
                   class="flex items-center justify-center p-8 rounded-xl border border-white/[0.07] bg-black/20">
                <a :href="`/admin/payment/${props.payment.id}/view`" target="_blank"
                   class="flex flex-col items-center gap-2 text-white/40 hover:text-white/70 transition-colors">
                  <span class="text-4xl">📄</span>
                  <span class="text-sm">Buka PDF</span>
                </a>
              </div>

              <!-- Previous verification info -->
              <div v-if="props.payment.verified_at"
                   class="mt-4 px-4 py-3 rounded-xl border border-white/[0.07] bg-white/[0.02] text-xs text-white/40">
                Diverifikasi oleh <span class="text-white/60 font-medium">{{ props.payment.verifier_name ?? 'Admin' }}</span>
                pada {{ props.payment.verified_at }}
                <span v-if="props.payment.admin_notes" class="block mt-1">
                  Catatan: <span class="text-white/60">{{ props.payment.admin_notes }}</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Verification actions -->
        <div class="space-y-5">

          <!-- Action card -->
          <div class="p-6 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
            <p class="text-xs font-semibold text-white/40 uppercase tracking-wider mb-5">Aksi Verifikasi</p>

            <!-- Already verified / rejected -->
            <div v-if="props.registration.status === 'verified'"
                 class="text-center py-4">
              <div class="w-10 h-10 mx-auto mb-3 rounded-full bg-green-500/20 border border-green-500/30
                          flex items-center justify-center">
                <svg class="w-5 h-5 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              </div>
              <p class="text-sm font-semibold text-green-400">Sudah Diverifikasi</p>
              <p class="text-xs text-white/40 mt-1">{{ props.payment?.verified_at }}</p>
            </div>

            <div v-else-if="props.registration.status === 'rejected'"
                 class="text-center py-4">
              <div class="w-10 h-10 mx-auto mb-3 rounded-full bg-red-500/20 border border-red-500/30
                          flex items-center justify-center">
                <svg class="w-5 h-5 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round"/>
                </svg>
              </div>
              <p class="text-sm font-semibold text-red-400">Sudah Ditolak</p>
              <p class="text-xs text-white/40 mt-1">{{ props.payment?.verified_at }}</p>
              <p v-if="props.payment?.admin_notes" class="text-xs text-red-300/70 mt-2 text-left px-2">
                "{{ props.payment.admin_notes }}"
              </p>
            </div>

            <!-- No payment yet -->
            <div v-else-if="!props.payment"
                 class="text-center py-6 text-white/30 text-sm">
              Tunggu peserta mengunggah bukti pembayaran.
            </div>

            <!-- Pending verification — show approve/reject -->
            <div v-else>
              <!-- Approve -->
              <div class="mb-4">
                <label class="block text-xs font-medium text-white/50 mb-2">
                  Catatan (opsional)
                </label>
                <textarea v-model="approveNotes" rows="2"
                          placeholder="Catatan untuk peserta (opsional)..."
                          class="w-full px-3 py-2 rounded-xl border border-white/10 bg-white/5
                                 text-sm text-white placeholder-white/20 resize-none
                                 focus:outline-none focus:border-green-500/50 transition-all duration-200"></textarea>
              </div>
              <button @click="approve" :disabled="processing"
                      class="w-full py-3 rounded-xl text-sm font-semibold mb-3
                             flex items-center justify-center gap-2 transition-all duration-200"
                      :class="processing
                        ? 'bg-green-600/50 text-white/50 cursor-not-allowed'
                        : 'bg-green-600 hover:bg-green-500 text-white shadow-lg shadow-green-600/25 active:scale-[0.98]'">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                {{ processing ? 'Memproses...' : 'Approve Pembayaran' }}
              </button>

              <!-- Divider -->
              <div class="flex items-center gap-3 my-4">
                <div class="flex-1 h-px bg-white/10"></div>
                <span class="text-xs text-white/30">atau</span>
                <div class="flex-1 h-px bg-white/10"></div>
              </div>

              <!-- Reject toggle -->
              <button v-if="!showRejectForm" @click="showRejectForm = true"
                      class="w-full py-2.5 rounded-xl text-sm font-medium
                             border border-red-500/30 text-red-400 hover:bg-red-500/10
                             transition-all duration-200">
                Tolak Pembayaran
              </button>

              <!-- Reject form -->
              <div v-else class="space-y-3">
                <div>
                  <label class="block text-xs font-medium text-red-400/70 mb-1.5">
                    Alasan Penolakan <span class="text-red-400">*</span>
                  </label>
                  <textarea v-model="rejectNotes" rows="3"
                            placeholder="Tuliskan alasan penolakan dengan jelas..."
                            class="w-full px-3 py-2 rounded-xl border text-sm text-white
                                   placeholder-white/20 resize-none bg-white/5
                                   focus:outline-none transition-all duration-200"
                            :class="rejectError ? 'border-red-500/50' : 'border-red-500/30 focus:border-red-500/60'">
                  </textarea>
                  <p v-if="rejectError" class="mt-1 text-xs text-red-400">{{ rejectError }}</p>
                </div>
                <div class="flex gap-2">
                  <button @click="reject" :disabled="processing"
                          class="flex-1 py-2.5 rounded-xl text-sm font-semibold
                                 bg-red-600 hover:bg-red-500 text-white
                                 shadow-lg shadow-red-600/25 transition-all duration-200
                                 disabled:opacity-60 disabled:cursor-not-allowed">
                    {{ processing ? 'Memproses...' : 'Konfirmasi Tolak' }}
                  </button>
                  <button @click="showRejectForm = false"
                          class="px-3 py-2.5 rounded-xl text-sm border border-white/10
                                 text-white/40 hover:text-white hover:bg-white/5 transition-all duration-200">
                    Batal
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick info -->
          <div class="p-5 rounded-2xl border border-white/[0.07] bg-white/[0.02]">
            <p class="text-xs font-semibold text-white/30 uppercase tracking-wider mb-3">Info Cepat</p>
            <div class="space-y-2 text-xs text-white/50">
              <div class="flex justify-between">
                <span>Lomba</span>
                <span class="text-white/80 font-medium">Web Development</span>
              </div>
              <div class="flex justify-between">
                <span>Anggota</span>
                <span class="text-white/80">{{ props.registration.member_count }} orang</span>
              </div>
              <div class="flex justify-between">
                <span>Biaya</span>
                <span class="text-amber-300 font-semibold">Rp 75.000</span>
              </div>
              <div class="flex justify-between">
                <span>Kategori</span>
                <span class="text-white/80">{{ categoryLabel(props.registration.category) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
</style>
