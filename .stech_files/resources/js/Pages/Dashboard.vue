<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

// ── Props (from DemoController::dashboard) ────────────────────────────────────
const props = defineProps<{
  user: {
    name: string
    email: string
    team: string
    competition: string
    status: string
  }
}>()

// ── Types ─────────────────────────────────────────────────────────────────────
type FlowState = 'pending' | 'uploading' | 'success'

// ── State ─────────────────────────────────────────────────────────────────────
const flowState      = ref<FlowState>('pending')
const sidebarCollapsed = ref(false)
const toastVisible   = ref(false)
const dragOver       = ref(false)
const selectedFile   = ref<File | null>(null)
const uploadProgress = ref(0)
const pageVisible    = ref(false)

// ── Sidebar navigation items ──────────────────────────────────────────────────
const navItems = [
  { id: 'dashboard',   label: 'Dashboard',       icon: 'grid',    locked: false, active: true  },
  { id: 'payment',     label: 'Pembayaran',       icon: 'credit',  locked: false, active: false },
  { id: 'submit',      label: 'Submit Karya',     icon: 'upload',  locked: true,  active: false },
  { id: 'team',        label: 'Manajemen Tim',    icon: 'users',   locked: true,  active: false },
  { id: 'schedule',    label: 'Jadwal Lomba',     icon: 'calendar',locked: true,  active: false },
  { id: 'certificate', label: 'Sertifikat',       icon: 'award',   locked: true,  active: false },
]

// ── Bank account for payment ──────────────────────────────────────────────────
const bankInfo = {
  bank:    'BCA',
  name:    'Panitia S-Tech 2026',
  account: '1234 5678 90',
  amount:  'Rp 75.000',
}

// ── Computed ──────────────────────────────────────────────────────────────────
const statusBadge = computed(() => {
  switch (flowState.value) {
    case 'pending':   return { label: 'Menunggu Pembayaran', color: 'text-amber-400 border-amber-500/30 bg-amber-500/10' }
    case 'uploading': return { label: 'Memproses Upload', color: 'text-blue-400 border-blue-500/30 bg-blue-500/10' }
    case 'success':   return { label: 'Menunggu Verifikasi', color: 'text-violet-400 border-violet-500/30 bg-violet-500/10' }
  }
})

// ── File handlers ─────────────────────────────────────────────────────────────
function handleDragEnter(e: DragEvent) {
  e.preventDefault()
  dragOver.value = true
}

function handleDragLeave(e: DragEvent) {
  e.preventDefault()
  dragOver.value = false
}

function handleDrop(e: DragEvent) {
  e.preventDefault()
  dragOver.value = false
  const file = e.dataTransfer?.files[0]
  if (file) setFile(file)
}

function handleFileInput(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) setFile(file)
}

function setFile(file: File) {
  selectedFile.value = file
}

function formatFileSize(bytes: number) {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

// ── Upload flow ───────────────────────────────────────────────────────────────
function startUpload() {
  if (!selectedFile.value) return

  flowState.value     = 'uploading'
  uploadProgress.value = 0

  // Simulate realistic upload progress
  const interval = setInterval(() => {
    uploadProgress.value += Math.random() * 18 + 5
    if (uploadProgress.value >= 100) {
      uploadProgress.value = 100
      clearInterval(interval)

      setTimeout(() => {
        flowState.value  = 'success'
        toastVisible.value = true

        // Auto-dismiss toast after 5 seconds
        setTimeout(() => { toastVisible.value = false }, 5000)
      }, 300)
    }
  }, 120)
}

function resetUpload() {
  selectedFile.value   = null
  flowState.value      = 'pending'
  uploadProgress.value = 0
}

function logout() {
  router.post('/logout')
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  setTimeout(() => { pageVisible.value = true }, 60)
})

// ── Icon path helper ──────────────────────────────────────────────────────────
function iconPath(id: string): string {
  const paths: Record<string, string> = {
    grid:     'M3 3h7v7H3V3zm11 0h7v7h-7V3zM3 14h7v7H3v-7zm11 0h7v7h-7v-7z',
    credit:   'M2 5a2 2 0 012-2h16a2 2 0 012 2v14a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm2 3h16v2H4V8zm0 5h6v2H4v-2z',
    upload:   'M16 16l-4-4-4 4m4-4V20M20 7a2 2 0 00-2-2h-1.17A5 5 0 006.17 5H4a2 2 0 00-2 2v11a2 2 0 002 2h16a2 2 0 002-2V7z',
    users:    'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm8 0a3 3 0 11-6 0 3 3 0 016 0m5 10v-2a4 4 0 00-3-3.87',
    calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    award:    'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z',
    lock:     'M12 17a2 2 0 100-4 2 2 0 000 4zm6-5a6 6 0 10-12 0v2h-2v8h16v-8h-2v-2z',
    chevron:  'M9 18l6-6-6-6',
    logout:   'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
    check:    'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    x:        'M6 18L18 6M6 6l12 12',
    paperclip:'M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13',
  }
  return paths[id] ?? ''
}
</script>

<template>
  <div class="flex h-screen bg-[#0a0a0a] text-white font-['Inter',sans-serif] overflow-hidden">

    <!-- ═══════════════════════════════════════════════════
         TOAST NOTIFICATION
    ════════════════════════════════════════════════════ -->
    <Transition
      enter-active-class="transition-all duration-500 ease-out"
      enter-from-class="opacity-0 translate-y-full"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-full"
    >
      <div
        v-if="toastVisible"
        id="toast-notification"
        class="fixed bottom-6 right-6 z-50 w-80 p-4 rounded-2xl
               border border-green-500/20 bg-[#111] shadow-2xl shadow-black/60
               backdrop-blur-sm"
      >
        <div class="flex items-start gap-3">
          <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500/15 border border-green-500/30
                      flex items-center justify-center">
            <svg class="w-4 h-4 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path :d="iconPath('check')" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-white">Bukti berhasil diunggah</p>
            <p class="text-xs text-white/50 mt-0.5 leading-relaxed">
              Bukti pembayaranmu sedang diproses admin. Estimasi verifikasi 1×24 jam.
            </p>
          </div>
          <button
            @click="toastVisible = false"
            id="toast-close-btn"
            class="flex-shrink-0 w-6 h-6 flex items-center justify-center
                   text-white/30 hover:text-white transition-colors duration-200"
          >
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path :d="iconPath('x')" stroke-linecap="round"/>
            </svg>
          </button>
        </div>
        <!-- Progress bar -->
        <div class="mt-3 h-px w-full bg-white/10 rounded overflow-hidden">
          <div class="h-full bg-green-500/50 rounded toast-progress-bar"></div>
        </div>
      </div>
    </Transition>

    <!-- ═══════════════════════════════════════════════════
         SIDEBAR
    ════════════════════════════════════════════════════ -->
    <aside
      :class="[
        'relative flex flex-col border-r border-white/[0.07] bg-[#0d0d0d]',
        'transition-all duration-300 ease-in-out',
        sidebarCollapsed ? 'w-[72px]' : 'w-64',
      ]"
    >
      <!-- Brand -->
      <div class="flex items-center h-16 px-4 border-b border-white/[0.07]"
           :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
        <div class="flex items-center gap-2.5">
          <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600
                      flex items-center justify-center text-sm font-bold shadow-lg">S</div>
          <Transition
            enter-active-class="transition-all duration-200"
            enter-from-class="opacity-0 -translate-x-2"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 translate-x-0"
            leave-to-class="opacity-0 -translate-x-2"
          >
            <span v-if="!sidebarCollapsed" class="text-sm font-semibold whitespace-nowrap">
              S-Tech <span class="text-white/40">'26</span>
            </span>
          </Transition>
        </div>

        <!-- Collapse toggle -->
        <button
          v-if="!sidebarCollapsed"
          @click="sidebarCollapsed = true"
          id="sidebar-collapse-btn"
          class="w-6 h-6 rounded-md flex items-center justify-center
                 text-white/30 hover:text-white hover:bg-white/5
                 transition-all duration-200"
        >
          <svg class="w-3.5 h-3.5 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path :d="iconPath('chevron')" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>

      <!-- Expand when collapsed -->
      <button
        v-if="sidebarCollapsed"
        @click="sidebarCollapsed = false"
        id="sidebar-expand-btn"
        class="absolute -right-3 top-20 w-6 h-6 rounded-full border border-white/10
               bg-[#0d0d0d] flex items-center justify-center
               text-white/40 hover:text-white transition-all duration-200 shadow-md"
      >
        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path :d="iconPath('chevron')" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

      <!-- Navigation -->
      <nav class="flex-1 py-4 px-2 space-y-0.5 overflow-hidden">
        <div
          v-for="item in navItems"
          :key="item.id"
          :id="`nav-${item.id}`"
          :class="[
            'group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm',
            'transition-all duration-200',
            item.locked
              ? 'opacity-40 cursor-not-allowed'
              : item.active
                ? 'bg-white/8 text-white border border-white/8'
                : 'text-white/50 hover:text-white hover:bg-white/5 cursor-pointer',
          ]"
        >
          <!-- Icon -->
          <div class="flex-shrink-0 w-5 h-5">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="w-full h-full">
              <path :d="iconPath(item.icon)" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>

          <!-- Label (hidden when collapsed) -->
          <Transition
            enter-active-class="transition-all duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <span v-if="!sidebarCollapsed" class="flex-1 whitespace-nowrap font-medium">
              {{ item.label }}
            </span>
          </Transition>

          <!-- Lock icon -->
          <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
          >
            <div v-if="item.locked && !sidebarCollapsed"
                 class="flex-shrink-0 w-3.5 h-3.5 text-white/30">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-full h-full">
                <rect x="5" y="11" width="14" height="10" rx="2"/>
                <path d="M8 11V7a4 4 0 118 0v4" stroke-linecap="round"/>
              </svg>
            </div>
          </Transition>

          <!-- Active indicator dot -->
          <div v-if="item.active && !item.locked"
               class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5
                      bg-violet-400 rounded-r"></div>
        </div>
      </nav>

      <!-- User profile at bottom -->
      <div class="p-3 border-t border-white/[0.07]">
        <div :class="['flex items-center gap-3 p-2 rounded-xl', sidebarCollapsed ? 'justify-center' : '']">
          <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-violet-500/30 to-indigo-600/30
                      border border-violet-500/20 flex items-center justify-center text-xs font-bold text-violet-300">
            {{ props.user.name.charAt(0).toUpperCase() }}
          </div>
          <Transition
            enter-active-class="transition-all duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
          >
            <div v-if="!sidebarCollapsed" class="flex-1 min-w-0">
              <p class="text-xs font-semibold text-white truncate">{{ props.user.name }}</p>
              <p class="text-[10px] text-white/40 truncate">{{ props.user.competition }}</p>
            </div>
          </Transition>
        </div>

        <!-- Logout -->
        <button
          v-if="!sidebarCollapsed"
          @click="logout"
          id="logout-btn"
          class="mt-1 w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs
                 text-white/30 hover:text-white/70 hover:bg-white/5
                 transition-all duration-200"
        >
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path :d="iconPath('logout')" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Keluar
        </button>
      </div>
    </aside>

    <!-- ═══════════════════════════════════════════════════
         MAIN CONTENT
    ════════════════════════════════════════════════════ -->
    <main class="flex-1 overflow-y-auto bg-[#0a0a0a]">

      <!-- Top bar -->
      <header class="sticky top-0 z-20 flex items-center justify-between
                     h-16 px-8 border-b border-white/[0.06] bg-[#0a0a0a]/90 backdrop-blur-sm">
        <div>
          <h1 class="text-sm font-semibold text-white">Dashboard Peserta</h1>
          <p class="text-xs text-white/40 mt-0.5">{{ props.user.team }} · {{ props.user.competition }}</p>
        </div>

        <!-- Status badge -->
        <div :class="['px-3 py-1.5 rounded-full border text-xs font-semibold', statusBadge.color]">
          {{ statusBadge.label }}
        </div>
      </header>

      <!-- Content area -->
      <Transition
        enter-active-class="transition-all duration-500 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        mode="out-in"
      >

        <!-- ─── STATE 1: Pending Payment ─────────────────────────────────── -->
        <div v-if="flowState === 'pending'" key="pending" class="p-8 max-w-3xl">

          <!-- Welcome banner -->
          <div class="mb-8 p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
            <p class="text-xs text-white/40 mb-1">Halo 👋</p>
            <h2 class="text-xl font-bold text-white">Selamat datang, {{ props.user.name }}!</h2>
            <p class="text-sm text-white/50 mt-1">Selesaikan pembayaran untuk mengaktifkan akunmu.</p>
          </div>

          <!-- Progress steps -->
          <div class="flex items-center gap-0 mb-8">
            <div class="flex flex-col items-center">
              <div class="w-7 h-7 rounded-full bg-green-500/20 border border-green-500/40
                          flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              </div>
              <p class="text-[10px] text-green-400 mt-1.5 font-medium">Registrasi</p>
            </div>
            <div class="flex-1 h-px bg-white/10 mx-3"></div>
            <div class="flex flex-col items-center">
              <div class="w-7 h-7 rounded-full bg-amber-500/20 border-2 border-amber-500
                          flex items-center justify-center">
                <span class="text-[10px] font-bold text-amber-400">2</span>
              </div>
              <p class="text-[10px] text-amber-400 mt-1.5 font-medium">Pembayaran</p>
            </div>
            <div class="flex-1 h-px bg-white/10 mx-3"></div>
            <div class="flex flex-col items-center">
              <div class="w-7 h-7 rounded-full border border-white/15 bg-white/5
                          flex items-center justify-center">
                <span class="text-[10px] font-bold text-white/30">3</span>
              </div>
              <p class="text-[10px] text-white/30 mt-1.5">Verifikasi</p>
            </div>
            <div class="flex-1 h-px bg-white/10 mx-3"></div>
            <div class="flex flex-col items-center">
              <div class="w-7 h-7 rounded-full border border-white/15 bg-white/5
                          flex items-center justify-center">
                <span class="text-[10px] font-bold text-white/30">4</span>
              </div>
              <p class="text-[10px] text-white/30 mt-1.5">Aktif</p>
            </div>
          </div>

          <!-- Bank info card -->
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
                  <button
                    id="copy-account-btn"
                    class="text-[10px] px-2 py-0.5 rounded border border-white/10 text-white/40
                           hover:text-white hover:border-white/20 transition-all duration-200"
                    @click="navigator.clipboard?.writeText(bankInfo.account)"
                  >salin</button>
                </div>
              </div>
              <div>
                <p class="text-xs text-white/40 mb-1">Jumlah Transfer</p>
                <p class="text-xl font-extrabold text-amber-300">{{ bankInfo.amount }}</p>
              </div>
            </div>
            <div class="mt-4 pt-4 border-t border-amber-500/10">
              <p class="text-xs text-amber-300/70">
                ⚠ Transfer dengan jumlah yang tepat. Pembayaran berbeda akan memperlambat verifikasi.
              </p>
            </div>
          </div>

          <!-- Upload area -->
          <div class="rounded-2xl border border-white/[0.07] bg-white/[0.02] p-6">
            <p class="text-sm font-semibold text-white mb-1">Upload Bukti Transfer</p>
            <p class="text-xs text-white/40 mb-5">Format: JPG, PNG, atau PDF · Maks 5 MB</p>

            <!-- Drop zone -->
            <div
              @dragenter="handleDragEnter"
              @dragover.prevent
              @dragleave="handleDragLeave"
              @drop="handleDrop"
              id="drop-zone"
              :class="[
                'relative flex flex-col items-center justify-center gap-3 p-10',
                'rounded-xl border-2 border-dashed transition-all duration-300 cursor-pointer',
                dragOver
                  ? 'border-violet-500 bg-violet-500/10 scale-[1.01]'
                  : selectedFile
                    ? 'border-green-500/40 bg-green-500/5'
                    : 'border-white/10 hover:border-white/20 hover:bg-white/[0.03]',
              ]"
              @click="($refs.fileInput as HTMLInputElement)?.click()"
            >
              <input
                ref="fileInput"
                type="file"
                accept="image/*,.pdf"
                class="hidden"
                @change="handleFileInput"
              />

              <Transition mode="out-in"
                enter-active-class="transition-all duration-300"
                enter-from-class="opacity-0 scale-90"
                enter-to-class="opacity-100 scale-100">

                <!-- No file selected -->
                <div v-if="!selectedFile" key="empty" class="flex flex-col items-center gap-2 text-center">
                  <div class="w-12 h-12 rounded-xl border border-white/10 bg-white/5
                              flex items-center justify-center mb-1
                              group-hover:border-violet-500/30 transition-all duration-300">
                    <svg class="w-5 h-5 text-white/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                      <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                            stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                  <p class="text-sm font-medium text-white/70">
                    Seret & lepas file di sini
                  </p>
                  <p class="text-xs text-white/30">atau klik untuk memilih file</p>
                </div>

                <!-- File selected -->
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
                  <button
                    @click.stop="resetUpload"
                    id="remove-file-btn"
                    class="w-7 h-7 rounded-lg flex items-center justify-center
                           border border-white/10 text-white/40 hover:text-red-400
                           hover:border-red-500/30 transition-all duration-200"
                  >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <path :d="iconPath('x')" stroke-linecap="round"/>
                    </svg>
                  </button>
                </div>
              </Transition>
            </div>

            <!-- Upload button -->
            <button
              @click="startUpload"
              id="upload-btn"
              :disabled="!selectedFile"
              :class="[
                'mt-4 w-full py-3 rounded-xl text-sm font-semibold transition-all duration-200',
                'flex items-center justify-center gap-2 active:scale-[0.98]',
                selectedFile
                  ? 'bg-violet-600 hover:bg-violet-500 text-white shadow-lg shadow-violet-600/25 hover:shadow-violet-500/35'
                  : 'bg-white/5 text-white/25 cursor-not-allowed border border-white/8',
              ]"
            >
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                      stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Upload Bukti Pembayaran
            </button>
          </div>
        </div>

        <!-- ─── STATE 2: Uploading ────────────────────────────────────────── -->
        <div v-else-if="flowState === 'uploading'" key="uploading"
             class="p-8 max-w-3xl flex flex-col items-center justify-center min-h-[60vh]">

          <div class="w-full max-w-sm text-center">
            <!-- Animated spinner ring -->
            <div class="relative w-24 h-24 mx-auto mb-8">
              <svg class="w-full h-full -rotate-90 animate-[spin_2s_linear_infinite]"
                   viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>
                <circle cx="50" cy="50" r="42" fill="none" stroke="url(#grad)" stroke-width="8"
                        stroke-linecap="round"
                        :stroke-dasharray="`${uploadProgress * 2.638} 263.8`"
                        class="transition-all duration-200"/>
                <defs>
                  <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" style="stop-color:#8b5cf6"/>
                    <stop offset="100%" style="stop-color:#6366f1"/>
                  </linearGradient>
                </defs>
              </svg>
              <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-lg font-bold text-white">{{ Math.round(uploadProgress) }}%</span>
              </div>
            </div>

            <h2 class="text-xl font-bold text-white mb-2">Mengunggah bukti...</h2>
            <p class="text-sm text-white/40 mb-8">Jangan tutup halaman ini.</p>

            <!-- File name -->
            <div class="flex items-center gap-3 p-4 rounded-xl border border-white/[0.07] bg-white/[0.03] text-left">
              <div class="w-8 h-8 rounded-lg border border-violet-500/30 bg-violet-500/10
                          flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-violet-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                  <path :d="iconPath('paperclip')" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-white truncate">{{ selectedFile?.name }}</p>
                <div class="mt-1.5 h-1 w-full bg-white/10 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-gradient-to-r from-violet-500 to-indigo-400 rounded-full transition-all duration-200"
                    :style="{ width: `${uploadProgress}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ─── STATE 3: Success / Waiting Verification ───────────────────── -->
        <div v-else-if="flowState === 'success'" key="success" class="p-8 max-w-3xl">

          <!-- Success hero card -->
          <div class="mb-6 p-8 rounded-2xl border border-green-500/20 bg-green-500/5 text-center">
            <!-- Animated checkmark -->
            <div class="relative w-16 h-16 mx-auto mb-6">
              <div class="absolute inset-0 rounded-full bg-green-500/20 animate-ping opacity-60"></div>
              <div class="relative w-16 h-16 rounded-full bg-green-500/20 border border-green-500/30
                          flex items-center justify-center">
                <svg class="w-7 h-7 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              </div>
            </div>

            <h2 class="text-2xl font-bold text-white mb-2">Upload Berhasil!</h2>
            <p class="text-sm text-white/50 leading-relaxed max-w-sm mx-auto">
              Bukti pembayaranmu sudah kami terima. Admin sedang memproses verifikasi
              dan akan menghubungimu dalam <strong class="text-white/80">1×24 jam</strong>.
            </p>
          </div>

          <!-- Info cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
              <p class="text-xs text-white/40 mb-2">File yang diunggah</p>
              <p class="text-sm font-medium text-white truncate">{{ selectedFile?.name }}</p>
              <p class="text-xs text-white/30 mt-1">{{ selectedFile ? formatFileSize(selectedFile.size) : '' }}</p>
            </div>
            <div class="p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03]">
              <p class="text-xs text-white/40 mb-2">Estimasi verifikasi</p>
              <p class="text-sm font-medium text-white">1×24 Jam</p>
              <p class="text-xs text-white/30 mt-1">Hari kerja, Senin–Jumat</p>
            </div>
          </div>

          <!-- Locked features notice -->
          <div class="p-5 rounded-2xl border border-white/[0.07] bg-white/[0.02]">
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="5" y="11" width="14" height="10" rx="2"/>
                  <path d="M8 11V7a4 4 0 118 0v4" stroke-linecap="round"/>
                </svg>
              </div>
              <div>
                <p class="text-sm font-semibold text-white/80">Fitur dalam antrian</p>
                <p class="text-xs text-white/40 mt-1 leading-relaxed">
                  Submit Karya, Manajemen Tim, Jadwal Lomba, dan Sertifikat akan terbuka
                  secara otomatis setelah pembayaranmu diverifikasi.
                </p>
              </div>
            </div>

            <!-- Locked features list -->
            <div class="mt-4 grid grid-cols-2 gap-2">
              <div v-for="item in navItems.filter(i => i.locked)" :key="item.id"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg border border-white/[0.06] bg-white/[0.02]">
                <svg class="w-3.5 h-3.5 text-white/20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="5" y="11" width="14" height="10" rx="2"/>
                  <path d="M8 11V7a4 4 0 118 0v4" stroke-linecap="round"/>
                </svg>
                <span class="text-xs text-white/30">{{ item.label }}</span>
              </div>
            </div>
          </div>

          <!-- Reset demo button -->
          <button
            @click="resetUpload"
            id="reset-demo-btn"
            class="mt-4 px-4 py-2 text-xs text-white/30 hover:text-white/60
                   border border-white/8 hover:border-white/15 rounded-lg
                   transition-all duration-200"
          >
            ↩ Reset demo
          </button>
        </div>

      </Transition>
    </main>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

/* Toast progress bar auto-drain animation */
.toast-progress-bar {
  animation: drain 5s linear forwards;
}

@keyframes drain {
  from { width: 100%; }
  to   { width: 0%;   }
}
</style>
