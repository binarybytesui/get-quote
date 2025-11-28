<?php
// /public/admin/products/index.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Admin auth (adjust to your project)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /get-quote/public/admin/views/index.php');
    exit;
}

// Make CSRF token available to JS
require_once __DIR__ . "/../../../src/helpers/csrf.php";
$csrf = generateCsrfToken();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin — Products</title>
  <style>
    /* products.css — minimal, paste into separate file if you prefer */
    :root{--bg:#f6f8fa;--card:#fff;--muted:#6b7280;--accent:#0ea5a4}
    body{font-family:Inter,ui-sans-serif,system-ui,Arial;margin:0;background:var(--bg);color:#111}
    .wrap{max-width:1200px;margin:28px auto;padding:18px}
    .header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
    .title{font-size:20px;font-weight:600}
    .grid{display:grid;grid-template-columns:260px 1fr;gap:18px}
    .card{background:var(--card);border-radius:8px;padding:12px;box-shadow:0 1px 3px rgba(0,0,0,.06)}

    /* category sidebar */
    .categories{max-height:70vh;overflow:auto}
    .cat{padding:8px;border-radius:6px;cursor:pointer;margin-bottom:6px}
    .cat.active{background:linear-gradient(90deg,#e6fffb,#f0fdfa)}
    .actions{display:flex;gap:8px}
    button{background:var(--accent);color:#fff;border:0;padding:8px 12px;border-radius:6px;cursor:pointer}
    button.ghost{background:transparent;color:var(--accent);border:1px solid rgba(14,165,164,.15)}

    /* table */
    table{width:100%;border-collapse:collapse}
    th,td{padding:8px 10px;border-bottom:1px solid #eef2f7;text-align:left}
    th{font-size:13px;color:var(--muted)}
    td.value{min-width:80px}
    td.editing{background:#fff9e6}

    /* modal */
    .modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.35);display:none;align-items:center;justify-content:center}
    .modal{width:680px;background:var(--card);padding:18px;border-radius:8px}
    .form-row{display:flex;gap:8px;margin-bottom:8px}
    .form-row input, .form-row select{flex:1;padding:8px;border:1px solid #e6eef3;border-radius:6px}

    /* toast */
    .toast{position:fixed;right:20px;top:20px;background:#111;color:#fff;padding:10px 14px;border-radius:6px;display:none}
  </style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="title">Products — Admin</div>
    <div class="actions">
      <button id="btnAdd">Add Product</button>
      <button class="ghost" id="btnRefresh">Refresh</button>
    </div>
  </div>

  <div class="grid">
    <div class="card">
      <h4 style="margin:0 0 8px 0">Categories</h4>
      <div id="categories" class="categories"></div>
    </div>

    <div class="card">
      <div style="overflow:auto;max-height:72vh">
        <table id="productsTable">
          <thead>
            <tr>
              <th>Category</th>
              <th>Name</th>
              <th>Part No</th>
              <th>Main Price</th>
              <th>Discount %</th>
              <th>Labour</th>
              <th>Wire</th>
              <th>Price (Total)</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal-backdrop" id="modalBackdrop">
  <div class="modal" role="dialog" aria-modal="true">
    <h3 id="modalTitle">Add product</h3>
    <div style="margin-top:8px">
      <div class="form-row">
        <input id="fCategory" placeholder="Category">
        <input id="fName" placeholder="Product name">
      </div>
      <div class="form-row">
        <input id="fPartNo" placeholder="Part No">
        <input id="fMainPrice" placeholder="Main price" type="number">
      </div>
      <div class="form-row">
        <input id="fDiscountPercent" placeholder="Discount percent" type="number">
        <input id="fLabourCharges" placeholder="Labour charges" type="number">
      </div>
      <div class="form-row">
        <input id="fWireCost" placeholder="Wire cost" type="number">
        <input id="fExtras" placeholder="Extras (optional)">
      </div>

      <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px">
        <button id="saveBtn">Save</button>
        <button class="ghost" id="cancelBtn">Cancel</button>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
// Expose CSRF token from PHP
window.CSRF_TOKEN = <?= json_encode($csrf) ?>;

const API_BASE = '/get-quote/public/admin/api/products';
let products = []; // flat array
let grouped = {}; // category -> [items]
let currentCategory = null;

const $categories = document.getElementById('categories');
const $tbody = document.querySelector('#productsTable tbody');
const $btnAdd = document.getElementById('btnAdd');
const $btnRefresh = document.getElementById('btnRefresh');
const $modal = document.getElementById('modalBackdrop');
const $modalTitle = document.getElementById('modalTitle');
const $saveBtn = document.getElementById('saveBtn');
const $cancelBtn = document.getElementById('cancelBtn');
const toast = document.getElementById('toast');

// form fields
const fCategory = document.getElementById('fCategory');
const fName = document.getElementById('fName');
const fPartNo = document.getElementById('fPartNo');
const fMainPrice = document.getElementById('fMainPrice');
const fDiscountPercent = document.getElementById('fDiscountPercent');
const fLabourCharges = document.getElementById('fLabourCharges');
const fWireCost = document.getElementById('fWireCost');
const fExtras = document.getElementById('fExtras');

let editingId = null; // for edit modal

// Toast helper
function showToast(msg, timeout=2500){
  toast.textContent = msg; toast.style.display = 'block';
  setTimeout(()=> toast.style.display='none', timeout);
}

// Load products
async function loadProducts(){
  try{
    const res = await fetch(API_BASE + '/getProducts.php', {credentials:'same-origin'});
    const data = await res.json();

    if (!Array.isArray(data)) {
      console.error('Expected array from getProducts.php', data);
      showToast('Failed to load products (unexpected format)');
      return;
    }

    products = data;
    groupProducts();
    renderCategories();
    renderTable();
  }catch(err){
    console.error(err);
    showToast('Failed to fetch products');
  }
}

function groupProducts(){
  grouped = {};
  for(const p of products){
    const cat = p.category || 'Uncategorized';
    if (!grouped[cat]) grouped[cat] = [];
    grouped[cat].push(p);
  }
}

function renderCategories(){
  $categories.innerHTML = '';
  const allBtn = document.createElement('div');
  allBtn.className = 'cat' + (currentCategory===null? ' active':'');
  allBtn.textContent = 'All (' + products.length + ')';
  allBtn.onclick = ()=>{ currentCategory = null; renderCategories(); renderTable(); };
  $categories.appendChild(allBtn);

  const keys = Object.keys(grouped).sort();
  for(const k of keys){
    const el = document.createElement('div');
    el.className = 'cat' + (currentCategory===k? ' active':'');
    el.textContent = `${k} (${grouped[k].length})`;
    el.onclick = ()=>{ currentCategory = k; renderCategories(); renderTable(); };
    $categories.appendChild(el);
  }
}

function renderTable(){
  $tbody.innerHTML = '';
  const list = currentCategory ? grouped[currentCategory] : products;
  for(const p of list){
    const tr = document.createElement('tr');
    tr.dataset.id = p.id;

    tr.innerHTML = `
      <td>${escapeHtml(p.category)}</td>
      <td class="editable" data-field="name">${escapeHtml(p.name)}</td>
      <td class="editable" data-field="partNo">${escapeHtml(p.partNo||'')}</td>
      <td class="editable value" data-field="mainPrice">${formatNumber(p.mainPrice)}</td>
      <td class="editable value" data-field="discountPercent">${formatNumber(p.discountPercent)}</td>
      <td class="editable value" data-field="labourCharges">${formatNumber(p.labourCharges||0)}</td>
      <td class="editable value" data-field="wireCost">${formatNumber(p.wireCost||0)}</td>
      <td>${formatNumber(p.price)}</td>
      <td>
        <button class="ghost" data-action="edit">Edit</button>
        <button data-action="delete">Delete</button>
      </td>
    `;

    // attach events
    tr.querySelectorAll('.editable').forEach(td=>{
      td.addEventListener('dblclick', ()=> startInlineEdit(td, p));
    });

    tr.querySelector('[data-action="edit"]').addEventListener('click', ()=> openEditModal(p));
    tr.querySelector('[data-action="delete"]').addEventListener('click', ()=> confirmDelete(p));

    $tbody.appendChild(tr);
  }
}

// Inline edit cell
function startInlineEdit(td, product){
  if (td.classList.contains('editing')) return;
  td.classList.add('editing');
  const orig = td.textContent.trim();
  const field = td.dataset.field;
  const input = document.createElement('input');
  input.value = orig;
  input.style.width = '100%';
  td.innerHTML = '';
  td.appendChild(input);
  input.focus();

  input.addEventListener('blur', async ()=>{
    const val = input.value.trim();
    td.classList.remove('editing');
    td.textContent = val;
    if (val == orig) return; // no change
    // call update API
    await updateField(product.id, field, val);
  });

  input.addEventListener('keydown', (e)=>{
    if (e.key === 'Enter') input.blur();
    if (e.key === 'Escape') { td.classList.remove('editing'); td.textContent = orig; }
  });
}

async function updateField(id, field, value){
  // convert UI camelCase keys to API expected ones: partNo -> part_no etc.
  const map = { partNo:'partNo', mainPrice:'mainPrice', discountPercent:'discountPercent', labourCharges:'labourCharges', wireCost:'wireCost' };
  const payload = { id, field, value, csrf: window.CSRF_TOKEN };

  try{
    const res = await fetch(API_BASE + '/updateProduct.php', {
      method:'POST',
      credentials:'same-origin',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    });
    const j = await res.json();
    if (j.success){
      showToast('Saved');
      await loadProducts();
    } else {
      showToast('Save failed: ' + (j.error || 'unknown'));
      console.error(j);
    }
  }catch(err){ console.error(err); showToast('Save failed'); }
}

function openEditModal(p){
  editingId = p.id;
  $modalTitle.textContent = 'Edit product';
  fCategory.value = p.category || '';
  fName.value = p.name || '';
  fPartNo.value = p.partNo || '';
  fMainPrice.value = p.mainPrice || 0;
  fDiscountPercent.value = p.discountPercent || 0;
  fLabourCharges.value = p.labourCharges || 0;
  fWireCost.value = p.wireCost || 0;
  fExtras.value = p.extras || '';
  $modal.style.display = 'flex';
}

function openAddModal(){
  editingId = null;
  $modalTitle.textContent = 'Add product';
  fCategory.value = '';
  fName.value = '';
  fPartNo.value = '';
  fMainPrice.value = '';
  fDiscountPercent.value = '';
  fLabourCharges.value = '';
  fWireCost.value = '';
  fExtras.value = '';
  $modal.style.display = 'flex';
}

async function submitForm(){
  const payload = {
    csrf: window.CSRF_TOKEN,
    category: fCategory.value.trim(),
    name: fName.value.trim(),
    partNo: fPartNo.value.trim(),
    mainPrice: parseFloat(fMainPrice.value) || 0,
    discountPercent: parseFloat(fDiscountPercent.value) || 0,
    labourCharges: parseFloat(fLabourCharges.value) || 0,
    wireCost: parseFloat(fWireCost.value) || 0,
    extras: fExtras.value.trim() || ''
  };

  try{
    let url = API_BASE + '/addProduct.php';
    if (editingId) { // full update
      payload.id = editingId;
      // api accepts snake_case keys too — convert if needed
      payload.main_price = payload.mainPrice; delete payload.mainPrice;
      payload.discount_percent = payload.discountPercent; delete payload.discountPercent;
      payload.labour_charges = payload.labourCharges; delete payload.labourCharges;
      payload.wire_cost = payload.wireCost; delete payload.wireCost;
      url = API_BASE + '/updateProduct.php';
    }

    const res = await fetch(url, {
      method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    });

    const j = await res.json();
    if (j.success){
      showToast(editingId ? 'Updated' : 'Added');
      closeModal();
      await loadProducts();
    } else {
      showToast('Failed: ' + (j.error || 'unknown'));
      console.error(j);
    }
  }catch(err){ console.error(err); showToast('Request failed'); }
}

function closeModal(){ $modal.style.display='none'; }

function confirmDelete(p){
  if (!confirm('Delete product "' + p.name + '"?')) return;
  doDelete(p.id);
}

async function doDelete(id){
  try{
    const res = await fetch(API_BASE + '/deleteProduct.php', {
      method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ id, csrf: window.CSRF_TOKEN })
    });
    const j = await res.json();
    if (j.success){ showToast('Deleted'); await loadProducts(); }
    else { showToast('Delete failed: ' + (j.error||'unknown')); }
  }catch(err){ console.error(err); showToast('Delete failed'); }
}

// helpers
function escapeHtml(s){ if (!s && s!==0) return ''; return String(s).replace(/[&<>"]+/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[c]||c)); }
function formatNumber(v){ return (typeof v === 'number' ? v : parseFloat(v||0)).toFixed(2); }

// events
$btnAdd.addEventListener('click', openAddModal);
$btnRefresh.addEventListener('click', loadProducts);
$cancelBtn.addEventListener('click', closeModal);
$saveBtn.addEventListener('click', submitForm);

// initial load
loadProducts();
</script>
</body>
</html>
