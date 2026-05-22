<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

// ── Types ────────────────────────────────────────────────────────────────────
interface LoginForm {
  email: string
  password: string
  loading: boolean
  error: string
}

// ── State ────────────────────────────────────────────────────────────────────
const showLoginModal = ref(false)
const heroVisible    = ref(false)
const sectionsVisible = ref(false)

const form = ref<LoginForm>({
  email:    '',
  password: '',
  loading:  false,
  error:    '',
})

// ── Competitions ─────────────────────────────────────────────────────────────
const competitions = [
  {
    id: 'web-design',
    icon: '⬡',
    title: 'Web Design',
    description: 'Rancang antarmuka yang elegan & fungsional. Tunjukkan kreativitas terbaikmu.',
    tag: 'Design',
    slots: 32,
    prize: 'Rp 5.000.000',
  },
  {
    id: 'competitive-programming',
    icon: '◈',
    title: 'Competitive Programming',
    description: 'Selesaikan soal algoritmik dengan kecepatan dan presisi tertinggi.',
    tag: 'Algorithm',
    slots: 64,
    prize: 'Rp 7.500.000',
  },
  {
    id: 'uiux-challenge',
    icon: '◻',
    title: 'UI/UX Challenge',
    description: 'Ciptakan pengalaman pengguna yang intuitif dan berpusat pada manusia.',
    tag: 'UX Research',
    slots: 40,
    prize: 'Rp 4.000.000',
  },
  {
    id: 'ctf',
    icon: '⬟',
    title: 'Capture The Flag',
    description: 'Temukan kerentanan, pecahkan tantangan keamanan siber nyata.',
    tag: 'Security',
    slots: 48,
    prize: 'Rp 6.000.000',
  },
  {
    id: 'data-analytics',
    icon: '◆',
    title: 'Data Analytics',
    description: 'Ubah data mentah menjadi wawasan strategis yang dapat ditindaklanjuti.',
    tag: 'Data Science',
    slots: 36,
    prize: 'Rp 5.500.000',
  },
  {
    id: 'iot',
    icon: '⬢',
    title: 'IoT Innovation',
    description: 'Bangun solusi Internet of Things untuk permasalahan kehidupan nyata.',
    tag: 'Hardware',
    slots: 28,
    prize: 'Rp 6.500.000',
  },
]

// ── Timeline ──────────────────────────────────────────────────────────────────
const timeline = [
  { date: '1 Jun 2026', label: 'Pendaftaran Dibuka' },
  { date: '30 Jun 2026', label: 'Pendaftaran Ditutup' },
  { date: '5 Jul 2026', label: 'Technical Meeting' },
  { date: '15 Jul 2026', label: 'Babak Penyisihan' },
  { date: '22 Jul 2026', label: 'Grand Final & Award Night' },
]

// ── Methods ───────────────────────────────────────────────────────────────────
function openLoginModal() {
  showLoginModal.value = true
}

function closeLoginModal() {
  showLoginModal.value = false
  form.value.error = ''
}

function submitLogin() {
  if (!form.value.email || !form.value.password) {
    form.value.error = 'Email dan password wajib diisi.'
    return
  }
  form.value.loading = true
  form.value.error   = ''

  router.post(
    '/login',
    { email: form.value.email, password: form.value.password },
    {
      onError: () => {
        form.value.loading = false
        form.value.error   = 'Login gagal. Silakan coba lagi.'
      },
    },
  )
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  // Check if we were redirected here to open the login modal
  const params = new URLSearchParams(window.location.search)
  if (params.get('login') === '1') {
    showLoginModal.value = true
  }

  // Staggered entrance animations
  requestAnimationFrame(() => {
    setTimeout(() => { heroVisible.value = true }, 80)
    setTimeout(() => { sectionsVisible.value = true }, 400)
  })
})
</script>

<template>
  <div class="min-h-screen bg-[#0a0a0a] text-white font-['Inter',sans-serif] overflow-x-hidden">

    <!-- ═══════════════════════════════════════════════════
         NAV
    ════════════════════════════════════════════════════ -->
    <header class="fixed top-0 inset-x-0 z-50 flex items-center justify-between px-6 md:px-12 h-16
                   border-b border-white/[0.06] bg-[#0a0a0a]/80 backdrop-blur-xl">
      <!-- Logo -->
      <a href="/" class="flex items-center gap-3 group">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600
                    flex items-center justify-center text-sm font-bold shadow-lg
                    group-hover:shadow-violet-500/30 transition-shadow duration-300">
          S
        </div>
        <span class="text-sm font-semibold tracking-tight">S-Tech <span class="text-white/40">'26</span></span>
      </a>

      <!-- Nav Links -->
      <nav class="hidden md:flex items-center gap-8 text-sm text-white/50">
        <a href="#competitions" class="hover:text-white transition-colors duration-200">Lomba</a>
        <a href="#timeline"     class="hover:text-white transition-colors duration-200">Timeline</a>
        <a href="#about"        class="hover:text-white transition-colors duration-200">Tentang</a>
      </nav>

      <!-- CTA -->
      <button
        @click="openLoginModal"
        id="nav-login-btn"
        class="px-4 py-2 text-sm font-medium rounded-lg border border-white/10
               bg-white/5 hover:bg-white/10 hover:border-white/20
               transition-all duration-200 active:scale-95"
      >
        Masuk
      </button>
    </header>

    <!-- ═══════════════════════════════════════════════════
         HERO
    ════════════════════════════════════════════════════ -->
    <section class="relative flex flex-col items-center justify-center text-center
                    pt-40 pb-32 px-6 min-h-screen overflow-hidden">

      <!-- Background glow -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2
                    w-[700px] h-[500px] bg-violet-600/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/4 w-[400px] h-[300px]
                    bg-indigo-500/8 rounded-full blur-3xl"></div>
      </div>

      <!-- Grid texture overlay -->
      <div class="absolute inset-0 pointer-events-none opacity-[0.025]"
           style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px),
                                    linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px);
                  background-size: 60px 60px;">
      </div>

      <Transition
        enter-active-class="transition-all duration-700 ease-out"
        enter-from-class="opacity-0 translate-y-6"
        enter-to-class="opacity-100 translate-y-0"
      >
        <div v-if="heroVisible">
          <!-- Badge -->
          <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-8 rounded-full
                      border border-violet-500/30 bg-violet-500/10 text-violet-300 text-xs font-medium">
            <span class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-pulse"></span>
            Pendaftaran Dibuka · 2026
          </div>

          <!-- Headline -->
          <h1 class="text-5xl md:text-7xl lg:text-8xl font-extrabold tracking-tight leading-[1.05] mb-6">
            <span class="text-white">Soedirman</span><br>
            <span class="bg-gradient-to-r from-violet-400 via-indigo-300 to-violet-500
                         bg-clip-text text-transparent">Technophoria</span>
          </h1>

          <!-- Sub-headline -->
          <p class="max-w-xl mx-auto text-lg text-white/50 leading-relaxed mb-10">
            Ajang kompetisi teknologi tahunan bergengsi. Buktikan kemampuanmu
            di depan para ahli industri dan raih hadiah jutaan rupiah.
          </p>

          <!-- CTAs -->
          <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <button
              @click="openLoginModal"
              id="hero-register-btn"
              class="group relative px-7 py-3.5 text-sm font-semibold rounded-xl
                     bg-violet-600 hover:bg-violet-500 text-white
                     shadow-lg shadow-violet-600/30 hover:shadow-violet-500/40
                     transition-all duration-200 active:scale-[0.98]"
            >
              <span class="relative z-10">Daftar Sekarang →</span>
            </button>
            <a href="#competitions"
               class="px-7 py-3.5 text-sm font-medium rounded-xl
                      border border-white/10 text-white/70 hover:text-white
                      hover:border-white/20 hover:bg-white/5
                      transition-all duration-200 active:scale-[0.98]">
              Lihat Lomba
            </a>
          </div>

          <!-- Stats row -->
          <div class="flex items-center justify-center gap-10 mt-16 text-center">
            <div>
              <p class="text-3xl font-bold text-white">6</p>
              <p class="text-xs text-white/40 mt-1">Kategori Lomba</p>
            </div>
            <div class="w-px h-8 bg-white/10"></div>
            <div>
              <p class="text-3xl font-bold text-white">248</p>
              <p class="text-xs text-white/40 mt-1">Slot Tersedia</p>
            </div>
            <div class="w-px h-8 bg-white/10"></div>
            <div>
              <p class="text-3xl font-bold text-white">34<span class="text-violet-400">jt</span></p>
              <p class="text-xs text-white/40 mt-1">Total Hadiah</p>
            </div>
          </div>
        </div>
      </Transition>
    </section>

    <!-- ═══════════════════════════════════════════════════
         COMPETITIONS
    ════════════════════════════════════════════════════ -->
    <Transition
      enter-active-class="transition-all duration-700 ease-out delay-100"
      enter-from-class="opacity-0 translate-y-8"
      enter-to-class="opacity-100 translate-y-0"
    >
      <section v-if="sectionsVisible" id="competitions" class="px-6 md:px-12 py-24 max-w-7xl mx-auto">
        <!-- Section header -->
        <div class="flex items-end justify-between mb-12">
          <div>
            <p class="text-xs font-medium text-violet-400 uppercase tracking-widest mb-2">Kompetisi</p>
            <h2 class="text-3xl md:text-4xl font-bold text-white">Kategori Lomba</h2>
            <p class="text-white/40 mt-2">Pilih bidang terbaik yang mencerminkan keahlianmu.</p>
          </div>
          <span class="hidden md:block text-sm text-white/30">6 Kategori Aktif</span>
        </div>

        <!-- Cards grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="comp in competitions"
            :key="comp.id"
            :id="`comp-card-${comp.id}`"
            class="group relative p-6 rounded-2xl border border-white/[0.07]
                   bg-white/[0.03] hover:bg-white/[0.06] hover:border-white/[0.12]
                   transition-all duration-300 cursor-pointer"
          >
            <!-- Icon -->
            <div class="w-10 h-10 mb-5 rounded-xl border border-white/10 bg-white/5
                        flex items-center justify-center text-xl
                        group-hover:border-violet-500/30 group-hover:bg-violet-500/10
                        transition-all duration-300">
              {{ comp.icon }}
            </div>

            <!-- Tag -->
            <span class="inline-block px-2.5 py-0.5 mb-3 rounded-full text-[10px] font-medium
                         border border-white/10 text-white/50 bg-white/5">
              {{ comp.tag }}
            </span>

            <!-- Title & desc -->
            <h3 class="text-base font-semibold text-white mb-2 group-hover:text-violet-200
                       transition-colors duration-200">{{ comp.title }}</h3>
            <p class="text-sm text-white/40 leading-relaxed">{{ comp.description }}</p>

            <!-- Footer -->
            <div class="flex items-center justify-between mt-6 pt-5 border-t border-white/[0.06]">
              <span class="text-xs text-white/30">{{ comp.slots }} slot</span>
              <span class="text-xs font-semibold text-violet-300">{{ comp.prize }}</span>
            </div>

            <!-- Hover accent line -->
            <div class="absolute left-0 top-6 bottom-6 w-px bg-violet-500/0
                        group-hover:bg-violet-500/60 rounded-r transition-all duration-300"></div>
          </div>
        </div>
      </section>
    </Transition>

    <!-- ═══════════════════════════════════════════════════
         TIMELINE
    ════════════════════════════════════════════════════ -->
    <Transition
      enter-active-class="transition-all duration-700 ease-out delay-200"
      enter-from-class="opacity-0 translate-y-8"
      enter-to-class="opacity-100 translate-y-0"
    >
      <section v-if="sectionsVisible" id="timeline" class="px-6 md:px-12 py-24 bg-white/[0.015]
                border-y border-white/[0.05]">
        <div class="max-w-4xl mx-auto">
          <div class="mb-12 text-center">
            <p class="text-xs font-medium text-violet-400 uppercase tracking-widest mb-2">Jadwal</p>
            <h2 class="text-3xl md:text-4xl font-bold text-white">Timeline Acara</h2>
          </div>

          <!-- Timeline track -->
          <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-8 md:gap-0">
            <!-- Connecting line (desktop) -->
            <div class="hidden md:block absolute top-5 left-0 right-0 h-px bg-white/10"></div>

            <div
              v-for="(item, index) in timeline"
              :key="index"
              class="relative flex flex-col items-center text-center flex-1 px-2"
            >
              <!-- Node -->
              <div :class="[
                'w-10 h-10 rounded-full border-2 flex items-center justify-center mb-3 z-10',
                index === 0
                  ? 'border-violet-500 bg-violet-500/20 text-violet-400'
                  : 'border-white/20 bg-[#0a0a0a] text-white/30',
              ]">
                <span class="text-xs font-bold">{{ index + 1 }}</span>
              </div>
              <p class="text-[11px] font-semibold text-white/80 leading-snug">{{ item.label }}</p>
              <p class="text-[10px] text-white/30 mt-1">{{ item.date }}</p>
            </div>
          </div>
        </div>
      </section>
    </Transition>

    <!-- ═══════════════════════════════════════════════════
         FOOTER
    ════════════════════════════════════════════════════ -->
    <footer class="px-6 md:px-12 py-12 text-center text-xs text-white/20">
      <p>© 2026 Soedirman Technophoria · Universitas Jenderal Soedirman</p>
      <p class="mt-1">Dibuat dengan ❤️ oleh Panitia S-Tech</p>
    </footer>

    <!-- ═══════════════════════════════════════════════════
         LOGIN MODAL
    ════════════════════════════════════════════════════ -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="showLoginModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4
               bg-black/70 backdrop-blur-md"
        @click.self="closeLoginModal"
      >
        <Transition
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 scale-95 translate-y-2"
          enter-to-class="opacity-100 scale-100 translate-y-0"
        >
          <div
            v-if="showLoginModal"
            class="relative w-full max-w-sm rounded-2xl border border-white/10
                   bg-[#111111] shadow-2xl shadow-black/60 p-8"
          >
            <!-- Close button -->
            <button
              @click="closeLoginModal"
              id="modal-close-btn"
              class="absolute top-4 right-4 w-7 h-7 rounded-lg border border-white/10
                     text-white/40 hover:text-white hover:border-white/20
                     flex items-center justify-center text-xs transition-all duration-200"
            >✕</button>

            <!-- Brand -->
            <div class="flex items-center gap-2.5 mb-8">
              <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600
                          flex items-center justify-center text-sm font-bold">S</div>
              <span class="text-sm font-semibold">S-Tech <span class="text-white/40">Portal</span></span>
            </div>

            <h3 class="text-xl font-bold text-white mb-1">Selamat datang kembali</h3>
            <p class="text-sm text-white/40 mb-8">Masuk untuk melanjutkan pendaftaranmu.</p>

            <!-- Error -->
            <div
              v-if="form.error"
              class="mb-5 px-4 py-3 rounded-xl border border-red-500/20 bg-red-500/10 text-red-400 text-sm"
            >{{ form.error }}</div>

            <!-- Form -->
            <form @submit.prevent="submitLogin" class="space-y-4">
              <div>
                <label class="block text-xs font-medium text-white/50 mb-2">Email</label>
                <input
                  v-model="form.email"
                  id="login-email"
                  type="email"
                  placeholder="kamu@email.com"
                  autocomplete="email"
                  class="w-full px-4 py-3 rounded-xl border border-white/10 bg-white/5
                         text-sm text-white placeholder-white/20
                         focus:outline-none focus:border-violet-500/60 focus:bg-white/8
                         transition-all duration-200"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-white/50 mb-2">Password</label>
                <input
                  v-model="form.password"
                  id="login-password"
                  type="password"
                  placeholder="••••••••"
                  autocomplete="current-password"
                  class="w-full px-4 py-3 rounded-xl border border-white/10 bg-white/5
                         text-sm text-white placeholder-white/20
                         focus:outline-none focus:border-violet-500/60 focus:bg-white/8
                         transition-all duration-200"
                />
              </div>

              <button
                type="submit"
                id="login-submit-btn"
                :disabled="form.loading"
                class="w-full py-3 rounded-xl text-sm font-semibold
                       bg-violet-600 hover:bg-violet-500 text-white
                       shadow-lg shadow-violet-600/25 hover:shadow-violet-500/35
                       transition-all duration-200 active:scale-[0.98]
                       disabled:opacity-60 disabled:cursor-not-allowed
                       flex items-center justify-center gap-2"
              >
                <svg v-if="form.loading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                {{ form.loading ? 'Memproses...' : 'Masuk ke Dashboard' }}
              </button>
            </form>

            <p class="mt-6 text-center text-xs text-white/30">
              Demo: masukkan email & password apa saja untuk lanjut.
            </p>
          </div>
        </Transition>
      </div>
    </Transition>

  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
</style>
