<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps<{
  user: { id: number; name: string; email: string }
  registration: {
    id: number; team_name: string; competition: string
    member_count: number; institution: string; phone: string
    category: string; status: string; status_label: string
    status_color: string; created_at: string; is_finalist?: boolean
  } | null
  payment: {
    id: number; original_filename: string; file_size: string
    status: string; uploaded_at: string | null
    verified_at: string | null; admin_notes: string | null
    is_image: boolean
  } | null
  submission?: {
    id: number; github_url: string | null; drive_url: string | null
    project_title: string | null; description: string | null
    submitted_at: string
  } | null
  finalists: Array<{
    id: number; team_name: string; institution: string
    category: string; competition: string; project_title: string
  }>
}>()

const page = usePage<{ flash: { type?: string; message?: string } }>()

// ── State ─────────────────────────────────────────────────────────────────────
const pageVisible    = ref(false)
const toastVisible   = ref(false)
const toastMsg       = ref('')
const toastType      = ref<'success' | 'error'>('success')
const dragOver       = ref(false)
const selectedFile   = ref<File | null>(null)
const uploading      = ref(false)
const uploadProgress = ref(0)

// Submit Karya form state
const submitForm = ref({
  github_url:    props.submission?.github_url    ?? '',
  drive_url:     props.submission?.drive_url     ?? '',
  project_title: props.submission?.project_title ?? '',
  description:   props.submission?.description   ?? '',
  loading: false,
})

// ── Computed: current step ─────────────────────────────────────────────────────
// Steps: 1=Bayar, 2=Verifikasi, 3=Data Tim, 4=Submit Karya, 5=Selesai
const currentStep = computed(() => {
  if (!props.registration) return 1
  const status = props.registration.status
  if (status === 'pending_payment') return props.payment ? 2 : 1
  if (status === 'pending_verification') return 2
  if (status === 'verified') return props.submission?.project_title ? 5 : 4
  if (status === 'rejected') return 1
  return 1
})

// Active panel: which step content to show (user can also navigate back)
const activePanel = ref(currentStep.value)
// sync when step changes
// (user can click completed steps to review them)

const steps = [
  { id: 1, label: 'Pembayaran',    short: 'Bayar'    },
  { id: 2, label: 'Verifikasi',    short: 'Verifikasi'},
  { id: 3, label: 'Info Tim',      short: 'Tim'      },
  { id: 4, label: 'Submit Karya',  short: 'Submit'   },
  { id: 5, label: 'Selesai',       short: 'Selesai'  },
]

function stepStatus(id: number): 'done' | 'active' | 'locked' {
  if (id < currentStep.value) return 'done'
  if (id === currentStep.value) return 'active'
  return 'locked'
}

function canViewStep(id: number): boolean {
  return id <= currentStep.value
}

function goStep(id: number) {
  if (!canViewStep(id)) return
  activePanel.value = id
}

// ── Rejection note ────────────────────────────────────────────────────────────
const isRejected = computed(() => props.registration?.status === 'rejected')

// ── File upload helpers ────────────────────────────────────────────────────────
function handleDragEnter(e: DragEvent) { e.preventDefault(); dragOver.value = true }
function handleDragLeave(e: DragEvent) { e.preventDefault(); dragOver.value = false }
function handleDrop(e: DragEvent) {
  e.preventDefault(); dragOver.value = false
  const file = e.dataTransfer?.files[0]
  if (file) selectedFile.value = file
}
function handleFileInput(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) selectedFile.value = file
}
function formatFileSize(bytes: number) {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1048576).toFixed(1)} MB`
}

// ── Upload payment ────────────────────────────────────────────────────────────
function startUpload() {
  if (!selectedFile.value || uploading.value) return
  uploading.value = true
  uploadProgress.value = 0

  const interval = setInterval(() => {
    uploadProgress.value += Math.random() * 15 + 5
    if (uploadProgress.value >= 90) { uploadProgress.value = 90; clearInterval(interval) }
  }, 120)

  const formData = new FormData()
  formData.append('proof', selectedFile.value)
  formData.append('_token', (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '')

  router.post('/payment/upload', formData, {
    forceFormData: true,
    onSuccess: () => {
      clearInterval(interval)
      uploadProgress.value = 100
      uploading.value = false
      selectedFile.value = null
      showToast('success', 'Bukti pembayaran berhasil diunggah! Menunggu verifikasi admin.')
    },
    onError: (errors) => {
      clearInterval(interval)
      uploading.value = false
      uploadProgress.value = 0
      showToast('error', Object.values(errors)[0] as string || 'Upload gagal. Coba lagi.')
    },
  })
}

// ── Submit Karya ──────────────────────────────────────────────────────────────
function submitKarya() {
  submitForm.value.loading = true
  router.post('/submission/upload', {
    github_url:    submitForm.value.github_url,
    drive_url:     submitForm.value.drive_url,
    project_title: submitForm.value.project_title,
    description:   submitForm.value.description,
  }, {
    preserveState: true,
    onSuccess: () => {
      submitForm.value.loading = false
      showToast('success', 'Karya berhasil disubmit!')
      activePanel.value = 5
    },
    onError: (err) => {
      submitForm.value.loading = false
      showToast('error', Object.values(err)[0] as string || 'Gagal submit karya')
    }
  })
}

// ── Toast ─────────────────────────────────────────────────────────────────────
function showToast(type: 'success' | 'error', msg: string) {
  toastType.value = type; toastMsg.value = msg; toastVisible.value = true
  setTimeout(() => { toastVisible.value = false }, 5500)
}

function logout() { router.post('/logout') }

function categoryLabel(cat: string): string {
  const m: Record<string, string> = { sma: 'SMA/SMK', mahasiswa: 'Mahasiswa', umum: 'Umum' }
  return m[cat] ?? cat
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  setTimeout(() => { pageVisible.value = true }, 60)
  const flash = page.props.flash
  if (flash?.message) showToast((flash.type as 'success' | 'error') ?? 'success', flash.message)
  activePanel.value = currentStep.value
})
</script>

<template>
  <div class="min-h-screen bg-[#080810] text-white font-['Inter',sans-serif]">

    <!-- ── TOAST ──────────────────────────────────────────────────────────────── -->
    <Transition enter-active-class="transition-all duration-500 ease-out"
                enter-from-class="opacity-0 translate-y-full"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-300 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-full">
      <div v-if="toastVisible" id="toast-notification"
           class="fixed bottom-6 right-6 z-50 w-80 p-4 rounded-2xl shadow-2xl shadow-black/60"
           :class="toastType === 'success' ? 'border border-green-500/20 bg-[#111]' : 'border border-red-500/20 bg-[#111]'">
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
               :class="toastType === 'success' ? 'bg-green-500/15 border border-green-500/30' : 'bg-red-500/15 border border-red-500/30'">
            <svg class="w-4 h-4" :class="toastType === 'success' ? 'text-green-400' : 'text-red-400'"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path v-if="toastType === 'success'" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/>
              <path v-else d="M6 18L18 6M6 6l12 12" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-white">{{ toastType === 'success' ? 'Berhasil!' : 'Gagal' }}</p>
            <p class="text-xs text-white/50 mt-0.5 leading-relaxed">{{ toastMsg }}</p>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ── TOPBAR ──────────────────────────────────────────────────────────────── -->
    <header class="sticky top-0 z-30 border-b border-white/[0.06] bg-[#080810]/95 backdrop-blur-sm">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
        <!-- Brand -->
        <div class="flex items-center">
          <span class="text-sm font-semibold text-white">Soedirman Technophoria <span class="text-white/40">'26</span></span>
        </div>
        <!-- Right: user + logout -->
        <div class="flex items-center gap-3">
          <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/5 border border-white/[0.07]">
            <div class="w-5 h-5 rounded-full bg-violet-500/30 border border-violet-500/40 flex items-center justify-center text-[10px] font-bold text-violet-300">
              {{ props.user.name.charAt(0).toUpperCase() }}
            </div>
            <span class="text-xs text-white/70 font-medium">{{ props.user.name }}</span>
          </div>
          <button @click="logout" id="logout-btn"
                  class="px-3 py-1.5 rounded-xl text-xs text-white/40 hover:text-white border border-white/[0.07]
                         hover:border-white/15 hover:bg-white/5 transition-all duration-200">
            Keluar
          </button>
        </div>
      </div>
    </header>

    <!-- ── MAIN ───────────────────────────────────────────────────────────────── -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

      <!-- ── FINALIST ANNOUNCEMENT VIEW (Visible when finalists count >= 3) ── -->
      <div v-if="props.finalists && props.finalists.length >= 3" class="space-y-8">
        <!-- Banner card -->
        <div class="relative overflow-hidden rounded-3xl border p-6 sm:p-8 md:p-10 backdrop-blur-xl"
             :class="props.registration?.is_finalist 
               ? 'border-green-500/20 bg-green-950/[0.04] shadow-green-500/[0.03]' 
               : 'border-white/[0.07] bg-white/[0.02] shadow-2xl'">
          
          <!-- Background glow -->
          <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 rounded-full blur-3xl"
                 :class="props.registration?.is_finalist ? 'w-[500px] h-[250px] bg-green-500/5' : 'w-[400px] h-[200px] bg-violet-600/5'"></div>
          </div>

          <div class="relative z-10 text-center max-w-xl mx-auto">
            <!-- Icon -->
            <div class="w-16 h-16 mx-auto mb-6 rounded-2xl flex items-center justify-center border shadow-lg"
                 :class="props.registration?.is_finalist 
                   ? 'bg-green-500/10 border-green-500/30 text-green-400' 
                   : 'bg-white/5 border-white/10 text-white/35'">
              <!-- Trophy Icon -->
              <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6 9H4.5a2.5 2.5 0 010-5H6M18 9h1.5a2.5 2.5 0 000-5H18M4 22h16M10 14.66V17c0 .55-.45 1-1 1H4v2h16v-2h-5c-.55 0-1-.45-1-1v-2.34M12 2a6 6 0 016 6v5a6 6 0 01-6 6 6 6 0 01-6-6V8a6 6 0 016-6z" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>

            <!-- Title & subtitle -->
            <div v-if="props.registration?.is_finalist">
              <h2 class="text-2xl sm:text-3xl font-extrabold text-green-400 tracking-tight mb-3">
                Selamat! Tim Anda Lolos ke Final
              </h2>
              <p class="text-sm sm:text-base text-green-300/60 leading-relaxed mb-6">
                Kerja keras tim <strong class="text-green-300">{{ props.registration.team_name }}</strong> membuahkan hasil. Karya Anda terpilih menjadi salah satu dari 3 karya terbaik yang melaju ke babak final Soedirman Technophoria 2026!
              </p>
            </div>
            <div v-else>
              <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-3">
                Pengumuman Finalis
              </h2>
              <p class="text-sm sm:text-base text-white/45 leading-relaxed mb-6">
                Kompetisi babak penyisihan telah resmi berakhir. Kami sangat mengapresiasi karya-karya luar biasa yang telah dikirimkan oleh seluruh peserta. Berikut adalah 3 tim terbaik yang berhasil lolos ke babak final Soedirman Technophoria 2026.
              </p>
            </div>
          </div>
        </div>

        <!-- Finalists List Section -->
        <div>
          <h3 class="text-lg font-bold text-white mb-4 text-center">Daftar Tim Finalis</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div v-for="finalist in props.finalists" :key="finalist.id"
                 class="relative rounded-2xl border p-5 bg-white/[0.02] flex flex-col justify-between transition-all duration-300"
                 :class="props.registration && finalist.id === props.registration.id
                   ? 'border-green-500/35 bg-green-950/[0.03] shadow-lg shadow-green-500/[0.02]' 
                   : 'border-white/[0.07]'">
              
              <!-- Badge -->
              <div class="absolute top-4 right-4 flex items-center gap-1.5">
                <span v-if="props.registration && finalist.id === props.registration.id" 
                      class="px-2 py-0.5 rounded-full border border-green-500/30 bg-green-500/10 text-green-400 text-[9px] font-bold uppercase tracking-wider">
                  Tim Anda
                </span>
                <span class="px-2 py-0.5 rounded-full border border-violet-500/30 bg-violet-500/10 text-violet-400 text-[9px] font-bold uppercase tracking-wider">
                  Finalis
                </span>
              </div>

              <div class="mt-2">
                <p class="text-xs text-white/40 font-medium tracking-wide uppercase">{{ categoryLabel(finalist.category) }}</p>
                <h4 class="text-base font-bold text-white mt-1 leading-tight">{{ finalist.team_name }}</h4>
                <p class="text-xs text-white/50 mt-1">{{ finalist.institution }}</p>
                
                <div class="mt-4 p-3 rounded-lg bg-white/[0.03] border border-white/[0.05]">
                  <p class="text-[10px] text-white/35 font-semibold uppercase tracking-wider">Project</p>
                  <p class="text-xs font-semibold text-white/80 mt-0.5 truncate">{{ finalist.project_title }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="!props.registration?.is_finalist" class="p-5 rounded-2xl border border-white/[0.05] bg-white/[0.01] text-center">
          <p class="text-xs text-white/30">
            Tetap semangat! Perjalanan sebagai developer baru saja dimulai. Sampai jumpa di event Soedirman Technophoria tahun depan.
          </p>
        </div>
      </div>

      <!-- ── REGULAR VIEW (Visible when finalists count < 3) ── -->
      <div v-else>
        <!-- Registration info card -->
      <div v-if="props.registration" class="mb-8 p-4 sm:p-5 rounded-2xl border border-white/[0.07] bg-white/[0.02] flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1">
          <p class="text-xs text-white/40 mb-0.5">Tim Terdaftar</p>
          <p class="text-base font-bold text-white">{{ props.registration.team_name }}</p>
          <p class="text-xs text-white/40 mt-0.5">{{ props.registration.institution }} · {{ props.registration.member_count }} anggota</p>
        </div>
        <!-- Rejected badge -->
        <div v-if="isRejected" class="px-3 py-2 rounded-xl border border-red-500/30 bg-red-500/10 text-center">
          <p class="text-xs font-bold text-red-400">Pembayaran Ditolak</p>
          <p class="text-[10px] text-red-400/70 mt-0.5">Silakan upload ulang bukti bayar</p>
        </div>
        <!-- Status badge -->
        <div v-else :class="['px-4 py-2 rounded-xl border text-center', {
          'border-amber-500/30 bg-amber-500/10':  props.registration.status === 'pending_payment',
          'border-violet-500/30 bg-violet-500/10': props.registration.status === 'pending_verification',
          'border-green-500/30 bg-green-500/10':  props.registration.status === 'verified',
        }]">
          <p :class="['text-xs font-bold', {
            'text-amber-400':  props.registration.status === 'pending_payment',
            'text-violet-400': props.registration.status === 'pending_verification',
            'text-green-400':  props.registration.status === 'verified',
          }]">{{ props.registration.status_label }}</p>
        </div>
      </div>

      <!-- No registration -->
      <div v-if="!props.registration" class="text-center py-20">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center">
          <svg class="w-7 h-7 text-white/20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h2 class="text-lg font-bold text-white mb-2">Belum Terdaftar</h2>
        <p class="text-sm text-white/40 mb-6">Kamu belum mendaftarkan tim. Daftar sekarang!</p>
        <a href="/" class="inline-block px-6 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-sm font-semibold text-white transition-all">
          Daftar Tim →
        </a>
      </div>

      <!-- ── STEPPER ──────────────────────────────────────────────────────────── -->
      <div v-if="props.registration" class="mb-8">
        <!-- Circle row with connecting line -->
        <div class="relative flex items-center justify-between mb-2">
          <!-- Background line (full width, at exact center of circles) -->
          <div class="absolute left-[18px] right-[18px] top-[18px] -translate-y-1/2 h-0.5 bg-white/10 z-0"></div>
          <!-- Progress fill -->
          <div class="absolute left-[18px] top-[18px] -translate-y-1/2 h-0.5 bg-gradient-to-r from-violet-500 to-indigo-500 transition-all duration-700 z-0"
               :style="{ width: `calc(${((currentStep - 1) / 4) * 100}% * (100% - 36px) / 100%)` }"></div>

          <!-- Each step circle only -->
          <button v-for="step in steps" :key="step.id"
                  @click="goStep(step.id)"
                  :disabled="!canViewStep(step.id)"
                  :id="`step-btn-${step.id}`"
                  class="relative z-10 flex-shrink-0">
            <div :class="['w-9 h-9 rounded-full border-2 flex items-center justify-center transition-all duration-300 text-sm font-bold',
                          stepStatus(step.id) === 'done'   ? 'bg-violet-600 border-violet-500 text-white shadow-lg shadow-violet-500/30 cursor-pointer'
                        : stepStatus(step.id) === 'active' ? 'bg-white border-white text-[#080810] shadow-lg shadow-white/20 scale-110'
                        :                                    'bg-[#080810] border-white/15 text-white/25 cursor-not-allowed']">
              <svg v-if="stepStatus(step.id) === 'done'" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              <span v-else>{{ step.id }}</span>
            </div>
          </button>
        </div>

        <!-- Labels row (separate, below circles) -->
        <div class="flex items-start justify-between">
          <span v-for="step in steps" :key="step.id"
                :class="['text-[10px] sm:text-xs font-semibold whitespace-nowrap text-center w-9 transition-colors',
                         stepStatus(step.id) === 'done'   ? 'text-violet-400'
                       : stepStatus(step.id) === 'active' ? 'text-white'
                       :                                    'text-white/25']">
            <span class="hidden sm:inline">{{ step.label }}</span>
            <span class="sm:hidden">{{ step.short }}</span>
          </span>
        </div>
      </div>

      <!-- ── STEP PANELS ──────────────────────────────────────────────────────── -->
      <Transition enter-active-class="transition-all duration-400 ease-out"
                  enter-from-class="opacity-0 translate-y-3"
                  enter-to-class="opacity-100 translate-y-0"
                  mode="out-in">

        <!-- ═══ STEP 1: PEMBAYARAN ════════════════════════════════════════════ -->
        <div v-if="activePanel === 1" key="step1">

          <!-- Rejection alert -->
          <div v-if="isRejected && props.payment?.admin_notes" class="mb-5 p-4 rounded-2xl border border-red-500/30 bg-red-500/10">
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <div>
                <p class="text-sm font-bold text-red-400 mb-1">Pembayaran Ditolak oleh Admin</p>
                <p class="text-sm text-red-300/80">{{ props.payment?.admin_notes }}</p>
                <p class="text-xs text-red-400/60 mt-2">Silakan upload ulang bukti pembayaran yang benar.</p>
              </div>
            </div>
          </div>

          <!-- Info: Tujuan transfer -->
          <div class="mb-5 p-5 rounded-2xl border border-amber-500/20 bg-amber-500/5">
            <p class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-3">💳 Info Pembayaran</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <div>
                <p class="text-[10px] text-white/40 mb-0.5">Bank</p>
                <p class="text-sm font-bold text-white">BCA</p>
              </div>
              <div>
                <p class="text-[10px] text-white/40 mb-0.5">No. Rekening</p>
                <p class="text-sm font-bold text-white">1234 5678 90</p>
              </div>
              <div>
                <p class="text-[10px] text-white/40 mb-0.5">Atas Nama</p>
                <p class="text-sm font-bold text-white">Panitia S-Tech</p>
              </div>
              <div>
                <p class="text-[10px] text-white/40 mb-0.5">Nominal</p>
                <p class="text-lg font-extrabold text-amber-400">Rp 75.000</p>
              </div>
            </div>
            <p class="text-xs text-white/40 mt-3 pt-3 border-t border-white/[0.06]">
              ⚠️ Transfer tepat <strong class="text-white/60">Rp 75.000</strong> (tidak kurang/lebih) agar verifikasi lebih mudah.
            </p>
          </div>

          <!-- Sudah upload sebelumnya -->
          <div v-if="props.payment && !isRejected" class="mb-5 p-4 rounded-2xl border border-violet-500/20 bg-violet-500/5">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl border border-violet-500/30 bg-violet-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-violet-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white">Bukti pembayaran sudah diupload</p>
                <p class="text-xs text-white/40 truncate mt-0.5">{{ props.payment.original_filename }} · {{ props.payment.file_size }}</p>
              </div>
            </div>
            <div class="mt-3 pt-3 border-t border-white/[0.06] flex items-center justify-between">
              <p class="text-xs text-white/40">Diupload: {{ props.payment.uploaded_at }}</p>
              <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border border-violet-500/30 bg-violet-500/10 text-violet-400">Menunggu Verifikasi</span>
            </div>
          </div>

          <!-- Upload form -->
          <div class="p-5 sm:p-6 rounded-2xl border border-white/[0.07] bg-white/[0.02]">
            <p class="text-sm font-semibold text-white mb-1">
              {{ props.payment ? 'Upload Ulang Bukti Transfer' : 'Upload Bukti Transfer' }}
            </p>
            <p class="text-xs text-white/40 mb-5">Format: JPG, PNG, atau PDF · Maks 5 MB</p>

            <div @dragenter="handleDragEnter" @dragover.prevent @dragleave="handleDragLeave" @drop="handleDrop"
                 id="drop-zone"
                 :class="['relative flex flex-col items-center justify-center gap-3 p-8',
                          'rounded-xl border-2 border-dashed transition-all duration-300 cursor-pointer',
                          dragOver ? 'border-violet-500 bg-violet-500/10 scale-[1.01]'
                          : selectedFile ? 'border-green-500/40 bg-green-500/5'
                          : 'border-white/10 hover:border-white/20 hover:bg-white/[0.03]']"
                 @click="($refs.fileInput as HTMLInputElement)?.click()">
              <input ref="fileInput" type="file" accept="image/*,.pdf" class="hidden" @change="handleFileInput" />
              <Transition mode="out-in" enter-active-class="transition-all duration-200" enter-from-class="opacity-0 scale-90" enter-to-class="opacity-100 scale-100">
                <div v-if="!selectedFile" key="empty" class="flex flex-col items-center gap-2 text-center">
                  <div class="w-12 h-12 rounded-xl border border-white/10 bg-white/5 flex items-center justify-center mb-1">
                    <svg class="w-5 h-5 text-white/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </div>
                  <p class="text-sm font-medium text-white/70">Seret & lepas file di sini</p>
                  <p class="text-xs text-white/30">atau klik untuk memilih file</p>
                </div>
                <div v-else key="selected" class="flex items-center gap-4 w-full px-2">
                  <div class="w-10 h-10 rounded-xl border border-green-500/30 bg-green-500/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ selectedFile.name }}</p>
                    <p class="text-xs text-white/40">{{ formatFileSize(selectedFile.size) }}</p>
                  </div>
                  <button @click.stop="selectedFile = null" class="w-7 h-7 rounded-lg flex items-center justify-center border border-white/10 text-white/40 hover:text-red-400 hover:border-red-500/30 transition-all">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round"/></svg>
                  </button>
                </div>
              </Transition>
            </div>

            <!-- Progress bar -->
            <div v-if="uploading" class="mt-3">
              <div class="flex items-center justify-between mb-1">
                <p class="text-xs text-white/50">Mengupload...</p>
                <p class="text-xs font-semibold text-white">{{ Math.round(uploadProgress) }}%</p>
              </div>
              <div class="h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-violet-500 to-indigo-400 rounded-full transition-all duration-200" :style="{ width: `${uploadProgress}%` }"></div>
              </div>
            </div>

            <button @click="startUpload" id="upload-btn" :disabled="!selectedFile || uploading"
                    :class="['mt-4 w-full py-3 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-2 active:scale-[0.98]',
                             selectedFile && !uploading
                               ? 'bg-violet-600 hover:bg-violet-500 text-white shadow-lg shadow-violet-600/25'
                               : 'bg-white/5 text-white/25 cursor-not-allowed border border-white/8']">
              <svg v-if="!uploading" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
              {{ uploading ? `Mengupload... ${Math.round(uploadProgress)}%` : 'Upload Bukti Pembayaran' }}
            </button>
          </div>

          <!-- Tip -->
          <p class="mt-4 text-xs text-center text-white/25">
            Setelah upload, admin akan memverifikasi dalam 1×24 jam. Kamu akan bisa lanjut ke langkah berikutnya.
          </p>
        </div>

        <!-- ═══ STEP 2: MENUNGGU VERIFIKASI ═══════════════════════════════════ -->
        <div v-else-if="activePanel === 2" key="step2" class="text-center py-8">
          <div class="relative w-20 h-20 mx-auto mb-6">
            <div class="absolute inset-0 rounded-full bg-violet-500/20 animate-ping opacity-40"></div>
            <div class="relative w-20 h-20 rounded-full bg-violet-500/15 border border-violet-500/30 flex items-center justify-center">
              <svg class="w-8 h-8 text-violet-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
          </div>
          <h2 class="text-xl font-bold text-white mb-2">Menunggu Verifikasi Admin</h2>
          <p class="text-sm text-white/50 max-w-sm mx-auto mb-6">
            Bukti pembayaran kamu sedang diperiksa oleh panitia. Proses ini biasanya memakan waktu <strong class="text-white/70">1×24 jam kerja</strong>.
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-xl mx-auto text-left">
            <div class="p-4 rounded-xl border border-white/[0.07] bg-white/[0.02]">
              <p class="text-[10px] text-white/40 mb-1">File</p>
              <p class="text-xs font-semibold text-white truncate">{{ props.payment?.original_filename ?? '-' }}</p>
            </div>
            <div class="p-4 rounded-xl border border-white/[0.07] bg-white/[0.02]">
              <p class="text-[10px] text-white/40 mb-1">Diupload</p>
              <p class="text-xs font-semibold text-white">{{ props.payment?.uploaded_at ?? '-' }}</p>
            </div>
            <div class="p-4 rounded-xl border border-white/[0.07] bg-white/[0.02]">
              <p class="text-[10px] text-white/40 mb-1">Status</p>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border border-violet-500/30 bg-violet-500/10 text-violet-400">Dalam Antrian</span>
            </div>
          </div>
          <p class="mt-6 text-xs text-white/25">Halaman ini akan otomatis menampilkan pembaruan status saat kamu refresh.</p>
        </div>

        <!-- ═══ STEP 3: INFO TIM ═══════════════════════════════════════════════ -->
        <div v-else-if="activePanel === 3" key="step3">
          <div class="mb-5 p-4 rounded-2xl border border-green-500/20 bg-green-500/5">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl border border-green-500/30 bg-green-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </div>
              <div>
                <p class="text-sm font-bold text-green-400">Pembayaran Terverifikasi!</p>
                <p class="text-xs text-green-400/70 mt-0.5">Diverifikasi pada {{ props.payment?.verified_at ?? '-' }}</p>
              </div>
            </div>
          </div>

          <h2 class="text-lg font-bold text-white mb-1">Informasi Tim Kamu</h2>
          <p class="text-sm text-white/40 mb-5">Pastikan data tim sudah benar. Hubungi panitia jika ada yang perlu diubah.</p>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
            <div class="p-4 rounded-xl border border-white/[0.07] bg-white/[0.02]">
              <p class="text-[10px] text-white/40 mb-1">Nama Tim</p>
              <p class="text-sm font-semibold text-white">{{ props.registration?.team_name }}</p>
            </div>
            <div class="p-4 rounded-xl border border-white/[0.07] bg-white/[0.02]">
              <p class="text-[10px] text-white/40 mb-1">Kategori</p>
              <p class="text-sm font-semibold text-white capitalize">{{ props.registration?.category?.replace('sma', 'Pelajar SMA/SMK') }}</p>
            </div>
            <div class="p-4 rounded-xl border border-white/[0.07] bg-white/[0.02]">
              <p class="text-[10px] text-white/40 mb-1">Institusi</p>
              <p class="text-sm font-semibold text-white">{{ props.registration?.institution }}</p>
            </div>
            <div class="p-4 rounded-xl border border-white/[0.07] bg-white/[0.02]">
              <p class="text-[10px] text-white/40 mb-1">Jumlah Anggota</p>
              <p class="text-sm font-semibold text-white">{{ props.registration?.member_count }} Orang</p>
            </div>
            <div class="p-4 rounded-xl border border-white/[0.07] bg-white/[0.02]">
              <p class="text-[10px] text-white/40 mb-1">Ketua Tim</p>
              <p class="text-sm font-semibold text-white">{{ props.user.name }}</p>
              <p class="text-xs text-white/40">{{ props.user.email }}</p>
            </div>
            <div class="p-4 rounded-xl border border-white/[0.07] bg-white/[0.02]">
              <p class="text-[10px] text-white/40 mb-1">No. HP Ketua</p>
              <p class="text-sm font-semibold text-white">{{ props.registration?.phone }}</p>
            </div>
          </div>

          <div class="p-4 rounded-2xl border border-violet-500/20 bg-violet-500/5 mb-5">
            <p class="text-xs text-violet-400 font-semibold mb-1">📌 Langkah Selanjutnya</p>
            <p class="text-sm text-white/60">
              Setelah memastikan data tim, kamu siap untuk <strong class="text-white">mengumpulkan karya</strong> pada step berikutnya. Pastikan karya sudah siap sebelum submit!
            </p>
          </div>

          <button @click="activePanel = 4" id="goto-submit-btn"
                  class="w-full py-3 rounded-xl bg-violet-600 hover:bg-violet-500 text-sm font-semibold text-white transition-all shadow-lg shadow-violet-600/25 flex items-center justify-center gap-2">
            Lanjut ke Submit Karya →
          </button>
        </div>

        <!-- ═══ STEP 4: SUBMIT KARYA ═══════════════════════════════════════════ -->
        <div v-else-if="activePanel === 4" key="step4">
          <!-- Already submitted banner -->
          <div v-if="props.submission" class="mb-5 p-4 rounded-2xl border border-green-500/20 bg-green-500/5 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <div>
              <p class="text-sm font-bold text-green-400">Karya sudah disubmit</p>
              <p class="text-xs text-green-400/70 mt-0.5">Disubmit pada {{ props.submission.submitted_at }} · Kamu bisa memperbarui hingga deadline</p>
            </div>
          </div>

          <h2 class="text-lg font-bold text-white mb-1">{{ props.submission ? 'Update' : 'Submit' }} Karya</h2>
          <p class="text-sm text-white/40 mb-5">Masukkan link repository GitHub dan/atau Google Drive hasil project timmu.</p>

          <!-- Guideline box -->
          <div class="mb-5 p-4 rounded-xl border border-amber-500/20 bg-amber-500/5">
            <p class="text-xs font-bold text-amber-400 mb-2">📋 Panduan Submit Karya</p>
            <ul class="text-xs text-white/50 space-y-1">
              <li>• Pastikan repository GitHub <strong class="text-white/70">sudah public</strong></li>
              <li>• Sertakan file <strong class="text-white/70">README.md</strong> dengan panduan menjalankan project</li>
              <li>• Jika ada video demo, upload ke Drive dan masukkan link di kolom Drive</li>
              <li>• Submit dapat diperbarui <strong class="text-white/70">selama deadline belum berakhir</strong></li>
            </ul>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-white/60 mb-1.5">Judul Project <span class="text-red-400">*</span></label>
              <input v-model="submitForm.project_title" type="text" placeholder="Contoh: Aplikasi Manajemen Perpustakaan"
                     class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-white/5 text-sm text-white placeholder-white/30 focus:border-violet-500/50 outline-none transition-all" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-white/60 mb-1.5">Link GitHub <span class="text-red-400">*</span></label>
              <input v-model="submitForm.github_url" type="url" placeholder="https://github.com/username/nama-repo"
                     class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-white/5 text-sm text-white placeholder-white/30 focus:border-violet-500/50 outline-none transition-all" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-white/60 mb-1.5">Link Google Drive <span class="text-white/30 font-normal">(Opsional — video demo, docs)</span></label>
              <input v-model="submitForm.drive_url" type="url" placeholder="https://drive.google.com/..."
                     class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-white/5 text-sm text-white placeholder-white/30 focus:border-violet-500/50 outline-none transition-all" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-white/60 mb-1.5">Deskripsi Singkat <span class="text-red-400">*</span></label>
              <textarea v-model="submitForm.description" rows="3"
                        placeholder="Jelaskan fitur utama project kamu, teknologi yang digunakan, dan cara kerjanya..."
                        class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-white/5 text-sm text-white placeholder-white/30 focus:border-violet-500/50 outline-none transition-all resize-none"></textarea>
            </div>
            <button @click="submitKarya" :disabled="submitForm.loading" id="submit-karya-btn"
                    class="w-full py-3 rounded-xl text-sm font-semibold bg-violet-600 hover:bg-violet-500 text-white shadow-lg shadow-violet-600/25 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
              <svg v-if="submitForm.loading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
              {{ props.submission ? '💾 Perbarui Karya' : '🚀 Submit Karya' }}
            </button>
          </div>
        </div>

        <!-- ═══ STEP 5: SELESAI ════════════════════════════════════════════════ -->
        <div v-else-if="activePanel === 5" key="step5" class="text-center py-8">
          <div class="relative w-20 h-20 mx-auto mb-6">
            <div class="absolute inset-0 rounded-full bg-green-500/20 animate-ping opacity-30"></div>
            <div class="relative w-20 h-20 rounded-full bg-green-500/15 border border-green-500/30 flex items-center justify-center">
              <svg class="w-9 h-9 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
          </div>
          <h2 class="text-2xl font-bold text-white mb-2">Semua Tahap Selesai! 🎉</h2>
          <p class="text-sm text-white/50 max-w-sm mx-auto mb-8">
            Tim <strong class="text-white">{{ props.registration?.team_name }}</strong> sudah menyelesaikan semua proses pendaftaran. Tunggu pengumuman dari panitia!
          </p>

          <!-- Summary -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-xl mx-auto text-left mb-6">
            <div class="p-4 rounded-xl border border-green-500/20 bg-green-500/5">
              <p class="text-[10px] text-green-400/70 mb-1">✅ Pembayaran</p>
              <p class="text-xs font-semibold text-white">Terverifikasi</p>
            </div>
            <div class="p-4 rounded-xl border border-green-500/20 bg-green-500/5">
              <p class="text-[10px] text-green-400/70 mb-1">✅ Karya Disubmit</p>
              <p class="text-xs font-semibold text-white truncate">{{ props.submission?.project_title }}</p>
            </div>
            <div class="p-4 rounded-xl border border-white/[0.07] bg-white/[0.02]">
              <p class="text-[10px] text-white/40 mb-1">⏳ Pengumuman</p>
              <p class="text-xs font-semibold text-white">Segera diumumkan</p>
            </div>
          </div>

          <button @click="activePanel = 4"
                  class="px-6 py-2.5 rounded-xl border border-white/10 text-sm text-white/50 hover:text-white hover:bg-white/5 transition-all">
            Lihat / Update Karya
          </button>
        </div>

      </Transition>
      </div>
    </main>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
</style>
