<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

// ── Types ─────────────────────────────────────────────────────────────────────
interface LoginForm {
  email: string
  password: string
  loading: boolean
  error: string
}

// ── State ─────────────────────────────────────────────────────────────────────
const showLoginModal  = ref(false)
const heroVisible     = ref(false)
const cardsVisible    = ref(false)
const mobileMenuOpen  = ref(false)

const form = ref<LoginForm>({
  email: '', password: '', loading: false, error: '',
})

// ── Competition info ──────────────────────────────────────────────────────────
const webDevTracks = [
  { icon: '◈', label: 'Front-End Design', desc: 'UI/UX & implementasi antarmuka modern' },
  { icon: '⬡', label: 'Full-Stack Web',    desc: 'Arsitektur end-to-end & database' },
  { icon: '◻', label: 'Web Innovation',   desc: 'Solusi web untuk masalah nyata' },
]

const seminarTopics = [
  { icon: '◆', label: 'Web Development Trends 2026' },
  { icon: '⬟', label: 'Career Path di Industri Tech' },
  { icon: '◇', label: 'Networking & Kolaborasi Industri' },
]

// ── Timeline ─────────────────────────────────────────────────────────────────
const timeline = [
  { date: '1 Jun 2026',  label: 'Pendaftaran Dibuka',  done: true  },
  { date: '30 Jun 2026', label: 'Batas Pendaftaran',   done: false },
  { date: '5 Jul 2026',  label: 'Technical Meeting',   done: false },
  { date: '15 Jul 2026', label: 'Penyisihan Online',   done: false },
  { date: '22 Jul 2026', label: 'Grand Final & Seminar Nasional', done: false },
]

// ── Methods ───────────────────────────────────────────────────────────────────
function openLoginModal() {
  showLoginModal.value = true
  mobileMenuOpen.value = false
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
  router.post('/login', { email: form.value.email, password: form.value.password }, {
    onError: () => { form.value.loading = false; form.value.error = 'Login gagal.' },
  })
}

onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  if (params.get('login') === '1') showLoginModal.value = true

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
        <a href="/" class="flex items-center gap-2.5 group flex-shrink-0">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600
                      flex items-center justify-center text-sm font-bold shadow-lg
                      group-hover:shadow-violet-500/25 transition-shadow duration-300">S</div>
          <div class="leading-tight">
            <span class="block text-sm font-bold tracking-tight">S-Tech</span>
            <span class="block text-[10px] text-white/35 -mt-0.5">Soedirman Technophoria</span>
          </div>
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
          <button @click="openLoginModal" id="nav-login-btn"
                  class="px-4 py-2 text-sm font-medium rounded-lg border border-white/10
                         text-white/70 hover:text-white hover:bg-white/5 hover:border-white/20
                         transition-all duration-200">
            Masuk
          </button>
          <button @click="openLoginModal" id="nav-register-btn"
                  class="px-4 py-2 text-sm font-semibold rounded-lg
                         bg-violet-600 hover:bg-violet-500 text-white
                         shadow-md shadow-violet-600/20 transition-all duration-200 active:scale-95">
            Daftar
          </button>
        </div>

        <!-- Mobile hamburger -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" id="mobile-menu-btn"
                class="md:hidden w-9 h-9 flex flex-col items-center justify-center gap-1.5
                       rounded-lg border border-white/10 hover:bg-white/5
                       transition-all duration-200">
          <span :class="['block w-5 h-0.5 bg-white/60 rounded transition-all duration-300',
                         mobileMenuOpen ? 'rotate-45 translate-y-2' : '']"></span>
          <span :class="['block w-5 h-0.5 bg-white/60 rounded transition-all duration-300',
                         mobileMenuOpen ? 'opacity-0' : '']"></span>
          <span :class="['block w-5 h-0.5 bg-white/60 rounded transition-all duration-300',
                         mobileMenuOpen ? '-rotate-45 -translate-y-2' : '']"></span>
        </button>
      </div>

      <!-- Mobile menu drawer -->
      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div v-if="mobileMenuOpen"
             class="md:hidden border-t border-white/[0.06] bg-[#0d0d0d] px-4 py-4 space-y-1">
          <a href="#about"       @click="mobileMenuOpen=false"
             class="block px-3 py-2.5 text-sm text-white/60 hover:text-white
                    hover:bg-white/5 rounded-lg transition-all duration-200">Tentang</a>
          <a href="#competition" @click="mobileMenuOpen=false"
             class="block px-3 py-2.5 text-sm text-white/60 hover:text-white
                    hover:bg-white/5 rounded-lg transition-all duration-200">Lomba Web Dev</a>
          <a href="#seminar"     @click="mobileMenuOpen=false"
             class="block px-3 py-2.5 text-sm text-white/60 hover:text-white
                    hover:bg-white/5 rounded-lg transition-all duration-200">Seminar Nasional</a>
          <a href="#timeline"    @click="mobileMenuOpen=false"
             class="block px-3 py-2.5 text-sm text-white/60 hover:text-white
                    hover:bg-white/5 rounded-lg transition-all duration-200">Timeline</a>
          <div class="pt-3 flex flex-col gap-2">
            <button @click="openLoginModal"
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

      <!-- Ambient glow -->
      <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2
                    w-[600px] h-[400px] md:w-[900px] md:h-[600px]
                    bg-violet-700/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 left-1/4 w-[300px] h-[300px]
                    bg-indigo-600/8 rounded-full blur-3xl"></div>
      </div>

      <!-- Grid pattern -->
      <div class="absolute inset-0 pointer-events-none opacity-[0.025]"
           style="background-image: linear-gradient(rgba(255,255,255,0.4) 1px, transparent 1px),
                                    linear-gradient(90deg, rgba(255,255,255,0.4) 1px, transparent 1px);
                  background-size: 56px 56px;"></div>

      <Transition enter-active-class="transition-all duration-700 ease-out"
                  enter-from-class="opacity-0 translate-y-8"
                  enter-to-class="opacity-100 translate-y-0">
        <div v-if="heroVisible" class="relative z-10 max-w-4xl mx-auto">

          <!-- Badge -->
          <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-6 sm:mb-8 rounded-full
                      border border-violet-500/25 bg-violet-500/8 text-violet-300 text-xs font-medium">
            <span class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-pulse"></span>
            Event Tahunan · Informatika Unsoed · 2026
          </div>

          <!-- Headline -->
          <h1 class="text-4xl sm:text-6xl md:text-7xl lg:text-8xl font-extrabold
                     tracking-tight leading-[1.05] mb-5 sm:mb-6">
            <span class="text-white">Soedirman</span><br>
            <span class="bg-gradient-to-r from-violet-400 via-indigo-300 to-violet-500
                         bg-clip-text text-transparent">Technophoria</span>
          </h1>

          <!-- Description -->
          <p class="max-w-2xl mx-auto text-sm sm:text-base md:text-lg text-white/45
                    leading-relaxed mb-4 px-2">
            Event teknologi tahunan yang mempertemukan pelajar SMA/SMK, mahasiswa,
            dan masyarakat umum dalam satu panggung kompetisi dan edukasi.
            Buktikan kemampuanmu di
            <span class="text-white/70 font-medium">Lomba Web Development</span>
            dan perluas wawasanmu lewat
            <span class="text-white/70 font-medium">Seminar Nasional</span>
            bersama para pakar industri.
          </p>

          <!-- CTAs -->
          <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-8 sm:mt-10">
            <button @click="openLoginModal" id="hero-register-btn"
                    class="w-full sm:w-auto px-7 py-3.5 text-sm font-semibold rounded-xl
                           bg-violet-600 hover:bg-violet-500 text-white
                           shadow-lg shadow-violet-600/25 hover:shadow-violet-500/40
                           transition-all duration-200 active:scale-[0.98]">
              Daftar Sekarang →
            </button>
            <a href="#about"
               class="w-full sm:w-auto px-7 py-3.5 text-sm font-medium text-center rounded-xl
                      border border-white/10 text-white/60 hover:text-white
                      hover:border-white/20 hover:bg-white/5 transition-all duration-200">
              Pelajari Lebih Lanjut
            </a>
          </div>

          <!-- Audience badges -->
          <div class="flex flex-wrap items-center justify-center gap-3 mt-12 sm:mt-14">
            <span class="px-4 py-2 rounded-full border border-white/10 bg-white/5
                         text-xs text-white/50 font-medium">
              🎓 Mahasiswa
            </span>
            <span class="px-4 py-2 rounded-full border border-white/10 bg-white/5
                         text-xs text-white/50 font-medium">
              📚 Pelajar SMA / SMK
            </span>
            <span class="px-4 py-2 rounded-full border border-white/10 bg-white/5
                         text-xs text-white/50 font-medium">
              🌐 Masyarakat Umum
            </span>
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
               class="px-4 sm:px-6 py-20 sm:py-24 border-t border-white/[0.05]
                      bg-white/[0.015]">
        <div class="max-w-6xl mx-auto">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">

            <!-- Text -->
            <div>
              <p class="text-xs font-semibold text-violet-400 uppercase tracking-widest mb-3">
                Tentang S-Tech
              </p>
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
              <p class="text-sm sm:text-base text-white/50 leading-relaxed">
                Kami percaya bahwa inovasi lahir dari kolaborasi. S-Tech bukan sekadar
                kompetisi — ini adalah ekosistem yang menghubungkan talenta muda
                dengan industri, mempertemukan ide dengan kesempatan, dan mengubah
                kreativitas menjadi solusi nyata.
              </p>
            </div>

            <!-- Pillars grid -->
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
         COMPETITION — WEB DEVELOPMENT
    ═══════════════════════════════════════════════ -->
    <Transition enter-active-class="transition-all duration-700 ease-out delay-75"
                enter-from-class="opacity-0 translate-y-8"
                enter-to-class="opacity-100 translate-y-0">
      <section v-if="cardsVisible" id="competition"
               class="px-4 sm:px-6 py-20 sm:py-24 max-w-6xl mx-auto">

        <div class="mb-10 sm:mb-12">
          <p class="text-xs font-semibold text-violet-400 uppercase tracking-widest mb-2">
            Cabang Lomba
          </p>
          <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white">
            Lomba Web Development
          </h2>
          <p class="text-sm sm:text-base text-white/40 mt-2 max-w-xl">
            Tunjukkan kemampuan membangun solusi web inovatif di hadapan para juri
            profesional dari industri teknologi.
          </p>
        </div>

        <!-- Main competition card -->
        <div class="relative overflow-hidden rounded-3xl border border-violet-500/20
                    bg-gradient-to-br from-violet-900/20 via-[#0d0b1a] to-[#080808] p-6 sm:p-8 lg:p-10 mb-6">
          <!-- Glow -->
          <div class="absolute top-0 right-0 w-64 h-64 bg-violet-600/10 rounded-full
                      blur-3xl pointer-events-none"></div>

          <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">

            <!-- Left: description -->
            <div>
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full
                          border border-violet-500/25 bg-violet-500/10 text-violet-300
                          text-xs font-medium mb-5">
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
                Bersaing bersama peserta terbaik dari berbagai sekolah dan
                perguruan tinggi, dinilai langsung oleh juri profesional dari
                industri teknologi.
              </p>

              <!-- Meta info -->
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

            <!-- Right: prize + guidebook -->
            <div class="space-y-4">

              <!-- Prize card -->
              <div class="p-6 rounded-2xl border border-amber-500/20 bg-amber-500/5">
                <p class="text-xs text-amber-400/70 mb-2 uppercase tracking-wider font-semibold">Total Hadiah</p>
                <p class="text-4xl font-extrabold text-amber-300 mb-1">Rp 5 Jt+</p>
                <p class="text-sm text-white/40">Juara 1 · Juara 2 · Juara 3</p>
                <div class="mt-4 pt-4 border-t border-amber-500/10 flex flex-wrap gap-2">
                  <span class="px-2.5 py-1 rounded-lg border border-amber-500/20
                               text-[10px] text-amber-300/70">🏆 Trophy</span>
                  <span class="px-2.5 py-1 rounded-lg border border-amber-500/20
                               text-[10px] text-amber-300/70">📜 E-Certificate</span>
                  <span class="px-2.5 py-1 rounded-lg border border-amber-500/20
                               text-[10px] text-amber-300/70">🤝 Networking</span>
                </div>
              </div>

              <!-- Guidebook notice -->
              <div class="p-4 rounded-2xl border border-white/[0.07] bg-white/[0.03]
                          flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5 w-8 h-8 rounded-lg border border-violet-500/25
                            bg-violet-500/10 flex items-center justify-center text-base">
                  📖
                </div>
                <div>
                  <p class="text-sm font-semibold text-white">Guidebook Tersedia</p>
                  <p class="text-xs text-white/40 mt-0.5 leading-relaxed">
                    Detail teknis, aturan, dan kriteria penilaian lengkap tersedia
                    dalam guidebook resmi yang akan dibagikan setelah pendaftaran.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <button @click="openLoginModal"
                class="w-full sm:w-auto px-8 py-3.5 text-sm font-semibold rounded-xl
                       bg-violet-600 hover:bg-violet-500 text-white
                       shadow-lg shadow-violet-600/25 transition-all duration-200 active:scale-[0.98]">
          Daftar Lomba Sekarang →
        </button>
      </section>
    </Transition>

    <!-- ══════════════════════════════════════════════
         SEMINAR NASIONAL
    ═══════════════════════════════════════════════ -->
    <Transition enter-active-class="transition-all duration-700 ease-out delay-100"
                enter-from-class="opacity-0 translate-y-8"
                enter-to-class="opacity-100 translate-y-0">
      <section v-if="cardsVisible" id="seminar"
               class="px-4 sm:px-6 py-20 sm:py-24 border-t border-white/[0.05]
                      bg-white/[0.015]">
        <div class="max-w-6xl mx-auto">

          <div class="mb-10 sm:mb-12">
            <p class="text-xs font-semibold text-indigo-400 uppercase tracking-widest mb-2">
              Acara Pendukung
            </p>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white">
              Seminar Nasional
            </h2>
            <p class="text-sm sm:text-base text-white/40 mt-2 max-w-xl">
              Sesi eksklusif bersama pembicara dari industri teknologi — terbuka
              untuk mahasiswa dan masyarakat umum.
            </p>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <!-- Seminar main card -->
            <div class="lg:col-span-2 relative overflow-hidden rounded-3xl border border-indigo-500/20
                        bg-gradient-to-br from-indigo-900/15 via-[#0a0c1a] to-[#080808]
                        p-6 sm:p-8">
              <div class="absolute top-0 right-0 w-48 h-48 bg-indigo-600/10 rounded-full
                          blur-3xl pointer-events-none"></div>
              <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full
                            border border-indigo-500/25 bg-indigo-500/10 text-indigo-300
                            text-xs font-medium mb-5">
                  <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                  Gratis untuk Mahasiswa Unsoed
                </div>
                <h3 class="text-xl sm:text-2xl font-bold text-white mb-3">
                  Teknologi & Karir di Era Digital
                </h3>
                <p class="text-sm text-white/50 leading-relaxed mb-6">
                  Seminar nasional menghadirkan praktisi dan pakar industri untuk
                  berbagi wawasan tentang tren teknologi terkini, peluang karir
                  di bidang tech, dan cara membangun koneksi profesional yang
                  bermakna.
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

            <!-- Topics list -->
            <div class="rounded-3xl border border-white/[0.07] bg-white/[0.03] p-6">
              <p class="text-xs font-semibold text-white/30 uppercase tracking-wider mb-4">
                Topik Bahasan
              </p>
              <div class="space-y-3">
                <div v-for="topic in seminarTopics" :key="topic.label"
                     class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/5
                            transition-all duration-200">
                  <span class="text-base text-indigo-300 flex-shrink-0">{{ topic.icon }}</span>
                  <p class="text-sm text-white/70">{{ topic.label }}</p>
                </div>
              </div>
              <div class="mt-6 pt-5 border-t border-white/[0.06]">
                <p class="text-xs text-white/25 mb-3">Speaker dari</p>
                <div class="flex flex-wrap gap-2">
                  <span class="px-2.5 py-1 rounded-lg border border-white/10 text-[10px] text-white/40">
                    Industri Tech
                  </span>
                  <span class="px-2.5 py-1 rounded-lg border border-white/10 text-[10px] text-white/40">
                    Akademisi
                  </span>
                  <span class="px-2.5 py-1 rounded-lg border border-white/10 text-[10px] text-white/40">
                    Startup
                  </span>
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

          <!-- Desktop: horizontal -->
          <div class="hidden md:flex items-start relative">
            <div class="absolute top-5 left-0 right-0 h-px bg-white/8"></div>
            <div v-for="(item, i) in timeline" :key="i"
                 class="relative flex flex-col items-center text-center flex-1 px-2">
              <div :class="['w-10 h-10 rounded-full border-2 flex items-center justify-center z-10 mb-4',
                            item.done
                              ? 'border-violet-500 bg-violet-500/20'
                              : i === 1
                                ? 'border-violet-500/70 bg-[#080808] ring-4 ring-violet-500/10'
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

          <!-- Mobile: vertical -->
          <div class="md:hidden space-y-0">
            <div v-for="(item, i) in timeline" :key="i" class="flex items-start gap-4">
              <div class="flex flex-col items-center">
                <div :class="['w-9 h-9 rounded-full border-2 flex items-center justify-center flex-shrink-0',
                              item.done
                                ? 'border-violet-500 bg-violet-500/20'
                                : i === 1
                                  ? 'border-violet-500/70 bg-transparent'
                                  : 'border-white/15 bg-transparent']">
                  <svg v-if="item.done" class="w-3.5 h-3.5 text-violet-400"
                       viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <span v-else class="text-xs font-bold"
                        :class="i === 1 ? 'text-violet-300' : 'text-white/25'">{{ i + 1 }}</span>
                </div>
                <div v-if="i < timeline.length - 1" class="w-px flex-1 min-h-[2.5rem] my-1"
                     :class="item.done ? 'bg-violet-500/30' : 'bg-white/8'"></div>
              </div>
              <div class="pb-6">
                <p class="text-sm font-semibold leading-snug"
                   :class="item.done ? 'text-violet-300' : i === 1 ? 'text-white' : 'text-white/50'">
                  {{ item.label }}
                </p>
                <p class="text-xs mt-0.5"
                   :class="item.done ? 'text-violet-400/60' : 'text-white/25'">
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
      <section v-if="cardsVisible"
               class="px-4 sm:px-6 py-16 sm:py-20 border-t border-white/[0.05]">
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
              <button @click="openLoginModal" id="cta-register-btn"
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
          <div class="w-6 h-6 rounded-md bg-gradient-to-br from-violet-500 to-indigo-600
                      flex items-center justify-center text-[10px] font-bold">S</div>
          <span>Soedirman Technophoria 2026</span>
        </div>
        <p>Program Kerja · Informatika · Universitas Jenderal Soedirman</p>
      </div>
    </footer>

    <!-- ══════════════════════════════════════════════
         LOGIN MODAL
    ═══════════════════════════════════════════════ -->
    <Transition enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0">
      <div v-if="showLoginModal"
           class="fixed inset-0 z-50 flex items-end sm:items-center justify-center
                  p-0 sm:p-4 bg-black/75 backdrop-blur-md"
           @click.self="closeLoginModal">
        <Transition enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100">
          <div v-if="showLoginModal"
               class="relative w-full sm:max-w-sm rounded-t-3xl sm:rounded-2xl
                      border border-white/10 bg-[#111] shadow-2xl p-6 sm:p-8">

            <!-- Mobile drag handle -->
            <div class="sm:hidden w-10 h-1 rounded-full bg-white/20 mx-auto mb-6"></div>

            <!-- Close btn -->
            <button @click="closeLoginModal" id="modal-close-btn"
                    class="absolute top-4 right-4 sm:top-5 sm:right-5 w-7 h-7 rounded-lg
                           border border-white/10 text-white/35 hover:text-white
                           flex items-center justify-center text-xs
                           transition-all duration-200">✕</button>

            <!-- Brand -->
            <div class="flex items-center gap-2.5 mb-7">
              <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600
                          flex items-center justify-center text-sm font-bold">S</div>
              <div>
                <p class="text-sm font-bold leading-none">S-Tech Portal</p>
                <p class="text-[10px] text-white/35 mt-0.5">Soedirman Technophoria</p>
              </div>
            </div>

            <h3 class="text-xl font-bold text-white mb-1">Masuk ke Akunmu</h3>
            <p class="text-sm text-white/40 mb-6">Lanjutkan proses pendaftaranmu.</p>

            <div v-if="form.error"
                 class="mb-4 px-4 py-3 rounded-xl border border-red-500/20
                        bg-red-500/10 text-red-400 text-sm">
              {{ form.error }}
            </div>

            <form @submit.prevent="submitLogin" class="space-y-4">
              <div>
                <label class="block text-xs font-medium text-white/50 mb-2">Email</label>
                <input v-model="form.email" id="login-email" type="email"
                       placeholder="kamu@email.com" autocomplete="email"
                       class="w-full px-4 py-3 rounded-xl border border-white/10 bg-white/5
                              text-sm text-white placeholder-white/20
                              focus:outline-none focus:border-violet-500/50
                              transition-all duration-200" />
              </div>
              <div>
                <label class="block text-xs font-medium text-white/50 mb-2">Password</label>
                <input v-model="form.password" id="login-password" type="password"
                       placeholder="••••••••" autocomplete="current-password"
                       class="w-full px-4 py-3 rounded-xl border border-white/10 bg-white/5
                              text-sm text-white placeholder-white/20
                              focus:outline-none focus:border-violet-500/50
                              transition-all duration-200" />
              </div>
              <button type="submit" id="login-submit-btn" :disabled="form.loading"
                      class="w-full py-3 rounded-xl text-sm font-semibold
                             bg-violet-600 hover:bg-violet-500 text-white
                             shadow-lg shadow-violet-600/20 transition-all duration-200
                             active:scale-[0.98] disabled:opacity-60
                             flex items-center justify-center gap-2 mt-2">
                <svg v-if="form.loading" class="animate-spin w-4 h-4"
                     viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10"
                          stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                {{ form.loading ? 'Memproses...' : 'Masuk ke Dashboard' }}
              </button>
            </form>

            <p class="mt-5 text-center text-xs text-white/25">
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
