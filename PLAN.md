# Plan Penyesuaian TPUIRF untuk Penelitian Preferensi Antarmuka

## 1. Tujuan

Menyesuaikan TPUIRF menjadi platform eksperimen yang dapat:

1. Mengumpulkan preferensi antarmuka peserta dalam enam dimensi.
2. Mencatat interaksi peserta secara berurutan dari awal sampai akhir eksperimen.
3. Menghasilkan data yang bisa direproduksi dan dianalisis untuk penelitian jurnal.
4. Membentuk konfigurasi UI akhir berdasarkan pilihan peserta.

Enam dimensi yang diuji:

| Task | Dimensi | Opsi awal |
|---|---|---|
| 1 | Tema warna | Light, Dark |
| 2 | Tipografi | Font family dan ukuran Small, Medium, Large |
| 3 | Kepadatan layout | Compact, Comfortable, Spacious |
| 4 | Posisi navigasi | Top navigation, Left sidebar, Bottom navigation |
| 5 | Gaya ikon | Outline, Filled, Duotone |
| 6 | Gaya desain akhir | Minimal/Flat, Material/Elevated, Glassmorphism, Neumorphism, High-contrast/Brutalist |

Istilah yang digunakan untuk navigasi di bawah adalah **bottom navigation** atau **bottom bar**. Untuk menjaga validitas perbandingan, semua varian dalam satu task harus memiliki isi, label, dan fungsi yang sama; yang berubah hanya dimensi yang sedang diuji.

## 2. Keputusan Desain Penelitian

Keputusan berikut perlu disepakati sebelum pengumpulan data utama:

- [ ] Menentukan apakah peserta memilih opsi yang disukai saja atau juga memberi rating 1–5 untuk setiap opsi.
- [ ] Menentukan apakah urutan enam task tetap atau diacak.
- [ ] Menentukan jumlah peserta dan kriteria inklusi/eksklusi bersama tim peneliti.
- [ ] Menentukan ambang idle, direkomendasikan 30 detik tanpa aktivitas.
- [ ] Menentukan apakah hover dicatat; hover tidak tersedia secara konsisten pada perangkat sentuh.
- [ ] Menetapkan versi protokol penelitian, misalnya `TPUIRF-PREF-v1`.

Rekomendasi awal:

- Urutan task tetap 1–6 agar alur mudah dipahami, tetapi urutan opsi di dalam setiap task diacak per peserta untuk mengurangi bias posisi.
- Simpan `option_order` yang dilihat setiap peserta agar proses randomisasi dapat diaudit.
- Gunakan satu konten contoh yang sama di semua varian.
- Minta satu pilihan utama dan rating 1–5 per opsi jika durasi eksperimen masih memungkinkan.
- Lakukan pilot test pada 5–10 peserta sebelum pengambilan data utama.

## 3. Alur Eksperimen

```text
Landing dan informed consent
        ↓
Identitas peserta/pseudonymous participant code
        ↓
Profil dan data demografis yang memang dibutuhkan
        ↓
Task 1: Light vs Dark
        ↓
Task 2: Font family dan font size
        ↓
Task 3: Layout density
        ↓
Task 4: Navigation position
        ↓
Task 5: Icon style
        ↓
Task 6: Final design style dengan live preview
        ↓
Ringkasan pilihan UI peserta
        ↓
Evaluasi trust, transparency, satisfaction, dan feedback
        ↓
Eksperimen selesai dan data dikunci
```

Status eksperimen yang disarankan:

```text
pending → consented → profiling → in_progress → evaluation → completed
```

Hanya `completed` dan `cancelled` yang dianggap terminal. Peserta harus bisa melanjutkan eksperimen dari task terakhir tanpa kehilangan data.

## 4. Spesifikasi Setiap Task

### Task 1 — Theme Preference

Tujuan: mengetahui preferensi peserta terhadap mode terang atau gelap.

Opsi:

- `light`: latar terang, teks gelap.
- `dark`: latar gelap, teks terang.

Komponen demo minimal:

- Header/judul.
- Dua atau tiga card.
- Tombol primer dan sekunder.
- Input atau search field.
- Teks pendek dan status/notification.

Data hasil:

- `selected_theme`.
- Rating masing-masing opsi jika digunakan.
- Waktu sampai interaksi pertama dan waktu sampai pilihan final.
- Jumlah perpindahan pilihan light ↔ dark.

### Task 2 — Typography Preference

Tujuan: mengetahui preferensi font family dan ukuran teks.

Task ini dibuat sebagai dua subtask dalam satu halaman/alur:

1. Pilih font family.
2. Pilih font size.

Opsi font family awal:

- `sans`: Inter atau font sans-serif yang setara.
- `serif`: Merriweather atau font serif yang setara.
- `high_legibility`: Atkinson Hyperlegible atau font dengan keterbacaan tinggi yang setara.

Opsi ukuran:

- `small`: basis sekitar 14 px.
- `medium`: basis sekitar 16 px.
- `large`: basis sekitar 18–20 px.

Semua contoh menggunakan teks, line-height, panjang paragraf, dan lebar container yang sama. Font eksternal sebaiknya disimpan lokal supaya eksperimen tidak dipengaruhi kegagalan jaringan.

Data hasil:

- `selected_font_family`.
- `selected_font_size`.
- Jumlah pergantian font dan ukuran.
- Durasi mengamati setiap preview.

### Task 3 — Layout Density Preference

Tujuan: mengetahui preferensi kepadatan dan jarak antar komponen.

Opsi:

- `compact`: card dan gap rapat, informasi lebih padat.
- `comfortable`: jarak sedang sebagai baseline.
- `spacious`: card lebih lebar dan ruang kosong lebih besar.

Yang boleh berubah:

- Gap antar-card.
- Padding card.
- Jumlah kolom yang terlihat pada lebar layar yang sama.
- Spacing vertikal antarbagian.

Yang tidak boleh berubah:

- Isi card.
- Urutan informasi.
- Warna, font, ikon, dan fungsi tombol.

Data hasil:

- `selected_layout_density`.
- Click count per card dan tombol.
- Scroll distance, maximum scroll depth, dan jumlah perubahan arah scroll.
- Durasi sampai pilihan final.

### Task 4 — Navigation Preference

Tujuan: mengetahui posisi navigasi yang paling disukai.

Opsi:

- `top`: header/top navigation.
- `left`: left sidebar navigation.
- `bottom`: bottom navigation/bottom bar.

Semua varian harus memiliki menu dan ikon yang sama, misalnya Home, Explore, Activity, dan Profile. Pada layar kecil, penelitian perlu menetapkan apakah sidebar tetap ditampilkan atau task ini hanya boleh dikerjakan pada ukuran layar tertentu.

Data hasil:

- `selected_navigation_position`.
- Klik per menu dan posisi navigasi.
- Waktu perpindahan dari menu A ke menu B.
- Jumlah salah klik atau perpindahan bolak-balik.

### Task 5 — Icon Style Preference

Tujuan: mengetahui gaya visual ikon yang paling disukai.

Opsi awal:

- `outline`: bentuk garis tanpa bidang penuh.
- `filled`: bentuk solid/terisi.
- `duotone`: dua tingkat warna atau opacity.

Semua varian harus memakai glyph, ukuran, label, dan fungsi yang sama. Jangan membandingkan ikon dengan makna berbeda karena hasilnya akan mencampur preferensi gaya dengan pemahaman simbol.

Alternatif dimensi untuk penelitian lanjutan, tidak dicampur ke task pertama:

- Rounded vs sharp.
- Icon-only vs icon + label.
- Small vs medium vs large icon.

Data hasil:

- `selected_icon_style`.
- Waktu sampai ikon pertama diklik.
- Click count dan salah klik per ikon.
- Rating kejelasan ikon jika digunakan.

### Task 6 — Final Design Style Preference

Tujuan: mengetahui bahasa visual/desain akhir yang paling disukai peserta setelah preferensi theme, typography, layout, navigation, dan icon selesai dipilih.

Opsi awal yang direkomendasikan:

- `minimal_flat`: tampilan bersih, warna solid, sedikit atau tanpa shadow.
- `material_elevated`: surface/card dengan elevation dan hierarchy yang jelas.
- `glassmorphism`: surface transparan, blur, border tipis, dan layer warna.
- `neumorphism`: surface lembut dengan kombinasi inner/outer shadow.
- `high_contrast_brutalist`: kontras tinggi, border tegas, dan bentuk visual yang eksplisit.

Jumlah gaya sebaiknya dibatasi sekitar 4–6 gaya pada satu eksperimen. Terlalu banyak pilihan meningkatkan kelelahan dan choice overload. Jika dosen menginginkan lebih banyak gaya, lakukan screening/pilot terlebih dahulu atau bagi gaya ke beberapa set eksperimen.

#### Live preview, bukan gambar statis

Setiap gaya ditampilkan sebagai komponen UI nyata yang responsif dan tetap dapat diklik. Semua preview harus memakai:

- Struktur DOM dan konten yang sama.
- Komponen, fungsi, dan urutan informasi yang sama.
- Preferensi hasil Task 1–5 milik peserta sebagai baseline.
- Data contoh yang sama.
- Ukuran area preview yang setara.

Yang berubah hanya design tokens/gaya visual, misalnya warna surface, border, radius, shadow, blur, dan treatment komponen. Dengan cara ini, pilihan tidak tercampur dengan perbedaan konten atau fungsi.

#### Pola expandable yang disarankan

```text
┌──────────────────────────────────────┐
│ Glassmorphism                  [⌄]   │
│ Deskripsi singkat                    │
├──────────────────────────────────────┤
│ Live preview responsif               │
│ Header, cards, form, buttons, nav    │
│                                      │
│ [Coba Full Screen] [Pilih Gaya Ini]  │
└──────────────────────────────────────┘
```

- Tampilkan nama dan deskripsi singkat saat collapsed.
- Hanya satu preview yang expanded dalam satu waktu agar layar mobile tidak terlalu panjang.
- Mount/render preview ketika pertama kali dibuka untuk mengurangi beban awal.
- Pada mobile, sediakan `Coba Full Screen` yang membuka dedicated preview page atau full-screen dialog.
- Sediakan tombol `Pilih Gaya Ini` yang tetap mudah dijangkau, idealnya sticky di bagian bawah preview mobile.
- Setelah kembali dari full-screen, pertahankan posisi scroll dan preview yang sedang aktif.
- Gunakan `aria-expanded`, pengelolaan focus, tombol minimal 44×44 px, dan dukungan `prefers-reduced-motion`.
- Jangan mengandalkan hover sebagai satu-satunya interaksi karena perangkat mobile tidak selalu memiliki pointer.

Data hasil:

- `selected_design_style`.
- Urutan gaya yang ditampilkan kepada peserta.
- Preview yang dibuka/ditutup dan berapa kali.
- Dwell time aktif pada setiap preview.
- Komponen yang dicoba di dalam setiap preview.
- Jumlah perpindahan dari satu gaya ke gaya lain.
- Penggunaan mode full-screen.
- Waktu sampai pilihan akhir dan rating setiap gaya jika digunakan.

Catatan metodologis: neumorphism dapat memiliki masalah kontras dan affordance, sedangkan glassmorphism dapat bermasalah pada readability/performance jika blur berlebihan. Implementasi preview tetap harus memenuhi baseline aksesibilitas; penelitian tidak perlu sengaja membuat salah satu gaya sulit digunakan.

## 5. Event Tracking

### Event minimum

| Event | Kapan dicatat | Data utama |
|---|---|---|
| `experiment_started` | Setelah consent diterima | experiment, participant, protocol version |
| `task_viewed` | Task selesai dirender | task, option order, viewport |
| `component_clicked` | Komponen diklik/tap | task, component, option, koordinat, timestamp |
| `selection_changed` | Pilihan peserta berubah | pilihan lama dan baru |
| `style_preview_expanded` | Preview gaya desain dibuka | style, urutan, trigger |
| `style_preview_collapsed` | Preview gaya desain ditutup | style dan active dwell time |
| `style_preview_fullscreened` | Preview dibuka penuh | style dan viewport |
| `scroll_started` | Scroll dimulai | posisi awal |
| `scroll_ended` | Scroll berhenti | posisi akhir, jarak, arah, durasi, depth |
| `task_submitted` | Pilihan task dikirim | pilihan final dan durasi |
| `task_completed` | Server mengonfirmasi penyimpanan | task dan server timestamp |
| `experiment_completed` | Evaluasi tersimpan | durasi total |
| `page_hidden` | Tab/background atau peserta keluar | task dan elapsed time |

Hover dan mouse movement bersifat opsional. Jangan mengirim event mouse setiap pixel karena akan menghasilkan data sangat besar dan noise tinggi.

### Field wajib setiap event

```json
{
  "event_uuid": "uuid-idempotency",
  "sequence_number": 27,
  "protocol_version": "TPUIRF-PREF-v1",
  "experiment_id": "uuid",
  "participant_id": "uuid/pseudonymous-code",
  "task_key": "navigation",
  "option_id": "bottom",
  "component_id": "nav-profile",
  "event_type": "component_clicked",
  "client_timestamp": "ISO-8601",
  "server_received_at": "ISO-8601",
  "elapsed_task_ms": 15320,
  "elapsed_experiment_ms": 182450,
  "delta_previous_event_ms": 820,
  "delta_previous_click_ms": 2140,
  "previous_component_id": "nav-home",
  "viewport_width": 1366,
  "viewport_height": 768,
  "input_method": "mouse",
  "payload": {}
}
```

Untuk Task 6, contoh `task_key` adalah `design_style`, `option_id` adalah `glassmorphism`, dan `component_id` dapat berupa `preview-card-primary-action`.

Gunakan `performance.now()` untuk menghitung selisih waktu pada browser karena monotonic dan tidak terpengaruh perubahan jam perangkat. Timestamp absolut tetap disimpan untuk audit dan sinkronisasi.

### Perhitungan perpindahan klik A → B

Saat setiap klik terjadi, client menyimpan:

- Timestamp klik terakhir.
- `component_id` terakhir.
- Nomor urut event.

Klik berikutnya menyimpan:

```text
previous_component_id = component A
component_id          = component B
delta_previous_click  = waktu klik B - waktu klik A
```

Data mentah tetap menjadi sumber utama. Tabel transisi A → B dan agregat click count dibuat saat analisis atau proses export, bukan menggantikan event mentah.

### Tracking scroll

Scroll dicatat sebagai satu sesi, bukan event untuk setiap perubahan pixel:

1. `scroll_started` ketika posisi mulai berubah.
2. Update posisi di client dengan throttle sekitar 200–300 ms.
3. `scroll_ended` setelah sekitar 300–500 ms tanpa perubahan.

Ringkasan satu sesi scroll:

- `start_y` dan `end_y`.
- `distance_px`.
- `duration_ms`.
- `direction`: up/down.
- `max_scroll_depth_percent`.
- `direction_change_count`.
- `component_id` untuk area scroll terkait.

## 6. Perubahan Database

### Tabel `experiments`

Tambahkan atau pastikan tersedia:

- `protocol_version`.
- `status`.
- `current_task`.
- `task_order` JSON.
- `started_at`, `completed_at`, `last_activity_at`.
- `total_active_duration_ms`.
- `consent_version` dan `consented_at`.

### Tabel baru `experiment_tasks`

- `id` UUID.
- `experiment_id` UUID.
- `task_key`.
- `sequence`.
- `option_order` JSON.
- `started_at`, `completed_at`.
- `active_duration_ms`, `idle_duration_ms`.
- `selected_option` atau `selection_payload` JSON.
- `change_count`.
- Unique key pada `experiment_id + task_key`.

### Tabel `interaction_events`

Menggantikan atau memperluas `interaction_logs` saat ini:

- Identitas dan nomor urut event.
- `task_key`, `option_id`, dan `component_id` yang stabil.
- Jenis event.
- Semua field waktu dan delta.
- Viewport dan input method.
- Payload JSON untuk data spesifik event.
- Index pada `(experiment_id, sequence_number)`, `(experiment_id, task_key)`, dan `(component_id, event_type)`.

### Tabel `participant_preferences`

- `experiment_id` dan `user_id`.
- `theme`.
- `font_family`.
- `font_size`.
- `layout_density`.
- `navigation_position`.
- `icon_style`.
- `design_style`.
- `final_config` JSON.

### Tabel `trust_evaluations`

Gunakan tabel yang sudah ada dan benar-benar simpan:

- Trust score.
- Transparency score.
- UI satisfaction.
- Recommendation quality jika rekomendasi ML sudah aktif.
- Feedback.

## 7. API dan Keamanan Data

Endpoint backend yang tersedia:

```text
POST /respondent/preference-experiment/start
GET  /respondent/preference-experiment/{experimentId}/tasks/{taskKey}
POST /respondent/preference-experiment/{experimentId}/tasks/{taskKey}/selection
POST /respondent/preference-experiment/{experimentId}/events/batch
POST /respondent/preference-experiment/{experimentId}/evaluation
POST /respondent/preference-experiment/{experimentId}/complete
```

Aturan wajib:

- Semua endpoint eksperimen memerlukan session autentik dan role respondent.
- `user_id` dan `experiment_id` tidak dipercaya dari browser; server mengambil user dari session dan memverifikasi kepemilikan eksperimen.
- Gunakan Form Request untuk validasi task, option, component, timestamp, dan payload.
- Gunakan `event_uuid` untuk mencegah event ganda ketika retry.
- Event dikirim secara batch, misalnya setiap 10–20 event atau setiap 3–5 detik.
- Simpan antrean sementara di browser dan retry ketika koneksi kembali.
- Gunakan `sendBeacon` hanya untuk flush terakhir saat page hidden, dengan format dan autentikasi yang sudah diuji.
- Terapkan rate limit dan batas ukuran payload.

## 8. Privasi dan Etika Penelitian

- Sediakan informed consent sebelum tracking dimulai.
- Gunakan participant code/pseudonymous ID pada dataset analisis.
- Pisahkan data identitas dari data event jika identitas asli memang harus disimpan.
- Jangan menyimpan isi input, tombol keyboard yang ditekan, token, cookie, atau data pribadi di payload event.
- Catat hanya interaksi yang relevan dengan tujuan penelitian.
- Tentukan masa retensi, prosedur penghapusan, dan siapa yang boleh mengakses data.
- Dokumentasikan versi consent dan versi protokol yang diterima peserta.
- Pastikan prosedur mengikuti persetujuan etik/komite penelitian institusi jika diwajibkan.

## 9. Dataset dan Metrik Analisis

### Dataset mentah

Satu baris per event, tanpa menghilangkan urutan dan timestamp.

### Dataset ringkasan per peserta dan task

- Pilihan final.
- Total active time dan idle time.
- Time to first interaction.
- Time to final selection.
- Total click/tap.
- Click count per component.
- Jumlah perubahan pilihan.
- Total scroll distance.
- Maximum scroll depth.
- Jumlah sesi dan perubahan arah scroll.
- Dwell time per design style preview.
- Jumlah expand, collapse, dan full-screen per design style.
- Completion status dan dropout point.

### Dataset transisi komponen

Contoh:

| Experiment | Task | From | To | Delta ms | Sequence |
|---|---|---|---|---:|---:|
| E-001 | navigation | nav-home | nav-profile | 2140 | 27 |

### Metrik kualitas data

- Missing event rate.
- Duplicate event rate.
- Event sequence gap.
- Client/server timestamp drift.
- Persentase eksperimen selesai.
- Jumlah peserta yang melewati ambang idle atau durasi ekstrem.

Aturan pembersihan data dan kriteria eksklusi harus ditentukan sebelum analisis utama dan ditulis di protokol.

## 10. Rencana Implementasi

### Fase 0 — Memperbaiki Fondasi

- [ ] Pisahkan autentikasi respondent dari admin/researcher.
- [x] Regenerasi session setelah login dan invalidasi session saat logout.
- [x] Perbaiki state machine eksperimen agar tidak putus setelah task terakhir.
- [ ] Ubah operasi yang menulis data dari GET menjadi POST.
- [x] Pastikan evaluasi tersimpan ke `trust_evaluations`.
- [x] Ganti interaction endpoint lama dengan endpoint tervalidasi dan terautentikasi.
- [x] Tambahkan test untuk alur eksperimen lengkap.

Kriteria selesai: satu peserta dapat menyelesaikan eksperimen end-to-end tanpa exception dan seluruh pilihan/evaluasi tersimpan.

### Fase 1 — Model Data dan Tracking Engine

- [x] Buat migration `experiment_tasks`, `interaction_events`, dan `participant_preferences`.
- [x] Definisikan konfigurasi terpusat untuk task, option, input method, dan event type.
- [x] Implementasikan sequence number, event UUID, timer monotonic, idle detector, dan batching.
- [x] Implementasikan tracking klik dan delta A → B.
- [x] Implementasikan scroll session summary.
- [x] Buat endpoint batch dengan validasi, ownership check, dan idempotency.

Kriteria selesai: click, transition, duration, dan scroll dapat direkonstruksi dari data mentah tanpa mengandalkan data agregat client.

### Fase 2 — Enam Task UI

- [x] Bangun shared preview content agar semua varian memakai isi identik.
- [x] Implementasikan task theme.
- [x] Implementasikan task typography.
- [x] Implementasikan task layout density.
- [x] Implementasikan task navigation position.
- [x] Implementasikan task icon style.
- [x] Implementasikan task final design style dengan expandable live preview.
- [x] Implementasikan mode full-screen yang responsif untuk preview mobile.
- [x] Pisahkan shared preview markup dari design tokens setiap gaya.
- [x] Implementasikan randomisasi urutan opsi dan simpan hasil randomisasinya.
- [x] Implementasikan halaman ringkasan pilihan akhir.

Kriteria selesai: setiap task hanya mengubah dimensi yang diuji dan menghasilkan preference record yang valid.

### Fase 3 — Evaluasi dan Export

- [x] Validasi rating 1–5 dan feedback.
- [x] Tambahkan export raw events.
- [x] Tambahkan export summary per task/participant.
- [x] Tambahkan export transition dan click count per component.
- [x] Hilangkan data identitas dari export penelitian standar.
- [x] Tambahkan protocol version dan metadata export.

Kriteria selesai: researcher dapat menghasilkan dataset mentah dan dataset analisis dengan definisi kolom terdokumentasi.

### Fase 4 — Quality Assurance dan Pilot

- [ ] Unit test state transition, duration calculation, idle detector, dan aggregator.
- [x] Feature test authorization, validation, duplicate event, dan completion flow.
- [ ] Browser test untuk click, scroll, refresh, reconnect, dan page hidden.
- [ ] Uji desktop, mobile, mouse, touch, light mode, dan dark mode.
- [ ] Uji aksesibilitas keyboard dan screen reader dasar.
- [ ] Jalankan pilot 5–10 peserta.
- [ ] Audit missing event, event ganda, durasi tidak masuk akal, dan kebingungan instruksi.
- [ ] Bekukan protokol dan komponen sebelum pengumpulan data utama.

Kriteria selesai: tidak ada bug blocker pada pilot, definisi metrik final sudah disepakati, dan protokol tidak berubah selama pengumpulan data utama.

### Fase 5 — Integrasi Rekomendasi/ML

Fase ini dikerjakan setelah pengumpulan data eksperimen stabil.

- [ ] Gunakan hasil preferensi sebagai target model.
- [ ] Pastikan feature training sama persis dengan feature inference.
- [ ] Simpan label encoder, daftar feature, target mapping, dan model version.
- [ ] Pisahkan training endpoint dari endpoint publik.
- [ ] Evaluasi model dengan split berbasis peserta agar data satu peserta tidak bocor ke train dan test.
- [ ] Simpan confidence dan explanation bersama versi model.

## 11. Definition of Done

Penyesuaian dianggap siap untuk pengambilan data utama jika:

- [x] Enam task tampil sebagai varian yang benar-benar berbeda sesuai dimensinya.
- [x] Preview gaya desain menggunakan UI nyata, responsif, dan konten yang identik.
- [x] Expandable preview hanya membuka satu gaya sekaligus dan mode full-screen berfungsi di mobile.
- [x] Dwell time setiap design preview dapat dihitung dengan benar.
- [x] Urutan opsi dan versi protokol tersimpan.
- [x] Setiap klik memiliki `component_id`, urutan, dan delta waktu yang valid.
- [x] Setiap scroll memiliki ringkasan jarak, durasi, arah, dan depth.
- [x] Timer membedakan active time dan idle time.
- [x] Refresh/reconnect menyimpan antrean event dan akumulasi durasi di browser untuk retry.
- [x] Peserta tidak dapat menulis event untuk eksperimen peserta lain.
- [x] Evaluasi dan preferensi akhir tersimpan.
- [x] Raw dataset dapat merekonstruksi seluruh urutan interaksi.
- [x] Export analisis tidak memuat data pribadi yang tidak diperlukan.
- [x] Test otomatis untuk happy path dan failure path utama lulus.
- [ ] Pilot test selesai dan protokol penelitian dibekukan.

## 12. Deliverable

- Aplikasi eksperimen enam task dengan expandable live design preview.
- Data dictionary untuk seluruh tabel dan field export.
- Dokumen protokol eksperimen dan informed consent.
- Raw event export.
- Participant/task summary export.
- Transition dan component click report.
- Automated test suite.
- Deployment dan backup guide.
- Catatan versi protokol, aplikasi, dan model ML.
