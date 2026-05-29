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
    status_color: string; created_at: string
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
}>()

const page = usePage<{ flash: { type?: string; message?: string } }>()

// ── State ─────────────────────────────────────────────────────────────────────
const sidebarCollapsed = ref(false)
const mobileMenuOpen   = ref(false)
const currentView      = ref('dashboard')
const toastVisible     = ref(false)
const toastMsg         = ref('')
const toastType        = ref<'success' | 'error'>('success')
const dragOver         = ref(false)
const selectedFile     = ref<File | null>(null)
const uploading        = ref(false)
const uploadProgress   = ref(0)
const pageVisible      = ref(false)

// ── Submit Karya State ────────────────────────────────────────────────────────
const submitForm = ref({
  github_url: props.submission?.github_url ?? '',
  drive_url: props.submission?.drive_url ?? '',
  project_title: props.submission?.project_title ?? '',
  description: props.submission?.description ?? '',
  loading: false,
})

function submitKarya() {
  submitForm.value.loading = true
  router.post('/submission/upload', submitForm.value, {
    preserveState: true,
    onSuccess: () => {
      submitForm.value.loading = false
      showToast('success', 'Karya berhasil disubmit!')
    },
    onError: (err) => {
      submitForm.value.loading = false
      showToast('error', Object.values(err)[0] as string || 'Gagal submit karya')
    }
  })
}

// ── Computed status ───────────────────────────────────────────────────────────
const currentStatus = computed(() => {
  if (!props.registration) return 'no_registration'
  return props.registration.status
})

const statusBadge = computed(() => {
  const map: Record<string, { label: string; color: string }> = {
    pending_payment:      { label: 'Menunggu Pembayaran',   color: 'text-amber-400 border-amber-500/30 bg-amber-500/10' },
    pending_verification: { label: 'Menunggu Verifikasi',   color: 'text-violet-400 border-violet-500/30 bg-violet-500/10' },
    verified:             { label: 'Terverifikasi ✓',        color: 'text-green-400 border-green-500/30 bg-green-500/10' },
    rejected:             { label: 'Pembayaran Ditolak',     color: 'text-red-400 border-red-500/30 bg-red-500/10' },
  }
  return map[currentStatus.value] ?? { label: 'Tidak Diketahui', color: 'text-gray-400 border-gray-500/30 bg-gray-500/10' }
})

// ── Bank info ─────────────────────────────────────────────────────────────────
const bankInfo = {
  bank: 'BCA', name: 'Panitia S-Tech 2026',
  account: '1234 5678 90', amount: 'Rp 75.000',
}

// ── Nav items ─────────────────────────────────────────────────────────────────
const navItems = computed(() => {
  const isVerified = currentStatus.value === 'verified'
  return [
    { id: 'dashboard',   label: 'Dashboard',    icon: 'grid',     locked: false,       active: currentView.value === 'dashboard' },
    { id: 'payment',     label: 'Pembayaran',   icon: 'credit',   locked: false,       active: currentView.value === 'payment' },
    { id: 'submit',      label: 'Submit Karya', icon: 'upload',   locked: !isVerified, active: currentView.value === 'submit' },
    { id: 'team',        label: 'Manajemen Tim',icon: 'users',    locked: !isVerified, active: currentView.value === 'team' },
    { id: 'schedule',    label: 'Jadwal Lomba', icon: 'calendar', locked: !isVerified, active: currentView.value === 'schedule' },
    { id: 'certificate', label: 'Sertifikat',   icon: 'award',    locked: !isVerified, active: currentView.value === 'certificate' },
  ]
})

function setView(id: string, locked: boolean) {
  if (locked) return
  currentView.value = id
  mobileMenuOpen.value = false
}

// ── File handlers ─────────────────────────────────────────────────────────────
function handleDragEnter(e: DragEvent) { e.preventDefault(); dragOver.value = true }
function handleDragLeave(e: DragEvent) { e.preventDefault(); dragOver.value = false }
function handleDrop(e: DragEvent) {
  e.preventDefault(); dragOver.value = false
  const file = e.dataTransfer?.files[0]
  if (file) setFile(file)
}
function handleFileInput(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) setFile(file)
}
function setFile(file: File) { selectedFile.value = file }
function formatFileSize(bytes: number) {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1048576).toFixed(1)} MB`
}

// ── Upload ────────────────────────────────────────────────────────────────────
function startUpload() {
  if (!selectedFile.value || uploading.value) return
  uploading.value    = true
  uploadProgress.value = 0

  // Animate progress
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
      const msg = Object.values(errors)[0] as string || 'Upload gagal. Coba lagi.'
      showToast('error', msg)
    },
  })
}

function showToast(type: 'success' | 'error', msg: string) {
  toastType.value    = type
  toastMsg.value     = msg
  toastVisible.value = true
  setTimeout(() => { toastVisible.value = false }, 5000)
}

function logout() {
  router.post('/logout')
}

// ── Icon helper ───────────────────────────────────────────────────────────────
function iconPath(id: string): string {
  const paths: Record<string, string> = {
    grid:     'M3 3h7v7H3V3zm11 0h7v7h-7V3zM3 14h7v7H3v-7zm11 0h7v7h-7v-7z',
    credit:   'M2 5a2 2 0 012-2h16a2 2 0 012 2v14a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm2 3h16v2H4V8zm0 5h6v2H4v-2z',
    upload:   'M16 16l-4-4-4 4m4-4V20M20 7a2 2 0 00-2-2h-1.17A5 5 0 006.17 5H4a2 2 0 00-2 2v11a2 2 0 002 2h16a2 2 0 002-2V7z',
    users:    'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm8 0a3 3 0 11-6 0 3 3 0 016 0m5 10v-2a4 4 0 00-3-3.87',
    calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    award:    'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z',
    chevron:  'M9 18l6-6-6-6',
    logout:   'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
    check:    'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    x:        'M6 18L18 6M6 6l12 12',
    paperclip:'M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13',
    info:     'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  }
  return paths[id] ?? ''
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  setTimeout(() => { pageVisible.value = true }, 60)

  // Show flash from server
  const flash = page.props.flash
  if (flash?.message) {
    showToast((flash.type as 'success' | 'error') ?? 'success', flash.message)
  }
})
</script>

<template>
  <div class="flex h-screen bg-[#0a0a0a] text-white font-['Inter',sans-serif] overflow-hidden">

    <!-- ── TOAST ────────────────────────────────────────────────────────────── -->
    <Transition enter-active-class="transition-all duration-500 ease-out"
                enter-from-class="opacity-0 translate-y-full"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-300 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-full">
      <div v-if="toastVisible" id="toast-notification"
           class="fixed bottom-6 right-6 z-50 w-80 p-4 rounded-2xl
                  shadow-2xl shadow-black/60 backdrop-blur-sm"
           :class="toastType === 'success'
             ? 'border border-green-500/20 bg-[#111]'
             : 'border border-red-500/20 bg-[#111]'">
        <div class="flex items-start gap-3">
          <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
               :class="toastType === 'success' ? 'bg-green-500/15 border border-green-500/30' : 'bg-red-500/15 border border-red-500/30'">
            <svg class="w-4 h-4" :class="toastType === 'success' ? 'text-green-400' : 'text-red-400'"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path v-if="toastType === 'success'" :d="iconPath('check')" stroke-linecap="round" stroke-linejoin="round"/>
              <path v-else :d="iconPath('x')" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-white">{{ toastType === 'success' ? 'Berhasil!' : 'Gagal' }}</p>
            <p class="text-xs text-white/50 mt-0.5 leading-relaxed">{{ toastMsg }}</p>
          </div>
          <button @click="toastVisible = false" id="toast-close-btn"
                  class="flex-shrink-0 w-6 h-6 flex items-center justify-center text-white/30 hover:text-white">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path :d="iconPath('x')" stroke-linecap="round"/>
            </svg>
          </button>
        </div>
        <div class="mt-3 h-px w-full bg-white/10 rounded overflow-hidden">
          <div class="h-full rounded toast-progress-bar"
               :class="toastType === 'success' ? 'bg-green-500/50' : 'bg-red-500/50'"></div>
        </div>
      </div>
    </Transition>

    <!-- ── MOBILE OVERLAY ───────────────────────────────────────────────────── -->
    <Transition enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-300"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="mobileMenuOpen" @click="mobileMenuOpen = false"
           class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm md:hidden"></div>
    </Transition>

    <!-- ── SIDEBAR ──────────────────────────────────────────────────────────── -->
    <aside :class="['fixed md:relative inset-y-0 left-0 z-50 flex flex-col border-r border-white/[0.07] bg-[#0d0d0d]',
                    'transition-all duration-300 ease-in-out',
                    sidebarCollapsed ? 'w-[72px] -translate-x-full md:translate-x-0' : 'w-64',
                    mobileMenuOpen && sidebarCollapsed ? 'translate-x-0 w-64' : '',
                    !mobileMenuOpen && !sidebarCollapsed ? '-translate-x-full md:translate-x-0' : 'translate-x-0']">
      <!-- Brand -->
      <div class="flex items-center h-16 px-4 border-b border-white/[0.07]"
           :class="sidebarCollapsed && !mobileMenuOpen ? 'justify-center' : 'justify-between'">
        <div class="flex items-center gap-2.5">
          <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600
                      flex items-center justify-center text-sm font-bold shadow-lg">S</div>
          <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 -translate-x-2"
                      enter-to-class="opacity-100 translate-x-0" leave-active-class="transition-all duration-200"
                      leave-from-class="opacity-100 translate-x-0" leave-to-class="opacity-0 -translate-x-2">
            <span v-if="!sidebarCollapsed || mobileMenuOpen" class="text-sm font-semibold whitespace-nowrap">
              S-Tech <span class="text-white/40">'26</span>
            </span>
          </Transition>
        </div>
        <button v-if="(!sidebarCollapsed || mobileMenuOpen) && false" @click="sidebarCollapsed = true" id="sidebar-collapse-btn"
                class="hidden md:flex w-6 h-6 rounded-md items-center justify-center text-white/30
                       hover:text-white hover:bg-white/5 transition-all duration-200">
          <svg class="w-3.5 h-3.5 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path :d="iconPath('chevron')" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <button v-if="mobileMenuOpen" @click="mobileMenuOpen = false"
                class="md:hidden w-8 h-8 flex items-center justify-center text-white/50 hover:text-white">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
             <path :d="iconPath('x')" stroke-linecap="round"/>
          </svg>
        </button>
      </div>

      <button v-if="sidebarCollapsed" @click="sidebarCollapsed = false" id="sidebar-expand-btn"
              class="hidden md:flex absolute -right-3 top-20 w-6 h-6 rounded-full border border-white/10
                     bg-[#0d0d0d] items-center justify-center text-white/40
                     hover:text-white transition-all duration-200 shadow-md">
        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path :d="iconPath('chevron')" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

      <!-- Nav -->
      <nav class="flex-1 py-4 px-2 space-y-0.5 overflow-hidden">
        <div v-for="item in navItems" :key="item.id" :id="`nav-${item.id}`"
             @click="setView(item.id, item.locked)"
             :class="['group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200',
                      item.locked ? 'opacity-40 cursor-not-allowed'
                      : item.active ? 'bg-white/8 text-white border border-white/8'
                      : 'text-white/50 hover:text-white hover:bg-white/5 cursor-pointer']">
          <div class="flex-shrink-0 w-5 h-5">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-full h-full">
              <path :d="iconPath(item.icon)" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0"
                      enter-to-class="opacity-100" leave-active-class="transition-all duration-150"
                      leave-from-class="opacity-100" leave-to-class="opacity-0">
            <span v-if="!sidebarCollapsed" class="flex-1 whitespace-nowrap font-medium">{{ item.label }}</span>
          </Transition>
          <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100">
            <div v-if="item.locked && !sidebarCollapsed" class="flex-shrink-0 w-3.5 h-3.5 text-white/30">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-full h-full">
                <rect x="5" y="11" width="14" height="10" rx="2"/>
                <path d="M8 11V7a4 4 0 118 0v4" stroke-linecap="round"/>
              </svg>
            </div>
          </Transition>
          <div v-if="item.active && !item.locked"
               class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 bg-violet-400 rounded-r"></div>
        </div>
      </nav>

      <!-- User -->
      <div class="p-3 border-t border-white/[0.07]">
        <div :class="['flex items-center gap-3 p-2 rounded-xl', sidebarCollapsed ? 'justify-center' : '']">
          <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-violet-500/30 to-indigo-600/30
                      border border-violet-500/20 flex items-center justify-center text-xs font-bold text-violet-300">
            {{ props.user.name.charAt(0).toUpperCase() }}
          </div>
          <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100">
            <div v-if="!sidebarCollapsed" class="flex-1 min-w-0">
              <p class="text-xs font-semibold text-white truncate">{{ props.user.name }}</p>
              <p class="text-[10px] text-white/40 truncate">{{ props.registration?.team_name ?? 'Peserta' }}</p>
            </div>
          </Transition>
        </div>
        <button v-if="!sidebarCollapsed" @click="logout" id="logout-btn"
                class="mt-1 w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs
                       text-white/30 hover:text-white/70 hover:bg-white/5 transition-all duration-200">
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path :d="iconPath('logout')" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Keluar
        </button>
      </div>
    </aside>

    <!-- ── MAIN CONTENT ──────────────────────────────────────────────────────── -->
    <main class="flex-1 overflow-y-auto bg-[#0a0a0a]">
      <!-- Top bar -->
      <header class="sticky top-0 z-20 flex items-center justify-between
                     h-16 px-4 md:px-8 border-b border-white/[0.06] bg-[#0a0a0a]/90 backdrop-blur-sm">
        <div class="flex items-center gap-3">
          <button @click="mobileMenuOpen = true"
                  class="md:hidden w-8 h-8 rounded-lg flex items-center justify-center border border-white/10
                         bg-white/5 text-white/50 hover:text-white transition-all">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/>
            </svg>
          </button>
          <div>
            <h1 class="text-sm font-semibold text-white">
              {{ navItems.find(i => i.id === currentView)?.label ?? 'Dashboard Peserta' }}
            </h1>
            <p class="text-[10px] sm:text-xs text-white/40 mt-0.5 truncate max-w-[150px] sm:max-w-xs">
              {{ props.registration ? `${props.registration.team_name} · ${props.registration.competition}` : 'Selamat datang' }}
            </p>
          </div>
        </div>
        <div :class="['px-3 py-1.5 rounded-full border text-[10px] sm:text-xs font-semibold whitespace-nowrap', statusBadge.color]">
          {{ statusBadge.label }}
        </div>
      </header>

      <Transition enter-active-class="transition-all duration-500 ease-out"
                  enter-from-class="opacity-0 translate-y-4"
                  enter-to-class="opacity-100 translate-y-0"
                  mode="out-in">

        <!-- ── TAB: DASHBOARD / PAYMENT ──────────────────────────────────────── -->
        <div v-if="currentView === 'dashboard' || currentView === 'payment'" key="dashboard" class="w-full">
          <!-- ── STATE: VERIFIED ──────────────────────────────────────────────── -->
          <div v-if="currentStatus === 'verified'" key="verified" class="p-4 sm:p-8 max-w-3xl">
          <div class="mb-6 p-8 rounded-2xl border border-green-500/20 bg-green-500/5 text-center">
            <div class="relative w-16 h-16 mx-auto mb-6">
              <div class="absolute inset-0 rounded-full bg-green-500/20 animate-ping opacity-60"></div>
              <div class="relative w-16 h-16 rounded-full bg-green-500/20 border border-green-500/30
                          flex items-center justify-center">
                <svg class="w-7 h-7 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              </div>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Pembayaran Terverifikasi! 🎉</h2>
            <p class="text-sm text-white/50 max-w-sm mx-auto leading-relaxed">
              Selamat <strong class="text-white">{{ props.user.name }}</strong>! Pendaftaran timmu
              <strong class="text-white">{{ props.registration?.team_name }}</strong> telah resmi dikonfirmasi.
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
              <p class="text-xs text-white/40 mb-1">Tim</p>
              <p class="text-sm font-semibold text-white">{{ props.registration?.team_name }}</p>
              <p class="text-xs text-white/40 mt-1">{{ props.registration?.member_count }} Anggota</p>
            </div>
            <div class="p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
              <p class="text-xs text-white/40 mb-1">Lomba</p>
              <p class="text-sm font-semibold text-white">Web Development Challenge</p>
              <p class="text-xs text-white/40 mt-1">{{ props.registration?.institution }}</p>
            </div>
            <div class="p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
              <p class="text-xs text-white/40 mb-1">Diverifikasi pada</p>
              <p class="text-sm font-semibold text-white">{{ props.payment?.verified_at ?? '-' }}</p>
            </div>
            <div class="p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
              <p class="text-xs text-white/40 mb-1">Status Pendaftaran</p>
              <p class="text-sm font-semibold text-green-400">✓ Aktif</p>
            </div>
          </div>

          <!-- Unlocked features -->
          <div class="p-5 rounded-2xl border border-violet-500/20 bg-violet-500/5">
            <p class="text-sm font-semibold text-white mb-3">Fitur Tersedia</p>
            <div class="grid grid-cols-2 gap-2">
              <div v-for="item in navItems.filter(i => !i.locked && i.id !== 'dashboard')" :key="item.id"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg border border-violet-500/15 bg-violet-500/5">
                <svg class="w-3.5 h-3.5 text-violet-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path :d="iconPath('check')" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="text-xs text-white/70">{{ item.label }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- ── STATE: REJECTED ───────────────────────────────────────────────── -->
        <div v-else-if="currentStatus === 'rejected'" key="rejected" class="p-8 max-w-3xl">
          <div class="mb-6 p-8 rounded-2xl border border-red-500/20 bg-red-500/5 text-center">
            <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-red-500/20 border border-red-500/30
                        flex items-center justify-center">
              <svg class="w-7 h-7 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path :d="iconPath('x')" stroke-linecap="round"/>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Pembayaran Ditolak</h2>
            <p class="text-sm text-white/50 max-w-sm mx-auto leading-relaxed mb-4">
              Maaf, bukti pembayaran kamu tidak dapat diverifikasi.
            </p>
            <div v-if="props.payment?.admin_notes"
                 class="px-4 py-3 rounded-xl border border-red-500/20 bg-red-500/10 text-left">
              <p class="text-xs text-red-400/70 mb-1">Alasan penolakan:</p>
              <p class="text-sm text-red-300">{{ props.payment.admin_notes }}</p>
            </div>
          </div>
          <div class="p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
            <p class="text-sm font-semibold text-white mb-2">Langkah Selanjutnya</p>
            <p class="text-xs text-white/50 leading-relaxed mb-4">
              Upload ulang bukti transfer yang valid di bawah ini.
              Pastikan foto/scan jelas dan menunjukkan nominal yang tepat.
            </p>
            <!-- Re-upload -->
            <div @dragenter="handleDragEnter" @dragover.prevent @dragleave="handleDragLeave" @drop="handleDrop"
                 id="drop-zone-retry"
                 :class="['relative flex flex-col items-center justify-center gap-3 p-8',
                          'rounded-xl border-2 border-dashed transition-all duration-300 cursor-pointer',
                          dragOver ? 'border-violet-500 bg-violet-500/10' : 'border-white/10 hover:border-white/20 hover:bg-white/[0.03]']"
                 @click="($refs.fileInputRetry as HTMLInputElement)?.click()">
              <input ref="fileInputRetry" type="file" accept="image/*,.pdf" class="hidden" @change="handleFileInput" />
              <p class="text-sm text-white/60">{{ selectedFile ? selectedFile.name : 'Klik atau seret file bukti baru' }}</p>
              <p class="text-xs text-white/30">JPG, PNG, PDF · Maks 5 MB</p>
            </div>
            <button @click="startUpload" :disabled="!selectedFile || uploading"
                    class="mt-3 w-full py-3 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-2"
                    :class="selectedFile && !uploading
                      ? 'bg-violet-600 hover:bg-violet-500 text-white shadow-lg shadow-violet-600/25'
                      : 'bg-white/5 text-white/25 cursor-not-allowed border border-white/8'">
              <svg v-if="uploading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ uploading ? `Mengupload... ${Math.round(uploadProgress)}%` : 'Upload Ulang Bukti' }}
            </button>
          </div>
        </div>

        <!-- ── STATE: PENDING VERIFICATION ──────────────────────────────────── -->
        <div v-else-if="currentStatus === 'pending_verification'" key="pending_verify" class="p-8 max-w-3xl">
          <div class="mb-6 p-8 rounded-2xl border border-violet-500/20 bg-violet-500/5 text-center">
            <div class="relative w-16 h-16 mx-auto mb-6">
              <div class="absolute inset-0 rounded-full bg-violet-500/20 animate-pulse"></div>
              <div class="relative w-16 h-16 rounded-full bg-violet-500/20 border border-violet-500/30
                          flex items-center justify-center">
                <svg class="w-7 h-7 text-violet-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Menunggu Verifikasi</h2>
            <p class="text-sm text-white/50 max-w-sm mx-auto leading-relaxed">
              Bukti pembayaranmu sedang diperiksa oleh admin.
              Estimasi <strong class="text-white/80">1×24 jam</strong> hari kerja.
            </p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
              <p class="text-xs text-white/40 mb-2">File yang diunggah</p>
              <p class="text-sm font-medium text-white truncate">{{ props.payment?.original_filename }}</p>
              <p class="text-xs text-white/30 mt-1">{{ props.payment?.file_size }}</p>
            </div>
            <div class="p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
              <p class="text-xs text-white/40 mb-2">Diunggah pada</p>
              <p class="text-sm font-medium text-white">{{ props.payment?.uploaded_at ?? '-' }}</p>
              <p class="text-xs text-white/30 mt-1">Estimasi verifikasi: 1×24 jam</p>
            </div>
          </div>
          <div class="mt-5 p-5 rounded-2xl border border-white/[0.07] bg-white/[0.02]">
            <div class="flex items-start gap-3">
              <svg class="w-4 h-4 text-white/30 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="5" y="11" width="14" height="10" rx="2"/>
                <path d="M8 11V7a4 4 0 118 0v4" stroke-linecap="round"/>
              </svg>
              <div>
                <p class="text-sm font-semibold text-white/80">Fitur masih terkunci</p>
                <p class="text-xs text-white/40 mt-1 leading-relaxed">
                  Submit Karya, Manajemen Tim, Jadwal, dan Sertifikat akan dibuka otomatis setelah verifikasi.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- ── STATE: PENDING PAYMENT ────────────────────────────────────────── -->
        <div v-else key="pending_payment" class="p-8 max-w-3xl">
          <!-- Welcome -->
          <div class="mb-8 p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
            <p class="text-xs text-white/40 mb-1">Halo 👋</p>
            <h2 class="text-xl font-bold text-white">Selamat datang, {{ props.user.name }}!</h2>
            <p class="text-sm text-white/50 mt-1">Selesaikan pembayaran untuk mengaktifkan akunmu.</p>
          </div>

          <!-- Steps -->
          <div class="flex items-center gap-0 mb-8">
            <div class="flex flex-col items-center">
              <div class="w-7 h-7 rounded-full bg-green-500/20 border border-green-500/40 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              </div>
              <p class="text-[10px] text-green-400 mt-1.5 font-medium">Registrasi</p>
            </div>
            <div class="flex-1 h-px bg-white/10 mx-3"></div>
            <div class="flex flex-col items-center">
              <div class="w-7 h-7 rounded-full bg-amber-500/20 border-2 border-amber-500 flex items-center justify-center">
                <span class="text-[10px] font-bold text-amber-400">2</span>
              </div>
              <p class="text-[10px] text-amber-400 mt-1.5 font-medium">Pembayaran</p>
            </div>
            <div class="flex-1 h-px bg-white/10 mx-3"></div>
            <div class="flex flex-col items-center">
              <div class="w-7 h-7 rounded-full border border-white/15 bg-white/5 flex items-center justify-center">
                <span class="text-[10px] font-bold text-white/30">3</span>
              </div>
              <p class="text-[10px] text-white/30 mt-1.5">Verifikasi</p>
            </div>
            <div class="flex-1 h-px bg-white/10 mx-3"></div>
            <div class="flex flex-col items-center">
              <div class="w-7 h-7 rounded-full border border-white/15 bg-white/5 flex items-center justify-center">
                <span class="text-[10px] font-bold text-white/30">4</span>
              </div>
              <p class="text-[10px] text-white/30 mt-1.5">Aktif</p>
            </div>
          </div>

          <!-- Info Tim -->
          <div class="mb-6 p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
            <p class="text-xs font-semibold text-white/50 uppercase tracking-wider mb-3">Data Pendaftaran</p>
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div><p class="text-xs text-white/40">Tim</p><p class="font-medium text-white">{{ props.registration?.team_name }}</p></div>
              <div><p class="text-xs text-white/40">Anggota</p><p class="font-medium text-white">{{ props.registration?.member_count }} orang</p></div>
              <div><p class="text-xs text-white/40">Institusi</p><p class="font-medium text-white">{{ props.registration?.institution }}</p></div>
              <div><p class="text-xs text-white/40">Kategori</p><p class="font-medium text-white capitalize">{{ props.registration?.category }}</p></div>
            </div>
          </div>

          <!-- Bank info -->
          <div class="mb-6 rounded-2xl border border-amber-500/20 bg-amber-500/5 p-6">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></div>
              <p class="text-xs font-semibold text-amber-400 uppercase tracking-wider">Informasi Pembayaran</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-xs text-white/40 mb-1">Bank</p>
                <p class="text-sm font-bold text-white">{{ bankInfo.bank }}</p>
              </div>
              <div>
                <p class="text-xs text-white/40 mb-1">Atas Nama</p>
                <p class="text-sm font-bold text-white">{{ bankInfo.name }}</p>
              </div>
              <div>
                <p class="text-xs text-white/40 mb-1">Nomor Rekening</p>
                <div class="flex items-center gap-2">
                  <p class="text-sm font-bold text-white font-mono tracking-widest">{{ bankInfo.account }}</p>
                  <button id="copy-account-btn"
                          class="text-[10px] px-2 py-0.5 rounded border border-white/10 text-white/40
                                 hover:text-white hover:border-white/20 transition-all duration-200"
                          @click="navigator.clipboard?.writeText(bankInfo.account)">salin</button>
                </div>
              </div>
              <div>
                <p class="text-xs text-white/40 mb-1">Jumlah Transfer</p>
                <p class="text-xl font-extrabold text-amber-300">{{ bankInfo.amount }}</p>
              </div>
            </div>
            <div class="mt-4 pt-4 border-t border-amber-500/10">
              <p class="text-xs text-amber-300/70">⚠ Transfer dengan jumlah yang tepat. Pembayaran berbeda akan memperlambat verifikasi.</p>
            </div>
          </div>

          <!-- Upload area -->
          <div class="rounded-2xl border border-white/[0.07] bg-white/[0.02] p-6">
            <p class="text-sm font-semibold text-white mb-1">Upload Bukti Transfer</p>
            <p class="text-xs text-white/40 mb-5">Format: JPG, PNG, atau PDF · Maks 5 MB</p>

            <!-- Drop zone -->
            <div @dragenter="handleDragEnter" @dragover.prevent @dragleave="handleDragLeave" @drop="handleDrop"
                 id="drop-zone"
                 :class="['relative flex flex-col items-center justify-center gap-3 p-10',
                          'rounded-xl border-2 border-dashed transition-all duration-300 cursor-pointer',
                          dragOver ? 'border-violet-500 bg-violet-500/10 scale-[1.01]'
                          : selectedFile ? 'border-green-500/40 bg-green-500/5'
                          : 'border-white/10 hover:border-white/20 hover:bg-white/[0.03]']"
                 @click="($refs.fileInput as HTMLInputElement)?.click()">
              <input ref="fileInput" type="file" accept="image/*,.pdf" class="hidden" @change="handleFileInput" />
              <Transition mode="out-in" enter-active-class="transition-all duration-300"
                          enter-from-class="opacity-0 scale-90" enter-to-class="opacity-100 scale-100">
                <div v-if="!selectedFile" key="empty" class="flex flex-col items-center gap-2 text-center">
                  <div class="w-12 h-12 rounded-xl border border-white/10 bg-white/5 flex items-center justify-center mb-1">
                    <svg class="w-5 h-5 text-white/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                      <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                            stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                  <p class="text-sm font-medium text-white/70">Seret & lepas file di sini</p>
                  <p class="text-xs text-white/30">atau klik untuk memilih file</p>
                </div>
                <div v-else key="selected" class="flex items-center gap-4 w-full px-4">
                  <div class="w-10 h-10 rounded-xl border border-green-500/30 bg-green-500/10
                              flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                      <path :d="iconPath('paperclip')" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ selectedFile.name }}</p>
                    <p class="text-xs text-white/40">{{ formatFileSize(selectedFile.size) }}</p>
                  </div>
                  <button @click.stop="selectedFile = null" id="remove-file-btn"
                          class="w-7 h-7 rounded-lg flex items-center justify-center
                                 border border-white/10 text-white/40 hover:text-red-400
                                 hover:border-red-500/30 transition-all duration-200">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <path :d="iconPath('x')" stroke-linecap="round"/>
                    </svg>
                  </button>
                </div>
              </Transition>
            </div>

            <!-- Upload progress -->
            <div v-if="uploading" class="mt-3">
              <div class="flex items-center justify-between mb-1">
                <p class="text-xs text-white/50">Mengupload...</p>
                <p class="text-xs font-semibold text-white">{{ Math.round(uploadProgress) }}%</p>
              </div>
              <div class="h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-violet-500 to-indigo-400 rounded-full transition-all duration-200"
                     :style="{ width: `${uploadProgress}%` }"></div>
              </div>
            </div>

            <!-- Upload button -->
            <button @click="startUpload" id="upload-btn" :disabled="!selectedFile || uploading"
                    :class="['mt-4 w-full py-3 rounded-xl text-sm font-semibold transition-all duration-200',
                             'flex items-center justify-center gap-2 active:scale-[0.98]',
                             selectedFile && !uploading
                               ? 'bg-violet-600 hover:bg-violet-500 text-white shadow-lg shadow-violet-600/25'
                               : 'bg-white/5 text-white/25 cursor-not-allowed border border-white/8']">
              <svg v-if="!uploading" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                      stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ uploading ? `Mengupload... ${Math.round(uploadProgress)}%` : 'Upload Bukti Pembayaran' }}
            </button>
          </div>
        </div>
        </div>

        <!-- ── TAB: SUBMIT KARYA ─────────────────────────────────────────────── -->
        <div v-else-if="currentView === 'submit'" key="submit" class="p-4 sm:p-8 max-w-3xl w-full">
          <div class="mb-6 p-6 sm:p-8 rounded-2xl border border-violet-500/20 bg-violet-500/5 text-center">
            <h2 class="text-2xl font-bold text-white mb-2">Submit Karya</h2>
            <p class="text-sm text-white/50 max-w-md mx-auto">
              Unggah link repository GitHub, link Google Drive (untuk video/dokumen), dan deskripsi project timmu.
            </p>
          </div>
          <div class="p-6 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
            <div v-if="props.submission" class="mb-6 p-4 rounded-xl border border-green-500/30 bg-green-500/10">
              <p class="text-sm font-semibold text-green-400 flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Karya sudah disubmit pada {{ props.submission.submitted_at }}
              </p>
            </div>
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-medium text-white/50 mb-1.5">Judul Project</label>
                <input v-model="submitForm.project_title" type="text" placeholder="Masukkan judul..."
                       class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-white/5 text-sm text-white placeholder-white/30 focus:border-violet-500/50 outline-none transition-all" />
              </div>
              <div>
                <label class="block text-xs font-medium text-white/50 mb-1.5">Link GitHub</label>
                <input v-model="submitForm.github_url" type="url" placeholder="https://github.com/..."
                       class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-white/5 text-sm text-white placeholder-white/30 focus:border-violet-500/50 outline-none transition-all" />
              </div>
              <div>
                <label class="block text-xs font-medium text-white/50 mb-1.5">Link Google Drive (Opsional)</label>
                <input v-model="submitForm.drive_url" type="url" placeholder="https://drive.google.com/..."
                       class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-white/5 text-sm text-white placeholder-white/30 focus:border-violet-500/50 outline-none transition-all" />
              </div>
              <div>
                <label class="block text-xs font-medium text-white/50 mb-1.5">Deskripsi Singkat</label>
                <textarea v-model="submitForm.description" rows="3" placeholder="Jelaskan fitur utama karya..."
                          class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-white/5 text-sm text-white placeholder-white/30 focus:border-violet-500/50 outline-none transition-all resize-none"></textarea>
              </div>
              <button @click="submitKarya" :disabled="submitForm.loading"
                      class="mt-2 w-full py-3 rounded-xl text-sm font-semibold bg-violet-600 hover:bg-violet-500 text-white shadow-lg shadow-violet-600/25 transition-all flex items-center justify-center gap-2">
                <svg v-if="submitForm.loading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                {{ props.submission ? 'Update Karya' : 'Submit Karya' }}
              </button>
            </div>
          </div>
        </div>

        <!-- ── TAB: MANAJEMEN TIM ────────────────────────────────────────────── -->
        <div v-else-if="currentView === 'team'" key="team" class="p-4 sm:p-8 max-w-3xl w-full">
          <h2 class="text-xl font-bold text-white mb-6">Manajemen Tim</h2>
          <div class="grid gap-4">
            <div class="p-5 rounded-2xl border border-violet-500/30 bg-violet-500/10 flex items-center gap-4">
              <div class="w-12 h-12 rounded-full bg-violet-500/20 flex items-center justify-center text-lg font-bold text-violet-300">
                {{ props.user.name.charAt(0) }}
              </div>
              <div class="flex-1">
                <p class="text-sm font-bold text-white">{{ props.user.name }}</p>
                <p class="text-xs text-white/50">{{ props.user.email }}</p>
              </div>
              <span class="px-2.5 py-1 rounded-full bg-violet-500/20 text-violet-400 text-[10px] font-bold uppercase tracking-wider">Ketua</span>
            </div>
            <!-- Anggota placeholder based on member_count -->
            <div v-for="i in Math.max(0, (props.registration?.member_count ?? 1) - 1)" :key="i"
                 class="p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03] flex items-center gap-4">
              <div class="w-12 h-12 rounded-full border border-white/10 bg-white/5 flex items-center justify-center text-lg font-bold text-white/30">
                A{{ i }}
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-white/80">Anggota {{ i }}</p>
                <p class="text-xs text-white/40">Data belum lengkap</p>
              </div>
              <button class="px-3 py-1.5 rounded-lg border border-white/10 text-xs text-white/50 hover:text-white hover:bg-white/5 transition-all">Edit</button>
            </div>
          </div>
        </div>

        <!-- ── TAB: JADWAL LOMBA ─────────────────────────────────────────────── -->
        <div v-else-if="currentView === 'schedule'" key="schedule" class="p-4 sm:p-8 max-w-3xl w-full">
          <h2 class="text-xl font-bold text-white mb-6">Jadwal Lomba</h2>
          <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-white/10 before:to-transparent">
            <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
              <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-[#0a0a0a] bg-violet-500 text-white shadow shadow-violet-500/50 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
              <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-xl border border-violet-500/30 bg-violet-500/10 shadow">
                <div class="flex items-center justify-between mb-1">
                  <div class="font-bold text-violet-400">Pendaftaran</div>
                  <time class="text-xs text-violet-400/70">1 Jun - 30 Jun</time>
                </div>
                <div class="text-sm text-white/60">Pendaftaran peserta dan pembayaran.</div>
              </div>
            </div>
            <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group">
              <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-[#0a0a0a] bg-white/10 text-white/50 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2">
                <span class="w-2 h-2 bg-white/30 rounded-full"></span>
              </div>
              <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-xl border border-white/[0.07] bg-white/[0.03]">
                <div class="flex items-center justify-between mb-1">
                  <div class="font-bold text-white/80">Submit Karya</div>
                  <time class="text-xs text-white/40">1 Jul - 15 Jul</time>
                </div>
                <div class="text-sm text-white/40">Pengumpulan hasil karya peserta.</div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── TAB: SERTIFIKAT ───────────────────────────────────────────────── -->
        <div v-else-if="currentView === 'certificate'" key="certificate" class="p-4 sm:p-8 max-w-3xl w-full">
          <div class="text-center py-16">
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-white/5 border border-white/10 flex items-center justify-center">
              <svg class="w-8 h-8 text-white/20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <h2 class="text-xl font-bold text-white mb-2">Sertifikat Belum Tersedia</h2>
            <p class="text-sm text-white/40 max-w-sm mx-auto">
              Sertifikat akan otomatis diterbitkan dan dapat diunduh setelah rangkaian kompetisi selesai.
            </p>
          </div>
        </div>

      </Transition>
    </main>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.toast-progress-bar {
  animation: drain 5s linear forwards;
}
@keyframes drain { from { width: 100%; } to { width: 0%; } }
</style>
