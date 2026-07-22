# Soedirman Technophoria (S-Tech) — Design Brief

Competition registration platform for "Soedirman Technophoria" (S-Tech), an annual tech event by
Himpunan Mahasiswa Informatika, Universitas Jenderal Soedirman. Two tracks: a **Web Development
competition** (team registration → payment → verification → project submission) and a **national
seminar**. All UI copy is in **Indonesian**. Audience: high school students, university students,
and the general public.

Use this brief to generate/regenerate high-fidelity screens consistent with the existing product.

---

## 1. Visual identity

**Mood:** optimistic, energetic campus-tech event. A "sky at dawn" narrative — deep blue night sky
that gradually lightens, with amber/gold as the sunrise accent. Clean white content cards float on
top of the gradient like clouds.

### Color palette

| Role | Color | Notes |
|---|---|---|
| Background gradient (top → bottom) | `#1e3a8a → #1e40af → #1d4ed8 → #2563eb → #3b82f6 → #1e40af` | Tailwind `blue-900→blue-600→blue-900`; long continuous gradient down the whole landing page, dark→light→dark. Dashboard/inner pages use a simpler `blue-600 → blue-700 → blue-900` gradient. |
| Header / nav bar | `blue-900` at 95% opacity + backdrop blur | Fixed/sticky top bar, sits above the gradient |
| Primary surface (cards) | White (`#ffffff`) | `border-4 border-white`, `rounded-3xl` (cards) or `rounded-2xl` (smaller tiles), `shadow-2xl` |
| Primary accent / CTA | Amber `amber-400`/`amber-500` on `blue-950` text | Every primary button ("Daftar", "Upload", "Submit", "Masuk ke Dashboard") is solid amber with a dark-blue label and an amber glow shadow (`shadow-amber-500/30-40`) |
| Headings on white cards | `blue-900` | |
| Body text on white cards | `gray-600` / `gray-500` | |
| Text on blue gradient (no card) | white / `blue-100` / `blue-200` | |
| Success | `green-50/100/200` bg, `green-600/700` text | verified, completed, approved |
| Warning / pending | `amber-50/100/200` bg, `amber-600/700` text | pending payment, money info |
| Info / in-progress | `blue-50/100/200` bg, `blue-600/700` text | pending verification |
| Error / rejected | `red-50/100/200` bg, `red-500/600/700` text | rejection, validation errors |
| Decorative | soft white/sky radial blur blobs, drifting cloud SVGs (hero only), twinkling star SVGs (below hero), a faint city‑skyline silhouette | Used sparingly as ambient background texture, never behind readable text |

### Typography

- **Font:** Inter (Google Fonts, weights 400/500/600/700/800/900), `font-display: swap`.
- Hero H1: 4xl → 8xl (mobile → desktop), `font-extrabold`, tight tracking.
- Section H2: 2xl → 4xl, `font-bold`.
- Card H3: xl → 2xl, `font-bold`, always `blue-900` on white cards.
- Eyebrow/label text: 10-11px, `font-bold`, `uppercase`, wide tracking, amber or blue-600.
- Body: sm/base, `leading-relaxed`, gray-600 on white / blue-100 on gradient.

### Shape & elevation language

- Big rounded corners everywhere: `rounded-3xl` for major cards/panels, `rounded-2xl`/`rounded-xl`
  for nested tiles, `rounded-full`/`rounded-lg` for pills and buttons.
- White cards always carry a **4px white border** (`border-4 border-white`) plus `shadow-2xl` —
  this is the signature "card floating on gradient" look, not a subtle 1px border.
- Nested info tiles inside a card use a 2px colored border matched to their semantic tint
  (e.g. `border-2 border-blue-100 bg-blue-50`, `border-2 border-amber-200 bg-amber-50`).
- Buttons/inputs use `rounded-xl`; pills/badges use `rounded-full`.
- Icons: [lucide](https://lucide.dev) icon set (`@lucide/vue`), stroke-based, 14–22px.

### Motion

- Subtle `fade-in-up` staggered entrance on hero content.
- Scroll-reveal (`IntersectionObserver`) fades in sections as the user scrolls.
- Hover: cards lift slightly (`lift-on-hover`), buttons `active:scale-[0.98]`.
- Toasts slide up from bottom-right (dashboard) or fade down from top-right (admin).
- Step-panel transitions use a simple fade/slide (`Transition mode="out-in"`).
- Cloud ornaments drift slowly (13-18s loops); background stars twinkle — decorative only, confined
  to the landing hero/section backgrounds.

---

## 2. Core UI components (reusable across screens)

- **Primary button** — pill/rounded-xl, solid `amber-500` (hover `amber-400`), `text-blue-950`,
  `font-bold`/`font-semibold`, `shadow-lg shadow-amber-500/30-40`, `active:scale-95/98`. Disabled
  state: flat gray (`bg-gray-100 text-gray-300 border border-gray-200`).
- **Secondary / outline button** — transparent, `border-2`, color matches context (blue on dark
  header, gray/blue on white cards), no fill.
- **Text input** — `rounded-xl border-2 border-gray-200 bg-gray-50`, focus → `border-blue-500`,
  `placeholder-gray-400`. Error state swaps border to `border-red-400` with a small red helper line
  below.
- **Status badge / pill** — small rounded-full chip, 2-tone (light bg + dark text of same hue):
  amber = belum bayar, blue = menunggu verifikasi, green = terverifikasi, red = ditolak.
- **Card** — white, `border-4 border-white`, `rounded-3xl`, `shadow-2xl`, generous padding
  (`p-6`–`p-12`).
- **Info tile** — smaller nested stat/info block inside a card: `rounded-xl/2xl border-2 bg-{color}-50`.
- **Toast notification** — floating white card (`border-4 border-white`, `rounded-2xl`,
  `shadow-2xl`), colored icon circle (green check / red x), title + message, auto-dismiss.
- **Modal** — bottom-sheet on mobile (`rounded-t-3xl`, slides up), centered dialog on desktop
  (`rounded-2xl`, scale-in), white card, drag-handle on mobile, tab switcher for Login/Register.
- **Stepper** — horizontal row of numbered circles connected by a line; states: done (filled amber,
  checkmark), active (white circle, blue-900 number, scaled up), locked (translucent, disabled).
  Progress line fills amber as steps complete.
- **Data table** (admin) — rows separated by hairline dividers, sticky header, status pill column,
  right-aligned actions column, pagination footer.

---

## 3. Screens

### 3.1 Public landing page (`/`) — `Welcome.vue`

Single scrolling page, full-bleed blue gradient background (dark hero → lighter mid-page → darker
footer), fixed translucent dark-blue header.

1. **Nav bar** (fixed, blue-900/95 + blur): logo/wordmark left, in-page anchor links center
   (Tentang / Lomba / Seminar / Timeline), "Masuk" (outline) + "Daftar" (solid amber) buttons right;
   collapses to hamburger + slide-down mobile menu below `md`.
2. **Hero**: full-viewport, drifting cloud illustrations top/bottom corners, radial glow blobs,
   faint dot-grid texture. Big two-line headline ("Soedirman" white / "Technophoria" amber),
   subheadline, primary CTA "Daftar Sekarang →" (amber) + secondary "Unduh Guidebook" (outlined,
   glassy), row of 3 audience pill badges (Mahasiswa / Pelajar SMA-SMK / Masyarakat Umum). Stars
   begin twinkling in the lower third, bridging into the next section.
3. **Tentang (About)**: one big white card, two-column layout — left: eyebrow + heading + paragraph;
   right: 3 stacked highlight rows (icon chip + title + description) on light blue tiles.
4. **Lomba & Seminar**: section heading + subtext on the gradient background, faint city-skyline
   silhouette, two side-by-side white cards:
   - **Lomba card**: eyebrow, title, description, 3 quick-fact chips (Peserta/Format/Biaya), a
     highlighted amber prize callout ("Rp 5 Jt+" with a left accent bar), CTA button.
   - **Seminar card**: "gratis" pill badge, title, description, 3 quick-fact chips
     (Tanggal/Format/Sertifikat), list of 3 topic rows with icons.
5. **Timeline**: centered heading on gradient, one white card containing a 5-stop timeline —
   vertical with a gradient connector line on mobile, horizontal row on desktop; each stop is a
   circle (checkmark if done, number otherwise) with label + date underneath; the final stop
   (Grand Final) is visually emphasized in amber, current/active stop in blue with a ring.
6. **CTA banner**: centered white card on a glowing amber background blob, headline + short copy +
   single large amber "Daftar Sekarang →" button.
7. **Footer**: simple bar, translucent blue-900/60, event name + org line.
8. **Login/Register modal** (triggered by any "Masuk"/"Daftar" button): overlay with blurred
   backdrop; white sheet/dialog with a 2-tab switcher.
   - **Login tab**: email + password fields, inline error banner, amber submit button with spinner
     state, "Belum punya akun? Daftar di sini" link.
   - **Register tab**: longer form — nama lengkap ketua, email, password + confirm, divider ("Info
     Tim"), nama tim, kategori peserta (select), jumlah anggota (select 1-4), asal
     sekolah/kampus, no HP, an amber "biaya pendaftaran Rp 75.000" info strip, amber submit button.
     Field-level red error text under any invalid input.

### 3.2 Participant Dashboard (`/dashboard`) — `Dashboard.vue`

Authenticated, single participant view. Same blue gradient background (simpler 3-stop version),
sticky blue-900 topbar (brand + user avatar chip + "Keluar" outline button). All step content sits
in `max-w-4xl` centered white cards.

**Registration summary strip** (top, white card): team name, institution + member count, and a
status pill on the right (amber/blue/green, or a red "Pembayaran Ditolak" chip when rejected).

**Stepper** (5 steps): Pembayaran → Verifikasi → Biodata Tim → Submit Karya → Selesai. Circle states
done/active/locked as described in §2; clicking a completed circle should let the participant review
that step's content again (currently the intended behavior — flag if regenerating this
interaction).

**Step panels** (one visible at a time, in a rounded white card unless noted):
1. **Pembayaran** — bank-transfer info tile (amber, Bank/No. Rekening/Atas Nama/Nominal grid +
   warning note), optional "already uploaded, awaiting verification" tile (blue), drag-and-drop
   upload zone (dashed border, states: empty / drag-over(amber) / file-selected(green)), progress
   bar during upload, amber submit button. Rejection banner (red) appears above if the previous
   payment was rejected, quoting the admin's note.
2. **Verifikasi** — centered waiting state: pulsing blue icon circle, "Menunggu Verifikasi Admin"
   heading, 3 info tiles (File / Diupload / Status "Dalam Antrian").
3. **Biodata Tim** — green "Pembayaran Terverifikasi" banner, then one form block per team member
   (Nama Lengkap, NIM/NISN, No. WhatsApp, Asal Sekolah/Kampus, Jurusan/Prodi, Angkatan/Kelas, Email)
   inside light-blue tinted sub-cards; leader's card tagged "Ketua Tim"; a "copy from leader" shortcut
   for institution/major/batch on subsequent members.
4. **Submit Karya** — amber guideline checklist box, form: Judul Project, Link GitHub, Link Google
   Drive (optional), Deskripsi (textarea); green "already submitted, can update" banner if
   applicable; amber submit/update button.
5. **Selesai** — celebratory centered state: green pulsing check icon, "Semua Tahap Selesai!"
   heading + party emoji/icon, 3 summary tiles (Pembayaran ✓ / Karya title / Pengumuman pending),
   outline button to go back and review/update the submitted work.

**Finalist announcement variant** (replaces the whole step flow once ≥3 finalists are published):
big banner card (green if the viewer's own team made the final, neutral blue/white otherwise) with
a trophy icon and congratulatory or thank-you copy, followed by a 3-column grid of finalist team
cards (category, team name, institution, project title tile), each tagged "Finalis" and, if it's the
viewer's own team, an extra "Tim Anda" badge.

**Toast**: bottom-right floating white card for success/error feedback after any action.

### 3.3 Admin panel (`/admin`, `/admin/peserta/{id}`)

> Currently implemented with a plain dark theme (`bg-blue-950`, translucent `white/[0.03-0.08]`
> panels, no gradient/cloud motif) — inconsistent with the public/participant pages above. **When
> regenerating, prefer unifying it with the same light-card-on-blue-gradient system used on the
> landing page and dashboard** (white `border-4` cards, amber primary actions, blue-900 headings)
> unless a deliberate "internal ops tool" dark-mode distinction is wanted. Structure/content below;
> restyle to the palette in §1.

**Admin — Daftar Peserta** (`Admin/Index.vue`):
- Sticky header: small "ST" logo mark, "Panel Admin" label, admin name, Keluar button.
- Page title + a 2-way view toggle: "Daftar Peserta" (list) vs "Submission Karya" (progress cards).
- Stat row: Total Tim + 4 status counters (Belum Bayar / Menunggu Verifikasi / Terverifikasi /
  Ditolak), each a small tinted card with count + colored dot + label.
- **List view**: pill-tab status filter + search box, then a data table (Tim/Peserta, Institusi,
  Kategori, Daftar tanggal, Status pill, Bukti-upload note, Aksi) with a "Lolos Final"/"Batal Final"
  toggle button (only once verified) and a "Detail →" link per row; pagination footer.
- **Progress view**: grid of per-team cards, each showing status/finalist badges, a compact 3-step
  mini-tracker (Bayar → Verifikasi → Submit, check/dot states), and submission details (project
  title, GitHub/Drive link chips — greyed if missing, description snippet) or an empty "Karya belum
  disubmit" state.

**Admin — Detail Peserta** (`Admin/Show.vue`):
- Header: back-to-list link, "Detail Peserta" label, admin name + Keluar.
- Page header: team name + user name/email, status pill top-right.
- Left column (2/3 width): Data Pendaftaran (8-field grid: ketua, email, tim, jumlah anggota,
  institusi, kategori, no HP, tanggal daftar); Biodata Anggota Tim (table, with an
  n/total-filled indicator); Bukti Pembayaran (file info + download link, inline image preview or a
  "buka PDF" fallback, plus a "diverifikasi oleh … pada …" note once processed).
- Right column (1/3 width): **Aksi Verifikasi** card — before verification: optional approve-notes
  textarea + green "Approve Pembayaran" button, divider, "Tolak Pembayaran" toggle revealing a
  required rejection-reason textarea + red "Konfirmasi Tolak"/"Batal" pair; after verification:
  a static green/red confirmation summary instead. Below it, a small "Info Cepat" card (Lomba,
  Anggota, Biaya, Kategori key/value list).

---

## 4. Content tone

- All labels, buttons, empty states, and validation messages are in **Bahasa Indonesia**, informal
  but respectful register (uses "kamu" for participants, more formal/neutral for admin-facing copy).
- Prices and dates are concrete and specific (e.g. "Rp 75.000", "22 Juli 2026") — keep placeholder
  content realistic to this event rather than generic lorem ipsum.
- Status language is explicit about what happens next ("Menunggu verifikasi admin dalam 1×24 jam",
  "Silakan upload ulang bukti pembayaran yang benar").

## 5. Responsive rules

- Breakpoint of consequence: `md` (768px) — nav collapses to hamburger, multi-column layouts
  (About, Lomba/Seminar, admin table columns) collapse to single column/stacked cards, desktop
  timeline row becomes a vertical mobile timeline.
- Modals become bottom sheets (`rounded-t-3xl`, drag handle) below `sm`, centered dialogs above it.
- Tables hide secondary columns (Institusi, Kategori, Tanggal, Bukti) progressively at `sm`/`md`/`lg`
  rather than horizontally scrolling the whole table.
