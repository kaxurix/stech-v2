<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps<{
  registrations: {
    data: Array<{
      id: number; team_name: string; competition: string
      member_count: number; institution: string; category: string
      status: string; status_label: string; status_color: string
      created_at: string
      user: { name: string; email: string }
      payment: { status: string; uploaded_at: string | null } | null
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
    meta: { current_page: number; last_page: number; total: number }
  }
  stats: {
    total: number; pending_payment: number; pending_verification: number
    verified: number; rejected: number
  }
  filters: { status?: string; search?: string }
}>()

const page = usePage<{ flash: { type?: string; message?: string }; auth: { user: { name: string } } }>()

// ── State ─────────────────────────────────────────────────────────────────────
const searchQuery  = ref(props.filters.search ?? '')
const activeFilter = ref(props.filters.status ?? 'all')
const toastVisible = ref(false)
const toastMsg     = ref('')
const toastType    = ref<'success' | 'error'>('success')
const adminView    = ref<'list' | 'progress'>('list')

// Show flash on mount
if (page.props.flash?.message) {
  toastVisible.value = true
  toastMsg.value     = page.props.flash.message
  toastType.value    = (page.props.flash.type as 'success' | 'error') ?? 'success'
  setTimeout(() => { toastVisible.value = false }, 4000)
}

// ── Filter / search ───────────────────────────────────────────────────────────
function applyFilter(status: string) {
  activeFilter.value = status
  router.get('/admin', { status: status === 'all' ? '' : status, search: searchQuery.value }, {
    preserveState: true, replace: true,
  })
}

function applySearch() {
  router.get('/admin', { status: activeFilter.value === 'all' ? '' : activeFilter.value, search: searchQuery.value }, {
    preserveState: true, replace: true,
  })
}

function goToPage(url: string | null) {
  if (!url) return
  router.get(url, {}, { preserveState: true })
}

function toggleFinalist(id: number) {
  router.post(`/admin/peserta/${id}/toggle-finalist`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      const pageFlash = page.props.flash
      if (pageFlash?.message) {
        toastVisible.value = true
        toastMsg.value     = pageFlash.message
        toastType.value    = (pageFlash.type as 'success' | 'error') ?? 'success'
        setTimeout(() => { toastVisible.value = false }, 4000)
      }
    }
  })
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const statusFilterTabs = [
  { key: 'all',                  label: 'Semua',              count: computed(() => props.stats.total) },
  { key: 'pending_payment',      label: 'Belum Bayar',        count: computed(() => props.stats.pending_payment) },
  { key: 'pending_verification', label: 'Menunggu Verifikasi',count: computed(() => props.stats.pending_verification) },
  { key: 'verified',             label: 'Terverifikasi',      count: computed(() => props.stats.verified) },
  { key: 'rejected',             label: 'Ditolak',            count: computed(() => props.stats.rejected) },
]

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
  const m: Record<string, string> = { sma: 'SMA/SMK', mahasiswa: 'Mahasiswa', umum: 'Umum' }
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
      <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
        <div class="flex items-center">
          <div>
            <p class="text-sm font-bold leading-none text-white">Soedirman Technophoria</p>
            <p class="text-[10px] text-white/35 mt-1">Panel Admin</p>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <span class="text-xs text-white/40">{{ page.props.auth.user.name }}</span>
          <button @click="logout" id="admin-logout-btn"
                  class="px-3 py-1.5 text-xs rounded-lg border border-white/10
                         text-white/50 hover:text-white hover:bg-white/5 transition-all duration-200">
            Keluar
          </button>
        </div>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

      <!-- Page title -->
      <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-xl font-bold text-white">Panel Admin S-Tech 2026</h1>
          <p class="text-xs text-white/40 mt-0.5">Kelola registrasi, verifikasi pembayaran, dan pantau submission karya peserta</p>
        </div>
        <!-- View toggle -->
        <div class="flex items-center gap-1.5 p-1 rounded-xl bg-white/[0.04] border border-white/[0.07] self-start">
          <button @click="adminView = 'list'"
                  :class="['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5',
                           adminView === 'list' ? 'bg-violet-600 text-white shadow' : 'text-white/50 hover:text-white']">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            Daftar Peserta
          </button>
          <button @click="adminView = 'progress'"
                  :class="['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5',
                           adminView === 'progress' ? 'bg-violet-600 text-white shadow' : 'text-white/50 hover:text-white']">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Submission Karya
          </button>
        </div>
      </div>

      <!-- Stats cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-5 gap-3 mb-7">
        <div class="col-span-2 sm:col-span-4 xl:col-span-1 p-4 rounded-2xl border border-white/[0.07] bg-white/[0.02] flex sm:flex-col xl:flex-col items-center sm:items-start gap-3 sm:gap-1">
          <p class="text-3xl font-extrabold text-white">{{ props.stats.total }}</p>
          <p class="text-xs text-white/40 font-medium">Total Tim Terdaftar</p>
        </div>
        <div v-for="stat in [
          { label: 'Belum Bayar',         value: props.stats.pending_payment,      border: 'border-amber-500/20',  text: 'text-amber-400',  dot: 'bg-amber-400' },
          { label: 'Menunggu Verifikasi', value: props.stats.pending_verification, border: 'border-violet-500/20', text: 'text-violet-400', dot: 'bg-violet-400' },
          { label: 'Terverifikasi',       value: props.stats.verified,             border: 'border-green-500/20',  text: 'text-green-400',  dot: 'bg-green-400' },
          { label: 'Ditolak',             value: props.stats.rejected,             border: 'border-red-500/20',    text: 'text-red-400',    dot: 'bg-red-400' },
        ]" :key="stat.label"
             :class="['p-4 rounded-2xl border bg-white/[0.02]', stat.border]">
          <p :class="['text-2xl font-extrabold mb-1', stat.text]">{{ stat.value }}</p>
          <div class="flex items-center gap-1.5">
            <span :class="['w-1.5 h-1.5 rounded-full flex-shrink-0', stat.dot]"></span>
            <p class="text-xs text-white/40">{{ stat.label }}</p>
          </div>
        </div>
      </div>

      <!-- ══ LIST VIEW ══════════════════════════════════════════════════════ -->
      <div v-if="adminView === 'list'">
        <!-- Filter tabs + search -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-6">
          <!-- Tabs -->
          <div class="flex items-center gap-1 p-1 rounded-xl bg-white/[0.04] border border-white/[0.07] flex-wrap">
            <button v-for="tab in statusFilterTabs" :key="tab.key"
                    @click="applyFilter(tab.key)"
                    :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200 flex items-center gap-1.5',
                             activeFilter === tab.key
                               ? 'bg-violet-600 text-white shadow-sm'
                               : 'text-white/50 hover:text-white hover:bg-white/5']">
              {{ tab.label }}
              <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                    :class="activeFilter === tab.key ? 'bg-white/20 text-white' : 'bg-white/10 text-white/50'">
                {{ tab.count.value }}
              </span>
            </button>
          </div>

          <!-- Search -->
          <div class="flex-1 flex items-center gap-2">
            <div class="relative flex-1 max-w-sm">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35" stroke-linecap="round"/>
              </svg>
              <input v-model="searchQuery" @keyup.enter="applySearch"
                     type="text" placeholder="Cari tim, nama, email..."
                     class="w-full pl-9 pr-4 py-2 rounded-xl border border-white/10 bg-white/5
                            text-sm text-white placeholder-white/30
                            focus:outline-none focus:border-violet-500/50 transition-all duration-200" />
            </div>
            <button @click="applySearch"
                    class="px-4 py-2 rounded-xl text-sm font-medium border border-white/10
                           text-white/60 hover:text-white hover:bg-white/5 transition-all duration-200">
              Cari
            </button>
          </div>
        </div>

        <!-- Table -->
        <div class="rounded-2xl border border-white/[0.07] bg-white/[0.02] overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-white/[0.07] bg-white/[0.03]">
                  <th class="px-5 py-3.5 text-left text-xs font-semibold text-white/40 uppercase tracking-wider">Tim / Peserta</th>
                  <th class="px-5 py-3.5 text-left text-xs font-semibold text-white/40 uppercase tracking-wider hidden md:table-cell">Institusi</th>
                  <th class="px-5 py-3.5 text-left text-xs font-semibold text-white/40 uppercase tracking-wider hidden lg:table-cell">Kategori</th>
                  <th class="px-5 py-3.5 text-left text-xs font-semibold text-white/40 uppercase tracking-wider hidden lg:table-cell">Daftar</th>
                  <th class="px-5 py-3.5 text-left text-xs font-semibold text-white/40 uppercase tracking-wider">Status</th>
                  <th class="px-5 py-3.5 text-left text-xs font-semibold text-white/40 uppercase tracking-wider hidden sm:table-cell">Bukti</th>
                  <th class="px-5 py-3.5 text-right text-xs font-semibold text-white/40 uppercase tracking-wider">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/[0.05]">
                <tr v-if="props.registrations.data.length === 0">
                  <td colspan="7" class="px-5 py-16 text-center text-white/30 text-sm">
                    Tidak ada data yang sesuai filter.
                  </td>
                </tr>
                <tr v-for="reg in props.registrations.data" :key="reg.id"
                    class="hover:bg-white/[0.03] transition-colors duration-150">
                  <!-- Tim & user -->
                  <td class="px-5 py-4">
                    <p class="font-semibold text-white">{{ reg.team_name }}</p>
                    <p class="text-xs text-white/40 mt-0.5">{{ reg.user.name }}</p>
                    <p class="text-xs text-white/30">{{ reg.user.email }}</p>
                  </td>
                  <!-- Institusi -->
                  <td class="px-5 py-4 hidden md:table-cell">
                    <p class="text-white/70 text-xs">{{ reg.institution }}</p>
                    <p class="text-white/30 text-xs mt-0.5">{{ reg.member_count }} anggota</p>
                  </td>
                  <!-- Kategori -->
                  <td class="px-5 py-4 hidden lg:table-cell">
                    <span class="px-2.5 py-1 rounded-full border border-white/10 text-xs text-white/50">
                      {{ categoryLabel(reg.category) }}
                    </span>
                  </td>
                  <!-- Tanggal -->
                  <td class="px-5 py-4 hidden lg:table-cell text-xs text-white/40">{{ reg.created_at }}</td>
                  <!-- Status -->
                  <td class="px-5 py-4">
                    <span :class="['px-2.5 py-1 rounded-full border text-[11px] font-semibold', statusBadgeClass(reg.status_color)]">
                      {{ reg.status_label }}
                    </span>
                  </td>
                  <!-- Bukti payment -->
                  <td class="px-5 py-4 hidden sm:table-cell text-xs text-white/40">
                    <span v-if="reg.payment" class="text-violet-400">
                      Diunggah {{ reg.payment.uploaded_at ?? '' }}
                    </span>
                    <span v-else class="text-white/20">Belum ada</span>
                  </td>
                  <!-- Aksi -->
                  <td class="px-5 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <span v-if="(reg as any).is_finalist" class="px-2 py-0.5 rounded-full border border-green-500/30 bg-green-500/10 text-green-400 text-[10px] font-bold">
                        Finalis
                      </span>
                      <button v-if="reg.status === 'verified'"
                              @click="toggleFinalist(reg.id)"
                              class="px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all duration-200"
                              :class="(reg as any).is_finalist 
                                ? 'border-red-500/30 bg-red-500/10 text-red-400 hover:bg-red-500/20' 
                                : 'border-violet-500/30 bg-violet-500/10 text-violet-400 hover:bg-violet-500/20'">
                        {{ (reg as any).is_finalist ? 'Batal Final' : 'Lolos Final' }}
                      </button>
                      <a :href="`/admin/peserta/${reg.id}`"
                         class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium
                                border border-white/10 text-white/60 hover:text-white hover:bg-white/5
                                transition-all duration-200">
                        Detail →
                      </a>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="props.registrations.links && props.registrations.links.length > 3"
               class="flex items-center justify-between px-5 py-4 border-t border-white/[0.07]">
            <p class="text-xs text-white/30">Total: {{ props.stats.total }} pendaftar</p>
            <div class="flex items-center gap-1">
              <button v-for="link in props.registrations.links" :key="link.label"
                      @click="goToPage(link.url)"
                      :disabled="!link.url"
                      v-html="link.label"
                      :class="['px-3 py-1.5 rounded-lg text-xs transition-all duration-200',
                               link.active ? 'bg-violet-600 text-white' : 'text-white/40 hover:text-white hover:bg-white/5',
                               !link.url ? 'opacity-30 cursor-not-allowed' : 'cursor-pointer']">
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ PROGRESS VIEW ══════════════════════════════════════════════════ -->
      <div v-else-if="adminView === 'progress'">
        <div class="flex items-center justify-between mb-5">
          <p class="text-sm text-white/40">Detail submission karya dan status setiap tim peserta.</p>
          <span class="text-xs text-white/30">{{ props.registrations.data.length }} tim ditampilkan</span>
        </div>

        <!-- Empty state -->
        <div v-if="props.registrations.data.length === 0" class="text-center py-20 rounded-2xl border border-white/[0.07] bg-white/[0.02]">
          <p class="text-white/30 text-sm">Belum ada tim yang terdaftar.</p>
        </div>

        <!-- Cards grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div v-for="reg in props.registrations.data" :key="reg.id"
               class="rounded-2xl border bg-white/[0.02] overflow-hidden transition-all duration-200 hover:bg-white/[0.035]"
               :class="{
                 'border-green-500/20':  reg.status === 'verified' && !!(reg as any).submission,
                 'border-violet-500/20': reg.status === 'verified' && !(reg as any).submission,
                 'border-amber-500/20':  reg.status === 'pending_verification',
                 'border-red-500/20':    reg.status === 'rejected',
                 'border-white/[0.07]':  reg.status === 'pending_payment',
               }">

            <!-- Card header -->
            <div class="px-5 py-4 flex items-start justify-between gap-3 border-b border-white/[0.06]">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <p class="font-bold text-white text-sm">{{ reg.team_name }}</p>
                  <span :class="['px-2 py-0.5 rounded-full border text-[10px] font-bold', statusBadgeClass(reg.status_color)]">
                    {{ reg.status_label }}
                  </span>
                  <span v-if="(reg as any).is_finalist" class="px-2 py-0.5 rounded-full border border-green-500/30 bg-green-500/10 text-green-400 text-[10px] font-bold">
                    ✓ Finalis
                  </span>
                </div>
                <p class="text-xs text-white/40 mt-0.5">{{ reg.user.name }} · {{ reg.institution }}</p>
              </div>
              <div class="flex-shrink-0 flex items-center gap-2">
                <button v-if="reg.status === 'verified'"
                        @click="toggleFinalist(reg.id)"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition-all duration-200"
                        :class="(reg as any).is_finalist 
                          ? 'border-red-500/30 bg-red-500/10 text-red-400 hover:bg-red-500/20' 
                          : 'border-violet-500/30 bg-violet-500/10 text-violet-400 hover:bg-violet-500/20'">
                  {{ (reg as any).is_finalist ? 'Batalkan Final' : 'Loloskan Final' }}
                </button>
                <a :href="`/admin/peserta/${reg.id}`"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium border border-white/10
                          text-white/50 hover:text-white hover:bg-white/5 transition-all whitespace-nowrap">
                  Detail →
                </a>
              </div>
            </div>

            <!-- Timeline steps -->
            <div class="px-5 py-3 flex items-center gap-0 border-b border-white/[0.06]">
              <!-- Step 1: Upload -->
              <div class="flex items-center gap-1.5 flex-1 min-w-0">
                <span :class="['w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0',
                               reg.payment ? 'bg-green-500/20 border border-green-500/30' : 'bg-white/5 border border-white/10']">
                  <svg v-if="reg.payment" class="w-2.5 h-2.5 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                  <span v-else class="w-1 h-1 rounded-full bg-white/20"></span>
                </span>
                <span :class="['text-[10px] font-medium truncate', reg.payment ? 'text-green-400' : 'text-white/25']">Bayar</span>
              </div>
              <div :class="['w-6 h-px flex-shrink-0', reg.payment ? 'bg-white/20' : 'bg-white/8']"></div>
              <!-- Step 2: Verifikasi -->
              <div class="flex items-center gap-1.5 flex-1 min-w-0">
                <span :class="['w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0',
                               reg.status === 'verified' ? 'bg-green-500/20 border border-green-500/30'
                             : reg.status === 'pending_verification' ? 'bg-violet-500/20 border border-violet-500/30'
                             : reg.status === 'rejected' ? 'bg-red-500/20 border border-red-500/30'
                             : 'bg-white/5 border border-white/10']">
                  <svg v-if="reg.status === 'verified'" class="w-2.5 h-2.5 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                  <svg v-else-if="reg.status === 'pending_verification'" class="w-2.5 h-2.5 text-violet-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l2 2"/><circle cx="12" cy="12" r="9"/></svg>
                  <svg v-else-if="reg.status === 'rejected'" class="w-2.5 h-2.5 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round"/></svg>
                  <span v-else class="w-1 h-1 rounded-full bg-white/20"></span>
                </span>
                <span :class="['text-[10px] font-medium truncate',
                               reg.status === 'verified' ? 'text-green-400'
                             : reg.status === 'pending_verification' ? 'text-violet-400'
                             : reg.status === 'rejected' ? 'text-red-400'
                             : 'text-white/25']">Verifikasi</span>
              </div>
              <div :class="['w-6 h-px flex-shrink-0', reg.status === 'verified' ? 'bg-white/20' : 'bg-white/8']"></div>
              <!-- Step 3: Submit -->
              <div class="flex items-center gap-1.5 flex-1 min-w-0">
                <span :class="['w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0',
                               !!(reg as any).submission ? 'bg-green-500/20 border border-green-500/30' : 'bg-white/5 border border-white/10']">
                  <svg v-if="!!(reg as any).submission" class="w-2.5 h-2.5 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                  <span v-else class="w-1 h-1 rounded-full bg-white/20"></span>
                </span>
                <span :class="['text-[10px] font-medium truncate', !!(reg as any).submission ? 'text-green-400' : 'text-white/25']">Submit</span>
              </div>
            </div>

            <!-- Submission detail -->
            <div class="px-5 py-4">
              <div v-if="(reg as any).submission" class="space-y-3">
                <!-- Judul project -->
                <div>
                  <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider mb-1">Judul Project</p>
                  <p class="text-sm font-semibold text-white">{{ (reg as any).submission.project_title }}</p>
                </div>

                <!-- Links -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                  <div v-if="(reg as any).submission.github_url"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg border border-white/[0.07] bg-white/[0.02]">
                    <svg class="w-3.5 h-3.5 text-white/40 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
                    </svg>
                    <a :href="(reg as any).submission.github_url" target="_blank" rel="noopener"
                       class="text-xs text-violet-400 hover:text-violet-300 transition-colors truncate">
                      GitHub Repository
                    </a>
                  </div>
                  <div v-else class="flex items-center gap-2 px-3 py-2 rounded-lg border border-white/[0.07] bg-white/[0.02]">
                    <svg class="w-3.5 h-3.5 text-white/20 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg>
                    <span class="text-xs text-white/20">GitHub belum diisi</span>
                  </div>

                  <div v-if="(reg as any).submission.drive_url"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg border border-white/[0.07] bg-white/[0.02]">
                    <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <a :href="(reg as any).submission.drive_url" target="_blank" rel="noopener"
                       class="text-xs text-blue-400 hover:text-blue-300 transition-colors truncate">
                      Google Drive
                    </a>
                  </div>
                  <div v-else class="flex items-center gap-2 px-3 py-2 rounded-lg border border-white/[0.07] bg-white/[0.02]">
                    <svg class="w-3.5 h-3.5 text-white/20 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="text-xs text-white/20">Drive tidak dilampirkan</span>
                  </div>
                </div>

                <!-- Deskripsi -->
                <div v-if="(reg as any).submission.description" class="px-3 py-2.5 rounded-lg border border-white/[0.07] bg-white/[0.02]">
                  <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider mb-1">Deskripsi</p>
                  <p class="text-xs text-white/60 leading-relaxed line-clamp-3">{{ (reg as any).submission.description }}</p>
                </div>

                <!-- Submitted at -->
                <p class="text-[10px] text-white/25">
                  Disubmit: {{ (reg as any).submission.submitted_at }}
                </p>
              </div>

              <!-- No submission yet -->
              <div v-else class="flex flex-col items-center justify-center py-5 text-center">
                <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/[0.07] flex items-center justify-center mb-2">
                  <svg class="w-4 h-4 text-white/15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <p class="text-xs text-white/25 font-medium">Karya belum disubmit</p>
                <p v-if="reg.status !== 'verified'" class="text-[10px] text-white/15 mt-0.5">
                  {{ reg.status === 'rejected' ? 'Pembayaran ditolak' : 'Menunggu verifikasi pembayaran' }}
                </p>
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
