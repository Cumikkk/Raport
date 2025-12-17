<?php
// Raport/includes/dashboard.php
include __DIR__ . '/header.php';
?>
<body>
<?php include __DIR__ . '/navbar.php'; ?>

<main class="content dk-dash">
  <div class="dk-wrap">

    <!-- INFO BAR (DI ATAS CARDS) -->
    <section class="dk-infobar" aria-label="Info Dashboard">
      <div class="dk-pill dk-pill-status">
        <span class="dk-pill-left">
          <i class="bi bi-activity"></i>
          <span class="dk-pill-label">Status</span>
        </span>
        <span id="rtDot" class="dot loading" title="Status koneksi"></span>
      </div>

      <!-- Terakhir diubah: dibuat fit-content (tidak melebar) -->
      <div class="dk-pill dk-pill-last">
        <span class="dk-pill-left">
          <i class="bi bi-clock-history"></i>
          <span class="dk-pill-label">Terakhir diubah</span>
        </span>
        <span class="dk-pill-last-value" id="rtTime">-</span>
      </div>

      <div class="dk-pill dk-pill-soft">
        <span class="dk-pill-left">
          <i class="bi bi-geo-alt-fill"></i>
          <span class="dk-pill-label">Zona waktu</span>
        </span>
        <span class="dk-pill-value">WIB</span>
      </div>
    </section>

    <!-- CARDS -->
    <section class="dk-cards" aria-label="Ringkasan Data">

      <!-- SISWA -->
      <div class="dk-card dk-card-blue">
        <div class="dk-card-top">
          <div class="dk-ico"><i class="bi bi-people-fill"></i></div>
          <div class="dk-meta">
            <div class="dk-label">Jumlah Siswa</div>
            <div class="dk-value" id="countSiswa">0</div>
          </div>
        </div>
        <div class="dk-foot">
          <span class="dk-foot-text">
            <i class="bi bi-arrow-repeat"></i>
            <span>Diperbarui Secara Otomatis</span>
          </span>
          <span class="dk-chip">Siswa</span>
        </div>
      </div>

      <!-- GURU -->
      <div class="dk-card dk-card-purple">
        <div class="dk-card-top">
          <div class="dk-ico"><i class="bi bi-person-badge-fill"></i></div>
          <div class="dk-meta">
            <div class="dk-label">Jumlah Guru</div>
            <div class="dk-value" id="countGuru">0</div>
          </div>
        </div>
        <div class="dk-foot">
          <span class="dk-foot-text">
            <i class="bi bi-arrow-repeat"></i>
            <span>Diperbarui Secara Otomatis</span>
          </span>
          <span class="dk-chip">Guru</span>
        </div>
      </div>

      <!-- ADMIN -->
      <div class="dk-card dk-card-teal">
        <div class="dk-card-top">
          <div class="dk-ico"><i class="bi bi-shield-lock-fill"></i></div>
          <div class="dk-meta">
            <div class="dk-label">Jumlah Admin</div>
            <div class="dk-value" id="countAdmin">0</div>
          </div>
        </div>
        <div class="dk-foot">
          <span class="dk-foot-text">
            <i class="bi bi-arrow-repeat"></i>
            <span>Diperbarui Secara Otomatis</span>
          </span>
          <span class="dk-chip">Admin</span>
        </div>
      </div>

      <!-- KELAS -->
      <div class="dk-card dk-card-orange">
        <div class="dk-card-top">
          <div class="dk-ico"><i class="bi bi-grid-1x2-fill"></i></div>
          <div class="dk-meta">
            <div class="dk-label">Jumlah Kelas</div>
            <div class="dk-value" id="countKelas">0</div>
          </div>
        </div>
        <div class="dk-foot">
          <span class="dk-foot-text">
            <i class="bi bi-arrow-repeat"></i>
            <span>Diperbarui Secara Otomatis</span>
          </span>
          <span class="dk-chip">Kelas</span>
        </div>
      </div>

    </section>

    <!-- PIE CHART -->
    <section class="dk-chart" aria-label="Diagram Perbandingan Data">
      <!-- HEADER DIAGRAM (seperti foto: kiri judul+desc, kanan tombol) -->
      <div class="dk-chart-header">
        <div class="dk-chart-titlewrap">
          <div class="dk-chart-title">Komposisi Data</div>
          <div class="dk-chart-sub">Perbandingan jumlah Siswa, Guru, Admin, dan Kelas.</div>
        </div>

        <div class="dk-chart-pill" aria-hidden="true">
          <i class="bi bi-pie-chart-fill"></i>
          <span>Diagram Pie</span>
        </div>
      </div>

      <div class="dk-chart-body">
        <canvas id="pieChart" height="240"></canvas>
      </div>
    </section>

  </div>
</main>

<style>
  /* ✅ FIX mobile scrollbar bawah (horizontal) TANPA mengubah layout lain */
  html, body{ max-width: 100%; overflow-x: hidden; }
  *, *::before, *::after{ box-sizing: border-box; }
  .dk-wrap, .dk-infobar, .dk-cards, .dk-chart{ max-width: 100%; overflow-x: hidden; }
  .dk-chart-body canvas{ display:block; width:100% !important; max-width:100% !important; }

  .dk-dash{ padding: clamp(12px, 2.2vw, 22px); }
  .dk-wrap{ max-width: 1200px; margin: 0 auto; }

  :root{
    --dk-ink: #0f172a;
    --dk-muted: #475569;
    --dk-shadow: 0 14px 38px rgba(2, 31, 87, 0.12);
    --dk-shadow-soft: 0 10px 28px rgba(2, 31, 87, 0.10);
    --dk-radius: 18px;
  }

  /* INFO BAR */
  .dk-infobar{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    justify-content:space-between;
    align-items:stretch;
    margin-bottom: 14px;
  }

  .dk-pill{
    display:inline-flex;
    align-items:center;
    gap:12px;
    padding: 10px 12px;
    border-radius: 999px;
    border: 1px solid rgba(226, 236, 252, .95);
    background: rgba(255,255,255,.97);
    box-shadow: 0 10px 24px rgba(2,31,87,.06);
    color: #1f2a44;
    font-size: 13px;
    font-weight: 900;
    width: fit-content;
    max-width: 100%;
  }

  .dk-pill-left{ display:flex; align-items:center; gap:10px; }
  .dk-pill i{ color: #0b57d0; font-size: 14px; }
  .dk-pill-label{ color: rgba(31,42,68,.82); font-weight: 900; }
  .dk-pill-value{ color: #1f2a44; font-weight: 950; }

  /* Terakhir diubah: wadah diperkecil + teks rapat */
  .dk-pill-last{
    max-width: 100%;
    gap: 10px;
  }
  .dk-pill-last-value{
    font-weight: 950;
    color: #0f172a;
    white-space: nowrap;
    letter-spacing: .1px;
  }

  .dk-pill-soft{
    background: linear-gradient(180deg, rgba(238,245,255,.97), rgba(255,255,255,.97));
  }

  /* DOT */
  .dot{
    width:10px; height:10px; border-radius:999px;
    background:#94a3b8;
    box-shadow: 0 0 0 7px rgba(11,87,208,.10);
    transition: background .2s ease, box-shadow .2s ease;
    flex: 0 0 auto;
  }
  .dot.ok{ background:#22c55e; box-shadow:0 0 0 7px rgba(34,197,94,.16); }
  .dot.err{ background:#ef4444; box-shadow:0 0 0 7px rgba(239,68,68,.16); }
  .dot.loading{ background:#f59e0b; box-shadow:0 0 0 7px rgba(245,158,11,.18); }

  /* GRID CARDS */
  .dk-cards{
    display:grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap:14px;
  }

  /* BASE CARD */
  .dk-card{
    border-radius: var(--dk-radius);
    padding: 16px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--dk-shadow-soft);
    border: 1px solid rgba(255,255,255,.22);
    transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
    color: #ffffff;
  }
  .dk-card:hover{
    transform: translateY(-3px);
    box-shadow: var(--dk-shadow);
    filter: saturate(1.05);
  }
  .dk-card::before{
    content:'';
    position:absolute;
    inset:0;
    background:
      radial-gradient(900px 260px at 0% 0%, rgba(255,255,255,.22), rgba(255,255,255,0) 55%),
      radial-gradient(480px 240px at 100% 100%, rgba(0,0,0,.14), rgba(0,0,0,0) 60%);
    pointer-events:none;
    mix-blend-mode: soft-light;
  }
  .dk-card::after{
    content:'';
    position:absolute;
    inset:auto -60px -80px auto;
    width:240px; height:240px; border-radius:999px;
    background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.18), rgba(255,255,255,0) 60%);
    pointer-events:none;
  }

  /* TOP */
  .dk-card-top{ display:flex; align-items:center; gap:12px; position:relative; z-index:1; }

  .dk-ico{
    width:52px; height:52px;
    border-radius: 16px;
    display:flex; align-items:center; justify-content:center;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.22);
    box-shadow: 0 12px 26px rgba(0,0,0,.14);
    flex:0 0 auto;
  }
  .dk-ico i{ font-size: 22px; color:#fff; }

  .dk-label{
    font-size: 13px;
    font-weight: 900;
    color: rgba(255,255,255,.92);
    letter-spacing:.2px;
  }
  .dk-value{
    margin-top: 2px;
    font-size: 34px;
    font-weight: 950;
    letter-spacing:.3px;
    line-height:1.1;
    color:#fff;
    text-shadow: 0 10px 24px rgba(0,0,0,.18);
  }

  .dk-foot{
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px dashed rgba(255,255,255,.28);
    display:flex; justify-content:space-between; align-items:center;
    position:relative; z-index:1;
    gap:10px;
  }
  .dk-foot-text{
    font-size: 12.5px;
    font-weight: 850;
    color: rgba(255,255,255,.92);
    display:flex; align-items:center; gap:8px;
    min-width: 0;
  }

  /* ✅ FIX: supaya teks "Diperbarui Secara Otomatis" tidak terpotong di PC */
  .dk-foot-text span{
    display:inline;
    white-space: normal;
    overflow: visible;
    text-overflow: clip;
    max-width: none;
    line-height: 1.15;
  }

  .dk-chip{
    font-size: 12px;
    font-weight: 950;
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.22);
    color: rgba(255,255,255,.92);
    flex: 0 0 auto;
  }

  /* COLOR VARIANTS */
  .dk-card-blue{
    background: linear-gradient(135deg, #0b57d0 0%, #2f7bff 55%, #78b0ff 110%);
  }
  .dk-card-purple{
    background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 55%, #a78bfa 115%);
  }
  .dk-card-teal{
    background: linear-gradient(135deg, #0f766e 0%, #14b8a6 55%, #67e8f9 120%);
  }
  .dk-card-orange{
    background: linear-gradient(135deg, #c2410c 0%, #f97316 55%, #fdba74 120%);
  }

  /* CHART */
  .dk-chart{
    margin-top: 14px;
    border-radius: 18px;
    border: 1px solid rgba(226, 236, 252, .95);
    background: rgba(255,255,255,.97);
    box-shadow: 0 10px 24px rgba(2,31,87,.06);
    overflow:hidden;
  }

  /* Header diagram seperti screenshot */
  .dk-chart-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    padding: 16px 16px 14px;
    background: linear-gradient(180deg, rgba(238,245,255,.85), rgba(255,255,255,.97));
    border-bottom: 1px solid rgba(226,236,252,.95);
  }
  .dk-chart-titlewrap{ min-width:0; }
  .dk-chart-title{
    font-weight: 950;
    font-size: 20px;
    line-height: 1.1;
    color: #0f172a;
    margin: 0;
  }
  .dk-chart-sub{
    margin-top: 4px;
    color: #475569;
    font-size: 13.5px;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 760px;
  }

  .dk-chart-pill{
    display:inline-flex;
    align-items:center;
    gap:10px;
    padding: 10px 14px;
    border-radius: 999px;
    font-weight: 900;
    color: #0b57d0;
    background: rgba(11,87,208,.08);
    border: 1px solid rgba(11,87,208,.22);
    box-shadow: 0 10px 18px rgba(2,31,87,.06);
    white-space: nowrap;
    flex: 0 0 auto;
  }
  .dk-chart-pill i{ font-size: 14px; color:#0b57d0; }

  .dk-chart-body{
    padding: 14px 14px 10px;
  }

  /* RESPONSIVE */
  @media (max-width: 992px){
    .dk-cards{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }

  @media (max-width: 520px){
    .dk-cards{ grid-template-columns: 1fr; }
    .dk-pill{ width: 100%; }
    .dk-chart-header{
      flex-direction: column;
      align-items: stretch;
    }
    .dk-chart-sub{
      white-space: normal;
      max-width: 100%;
    }
    .dk-chart-pill{
      justify-content:center;
      width: fit-content;
      align-self: flex-end;
    }
  }
</style>

<!-- Chart.js (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
(function () {
  const elSiswa = document.getElementById('countSiswa');
  const elGuru  = document.getElementById('countGuru');
  const elAdmin = document.getElementById('countAdmin');
  const elKelas = document.getElementById('countKelas');

  const rtDot  = document.getElementById('rtDot');
  const rtTime = document.getElementById('rtTime');

  const endpoint   = 'dashboard_stats.php';
  const intervalMs = 3000;

  const KEY_SIGNATURE = 'dash_signature_v1';
  const KEY_CHANGEDAT = 'dash_last_changed_at_v1';

  let lastSignature = localStorage.getItem(KEY_SIGNATURE) || '';
  let lastChangedAt = localStorage.getItem(KEY_CHANGEDAT) || '';

  function fmt(n) {
    try { return Number(n || 0).toLocaleString('id-ID'); }
    catch (e) { return String(n || 0); }
  }

  function setDot(mode){
    if (!rtDot) return;
    rtDot.classList.remove('ok','err','loading');
    rtDot.classList.add(mode);
  }

  rtTime.textContent = lastChangedAt ? lastChangedAt : '-';

  // ===== PIE CHART =====
  const ctx = document.getElementById('pieChart');
  let pie = null;

  // Plugin: leader line + label luar + anti-tabrakan + safe area bawah (biar tidak mepet legend)
  const pieOutLabels = {
    id: 'pieOutLabels',
    afterDatasetsDraw(chart) {
      const { ctx } = chart;
      const meta = chart.getDatasetMeta(0);
      if (!meta || !meta.data) return;

      const data = chart.data.datasets[0].data || [];
      const labels = chart.data.labels || [];

      // Kumpulkan kandidat label: sisi kanan & kiri
      const right = [];
      const left  = [];

      meta.data.forEach((arc, i) => {
        const val = Number(data[i] || 0);
        if (val <= 0) return;

        const angle = (arc.startAngle + arc.endAngle) / 2;
        const r = arc.outerRadius;

        const isRight = Math.cos(angle) >= 0;

        const x0 = arc.x + Math.cos(angle) * (r * 0.92);
        const y0 = arc.y + Math.sin(angle) * (r * 0.92);

        const x1 = arc.x + Math.cos(angle) * (r * 1.12);
        const y1 = arc.y + Math.sin(angle) * (r * 1.12);

        // segmen horizontal ke kiri/kanan
        const x2 = x1 + (isRight ? 22 : -22);
        const y2 = y1;

        const item = { i, isRight, x0, y0, x1, y1, x2, y2 };
        (isRight ? right : left).push(item);
      });

      // Fungsi rapikan jarak label agar tidak dempet + paksa tetap di "safe area"
      const MIN_GAP = 16; // jarak antar label
      function spread(list) {
        list.sort((a,b) => a.y2 - b.y2);

        // dorong turun jika terlalu rapat
        for (let k = 1; k < list.length; k++) {
          const prev = list[k-1];
          const cur  = list[k];
          if (cur.y2 - prev.y2 < MIN_GAP) {
            cur.y2 = prev.y2 + MIN_GAP;
          }
        }

        // ===== SAFE AREA ATAS/BAWAH =====
        const area = chart.chartArea;

        // sedikit ruang atas
        const topLimit = area.top + 12;

        // ✅ ini inti revisi: naikkan batas bawah, supaya label tidak mepet legend
        // kalau masih mepet, naikkan angkanya (misal 40 / 46)
        const botLimit = area.bottom - 34;

        // kalau label paling bawah melewati batas bawah aman, tarik semuanya naik
        const overflow = (list.length ? (list[list.length-1].y2 - botLimit) : 0);
        if (overflow > 0) {
          for (let k = 0; k < list.length; k++) list[k].y2 -= overflow;
        }

        // kalau label paling atas terlalu naik melewati batas atas, dorong turun sedikit
        const underflow = (list.length ? (topLimit - list[0].y2) : 0);
        if (underflow > 0) {
          for (let k = 0; k < list.length; k++) list[k].y2 += underflow;
        }

        // setelah y2 berubah, samakan y1 agar segmen horizontal lebih rapi
        for (let k = 0; k < list.length; k++) {
          list[k].y1 = list[k].y2;
        }
      }

      spread(right);
      spread(left);

      const all = right.concat(left);

      ctx.save();
      ctx.font = '900 12px system-ui, -apple-system, Segoe UI, Roboto, Arial';
      ctx.fillStyle = '#0f172a';

      all.forEach((it) => {
        const val = Number(data[it.i] || 0);
        if (val <= 0) return;

        const isRight = it.isRight;

        // garis keluar
        ctx.strokeStyle = 'rgba(15, 23, 42, 0.55)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(it.x0, it.y0);
        ctx.lineTo(it.x1, it.y1);
        ctx.lineTo(it.x2, it.y2);
        ctx.stroke();

        // teks label
        const text = `${labels[it.i]}: ${fmt(val)}`;
        ctx.textAlign = isRight ? 'left' : 'right';
        ctx.textBaseline = 'middle';
        ctx.fillText(text, it.x2 + (isRight ? 6 : -6), it.y2);
      });

      ctx.restore();
    }
  };

  function initPie() {
    if (!ctx || typeof Chart === 'undefined') return;

    pie = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ['Siswa', 'Guru', 'Admin', 'Kelas'],
        datasets: [{
          data: [0, 0, 0, 0],
          borderWidth: 2,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,

        // ✅ tambah ruang bawah agar label/garis tidak mepet legend
        layout: {
          padding: { top: 22, right: 22, bottom: 44, left: 22 }
        },

        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              // ✅ opsional: legend lebih lega
              padding: 20
            }
          },
          tooltip: {
            callbacks: {
              label: function (item) {
                const val = item.raw || 0;
                return ' ' + item.label + ': ' + fmt(val);
              }
            }
          }
        }
      },
      plugins: [pieOutLabels]
    });

    const parent = ctx.parentElement;
    if (parent) parent.style.minHeight = '340px';
  }

  initPie();

  function updatePie(values){
    if (!pie) return;
    pie.data.datasets[0].data = values;
    pie.update();
  }

  let timer = null;
  let controller = null;

  async function fetchStats() {
    if (controller) controller.abort();
    controller = new AbortController();

    try {
      setDot('loading');

      const res = await fetch(endpoint + '?t=' + Date.now(), {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        cache: 'no-store',
        signal: controller.signal
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);

      const json = await res.json();
      if (!json || json.ok !== true) throw new Error((json && json.msg) ? json.msg : 'Respon tidak valid');

      const siswa = Number(json.siswa || 0);
      const guru  = Number(json.guru  || 0);
      const admin = Number(json.admin || 0);
      const kelas = Number(json.kelas || 0);

      elSiswa.textContent = fmt(siswa);
      elGuru.textContent  = fmt(guru);
      elAdmin.textContent = fmt(admin);
      elKelas.textContent = fmt(kelas);

      updatePie([siswa, guru, admin, kelas]);

      const signature = [siswa, guru, admin, kelas].join('|');

      if (signature !== lastSignature) {
        lastSignature = signature;
        localStorage.setItem(KEY_SIGNATURE, lastSignature);

        lastChangedAt = json.updated_at || '';
        if (lastChangedAt) {
          localStorage.setItem(KEY_CHANGEDAT, lastChangedAt);
          rtTime.textContent = lastChangedAt;
        } else {
          rtTime.textContent = '-';
        }
      }

      setDot('ok');
    } catch (e) {
      if (e.name === 'AbortError') return;
      console.error(e);
      setDot('err');
    }
  }

  fetchStats();
  timer = setInterval(fetchStats, intervalMs);

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      if (timer) clearInterval(timer);
      timer = null;
      setDot('loading');
    } else {
      fetchStats();
      timer = setInterval(fetchStats, intervalMs);
    }
  });
})();
</script>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
