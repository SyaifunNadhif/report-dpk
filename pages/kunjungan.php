<!-- ===== Init params dari POST (maping_account -> create) ===== -->
<div id="kun-init"
  data-no_rekening="<?= htmlspecialchars($_POST['no_rekening'] ?? '') ?>"
  data-closing_date="<?= htmlspecialchars($_POST['closing_date'] ?? '') ?>"
  data-harian_date="<?= htmlspecialchars($_POST['harian_date'] ?? '') ?>"
  data-kode_kantor="<?= htmlspecialchars($_POST['kode_kantor'] ?? '') ?>">
</div>

<style>
  .kun-cam-backdrop{ z-index: 2147483647 !important; }
  .kun-cam-sheet{ position: relative; z-index: 2147483647 !important; }
</style>

<div class="kun-wrap">
  <div class="kun-card">
    <h2 class="kun-title">📍 Create Kunjungan</h2>
    <p class="kun-desc">Lengkapi data, ambil foto/unggah, dan sistem akan menempel waktu & lokasi ke foto.</p>

    <div id="kun-status" class="kun-status hidden"></div>

    <!-- DATA DEBITUR -->
    <fieldset class="kun-fieldset">
      <legend>Data Debitur</legend>
      <div class="kun-grid2">
        <div><label>No. Rekening</label><input id="f_no_rek" type="text" readonly></div>
        <div><label>Nama Debitur</label><input id="f_nama" type="text" readonly></div>
        <div><label>Kolektabilitas</label><input id="f_kolek" type="text" readonly></div>
        <div><label>Baki Debet</label><input id="f_bd" type="text" readonly></div>
        <div><label>Hari Menunggak</label><input id="f_hm" type="text" readonly></div>
        <div><label>Tunggakan Pokok</label><input id="f_tp" type="text" readonly></div>
        <div><label>Tunggakan Bunga</label><input id="f_tb" type="text" readonly></div>
      </div>
    </fieldset>

    <!-- TINDAKAN -->
    <fieldset class="kun-fieldset">
      <legend>Tindakan</legend>
      <div class="kun-grid2">
        <div>
          <label>Kode Tindakan</label>
          <select id="kode_tindakan" required>
            <option value="">— Pilih —</option>
            <!-- Contacted -->
            <option value="ALM">ALM - Almarhum</option><option value="PBD">PBD - Pasang Badan</option>
            <option value="KSS">KSS - Kasus</option><option value="BCN">BCN - Bencana</option>
            <option value="SKT">SKT - Sakit</option><option value="JJA">JJA - Janji Jual Aset</option>
            <option value="JJJ">JJJ - Janji Jual Jaminan</option><option value="RES">RES - Restruktur</option>
            <option value="HPR">HPR - Proses Hukum</option><option value="PTP">PTP - Promise to Pay</option>
            <option value="PET">PET - Pick up Promise Taken</option><option value="PPK">PPK - Pick up Payment Collected</option>
            <option value="LNS">LNS - Pelunasan</option>
            <!-- No-contacted -->
            <option value="FRD">FRD - Fraud</option><option value="ARA">ARA - Alamat Salah sejak Awal</option>
            <option value="CRA">CRA - Cerai</option><option value="PHD">PHD - Pindah</option>
            <option value="RKS">RKS - Rumah Kosong</option><option value="SKP">SKP - Skip</option>
          </select>
        </div>
        <div>
          <label>Jenis Tindakan</label>
          <select id="jenis_tindakan" required>
            <option value="">— Pilih —</option>
            <option value="Kunjungan">Kunjungan</option><option value="Telepon">Telepon</option><option value="Lainnya">Lainnya</option>
          </select>
        </div>
        <div>
          <label>Lokasi Tindakan</label>
          <select id="lokasi_tindakan" required>
            <option value="">— Pilih —</option>
            <option value="Rumah">Rumah</option><option value="Kantor">Kantor</option>
            <option value="Handphone">Handphone</option><option value="Lainnya">Lainnya</option>
          </select>
        </div>
        <div>
          <label>Orang Ditemui</label>
          <select id="orang_ditemui">
            <option value="">— Pilih —</option>
            <option value="Debitur">Debitur</option><option value="Ibu">Ibu</option>
            <option value="Bapak">Bapak</option><option value="Pasangan">Pasangan</option>
            <option value="Anak">Anak</option><option value="Lainnya">Lainnya</option>
          </select>
        </div>

        <div>
          <label>Nominal Janji Bayar (aktif untuk PTP/PET/PPK/LNS/RES)</label>
          <input id="nominal_janji_bayar" type="number" min="0" step="1000" placeholder="0" disabled>
        </div>
        <div>
          <label>Tanggal Janji Bayar</label>
          <input id="tanggal_janji_bayar" type="date" disabled>
        </div>

        <!-- status hidden -->
        <input id="status_kunjungan" type="hidden" value="">
        <div>
          <label>Keterangan</label>
          <textarea id="keterangan" rows="3" placeholder="Catatan singkat"></textarea>
        </div>
      </div>
    </fieldset>

    <!-- LOKASI & FOTO -->
    <fieldset class="kun-fieldset">
      <legend>Lokasi & Foto</legend>
      <div class="kun-grid2">
        <!-- Koordinat disembunyikan tapi tetap ada di DOM -->
        <div style="display:none">
          <label>Koordinat</label>
          <div class="kun-row">
            <input id="lat" type="text" placeholder="Lat" readonly>
            <input id="lng" type="text" placeholder="Lng" readonly>
          </div>
        </div>
        <div>
          <label>Alamat (otomatis dari koordinat)</label>
          <div class="kun-row">
            <input id="alamat_gps" type="text" placeholder="Klik tombol lokasi agar terisi…" readonly style="flex:1">
            <button id="btnLoc" type="button" class="kbtn kbtn-secondary" title="Ambil Koordinat">📍</button>
          </div>
        </div>

        <div>
          <label>Foto Kunjungan</label>
          <div class="kun-row">
            <input id="nama_foto" type="text" placeholder="Belum dipilih" readonly>
            <button id="btnUpload" type="button" class="kbtn" title="Upload dari Galeri">⬆️ Upload</button>
            <button id="btnCamera" type="button" class="kbtn kbtn-primary" title="Ambil Foto (Kamera Live)">📷 Kamera</button>
            <button id="btnDownload" type="button" class="kbtn" title="Unduh foto bertanda" disabled>⬇️ Unduh</button>
          </div>
          <input id="fileFoto" type="file" accept="image/*" class="hidden">
        </div>

        <div>
          <label>Waktu Buat</label>
          <input id="created" type="text" readonly>
        </div>

        <div class="kun-col-full">
          <label>Preview</label>
          <div class="kun-preview"><canvas id="canvas" width="0" height="0"></canvas></div>
        </div>
      </div>
    </fieldset>

    <div class="kun-actions">
      <button id="btnSimpan" class="kbtn kbtn-primary" type="button" title="Simpan">💾 Simpan</button>
      <button id="btnBatal" class="kbtn" type="button" onclick="history.back()">Batal</button>
    </div>
  </div>
</div>

<!-- Kamera Modal -->
<div class="kun-cam-backdrop" id="camPanel" hidden>
  <div class="kun-cam-sheet">
    <div class="kun-cam-head">
      <strong>🎥 Kamera</strong>
      <div class="kun-row">
        <button id="btnSwitch" type="button" class="kbtn">Switch</button>
        <button id="btnClose" type="button" class="kbtn">Tutup</button>
      </div>
    </div>
    <video id="video" autoplay playsinline muted></video>
    <div class="kun-actions" style="margin-top:10px">
      <button id="btnSnap" type="button" class="kbtn kbtn-secondary">📸 Capture</button>
    </div>
    <div id="camStatus" class="kun-desc" style="margin-top:6px"></div>
  </div>
</div>

<style>
  /* Scoped */
  .kun-wrap{ max-width:1080px; margin:0 auto; padding:16px; }
  .kun-card{ background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:16px; box-shadow:0 6px 18px rgba(0,0,0,.06); }
  .kun-title{ margin:0 0 4px; font-size:20px; font-weight:700; color:#0f172a; }
  .kun-desc{ margin:0 0 10px; color:#475569; font-size:14px; }

  .kun-status{ margin:0 0 12px; padding:10px 12px; border-radius:10px; font-size:14px; }
  .hidden{ display:none !important; }

  .kun-fieldset{ border:1px solid #e5e7eb; border-radius:12px; padding:12px; margin:10px 0; }
  .kun-fieldset > legend{ padding:0 6px; color:#1f2937; font-weight:600; }

  .kun-grid2{ display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  .kun-col-full{ grid-column:1 / -1; }
  .kun-row{ display:flex; gap:8px; align-items:center; flex-wrap:wrap; }

  label{ display:block; font-size:13px; color:#334155; margin-bottom:6px; }
  input[type="text"], input[type="date"], input[type="number"], textarea, select{
    width:100%; background:#fff; border:1px solid #cbd5e1; color:#0f172a; padding:10px 12px; border-radius:10px; font-size:14px;
  }
  textarea{ min-height:70px; resize:vertical; }

  .kbtn{ display:inline-flex; gap:8px; align-items:center; justify-content:center; padding:10px 14px; border-radius:12px;
         background:#f1f5f9; border:1px solid #cbd5e1; color:#0f172a; font-weight:600; }
  .kbtn:hover{ background:#e2e8f0; }
  .kbtn-primary{ background:#2563eb; border-color:#1d4ed8; color:#fff; box-shadow:0 6px 14px rgba(37,99,235,.25); }
  .kbtn-primary:hover{ background:#1e40af; }
  .kbtn-secondary{ background:#10b981; border-color:#059669; color:#fff; box-shadow:0 6px 14px rgba(16,185,129,.25); }
  .kbtn-secondary:hover{ background:#047857; }

  .kun-actions{ display:flex; gap:10px; flex-wrap:wrap; margin-top:12px; }

  /* Preview: lebih enak dilihat di web */
  .kun-preview{ 
    background:#f8fafc; border:1px dashed #cbd5e1; border-radius:12px; 
    min-height:220px; display:flex; align-items:center; justify-content:center; padding:10px;
  }
  @media (min-width:1024px){ .kun-preview{ min-height:360px; } }
  #canvas{ max-width:100%; height:auto; border-radius:10px; display:block; }

  /* Kamera modal */
  .kun-cam-backdrop{ position:fixed; inset:0; background:rgba(15,23,42,.5); display:grid; place-items:center; z-index:1000; }
  .kun-cam-sheet{ width:min(96vw,560px); background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:12px; }
  .kun-cam-head{ display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; }
  #video{ width:100%; height:auto; max-height:65vh; object-fit:contain; background:#000; border-radius:12px; }

  @media (max-width:900px){ .kun-grid2{ grid-template-columns:1fr; } }
  @media (max-width:640px){
    .kun-wrap{ padding:12px; }
    input, select, textarea{ font-size:13px; }
    .kbtn{ width:100%; }
    .kun-preview{ min-height:180px; }
  }
</style>

<script>
(() => {
  const API_URL = './api/kunjungan/';

  // Helpers
  const $ = s => document.querySelector(s);
  const ctx = $('#canvas').getContext('2d');
  const token = ()=> (window.AUTH_TOKEN || localStorage.getItem('dpk_token') || '').trim();
  const fmtRp = n => Number(n||0).toLocaleString('id-ID');
  const nowWIB = ()=>{
    const d=new Date(), p=v=>String(v).padStart(2,'0');
    return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}:${p(d.getSeconds())}`;
  };

  // Hanya set ini yang mengaktifkan PTP-fields & set status = 1
  const ENABLE_PTP = new Set(['PTP','PET','PPK','LNS','RES']);

  // State
  let latestGeo = { lat:null, lng:null, acc:null, alamat:null };
  let currentImage = null; let stamped = false;

  const init = $('#kun-init')?.dataset || {};
  $('#created').value = nowWIB();

  // ================= LOAD DETAIL (type: detail_by_rekening) =================
  (async () => {
    const body = {
      kode_kantor: (init.kode_kantor || '001').toString().padStart(3,'0'),
      type: 'detail_by_rekening',
      closing_date: init.closing_date,
      harian_date : init.harian_date,
      no_rekening : init.no_rekening
    };
    try{
      const headers = { 'Content-Type':'application/json' };
      const t = token(); if(t) headers['Authorization'] = t;

      const res = await fetch(API_URL, { method:'POST', headers, body: JSON.stringify(body) });
      const j = await res.json().catch(()=> ({}));
      if(!res.ok) throw new Error(j?.message || ('HTTP '+res.status));

      const d0 = (Array.isArray(j?.data?.rows) && j.data.rows[0]) ? j.data.rows[0] : (j?.data || {});
      // tampil (pakai kolek_update)
      $('#f_no_rek').value = d0.no_rekening || init.no_rekening || '-';
      $('#f_nama').value  = d0.nama_nasabah || d0.nama_debitur || d0.key_name || '-';
      $('#f_kolek').value = d0.kolek_update ?? '-';
      $('#f_bd').value    = fmtRp(d0.baki_debet_update ?? d0.baki_debet ?? 0);
      $('#f_hm').value    = (d0.hari_menunggak ?? '-') + (d0.hari_menunggak!=null?' hari':'');
      $('#f_tp').value    = fmtRp(d0.tunggakan_pokok ?? 0);
      $('#f_tb').value    = fmtRp(d0.tunggakan_bunga ?? 0);

      // raw
      $('#f_no_rek').dataset.raw = d0.no_rekening || '';
      $('#f_nama').dataset.raw   = $('#f_nama').value || '';
      $('#f_kolek').dataset.raw  = d0.kolek_update ?? '';
      $('#f_bd').dataset.raw     = Number(d0.baki_debet_update ?? d0.baki_debet ?? 0);
      $('#f_hm').dataset.raw     = Number(d0.hari_menunggak ?? 0);
      $('#f_tp').dataset.raw     = Number(d0.tunggakan_pokok ?? 0);
      $('#f_tb').dataset.raw     = Number(d0.tunggakan_bunga ?? 0);
    }catch(e){
      console.warn('Load detail error:', e?.message || e);
    }
  })();

  // ===== GEO + Reverse geocode (detail RT/RW, kel/desa, kec, kota/kab, prov, pos)
  function fmtCoord(v){ return (v==null || isNaN(v)) ? '' : Number(v).toFixed(6); }

  function formatOSM(addr = {}){
    // Banyak provider di OSM: neighbourhood (RT/RW), quarter, suburb (kel/desa), city_district (kecamatan),
    // town/city/municipality (kota/kab)
    const rtRw   = addr.neighbourhood || addr.quarter || addr.residential || addr.hamlet || '';
    const jalan  = addr.road || addr.pedestrian || addr.cycleway || addr.footway || '';
    const dusun  = addr.village || addr.suburb || '';
    const kelDes = addr.suburb || addr.city_block || '';
    const kec    = addr.city_district || addr.district || addr.subdistrict || '';
    const kota   = addr.city || addr.town || addr.municipality || addr.county || '';
    const prov   = addr.state || '';
    const pos    = addr.postcode || '';
    // Contoh: Jl. Melati No.1, RT 02/RW 03, Kel. Sekayu, Kec. Semarang Tengah, Kota Semarang, Jawa Tengah 50231
    const parts = [];
    if(jalan) parts.push(jalan);
    if(rtRw)  parts.push(rtRw);
    if(dusun && !parts.includes(dusun)) parts.push(`Kel./Desa ${dusun}`);
    if(kec)   parts.push(`Kec. ${kec}`);
    if(kota)  parts.push(kota.startsWith('Kota')||kota.startsWith('Kab.')?kota:(`Kota/Kab. ${kota}`));
    if(prov)  parts.push(prov);
    if(pos)   parts.push(pos);
    return parts.filter(Boolean).join(', ');
  }

  async function resolveAddress(lat, lng){
    $('#alamat_gps').value = 'Mencari alamat…'; latestGeo.alamat=null;

    // Coba beberapa zoom agar dapat RT/RW kalau ada
    const zooms = [19,18,17,16,15,14];
    for(const z of zooms){
      try{
        const u=`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&zoom=${z}&addressdetails=1&accept-language=id`;
        const r=await fetch(u,{headers:{'Accept':'application/json'}});
        if(r.ok){
          const d=await r.json();
          const formatted = formatOSM(d.address||{});
          if(formatted){
            latestGeo.alamat=formatted;
            $('#alamat_gps').value=formatted;
            return formatted;
          }
          if(d.display_name){
            latestGeo.alamat=d.display_name;
            $('#alamat_gps').value=d.display_name;
            return d.display_name;
          }
        }
      }catch{}
    }

    // Fallback BigDataCloud (kadang lebih bagus untuk kec/kota)
    try{
      const u=`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${encodeURIComponent(lat)}&longitude=${encodeURIComponent(lng)}&localityLanguage=id`;
      const r=await fetch(u);
      if(r.ok){
        const d=await r.json();
        const parts=[d.localityInfo?.administrative?.[2]?.name, d.locality, d.city, d.principalSubdivision, d.postcode, d.countryName].filter(Boolean);
        const name=parts.join(', ');
        if(name){
          latestGeo.alamat=name; $('#alamat_gps').value=name; return name;
        }
      }
    }catch{}

    const approx=`Sekitar ${Number(lat).toFixed(5)}, ${Number(lng).toFixed(5)}`;
    latestGeo.alamat=approx; $('#alamat_gps').value=approx; 
    return approx;
  }

  $('#btnLoc').addEventListener('click', ()=>{
    if(!navigator.geolocation){ return; }
    navigator.geolocation.getCurrentPosition(async pos=>{
      latestGeo.lat = pos.coords.latitude; latestGeo.lng = pos.coords.longitude; latestGeo.acc = pos.coords.accuracy || null;
      $('#lat').value = fmtCoord(latestGeo.lat); $('#lng').value = fmtCoord(latestGeo.lng);
      await resolveAddress(latestGeo.lat, latestGeo.lng);
      if(currentImage) drawWatermark();
    }, ()=>{}, { enableHighAccuracy:true, timeout:15000, maximumAge:0 });
  });

  // ===== FOTO
  const previewBox = document.querySelector('.kun-preview');
  function drawImageToCanvas(img){
    const maxW = Math.max(360, previewBox.clientWidth || 360);
    const ratio = img.naturalWidth / img.naturalHeight;
    const cssW = maxW, cssH = Math.round(cssW/ratio);
    const dpr = window.devicePixelRatio || 1;
    $('#canvas').width = Math.round(cssW*dpr);
    $('#canvas').height= Math.round(cssH*dpr);
    $('#canvas').style.width = cssW+'px'; $('#canvas').style.height = cssH+'px';
    ctx.setTransform(dpr,0,0,dpr,0,0);
    ctx.clearRect(0,0,cssW,cssH);
    ctx.drawImage(img,0,0,cssW,cssH);
  }
  function wrapText(ctx, text, maxWidth){
    if(!text) return []; const words=String(text).split(/\s+/); const lines=[]; let line='';
    for(const w of words){ const test=line?line+' '+w:w; if(ctx.measureText(test).width<=maxWidth){ line=test; } else { if(line) lines.push(line); line=w; if(lines.length>=2) break; } }
    if(line && lines.length<2) lines.push(line); return lines;
  }
  function drawWatermark(){
    if(!currentImage) return;
    drawImageToCanvas(currentImage.img);
    const dpr = window.devicePixelRatio || 1;
    const nowText = new Date().toLocaleString('id-ID', { hour12:false }) + ' WIB';
    const hasGeo = latestGeo.lat!=null && latestGeo.lng!=null && !Number.isNaN(latestGeo.lat) && !Number.isNaN(latestGeo.lng);
    const geoText = hasGeo ? `${Number(latestGeo.lat).toFixed(6)}, ${Number(latestGeo.lng).toFixed(6)}` : 'Koordinat: —';
    const addrText = latestGeo.alamat || '';
    ctx.font='16px ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial';
    const lineH=22, padX=12, padY=8, margin=14;
    const maxTxtW = ($('#canvas').width/dpr) - (margin*2) - (padX*2);
    const lines = [ nowText, geoText ].concat(wrapText(ctx, addrText, Math.min(maxTxtW, 520)));
    const textW = Math.max(...lines.map(t=>ctx.measureText(t).width));
    const boxW = Math.min(textW + padX*2, ($('#canvas').width/dpr) - margin*2);
    const boxH = (lines.length*lineH) + padY*2;
    const x = ($('#canvas').width/dpr) - margin - boxW;
    const y = ($('#canvas').height/dpr) - margin - boxH;
    ctx.globalAlpha = .65; ctx.fillStyle = '#000';
    const rr=8; ctx.beginPath();
    ctx.moveTo(x+rr,y); ctx.arcTo(x+boxW,y,x+boxW,y+boxH,rr); ctx.arcTo(x+boxW,y+boxH,x,y+boxH,rr);
    ctx.arcTo(x,y+boxH,x,y,rr); ctx.arcTo(x,y,x+boxW,y,rr); ctx.closePath(); ctx.fill();
    ctx.globalAlpha = 1; ctx.fillStyle = '#fff';
    let yy = y + padY + lineH - 4;
    lines.forEach(t => { ctx.fillText(t, x + padX, yy); yy += lineH; });
    $('#btnDownload').disabled = false; stamped = true;
  }
  $('#btnUpload').addEventListener('click', ()=> $('#fileFoto').click());
  $('#fileFoto').addEventListener('change', async ()=>{
    const f = $('#fileFoto').files?.[0]; if(!f) return;
    $('#nama_foto').value = f.name || 'foto.jpg';
    const url = URL.createObjectURL(f);
    const img = new Image();
    img.onload = async ()=>{ currentImage={img}; if(!$('#alamat_gps').value) { $('#btnLoc').click(); await new Promise(r=>setTimeout(r,300)); } drawWatermark(); URL.revokeObjectURL(url); };
    img.src = url;
  });

  // Kamera
  let stream=null, devices=[], devIdx=0, forced=null;
  const camPanel = $('#camPanel'), video = $('#video'), camStatus = $('#camStatus');
  async function listDevices(){ try{ devices=(await navigator.mediaDevices.enumerateDevices()).filter(d=>d.kind==='videoinput'); }catch{ devices=[]; } }
  function stopCam(){ if(stream){ stream.getTracks().forEach(t=>t.stop()); stream=null; } }
  async function startCam(deviceId){
    if(!navigator.mediaDevices?.getUserMedia){ return false; }
    try{
      await listDevices(); stopCam(); camStatus.textContent='Meminta izin kamera…';
      const defFacing = /Mobi|Android/i.test(navigator.userAgent) ? 'environment' : 'user';
      const facing = forced || defFacing;
      let cons = deviceId ? { video:{ deviceId:{ exact: deviceId } } } :
                            { video:{ facingMode:{ ideal: facing }, width:{ ideal:1280 }, height:{ ideal:720 } } };
      try{ stream=await navigator.mediaDevices.getUserMedia(cons); }
      catch{ cons = deviceId ? { video:{ deviceId:{ exact: deviceId } } } : { video:true }; stream=await navigator.mediaDevices.getUserMedia(cons); }
      video.srcObject = stream; await video.play().catch(()=>{});
      camPanel.hidden=false; camStatus.textContent='Kamera siap.';
      if(deviceId) devIdx=Math.max(0, devices.findIndex(d=>d.deviceId===deviceId));
      return true;
    }catch(e){ return false; }
  }
  $('#btnCamera').addEventListener('click', async ()=>{
    const pGeo = new Promise(res=>{ if(!$('#alamat_gps').value) $('#btnLoc').click(); setTimeout(res, 200); });
    await startCam(); await pGeo;
  });
  $('#btnClose').addEventListener('click', ()=>{ stopCam(); camPanel.hidden=true; });
  $('#btnSwitch').addEventListener('click', async ()=>{
    forced = (forced==='user') ? 'environment' : (forced==='environment' ? 'user' : 'user');
    const ok = await startCam();
    if(!ok && devices.length){ devIdx=(devIdx+1)%devices.length; forced=null; startCam(devices[devIdx].deviceId); }
  });
  $('#btnSnap').addEventListener('click', ()=>{
    if(!stream) return;
    const off=document.createElement('canvas'); const w=video.videoWidth||1280, h=video.videoHeight||720;
    off.width=w; off.height=h; off.getContext('2d').drawImage(video,0,0,w,h);
    off.toBlob(blob=>{
      const ts = new Date().toISOString().replace(/[-:T]/g,'').slice(0,14);
      const fname = `IMG_${ts}.jpg`; $('#nama_foto').value=fname;
      const img=new Image(); img.onload=()=>{ currentImage={img}; drawWatermark(); };
      img.src=URL.createObjectURL(blob);
      stopCam(); camPanel.hidden=true;
    },'image/jpeg',0.92);
  });

  // Unduh foto
  $('#btnDownload').addEventListener('click', ()=>{
    if(!stamped) return;
    const a=document.createElement('a'); const ts=new Date().toISOString().replace(/[-:T]/g,'').slice(0,14);
    a.download=`kunjungan_${ts}.jpg`; a.href=$('#canvas').toDataURL('image/jpeg',0.92); a.click();
  });

  // PTP & status (otomatis) — status = 1 kalau ENABLE_PTP, selain itu 0
  function updateFromKode(){
    const kode = $('#kode_tindakan').value;
    const enable = ENABLE_PTP.has(kode);
    $('#nominal_janji_bayar').disabled = !enable;
    $('#tanggal_janji_bayar').disabled = !enable;
    if(!enable){ $('#nominal_janji_bayar').value=''; $('#tanggal_janji_bayar').value=''; }
    $('#status_kunjungan').value = enable ? '1' : '0';
  }
  $('#kode_tindakan').addEventListener('change', updateFromKode);
  updateFromKode();

  // ========= helper foto untuk FormData =========
  function canvasToBlobPromise(canvas, type='image/jpeg', quality=0.92){
    return new Promise(resolve => canvas.toBlob(b=>resolve(b), type, quality));
  }
  async function getPhotoBlob(){
    if(stamped && $('#canvas').width && $('#canvas').height){
      const blob = await canvasToBlobPromise($('#canvas'));
      const name = $('#nama_foto').value || `kunjungan_${Date.now()}.jpg`;
      return {blob, name};
    }
    const f = $('#fileFoto').files?.[0];
    if(f){ return {blob:f, name:f.name || 'foto.jpg'}; }
    return null;
  }

  // ================== SIMPAN (type: create_kunjungan) - multipart ==================
  $('#btnSimpan').addEventListener('click', async () => {
    try {
      const t = token();
      const fd = new FormData();

      fd.append('type', 'create_kunjungan');
      fd.append('no_rekening', ($('#f_no_rek').dataset.raw || '').trim());
      fd.append('baki_debet', String($('#f_bd').dataset.raw || 0));
      fd.append('hari_menunggak', String($('#f_hm').dataset.raw || 0));
      fd.append('kolektabilitas', ($('#f_kolek').dataset.raw || $('#f_kolek').value || ''));

      const nama = ($('#f_nama').dataset.raw || $('#f_nama').value || '').trim();
      fd.append('nama_nasabah', nama);
      fd.append('nama_debitur', nama);

      fd.append('tunggakan_pokok', String($('#f_tp').dataset.raw || 0));
      fd.append('tunggakan_bunga', String($('#f_tb').dataset.raw || 0));

      const kode = $('#kode_tindakan').value.trim();
      fd.append('kode_tindakan', kode);
      fd.append('jenis_tindakan', $('#jenis_tindakan').value.trim());
      fd.append('lokasi_tindakan', $('#lokasi_tindakan').value.trim());
      fd.append('orang_ditemui', $('#orang_ditemui').value.trim());

      const enable = ENABLE_PTP.has(kode);
      fd.append('nominal_janji_bayar', enable && $('#nominal_janji_bayar').value ? $('#nominal_janji_bayar').value : '0');
      fd.append('tanggal_janji_bayar', enable ? ($('#tanggal_janji_bayar').value || '') : '');
      fd.append('status_kunjungan', enable ? '1' : '0');
      fd.append('keterangan', $('#keterangan').value.trim());

      const koordinat = ($('#lat').value && $('#lng').value) ? `${$('#lat').value}, ${$('#lng').value}` : '';
      fd.append('alamat_gps', $('#alamat_gps').value.trim());
      fd.append('koordinat', koordinat);
      fd.append('tgl_kunjungan', $('#created').value || nowWIB());

      const foto = await getPhotoBlob();
      if (foto) {
        fd.append('foto', foto.blob, foto.name);
        fd.append('nama_foto', foto.name);
      } else {
        fd.append('nama_foto', $('#nama_foto').value.trim());
      }

      if (!fd.get('no_rekening')) throw new Error('No. rekening kosong');
      if (!fd.get('kode_tindakan')) throw new Error('Pilih kode tindakan');
      if (!fd.get('jenis_tindakan')) throw new Error('Pilih jenis tindakan');

      const headers = t ? { 'Authorization': t } : {};

      // tampilkan loading agar user tahu sedang disimpan
      $('#btnSimpan').disabled = true;
      $('#btnSimpan').textContent = 'Menyimpan...';

      const res = await fetch(API_URL, { method: 'POST', headers, body: fd });
      const j = await res.json().catch(() => ({}));

      if (!res.ok) throw new Error(j?.message || ('HTTP ' + res.status));
      if (!j.success && !j.status && !j.insert_id) throw new Error('Server belum mengonfirmasi penyimpanan');

      // jika sampai sini sukses, baru pindah
      alert('Data kunjungan berhasil disimpan!');
      location.assign('account_handle');

    } catch (e) {
      alert(e?.message || 'Gagal menyimpan kunjungan');
    } finally {
      $('#btnSimpan').disabled = false;
      $('#btnSimpan').textContent = 'Simpan';
    }
  });

})();
</script>
