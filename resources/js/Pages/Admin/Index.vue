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
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600
                      flex items-center justify-center text-sm font-bold">S</div>
          <div>
            <p class="text-sm font-bold leading-none">S-Tech Admin</p>
            <p class="text-[10px] text-white/35 mt-0.5">Panel Verifikasi</p>
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

    <div class="max-w-7xl mx-auto px-6 py-8">

      <!-- Page title + stats -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-white mb-1">Panel Admin</h1>
        <p class="text-sm text-white/40">Kelola dan verifikasi pembayaran peserta S-Tech 2026</p>
      </div>

      <!-- Stats cards -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-8">
        <div v-for="stat in [
          { label: 'Total Pendaftar',       value: props.stats.total,                color: 'border-white/[0.07]' },
          { label: 'Belum Bayar',           value: props.stats.pending_payment,      color: 'border-amber-500/20' },
          { label: 'Menunggu Verifikasi',   value: props.stats.pending_verification, color: 'border-violet-500/20' },
          { label: 'Terverifikasi',         value: props.stats.verified,             color: 'border-green-500/20' },
          { label: 'Ditolak',               value: props.stats.rejected,             color: 'border-red-500/20' },
        ]" :key="stat.label"
             class="p-4 rounded-2xl border bg-white/[0.03]" :class="stat.color">
          <p class="text-2xl font-bold text-white mb-1">{{ stat.value }}</p>
          <p class="text-xs text-white/40">{{ stat.label }}</p>
        </div>
      </div>

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
                  <a :href="`/admin/peserta/${reg.id}`"
                     class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium
                            border border-white/10 text-white/60 hover:text-white hover:bg-white/5
                            transition-all duration-200">
                    Detail →
                  </a>
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
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
</style>
