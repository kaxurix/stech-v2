<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import {
  CreditCard, AlertTriangle, ClipboardList, Save, Rocket,
  PartyPopper, CheckCircle2, Clock,
} from '@lucide/vue'

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
  teamMembers: Array<{
    position: number; is_leader: boolean; full_name: string
    identity_number: string; institution: string; major: string | null
    batch: string | null; phone: string; email: string | null
  }>
  teamMembersComplete: boolean
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

// ── Biodata Tim form state ──────────────────────────────────────────────────────
interface MemberForm {
  full_name: string; identity_number: string; institution: string
  major: string; batch: string; phone: string; email: string
}
function blankMember(): MemberForm {
  return { full_name: '', identity_number: '', institution: '', major: '', batch: '', phone: '', email: '' }
}
function buildInitialMembers(): MemberForm[] {
  const count = props.registration?.member_count ?? 1
  const existing = props.teamMembers ?? []
  return Array.from({ length: count }, (_, i) => {
    const m = existing.find(x => x.position === i + 1)
    if (m) {
      return {
        full_name: m.full_name, identity_number: m.identity_number, institution: m.institution,
        major: m.major ?? '', batch: m.batch ?? '', phone: m.phone, email: m.email ?? '',
      }
    }
    if (i === 0) {
      // Prefill ketua tim dari data akun & pendaftaran yang sudah ada
      return {
        full_name: props.user.name, identity_number: '', institution: props.registration?.institution ?? '',
        major: '', batch: '', phone: props.registration?.phone ?? '', email: props.user.email,
      }
    }
    return blankMember()
  })
}
const memberForms   = ref<MemberForm[]>(buildInitialMembers())
const teamFormLoading = ref(false)
const teamFormErrors  = ref<Record<string, string>>({})

function copyFromLeader(field: 'institution' | 'major' | 'batch') {
  const leaderValue = memberForms.value[0]?.[field] ?? ''
  memberForms.value.forEach((m, i) => { if (i > 0) m[field] = leaderValue })
}

function submitTeamMembers() {
  teamFormLoading.value = true
  teamFormErrors.value = {}
  router.post('/team-members/upload', { members: memberForms.value }, {
    preserveState: true,
    onSuccess: () => {
      teamFormLoading.value = false
      showToast('success', 'Biodata tim berhasil disimpan.')
      activePanel.value = props.submission?.project_title ? 5 : 4
    },
    onError: (errors) => {
      teamFormLoading.value = false
      teamFormErrors.value = errors
      showToast('error', Object.values(errors)[0] as string || 'Gagal menyimpan biodata tim.')
    },
  })
}

// ── Computed: current step ─────────────────────────────────────────────────────
// Steps: 1=Bayar, 2=Verifikasi, 3=Biodata Tim, 4=Submit Karya, 5=Selesai
const currentStep = computed(() => {
  if (!props.registration) return 1
  const status = props.registration.status
  if (status === 'pending_payment') return props.payment ? 2 : 1
  if (status === 'pending_verification') return 2
  if (status === 'verified') {
    if (!props.teamMembersComplete) return 3
    return props.submission?.project_title ? 5 : 4
  }
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
  { id: 3, label: 'Biodata Tim',   short: 'Tim'      },
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

// ── Kompresi gambar sisi klien sebelum upload ────────────────────────────────
// Foto struk dari kamera HP sering 5-15MB; ini menyusutkannya ke ukuran wajar
// sebelum dikirim, supaya lebih cepat di koneksi lambat & jarang kena limit 5MB server.
const MAX_DIMENSION = 1920
const JPEG_QUALITY   = 0.82

async function compressIfImage(file: File): Promise<File> {
  if (!file.type.startsWith('image/') || file.type === 'image/svg+xml') return file

  try {
    const bitmap = await createImageBitmap(file)
    const scale  = Math.min(1, MAX_DIMENSION / Math.max(bitmap.width, bitmap.height))
    const width  = Math.round(bitmap.width * scale)
    const height = Math.round(bitmap.height * scale)

    const canvas = document.createElement('canvas')
    canvas.width = width
    canvas.height = height
    const ctx = canvas.getContext('2d')
    if (!ctx) return file
    ctx.drawImage(bitmap, 0, 0, width, height)

    const blob = await new Promise<Blob | null>((resolve) =>
      canvas.toBlob(resolve, 'image/jpeg', JPEG_QUALITY)
    )
    if (!blob || blob.size >= file.size) return file

    const newName = file.name.replace(/\.[^.]+$/, '') + '.jpg'
    return new File([blob], newName, { type: 'image/jpeg', lastModified: Date.now() })
  } catch {
    return file
  }
}

// ── Upload payment ────────────────────────────────────────────────────────────
async function startUpload() {
  if (!selectedFile.value || uploading.value) return
  uploading.value = true
  uploadProgress.value = 0

  const fileToUpload = await compressIfImage(selectedFile.value)

  const formData = new FormData()
  formData.append('proof', fileToUpload)
  formData.append('_token', (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '')

  router.post('/payment/upload', formData, {
    forceFormData: true,
    onProgress: (progress) => {
      uploadProgress.value = progress?.percentage ?? 0
    },
    onSuccess: () => {
      uploadProgress.value = 100
      uploading.value = false
      selectedFile.value = null
      showToast('success', 'Bukti pembayaran berhasil diunggah! Menunggu verifikasi admin.')
    },
    onError: (errors) => {
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

// ── Ornamen dekoratif ─────────────────────────────────────────────────────────
// Bintang sebaran — diperbesar & lebih ramai, tapi tetap dijaga di sisi
// kiri/kanan (luar area 12%-88%) supaya tidak menutupi kartu konten yang
// terpusat (max-w-4xl mx-auto).
const dashboardStars = [
  { top: '4%',  left: '5%',  size: 30, rotate: -15, opacity: 0.24, duration: 5.4 },
  { top: '9%',  left: '92%', size: 38, rotate: 24,  opacity: 0.18, duration: 6.6 },
  { top: '19%', left: '1%',  size: 48, rotate: 30,  opacity: 0.16, duration: 5.8 },
  { top: '25%', left: '88%', size: 24, rotate: -22, opacity: 0.26, duration: 5   },
  { top: '33%', left: '6%',  size: 26, rotate: 12,  opacity: 0.2,  duration: 6.2 },
  { top: '41%', left: '93%', size: 36, rotate: -30, opacity: 0.18, duration: 6.8 },
  { top: '52%', left: '1%',  size: 42, rotate: 25,  opacity: 0.22, duration: 5.2 },
  { top: '58%', left: '89%', size: 30, rotate: -12, opacity: 0.2,  duration: 6   },
  { top: '68%', left: '5%',  size: 24, rotate: 18,  opacity: 0.24, duration: 5.6 },
  { top: '74%', left: '94%', size: 50, rotate: -18, opacity: 0.16, duration: 6.4 },
  { top: '83%', left: '2%',  size: 32, rotate: 22,  opacity: 0.2,  duration: 5.4 },
  { top: '90%', left: '90%', size: 26, rotate: -25, opacity: 0.22, duration: 6.2 },
]

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  setTimeout(() => { pageVisible.value = true }, 60)
  const flash = page.props.flash
  if (flash?.message) showToast((flash.type as 'success' | 'error') ?? 'success', flash.message)
  activePanel.value = currentStep.value
})
</script>

<template>
  <div class="relative min-h-screen overflow-hidden bg-gradient-to-b from-blue-600 via-blue-700 to-blue-900 text-gray-900">

    <!-- ══════════════════════════════════════════════
         ORNAMEN & MOTIF BACKGROUND
    ═══════════════════════════════════════════════ -->
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden select-none" aria-hidden="true">
      <!-- Motif grid tipis -->
      <div class="absolute inset-0 opacity-[0.05]"
           style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px),
                                    linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px);
                  background-size: 56px 56px;"></div>

      <!-- Awan kiri/kanan — komposisi asimetris (bukan cermin persis) supaya terasa
           lebih organik, ukuran & posisi divariasikan, tidak menempel tepi layar -->
      <img src="/images/ornaments/awan-kiri.svg" alt=""
           class="absolute top-[16%] left-3 sm:left-6 w-40 sm:w-52 md:w-64 opacity-80
                  animate-cloud-drift [animation-duration:15s] [animation-delay:-8s]" draggable="false" />
      <img src="/images/ornaments/awan-kiri-blur.svg" alt=""
           class="absolute top-[71%] left-6 sm:left-10 w-24 sm:w-32 md:w-40 opacity-55
                  animate-cloud-drift [animation-duration:19s] [animation-delay:-3s]" draggable="false" />
      <img src="/images/ornaments/awan-kanan-blur.svg" alt=""
           class="absolute top-[36%] right-3 sm:right-6 w-28 sm:w-36 md:w-44 opacity-55
                  animate-cloud-drift [animation-duration:16s] [animation-delay:-11s]" draggable="false" />
      <img src="/images/ornaments/awan-kanan.svg" alt=""
           class="absolute top-[80%] right-2 sm:right-8 w-36 sm:w-48 md:w-60 opacity-80
                  animate-cloud-drift [animation-duration:14s] [animation-delay:-6s]" draggable="false" />

      <!-- Bintang sebaran -->
      <img v-for="(s, si) in dashboardStars" :key="si" src="/images/ornaments/bintangpx.svg" alt=""
           class="absolute animate-star-twinkle"
           :style="{ top: s.top, left: s.left, width: s.size + 'px',
                     '--star-opacity': s.opacity, '--star-rotate': s.rotate + 'deg',
                     transform: `rotate(${s.rotate}deg)`, animationDuration: s.duration + 's' }" />
    </div>

    <!-- ══════════════════════════════════════════════
         MAIN CONTENT
    ═══════════════════════════════════════════════ -->
    <div class="relative z-10">

    <!-- ── TOAST ──────────────────────────────────────────────────────────────── -->
    <Transition enter-active-class="transition-all duration-500 ease-out"
                enter-from-class="opacity-0 translate-y-full"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-300 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-full">
      <div v-if="toastVisible" id="toast-notification"
           class="fixed bottom-6 right-6 z-50 w-80 p-4 rounded-2xl border-4 border-white bg-white shadow-2xl">
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
               :class="toastType === 'success' ? 'bg-green-100 border border-green-300' : 'bg-red-100 border border-red-300'">
            <svg class="w-4 h-4" :class="toastType === 'success' ? 'text-green-600' : 'text-red-500'"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path v-if="toastType === 'success'" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/>
              <path v-else d="M6 18L18 6M6 6l12 12" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-blue-900">{{ toastType === 'success' ? 'Berhasil!' : 'Gagal' }}</p>
            <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ toastMsg }}</p>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ── TOPBAR ──────────────────────────────────────────────────────────────── -->
    <header class="sticky top-0 z-30 border-b border-blue-800/40 bg-blue-900/95 backdrop-blur-xl">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
        <!-- Brand -->
        <div class="flex items-center">
          <span class="text-sm font-semibold text-white">Soedirman Technophoria <span class="text-blue-300">'26</span></span>
        </div>
        <!-- Right: user + logout -->
        <div class="flex items-center gap-3">
          <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/10 border border-white/20">
            <div class="w-5 h-5 rounded-full bg-amber-400/25 border border-amber-300/40 flex items-center justify-center text-[10px] font-bold text-amber-200">
              {{ props.user.name.charAt(0).toUpperCase() }}
            </div>
            <span class="text-xs text-blue-100 font-medium">{{ props.user.name }}</span>
          </div>
          <button @click="logout" id="logout-btn"
                  class="px-3 py-1.5 rounded-xl text-xs font-medium border border-blue-400/40
                         text-blue-100 hover:text-white hover:bg-blue-800 hover:border-blue-400 transition-all duration-200">
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
        <div class="relative overflow-hidden rounded-3xl border-4 border-white p-6 sm:p-8 md:p-10 shadow-2xl"
             :class="props.registration?.is_finalist ? 'bg-green-50' : 'bg-white'">

          <!-- Background glow -->
          <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 rounded-full blur-3xl"
                 :class="props.registration?.is_finalist ? 'w-[500px] h-[250px] bg-green-300/20' : 'w-[400px] h-[200px] bg-blue-300/10'"></div>
          </div>

          <div class="relative z-10 text-center max-w-xl mx-auto">
            <!-- Icon -->
            <div class="w-16 h-16 mx-auto mb-6 rounded-2xl flex items-center justify-center border-2 shadow-lg"
                 :class="props.registration?.is_finalist
                   ? 'bg-green-100 border-green-300 text-green-600'
                   : 'bg-blue-50 border-blue-100 text-blue-300'">
              <!-- Trophy Icon -->
              <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6 9H4.5a2.5 2.5 0 010-5H6M18 9h1.5a2.5 2.5 0 000-5H18M4 22h16M10 14.66V17c0 .55-.45 1-1 1H4v2h16v-2h-5c-.55 0-1-.45-1-1v-2.34M12 2a6 6 0 016 6v5a6 6 0 01-6 6 6 6 0 01-6-6V8a6 6 0 016-6z" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>

            <!-- Title & subtitle -->
            <div v-if="props.registration?.is_finalist">
              <h2 class="text-2xl sm:text-3xl font-extrabold text-green-600 tracking-tight mb-3">
                Selamat! Tim Anda Lolos ke Final
              </h2>
              <p class="text-sm sm:text-base text-gray-600 leading-relaxed mb-6">
                Kerja keras tim <strong class="text-green-700">{{ props.registration.team_name }}</strong> membuahkan hasil. Karya Anda terpilih menjadi salah satu dari 3 karya terbaik yang melaju ke babak final Soedirman Technophoria 2026!
              </p>
            </div>
            <div v-else>
              <h2 class="text-2xl sm:text-3xl font-extrabold text-blue-900 tracking-tight mb-3">
                Pengumuman Finalis
              </h2>
              <p class="text-sm sm:text-base text-gray-600 leading-relaxed mb-6">
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
                 class="relative rounded-2xl border-2 p-5 bg-white flex flex-col justify-between transition-all duration-300 shadow-lg"
                 :class="props.registration && finalist.id === props.registration.id
                   ? 'border-green-300 bg-green-50'
                   : 'border-white'">

              <!-- Badge -->
              <div class="absolute top-4 right-4 flex items-center gap-1.5">
                <span v-if="props.registration && finalist.id === props.registration.id"
                      class="px-2 py-0.5 rounded-full border border-green-300 bg-green-100 text-green-700 text-[9px] font-bold uppercase tracking-wider">
                  Tim Anda
                </span>
                <span class="px-2 py-0.5 rounded-full border border-blue-300 bg-blue-50 text-blue-600 text-[9px] font-bold uppercase tracking-wider">
                  Finalis
                </span>
              </div>

              <div class="mt-2">
                <p class="text-xs text-gray-500 font-medium tracking-wide uppercase">{{ categoryLabel(finalist.category) }}</p>
                <h4 class="text-base font-bold text-blue-900 mt-1 leading-tight">{{ finalist.team_name }}</h4>
                <p class="text-xs text-gray-500 mt-1">{{ finalist.institution }}</p>

                <div class="mt-4 p-3 rounded-lg bg-blue-50 border border-blue-100">
                  <p class="text-[10px] text-blue-600 font-semibold uppercase tracking-wider">Project</p>
                  <p class="text-xs font-semibold text-blue-900 mt-0.5 truncate">{{ finalist.project_title }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="!props.registration?.is_finalist" class="p-5 rounded-2xl border border-white/20 bg-white/10 text-center">
          <p class="text-xs text-blue-100">
            Tetap semangat! Perjalanan sebagai developer baru saja dimulai. Sampai jumpa di event Soedirman Technophoria tahun depan.
          </p>
        </div>
      </div>

      <!-- ── REGULAR VIEW (Visible when finalists count < 3) ── -->
      <div v-else>
        <!-- Registration info card -->
      <div v-if="props.registration" class="mb-8 p-4 sm:p-5 rounded-2xl border-4 border-white bg-white shadow-xl flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1">
          <p class="text-xs text-gray-600 mb-0.5">Tim Terdaftar</p>
          <p class="text-base font-bold text-blue-900">{{ props.registration.team_name }}</p>
          <p class="text-xs text-gray-600 mt-0.5">{{ props.registration.institution }} · {{ props.registration.member_count }} anggota</p>
        </div>
        <!-- Rejected badge -->
        <div v-if="isRejected" class="px-3 py-2 rounded-xl border border-red-200 bg-red-50 text-center">
          <p class="text-xs font-bold text-red-700">Pembayaran Ditolak</p>
          <p class="text-[10px] text-red-700 mt-0.5">Silakan upload ulang bukti bayar</p>
        </div>
        <!-- Status badge -->
        <div v-else :class="['px-4 py-2 rounded-xl border text-center', {
          'border-amber-200 bg-amber-50':  props.registration.status === 'pending_payment',
          'border-blue-200 bg-blue-50':    props.registration.status === 'pending_verification',
          'border-green-200 bg-green-50':  props.registration.status === 'verified',
        }]">
          <p :class="['text-xs font-bold', {
            'text-amber-700': props.registration.status === 'pending_payment',
            'text-blue-600':  props.registration.status === 'pending_verification',
            'text-green-700': props.registration.status === 'verified',
          }]">{{ props.registration.status_label }}</p>
        </div>
      </div>

      <!-- No registration -->
      <div v-if="!props.registration" class="text-center py-20">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center">
          <svg class="w-7 h-7 text-white/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h2 class="text-lg font-bold text-white mb-2">Belum Terdaftar</h2>
        <p class="text-sm text-blue-100 mb-6">Kamu belum mendaftarkan tim. Daftar sekarang!</p>
        <a href="/" class="inline-block px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-sm font-semibold text-blue-950 shadow-lg shadow-amber-500/30 transition-all">
          Daftar Tim →
        </a>
      </div>

      <!-- ── STEPPER ──────────────────────────────────────────────────────────── -->
      <div v-if="props.registration" class="mb-8">
        <!-- Circle row with connecting line -->
        <div class="relative flex items-center justify-between mb-2">
          <!-- Background line (full width, at exact center of circles) -->
          <div class="absolute left-[18px] right-[18px] top-[18px] -translate-y-1/2 h-0.5 bg-white/20 z-0"></div>
          <!-- Progress fill -->
          <div class="absolute left-[18px] top-[18px] -translate-y-1/2 h-0.5 bg-gradient-to-r from-amber-400 to-amber-500 transition-all duration-700 z-0"
               :style="{ width: `calc(${((currentStep - 1) / 4) * 100}% * (100% - 36px) / 100%)` }"></div>

          <!-- Each step circle only -->
          <button v-for="step in steps" :key="step.id"
                  @click="goStep(step.id)"
                  :disabled="!canViewStep(step.id)"
                  :id="`step-btn-${step.id}`"
                  class="relative z-10 flex-shrink-0">
            <div :class="['w-9 h-9 rounded-full border-2 flex items-center justify-center transition-all duration-300 text-sm font-bold',
                          stepStatus(step.id) === 'done'   ? 'bg-amber-500 border-amber-400 text-blue-950 shadow-lg shadow-amber-500/30 cursor-pointer'
                        : stepStatus(step.id) === 'active' ? 'bg-white border-white text-blue-900 shadow-lg shadow-white/20 scale-110'
                        :                                    'bg-white/10 border-white/20 text-white/40 cursor-not-allowed']">
              <svg v-if="stepStatus(step.id) === 'done'" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              <span v-else>{{ step.id }}</span>
            </div>
          </button>
        </div>

        <!-- Labels row (separate, below circles) -->
        <div class="flex items-start justify-between">
          <span v-for="step in steps" :key="step.id"
                :class="['text-[10px] sm:text-xs font-semibold whitespace-nowrap text-center w-9 transition-colors',
                         stepStatus(step.id) === 'done'   ? 'text-amber-200'
                       : stepStatus(step.id) === 'active' ? 'text-white'
                       :                                    'text-white/40']">
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
          <div v-if="isRejected && props.payment?.admin_notes" class="mb-5 p-4 rounded-2xl border-2 border-red-200 bg-red-50">
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <div>
                <p class="text-sm font-bold text-red-600 mb-1">Pembayaran Ditolak oleh Admin</p>
                <p class="text-sm text-red-500">{{ props.payment?.admin_notes }}</p>
                <p class="text-xs text-red-400 mt-2">Silakan upload ulang bukti pembayaran yang benar.</p>
              </div>
            </div>
          </div>

          <!-- Info: Tujuan transfer -->
          <div class="mb-5 p-5 rounded-2xl border-2 border-amber-200 bg-amber-50">
            <p class="text-xs font-bold text-amber-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
              <CreditCard :size="13" /> Info Pembayaran
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <div>
                <p class="text-[10px] text-gray-500 mb-0.5">Bank</p>
                <p class="text-sm font-bold text-blue-900">BCA</p>
              </div>
              <div>
                <p class="text-[10px] text-gray-500 mb-0.5">No. Rekening</p>
                <p class="text-sm font-bold text-blue-900">1234 5678 90</p>
              </div>
              <div>
                <p class="text-[10px] text-gray-500 mb-0.5">Atas Nama</p>
                <p class="text-sm font-bold text-blue-900">Panitia S-Tech</p>
              </div>
              <div>
                <p class="text-[10px] text-gray-500 mb-0.5">Nominal</p>
                <p class="text-lg font-extrabold text-amber-700">Rp 75.000</p>
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-3 pt-3 border-t border-amber-200/60 flex items-start gap-1.5">
              <AlertTriangle :size="14" class="flex-shrink-0 mt-0.5 text-amber-500" />
              <span>Transfer tepat <strong class="text-gray-700">Rp 75.000</strong> (tidak kurang/lebih) agar verifikasi lebih mudah.</span>
            </p>
          </div>

          <!-- Sudah upload sebelumnya -->
          <div v-if="props.payment && !isRejected" class="mb-5 p-4 rounded-2xl border-2 border-blue-200 bg-blue-50">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl border border-blue-300 bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-blue-900">Bukti pembayaran sudah diupload</p>
                <p class="text-xs text-gray-500 truncate mt-0.5">{{ props.payment.original_filename }} · {{ props.payment.file_size }}</p>
              </div>
            </div>
            <div class="mt-3 pt-3 border-t border-blue-200/60 flex items-center justify-between">
              <p class="text-xs text-gray-500">Diupload: {{ props.payment.uploaded_at }}</p>
              <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border border-blue-300 bg-blue-100 text-blue-700">Menunggu Verifikasi</span>
            </div>
          </div>

          <!-- Upload form -->
          <div class="p-5 sm:p-6 rounded-3xl border-4 border-white bg-white shadow-2xl">
            <p class="text-sm font-semibold text-blue-900 mb-1">
              {{ props.payment ? 'Upload Ulang Bukti Transfer' : 'Upload Bukti Transfer' }}
            </p>
            <p class="text-xs text-gray-500 mb-5">Format: JPG, PNG, atau PDF · Maks 5 MB</p>

            <div @dragenter="handleDragEnter" @dragover.prevent @dragleave="handleDragLeave" @drop="handleDrop"
                 id="drop-zone"
                 :class="['relative flex flex-col items-center justify-center gap-3 p-8',
                          'rounded-xl border-2 border-dashed transition-all duration-300 cursor-pointer',
                          dragOver ? 'border-amber-400 bg-amber-50 scale-[1.01]'
                          : selectedFile ? 'border-green-400 bg-green-50'
                          : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50']"
                 @click="($refs.fileInput as HTMLInputElement)?.click()">
              <input ref="fileInput" type="file" accept="image/*,.pdf" class="hidden" @change="handleFileInput" />
              <Transition mode="out-in" enter-active-class="transition-all duration-200" enter-from-class="opacity-0 scale-90" enter-to-class="opacity-100 scale-100">
                <div v-if="!selectedFile" key="empty" class="flex flex-col items-center gap-2 text-center">
                  <div class="w-12 h-12 rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-center mb-1">
                    <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </div>
                  <p class="text-sm font-medium text-gray-600">Seret & lepas file di sini</p>
                  <p class="text-xs text-gray-500">atau klik untuk memilih file</p>
                </div>
                <div v-else key="selected" class="flex items-center gap-4 w-full px-2">
                  <div class="w-10 h-10 rounded-xl border border-green-300 bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ selectedFile.name }}</p>
                    <p class="text-xs text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
                  </div>
                  <button @click.stop="selectedFile = null" class="w-7 h-7 rounded-lg flex items-center justify-center border border-gray-200 text-gray-500 hover:text-red-500 hover:border-red-300 transition-all">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round"/></svg>
                  </button>
                </div>
              </Transition>
            </div>

            <!-- Progress bar -->
            <div v-if="uploading" class="mt-3">
              <div class="flex items-center justify-between mb-1">
                <p class="text-xs text-gray-500">Mengupload...</p>
                <p class="text-xs font-semibold text-blue-900">{{ Math.round(uploadProgress) }}%</p>
              </div>
              <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 rounded-full transition-all duration-200" :style="{ width: `${uploadProgress}%` }"></div>
              </div>
            </div>

            <button @click="startUpload" id="upload-btn" :disabled="!selectedFile || uploading"
                    :class="['mt-4 w-full py-3 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-2 active:scale-[0.98]',
                             selectedFile && !uploading
                               ? 'bg-amber-500 hover:bg-amber-400 text-blue-950 shadow-lg shadow-amber-500/30'
                               : 'bg-gray-100 text-gray-300 cursor-not-allowed border border-gray-200']">
              <svg v-if="!uploading" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
              {{ uploading ? `Mengupload... ${Math.round(uploadProgress)}%` : 'Upload Bukti Pembayaran' }}
            </button>
          </div>

          <!-- Tip -->
          <p class="mt-4 text-xs text-center text-blue-100">
            Setelah upload, admin akan memverifikasi dalam 1×24 jam. Kamu akan bisa lanjut ke langkah berikutnya.
          </p>
        </div>

        <!-- ═══ STEP 2: MENUNGGU VERIFIKASI ═══════════════════════════════════ -->
        <div v-else-if="activePanel === 2" key="step2" class="rounded-3xl border-4 border-white bg-white shadow-2xl p-8 text-center">
          <div class="relative w-20 h-20 mx-auto mb-6">
            <div class="absolute inset-0 rounded-full bg-blue-200 animate-ping opacity-40"></div>
            <div class="relative w-20 h-20 rounded-full bg-blue-100 border border-blue-300 flex items-center justify-center">
              <svg class="w-8 h-8 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
          </div>
          <h2 class="text-xl font-bold text-blue-900 mb-2">Menunggu Verifikasi Admin</h2>
          <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6">
            Bukti pembayaran kamu sedang diperiksa oleh panitia. Proses ini biasanya memakan waktu <strong class="text-gray-700">1×24 jam kerja</strong>.
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-xl mx-auto text-left">
            <div class="p-4 rounded-xl border-2 border-blue-100 bg-blue-50">
              <p class="text-[10px] text-blue-600 font-semibold mb-1">File</p>
              <p class="text-xs font-semibold text-blue-900 truncate">{{ props.payment?.original_filename ?? '-' }}</p>
            </div>
            <div class="p-4 rounded-xl border-2 border-blue-100 bg-blue-50">
              <p class="text-[10px] text-blue-600 font-semibold mb-1">Diupload</p>
              <p class="text-xs font-semibold text-blue-900">{{ props.payment?.uploaded_at ?? '-' }}</p>
            </div>
            <div class="p-4 rounded-xl border-2 border-blue-100 bg-blue-50">
              <p class="text-[10px] text-blue-600 font-semibold mb-1">Status</p>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border border-blue-300 bg-blue-100 text-blue-700">Dalam Antrian</span>
            </div>
          </div>
          <p class="mt-6 text-xs text-gray-500">Halaman ini akan otomatis menampilkan pembaruan status saat kamu refresh.</p>
        </div>

        <!-- ═══ STEP 3: BIODATA TIM ═══════════════════════════════════════════ -->
        <div v-else-if="activePanel === 3" key="step3">
          <div class="mb-5 p-4 rounded-2xl border-2 border-green-200 bg-green-50">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl border border-green-300 bg-green-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </div>
              <div>
                <p class="text-sm font-bold text-green-700">Pembayaran Terverifikasi!</p>
                <p class="text-xs text-green-600 mt-0.5">Diverifikasi pada {{ props.payment?.verified_at ?? '-' }}</p>
              </div>
            </div>
          </div>

          <div class="rounded-3xl border-4 border-white bg-white shadow-2xl p-6 sm:p-8">
            <h2 class="text-lg font-bold text-blue-900 mb-1">Biodata Anggota Tim</h2>
            <p class="text-sm text-gray-500 mb-5">
              Lengkapi data {{ props.registration?.member_count }} anggota tim <strong class="text-blue-900">{{ props.registration?.team_name }}</strong> sebelum lanjut submit karya.
            </p>

            <div class="space-y-5">
              <div v-for="(member, idx) in memberForms" :key="idx"
                   class="p-4 sm:p-5 rounded-2xl border-2 border-blue-100 bg-blue-50/60">
                <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                  <p class="text-sm font-bold text-blue-900 flex items-center gap-2">
                    Anggota {{ idx + 1 }}
                    <span v-if="idx === 0" class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold">Ketua Tim</span>
                  </p>
                  <button v-if="idx > 0" type="button"
                          @click="copyFromLeader('institution'); copyFromLeader('major'); copyFromLeader('batch')"
                          class="text-[11px] text-blue-500 hover:text-blue-700 underline">
                    Salin institusi/jurusan/angkatan dari Ketua
                  </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Lengkap</label>
                    <input v-model="member.full_name" type="text" placeholder="Nama lengkap"
                           class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-all" />
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">NIM / NISN</label>
                    <input v-model="member.identity_number" type="text" placeholder="Nomor induk"
                           class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-all" />
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nomor WhatsApp</label>
                    <input v-model="member.phone" type="tel" placeholder="08xxxxxxxxxx"
                           class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-all" />
                  </div>
                  <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Asal Sekolah / Kampus</label>
                    <input v-model="member.institution" type="text" placeholder="Nama sekolah atau kampus"
                           class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-all" />
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jurusan/Prodi <span class="text-gray-500 font-normal">(opsional)</span></label>
                    <input v-model="member.major" type="text" placeholder="Contoh: Informatika"
                           class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-all" />
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Angkatan/Kelas <span class="text-gray-500 font-normal">(opsional)</span></label>
                    <input v-model="member.batch" type="text" placeholder="Contoh: 2023 / XII IPA 1"
                           class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-all" />
                  </div>
                  <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email <span class="text-gray-500 font-normal">(opsional)</span></label>
                    <input v-model="member.email" type="email" placeholder="email@contoh.com"
                           class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-all" />
                  </div>
                </div>
              </div>
            </div>

            <p v-if="teamFormErrors.members" class="mt-4 text-xs text-red-500">{{ teamFormErrors.members }}</p>

            <button @click="submitTeamMembers" :disabled="teamFormLoading" id="submit-team-btn"
                    class="mt-5 w-full py-3 rounded-xl text-sm font-semibold text-blue-950 transition-all shadow-lg shadow-amber-500/30
                           bg-amber-500 hover:bg-amber-400 flex items-center justify-center gap-2 active:scale-[0.98] disabled:opacity-60">
              <svg v-if="teamFormLoading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
              {{ teamFormLoading ? 'Menyimpan...' : 'Simpan & Lanjut ke Submit Karya →' }}
            </button>
          </div>
        </div>

        <!-- ═══ STEP 4: SUBMIT KARYA ═══════════════════════════════════════════ -->
        <div v-else-if="activePanel === 4" key="step4">
          <!-- Already submitted banner -->
          <div v-if="props.submission" class="mb-5 p-4 rounded-2xl border-2 border-green-200 bg-green-50 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <div>
              <p class="text-sm font-bold text-green-700">Karya sudah disubmit</p>
              <p class="text-xs text-green-600 mt-0.5">Disubmit pada {{ props.submission.submitted_at }} · Kamu bisa memperbarui hingga deadline</p>
            </div>
          </div>

          <div class="rounded-3xl border-4 border-white bg-white shadow-2xl p-6 sm:p-8">
            <h2 class="text-lg font-bold text-blue-900 mb-1">{{ props.submission ? 'Update' : 'Submit' }} Karya</h2>
            <p class="text-sm text-gray-500 mb-5">Masukkan link repository GitHub dan/atau Google Drive hasil project timmu.</p>

            <!-- Guideline box -->
            <div class="mb-5 p-4 rounded-xl border-2 border-amber-200 bg-amber-50">
              <p class="text-xs font-bold text-amber-700 mb-2 flex items-center gap-1.5">
                <ClipboardList :size="13" /> Panduan Submit Karya
              </p>
              <ul class="text-xs text-gray-600 space-y-1">
                <li>• Pastikan repository GitHub <strong class="text-gray-800">sudah public</strong></li>
                <li>• Sertakan file <strong class="text-gray-800">README.md</strong> dengan panduan menjalankan project</li>
                <li>• Jika ada video demo, upload ke Drive dan masukkan link di kolom Drive</li>
                <li>• Submit dapat diperbarui <strong class="text-gray-800">selama deadline belum berakhir</strong></li>
              </ul>
            </div>

            <div class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Judul Project <span class="text-red-500">*</span></label>
                <input v-model="submitForm.project_title" type="text" placeholder="Contoh: Aplikasi Manajemen Perpustakaan"
                       class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-all" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Link GitHub <span class="text-red-500">*</span></label>
                <input v-model="submitForm.github_url" type="url" placeholder="https://github.com/username/nama-repo"
                       class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-all" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Link Google Drive <span class="text-gray-500 font-normal">(Opsional — video demo, docs)</span></label>
                <input v-model="submitForm.drive_url" type="url" placeholder="https://drive.google.com/..."
                       class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-all" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Deskripsi Singkat <span class="text-red-500">*</span></label>
                <textarea v-model="submitForm.description" rows="3"
                          placeholder="Jelaskan fitur utama project kamu, teknologi yang digunakan, dan cara kerjanya..."
                          class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-all resize-none"></textarea>
              </div>
              <button @click="submitKarya" :disabled="submitForm.loading" id="submit-karya-btn"
                      class="w-full py-3 rounded-xl text-sm font-semibold bg-amber-500 hover:bg-amber-400 text-blue-950 shadow-lg shadow-amber-500/30 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                <svg v-if="submitForm.loading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                <template v-else>
                  <Save v-if="props.submission" :size="15" />
                  <Rocket v-else :size="15" />
                </template>
                {{ props.submission ? 'Perbarui Karya' : 'Submit Karya' }}
              </button>
            </div>
          </div>
        </div>

        <!-- ═══ STEP 5: SELESAI ════════════════════════════════════════════════ -->
        <div v-else-if="activePanel === 5" key="step5" class="rounded-3xl border-4 border-white bg-white shadow-2xl p-8 text-center">
          <div class="relative w-20 h-20 mx-auto mb-6">
            <div class="absolute inset-0 rounded-full bg-green-200 animate-ping opacity-30"></div>
            <div class="relative w-20 h-20 rounded-full bg-green-100 border border-green-300 flex items-center justify-center">
              <svg class="w-9 h-9 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
          </div>
          <h2 class="text-2xl font-bold text-blue-900 mb-2 flex items-center justify-center gap-2">
            Semua Tahap Selesai! <PartyPopper :size="22" class="text-amber-500" />
          </h2>
          <p class="text-sm text-gray-500 max-w-sm mx-auto mb-8">
            Tim <strong class="text-blue-900">{{ props.registration?.team_name }}</strong> sudah menyelesaikan semua proses pendaftaran. Tunggu pengumuman dari panitia!
          </p>

          <!-- Summary -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-xl mx-auto text-left mb-6">
            <div class="p-4 rounded-xl border-2 border-green-200 bg-green-50">
              <p class="text-[10px] text-green-600 font-semibold mb-1 flex items-center gap-1"><CheckCircle2 :size="11" /> Pembayaran</p>
              <p class="text-xs font-semibold text-blue-900">Terverifikasi</p>
            </div>
            <div class="p-4 rounded-xl border-2 border-green-200 bg-green-50">
              <p class="text-[10px] text-green-600 font-semibold mb-1 flex items-center gap-1"><CheckCircle2 :size="11" /> Karya Disubmit</p>
              <p class="text-xs font-semibold text-blue-900 truncate">{{ props.submission?.project_title }}</p>
            </div>
            <div class="p-4 rounded-xl border-2 border-blue-100 bg-blue-50">
              <p class="text-[10px] text-blue-600 font-semibold mb-1 flex items-center gap-1"><Clock :size="11" /> Pengumuman</p>
              <p class="text-xs font-semibold text-blue-900">Segera diumumkan</p>
            </div>
          </div>

          <button @click="activePanel = 4"
                  class="px-6 py-2.5 rounded-xl border-2 border-blue-200 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 hover:border-blue-300 transition-all">
            Lihat / Update Karya
          </button>
        </div>

      </Transition>
      </div>
    </main>
    </div><!-- end main content wrapper -->
  </div>
</template>
