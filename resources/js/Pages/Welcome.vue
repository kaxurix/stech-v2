<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import {
  X, GraduationCap, BookOpen, Globe, Target, Handshake, Lightbulb,
  TrendingUp, Briefcase, Network, CreditCard, Check,
} from '@lucide/vue'

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
// `current` menandai tahap yang sedang berjalan (dot biru). Geser flag ini
// saat tahap berganti; `done: true` untuk tahap yang sudah lewat (dot hijau).
const timeline = [
  { date: '11 Sep – 11 Okt 2026', label: 'Pendaftaran & Pengumpulan', done: false, current: true },
  { date: '12 – 16 Okt 2026',     label: 'Penjurian',                 done: false },
  { date: '17 Okt 2026',          label: 'Pengumuman',                done: false },
  { date: '18 Okt 2026',          label: 'Technical Meeting',         done: false },
  { date: '19 – 30 Okt 2026',     label: 'Penyelesaian',              done: false },
  { date: '31 Okt 2026',          label: 'Seminar & Grand Final',     done: false },
]

// ── Bintang dekoratif ──────────────────────────────────────────────────────────
// Pool bintang yang lebih besar & lebih tersebar (posisi/ukuran/rotasi bervariasi,
// dari yang kecil-kecil sampai cukup besar). Tiap section mengambil slice
// berbeda dari pool ini supaya taburannya tidak terlihat berulang identik
// antar section, sekaligus melanjutkan motif "naik menuju cahaya" (bintang
// mulai muncul begitu meninggalkan zona awan di hero).
const starFieldPool = [
  { top: '6%',  left: '5%',  size: 26, rotate: -15, opacity: 0.32, duration: 5   },
  { top: '13%', left: '90%', size: 34, rotate: 20,  opacity: 0.26, duration: 6.4 },
  { top: '24%', left: '8%',  size: 18, rotate: 35,  opacity: 0.3,  duration: 5.6 },
  { top: '33%', left: '95%', size: 22, rotate: -25, opacity: 0.24, duration: 7   },
  { top: '46%', left: '3%',  size: 30, rotate: 10,  opacity: 0.28, duration: 6.2 },
  { top: '55%', left: '92%', size: 16, rotate: -10, opacity: 0.34, duration: 5.4 },
  { top: '64%', left: '6%',  size: 24, rotate: 28,  opacity: 0.22, duration: 6.8 },
  { top: '74%', left: '94%', size: 32, rotate: -30, opacity: 0.26, duration: 7.2 },
  { top: '84%', left: '9%',  size: 20, rotate: 15,  opacity: 0.3,  duration: 5.8 },
  { top: '92%', left: '88%', size: 28, rotate: -18, opacity: 0.25, duration: 6.6 },
  { top: '38%', left: '50%', size: 14, rotate: 40,  opacity: 0.18, duration: 5.2 },
  { top: '68%', left: '45%', size: 18, rotate: -35, opacity: 0.2,  duration: 6   },
  { top: '10%', left: '38%', size: 12, rotate: 22,  opacity: 0.16, duration: 4.8 },
  { top: '18%', left: '65%', size: 40, rotate: -12, opacity: 0.2,  duration: 7.6 },
  { top: '29%', left: '22%', size: 16, rotate: 45,  opacity: 0.22, duration: 5.4 },
  { top: '41%', left: '78%', size: 20, rotate: -22, opacity: 0.24, duration: 6.4 },
  { top: '50%', left: '15%', size: 36, rotate: 18,  opacity: 0.2,  duration: 7   },
  { top: '58%', left: '60%', size: 10, rotate: -40, opacity: 0.16, duration: 4.6 },
  { top: '61%', left: '28%', size: 22, rotate: 30,  opacity: 0.26, duration: 6   },
  { top: '71%', left: '55%', size: 14, rotate: -18, opacity: 0.18, duration: 5.2 },
  { top: '79%', left: '35%', size: 30, rotate: 12,  opacity: 0.22, duration: 6.8 },
  { top: '88%', left: '68%', size: 18, rotate: -28, opacity: 0.2,  duration: 5.6 },
  { top: '4%',  left: '75%', size: 24, rotate: 8,   opacity: 0.24, duration: 6.2 },
  { top: '96%', left: '20%', size: 12, rotate: 36,  opacity: 0.16, duration: 4.4 },
]

const seminarTopics = [
  { icon: TrendingUp, label: 'Web Development Trends 2026' },
  { icon: Briefcase,  label: 'Career Path di Industri Tech' },
  { icon: Network,    label: 'Networking & Kolaborasi Industri' },
]

const aboutHighlights = [
  { icon: Target,    title: 'Edukasi Teknologi', desc: 'Mengenalkan tren dan praktik teknologi terkini' },
  { icon: Handshake, title: 'Networking',         desc: 'Membangun koneksi antar mahasiswa & industri' },
  { icon: Lightbulb, title: 'Kreativitas',        desc: 'Mendorong inovasi solusi berbasis web' },
]

const audienceBadges = [
  { icon: BookOpen,      label: 'Pelajar SMA / SMK' },
  { icon: GraduationCap, label: 'Mahasiswa' },
  { icon: Globe,         label: 'Masyarakat Umum' },
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

  // Scroll reveal untuk section di bawah hero — aman digerbang IntersectionObserver
  // karena section-section ini tidak pernah jadi bagian viewport awal / LCP.
  const groups = document.querySelectorAll('.reveal-group')
  if ('IntersectionObserver' in window && groups.length) {
    const observer = new IntersectionObserver((entries) => {
      for (const entry of entries) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible')
          observer.unobserve(entry.target)
        }
      }
    }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' })
    groups.forEach((el) => observer.observe(el))
  } else {
    groups.forEach((el) => el.classList.add('is-visible'))
  }
})
</script>

<template>
  <!-- Gradient panjang & kontinu: gelap-pekat di hero (mendung) → makin terang
       menuju Timeline/CTA (cahaya) → tenang lagi di footer. Ini "jalur cerita"
       visual halaman, bukan tiap section punya background sendiri-sendiri. -->
  <div class="relative min-h-screen text-gray-900 overflow-x-hidden"
       style="background: linear-gradient(to bottom,
              #1e3a8a 0%, #1e40af 20%, #1d4ed8 38%, #2563eb 56%, #3b82f6 74%, #2f5fd0 88%, #1e40af 100%);">

    <!-- ── MAIN CONTENT ───────────────────────────────────────────────────── -->
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
            Soedirman Technophoria <span class="text-blue-300">'26</span>
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
                         bg-amber-500 hover:bg-amber-600 text-white
                         shadow-md shadow-amber-500/30 transition-all duration-200 active:scale-95">
            Daftar
          </button>
        </div>

        <!-- Mobile hamburger -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" id="mobile-menu-btn"
                :aria-expanded="mobileMenuOpen" aria-label="Buka menu navigasi"
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
                           bg-amber-500 hover:bg-amber-600 text-white transition-all duration-200">
              Daftar Sekarang
            </button>
          </div>
        </div>
      </Transition>
    </header>

    <main>
    <!-- ══════════════════════════════════════════════
         HERO
    ═══════════════════════════════════════════════ -->
    <section class="relative flex flex-col items-center justify-center text-center
                    pt-36 pb-24 px-4 sm:px-6 min-h-screen overflow-hidden">

      <!-- Awan — hidup lewat drift halus, hanya di hero (menyatu ke bawah seiring scroll,
           merepresentasikan "meninggalkan mendung" secara harfiah, bukan lewat opacity trick) -->
      <div class="absolute inset-0 pointer-events-none overflow-hidden select-none" aria-hidden="true">
        <img src="/images/ornaments/awan-tas-kiri.svg" alt=""
             class="absolute top-0 left-0 w-40 sm:w-56 md:w-72 lg:w-96 -translate-x-4 -translate-y-4
                    animate-cloud-drift [animation-duration:16s]"
             draggable="false" />
        <img src="/images/ornaments/awan-atas-kanan.svg" alt=""
             class="absolute top-0 right-0 w-40 sm:w-56 md:w-72 lg:w-96 translate-x-4 -translate-y-4
                    animate-cloud-drift [animation-duration:13s] [animation-delay:-4s]"
             draggable="false" />
        <img src="/images/ornaments/awan-kiri.svg" alt=""
             class="absolute top-[30%] left-[3%] w-28 sm:w-40 md:w-52 opacity-90
                    animate-cloud-drift [animation-duration:15s] [animation-delay:-8s]"
             draggable="false" />
        <img src="/images/ornaments/awan-kiri-blur.svg" alt=""
             class="absolute top-[55%] left-[5%] w-24 sm:w-36 md:w-44 opacity-70
                    animate-cloud-drift [animation-duration:18s] [animation-delay:-2s]"
             draggable="false" />
        <img src="/images/ornaments/awan-kanan.svg" alt=""
             class="absolute top-[30%] right-[3%] w-28 sm:w-40 md:w-52 opacity-90
                    animate-cloud-drift [animation-duration:14s] [animation-delay:-6s]"
             draggable="false" />
        <img src="/images/ornaments/awan-kanan-blur.svg" alt=""
             class="absolute top-[55%] right-[5%] w-24 sm:w-36 md:w-44 opacity-70
                    animate-cloud-drift [animation-duration:17s] [animation-delay:-10s]"
             draggable="false" />
        <img src="/images/ornaments/awan-kiri-bawah.svg" alt=""
             class="absolute bottom-0 left-0 w-40 sm:w-56 md:w-72 lg:w-96 -translate-x-4 translate-y-4
                    animate-cloud-drift [animation-duration:15s] [animation-delay:-5s]"
             draggable="false" />
        <img src="/images/ornaments/awan-kanan-bawah.svg" alt=""
             class="absolute bottom-0 right-0 w-40 sm:w-56 md:w-72 lg:w-96 translate-x-4 translate-y-4
                    animate-cloud-drift [animation-duration:16s] [animation-delay:-9s]"
             draggable="false" />
      </div>

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

      <div class="relative z-10 max-w-4xl mx-auto">

          <h1 class="text-4xl sm:text-6xl md:text-7xl lg:text-8xl font-extrabold
                     tracking-tight leading-[1.05] mb-5 sm:mb-6 animate-fade-in-up">
            <span class="text-white drop-shadow-lg">Soedirman</span><br>
            <span class="text-amber-300 drop-shadow-lg">Technophoria</span>
          </h1>
          <p class="max-w-2xl mx-auto text-sm sm:text-base md:text-lg text-white/80
                    leading-relaxed mb-4 px-2 animate-fade-in-up [animation-delay:120ms]">
            Event teknologi tahunan yang mempertemukan pelajar SMA/SMK, mahasiswa,
            dan masyarakat umum dalam satu panggung kompetisi dan edukasi.
            Buktikan kemampuanmu di
            <span class="text-white font-semibold">Lomba Web Development</span>
            dan perluas wawasanmu lewat
            <span class="text-white font-semibold">Seminar Nasional</span>.
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-8 sm:mt-10 animate-fade-in-up [animation-delay:240ms]">
            <button @click="openModal('register')" id="hero-register-btn"
                    class="w-full sm:w-56 px-7 py-3.5 text-sm font-bold rounded-xl
                           bg-amber-500 hover:bg-amber-600 text-white
                           shadow-lg shadow-amber-500/40 hover:shadow-amber-600/50
                           transition-all duration-200 active:scale-[0.98]">
              Daftar Sekarang →
            </button>
            <a href="https://drive.google.com/drive/folders/1XKAmL9m-LU5B7DW3ibcGnJVsYtiOiTXN?usp=sharing" target="_blank" rel="noopener noreferrer"
               class="w-full sm:w-56 px-7 py-3.5 text-sm font-medium text-center rounded-xl
                      border-2 border-white/50 text-white hover:bg-white/20
                      hover:border-white transition-all duration-200
                      flex items-center justify-center gap-2 backdrop-blur-sm">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Unduh Guidebook
            </a>
          </div>
          <div class="flex flex-wrap items-center justify-center gap-3 mt-12 sm:mt-14 animate-fade-in-up [animation-delay:360ms]">
            <span v-for="badge in audienceBadges" :key="badge.label"
                  class="flex items-center gap-1.5 px-4 py-2 rounded-full border-2 border-white/50 bg-white/15 text-xs text-white font-semibold backdrop-blur-sm">
              <component :is="badge.icon" :size="14" />
              {{ badge.label }}
            </span>
          </div>
      </div>

      <!-- Bintang mulai muncul di bagian bawah hero — jembatan visual:
           "meninggalkan zona awan, memasuki zona bintang" saat scroll turun -->
      <div class="absolute inset-x-0 bottom-0 h-1/3 pointer-events-none overflow-hidden select-none" aria-hidden="true">
        <img v-for="(s, si) in starFieldPool.slice(0, 10)" :key="si" src="/images/ornaments/bintangpx.svg" alt=""
             class="absolute animate-star-twinkle"
             :style="{ top: s.top, left: s.left, width: s.size + 'px',
                       '--star-opacity': s.opacity, '--star-rotate': s.rotate + 'deg',
                       transform: `rotate(${s.rotate}deg)`, animationDuration: s.duration + 's' }" />
      </div>
    </section>

    <!-- ══════════════════════════════════════════════
         ABOUT
    ═══════════════════════════════════════════════ -->
      <section id="about"
               class="relative min-h-screen flex flex-col items-center justify-center
                      px-4 sm:px-6 py-20 sm:py-24 reveal-group">
        <div class="absolute inset-0 pointer-events-none opacity-[0.06]" aria-hidden="true"
             style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px),
                                      linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px);
                    background-size: 56px 56px;"></div>
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none" aria-hidden="true">
          <img v-for="(s, si) in starFieldPool.slice(2, 16)" :key="si" src="/images/ornaments/bintangpx.svg" alt=""
               class="absolute animate-star-twinkle"
               :style="{ top: s.top, left: s.left, width: (s.size * 1.7) + 'px',
                         '--star-opacity': s.opacity, '--star-rotate': s.rotate + 'deg',
                         transform: `rotate(${s.rotate}deg)`, animationDuration: s.duration + 's' }" />
        </div>

        <!-- Sambungan Hero↔Tentang — aset gabungan (awan + beam penghubung) dari desain kamu.
             viewBox 5326×2486; beam-nya ada di y=1177-1327 (titik tengah ≈50.36% dari tinggi
             gambar), jadi digeser naik persis segitu supaya beam pas di garis batas
             hero/About dan awan di atas/bawahnya seimbang & proporsional. -->
        <img src="/images/ornaments/awan-antar-section.svg" alt=""
             class="absolute top-0 inset-x-0 w-full h-auto -translate-y-[50.36%] z-[6]"
             aria-hidden="true" draggable="false" />

        <div class="relative max-w-6xl mx-auto">
          <div class="relative bg-white border-4 border-white rounded-3xl shadow-2xl p-8 sm:p-12 reveal-item overflow-hidden">
            <div class="absolute inset-0 pointer-events-none opacity-[0.04]"
                 style="background-image: radial-gradient(circle, #1e3a8a 1px, transparent 1px); background-size: 22px 22px;"
                 aria-hidden="true"></div>
            <div class="relative grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
              <div>
                <p class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-3">Tentang S-Tech</p>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-blue-900 mb-5 leading-snug">
                  Ruang Bertumbuh bagi<br>Generasi Teknologi
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
                <div v-for="item in aboutHighlights" :key="item.title"
                     class="lift-on-hover flex items-start gap-4 p-4 rounded-2xl border-2 border-blue-100
                            bg-blue-50 hover:bg-blue-100 hover:border-blue-200 hover:shadow-lg">
                  <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0">
                    <component :is="item.icon" :size="17" />
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-blue-900 mb-0.5">{{ item.title }}</p>
                    <p class="text-xs text-gray-600 leading-relaxed">{{ item.desc }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

    <!-- ══════════════════════════════════════════════
         LOMBA & SEMINAR (digabung — dua panggung, satu section)
    ═══════════════════════════════════════════════ -->
      <section class="relative px-4 sm:px-6 py-20 sm:py-24 reveal-group overflow-hidden">
        <div class="absolute inset-0 pointer-events-none opacity-[0.06]" aria-hidden="true"
             style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px),
                                      linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px);
                    background-size: 56px 56px;"></div>
        <!-- Awan sisi kiri/kanan — ditaruh di pinggir sejati (bukan pinggir kartu) supaya
             mengisi ruang kosong di layar lebar, dengan rotasi & ukuran bervariasi
             supaya tidak terasa seperti 4 salinan identik. Tidak lagi menempel pas
             di batas atas/bawah section (dulu kepotong overflow-hidden jadi terlihat
             seperti "kotak" kecil) — sekarang digeser masuk secukupnya + boleh miring. -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none" aria-hidden="true">
          <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2
                      w-[500px] h-[300px] md:w-[850px] md:h-[550px]
                      bg-white/[0.06] rounded-full blur-3xl"></div>
          <img src="/images/ornaments/awan-kiri.svg" alt=""
               class="absolute top-10 sm:top-8 left-0 w-24 sm:w-40 md:w-56 -translate-x-8 sm:-translate-x-10
                      opacity-85 rotate-[-10deg]
                      animate-cloud-drift [animation-duration:15s] [animation-delay:-3s]"
               draggable="false" />
          <img src="/images/ornaments/awan-kanan.svg" alt=""
               class="absolute top-16 sm:top-12 right-0 w-20 sm:w-32 md:w-44 translate-x-6 sm:translate-x-8
                      opacity-85 rotate-[8deg]
                      animate-cloud-drift [animation-duration:14s] [animation-delay:-7s]"
               draggable="false" />
          <img src="/images/ornaments/awan-kiri-blur.svg" alt=""
               class="absolute bottom-14 sm:bottom-10 left-0 w-16 sm:w-24 md:w-32 -translate-x-6 sm:-translate-x-8
                      opacity-55 rotate-[16deg]
                      animate-cloud-drift [animation-duration:17s] [animation-delay:-5s]"
               draggable="false" />
          <img src="/images/ornaments/awan-kanan-blur.svg" alt=""
               class="absolute bottom-8 sm:bottom-6 right-0 w-24 sm:w-36 md:w-48 translate-x-8 sm:translate-x-10
                      opacity-55 rotate-[-14deg]
                      animate-cloud-drift [animation-duration:16s] [animation-delay:-9s]"
               draggable="false" />
          <img src="/images/ornaments/awan-kiri-blur.svg" alt=""
               class="absolute top-1/2 left-0 -translate-y-1/2 w-14 sm:w-20 -translate-x-10 sm:-translate-x-12
                      opacity-30 rotate-[-30deg] hidden sm:block
                      animate-cloud-drift [animation-duration:19s] [animation-delay:-11s]"
               draggable="false" />
          <img src="/images/ornaments/awan-kanan-blur.svg" alt=""
               class="absolute top-1/2 right-0 -translate-y-1/2 w-14 sm:w-20 translate-x-10 sm:translate-x-12
                      opacity-30 rotate-[26deg] hidden sm:block
                      animate-cloud-drift [animation-duration:18s] [animation-delay:-13s]"
               draggable="false" />
          <img v-for="(s, si) in starFieldPool.slice(5, 18)" :key="si" src="/images/ornaments/bintangpx.svg" alt=""
               class="absolute animate-star-twinkle"
               :style="{ top: s.top, left: s.left, width: s.size + 'px',
                         '--star-opacity': s.opacity, '--star-rotate': s.rotate + 'deg',
                         transform: `rotate(${s.rotate}deg)`, animationDuration: s.duration + 's' }" />
        </div>

        <div class="relative max-w-6xl mx-auto">
        <div class="relative mb-8 text-center reveal-item">
          <p class="text-xs font-bold text-amber-200 uppercase tracking-widest mb-2">Event Utama</p>
          <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white">Lomba &amp; Seminar Nasional</h2>

        </div>

        <div class="relative grid grid-cols-1 lg:grid-cols-2 gap-6">

          <!-- LOMBA -->
          <div id="competition" class="reveal-item bg-white border-4 border-white rounded-3xl shadow-2xl p-6 sm:p-8 flex flex-col">
            <p class="text-[11px] font-bold text-blue-500 uppercase tracking-widest mb-2">Lomba</p>
            <h3 class="text-xl sm:text-2xl font-bold text-blue-900 mb-3">Web Development Challenge 2026</h3>
            <p class="text-sm text-gray-600 leading-relaxed mb-5">
              Rancang dan bangun aplikasi web yang inovatif, fungsional, dan berdampak nyata
              di hadapan para juri profesional.
            </p>
            <div class="flex flex-wrap gap-2 mb-5">
              <div class="px-3 py-1.5 rounded-lg border-2 border-blue-100 bg-blue-50">
                <p class="text-[9px] text-blue-600 font-semibold">Peserta</p>
                <p class="text-xs font-semibold text-blue-900">SMA/SMK &amp; Mahasiswa</p>
              </div>
              <div class="px-3 py-1.5 rounded-lg border-2 border-blue-100 bg-blue-50">
                <p class="text-[9px] text-blue-600 font-semibold">Format Tim</p>
                <p class="text-xs font-semibold text-blue-900">1–4 Orang</p>
              </div>
              <div class="px-3 py-1.5 rounded-lg border-2 border-blue-100 bg-blue-50">
                <p class="text-[9px] text-blue-600 font-semibold">Biaya</p>
                <p class="text-xs font-semibold text-blue-900">Rp 100.000</p>
              </div>
            </div>

            <!-- Fokus utama kartu ini -->
            <div class="relative p-5 rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100/60
                        border-2 border-amber-200 shadow-lg shadow-amber-500/10 mb-4">
              <div class="absolute left-0 top-5 bottom-5 w-1 rounded-full bg-amber-400"></div>
              <p class="text-xs text-amber-700 mb-1 uppercase tracking-wider font-bold pl-3">Total Hadiah</p>
              <p class="text-4xl font-extrabold text-amber-700 mb-0.5 pl-3">Rp 5 Jt+</p>
              <p class="text-sm text-gray-500 pl-3">Juara 1 · Juara 2 · Juara 3</p>
            </div>

            <button @click="openModal('register')"
                    class="mt-auto w-full py-3.5 text-sm font-bold rounded-xl
                           bg-amber-500 hover:bg-amber-600 text-white
                           shadow-lg shadow-amber-500/40 transition-all duration-200 active:scale-[0.98]">
              Daftar Lomba Sekarang →
            </button>
          </div>

          <!-- SEMINAR -->
          <div id="seminar" class="reveal-item [transition-delay:120ms] bg-white border-4 border-white rounded-3xl shadow-2xl p-6 sm:p-8 flex flex-col">
            <p class="text-[11px] font-bold text-blue-500 uppercase tracking-widest mb-2">Seminar Nasional</p>
            <h3 class="text-xl sm:text-2xl font-bold text-blue-900 mb-3">Teknologi &amp; Karir di Era Digital</h3>
            <p class="text-sm text-gray-600 leading-relaxed mb-5">
              Seminar nasional menghadirkan praktisi dan pakar industri untuk berbagi wawasan
              tentang tren teknologi terkini dan peluang karir.
            </p>
            <div class="flex flex-wrap gap-2 mb-5">
              <div class="px-3 py-1.5 rounded-lg border-2 border-blue-100 bg-blue-50">
                <p class="text-[9px] text-blue-600 font-semibold">Tanggal</p>
                <p class="text-xs font-semibold text-blue-900">31 Oktober 2026</p>
              </div>
              <div class="px-3 py-1.5 rounded-lg border-2 border-blue-100 bg-blue-50">
                <p class="text-[9px] text-blue-600 font-semibold">Format</p>
                <p class="text-xs font-semibold text-blue-900">Offline</p>
              </div>
              <div class="px-3 py-1.5 rounded-lg border-2 border-blue-100 bg-blue-50">
                <p class="text-[9px] text-blue-600 font-semibold">Sertifikat</p>
                <p class="text-xs font-semibold text-blue-900">E-Certificate</p>
              </div>
              <div class="px-3 py-1.5 rounded-lg border-2 border-blue-100 bg-blue-50">
                <p class="text-[9px] text-blue-600 font-semibold">Biaya</p>
                <p class="text-xs font-semibold text-blue-900">Gratis</p>
              </div>
            </div>

            <p class="text-[11px] font-bold text-blue-500 uppercase tracking-wider mb-2">Topik Bahasan</p>
            <div class="space-y-1 mb-5">
              <div v-for="topic in seminarTopics" :key="topic.label"
                   class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-blue-50 transition-all duration-200">
                <component :is="topic.icon" :size="16" class="text-blue-500 flex-shrink-0" />
                <p class="text-sm text-gray-700 font-medium">{{ topic.label }}</p>
              </div>
            </div>

            <a href="#" target="_blank" rel="noopener noreferrer"
               class="mt-auto w-full py-3.5 text-sm font-bold rounded-xl text-center
                      bg-amber-500 hover:bg-amber-600 text-white
                      shadow-lg shadow-amber-500/40 transition-all duration-200 active:scale-[0.98]">
              Daftar Seminar Sekarang →
            </a>
          </div>
        </div>
        </div>
      </section>

    <!-- ══════════════════════════════════════════════
         TIMELINE
    ═══════════════════════════════════════════════ -->
      <section id="timeline"
               class="relative px-4 sm:px-6 py-20 sm:py-24 reveal-group overflow-hidden">
        <!-- Foto bersama tim S-Tech — background section, opacity rendah &
             tepi memudar (mask) supaya menyatu dengan gradient biru sekitarnya. -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none" aria-hidden="true">
          <img src="/images/fotbar.webp" alt="" loading="lazy"
               class="w-full h-full object-cover opacity-[0.16] grayscale-[15%]"
               style="mask-image: radial-gradient(ellipse 75% 70% at center, black 35%, transparent 100%);
                      -webkit-mask-image: radial-gradient(ellipse 75% 70% at center, black 35%, transparent 100%);"
               draggable="false" />
        </div>
        <div class="absolute inset-0 pointer-events-none opacity-[0.06]" aria-hidden="true"
             style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px),
                                      linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px);
                    background-size: 56px 56px;"></div>
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none" aria-hidden="true">
          <img v-for="(s, si) in starFieldPool.slice(9, 20)" :key="si" src="/images/ornaments/bintangpx.svg" alt=""
               class="absolute animate-star-twinkle"
               :style="{ top: s.top, left: s.left, width: s.size + 'px',
                         '--star-opacity': s.opacity, '--star-rotate': s.rotate + 'deg',
                         transform: `rotate(${s.rotate}deg)`, animationDuration: s.duration + 's' }" />
        </div>
        <div class="relative max-w-6xl mx-auto">
          <div class="mb-10 sm:mb-14 text-center reveal-item">
            <p class="text-xs font-bold text-amber-200 uppercase tracking-widest mb-2">Jadwal</p>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white">Timeline Acara</h2>
          </div>
          <div class="reveal-item [transition-delay:120ms] bg-white border-4 border-white rounded-3xl shadow-2xl p-6 sm:p-8">

            <!-- Mobile: vertical timeline — titik terakhir (Grand Final) ditandai emas -->
            <div class="md:hidden relative pl-2">
              <div class="absolute top-1 bottom-1 left-[19px] w-0.5 bg-gradient-to-b from-blue-100 to-amber-300"></div>
              <div v-for="(item, i) in timeline" :key="i"
                   class="relative flex items-start gap-4 pb-7 last:pb-0">
                <div :class="['relative z-10 flex-shrink-0 w-10 h-10 rounded-full border-2 flex items-center justify-center',
                              item.done ? 'border-green-500 bg-green-500 shadow-lg shadow-green-500/30'
                              : i === timeline.length - 1 ? 'border-amber-400 bg-amber-50 shadow-lg shadow-amber-400/30'
                              : item.current ? 'border-blue-500 bg-blue-500 ring-4 ring-blue-100'
                              : 'border-blue-200 bg-white']">
                  <svg v-if="item.done" class="w-4 h-4 text-white"
                       viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <span v-else class="text-xs font-bold"
                        :class="item.current ? 'text-white' : i === timeline.length - 1 ? 'text-amber-500' : 'text-blue-300'">{{ i + 1 }}</span>
                </div>
                <div class="pt-2">
                  <p class="text-sm font-semibold leading-snug"
                     :class="item.done ? 'text-green-700' : i === timeline.length - 1 ? 'text-amber-600 font-bold' : item.current ? 'text-blue-700 font-bold' : 'text-gray-500'">
                    {{ item.label }}
                  </p>
                  <p class="text-xs mt-1"
                     :class="item.done ? 'text-green-700' : i === timeline.length - 1 ? 'text-amber-500' : 'text-gray-500'">
                    {{ item.date }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Desktop: baris sejajar — titik terakhir (Grand Final) ditandai emas -->
            <div class="hidden md:flex items-start relative">
              <div class="absolute top-5 left-0 right-0 h-0.5 bg-gradient-to-r from-blue-100 via-blue-100 to-amber-300"></div>
              <div v-for="(item, i) in timeline" :key="i"
                   class="relative flex flex-col items-center text-center flex-1 px-2">
                <div :class="['w-10 h-10 rounded-full border-2 flex items-center justify-center z-10 mb-4',
                              item.done ? 'border-green-500 bg-green-500 shadow-lg shadow-green-500/30'
                              : i === timeline.length - 1 ? 'border-amber-400 bg-amber-50 shadow-lg shadow-amber-400/40'
                              : item.current ? 'border-blue-500 bg-blue-500 ring-4 ring-blue-100'
                              : 'border-blue-200 bg-white']">
                  <svg v-if="item.done" class="w-4 h-4 text-white"
                       viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <span v-else class="text-xs font-bold"
                        :class="item.current ? 'text-white' : i === timeline.length - 1 ? 'text-amber-500' : 'text-blue-300'">{{ i + 1 }}</span>
                </div>
                <p class="text-xs font-semibold leading-snug"
                   :class="item.done ? 'text-green-700' : i === timeline.length - 1 ? 'text-amber-600 font-bold' : item.current ? 'text-blue-700 font-bold' : 'text-gray-500'">
                  {{ item.label }}
                </p>
                <p class="text-[10px] mt-1.5"
                   :class="item.done ? 'text-green-700' : i === timeline.length - 1 ? 'text-amber-500' : 'text-gray-500'">
                  {{ item.date }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

    <!-- ══════════════════════════════════════════════
         CTA BANNER
    ═══════════════════════════════════════════════ -->
      <section class="relative px-4 sm:px-6 py-16 sm:py-20 overflow-hidden reveal-group">
        <!-- Foto juara 1/2/3 — mengintip di belakang kartu CTA, opacity rendah &
             tepi memudar (mask) supaya menyatu ke background, bukan tempelan kotak. -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none" aria-hidden="true">
          <img src="/images/juara1.webp" alt="" loading="lazy"
               class="absolute -top-10 -left-8 sm:top-0 sm:left-2 md:left-8
                      w-44 sm:w-60 md:w-72 aspect-[4/3] object-cover rounded-3xl
                      opacity-[0.11] grayscale-[20%] -rotate-6"
               style="mask-image: radial-gradient(ellipse 65% 65% at center, black 30%, transparent 100%);
                      -webkit-mask-image: radial-gradient(ellipse 65% 65% at center, black 30%, transparent 100%);"
               draggable="false" />
          <img src="/images/juara3.webp" alt="" loading="lazy"
               class="absolute -top-8 -right-10 sm:top-0 sm:right-2 md:right-10
                      w-40 sm:w-56 md:w-64 aspect-[4/3] object-cover rounded-3xl
                      opacity-[0.1] grayscale-[20%] rotate-4"
               style="mask-image: radial-gradient(ellipse 65% 65% at center, black 30%, transparent 100%);
                      -webkit-mask-image: radial-gradient(ellipse 65% 65% at center, black 30%, transparent 100%);"
               draggable="false" />
          <img src="/images/juara2.webp" alt="" loading="lazy"
               class="absolute -bottom-12 left-1/4 sm:-bottom-6 sm:left-8 md:left-20
                      w-48 sm:w-64 md:w-72 aspect-[4/3] object-cover rounded-3xl
                      opacity-[0.11] grayscale-[20%] rotate-3"
               style="mask-image: radial-gradient(ellipse 65% 65% at center, black 30%, transparent 100%);
                      -webkit-mask-image: radial-gradient(ellipse 65% 65% at center, black 30%, transparent 100%);"
               draggable="false" />
          <!-- Foto "po" — di pojok kanan-bawah, cermin dari juara2 di kiri-bawah,
               supaya sisi kanan sama-sama membentang penuh (atas→bawah) seperti sisi kiri. -->
          <img src="/images/po.webp" alt="" loading="lazy"
               class="absolute -bottom-10 -right-8 sm:-bottom-4 sm:right-6 md:right-16
                      w-40 sm:w-52 md:w-60 aspect-[3/2] object-cover rounded-3xl
                      opacity-[0.1] grayscale-[20%] -rotate-5"
               style="mask-image: radial-gradient(ellipse 65% 65% at center, black 30%, transparent 100%);
                      -webkit-mask-image: radial-gradient(ellipse 65% 65% at center, black 30%, transparent 100%);"
               draggable="false" />
        </div>
        <div class="absolute inset-0 pointer-events-none opacity-[0.06]" aria-hidden="true"
             style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px),
                                      linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px);
                    background-size: 56px 56px;"></div>
        <!-- Titik "menembus cahaya" — puncak dari perjalanan gradient halaman -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
          <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2
                      w-[700px] h-[500px] bg-amber-300/25 rounded-full blur-[110px]"></div>
          <img v-for="(s, si) in starFieldPool.slice(13, 24)" :key="si" src="/images/ornaments/bintangpx.svg" alt=""
               class="absolute animate-star-twinkle"
               :style="{ top: s.top, left: s.left, width: s.size + 'px',
                         '--star-opacity': s.opacity, '--star-rotate': s.rotate + 'deg',
                         transform: `rotate(${s.rotate}deg)`, animationDuration: s.duration + 's' }" />
        </div>
        <div class="relative max-w-3xl mx-auto text-center reveal-item">
          <div class="bg-white border-4 border-white rounded-3xl shadow-2xl p-8 sm:p-12">
            <div>
              <p class="text-xs font-bold text-amber-700 uppercase tracking-widest mb-3">Bergabung Sekarang</p>
              <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-blue-900 mb-3">
                Siap Menunjukkan Kemampuanmu?
              </h2>
              <p class="text-sm sm:text-base text-gray-500 mb-8 max-w-md mx-auto">
                Daftar sekarang dan jadilah bagian dari generasi developer
                terbaik Informatika Unsoed.
              </p>
              <button @click="openModal('register')" id="cta-register-btn"
                      class="px-8 sm:px-10 py-3.5 text-sm font-bold rounded-xl
                             bg-amber-500 hover:bg-amber-600 text-white
                             shadow-xl shadow-amber-500/40 hover:shadow-amber-600/50
                             transition-all duration-200 active:scale-[0.98]">
                Daftar Sekarang →
              </button>
            </div>
          </div>
        </div>
      </section>
    </main>

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
            <button @click="closeModal" id="modal-close-btn" aria-label="Tutup"
                    class="absolute top-4 right-4 w-7 h-7 rounded-lg z-10
                           border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-100
                           flex items-center justify-center transition-all duration-200">
              <X :size="14" />
            </button>

            <!-- Tab switcher -->
            <div class="flex border-b border-gray-200 mt-2 sm:mt-0 flex-shrink-0">
              <button @click="modalTab = 'login'"
                      :class="['flex-1 py-4 text-sm font-semibold transition-all duration-200',
                               modalTab === 'login'
                                 ? 'text-blue-700 border-b-2 border-blue-600'
                                 : 'text-gray-500 hover:text-gray-600']">
                Masuk
              </button>
              <button @click="modalTab = 'register'"
                      :class="['flex-1 py-4 text-sm font-semibold transition-all duration-200',
                               modalTab === 'register'
                                 ? 'text-blue-700 border-b-2 border-blue-600'
                                 : 'text-gray-500 hover:text-gray-600']">
                Daftar
              </button>
            </div>

            <!-- ── LOGIN TAB ── -->
            <div v-if="modalTab === 'login'" class="p-6 sm:p-8 overflow-y-auto">
              <div class="mb-6">
                <p class="text-sm font-bold leading-none text-blue-900">Soedirman Technophoria</p>
                <p class="text-[10px] text-gray-500 mt-1">Portal Peserta</p>
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
                               bg-amber-500 hover:bg-amber-600 text-white
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
              <p class="mt-5 text-center text-xs text-gray-500">
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
                  <span class="text-xs text-gray-500 font-semibold">Info Tim</span>
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
                      <option :value="1">1 Orang</option>
                      <option :value="2">2 Orang</option>
                      <option :value="3">3 Orang</option>
                      <option :value="4">4 Orang</option>
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
                <div class="flex items-start gap-2 px-4 py-3 rounded-xl border-2 border-amber-200 bg-amber-50 text-xs text-amber-700">
                  <CreditCard :size="14" class="flex-shrink-0 mt-0.5" />
                  <span>Biaya pendaftaran: <strong>Rp 75.000 / tim</strong>. Bukti transfer diunggah setelah daftar.</span>
                </div>

                <!-- Submit -->
                <button type="submit" id="register-submit-btn" :disabled="registerForm.loading"
                        class="w-full py-3 rounded-xl text-sm font-bold
                               bg-amber-500 hover:bg-amber-600 text-white
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
              <p class="mt-5 text-center text-xs text-gray-500">
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
