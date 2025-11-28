<?php
// /public/admin/products/index.php
session_start();

// Admin auth
// Admin auth (adjust to your project)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header('Location: /get-quote/public/admin/views/index.php');
  exit;
}

// Show PHP errors temporarily if needed (disable on production)
// ini_set('display_errors',1); error_reporting(E_ALL);

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
    :root {
      --bg: #f6f8fa;
      --card: #fff;
      --muted: #6b7280;
      --accent: #0ea5a4
    }

    body {
      font-family: Inter, Arial, Helvetica, sans-serif;
      margin: 0;
      background: var(--bg);
      color: #111
    }

    .wrap {
      max-width: 1200px;
      margin: 28px auto;
      padding: 18px
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 12px
    }

    .title {
      font-size: 20px;
      font-weight: 600
    }

    .grid {
      display: grid;
      grid-template-columns: 260px 1fr;
      gap: 18px
    }

    .card {
      background: var(--card);
      border-radius: 8px;
      padding: 12px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, .06)
    }

    .categories {
      max-height: 70vh;
      overflow: auto
    }

    .cat {
      padding: 8px;
      border-radius: 6px;
      cursor: pointer;
      margin-bottom: 6px
    }

    .cat.active {
      background: linear-gradient(90deg, #e6fffb, #f0fdfa)
    }

    .actions {
      display: flex;
      gap: 8px
    }

    button {
      background: var(--accent);
      color: #fff;
      border: 0;
      padding: 8px 12px;
      border-radius: 6px;
      cursor: pointer
    }

    button.ghost {
      background: transparent;
      color: var(--accent);
      border: 1px solid rgba(14, 165, 164, .15)
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px
    }

    th,
    td {
      padding: 10px 12px;
      border-bottom: 1px solid #eef2f7;
      text-align: left
    }

    th {
      font-size: 13px;
      color: var(--muted);
      font-weight: 600
    }

    td.value {
      min-width: 70px
    }

    tr:hover {
      background: #fbfcfd
    }

    .action-icon {
      cursor: pointer;
      padding: 6px;
      border-radius: 6px;
      margin-left: 6px;
      font-size: 14px
    }

    .action-icon:hover {
      background: #f3faf9
    }

    .action-icon.delete:hover {
      background: #fff0f0
    }

    .tabs {
      display: flex;
      gap: 8px;
      margin-bottom: 12px
    }

    .tab {
      padding: 8px 12px;
      border-radius: 8px;
      cursor: pointer;
      background: transparent;
      border: 1px solid #eef2f7
    }

    .tab.active {
      background: #fff;
      border-color: #e6fffb;
      box-shadow: 0 1px 2px rgba(0, 0, 0, .03)
    }

    .modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .35);
      display: none;
      align-items: center;
      justify-content: center
    }

    .modal {
      width: 680px;
      background: var(--card);
      padding: 18px;
      border-radius: 8px
    }

    .form-row {
      display: flex;
      gap: 8px;
      margin-bottom: 8px
    }

    .form-row input,
    .form-row select {
      flex: 1;
      padding: 8px;
      border: 1px solid #e6eef3;
      border-radius: 6px
    }

    .toast {
      position: fixed;
      right: 20px;
      top: 20px;
      background: #111;
      color: #fff;
      padding: 10px 14px;
      border-radius: 6px;
      display: none
    }

    .muted {
      color: var(--muted);
      font-size: 13px
    }

    .small {
      font-size: 13px
    }

    .compact th,
    .compact td {
      padding: 8px 10px
    }

    .id-col {
      width: 60px
    }

    .actions-col {
      width: 120px;
      text-align: right
    }

    .action-icon {
      cursor: pointer;
      padding: 6px;
      border-radius: 6px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-left: 6px;
      color: #0ea5a4;
      transition: 0.2s;
    }

    .action-icon:hover {
      background: #e6fffb;
    }

    .action-icon.delete:hover {
      background: #fff0f0;
      color: #dc2626;
    }

    .action-icon.restore:hover {
      background: #f0fdf4;
      color: #16a34a;
    }

    .action-icon.permanent:hover {
      background: #fef2f2;
      color: #b91c1c;
    }
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
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <strong>Categories</strong>
          <div class="muted small" id="trashCount">Trash: 0</div>
        </div>
        <div class="tabs" style="margin-bottom:10px">
          <div class="tab active" data-tab="main" id="tabMain">Products</div>
          <div class="tab" data-tab="trash" id="tabTrash">Trash</div>
        </div>
        <div id="categories" class="categories"></div>
      </div>

      <div class="card">
        <div style="overflow:auto;max-height:72vh">
          <table id="productsTable" class="compact">
            <thead>
              <tr>
                <th class="id-col">ID</th>
                <th>Ctg</th>
                <th>Name</th>
                <th>Part</th>
                <th>Main</th>
                <th>Dis%</th>
                <th>Lab</th>
                <th>Wire</th>
                <th>Price</th>
                <th class="actions-col">Actions</th>
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
    window.CSRF_TOKEN = <?= json_encode($csrf) ?>;
    const API_BASE = '/get-quote/public/admin/api/products';
    let products = []; // active
    let trash = [];    // trashed
    let grouped = {};
    let currentCategory = null;
    let activeTab = 'main';
    let editingId = null;

    const $categories = document.getElementById('categories');
    const $tbody = document.querySelector('#productsTable tbody');
    const $btnAdd = document.getElementById('btnAdd');
    const $btnRefresh = document.getElementById('btnRefresh');
    const $modal = document.getElementById('modalBackdrop');
    const $modalTitle = document.getElementById('modalTitle');
    const $saveBtn = document.getElementById('saveBtn');
    const $cancelBtn = document.getElementById('cancelBtn');
    const toast = document.getElementById('toast');
    const tabMain = document.getElementById('tabMain');
    const tabTrash = document.getElementById('tabTrash');
    const trashCount = document.getElementById('trashCount');

    const fCategory = document.getElementById('fCategory');
    const fName = document.getElementById('fName');
    const fPartNo = document.getElementById('fPartNo');
    const fMainPrice = document.getElementById('fMainPrice');
    const fDiscountPercent = document.getElementById('fDiscountPercent');
    const fLabourCharges = document.getElementById('fLabourCharges');
    const fWireCost = document.getElementById('fWireCost');
    const fExtras = document.getElementById('fExtras');

    function showToast(msg, timeout = 2200) {
      toast.textContent = msg; toast.style.display = 'block';
      setTimeout(() => toast.style.display = 'none', timeout);
    }

    async function loadProducts() {
      try {
        const res = await fetch(API_BASE + '/getProducts.php', { credentials: 'same-origin', cache: 'no-store' });
        const data = await res.json();
        if (!Array.isArray(data)) { console.error('Bad products JSON', data); showToast('Failed to load'); return; }
        products = data;
        groupProducts();
        renderCategories();
        if (activeTab === 'main') renderTable();
        updateTrashCount();
      } catch (err) { console.error(err); showToast('Failed to fetch products'); }
    }

    async function loadTrash() {
      try {
        const res = await fetch(API_BASE + '/getTrash.php', { credentials: 'same-origin', cache: 'no-store' });
        const data = await res.json();
        if (!Array.isArray(data)) { console.error('Bad trash JSON', data); showToast('Failed to load trash'); return; }
        trash = data;
        if (activeTab === 'trash') renderTrashTable();
        updateTrashCount();
      } catch (err) { console.error(err); showToast('Failed to fetch trash'); }
    }

    function groupProducts() {
      grouped = {};
      for (const p of products) {
        const cat = p.category || 'Uncategorized';
        if (!grouped[cat]) grouped[cat] = [];
        grouped[cat].push(p);
      }
    }

    function renderCategories() {
      $categories.innerHTML = '';
      const allBtn = document.createElement('div');
      allBtn.className = 'cat' + (currentCategory === null ? ' active' : '');
      allBtn.textContent = 'All (' + products.length + ')';
      allBtn.onclick = () => { currentCategory = null; renderCategories(); renderTable(); };
      $categories.appendChild(allBtn);

      const keys = Object.keys(grouped).sort();
      for (const k of keys) {
        const el = document.createElement('div');
        el.className = 'cat' + (currentCategory === k ? ' active' : '');
        el.textContent = `${k} (${grouped[k].length})`;
        el.onclick = () => { currentCategory = k; renderCategories(); renderTable(); };
        $categories.appendChild(el);
      }
    }

    function renderTable() {
      $tbody.innerHTML = '';
      const list = currentCategory ? grouped[currentCategory] : products;
      for (const p of list) {
        const tr = document.createElement('tr');
        tr.dataset.id = p.id;
        tr.innerHTML = `
      <td class="small">${p.id}</td>
      <td class="small">${escapeHtml(p.category)}</td>
      <td>${escapeHtml(p.name)}</td>
      <td class="small">${escapeHtml(p.partNo || '')}</td>
      <td class="small">${formatNumber(p.mainPrice)}</td>
      <td class="small">${formatNumber(p.discountPercent)}</td>
      <td class="small">${formatNumber(p.labourCharges || 0)}</td>
      <td class="small">${formatNumber(p.wireCost || 0)}</td>
      <td class="small">${formatNumber(p.price)}</td>
      <td class="small actions-col">
        <span class="action-icon edit" data-action="edit" title="Edit">
  <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
    <path d="M12 20h9" />
    <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
  </svg>
</span>

<span class="action-icon delete" data-action="delete" title="Move to Trash">
  <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
    <polyline points="3 6 5 6 21 6" />
    <path d="M19 6l-1 14H6L5 6" />
    <path d="M10 11v6" />
    <path d="M14 11v6" />
    <path d="M9 6V4h6v2" />
  </svg>
</span>

      </td>
    `;
        tr.querySelector('[data-action="edit"]').addEventListener('click', () => openEditModal(p));
        tr.querySelector('[data-action="delete"]').addEventListener('click', () => moveToTrashConfirm(p));
        $tbody.appendChild(tr);
      }
    }

    function renderTrashTable() {
      $tbody.innerHTML = '';
      for (const p of trash) {
        const tr = document.createElement('tr');
        tr.dataset.id = p.id;
        tr.innerHTML = `
      <td class="small">${p.id}</td>
      <td class="small">${escapeHtml(p.category)}</td>
      <td>${escapeHtml(p.name)}</td>
      <td class="small">${escapeHtml(p.partNo || '')}</td>
      <td class="small">${formatNumber(p.mainPrice)}</td>
      <td class="small">${formatNumber(p.discountPercent)}</td>
      <td class="small">${formatNumber(p.labourCharges || 0)}</td>
      <td class="small">${formatNumber(p.wireCost || 0)}</td>
      <td class="small">${formatNumber(p.price)}</td>
      <td class="small actions-col">
        <span class="action-icon restore" data-action="restore" title="Restore">
          <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
            <path d="M3 10v5h5" />
            <path d="M3.34 15A9 9 0 1 0 5 5.34L3 7" />
          </svg>
        </span>
        <span class="action-icon delete permanent" data-action="delete" title="Delete Permanently">
          <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
            <polyline points="3 6 5 6 21 6" />
            <path d="M19 6l-1 14H6L5 6" />
            <path d="M9 6V4h6v2" />
            <line x1="10" y1="11" x2="14" y2="15" />
            <line x1="14" y1="11" x2="10" y2="15" />
          </svg>
        </span>
      </td>
    `;
        tr.querySelector('[data-action="restore"]').addEventListener('click', () => restoreConfirm(p));
        tr.querySelector('[data-action="delete"]').addEventListener('click', () => deletePermanentConfirm(p));
        $tbody.appendChild(tr);
      }
    }

    function updateTrashCount() {
      trashCount.textContent = `Trash: ${trash.length}`;
    }

    // modal
    function openAddModal() { editingId = null; $modalTitle.textContent = 'Add product'; fCategory.value = ''; fName.value = ''; fPartNo.value = ''; fMainPrice.value = ''; fDiscountPercent.value = ''; fLabourCharges.value = ''; fWireCost.value = ''; fExtras.value = ''; $modal.style.display = 'flex'; }
    function openEditModal(p) { editingId = p.id; $modalTitle.textContent = 'Edit product'; fCategory.value = p.category || ''; fName.value = p.name || ''; fPartNo.value = p.partNo || ''; fMainPrice.value = p.mainPrice || 0; fDiscountPercent.value = p.discountPercent || 0; fLabourCharges.value = p.labourCharges || 0; fWireCost.value = p.wireCost || 0; fExtras.value = p.extras || ''; $modal.style.display = 'flex'; }
    function closeModal() { $modal.style.display = 'none'; }

    async function submitForm() {
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

      try {
        let url = API_BASE + '/addProduct.php';
        if (editingId) {
          payload.id = editingId;
          // convert to snake_case accepted by API
          payload.main_price = payload.mainPrice; delete payload.mainPrice;
          payload.discount_percent = payload.discountPercent; delete payload.discountPercent;
          payload.labour_charges = payload.labourCharges; delete payload.labourCharges;
          payload.wire_cost = payload.wireCost; delete payload.wireCost;
          url = API_BASE + '/updateProduct.php';
        }

        const res = await fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const j = await res.json();
        if (j.success) { showToast(editingId ? 'Updated' : 'Added'); closeModal(); await reloadAll(); }
        else { showToast('Failed: ' + (j.error || 'unknown')); console.error(j); }
      } catch (err) { console.error(err); showToast('Request failed'); }
    }

    async function moveToTrashConfirm(p) {
      if (!confirm(`Move to trash: "${p.name}"?`)) return;
      try {
        const res = await fetch(API_BASE + '/deleteProduct.php', {
          method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: p.id, csrf: window.CSRF_TOKEN })
        });
        const j = await res.json();
        if (j.success) { showToast('Moved to trash'); await reloadAll(); }
        else { showToast('Trash failed: ' + (j.error || 'unknown')); console.error(j); }
      } catch (err) { console.error(err); showToast('Trash failed'); }
    }

    async function restoreConfirm(p) {
      if (!confirm(`Restore product: "${p.name}"?`)) return;
      try {
        const res = await fetch(API_BASE + '/restoreProduct.php', {
          method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: p.id, csrf: window.CSRF_TOKEN })
        });
        const j = await res.json();
        if (j.success) { showToast('Restored'); await reloadAll(); }
        else { showToast('Restore failed: ' + (j.error || 'unknown')); console.error(j); }
      } catch (err) { console.error(err); showToast('Restore failed'); }
    }

    async function deletePermanentConfirm(p) {
      if (!confirm(`Permanently delete: "${p.name}"? This action cannot be undone.`)) return;
      try {
        const res = await fetch(API_BASE + '/deletePermanent.php', {
          method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: p.id, csrf: window.CSRF_TOKEN })
        });
        const j = await res.json();
        if (j.success) { showToast('Deleted permanently'); await reloadAll(); }
        else { showToast('Delete failed: ' + (j.error || 'unknown')); console.error(j); }
      } catch (err) { console.error(err); showToast('Delete failed'); }
    }

    async function reloadAll() { await Promise.all([loadProducts(), loadTrash()]); if (activeTab === 'main') renderTable(); else renderTrashTable(); }

    function escapeHtml(s) { if (s === null || s === undefined) return ''; return String(s).replace(/[&<>\"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c] || c)); }
    function formatNumber(v) { return (typeof v === 'number' ? v : parseFloat(v || 0)).toFixed(2); }

    // tab handling
    tabMain.addEventListener('click', () => { activeTab = 'main'; tabMain.classList.add('active'); tabTrash.classList.remove('active'); renderTable(); });
    tabTrash.addEventListener('click', () => { activeTab = 'trash'; tabTrash.classList.add('active'); tabMain.classList.remove('active'); renderTrashTable(); });

    $btnAdd.addEventListener('click', openAddModal);
    $btnRefresh.addEventListener('click', reloadAll);
    $cancelBtn.addEventListener('click', closeModal);
    $saveBtn.addEventListener('click', submitForm);

    // initial
    reloadAll();
  </script>
</body>

</html>