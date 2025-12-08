<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
<title>POS (Struk Offline) • UMKM BKK</title>
<script src="https://cdn.tailwindcss.com"></script>
</head><body class="bg-gray-50 text-gray-900">
<div class="max-w-md mx-auto min-h-screen flex flex-col">
  <header class="sticky top-0 bg-white border-b z-10">
    <div class="px-4 py-3 flex items-center justify-between">
      <a href="./index.html" class="text-xs px-3 py-1 border rounded-lg">← Dashboard</a>
      <h1 class="text-lg font-semibold">POS • Struk</h1>
      <span></span>
    </div>
  </header>

  <main class="flex-1 p-4">
    <!-- UMKM & cari item -->
    <div class="bg-white rounded-2xl shadow p-4 mb-3">
      <label class="block text-sm mb-1">UMKM</label>
      <select id="umkm" class="w-full border rounded-xl px-3 py-2 text-sm"></select>
      <div class="mt-3">
        <input id="q" class="w-full border rounded-xl px-3 py-2 text-sm" placeholder="Cari item…">
      </div>
      <ul id="suggest" class="mt-2 text-sm max-h-40 overflow-auto"></ul>
    </div>

    <!-- Keranjang -->
    <div class="bg-white rounded-2xl shadow p-4">
      <div class="flex items-center justify-between mb-2">
        <div class="font-semibold">Keranjang</div>
        <button id="clear" class="text-xs px-2 py-1 border rounded-lg">Bersihkan</button>
      </div>
      <ul id="cart" class="text-sm space-y-2"></ul>
      <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
        <input id="discount" type="number" step="0.01" class="border rounded-xl px-3 py-2" placeholder="Diskon Rp">
        <input id="tax"      type="number" step="0.01" class="border rounded-xl px-3 py-2" placeholder="Pajak Rp">
        <input id="svc"      type="number" step="0.01" class="border rounded-xl px-3 py-2" placeholder="Service Rp">
        <input id="ship"     type="number" step="0.01" class="border rounded-xl px-3 py-2" placeholder="Ongkir Rp">
      </div>
      <div class="mt-3 flex items-center justify-between">
        <div class="text-xs text-gray-500">Total</div>
        <div id="total" class="text-lg font-semibold">Rp 0</div>
      </div>
      <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
        <input id="cash" type="number" step="0.01" class="border rounded-xl px-3 py-2" placeholder="Tunai Diterima">
        <input id="change" disabled class="border rounded-xl px-3 py-2 bg-gray-100" placeholder="Kembalian">
      </div>
      <button id="pay" class="mt-3 w-full bg-emerald-600 text-white rounded-xl py-2 text-sm">Bayar & Cetak</button>
      <div id="msg" class="text-xs text-red-600 mt-2 hidden"></div>
      <div id="inv" class="text-center text-xs mt-3 hidden">
        <a id="invA" class="underline" href="#" target="_blank">Lihat/Cetak Struk</a>
      </div>
    </div>
  </main>

  <footer class="p-4 text-center text-xs text-gray-500">© UMKM BKK</footer>
</div>

<script>
const API='../api/index.php';
const fmtIDR = n => new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n||0);
let items = []; let cart=[]; let lastOrder=null;

async function ensureAuth(){
  const r=await fetch(`${API}?action=me`); const j=await r.json();
  if(!j.success){ location.href='./login.html'; }
}

async function loadUMKM(){
  const r=await fetch(`${API}?action=list_umkm`); const j=await r.json();
  const sel=document.getElementById('umkm'); sel.innerHTML='';
  (j.data||[]).forEach(u=>{ const o=document.createElement('option'); o.value=u.id; o.textContent=u.nama_umkm; sel.appendChild(o); });
  await loadItems();
}

async function loadItems(){
  const u=document.getElementById('umkm').value; if(!u) return;
  const r=await fetch(`${API}?action=list_items&umkm_id=${u}`); const j=await r.json();
  items = j.data||[];
  renderSuggest('');
}

function renderSuggest(q){
  const box=document.getElementById('suggest'); box.innerHTML='';
  const res = items.filter(i=> (i.nama_item||'').toLowerCase().includes(q.toLowerCase()));
  res.slice(0,50).forEach(i=>{
    const li=document.createElement('li');
    li.className='flex items-center justify-between border rounded-xl px-3 py-2';
    li.innerHTML=`<span>${i.nama_item}</span><span class="text-xs">${fmtIDR(i.sell_price||0)}</span>`;
    li.addEventListener('click',()=> addToCart(i));
    box.appendChild(li);
  });
}

function addToCart(i){
  const ex=cart.find(x=>x.item_id===i.id);
  if(ex){ ex.qty += 1; }
  else { cart.push({item_id:i.id, nama:i.nama_item, price:i.sell_price||0, qty:1}); }
  redraw();
}

function redraw(){
  const ul=document.getElementById('cart'); ul.innerHTML='';
  let sum = 0;
  cart.forEach((c,idx)=>{
    sum += c.price*c.qty;
    const li=document.createElement('li'); li.className='border rounded-xl px-3 py-2';
    li.innerHTML=`
      <div class="flex items-center justify-between">
        <div class="font-medium">${c.nama}</div>
        <button class="text-xs px-2 py-1 border rounded-lg" data-del="${idx}">Hapus</button>
      </div>
      <div class="mt-1 grid grid-cols-3 gap-2 text-xs">
        <input type="number" step="0.001" value="${c.qty}" data-qty="${idx}" class="border rounded-xl px-2 py-1">
        <input type="number" step="0.01" value="${c.price}" data-price="${idx}" class="border rounded-xl px-2 py-1">
        <div class="text-right pt-2">${fmtIDR(c.price*c.qty)}</div>
      </div>`;
    ul.appendChild(li);
  });
  document.getElementById('total').textContent = fmtIDR(calcGrand(sum));
  // bind
  ul.querySelectorAll('input[data-qty]').forEach(inp=> inp.addEventListener('input', e=>{ cart[+inp.dataset.qty].qty = parseFloat(inp.value||'0')||0; redraw(); }));
  ul.querySelectorAll('input[data-price]').forEach(inp=> inp.addEventListener('input', e=>{ cart[+inp.dataset.price].price = parseFloat(inp.value||'0')||0; redraw(); }));
  ul.querySelectorAll('button[data-del]').forEach(b=> b.addEventListener('click', ()=>{ cart.splice(+b.dataset.del,1); redraw(); }));

  // kembalian
  const cash = parseFloat(document.getElementById('cash').value||'0');
  const change = Math.max(0, cash - calcGrand(sum));
  document.getElementById('change').value = change;
}

function calcGrand(subtotal){
  const disc=parseFloat(document.getElementById('discount').value||'0');
  const tax =parseFloat(document.getElementById('tax').value||'0');
  const svc =parseFloat(document.getElementById('svc').value||'0');
  const ship=parseFloat(document.getElementById('ship').value||'0');
  return Math.max(0, subtotal - disc + tax + svc + ship);
}

document.getElementById('q').addEventListener('input', e=> renderSuggest(e.target.value));
document.getElementById('umkm').addEventListener('change', async()=>{ await loadItems(); cart=[]; redraw(); });
document.getElementById('clear').addEventListener('click', ()=>{ cart=[]; redraw(); });
['discount','tax','svc','ship','cash'].forEach(id=> document.getElementById(id).addEventListener('input', ()=> redraw()));

document.getElementById('pay').addEventListener('click', async()=>{
  if(cart.length===0) return;
  const umkm_id = document.getElementById('umkm').value;
  const items = cart.map(c=>({item_id:c.item_id, qty:c.qty, price:c.price}));
  const payload = {
    umkm_id, items,
    discount: parseFloat(document.getElementById('discount').value||'0'),
    tax: parseFloat(document.getElementById('tax').value||'0'),
    service_fee: parseFloat(document.getElementById('svc').value||'0'),
    shipping_fee: parseFloat(document.getElementById('ship').value||'0'),
    cash_paid: parseFloat(document.getElementById('cash').value||'0'),
    note: 'POS offline'
  };
  const r=await fetch(`${API}?action=pos_checkout`, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)});
  const j=await r.json();
  const msg=document.getElementById('msg'); const link=document.getElementById('inv'); const a=document.getElementById('invA');
  if(j.success){
    msg.classList.add('hidden');
    a.href = `${API}?action=order_invoice_print&order_id=${j.data.order_id}`;
    link.classList.remove('hidden');
    lastOrder=j.data.order_id;
    alert('Pembayaran sukses. Kembalian: '+fmtIDR(j.data.change));
    cart=[]; redraw();
  } else {
    msg.textContent=j.message||'Gagal POS'; msg.classList.remove('hidden');
  }
});

(async()=>{ await ensureAuth(); await loadUMKM(); redraw(); })();
</script>
</body></html>
