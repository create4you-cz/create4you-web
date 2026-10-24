/* create4you.cz — interaktivní ukázky v sekci „Vyzkoušejte si“ */
(() => {
  const root = document.getElementById('ukazky');
  if (!root) return;
  const $ = (s, c = root) => c.querySelector(s);
  const $$ = (s, c = root) => [...c.querySelectorAll(s)];
  const kc = (n) => Math.round(n).toLocaleString('cs-CZ') + ' Kč';
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Plynulé „dopočítání“ čísla
  const tween = (el, to, fmt = kc) => {
    const from = +el.dataset.val || 0;
    el.dataset.val = to;
    if (reduced) { el.textContent = fmt(to); return; }
    const t0 = performance.now();
    const step = (t) => {
      const p = Math.min(1, (t - t0) / 600);
      el.textContent = fmt(from + (to - from) * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };

  /* ---------- přepínání ukázek ---------- */
  const tabs = $$('.demo-tabs button');
  const url = $('.dw-url b');
  let auto = null;
  let touched = false;
  const show = (id) => {
    tabs.forEach((t) => {
      const on = t.dataset.demo === id;
      t.classList.toggle('on', on);
      t.setAttribute('aria-selected', on);
    });
    $$('.demo').forEach((d) => {
      const on = d.id === 'demo-' + id;
      d.hidden = !on;
      d.classList.toggle('on', on);
      if (on) url.textContent = d.dataset.url;
    });
  };
  tabs.forEach((t) => t.addEventListener('click', () => {
    touched = true;
    clearInterval(auto);
    root.classList.add('manual');
    show(t.dataset.demo);
  }));
  // Dokud návštěvník nekliká, ukázky se samy střídají
  const io = new IntersectionObserver(([en]) => {
    clearInterval(auto);
    if (en.isIntersecting && !touched && !reduced) {
      auto = setInterval(() => {
        const i = tabs.findIndex((t) => t.classList.contains('on'));
        show(tabs[(i + 1) % tabs.length].dataset.demo);
      }, 7000);
    }
  }, { threshold: .4 });
  io.observe($('.demos'));
  $('.dw-body').addEventListener('pointerdown', () => { touched = true; clearInterval(auto); root.classList.add('manual'); });

  /* ---------- CRM ---------- */
  const kb = $('.kb');
  const crmUpdate = () => {
    let open = 0, done = 0;
    $$('.kb-col', kb).forEach((col) => {
      const cards = $$('.kb-card', col);
      $('.kb-count', col).textContent = cards.length;
      cards.forEach((c) => { col.dataset.col === 'done' ? (done += +c.dataset.price) : (open += +c.dataset.price); });
    });
    tween($('[data-crm-sum="open"]'), open);
    tween($('[data-crm-sum="done"]'), done);
    const total = $$('.kb-card', kb).length;
    const won = $$('[data-col="work"] .kb-card, [data-col="done"] .kb-card', kb).length;
    tween($('[data-crm-rate]'), (won / total) * 100, (n) => Math.round(n) + ' %');
  };
  const moveCard = (card, col) => {
    if (!col || card.parentElement === $('.kb-list', col)) return;
    const apply = () => { $('.kb-list', col).prepend(card); crmUpdate(); };
    card.classList.add('flash');
    setTimeout(() => card.classList.remove('flash'), 700);
    document.startViewTransition && !reduced ? document.startViewTransition(apply) : apply();
  };
  $$('.kb-card', kb).forEach((c, i) => { c.style.viewTransitionName = 'kb' + i; });
  let dragged = null;
  kb.addEventListener('dragstart', (e) => { dragged = e.target.closest('.kb-card'); dragged?.classList.add('drag'); });
  kb.addEventListener('dragend', () => { dragged?.classList.remove('drag'); $$('.kb-col', kb).forEach((c) => c.classList.remove('over')); });
  kb.addEventListener('dragover', (e) => {
    const col = e.target.closest('.kb-col');
    if (!dragged || !col) return;
    e.preventDefault();
    $$('.kb-col', kb).forEach((c) => c.classList.toggle('over', c === col));
  });
  kb.addEventListener('drop', (e) => { e.preventDefault(); if (dragged) moveCard(dragged, e.target.closest('.kb-col')); });
  kb.addEventListener('click', (e) => {
    const btn = e.target.closest('.kb-next');
    if (!btn) return;
    const card = btn.closest('.kb-card');
    const cols = $$('.kb-col', kb);
    const i = cols.indexOf(card.closest('.kb-col'));
    moveCard(card, cols[(i + 1) % cols.length]);
  });
  crmUpdate();

  /* ---------- Platby ---------- */
  const payDue = $('[data-pay-due]');
  const payUpdate = () => tween(payDue, $$('.pay-table tbody tr:not(.paid)').reduce((s, r) => s + +r.dataset.amount, 0));
  const modal = $('.pay-modal');
  const qr = $('.pm-qr');
  const drawQr = (seed) => {
    // Dekorativní QR (ukázka) – skutečný portál generuje QR platbu dle standardu SPAYD
    const ctx = qr.getContext('2d');
    const n = 25, s = qr.width / n;
    let x = seed;
    const rnd = () => ((x = (x * 9301 + 49297) % 233280) / 233280);
    ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, qr.width, qr.height);
    ctx.fillStyle = '#111';
    for (let i = 0; i < n; i++) for (let j = 0; j < n; j++) if (rnd() > .52) ctx.fillRect(i * s, j * s, s, s);
    [[0, 0], [n - 7, 0], [0, n - 7]].forEach(([a, b]) => {
      ctx.fillStyle = '#111'; ctx.fillRect(a * s, b * s, 7 * s, 7 * s);
      ctx.fillStyle = '#fff'; ctx.fillRect((a + 1) * s, (b + 1) * s, 5 * s, 5 * s);
      ctx.fillStyle = '#111'; ctx.fillRect((a + 2) * s, (b + 2) * s, 3 * s, 3 * s);
    });
  };
  $('.pay-table').addEventListener('click', (e) => {
    const btn = e.target.closest('.pay-btn');
    if (!btn) return;
    const row = btn.closest('tr');
    const vs = row.cells[0].firstChild.textContent.replace('-', '');
    $('[data-pm-vs]').textContent = vs;
    $('[data-pm-amount]').textContent = kc(+row.dataset.amount);
    drawQr(+vs);
    modal.hidden = false;
    setTimeout(() => {
      modal.hidden = true;
      row.classList.add('paid', 'flash');
      row.querySelector('.st').className = 'st ok';
      row.querySelector('.st').textContent = 'Uhrazeno';
      btn.remove();
      payUpdate();
    }, 2200);
  });
  payUpdate();

  /* ---------- Rezervace ---------- */
  const days = $('[data-book-days]');
  const slots = $('[data-book-slots]');
  const goBtn = $('.book-go');
  const dayNames = ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'];
  let pickedDay = null, pickedTime = null;
  const d0 = new Date();
  for (let i = 1, added = 0; added < 6; i++) {
    const d = new Date(d0.getFullYear(), d0.getMonth(), d0.getDate() + i);
    if (d.getDay() === 0 || d.getDay() === 6) continue;
    added++;
    const b = document.createElement('button');
    b.type = 'button';
    b.innerHTML = `<small>${dayNames[d.getDay()]}</small><b>${d.getDate()}.</b><small>${d.getMonth() + 1}.</small>`;
    b.dataset.label = `${dayNames[d.getDay()]} ${d.getDate()}. ${d.getMonth() + 1}.`;
    b.dataset.seed = d.getDate();
    days.append(b);
  }
  const renderSlots = (seed) => {
    slots.innerHTML = '';
    ['8:00', '9:30', '11:00', '13:00', '14:30', '16:00'].forEach((t, i) => {
      const b = document.createElement('button');
      b.type = 'button';
      b.textContent = t;
      b.disabled = (seed + i) % 4 === 0;
      if (b.disabled) b.title = 'Obsazeno';
      slots.append(b);
    });
  };
  days.addEventListener('click', (e) => {
    const b = e.target.closest('button');
    if (!b) return;
    $$('button', days).forEach((x) => x.classList.toggle('on', x === b));
    pickedDay = b.dataset.label;
    pickedTime = null;
    goBtn.disabled = true;
    renderSlots(+b.dataset.seed);
  });
  slots.addEventListener('click', (e) => {
    const b = e.target.closest('button:not(:disabled)');
    if (!b) return;
    $$('button', slots).forEach((x) => x.classList.toggle('on', x === b));
    pickedTime = b.textContent;
    goBtn.disabled = false;
  });
  days.firstElementChild.click();
  goBtn.addEventListener('click', () => {
    const svc = $('input[name="bk-svc"]:checked').value;
    $('[data-book-summary]').textContent = `${svc} · ${pickedDay} v ${pickedTime}`;
    $('.book').hidden = true;
    $('.book-done').hidden = false;
  });
  $('.book-again').addEventListener('click', () => { $('.book').hidden = false; $('.book-done').hidden = true; });

  /* ---------- E-shop ---------- */
  const shop = $('#demo-shop');
  let qty = 1, cart = 0;
  const FREE = 1500, PRICE = 490;
  $('.swatches', shop).addEventListener('click', (e) => {
    const b = e.target.closest('button');
    if (!b) return;
    $$('.swatches button', shop).forEach((x) => x.classList.toggle('on', x === b));
    $('.shop-img', shop).style.setProperty('--mug', getComputedStyle(b).getPropertyValue('--c'));
  });
  $$('[data-q]', shop).forEach((b) => b.addEventListener('click', () => {
    qty = Math.max(1, Math.min(9, qty + +b.dataset.q));
    $('[data-qty]', shop).textContent = qty;
  }));
  const shipUpdate = () => {
    const total = cart * PRICE;
    const left = Math.max(0, FREE - total);
    $('[data-ship-text]', shop).textContent = left ? `Do dopravy zdarma zbývá ${kc(left)}` : '🎉 Doprava zdarma!';
    $('[data-ship-bar]', shop).style.width = Math.min(100, (total / FREE) * 100) + '%';
  };
  $('.add-cart', shop).addEventListener('click', (e) => {
    cart += qty;
    $('[data-cart-count]', shop).textContent = cart;
    const btn = $('.cart-btn', shop);
    btn.classList.remove('bump');
    void btn.offsetWidth;
    btn.classList.add('bump');
    e.target.textContent = '✓ Přidáno';
    setTimeout(() => { e.target.textContent = 'Do košíku'; }, 1400);
    shipUpdate();
  });
  shipUpdate();

  /* ---------- AI doklady ---------- */
  const ai = $('#demo-ai');
  const aiBtn = $('.ai-go', ai);
  aiBtn.addEventListener('click', async () => {
    if (ai.classList.contains('running')) return;
    const fields = $$('dd', ai);
    fields.forEach((f) => { f.textContent = ''; f.parentElement.classList.remove('got'); });
    $('.ai-done', ai).hidden = true;
    ai.classList.add('running');
    aiBtn.textContent = 'Čtu doklad…';
    await new Promise((r) => setTimeout(r, reduced ? 0 : 1100));
    for (const f of fields) {
      f.parentElement.classList.add('got');
      const v = f.dataset.v;
      for (let i = 1; i <= v.length; i++) {
        f.textContent = v.slice(0, i);
        if (!reduced) await new Promise((r) => setTimeout(r, 22));
      }
      if (!reduced) await new Promise((r) => setTimeout(r, 120));
    }
    ai.classList.remove('running');
    $('.ai-done', ai).hidden = false;
    aiBtn.textContent = '✦ Vytěžit znovu';
  });

  /* ---------- Editor webu ---------- */
  const ed = $('#demo-editor');
  const saved = $('.ed-saved', ed);
  let savedT = null;
  const flashSaved = () => {
    saved.classList.add('on');
    clearTimeout(savedT);
    savedT = setTimeout(() => saved.classList.remove('on'), 1200);
  };
  [['ed-title', 'title'], ['ed-sub', 'sub'], ['ed-btn', 'btn']].forEach(([id, key]) => {
    document.getElementById(id).addEventListener('input', (e) => {
      $$(`[data-ed="${key}"]`, ed).forEach((el) => { el.textContent = e.target.value || ' '; });
      flashSaved();
    });
  });
  $('[data-ed-colors]', ed).addEventListener('click', (e) => {
    const b = e.target.closest('button');
    if (!b) return;
    $$('[data-ed-colors] button', ed).forEach((x) => x.classList.toggle('on', x === b));
    $('.ed-site', ed).style.setProperty('--brand', getComputedStyle(b).getPropertyValue('--c'));
    flashSaved();
  });
})();
