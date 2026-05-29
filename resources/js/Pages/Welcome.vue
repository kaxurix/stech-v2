<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

// ── Types ─────────────────────────────────────────────────────────────────────
interface LoginForm {
  email: string; password: string; loading: boolean; error: string
}
interface RegisterForm {
  name: string; email: string; password: string; password_confirmation: string
  team_name: string; competition: string; member_count: number
  institution: string; phone: string; category: string
  loading: boolean; errors: Record<string, string>
}

// ── Page props ────────────────────────────────────────────────────────────────
const page = usePage()

// ── State ─────────────────────────────────────────────────────────────────────
const showModal     = ref(false)
const modalTab      = ref<'login' | 'register'>('login') // aktif tab
const heroVisible   = ref(false)
const cardsVisible  = ref(false)
const mobileMenuOpen = ref(false)

const loginForm = ref<LoginForm>({
  email: '', password: '', loading: false, error: '',
})

const registerForm = ref<RegisterForm>({
  name: '', email: '', password: '', password_confirmation: '',
  team_name: '', competition: 'web-development', member_count: 2,
  institution: '', phone: '', category: 'mahasiswa',
  loading: false, errors: {},
})

// ── Timeline ─────────────────────────────────────────────────────────────────
const timeline = [
  { date: '1 Jun 2026',  label: 'Pendaftaran Dibuka',  done: true  },
  { date: '30 Jun 2026', label: 'Batas Pendaftaran',   done: false },
  { date: '5 Jul 2026',  label: 'Technical Meeting',   done: false },
  { date: '15 Jul 2026', label: 'Penyisihan Online',   done: false },
  { date: '22 Jul 2026', label: 'Grand Final & Seminar Nasional', done: false },
]

const seminarTopics = [
  { icon: '◆', label: 'Web Development Trends 2026' },
  { icon: '⬟', label: 'Career Path di Industri Tech' },
  { icon: '◇', label: 'Networking & Kolaborasi Industri' },
]

// ── Methods ───────────────────────────────────────────────────────────────────
function openModal(tab: 'login' | 'register' = 'login') {
  modalTab.value = tab
  showModal.value = true
  mobileMenuOpen.value = false
  loginForm.value.error = ''
  registerForm.value.errors = {}
}
function closeModal() {
  showModal.value = false
  loginForm.value.error = ''
  registerForm.value.errors = {}
}

function submitLogin() {
  if (!loginForm.value.email || !loginForm.value.password) {
    loginForm.value.error = 'Email dan password wajib diisi.'
    return
  }
  loginForm.value.loading = true
  loginForm.value.error = ''
  router.post('/login',
    { email: loginForm.value.email, password: loginForm.value.password },
    {
      onError: (errors) => {
        loginForm.value.loading = false
        loginForm.value.error = errors.email || 'Login gagal. Periksa email dan password.'
      },
    }
  )
}

function submitRegister() {
  registerForm.value.loading = true
  registerForm.value.errors = {}
  router.post('/register', {
    name:                  registerForm.value.name,
    email:                 registerForm.value.email,
    password:              registerForm.value.password,
    password_confirmation: registerForm.value.password_confirmation,
    team_name:             registerForm.value.team_name,
    competition:           registerForm.value.competition,
    member_count:          registerForm.value.member_count,
    institution:           registerForm.value.institution,
    phone:                 registerForm.value.phone,
    category:              registerForm.value.category,
  }, {
    onError: (errors) => {
      registerForm.value.loading = false
      registerForm.value.errors = errors
    },
  })
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  if (params.get('login') === '1')    openModal('login')
  if (params.get('register') === '1') openModal('register')

  requestAnimationFrame(() => {
    setTimeout(() => { heroVisible.value  = true }, 60)
    setTimeout(() => { cardsVisible.value = true }, 350)
  })
})
</script>

<template>
  <div class="min-h-screen bg-[#080808] text-white font-['Inter',sans-serif] overflow-x-hidden">

    <!-- ══════════════════════════════════════════════
         NAV
    ═══════════════════════════════════════════════ -->
    <header class="fixed top-0 inset-x-0 z-50 border-b border-white/[0.06]
                   bg-[#080808]/85 backdrop-blur-xl">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">

        <!-- Logo -->
        <a href="/" class="flex items-center group flex-shrink-0">
          <span class="text-base font-bold tracking-tight bg-gradient-to-r from-white via-white to-white/70 bg-clip-text text-transparent">
            Soedirman Technophoria
          </span>
        </a>

        <!-- Desktop nav -->
        <nav class="hidden md:flex items-center gap-7 text-sm text-white/50">
          <a href="#about"       class="hover:text-white transition-colors duration-200">Tentang</a>
          <a href="#competition" class="hover:text-white transition-colors duration-200">Lomba</a>
          <a href="#seminar"     class="hover:text-white transition-colors duration-200">Seminar</a>
          <a href="#timeline"    class="hover:text-white transition-colors duration-200">Timeline</a>
        </nav>

        <!-- Desktop CTA -->
        <div class="hidden md:flex items-center gap-3">
          <button @click="openModal('login')" id="nav-login-btn"
                  class="px-4 py-2 text-sm font-medium rounded-lg border border-white/10
                         text-white/70 hover:text-white hover:bg-white/5 hover:border-white/20
                         transition-all duration-200">
            Masuk
          </button>
          <button @click="openModal('register')" id="nav-register-btn"
                  class="px-4 py-2 text-sm font-semibold rounded-lg
                         bg-violet-600 hover:bg-violet-500 text-white
                         shadow-md shadow-violet-600/20 transition-all duration-200 active:scale-95">
            Daftar
          </button>
        </div>

        <!-- Mobile hamburger -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" id="mobile-menu-btn"
                class="md:hidden w-9 h-9 flex flex-col items-center justify-center gap-1.5
                       rounded-lg border border-white/10 hover:bg-white/5 transition-all duration-200">
          <span :class="['block w-5 h-0.5 bg-white/60 rounded transition-all duration-300',
                         mobileMenuOpen ? 'rotate-45 translate-y-2' : '']"></span>
          <span :class="['block w-5 h-0.5 bg-white/60 rounded transition-all duration-300',
                         mobileMenuOpen ? 'opacity-0' : '']"></span>
          <span :class="['block w-5 h-0.5 bg-white/60 rounded transition-all duration-300',
                         mobileMenuOpen ? '-rotate-45 -translate-y-2' : '']"></span>
        </button>
      </div>

      <!-- Mobile menu -->
      <Transition enter-active-class="transition-all duration-300 ease-out"
                  enter-from-class="opacity-0 -translate-y-2"
                  enter-to-class="opacity-100 translate-y-0"
                  leave-active-class="transition-all duration-200 ease-in"
                  leave-from-class="opacity-100 translate-y-0"
                  leave-to-class="opacity-0 -translate-y-2">
        <div v-if="mobileMenuOpen"
             class="md:hidden border-t border-white/[0.06] bg-[#0d0d0d] px-4 py-4 space-y-1">
          <a href="#about"       @click="mobileMenuOpen=false"
             class="block px-3 py-2.5 text-sm text-white/60 hover:text-white hover:bg-white/5 rounded-lg transition-all">Tentang</a>
          <a href="#competition" @click="mobileMenuOpen=false"
             class="block px-3 py-2.5 text-sm text-white/60 hover:text-white hover:bg-white/5 rounded-lg transition-all">Lomba Web Dev</a>
          <a href="#seminar"     @click="mobileMenuOpen=false"
             class="block px-3 py-2.5 text-sm text-white/60 hover:text-white hover:bg-white/5 rounded-lg transition-all">Seminar</a>
          <a href="#timeline"    @click="mobileMenuOpen=false"
             class="block px-3 py-2.5 text-sm text-white/60 hover:text-white hover:bg-white/5 rounded-lg transition-all">Timeline</a>
          <div class="pt-3 flex flex-col gap-2">
            <button @click="openModal('register')"
                    class="w-full py-3 text-sm font-semibold rounded-xl
                           bg-violet-600 hover:bg-violet-500 text-white transition-all duration-200">
              Daftar Sekarang
            </button>
          </div>
        </div>
      </Transition>
    </header>

    <!-- ══════════════════════════════════════════════
         HERO
    ═══════════════════════════════════════════════ -->
    <section class="relative flex flex-col items-center justify-center text-center
                    pt-36 pb-24 px-4 sm:px-6 min-h-screen overflow-hidden">
      <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2
                    w-[600px] h-[400px] md:w-[900px] md:h-[600px]
                    bg-violet-700/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 left-1/4 w-[300px] h-[300px]
                    bg-indigo-600/8 rounded-full blur-3xl"></div>
      </div>
      <div class="absolute inset-0 pointer-events-none opacity-[0.025]"
           style="background-image: linear-gradient(rgba(255,255,255,0.4) 1px, transparent 1px),
                                    linear-gradient(90deg, rgba(255,255,255,0.4) 1px, transparent 1px);
                  background-size: 56px 56px;"></div>

      <Transition enter-active-class="transition-all duration-700 ease-out"
                  enter-from-class="opacity-0 translate-y-8"
                  enter-to-class="opacity-100 translate-y-0">
        <div v-if="heroVisible" class="relative z-10 max-w-4xl mx-auto">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-6 sm:mb-8 rounded-full
                      border border-violet-500/25 bg-violet-500/8 text-violet-300 text-xs font-medium">
            <span class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-pulse"></span>
            Event Tahunan · Informatika Unsoed · 2026
          </div>
          <h1 class="text-4xl sm:text-6xl md:text-7xl lg:text-8xl font-extrabold
                     tracking-tight leading-[1.05] mb-5 sm:mb-6">
            <span class="text-white">Soedirman</span><br>
            <span class="bg-gradient-to-r from-violet-400 via-indigo-300 to-violet-500
                         bg-clip-text text-transparent">Technophoria</span>
          </h1>
          <p class="max-w-2xl mx-auto text-sm sm:text-base md:text-lg text-white/45
                    leading-relaxed mb-4 px-2">
            Event teknologi tahunan yang mempertemukan pelajar SMA/SMK, mahasiswa,
            dan masyarakat umum dalam satu panggung kompetisi dan edukasi.
            Buktikan kemampuanmu di
            <span class="text-white/70 font-medium">Lomba Web Development</span>
            dan perluas wawasanmu lewat
            <span class="text-white/70 font-medium">Seminar Nasional</span>.
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-8 sm:mt-10">
            <button @click="openModal('register')" id="hero-register-btn"
                    class="w-full sm:w-auto px-7 py-3.5 text-sm font-semibold rounded-xl
                           bg-violet-600 hover:bg-violet-500 text-white
                           shadow-lg shadow-violet-600/25 hover:shadow-violet-500/40
                           transition-all duration-200 active:scale-[0.98]">
              Daftar Sekarang →
            </button>
            <a href="https://drive.google.com/your-guidebook-link" target="_blank" rel="noopener noreferrer"
               class="w-full sm:w-auto px-7 py-3.5 text-sm font-medium text-center rounded-xl
                      border border-violet-500/30 text-violet-300 hover:text-white
                      hover:border-violet-500/60 hover:bg-violet-500/10 transition-all duration-200
                      flex items-center justify-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Unduh Guidebook
            </a>
          </div>
          <div class="flex flex-wrap items-center justify-center gap-3 mt-12 sm:mt-14">
            <span class="px-4 py-2 rounded-full border border-white/10 bg-white/5 text-xs text-white/50 font-medium">🎓 Mahasiswa</span>
            <span class="px-4 py-2 rounded-full border border-white/10 bg-white/5 text-xs text-white/50 font-medium">📚 Pelajar SMA / SMK</span>
            <span class="px-4 py-2 rounded-full border border-white/10 bg-white/5 text-xs text-white/50 font-medium">🌐 Masyarakat Umum</span>
          </div>
        </div>
      </Transition>
    </section>

    <!-- ══════════════════════════════════════════════
         ABOUT
    ═══════════════════════════════════════════════ -->
    <Transition enter-active-class="transition-all duration-700 ease-out"
                enter-from-class="opacity-0 translate-y-8"
                enter-to-class="opacity-100 translate-y-0">
      <section v-if="cardsVisible" id="about"
               class="px-4 sm:px-6 py-20 sm:py-24 border-t border-white/[0.05] bg-white/[0.015]">
        <div class="max-w-6xl mx-auto">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <div>
              <p class="text-xs font-semibold text-violet-400 uppercase tracking-widest mb-3">Tentang S-Tech</p>
              <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-5 leading-snug">
                Ruang Bertumbuh bagi<br>Generasi Teknologi Indonesia
              </h2>
              <p class="text-sm sm:text-base text-white/50 leading-relaxed mb-5">
                Soedirman Technophoria (S-Tech) adalah event tahunan yang diselenggarakan
                oleh Himpunan Mahasiswa Informatika Universitas Jenderal Soedirman.
                Melalui <strong class="text-white/80">Lomba Web Development</strong> dan
                <strong class="text-white/80">Seminar Nasional</strong>, S-Tech hadir sebagai
                wadah bagi pelajar, mahasiswa, dan masyarakat untuk berkompetisi,
                belajar, dan tumbuh bersama di era digital.
              </p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-1 gap-3">
              <div v-for="(item, i) in [
                { icon: '🎯', title: 'Edukasi Teknologi', desc: 'Mengenalkan tren dan praktik teknologi terkini' },
                { icon: '🤝', title: 'Networking',         desc: 'Membangun koneksi antar mahasiswa & industri' },
                { icon: '💡', title: 'Kreativitas',        desc: 'Mendorong inovasi solusi berbasis web' },
              ]" :key="i"
                   class="flex items-start gap-4 p-4 rounded-2xl border border-white/[0.07]
                          bg-white/[0.03] hover:bg-white/[0.05] transition-all duration-300">
                <span class="text-2xl flex-shrink-0">{{ item.icon }}</span>
                <div>
                  <p class="text-sm font-semibold text-white mb-0.5">{{ item.title }}</p>
                  <p class="text-xs text-white/40 leading-relaxed">{{ item.desc }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </Transition>

    <!-- ══════════════════════════════════════════════
         COMPETITION
    ═══════════════════════════════════════════════ -->
    <Transition enter-active-class="transition-all duration-700 ease-out delay-75"
                enter-from-class="opacity-0 translate-y-8"
                enter-to-class="opacity-100 translate-y-0">
      <section v-if="cardsVisible" id="competition"
               class="px-4 sm:px-6 py-20 sm:py-24 max-w-6xl mx-auto">
        <div class="mb-10 sm:mb-12">
          <p class="text-xs font-semibold text-violet-400 uppercase tracking-widest mb-2">Cabang Lomba</p>
          <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white">Lomba Web Development</h2>
          <p class="text-sm sm:text-base text-white/40 mt-2 max-w-xl">
            Tunjukkan kemampuan membangun solusi web inovatif di hadapan para juri profesional.
          </p>
        </div>
        <div class="relative overflow-hidden rounded-3xl border border-violet-500/20
                    bg-gradient-to-br from-violet-900/20 via-[#0d0b1a] to-[#080808] p-6 sm:p-8 lg:p-10 mb-6">
          <div class="absolute top-0 right-0 w-64 h-64 bg-violet-600/10 rounded-full blur-3xl pointer-events-none"></div>
          <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">
            <div>
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full
                          border border-violet-500/25 bg-violet-500/10 text-violet-300 text-xs font-medium mb-5">
                <span class="w-1.5 h-1.5 rounded-full bg-violet-400"></span>
                Lomba Utama · S-Tech 2026
              </div>
              <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-4">
                Web Development<br>
                <span class="text-violet-300">Challenge 2026</span>
              </h3>
              <p class="text-sm text-white/50 leading-relaxed mb-6">
                Tunjukkan kemampuan terbaikmu dalam merancang dan membangun
                aplikasi web yang inovatif, fungsional, dan berdampak nyata.
              </p>
              <div class="flex flex-wrap gap-3">
                <div class="px-4 py-2 rounded-xl border border-white/10 bg-white/5">
                  <p class="text-[10px] text-white/35 mb-0.5">Peserta</p>
                  <p class="text-xs font-semibold text-white">SMA/SMK & Mahasiswa</p>
                </div>
                <div class="px-4 py-2 rounded-xl border border-white/10 bg-white/5">
                  <p class="text-[10px] text-white/35 mb-0.5">Format Tim</p>
                  <p class="text-xs font-semibold text-white">2–3 Orang / Tim</p>
                </div>
                <div class="px-4 py-2 rounded-xl border border-white/10 bg-white/5">
                  <p class="text-[10px] text-white/35 mb-0.5">Biaya Daftar</p>
                  <p class="text-xs font-semibold text-white">Rp 75.000 / Tim</p>
                </div>
              </div>
            </div>
            <div class="space-y-4">
              <div class="p-6 rounded-2xl border border-amber-500/20 bg-amber-500/5">
                <p class="text-xs text-amber-400/70 mb-2 uppercase tracking-wider font-semibold">Total Hadiah</p>
                <p class="text-4xl font-extrabold text-amber-300 mb-1">Rp 5 Jt+</p>
                <p class="text-sm text-white/40">Juara 1 · Juara 2 · Juara 3</p>
                <div class="mt-4 pt-4 border-t border-amber-500/10 flex flex-wrap gap-2">
                  <span class="px-2.5 py-1 rounded-lg border border-amber-500/20 text-[10px] text-amber-300/70">🏆 Trophy</span>
                  <span class="px-2.5 py-1 rounded-lg border border-amber-500/20 text-[10px] text-amber-300/70">📜 E-Certificate</span>
                  <span class="px-2.5 py-1 rounded-lg border border-amber-500/20 text-[10px] text-amber-300/70">🤝 Networking</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <button @click="openModal('register')"
                class="w-full sm:w-auto px-8 py-3.5 text-sm font-semibold rounded-xl
                       bg-violet-600 hover:bg-violet-500 text-white
                       shadow-lg shadow-violet-600/25 transition-all duration-200 active:scale-[0.98]">
          Daftar Lomba Sekarang →
        </button>
      </section>
    </Transition>

    <!-- ══════════════════════════════════════════════
         SEMINAR
    ═══════════════════════════════════════════════ -->
    <Transition enter-active-class="transition-all duration-700 ease-out delay-100"
                enter-from-class="opacity-0 translate-y-8"
                enter-to-class="opacity-100 translate-y-0">
      <section v-if="cardsVisible" id="seminar"
               class="px-4 sm:px-6 py-20 sm:py-24 border-t border-white/[0.05] bg-white/[0.015]">
        <div class="max-w-6xl mx-auto">
          <div class="mb-10 sm:mb-12">
            <p class="text-xs font-semibold text-indigo-400 uppercase tracking-widest mb-2">Acara Pendukung</p>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white">Seminar Nasional</h2>
            <p class="text-sm sm:text-base text-white/40 mt-2 max-w-xl">
              Sesi eksklusif bersama pembicara dari industri teknologi.
            </p>
          </div>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2 relative overflow-hidden rounded-3xl border border-indigo-500/20
                        bg-gradient-to-br from-indigo-900/15 via-[#0a0c1a] to-[#080808] p-6 sm:p-8">
              <div class="absolute top-0 right-0 w-48 h-48 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>
              <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full
                            border border-indigo-500/25 bg-indigo-500/10 text-indigo-300 text-xs font-medium mb-5">
                  <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                  Gratis untuk Mahasiswa Unsoed
                </div>
                <h3 class="text-xl sm:text-2xl font-bold text-white mb-3">Teknologi & Karir di Era Digital</h3>
                <p class="text-sm text-white/50 leading-relaxed mb-6">
                  Seminar nasional menghadirkan praktisi dan pakar industri untuk berbagi wawasan
                  tentang tren teknologi terkini dan peluang karir.
                </p>
                <div class="flex flex-wrap gap-3">
                  <div class="px-4 py-2 rounded-xl border border-white/10 bg-white/5">
                    <p class="text-[10px] text-white/35 mb-0.5">Tanggal</p>
                    <p class="text-xs font-semibold text-white">22 Juli 2026</p>
                  </div>
                  <div class="px-4 py-2 rounded-xl border border-white/10 bg-white/5">
                    <p class="text-[10px] text-white/35 mb-0.5">Format</p>
                    <p class="text-xs font-semibold text-white">Offline · Purwokerto</p>
                  </div>
                  <div class="px-4 py-2 rounded-xl border border-white/10 bg-white/5">
                    <p class="text-[10px] text-white/35 mb-0.5">Sertifikat</p>
                    <p class="text-xs font-semibold text-white">E-Certificate ✓</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="rounded-3xl border border-white/[0.07] bg-white/[0.03] p-6">
              <p class="text-xs font-semibold text-white/30 uppercase tracking-wider mb-4">Topik Bahasan</p>
              <div class="space-y-3">
                <div v-for="topic in seminarTopics" :key="topic.label"
                     class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 transition-all duration-200">
                  <span class="text-base text-indigo-300 flex-shrink-0">{{ topic.icon }}</span>
                  <p class="text-sm text-white/70">{{ topic.label }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </Transition>

    <!-- ══════════════════════════════════════════════
         TIMELINE
    ═══════════════════════════════════════════════ -->
    <Transition enter-active-class="transition-all duration-700 ease-out delay-150"
                enter-from-class="opacity-0 translate-y-8"
                enter-to-class="opacity-100 translate-y-0">
      <section v-if="cardsVisible" id="timeline"
               class="px-4 sm:px-6 py-20 sm:py-24 border-t border-white/[0.05]">
        <div class="max-w-6xl mx-auto">
          <div class="mb-10 sm:mb-14 text-center">
            <p class="text-xs font-semibold text-violet-400 uppercase tracking-widest mb-2">Jadwal</p>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white">Timeline Acara</h2>
          </div>
          <div class="hidden md:flex items-start relative">
            <div class="absolute top-5 left-0 right-0 h-px bg-white/8"></div>
            <div v-for="(item, i) in timeline" :key="i"
                 class="relative flex flex-col items-center text-center flex-1 px-2">
              <div :class="['w-10 h-10 rounded-full border-2 flex items-center justify-center z-10 mb-4',
                            item.done ? 'border-violet-500 bg-violet-500/20'
                            : i === 1 ? 'border-violet-500/70 bg-[#080808] ring-4 ring-violet-500/10'
                            : 'border-white/15 bg-[#080808]']">
                <svg v-if="item.done" class="w-4 h-4 text-violet-400"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span v-else class="text-xs font-bold"
                      :class="i === 1 ? 'text-violet-300' : 'text-white/25'">{{ i + 1 }}</span>
              </div>
              <p class="text-xs font-semibold leading-snug"
                 :class="item.done ? 'text-violet-300' : i === 1 ? 'text-white' : 'text-white/50'">
                {{ item.label }}
              </p>
              <p class="text-[10px] mt-1.5"
                 :class="item.done ? 'text-violet-400/60' : 'text-white/25'">
                {{ item.date }}
              </p>
            </div>
          </div>
        </div>
      </section>
    </Transition>

    <!-- ══════════════════════════════════════════════
         CTA BANNER
    ═══════════════════════════════════════════════ -->
    <Transition enter-active-class="transition-all duration-700 ease-out delay-200"
                enter-from-class="opacity-0 translate-y-8"
                enter-to-class="opacity-100 translate-y-0">
      <section v-if="cardsVisible" class="px-4 sm:px-6 py-16 sm:py-20 border-t border-white/[0.05]">
        <div class="max-w-3xl mx-auto text-center">
          <div class="relative overflow-hidden rounded-3xl border border-violet-500/20
                      bg-gradient-to-br from-violet-900/20 to-[#080808] p-8 sm:p-12">
            <div class="absolute inset-0 pointer-events-none">
              <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2
                          w-[400px] h-[200px] bg-violet-600/10 rounded-full blur-3xl"></div>
            </div>
            <div class="relative z-10">
              <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-3">
                Siap Menunjukkan Kemampuanmu?
              </h2>
              <p class="text-sm sm:text-base text-white/45 mb-8 max-w-md mx-auto">
                Daftar sekarang dan jadilah bagian dari generasi developer
                terbaik Informatika Unsoed.
              </p>
              <button @click="openModal('register')" id="cta-register-btn"
                      class="px-8 sm:px-10 py-3.5 text-sm font-semibold rounded-xl
                             bg-violet-600 hover:bg-violet-500 text-white
                             shadow-xl shadow-violet-600/30 hover:shadow-violet-500/40
                             transition-all duration-200 active:scale-[0.98]">
                Daftar Sekarang →
              </button>
            </div>
          </div>
        </div>
      </section>
    </Transition>

    <!-- ══════════════════════════════════════════════
         FOOTER
    ═══════════════════════════════════════════════ -->
    <footer class="px-4 sm:px-6 py-10 border-t border-white/[0.05]">
      <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center
                  justify-between gap-4 text-xs text-white/20">
        <div class="flex items-center gap-2.5">
          <span>Soedirman Technophoria 2026</span>
        </div>
        <p>Program Kerja · Informatika · Universitas Jenderal Soedirman</p>
      </div>
    </footer>

    <!-- ══════════════════════════════════════════════
         MODAL (LOGIN + REGISTER)
    ═══════════════════════════════════════════════ -->
    <Transition enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0">
      <div v-if="showModal"
           class="fixed inset-0 z-50 flex items-end sm:items-center justify-center
                  p-0 sm:p-4 bg-black/75 backdrop-blur-md"
           @click.self="closeModal">
        <Transition enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100">
          <div v-if="showModal"
               class="relative w-full sm:max-w-md rounded-t-3xl sm:rounded-2xl
                      border border-white/10 bg-[#111] shadow-2xl overflow-hidden
                      max-h-[90vh] flex flex-col">

            <!-- Mobile drag handle -->
            <div class="sm:hidden w-10 h-1 rounded-full bg-white/20 mx-auto mt-4 mb-2 flex-shrink-0"></div>

            <!-- Close btn -->
            <button @click="closeModal" id="modal-close-btn"
                    class="absolute top-4 right-4 w-7 h-7 rounded-lg z-10
                           border border-white/10 text-white/35 hover:text-white
                           flex items-center justify-center text-xs transition-all duration-200">✕</button>

            <!-- Tab switcher -->
            <div class="flex border-b border-white/10 mt-2 sm:mt-0 flex-shrink-0">
              <button @click="modalTab = 'login'"
                      :class="['flex-1 py-4 text-sm font-semibold transition-all duration-200',
                               modalTab === 'login'
                                 ? 'text-white border-b-2 border-violet-500'
                                 : 'text-white/40 hover:text-white/70']">
                Masuk
              </button>
              <button @click="modalTab = 'register'"
                      :class="['flex-1 py-4 text-sm font-semibold transition-all duration-200',
                               modalTab === 'register'
                                 ? 'text-white border-b-2 border-violet-500'
                                 : 'text-white/40 hover:text-white/70']">
                Daftar
              </button>
            </div>

            <!-- ── LOGIN TAB ── -->
            <div v-if="modalTab === 'login'" class="p-6 sm:p-8 overflow-y-auto">
              <div class="mb-6">
                <p class="text-sm font-bold leading-none text-white">Soedirman Technophoria</p>
                <p class="text-[10px] text-white/35 mt-1">Portal Peserta</p>
              </div>
              <h3 class="text-xl font-bold text-white mb-1">Masuk ke Akunmu</h3>
              <p class="text-sm text-white/40 mb-6">Lanjutkan proses pendaftaranmu.</p>

              <div v-if="loginForm.error"
                   class="mb-4 px-4 py-3 rounded-xl border border-red-500/20
                          bg-red-500/10 text-red-400 text-sm">
                {{ loginForm.error }}
              </div>

              <form @submit.prevent="submitLogin" class="space-y-4">
                <div>
                  <label class="block text-xs font-medium text-white/50 mb-2">Email</label>
                  <input v-model="loginForm.email" id="login-email" type="email"
                         placeholder="kamu@email.com" autocomplete="email"
                         class="w-full px-4 py-3 rounded-xl border border-white/10 bg-white/5
                                text-sm text-white placeholder-white/20
                                focus:outline-none focus:border-violet-500/50 transition-all duration-200" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-white/50 mb-2">Password</label>
                  <input v-model="loginForm.password" id="login-password" type="password"
                         placeholder="••••••••" autocomplete="current-password"
                         class="w-full px-4 py-3 rounded-xl border border-white/10 bg-white/5
                                text-sm text-white placeholder-white/20
                                focus:outline-none focus:border-violet-500/50 transition-all duration-200" />
                </div>
                <button type="submit" id="login-submit-btn" :disabled="loginForm.loading"
                        class="w-full py-3 rounded-xl text-sm font-semibold
                               bg-violet-600 hover:bg-violet-500 text-white
                               shadow-lg shadow-violet-600/20 transition-all duration-200
                               active:scale-[0.98] disabled:opacity-60
                               flex items-center justify-center gap-2 mt-2">
                  <svg v-if="loginForm.loading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  {{ loginForm.loading ? 'Memproses...' : 'Masuk ke Dashboard' }}
                </button>
              </form>
              <p class="mt-5 text-center text-xs text-white/30">
                Belum punya akun?
                <button @click="modalTab = 'register'" class="text-violet-400 hover:text-violet-300 underline">Daftar di sini</button>
              </p>
            </div>

            <!-- ── REGISTER TAB ── -->
            <div v-else class="p-6 sm:p-8 max-h-[80vh] overflow-y-auto">
              <h3 class="text-xl font-bold text-white mb-1">Daftar Tim</h3>
              <p class="text-sm text-white/40 mb-6">Isi data lengkap untuk mendaftar S-Tech 2026.</p>

              <!-- Global error -->
              <div v-if="Object.keys(registerForm.errors).length > 0 && !registerForm.errors.name && !registerForm.errors.email"
                   class="mb-4 px-4 py-3 rounded-xl border border-red-500/20 bg-red-500/10 text-red-400 text-sm">
                Terdapat kesalahan. Periksa data yang kamu isi.
              </div>

              <form @submit.prevent="submitRegister" class="space-y-4">

                <!-- Nama lengkap ketua -->
                <div>
                  <label class="block text-xs font-medium text-white/50 mb-1.5">Nama Lengkap (Ketua Tim)</label>
                  <input v-model="registerForm.name" type="text" placeholder="Nama lengkap kamu"
                         class="w-full px-4 py-2.5 rounded-xl border bg-white/5 text-sm text-white
                                placeholder-white/20 focus:outline-none transition-all duration-200"
                         :class="registerForm.errors.name ? 'border-red-500/50' : 'border-white/10 focus:border-violet-500/50'" />
                  <p v-if="registerForm.errors.name" class="mt-1 text-xs text-red-400">{{ registerForm.errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-xs font-medium text-white/50 mb-1.5">Email</label>
                  <input v-model="registerForm.email" type="email" placeholder="kamu@email.com"
                         class="w-full px-4 py-2.5 rounded-xl border bg-white/5 text-sm text-white
                                placeholder-white/20 focus:outline-none transition-all duration-200"
                         :class="registerForm.errors.email ? 'border-red-500/50' : 'border-white/10 focus:border-violet-500/50'" />
                  <p v-if="registerForm.errors.email" class="mt-1 text-xs text-red-400">{{ registerForm.errors.email }}</p>
                </div>

                <!-- Password -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-white/50 mb-1.5">Password</label>
                    <input v-model="registerForm.password" type="password" placeholder="Min. 6 karakter"
                           class="w-full px-4 py-2.5 rounded-xl border bg-white/5 text-sm text-white
                                  placeholder-white/20 focus:outline-none transition-all duration-200"
                           :class="registerForm.errors.password ? 'border-red-500/50' : 'border-white/10 focus:border-violet-500/50'" />
                    <p v-if="registerForm.errors.password" class="mt-1 text-xs text-red-400">{{ registerForm.errors.password }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-white/50 mb-1.5">Konfirmasi Password</label>
                    <input v-model="registerForm.password_confirmation" type="password" placeholder="Ulangi password"
                           class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-white/5 text-sm text-white
                                  placeholder-white/20 focus:outline-none focus:border-violet-500/50 transition-all duration-200" />
                  </div>
                </div>

                <!-- Divider -->
                <div class="flex items-center gap-3 py-1">
                  <div class="flex-1 h-px bg-white/10"></div>
                  <span class="text-xs text-white/30">Info Tim</span>
                  <div class="flex-1 h-px bg-white/10"></div>
                </div>

                <!-- Nama tim -->
                <div>
                  <label class="block text-xs font-medium text-white/50 mb-1.5">Nama Tim</label>
                  <input v-model="registerForm.team_name" type="text" placeholder="Nama tim kamu"
                         class="w-full px-4 py-2.5 rounded-xl border bg-white/5 text-sm text-white
                                placeholder-white/20 focus:outline-none transition-all duration-200"
                         :class="registerForm.errors.team_name ? 'border-red-500/50' : 'border-white/10 focus:border-violet-500/50'" />
                  <p v-if="registerForm.errors.team_name" class="mt-1 text-xs text-red-400">{{ registerForm.errors.team_name }}</p>
                </div>

                <!-- Kategori & Jumlah Anggota -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-white/50 mb-1.5">Kategori Peserta</label>
                    <select v-model="registerForm.category"
                            class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-[#1a1a1a] text-sm text-white
                                   focus:outline-none focus:border-violet-500/50 transition-all duration-200">
                      <option value="sma">Pelajar SMA/SMK</option>
                      <option value="mahasiswa">Mahasiswa</option>
                      <option value="umum">Masyarakat Umum</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-white/50 mb-1.5">Jumlah Anggota</label>
                    <select v-model="registerForm.member_count"
                            class="w-full px-4 py-2.5 rounded-xl border border-white/10 bg-[#1a1a1a] text-sm text-white
                                   focus:outline-none focus:border-violet-500/50 transition-all duration-200">
                      <option :value="2">2 Orang</option>
                      <option :value="3">3 Orang</option>
                    </select>
                  </div>
                </div>

                <!-- Institusi -->
                <div>
                  <label class="block text-xs font-medium text-white/50 mb-1.5">Asal Sekolah / Kampus</label>
                  <input v-model="registerForm.institution" type="text" placeholder="Nama sekolah atau kampus"
                         class="w-full px-4 py-2.5 rounded-xl border bg-white/5 text-sm text-white
                                placeholder-white/20 focus:outline-none transition-all duration-200"
                         :class="registerForm.errors.institution ? 'border-red-500/50' : 'border-white/10 focus:border-violet-500/50'" />
                  <p v-if="registerForm.errors.institution" class="mt-1 text-xs text-red-400">{{ registerForm.errors.institution }}</p>
                </div>

                <!-- No HP -->
                <div>
                  <label class="block text-xs font-medium text-white/50 mb-1.5">Nomor HP Ketua Tim</label>
                  <input v-model="registerForm.phone" type="tel" placeholder="08xxxxxxxxxx"
                         class="w-full px-4 py-2.5 rounded-xl border bg-white/5 text-sm text-white
                                placeholder-white/20 focus:outline-none transition-all duration-200"
                         :class="registerForm.errors.phone ? 'border-red-500/50' : 'border-white/10 focus:border-violet-500/50'" />
                  <p v-if="registerForm.errors.phone" class="mt-1 text-xs text-red-400">{{ registerForm.errors.phone }}</p>
                </div>

                <!-- Biaya info -->
                <div class="px-4 py-3 rounded-xl border border-amber-500/20 bg-amber-500/5 text-xs text-amber-300/80">
                  💳 Biaya pendaftaran: <strong>Rp 75.000 / tim</strong>. Bukti transfer diunggah setelah daftar.
                </div>

                <!-- Submit -->
                <button type="submit" id="register-submit-btn" :disabled="registerForm.loading"
                        class="w-full py-3 rounded-xl text-sm font-semibold
                               bg-violet-600 hover:bg-violet-500 text-white
                               shadow-lg shadow-violet-600/20 transition-all duration-200
                               active:scale-[0.98] disabled:opacity-60
                               flex items-center justify-center gap-2">
                  <svg v-if="registerForm.loading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  {{ registerForm.loading ? 'Mendaftarkan...' : 'Daftar Sekarang' }}
                </button>
              </form>
              <p class="mt-5 text-center text-xs text-white/30">
                Sudah punya akun?
                <button @click="modalTab = 'login'" class="text-violet-400 hover:text-violet-300 underline">Masuk</button>
              </p>
            </div>

          </div>
        </Transition>
      </div>
    </Transition>

  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
</style>
