{{-- ============================================================
  THEME: "Kertas Tanda Terima & Tinta Biru" (blueprint/workshop)
  Sumber tunggal design token + komponen. Di-include oleh
  layouts/app.blade.php dan halaman standalone (login, tracking).
  Palet tervalidasi WCAG AA terhadap kertas #FAF8F3:
    ink 10.84 / act 5.78 / body 14.64 / muted-dark 4.48 / rose 6.10
  #4A7FA5 (4.06) hanya untuk garis/ikon >=3:1, bukan teks kecil;
  teks garansi memakai #3E6E96 (5.11).
============================================================ --}}
<style>
:root {
    --paper: #FAF8F3;
    --surface: #FFFFFF;
    --ink: #1E3A5F;
    --ink-soft: rgba(30, 58, 95, .38);
    --ink-faint: rgba(30, 58, 95, .16);
    --act: #2F6690;
    --act-deep: #24506F;
    --warranty: #3E6E96;
    --warranty-line: #4A7FA5;
    --body: #1A2530;
    --muted: #5F7080;
    --line: rgba(30, 58, 95, .22);
    --line-soft: rgba(30, 58, 95, .12);
    --rose: #B91C1C;
    --rose-bg: rgba(185, 28, 28, .06);
    --amber-ink: #8A5A1B;
    --amber-bg: rgba(138, 90, 27, .08);
    --font-ui: "IBM Plex Sans Condensed", "IBM Plex Sans", "Segoe UI", system-ui, sans-serif;
    --font-mono: "IBM Plex Mono", "JetBrains Mono", ui-monospace, "Consolas", monospace;
}

html, body { background: var(--paper); color: var(--body); }

body, .ui { font-family: var(--font-ui); }
.mono { font-family: var(--font-mono); }

/* ---------- Kop / header ---------- */
.masthead { border-bottom: 2px solid var(--ink); background: var(--surface); }
.masthead-inner { border-bottom: 1px solid var(--line-soft); }
.brand-mark {
    width: 38px; height: 38px; border: 1.5px solid var(--ink);
    color: var(--ink); font-family: var(--font-mono); font-weight: 600;
    display: flex; align-items: center; justify-content: center;
    border-radius: 4px; background: var(--paper);
}
.navlink {
    padding: 5px 10px; border-radius: 4px; font-size: 13px; font-weight: 500;
    color: var(--body); border: 1px solid transparent;
}
.navlink:hover { border-color: var(--line); color: var(--ink); }
.navlink.active { border-color: var(--ink); color: var(--ink); background: var(--paper); font-weight: 600; }

/* ---------- Panel & kartu ---------- */
.panel {
    background: var(--surface); border: 1px solid var(--line);
    border-radius: 5px;
}
.panel-head { border-bottom: 1px solid var(--line-soft); }
.rule-b { border-bottom: 1px solid var(--line-soft); }

/* ---------- Tombol ---------- */
.btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 8px 14px; border-radius: 4px; font-size: 13px; font-weight: 600;
    border: 1px solid transparent; cursor: pointer; transition: background .12s, border-color .12s;
}
.btn-act { background: var(--act); color: #FFFFFF; }
.btn-act:hover { background: var(--act-deep); }
.btn-ink { background: transparent; color: var(--ink); border-color: var(--ink); }
.btn-ink:hover { background: var(--ink); color: #FFFFFF; }
.btn-ghost { background: transparent; color: var(--muted); border-color: var(--line); }
.btn-ghost:hover { color: var(--ink); border-color: var(--ink); }
.btn-danger { background: transparent; color: var(--rose); border-color: var(--rose); }
.btn-danger:hover { background: var(--rose-bg); }
.btn:disabled { opacity: .5; cursor: not-allowed; }
.btn:focus-visible, a:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
    outline: 2px solid var(--act); outline-offset: 1px;
}

/* ---------- Form ---------- */
.field {
    width: 100%; background: var(--surface); border: 1px solid var(--line);
    border-radius: 4px; padding: 8px 11px; font-size: 13.5px; color: var(--body);
}
.field.mono { font-family: var(--font-mono); }
.field:focus { border-color: var(--act); outline: none; box-shadow: 0 0 0 1px var(--act); }
.field::placeholder { color: var(--muted); }
label { font-size: 12px; font-weight: 600; color: var(--ink); }

/* ---------- Tabel gaya lembar kerja ---------- */
.sheet { width: 100%; border-collapse: collapse; font-size: 13px; }
.sheet thead th {
    font-size: 11px; font-weight: 600; color: var(--ink); text-align: left;
    padding: 9px 14px; border-bottom: 1.5px solid var(--ink); background: var(--paper);
}
.sheet tbody td { padding: 10px 14px; border-bottom: 1px solid var(--line-soft); color: var(--body); vertical-align: top; }
.sheet tbody tr:hover td { background: #F4F1EA; }

/* ---------- Cap stempel status (bukan pill) ---------- */
.stamp {
    display: inline-block; font-family: var(--font-mono); font-size: 11px; font-weight: 700;
    letter-spacing: .04em; padding: 3px 8px; border-radius: 3px;
    border: 1.5px solid currentColor; text-transform: uppercase; background: transparent;
    transform: rotate(-1.2deg);
}
.stamp-pending   { color: var(--amber-ink); background: var(--amber-bg); }
.stamp-diagnosing{ color: var(--ink); }
.stamp-progress  { color: #FFFFFF; background: var(--act); border-color: var(--act-deep); transform: rotate(1.4deg); }
.stamp-ready     { color: var(--act-deep); background: rgba(47, 102, 144, .08); }
.stamp-done      { color: var(--warranty); }
.stamp-cancelled { color: var(--rose); background: var(--rose-bg); }

@keyframes stamp-in {
    0% { transform: scale(1.6) rotate(-6deg); opacity: 0; }
    60% { transform: scale(.94) rotate(-.8deg); opacity: 1; }
    100% { transform: scale(1) rotate(-1.2deg); opacity: 1; }
}
.stamp-anim { animation: stamp-in .28s ease-out both; }

/* ---------- Garis dimensi (anotasi angka penting) ---------- */
.dim-value { font-family: var(--font-mono); font-size: 15px; font-weight: 600; color: var(--ink); }
.dim-label { font-size: 11px; color: var(--muted); }

/* ---------- Timeline vertikal bertinta ---------- */
.timeline { position: relative; margin-left: 7px; padding-left: 26px; border-left: 1px solid var(--line); }
.timeline .tstep { position: relative; padding-bottom: 22px; }
.timeline .tstep:last-child { padding-bottom: 0; }
.timeline .tdot {
    position: absolute; left: -31.5px; top: 3px; width: 9px; height: 9px;
    border-radius: 50%; background: var(--paper); border: 1.5px solid var(--line);
}
.timeline .tstep.done .tdot { background: var(--ink); border-color: var(--ink); }
.timeline .tstep.done .tlabel { color: var(--ink); font-weight: 600; }
.timeline .tstep.now .tdot { background: var(--act); border-color: var(--act); box-shadow: 0 0 0 3px rgba(47, 102, 144, .18); }
.timeline .tstep.now .tlabel { color: var(--act-deep); font-weight: 700; }
.tlabel { font-size: 13px; color: var(--muted); }
.tdesc { font-size: 12px; color: var(--muted); margin-top: 2px; }

/* ---------- Kartu statistik kop-nota ---------- */
.statcard { background: var(--surface); border: 1px solid var(--line); border-top: 2px solid var(--ink); border-radius: 5px; padding: 12px 14px; }
.statnum { font-family: var(--font-mono); font-size: 24px; font-weight: 600; color: var(--ink); line-height: 1.1; }

/* ---------- Banner ---------- */
.banner { border-radius: 4px; padding: 12px 14px; font-size: 13px; border: 1px solid; }
.banner-rose { color: var(--rose); border-color: rgba(185, 28, 28, .35); background: var(--rose-bg); }
.banner-amber { color: var(--amber-ink); border-color: rgba(138, 90, 27, .35); background: var(--amber-bg); }
.banner-act { color: var(--act-deep); border-color: var(--line); background: rgba(47, 102, 144, .06); }
.banner-warranty { color: var(--warranty); border-color: var(--warranty-line); background: rgba(74, 127, 165, .07); }

/* ---------- Cetak ---------- */
@media print {
    .no-print { display: none !important; }
    body { background: #FFFFFF !important; }
    .panel { border-color: #999 !important; box-shadow: none !important; }
}

/* ---------- Utilitas kecil ---------- */
.t-xs { font-size: 11px; } .t-sm { font-size: 12.5px; } .t-md { font-size: 13.5px; }
.t-ink { color: var(--ink); } .t-muted { color: var(--muted); } .t-body { color: var(--body); }
code, .code-chip { font-family: var(--font-mono); }
.code-chip { font-size: 12px; color: var(--ink); font-weight: 600; }
</style>
