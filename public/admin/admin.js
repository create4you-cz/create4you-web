// Administrace create4you.cz
import { createApi } from './api.js';

const CATEGORIES = { web: 'Weby na míru', app: 'Aplikace & CRM', eshop: 'E-shopy', sablona: 'Šablonové weby' };
const STATUSES = { done: 'Dokončeno', progress: 'Právě děláme' };
const INQ = { new: 'Nová', progress: 'Řeší se', won: 'Zakázka', closed: 'Uzavřeno' };
const SETTINGS = {
  Kontakt: { contact_name: 'Jméno', contact_email: 'E-mail', contact_phone: 'Telefon', contact_city: 'Město / působnost' },
  'Fakturační údaje (patička)': { company_name: 'Obchodní jméno', company_ico: 'IČO', company_dic: 'DIČ (prázdné, pokud nejste plátce DPH)', company_addr: 'Sídlo', company_note: 'Poznámka k zápisu' },
  'Čísla v úvodu webu': { stat_projects: 'Počet projektů', stat_years: 'Let zkušeností (prázdné = od roku 2019)', stat_response: 'Odpověď do (hodin)' },
  Ceník: { price_template: 'Šablonový web – Kč / měsíc', price_template_note: 'Šablonový web – doplňující text', price_custom: 'Web na míru – cena', price_app: 'Aplikace & CRM – cena', price_eshop: 'E-shop – cena' },
  'Sociální sítě': { social_facebook: 'Facebook URL', social_instagram: 'Instagram URL', social_linkedin: 'LinkedIn URL' },
};
const ICONS = {
  prehled: 'M3 12l9-8 9 8M5 10v10h14V10',
  reference: 'M3 7h18M3 12h18M3 17h12',
  hodnoceni: 'M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z',
  poptavky: 'M4 6h16v12H4zM4 6l8 7 8-7',
  nastaveni: 'M12 15a3 3 0 100-6 3 3 0 000 6zM19.4 15a1.7 1.7 0 00.3 1.8l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.8-.3 1.7 1.7 0 00-1 1.5V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1.1-1.5 1.7 1.7 0 00-1.8.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.8 1.7 1.7 0 00-1.5-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.5-1.1 1.7 1.7 0 00-.3-1.8l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.8.3H9a1.7 1.7 0 001-1.5V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.8-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.8V9a1.7 1.7 0 001.5 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.5 1z',
};

const app = document.getElementById('app');
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[c]);
const fmtDate = (iso) => iso ? new Date(iso).toLocaleString('cs-CZ', { day: 'numeric', month: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';
const imgSrc = (u) => !u ? '' : /^(https?:|data:)/.test(u) ? u : '..' + (u.startsWith('/') ? u : '/' + u);
let api;
let newInquiries = 0;

/* ---------- oznámení a zveřejnění změn ---------- */
function toast(msg, type = 'ok') {
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.textContent = msg;
  document.body.append(t);
  setTimeout(() => t.classList.add('out'), 4200);
  setTimeout(() => t.remove(), 4800);
}

let publishTimer = null;
/** Po změně obsahu webu počká chvíli na další úpravy a pak spustí aktualizaci webu */
function changed(msg = 'Uloženo.') {
  toast(msg);
  clearTimeout(publishTimer);
  setPublishState('pending');
  publishTimer = setTimeout(publishNow, 6000);
}
async function publishNow() {
  clearTimeout(publishTimer);
  setPublishState('busy');
  try {
    toast(await api.publish());
    setPublishState('done');
  } catch (err) {
    toast(err.message, 'err');
    setPublishState('pending');
  }
}
function setPublishState(state) {
  const el = document.querySelector('.publish');
  if (!el) return;
  el.dataset.state = state;
  el.querySelector('span').textContent = { pending: 'Čeká na zveřejnění…', busy: 'Web se aktualizuje…', done: 'Web je aktuální', idle: 'Web je aktuální' }[state];
}

/* ---------- rozvržení ---------- */
function shell(active, title, html) {
  const nav = [['prehled', 'Přehled'], ['reference', 'Reference'], ['hodnoceni', 'Hodnocení'], ['poptavky', 'Poptávky'], ['nastaveni', 'Nastavení']];
  app.innerHTML = `
  ${api.demo ? '<div class="demo-bar">Ukázkový režim – změny se ukládají jen ve vašem prohlížeči. Po propojení se Supabase se tu budou spravovat skutečná data webu.</div>' : ''}
  <div class="shell">
    <aside class="side">
      <a class="brand" href="#prehled"><span class="logo-dot"></span><span>create<b>4</b>you</span> <small>admin</small></a>
      <nav>${nav.map(([id, label]) => `<a href="#${id}" class="${active === id ? 'on' : ''}">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="${ICONS[id]}"/></svg>
        ${label}${id === 'poptavky' && newInquiries ? `<span class="pill">${newInquiries}</span>` : ''}</a>`).join('')}</nav>
      <div class="side-foot">
        <button class="publish" type="button" data-state="idle" title="Zveřejnit změny na webu teď"><i></i><span>Web je aktuální</span></button>
        <a href="../" target="_blank">↗ Zobrazit web</a>
        <a href="#odhlasit">Odhlásit se</a>
      </div>
    </aside>
    <main class="main"><header class="top"><h1>${esc(title)}</h1></header>${html}</main>
  </div>`;
  app.querySelector('.publish').addEventListener('click', publishNow);
  if (publishTimer) setPublishState('pending');
}

/* ---------- přihlášení ---------- */
function loginView(error = '') {
  app.innerHTML = `
  <div class="auth">
    <form class="auth-card" id="login">
      <a class="brand" href="../"><span class="logo-dot"></span><span>create<b>4</b>you</span></a>
      <h1>Přihlášení do administrace</h1>
      ${api.demo ? '<p class="muted">Ukázkový režim: zadejte libovolný e-mail a heslo.</p>' : ''}
      ${error ? `<div class="flash err">${esc(error)}</div>` : ''}
      <label>E-mail<input type="email" name="email" required autofocus autocomplete="username"></label>
      <label>Heslo<input type="password" name="password" required autocomplete="current-password"></label>
      <button class="btn primary wide">Přihlásit se</button>
    </form>
  </div>`;
  app.querySelector('#login').addEventListener('submit', async (e) => {
    e.preventDefault();
    const f = e.target;
    f.querySelector('button').disabled = true;
    try {
      await api.signIn(f.email.value.trim(), f.password.value);
      route();
    } catch (err) {
      loginView(err.message);
    }
  });
}

/* ---------- přehled ---------- */
async function dashboardView() {
  const [projects, reviews, inquiries] = await Promise.all([api.listProjects(), api.listReviews(), api.listInquiries()]);
  newInquiries = inquiries.filter((q) => q.status === 'new').length;
  const demoReviews = reviews.filter((r) => r.is_demo && r.visible).length;
  const noCover = projects.filter((p) => p.visible && !p.cover).length;
  shell('prehled', 'Přehled', `
    <div class="stats">
      <div class="card stat"><span>Reference</span><b>${projects.length}</b></div>
      <div class="card stat"><span>Hodnocení</span><b>${reviews.filter((r) => r.visible).length}</b></div>
      <div class="card stat"><span>Nové poptávky</span><b>${newInquiries}</b></div>
      <div class="card stat"><span>Poptávek celkem</span><b>${inquiries.length}</b></div>
    </div>
    ${demoReviews || noCover ? `<div class="card todo"><h2>K dokončení</h2><ul>
      ${demoReviews ? `<li>Na webu je <b>${demoReviews}</b> ukázkových hodnocení. <a href="#hodnoceni">Nahraďte je skutečnými</a>.</li>` : ''}
      ${noCover ? `<li><b>${noCover}</b> referencí nemá screenshot. <a href="#reference">Nahrát</a></li>` : ''}
    </ul></div>` : ''}
    <div class="card">
      <div class="card-head"><h2>Poslední poptávky</h2><a class="btn" href="#poptavky">Všechny poptávky</a></div>
      ${inquiries.length ? inquiryTable(inquiries.slice(0, 6)) : '<p class="muted">Zatím žádné poptávky. Jakmile někdo vyplní formulář na webu, objeví se tady.</p>'}
    </div>
    <div class="quick">
      <a class="card quick-link" href="#reference/nova">＋ Přidat referenci</a>
      <a class="card quick-link" href="#hodnoceni/nove">＋ Přidat hodnocení</a>
    </div>`);
}

/* ---------- reference ---------- */
async function projectsView() {
  const rows = await api.listProjects();
  shell('reference', 'Reference', `
    <div class="card">
      <div class="card-head"><p class="muted">Přetažením řádků změníte pořadí na webu. ★ = zvýrazněná reference (velká dlaždice).</p><a class="btn primary" href="#reference/nova">＋ Nová reference</a></div>
      <table class="table sortable" data-table="projects">
        <thead><tr><th></th><th>Náhled</th><th>Název</th><th>Kategorie</th><th>Stav</th><th>Fotky</th><th>Na webu</th><th></th></tr></thead>
        <tbody>${rows.map((p) => `
          <tr data-id="${p.id}" draggable="true">
            <td class="grip" title="Přetáhnout">⋮⋮</td>
            <td>${p.cover ? `<img class="thumb" src="${esc(imgSrc(p.cover))}" alt="">` : `<span class="thumb ph" style="--c:${esc(p.accent)}">${esc(p.title[0] || '')}</span>`}</td>
            <td><a href="#reference/${p.id}"><b>${esc(p.title)}</b></a>${p.featured ? ' <span title="Zvýrazněná">★</span>' : ''}<br><small class="muted">${esc(p.url || p.client)}</small></td>
            <td>${esc(CATEGORIES[p.category] || p.category)}</td>
            <td><span class="badge ${esc(p.status)}">${esc(STATUSES[p.status] || '')}</span></td>
            <td>${(p.images?.length || 0) + (p.cover ? 1 : 0) + (p.cover_mobile ? 1 : 0)}</td>
            <td><button class="switch ${p.visible ? 'on' : ''}" data-toggle="${p.id}" title="Zobrazit / skrýt"><span></span></button></td>
            <td class="actions"><a class="btn sm" href="#reference/${p.id}">Upravit</a><button class="btn sm danger" data-delete="${p.id}">Smazat</button></td>
          </tr>`).join('')}</tbody>
      </table>
    </div>`);
  bindSortable('projects');
  app.querySelectorAll('[data-toggle]').forEach((b) => b.addEventListener('click', async () => {
    const p = rows.find((x) => x.id === Number(b.dataset.toggle));
    await api.setProject(p.id, { visible: !p.visible });
    changed(p.visible ? 'Reference skryta.' : 'Reference zobrazena.');
    projectsView();
  }));
  app.querySelectorAll('[data-delete]').forEach((b) => b.addEventListener('click', () => confirmBox(b, 'Smazat referenci včetně fotek?', async () => {
    const p = await api.getProject(b.dataset.delete);
    await api.deleteProject(p.id);
    await Promise.all([p.cover, p.cover_mobile, ...p.images.map((i) => i.file)].map((u) => api.removeFile(u).catch(() => {})));
    changed('Reference smazána.');
    projectsView();
  })));
}

async function projectEditView(id) {
  const isNew = id === 'nova';
  const p = isNew
    ? { title: '', slug: '', category: 'web', client: '', url: '', year: String(new Date().getFullYear()), status: 'done', excerpt: '', body: '', tags: '', links: '', accent: '#7c5cff', cover: '', cover_w: null, cover_h: null, cover_mobile: '', mobile_w: null, mobile_h: null, mobile_top: '', featured: false, visible: true, images: [] }
    : await api.getProject(id);
  if (!p) return notFound();
  const opt = (map, cur) => Object.entries(map).map(([k, v]) => `<option value="${k}" ${k === cur ? 'selected' : ''}>${esc(v)}</option>`).join('');
  shell('reference', isNew ? 'Nová reference' : 'Upravit referenci', `
    <form class="grid-form" id="pform">
      <div class="col-main">
        <div class="card">
          <label>Název *<input name="title" required value="${esc(p.title)}" placeholder="např. AccuWise – firemní web"></label>
          <div class="row2">
            <label>Klient<input name="client" value="${esc(p.client)}" placeholder="Název firmy"></label>
            <label>Odkaz na web / aplikaci<input name="url" value="${esc(p.url)}" placeholder="https://"></label>
          </div>
          <label>Krátký popis (karta v referencích)<textarea name="excerpt" rows="2" maxlength="240">${esc(p.excerpt)}</textarea></label>
          <label>Detailní popis (stránka reference)<textarea name="body" rows="9" placeholder="Co klient potřeboval, co jsme udělali, výsledek… Prázdný řádek = nový odstavec.">${esc(p.body)}</textarea></label>
          <label>Další odkazy – např. jazykové verze (jeden na řádek: Popisek | https://…)<textarea name="links" rows="3" placeholder="Slovenčina | https://vasweb.cz/sk">${esc(p.links)}</textarea></label>
          <label>Štítky (oddělené čárkou)<input name="tags" value="${esc(p.tags)}" placeholder="Web na míru, E-maily, Administrace"></label>
        </div>
        <div class="card" id="galerie">
          <h2>Galerie</h2>
          ${isNew ? '<p class="muted">Galerii můžete doplnit po prvním uložení reference.</p>' : `
          <div class="gallery-admin">${p.images.map((img) => `
            <figure data-img="${img.id}">
              <img src="${esc(imgSrc(img.file))}" alt="">
              <input data-caption="${img.id}" value="${esc(img.caption)}" placeholder="Popisek">
              <button type="button" class="btn sm danger" data-del-img="${img.id}">Odstranit</button>
            </figure>`).join('')}</div>
          <label class="drop"><input type="file" accept="image/*" multiple data-upload="gallery"><span>Přetáhněte sem obrázky nebo klikněte pro výběr<br><small>automaticky se zmenší a převedou do WebP</small></span></label>`}
        </div>
      </div>
      <div class="col-side">
        <div class="card">
          <label class="check"><input type="checkbox" name="visible" ${p.visible ? 'checked' : ''}> Zobrazit na webu</label>
          <label class="check"><input type="checkbox" name="featured" ${p.featured ? 'checked' : ''}> ★ Zvýrazněná (velká dlaždice)</label>
          <label>Kategorie<select name="category">${opt(CATEGORIES, p.category)}</select></label>
          <label>Stav<select name="status">${opt(STATUSES, p.status)}</select></label>
          <div class="row2"><label>Rok<input name="year" value="${esc(p.year)}"></label><label>Barva<input type="color" name="accent" value="${esc(p.accent)}"></label></div>
          <label>Adresa (slug)<input name="slug" value="${esc(p.slug)}" placeholder="vytvoří se z názvu"></label>
          <button class="btn primary wide">Uložit referenci</button>
          ${!isNew && p.visible ? `<a class="btn wide" target="_blank" href="../reference/${esc(p.slug)}/">↗ Zobrazit na webu</a>` : ''}
        </div>
        ${shotCard('cover', 'Screenshot – počítač', 'celá stránka nebo úvodní obrazovka, šířka ideálně 1440–1920 px', p.cover)}
        ${shotCard('cover_mobile', 'Screenshot – mobil', 'snímek z telefonu (bez stavového řádku)', p.cover_mobile, true)}
      </div>
    </form>`);

  const form = app.querySelector('#pform');
  const state = { ...p };
  // Nahrání screenshotů
  form.querySelectorAll('[data-upload]').forEach((input) => input.addEventListener('change', async () => {
    const kind = input.dataset.upload;
    const files = [...input.files];
    if (!files.length) return;
    const drop = input.closest('.drop');
    drop.classList.add('busy');
    try {
      if (kind === 'gallery') {
        for (const f of files) {
          const up = await api.upload(f, 'galerie');
          await api.addImage(p.id, { file: up.url, w: up.w, h: up.h, caption: '', sort: p.images.length });
        }
        changed(files.length > 1 ? 'Obrázky nahrány.' : 'Obrázek nahrán.');
        return projectEditView(p.id);
      }
      const up = await api.upload(files[0], 'reference');
      if (kind === 'cover') Object.assign(state, { cover: up.url, cover_w: up.w, cover_h: up.h });
      else Object.assign(state, { cover_mobile: up.url, mobile_w: up.w, mobile_h: up.h, mobile_top: up.top });
      const card = form.querySelector(`[data-shot="${kind}"] .shot-prev`);
      card.innerHTML = `<img src="${esc(imgSrc(up.url))}" alt="">`;
      toast('Screenshot připraven – nezapomeňte referenci uložit.');
    } catch (err) {
      toast(err.message, 'err');
    } finally {
      drop.classList.remove('busy');
      input.value = '';
    }
  }));
  form.querySelectorAll('[data-remove-shot]').forEach((b) => b.addEventListener('click', () => {
    const kind = b.dataset.removeShot;
    if (kind === 'cover') Object.assign(state, { cover: '', cover_w: null, cover_h: null });
    else Object.assign(state, { cover_mobile: '', mobile_w: null, mobile_h: null, mobile_top: '' });
    b.closest('[data-shot]').querySelector('.shot-prev').innerHTML = '<p class="muted">Bude odstraněn po uložení.</p>';
  }));
  // Galerie
  form.querySelectorAll('[data-caption]').forEach((inp) => inp.addEventListener('change', async () => {
    await api.setImage(Number(inp.dataset.caption), { caption: inp.value.trim() });
    changed('Popisek uložen.');
  }));
  form.querySelectorAll('[data-del-img]').forEach((b) => b.addEventListener('click', () => confirmBox(b, 'Odstranit obrázek?', async () => {
    const img = p.images.find((i) => i.id === Number(b.dataset.delImg));
    await api.deleteImage(img.id);
    api.removeFile(img.file).catch(() => {});
    changed('Obrázek odstraněn.');
    projectEditView(p.id);
  })));
  // Uložení
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const row = {
      ...state,
      title: fd.get('title').trim(), client: fd.get('client').trim(), url: fd.get('url').trim(), excerpt: fd.get('excerpt').trim(),
      body: fd.get('body').trim(), links: fd.get('links').trim(), tags: fd.get('tags').trim(), category: fd.get('category'),
      status: fd.get('status'), year: fd.get('year').trim(), accent: fd.get('accent'), slug: fd.get('slug').trim(),
      visible: fd.has('visible'), featured: fd.has('featured'),
    };
    if (row.url && !/^https?:\/\//i.test(row.url)) row.url = 'https://' + row.url;
    delete row.images;
    delete row.created_at;
    delete row.updated_at;
    const btn = form.querySelector('button.primary');
    btn.disabled = true;
    try {
      if (p.cover && p.cover !== row.cover) api.removeFile(p.cover).catch(() => {});
      if (p.cover_mobile && p.cover_mobile !== row.cover_mobile) api.removeFile(p.cover_mobile).catch(() => {});
      const saved = await api.saveProject(row);
      changed('Reference uložena.');
      location.hash = `#reference/${saved.id}`;
      if (!isNew) projectEditView(saved.id);
    } catch (err) {
      toast(err.message, 'err');
      btn.disabled = false;
    }
  });
}

function shotCard(kind, title, hint, url, mobile = false) {
  return `<div class="card" data-shot="${kind}">
    <h2>${title}</h2>
    <div class="shot-prev ${mobile ? 'mobile' : ''}">${url ? `<img src="${esc(imgSrc(url))}" alt="">` : '<p class="muted">Zatím bez screenshotu.</p>'}</div>
    ${url ? `<button type="button" class="btn sm danger" data-remove-shot="${kind}">Odstranit</button>` : ''}
    <label class="drop small"><input type="file" accept="image/*" data-upload="${kind}"><span>${url ? 'Nahrát jiný' : 'Nahrát screenshot'}<br><small>${hint}</small></span></label>
  </div>`;
}

/* ---------- hodnocení ---------- */
async function reviewsView() {
  const rows = await api.listReviews();
  shell('hodnoceni', 'Hodnocení klientů', `
    <div class="card">
      <div class="card-head"><p class="muted">Hodnocení se na webu zobrazují v pohyblivém pásu, průměr hvězdiček v úvodu stránky.</p><a class="btn primary" href="#hodnoceni/nove">＋ Nové hodnocení</a></div>
      <table class="table sortable" data-table="reviews">
        <thead><tr><th></th><th>Autor</th><th>Text</th><th>Hvězdy</th><th>Na webu</th><th></th></tr></thead>
        <tbody>${rows.map((r) => `
          <tr data-id="${r.id}" draggable="true">
            <td class="grip">⋮⋮</td>
            <td><b>${esc(r.author)}</b>${r.is_demo ? ' <span class="badge demo">ukázka</span>' : ''}<br><small class="muted">${esc([r.role, r.company].filter(Boolean).join(', '))}</small></td>
            <td class="clip">${esc(r.text.length > 120 ? r.text.slice(0, 120) + '…' : r.text)}${r.project?.title ? `<br><small class="muted">↳ ${esc(r.project.title)}</small>` : ''}</td>
            <td class="stars">${'★'.repeat(r.rating)}</td>
            <td><button class="switch ${r.visible ? 'on' : ''}" data-toggle="${r.id}"><span></span></button></td>
            <td class="actions"><a class="btn sm" href="#hodnoceni/${r.id}">Upravit</a><button class="btn sm danger" data-delete="${r.id}">Smazat</button></td>
          </tr>`).join('')}</tbody>
      </table>
    </div>`);
  bindSortable('reviews');
  app.querySelectorAll('[data-toggle]').forEach((b) => b.addEventListener('click', async () => {
    const r = rows.find((x) => x.id === Number(b.dataset.toggle));
    await api.setReview(r.id, { visible: !r.visible });
    changed();
    reviewsView();
  }));
  app.querySelectorAll('[data-delete]').forEach((b) => b.addEventListener('click', () => confirmBox(b, 'Smazat hodnocení?', async () => {
    const r = rows.find((x) => x.id === Number(b.dataset.delete));
    await api.deleteReview(r.id);
    api.removeFile(r.photo).catch(() => {});
    changed('Hodnocení smazáno.');
    reviewsView();
  })));
}

async function reviewEditView(id) {
  const isNew = id === 'nove';
  const r = isNew ? { author: '', role: '', company: '', text: '', rating: 5, photo: '', project_id: null, visible: true, is_demo: false } : await api.getReview(id);
  if (!r) return notFound();
  const projects = await api.listProjects();
  shell('hodnoceni', isNew ? 'Nové hodnocení' : 'Upravit hodnocení', `
    ${r.is_demo ? '<div class="flash warn">Toto je ukázkové hodnocení. Přepište ho skutečnou zpětnou vazbou od klienta – po uložení zmizí označení „ukázka“.</div>' : ''}
    <form class="grid-form" id="rform">
      <div class="col-main"><div class="card">
        <div class="row2">
          <label>Jméno autora *<input name="author" required value="${esc(r.author)}"></label>
          <label>Pozice<input name="role" value="${esc(r.role)}" placeholder="jednatel, majitelka…"></label>
        </div>
        <label>Firma<input name="company" value="${esc(r.company)}"></label>
        <label>Text hodnocení *<textarea name="text" rows="6" required>${esc(r.text)}</textarea></label>
        <div class="rating-input"><span>Hodnocení</span>${[5, 4, 3, 2, 1].map((i) => `<input type="radio" id="st${i}" name="rating" value="${i}" ${r.rating === i ? 'checked' : ''}><label for="st${i}">★</label>`).join('')}</div>
        <label>Související reference<select name="project_id"><option value="">— žádná —</option>${projects.map((p) => `<option value="${p.id}" ${r.project_id === p.id ? 'selected' : ''}>${esc(p.title)}</option>`).join('')}</select></label>
      </div></div>
      <div class="col-side"><div class="card">
        <label class="check"><input type="checkbox" name="visible" ${r.visible ? 'checked' : ''}> Zobrazit na webu</label>
        <h2>Fotka / logo</h2>
        <div class="shot-prev avatar-box">${r.photo ? `<img class="avatar-prev" src="${esc(imgSrc(r.photo))}" alt="">` : ''}</div>
        <label class="drop small"><input type="file" accept="image/*" data-photo><span>Nahrát fotku<br><small>čtverec, min. 200×200</small></span></label>
        <button class="btn primary wide">Uložit hodnocení</button>
      </div></div>
    </form>`);
  const form = app.querySelector('#rform');
  let photo = r.photo;
  form.querySelector('[data-photo]').addEventListener('change', async (e) => {
    const f = e.target.files[0];
    if (!f) return;
    try {
      photo = (await api.upload(f, 'hodnoceni')).url;
      form.querySelector('.avatar-box').innerHTML = `<img class="avatar-prev" src="${esc(imgSrc(photo))}" alt="">`;
    } catch (err) {
      toast(err.message, 'err');
    }
  });
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    try {
      if (r.photo && r.photo !== photo) api.removeFile(r.photo).catch(() => {});
      await api.saveReview({
        ...(isNew ? {} : { id: r.id }),
        author: fd.get('author').trim(), role: fd.get('role').trim(), company: fd.get('company').trim(), text: fd.get('text').trim(),
        rating: Number(fd.get('rating')) || 5, project_id: Number(fd.get('project_id')) || null, visible: fd.has('visible'), photo, is_demo: false,
      });
      changed('Hodnocení uloženo.');
      location.hash = '#hodnoceni';
    } catch (err) {
      toast(err.message, 'err');
    }
  });
}

/* ---------- poptávky ---------- */
function inquiryTable(rows) {
  return `<table class="table">
    <thead><tr><th>Datum</th><th>Kontakt</th><th>Zájem o</th><th>Rozpočet</th><th>Stav</th></tr></thead>
    <tbody>${rows.map((q) => `
      <tr class="${q.status === 'new' ? 'unread' : ''}">
        <td>${esc(fmtDate(q.created_at))}</td>
        <td><a href="#poptavky/${q.id}"><b>${esc(q.name)}</b></a><br><small class="muted">${esc(q.email)}</small></td>
        <td>${esc(q.services)}</td><td>${esc(q.budget)}</td>
        <td><span class="badge ${esc(q.status)}">${esc(INQ[q.status] || q.status)}</span></td>
      </tr>`).join('')}</tbody>
  </table>`;
}

async function inquiriesView(id, filter = '') {
  const all = await api.listInquiries();
  newInquiries = all.filter((q) => q.status === 'new').length;
  const q = id ? all.find((x) => x.id === Number(id)) : null;
  const rows = filter ? all.filter((x) => x.status === filter) : all;
  shell('poptavky', 'Poptávky', `
    ${q ? `<div class="card inquiry">
      <div class="card-head"><h2>${esc(q.name)}${q.company ? ' · ' + esc(q.company) : ''}</h2><a class="btn sm" href="#poptavky">← Zpět na seznam</a></div>
      <dl>
        <dt>E-mail</dt><dd><a href="mailto:${esc(q.email)}">${esc(q.email)}</a></dd>
        ${q.phone ? `<dt>Telefon</dt><dd><a href="tel:${esc(q.phone.replace(/\s+/g, ''))}">${esc(q.phone)}</a></dd>` : ''}
        <dt>Zájem o</dt><dd>${esc(q.services || '—')}</dd>
        <dt>Rozpočet</dt><dd>${esc(q.budget || '—')}</dd>
        <dt>Přijato</dt><dd>${esc(fmtDate(q.created_at))}</dd>
      </dl>
      <div class="msg">${q.message ? esc(q.message).split(/\n{2,}/).map((p) => `<p>${p.replace(/\n/g, '<br>')}</p>`).join('') : '<p class="muted">Bez zprávy.</p>'}</div>
      <form class="inq-form" id="qform">
        <label>Stav<select name="status">${Object.entries(INQ).map(([k, v]) => `<option value="${k}" ${q.status === k ? 'selected' : ''}>${v}</option>`).join('')}</select></label>
        <label>Interní poznámka<textarea name="note" rows="3">${esc(q.note)}</textarea></label>
        <div class="btns">
          <button class="btn primary">Uložit</button>
          <a class="btn" href="mailto:${esc(q.email)}?subject=${encodeURIComponent('Re: Vaše poptávka – create4you')}">Odpovědět e-mailem</a>
          <button type="button" class="btn danger" data-delete>Smazat</button>
        </div>
      </form>
    </div>` : ''}
    <div class="card">
      <div class="tabs">
        <a href="#poptavky" class="${!filter ? 'on' : ''}">Vše</a>
        ${Object.entries(INQ).map(([k, v]) => `<a href="#poptavky?stav=${k}" class="${filter === k ? 'on' : ''}">${v}</a>`).join('')}
      </div>
      ${rows.length ? inquiryTable(rows) : '<p class="muted">Žádné poptávky.</p>'}
    </div>`);
  if (!q) return;
  if (q.status === 'new') {
    // Otevřením se poptávka označí jako „řeší se“
    await api.setInquiry(q.id, { status: 'progress' });
    q.status = 'progress';
    app.querySelector('select[name=status]').value = 'progress';
  }
  const form = app.querySelector('#qform');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    await api.setInquiry(q.id, { status: form.status.value, note: form.note.value.trim() });
    toast('Poptávka uložena.');
    inquiriesView(q.id, filter);
  });
  form.querySelector('[data-delete]').addEventListener('click', (e) => confirmBox(e.target, 'Smazat poptávku?', async () => {
    await api.deleteInquiry(q.id);
    toast('Poptávka smazána.');
    location.hash = '#poptavky';
  }));
}

/* ---------- nastavení ---------- */
async function settingsView() {
  const values = await api.getSettings();
  shell('nastaveni', 'Nastavení', `
    <form id="sform">
      ${Object.entries(SETTINGS).map(([group, fields]) => `<div class="card"><h2>${esc(group)}</h2><div class="row2">
        ${Object.entries(fields).map(([k, label]) => `<label>${esc(label)}<input name="${k}" value="${esc(values[k] ?? '')}"></label>`).join('')}
      </div></div>`).join('')}
      <button class="btn primary">Uložit nastavení</button>
    </form>
    ${api.demo ? `<div class="card" style="margin-top:24px"><h2>Ukázkový režim</h2><p class="muted">Vrátí ukázková data do původního stavu.</p><button class="btn" id="reset">Obnovit ukázková data</button></div>` : `
    <form class="card" id="pwform" style="margin-top:24px">
      <h2>Změna hesla</h2>
      <div class="row2"><label>Nové heslo (min. 10 znaků)<input type="password" name="pw" minlength="10" required autocomplete="new-password"></label>
      <label>Nové heslo znovu<input type="password" name="pw2" minlength="10" required autocomplete="new-password"></label></div>
      <button class="btn">Změnit heslo</button>
    </form>`}`);
  app.querySelector('#sform').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    await api.saveSettings(Object.fromEntries([...fd.entries()].map(([k, v]) => [k, String(v).trim()])));
    changed('Nastavení uloženo.');
  });
  app.querySelector('#pwform')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const f = e.target;
    if (f.pw.value !== f.pw2.value) return toast('Hesla se neshodují.', 'err');
    try {
      await api.changePassword(f.pw.value);
      f.reset();
      toast('Heslo bylo změněno.');
    } catch (err) {
      toast(err.message, 'err');
    }
  });
  app.querySelector('#reset')?.addEventListener('click', async () => {
    await api.reset();
    location.reload();
  });
}

/* ---------- pomocníci ---------- */
/** Potvrzení přímo v rozhraní (místo systémového dialogu) */
function confirmBox(anchor, question, onYes) {
  document.querySelector('.confirm')?.remove();
  const box = document.createElement('div');
  box.className = 'confirm';
  box.innerHTML = `<span>${esc(question)}</span><button type="button" class="btn sm danger" data-yes>Ano, smazat</button><button type="button" class="btn sm" data-no>Zrušit</button>`;
  anchor.closest('td, .btns, figure, .card')?.append(box);
  box.querySelector('[data-no]').addEventListener('click', () => box.remove());
  box.querySelector('[data-yes]').addEventListener('click', async () => {
    box.querySelector('[data-yes]').disabled = true;
    try {
      await onYes();
    } catch (err) {
      toast(err.message, 'err');
    }
  });
}

function bindSortable(table) {
  const tbody = app.querySelector('table.sortable tbody');
  let dragged = null;
  tbody.addEventListener('dragstart', (e) => { dragged = e.target.closest('tr'); dragged.classList.add('dragging'); });
  tbody.addEventListener('dragover', (e) => {
    e.preventDefault();
    const over = e.target.closest('tr');
    if (!dragged || !over || over === dragged) return;
    const r = over.getBoundingClientRect();
    tbody.insertBefore(dragged, e.clientY > r.top + r.height / 2 ? over.nextSibling : over);
  });
  tbody.addEventListener('dragend', async () => {
    dragged.classList.remove('dragging');
    dragged = null;
    await api.reorder(table, [...tbody.rows].map((r) => Number(r.dataset.id)));
    changed('Pořadí uloženo.');
  });
}

function notFound() {
  shell('', 'Nenalezeno', '<div class="card"><p>Tato položka neexistuje. <a href="#prehled">Zpět na přehled</a></p></div>');
}

/* ---------- směrování ---------- */
async function route() {
  const [path, query] = location.hash.slice(1).split('?');
  const [view, id] = (path || 'prehled').split('/');
  if (view === 'odhlasit') {
    await api.signOut();
    location.hash = '';
    return loginView();
  }
  if (!(await api.user())) return loginView();
  try {
    if (view === 'reference') return id ? projectEditView(id) : projectsView();
    if (view === 'hodnoceni') return id ? reviewEditView(id) : reviewsView();
    if (view === 'poptavky') return inquiriesView(id, new URLSearchParams(query).get('stav') || '');
    if (view === 'nastaveni') return settingsView();
    return dashboardView();
  } catch (err) {
    shell(view, 'Chyba', `<div class="flash err">${esc(err.message)}</div>`);
  }
}

api = await createApi();
addEventListener('hashchange', route);
route();
