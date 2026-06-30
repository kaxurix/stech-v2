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
  <div class="relative min-h-screen bg-gradient-to-b from-blue-600 via-blue-700 to-blue-900 text-gray-900 font-['Inter',sans-serif] overflow-x-hidden">

    <!-- ── CLOUD ORNAMENTS LAYER ──────────────────────────────────────────── -->
    <div class="fixed inset-0 z-[1] pointer-events-none overflow-hidden select-none" aria-hidden="true">

      <!-- Awan Atas Kiri -->
      <img src="/images/ornaments/awan-tas-kiri.svg"
           alt=""
           class="absolute top-0 left-0 w-40 sm:w-56 md:w-72 lg:w-96
                  opacity-100 -translate-x-4 -translate-y-4"
           draggable="false" />

      <!-- Awan Atas Kanan -->
      <img src="/images/ornaments/awan-atas-kanan.svg"
           alt=""
           class="absolute top-0 right-0 w-40 sm:w-56 md:w-72 lg:w-96
                  opacity-100 translate-x-4 -translate-y-4"
           draggable="false" />

      <!-- Awan Kiri Tengah -->
      <img src="/images/ornaments/awan-kiri.svg"
           alt=""
           class="absolute top-[30%] left-[3%] w-28 sm:w-40 md:w-52
                  opacity-90"
           draggable="false" />

      <!-- Awan Kiri Blur (tengah bawah kiri) -->
      <img src="/images/ornaments/awan-kiri-blur.svg"
           alt=""
           class="absolute top-[55%] left-[5%] w-24 sm:w-36 md:w-44
                  opacity-70"
           draggable="false" />

      <!-- Awan Kanan Tengah -->
      <img src="/images/ornaments/awan-kanan.svg"
           alt=""
           class="absolute top-[30%] right-[3%] w-28 sm:w-40 md:w-52
                  opacity-90"
           draggable="false" />

      <!-- Awan Kanan Blur (tengah bawah kanan) -->
      <img src="/images/ornaments/awan-kanan-blur.svg"
           alt=""
           class="absolute top-[55%] right-[5%] w-24 sm:w-36 md:w-44
                  opacity-70"
           draggable="false" />

      <!-- Awan Kiri Bawah -->
      <img src="/images/ornaments/awan-kiri-bawah.svg"
           alt=""
           class="absolute bottom-0 left-0 w-40 sm:w-56 md:w-72 lg:w-96
                  opacity-100 -translate-x-4 translate-y-4"
           draggable="false" />

      <!-- Awan Kanan Bawah -->
      <img src="/images/ornaments/awan-kanan-bawah.svg"
           alt=""
           class="absolute bottom-0 right-0 w-40 sm:w-56 md:w-72 lg:w-96
                  opacity-100 translate-x-4 translate-y-4"
           draggable="false" />

    </div><!-- end cloud layer -->

    <!-- ── MAIN CONTENT (z-[10] to sit above cloud layer) ───────────────── -->
    <div class="relative z-[10]">

    <!-- ══════════════════════════════════════════════
         NAV
    ═══════════════════════════════════════════════ -->
    <header class="fixed top-0 inset-x-0 z-50 border-b border-blue-800/40
                   bg-blue-900/95 backdrop-blur-xl">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">

        <!-- Logo -->
        <a href="/" class="flex items-center group flex-shrink-0">
          <span class="text-base font-bold tracking-tight text-white">
            Soedirman Technophoria
          </span>
        </a>

        <!-- Desktop nav -->
        <nav class="hidden md:flex items-center gap-7 text-sm text-blue-200">
          <a href="#about"       class="hover:text-white transition-colors duration-200">Tentang</a>
          <a href="#competition" class="hover:text-white transition-colors duration-200">Lomba</a>
          <a href="#seminar"     class="hover:text-white transition-colors duration-200">Seminar</a>
          <a href="#timeline"    class="hover:text-white transition-colors duration-200">Timeline</a>
        </nav>

        <!-- Desktop CTA -->
        <div class="hidden md:flex items-center gap-3">
          <button @click="openModal('login')" id="nav-login-btn"
                  class="px-4 py-2 text-sm font-medium rounded-lg border border-blue-400/40
                         text-blue-100 hover:text-white hover:bg-blue-800 hover:border-blue-400
                         transition-all duration-200">
            Masuk
          </button>
          <button @click="openModal('register')" id="nav-register-btn"
                  class="px-4 py-2 text-sm font-semibold rounded-lg
                         bg-amber-500 hover:bg-amber-400 text-white
                         shadow-md shadow-amber-500/30 transition-all duration-200 active:scale-95">
            Daftar
          </button>
        </div>

        <!-- Mobile hamburger -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" id="mobile-menu-btn"
                class="md:hidden w-9 h-9 flex flex-col items-center justify-center gap-1.5
                       rounded-lg border border-blue-400/30 hover:bg-blue-800 transition-all duration-200">
          <span :class="['block w-5 h-0.5 bg-white rounded transition-all duration-300',
                         mobileMenuOpen ? 'rotate-45 translate-y-2' : '']"></span>
          <span :class="['block w-5 h-0.5 bg-white rounded transition-all duration-300',
                         mobileMenuOpen ? 'opacity-0' : '']"></span>
          <span :class="['block w-5 h-0.5 bg-white rounded transition-all duration-300',
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
             class="md:hidden border-t border-blue-800 bg-blue-900 px-4 py-4 space-y-1">
          <a href="#about"       @click="mobileMenuOpen=false"
             class="block px-3 py-2.5 text-sm text-blue-100 hover:text-white hover:bg-blue-800 rounded-lg transition-all">Tentang</a>
          <a href="#competition" @click="mobileMenuOpen=false"
             class="block px-3 py-2.5 text-sm text-blue-100 hover:text-white hover:bg-blue-800 rounded-lg transition-all">Lomba Web Dev</a>
          <a href="#seminar"     @click="mobileMenuOpen=false"
             class="block px-3 py-2.5 text-sm text-blue-100 hover:text-white hover:bg-blue-800 rounded-lg transition-all">Seminar</a>
          <a href="#timeline"    @click="mobileMenuOpen=false"
             class="block px-3 py-2.5 text-sm text-blue-100 hover:text-white hover:bg-blue-800 rounded-lg transition-all">Timeline</a>
          <div class="pt-3 flex flex-col gap-2">
            <button @click="openModal('register')"
                    class="w-full py-3 text-sm font-semibold rounded-xl
                           bg-amber-500 hover:bg-amber-400 text-white transition-all duration-200">
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
                    bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 left-1/4 w-[300px] h-[300px]
                    bg-sky-300/20 rounded-full blur-3xl"></div>
      </div>
      <div class="absolute inset-0 pointer-events-none opacity-[0.06]"
           style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px),
                                    linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px);
                  background-size: 56px 56px;"></div>

      <Transition enter-active-class="transition-all duration-700 ease-out"
                  enter-from-class="opacity-0 translate-y-8"
                  enter-to-class="opacity-100 translate-y-0">
        <div v-if="heroVisible" class="relative z-10 max-w-4xl mx-auto">
          
          <h1 class="text-4xl sm:text-6xl md:text-7xl lg:text-8xl font-extrabold
                     tracking-tight leading-[1.05] mb-5 sm:mb-6">
            <span class="text-white drop-shadow-lg">Soedirman</span><br>
            <span class="text-amber-300 drop-shadow-lg">Technophoria</span>
          </h1>
          <p class="max-w-2xl mx-auto text-sm sm:text-base md:text-lg text-white/80
                    leading-relaxed mb-4 px-2">
            Event teknologi tahunan yang mempertemukan pelajar SMA/SMK, mahasiswa,
            dan masyarakat umum dalam satu panggung kompetisi dan edukasi.
            Buktikan kemampuanmu di
            <span class="text-white font-semibold">Lomba Web Development</span>
            dan perluas wawasanmu lewat
            <span class="text-white font-semibold">Seminar Nasional</span>.
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-8 sm:mt-10">
            <button @click="openModal('register')" id="hero-register-btn"
                    class="w-full sm:w-auto px-7 py-3.5 text-sm font-bold rounded-xl
                           bg-amber-500 hover:bg-amber-400 text-white
                           shadow-lg shadow-amber-500/40 hover:shadow-amber-400/50
                           transition-all duration-200 active:scale-[0.98]">
              Daftar Sekarang →
            </button>
            <a href="https://drive.google.com/your-guidebook-link" target="_blank" rel="noopener noreferrer"
               class="w-full sm:w-auto px-7 py-3.5 text-sm font-medium text-center rounded-xl
                      border-2 border-white/50 text-white hover:bg-white/20
                      hover:border-white transition-all duration-200
                      flex items-center justify-center gap-2 backdrop-blur-sm">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Unduh Guidebook
            </a>
          </div>
          <div class="flex flex-wrap items-center justify-center gap-3 mt-12 sm:mt-14">
            <span class="px-4 py-2 rounded-full border-2 border-white/50 bg-white/15 text-xs text-white font-semibold backdrop-blur-sm">🎓 Mahasiswa</span>
            <span class="px-4 py-2 rounded-full border-2 border-white/50 bg-white/15 text-xs text-white font-semibold backdrop-blur-sm">📚 Pelajar SMA / SMK</span>
            <span class="px-4 py-2 rounded-full border-2 border-white/50 bg-white/15 text-xs text-white font-semibold backdrop-blur-sm">🌐 Masyarakat Umum</span>
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
               class="px-4 sm:px-6 py-20 sm:py-24">
        <div class="max-w-6xl mx-auto">
          <div class="bg-white border-4 border-white rounded-3xl shadow-2xl p-8 sm:p-12">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
              <div>
                <p class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-3">Tentang S-Tech</p>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-blue-900 mb-5 leading-snug">
                  Ruang Bertumbuh bagi<br>Generasi Teknologi Indonesia
                </h2>
                <p class="text-sm sm:text-base text-gray-600 leading-relaxed mb-5">
                  Soedirman Technophoria (S-Tech) adalah event tahunan yang diselenggarakan
                  oleh Himpunan Mahasiswa Informatika Universitas Jenderal Soedirman.
                  Melalui <strong class="text-blue-800">Lomba Web Development</strong> dan
                  <strong class="text-blue-800">Seminar Nasional</strong>, S-Tech hadir sebagai
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
                     class="flex items-start gap-4 p-4 rounded-2xl border-2 border-blue-100
                            bg-blue-50 hover:bg-blue-100 transition-all duration-300">
                  <span class="text-2xl flex-shrink-0">{{ item.icon }}</span>
                  <div>
                    <p class="text-sm font-semibold text-blue-900 mb-0.5">{{ item.title }}</p>
                    <p class="text-xs text-gray-500 leading-relaxed">{{ item.desc }}</p>
                  </div>
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
          <p class="text-xs font-bold text-amber-300 uppercase tracking-widest mb-2">Cabang Lomba</p>
          <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white">Lomba Web Development</h2>
          <p class="text-sm sm:text-base text-blue-100 mt-2 max-w-xl">
            Tunjukkan kemampuan membangun solusi web inovatif di hadapan para juri profesional.
          </p>
        </div>
        <div class="bg-white border-4 border-white rounded-3xl shadow-2xl p-6 sm:p-8 lg:p-10 mb-6 overflow-hidden">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">
            <div>
              
              <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-blue-900 mb-4">
                Web Development<br>
                <span class="text-blue-600">Challenge 2026</span>
              </h3>
              <p class="text-sm text-gray-600 leading-relaxed mb-6">
                Tunjukkan kemampuan terbaikmu dalam merancang dan membangun
                aplikasi web yang inovatif, fungsional, dan berdampak nyata.
              </p>
              <div class="flex flex-wrap gap-3">
                <div class="px-4 py-2 rounded-xl border-2 border-blue-100 bg-blue-50">
                  <p class="text-[10px] text-blue-400 mb-0.5 font-semibold">Peserta</p>
                  <p class="text-xs font-semibold text-blue-900">SMA/SMK & Mahasiswa</p>
                </div>
                <div class="px-4 py-2 rounded-xl border-2 border-blue-100 bg-blue-50">
                  <p class="text-[10px] text-blue-400 mb-0.5 font-semibold">Format Tim</p>
                  <p class="text-xs font-semibold text-blue-900">2–3 Orang / Tim</p>
                </div>
                <div class="px-4 py-2 rounded-xl border-2 border-blue-100 bg-blue-50">
                  <p class="text-[10px] text-blue-400 mb-0.5 font-semibold">Biaya Daftar</p>
                  <p class="text-xs font-semibold text-blue-900">Rp 75.000 / Tim</p>
                </div>
              </div>
            </div>
            <div class="space-y-4">
              <div class="p-6 rounded-2xl border-2 border-amber-200 bg-amber-50">
                <p class="text-xs text-amber-600 mb-2 uppercase tracking-wider font-bold">Total Hadiah</p>
                <p class="text-4xl font-extrabold text-amber-500 mb-1">Rp 5 Jt+</p>
                <p class="text-sm text-gray-500">Juara 1 · Juara 2 · Juara 3</p>
                
              </div>

              <!-- Tombol Daftar dipindah ke sini, di dalam kotak putih -->
              <button @click="openModal('register')"
                      class="w-full py-3.5 text-sm font-bold rounded-xl
                             bg-amber-500 hover:bg-amber-400 text-white
                             shadow-lg shadow-amber-500/40 transition-all duration-200 active:scale-[0.98]">
                Daftar Lomba Sekarang →
              </button>
            </div>
          </div>
        </div>
      </section>
    </Transition>

    <!-- ══════════════════════════════════════════════
         SEMINAR
    ═══════════════════════════════════════════════ -->
    <Transition enter-active-class="transition-all duration-700 ease-out delay-100"
                enter-from-class="opacity-0 translate-y-8"
                enter-to-class="opacity-100 translate-y-0">
      <section v-if="cardsVisible" id="seminar"
               class="px-4 sm:px-6 py-20 sm:py-24">
        <div class="max-w-6xl mx-auto">
          <div class="mb-10 sm:mb-12">
            <p class="text-xs font-bold text-amber-300 uppercase tracking-widest mb-2">Acara Pendukung</p>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white">Seminar Nasional</h2>
            <p class="text-sm sm:text-base text-blue-100 mt-2 max-w-xl">
              Sesi eksklusif bersama pembicara dari industri teknologi.
            </p>
          </div>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2 bg-white border-4 border-white rounded-3xl shadow-2xl p-6 sm:p-8">
              <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full
                            border border-blue-200 bg-blue-50 text-blue-700 text-xs font-semibold mb-5">
                  <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                  Gratis untuk Mahasiswa Unsoed
                </div>
                <h3 class="text-xl sm:text-2xl font-bold text-blue-900 mb-3">Teknologi & Karir di Era Digital</h3>
                <p class="text-sm text-gray-600 leading-relaxed mb-6">
                  Seminar nasional menghadirkan praktisi dan pakar industri untuk berbagi wawasan
                  tentang tren teknologi terkini dan peluang karir.
                </p>
                <div class="flex flex-wrap gap-3">
                  <div class="px-4 py-2 rounded-xl border-2 border-blue-100 bg-blue-50">
                    <p class="text-[10px] text-blue-400 mb-0.5 font-semibold">Tanggal</p>
                    <p class="text-xs font-semibold text-blue-900">22 Juli 2026</p>
                  </div>
                  <div class="px-4 py-2 rounded-xl border-2 border-blue-100 bg-blue-50">
                    <p class="text-[10px] text-blue-400 mb-0.5 font-semibold">Format</p>
                    <p class="text-xs font-semibold text-blue-900">Offline · Purwokerto</p>
                  </div>
                  <div class="px-4 py-2 rounded-xl border-2 border-blue-100 bg-blue-50">
                    <p class="text-[10px] text-blue-400 mb-0.5 font-semibold">Sertifikat</p>
                    <p class="text-xs font-semibold text-blue-900">E-Certificate ✓</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-white border-4 border-white rounded-3xl shadow-2xl p-6">
              <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-4">Topik Bahasan</p>
              <div class="space-y-3">
                <div v-for="topic in seminarTopics" :key="topic.label"
                     class="flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50 transition-all duration-200">
                  <span class="text-base text-blue-500 flex-shrink-0">{{ topic.icon }}</span>
                  <p class="text-sm text-gray-700 font-medium">{{ topic.label }}</p>
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
               class="px-4 sm:px-6 py-20 sm:py-24">
        <div class="max-w-6xl mx-auto">
          <div class="mb-10 sm:mb-14 text-center">
            <p class="text-xs font-bold text-amber-300 uppercase tracking-widest mb-2">Jadwal</p>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white">Timeline Acara</h2>
          </div>
          <div class="bg-white border-4 border-white rounded-3xl shadow-2xl p-8">
            <div class="hidden md:flex items-start relative">
              <div class="absolute top-5 left-0 right-0 h-0.5 bg-blue-100"></div>
              <div v-for="(item, i) in timeline" :key="i"
                   class="relative flex flex-col items-center text-center flex-1 px-2">
                <div :class="['w-10 h-10 rounded-full border-2 flex items-center justify-center z-10 mb-4',
                              item.done ? 'border-green-500 bg-green-500 shadow-lg shadow-green-500/30'
                              : i === 1 ? 'border-blue-500 bg-blue-500 ring-4 ring-blue-100'
                              : 'border-blue-200 bg-white']">
                  <svg v-if="item.done" class="w-4 h-4 text-white"
                       viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <span v-else class="text-xs font-bold"
                        :class="i === 1 ? 'text-white' : 'text-blue-300'">{{ i + 1 }}</span>
                </div>
                <p class="text-xs font-semibold leading-snug"
                   :class="item.done ? 'text-green-600' : i === 1 ? 'text-blue-700 font-bold' : 'text-gray-400'">
                  {{ item.label }}
                </p>
                <p class="text-[10px] mt-1.5"
                   :class="item.done ? 'text-green-400' : 'text-gray-300'">
                  {{ item.date }}
                </p>
              </div>
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
      <section v-if="cardsVisible" class="px-4 sm:px-6 py-16 sm:py-20">
        <div class="max-w-3xl mx-auto text-center">
          <div class="bg-white border-4 border-white rounded-3xl shadow-2xl p-8 sm:p-12">
            <div>
              <p class="text-xs font-bold text-amber-500 uppercase tracking-widest mb-3">Bergabung Sekarang</p>
              <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-blue-900 mb-3">
                Siap Menunjukkan Kemampuanmu?
              </h2>
              <p class="text-sm sm:text-base text-gray-500 mb-8 max-w-md mx-auto">
                Daftar sekarang dan jadilah bagian dari generasi developer
                terbaik Informatika Unsoed.
              </p>
              <button @click="openModal('register')" id="cta-register-btn"
                      class="px-8 sm:px-10 py-3.5 text-sm font-bold rounded-xl
                             bg-amber-500 hover:bg-amber-400 text-white
                             shadow-xl shadow-amber-500/40 hover:shadow-amber-400/50
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
    <footer class="px-4 sm:px-6 py-10 border-t border-blue-800/40 bg-blue-900/60">
      <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center
                  justify-between gap-4 text-xs text-blue-300">
        <div class="flex items-center gap-2.5">
          <span class="font-semibold text-white">Soedirman Technophoria 2026</span>
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
                      border-4 border-white bg-white shadow-2xl overflow-hidden
                      max-h-[90vh] flex flex-col">

            <!-- Mobile drag handle -->
            <div class="sm:hidden w-10 h-1 rounded-full bg-blue-200 mx-auto mt-4 mb-2 flex-shrink-0"></div>

            <!-- Close btn -->
            <button @click="closeModal" id="modal-close-btn"
                    class="absolute top-4 right-4 w-7 h-7 rounded-lg z-10
                           border border-gray-200 text-gray-400 hover:text-gray-700 hover:bg-gray-100
                           flex items-center justify-center text-xs transition-all duration-200">✕</button>

            <!-- Tab switcher -->
            <div class="flex border-b border-gray-200 mt-2 sm:mt-0 flex-shrink-0">
              <button @click="modalTab = 'login'"
                      :class="['flex-1 py-4 text-sm font-semibold transition-all duration-200',
                               modalTab === 'login'
                                 ? 'text-blue-700 border-b-2 border-blue-600'
                                 : 'text-gray-400 hover:text-gray-600']">
                Masuk
              </button>
              <button @click="modalTab = 'register'"
                      :class="['flex-1 py-4 text-sm font-semibold transition-all duration-200',
                               modalTab === 'register'
                                 ? 'text-blue-700 border-b-2 border-blue-600'
                                 : 'text-gray-400 hover:text-gray-600']">
                Daftar
              </button>
            </div>

            <!-- ── LOGIN TAB ── -->
            <div v-if="modalTab === 'login'" class="p-6 sm:p-8 overflow-y-auto">
              <div class="mb-6">
                <p class="text-sm font-bold leading-none text-blue-900">Soedirman Technophoria</p>
                <p class="text-[10px] text-gray-400 mt-1">Portal Peserta</p>
              </div>
              <h3 class="text-xl font-bold text-blue-900 mb-1">Masuk ke Akunmu</h3>
              <p class="text-sm text-gray-500 mb-6">Lanjutkan proses pendaftaranmu.</p>

              <div v-if="loginForm.error"
                   class="mb-4 px-4 py-3 rounded-xl border border-red-200
                          bg-red-50 text-red-600 text-sm">
                {{ loginForm.error }}
              </div>

              <form @submit.prevent="submitLogin" class="space-y-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-2">Email</label>
                  <input v-model="loginForm.email" id="login-email" type="email"
                         placeholder="kamu@email.com" autocomplete="email"
                         class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-gray-50
                                text-sm text-gray-800 placeholder-gray-400
                                focus:outline-none focus:border-blue-500 transition-all duration-200" />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-2">Password</label>
                  <input v-model="loginForm.password" id="login-password" type="password"
                         placeholder="••••••••" autocomplete="current-password"
                         class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-gray-50
                                text-sm text-gray-800 placeholder-gray-400
                                focus:outline-none focus:border-blue-500 transition-all duration-200" />
                </div>
                <button type="submit" id="login-submit-btn" :disabled="loginForm.loading"
                        class="w-full py-3 rounded-xl text-sm font-bold
                               bg-amber-500 hover:bg-amber-400 text-white
                               shadow-lg shadow-amber-500/30 transition-all duration-200
                               active:scale-[0.98] disabled:opacity-60
                               flex items-center justify-center gap-2 mt-2">
                  <svg v-if="loginForm.loading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  {{ loginForm.loading ? 'Memproses...' : 'Masuk ke Dashboard' }}
                </button>
              </form>
              <p class="mt-5 text-center text-xs text-gray-400">
                Belum punya akun?
                <button @click="modalTab = 'register'" class="text-blue-600 hover:text-blue-500 underline font-semibold">Daftar di sini</button>
              </p>
            </div>

            <!-- ── REGISTER TAB ── -->
            <div v-else class="p-6 sm:p-8 max-h-[80vh] overflow-y-auto">
              <h3 class="text-xl font-bold text-blue-900 mb-1">Daftar Tim</h3>
              <p class="text-sm text-gray-500 mb-6">Isi data lengkap untuk mendaftar S-Tech 2026.</p>

              <!-- Global error -->
              <div v-if="Object.keys(registerForm.errors).length > 0 && !registerForm.errors.name && !registerForm.errors.email"
                   class="mb-4 px-4 py-3 rounded-xl border border-red-200 bg-red-50 text-red-600 text-sm">
                Terdapat kesalahan. Periksa data yang kamu isi.
              </div>

              <form @submit.prevent="submitRegister" class="space-y-4">

                <!-- Nama lengkap ketua -->
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Lengkap (Ketua Tim)</label>
                  <input v-model="registerForm.name" type="text" placeholder="Nama lengkap kamu"
                         class="w-full px-4 py-2.5 rounded-xl border-2 bg-gray-50 text-sm text-gray-800
                                placeholder-gray-400 focus:outline-none transition-all duration-200"
                         :class="registerForm.errors.name ? 'border-red-400' : 'border-gray-200 focus:border-blue-500'" />
                  <p v-if="registerForm.errors.name" class="mt-1 text-xs text-red-500">{{ registerForm.errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email</label>
                  <input v-model="registerForm.email" type="email" placeholder="kamu@email.com"
                         class="w-full px-4 py-2.5 rounded-xl border-2 bg-gray-50 text-sm text-gray-800
                                placeholder-gray-400 focus:outline-none transition-all duration-200"
                         :class="registerForm.errors.email ? 'border-red-400' : 'border-gray-200 focus:border-blue-500'" />
                  <p v-if="registerForm.errors.email" class="mt-1 text-xs text-red-500">{{ registerForm.errors.email }}</p>
                </div>

                <!-- Password -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Password</label>
                    <input v-model="registerForm.password" type="password" placeholder="Min. 6 karakter"
                           class="w-full px-4 py-2.5 rounded-xl border-2 bg-gray-50 text-sm text-gray-800
                                  placeholder-gray-400 focus:outline-none transition-all duration-200"
                           :class="registerForm.errors.password ? 'border-red-400' : 'border-gray-200 focus:border-blue-500'" />
                    <p v-if="registerForm.errors.password" class="mt-1 text-xs text-red-500">{{ registerForm.errors.password }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Konfirmasi Password</label>
                    <input v-model="registerForm.password_confirmation" type="password" placeholder="Ulangi password"
                           class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50 text-sm text-gray-800
                                  placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-all duration-200" />
                  </div>
                </div>

                <!-- Divider -->
                <div class="flex items-center gap-3 py-1">
                  <div class="flex-1 h-px bg-gray-200"></div>
                  <span class="text-xs text-gray-400 font-semibold">Info Tim</span>
                  <div class="flex-1 h-px bg-gray-200"></div>
                </div>

                <!-- Nama tim -->
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Tim</label>
                  <input v-model="registerForm.team_name" type="text" placeholder="Nama tim kamu"
                         class="w-full px-4 py-2.5 rounded-xl border-2 bg-gray-50 text-sm text-gray-800
                                placeholder-gray-400 focus:outline-none transition-all duration-200"
                         :class="registerForm.errors.team_name ? 'border-red-400' : 'border-gray-200 focus:border-blue-500'" />
                  <p v-if="registerForm.errors.team_name" class="mt-1 text-xs text-red-500">{{ registerForm.errors.team_name }}</p>
                </div>

                <!-- Kategori & Jumlah Anggota -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kategori Peserta</label>
                    <select v-model="registerForm.category"
                            class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50 text-sm text-gray-800
                                   focus:outline-none focus:border-blue-500 transition-all duration-200">
                      <option value="sma">Pelajar SMA/SMK</option>
                      <option value="mahasiswa">Mahasiswa</option>
                      <option value="umum">Masyarakat Umum</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah Anggota</label>
                    <select v-model="registerForm.member_count"
                            class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-gray-50 text-sm text-gray-800
                                   focus:outline-none focus:border-blue-500 transition-all duration-200">
                      <option :value="2">2 Orang</option>
                      <option :value="3">3 Orang</option>
                    </select>
                  </div>
                </div>

                <!-- Institusi -->
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1.5">Asal Sekolah / Kampus</label>
                  <input v-model="registerForm.institution" type="text" placeholder="Nama sekolah atau kampus"
                         class="w-full px-4 py-2.5 rounded-xl border-2 bg-gray-50 text-sm text-gray-800
                                placeholder-gray-400 focus:outline-none transition-all duration-200"
                         :class="registerForm.errors.institution ? 'border-red-400' : 'border-gray-200 focus:border-blue-500'" />
                  <p v-if="registerForm.errors.institution" class="mt-1 text-xs text-red-500">{{ registerForm.errors.institution }}</p>
                </div>

                <!-- No HP -->
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nomor HP Ketua Tim</label>
                  <input v-model="registerForm.phone" type="tel" placeholder="08xxxxxxxxxx"
                         class="w-full px-4 py-2.5 rounded-xl border-2 bg-gray-50 text-sm text-gray-800
                                placeholder-gray-400 focus:outline-none transition-all duration-200"
                         :class="registerForm.errors.phone ? 'border-red-400' : 'border-gray-200 focus:border-blue-500'" />
                  <p v-if="registerForm.errors.phone" class="mt-1 text-xs text-red-500">{{ registerForm.errors.phone }}</p>
                </div>

                <!-- Biaya info -->
                <div class="px-4 py-3 rounded-xl border-2 border-amber-200 bg-amber-50 text-xs text-amber-700">
                  💳 Biaya pendaftaran: <strong>Rp 75.000 / tim</strong>. Bukti transfer diunggah setelah daftar.
                </div>

                <!-- Submit -->
                <button type="submit" id="register-submit-btn" :disabled="registerForm.loading"
                        class="w-full py-3 rounded-xl text-sm font-bold
                               bg-amber-500 hover:bg-amber-400 text-white
                               shadow-lg shadow-amber-500/30 transition-all duration-200
                               active:scale-[0.98] disabled:opacity-60
                               flex items-center justify-center gap-2">
                  <svg v-if="registerForm.loading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  {{ registerForm.loading ? 'Mendaftarkan...' : 'Daftar Sekarang' }}
                </button>
              </form>
              <p class="mt-5 text-center text-xs text-gray-400">
                Sudah punya akun?
                <button @click="modalTab = 'login'" class="text-blue-600 hover:text-blue-500 underline font-semibold">Masuk</button>
              </p>
            </div>

          </div>
        </Transition>
      </div>
    </Transition>

    </div><!-- end main content wrapper -->
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
</style>
