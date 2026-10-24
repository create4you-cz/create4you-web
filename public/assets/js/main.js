/* create4you.cz — interakce */
(() => {
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const finePointer = matchMedia('(hover: hover) and (pointer: fine)').matches;
  const root = document.documentElement;

  /* ---------- téma ---------- */
  $('.theme-toggle')?.addEventListener('click', () => {
    const current = root.dataset.theme || 'dark';
    const next = current === 'light' ? 'dark' : 'light';
    const apply = () => {
      root.dataset.theme = next;
      try { localStorage.setItem('theme', next); } catch (e) {}
    };
    document.startViewTransition && !reduced ? document.startViewTransition(apply) : apply();
  });

  /* ---------- navigace ---------- */
  const nav = $('.nav');
  const burger = $('.burger');
  burger?.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    burger.setAttribute('aria-expanded', open);
  });
  $$('.nav-links a').forEach((a) => a.addEventListener('click', () => {
    nav.classList.remove('open');
    burger?.setAttribute('aria-expanded', 'false');
  }));

  const progress = $('.progress');
  const onScroll = () => {
    const y = scrollY;
    nav.classList.toggle('scrolled', y > 20);
    const max = document.documentElement.scrollHeight - innerHeight;
    progress.style.setProperty('--p', max > 0 ? (y / max).toFixed(4) : 0);
  };
  addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // Posuvné zvýraznění aktivní sekce v menu
  const ink = $('.nav-ink');
  const links = $$('.nav-links a[data-spy]');
  const moveInk = (a) => {
    if (!ink) return;
    links.forEach((l) => l.classList.toggle('on', l === a));
    if (!a) { ink.style.opacity = 0; return; }
    ink.style.left = a.offsetLeft + 'px';
    ink.style.width = a.offsetWidth + 'px';
    ink.style.opacity = 1;
  };
  const sections = links.map((l) => document.getElementById(l.dataset.spy)).filter(Boolean);
  if (sections.length) {
    const spy = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (en.isIntersecting) moveInk(links.find((l) => l.dataset.spy === en.target.id));
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    sections.forEach((s) => spy.observe(s));
    addEventListener('scroll', () => { if (scrollY < innerHeight * .5) moveInk(null); }, { passive: true });
  }

  /* ---------- rozdělení nadpisů na slova ---------- */
  $$('.split .line').forEach((line) => {
    let i = 0;
    const walk = (node) => {
      [...node.childNodes].forEach((n) => {
        if (n.nodeType === 3) {
          const frag = document.createDocumentFragment();
          n.textContent.split(/(\s+)/).forEach((part) => {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.append(part); return; }
            const w = document.createElement('span');
            w.className = 'w';
            w.style.setProperty('--i', i++);
            w.textContent = part;
            frag.append(w);
          });
          n.replaceWith(frag);
        } else if (n.nodeType === 1 && !n.classList.contains('rotator')) {
          walk(n);
        } else if (n.nodeType === 1) {
          n.classList.add('w');
          n.style.setProperty('--i', i++);
        }
      });
    };
    walk(line);
  });

  /* ---------- scroll reveal ---------- */
  const io = new IntersectionObserver((entries) => {
    entries.forEach((en) => {
      if (!en.isIntersecting) return;
      en.target.classList.add('in');
      io.unobserve(en.target);
    });
  }, { threshold: .12, rootMargin: '0px 0px -40px 0px' });
  // Postupné zpoždění pro prvky ve stejné mřížce
  $$('.bento, .work-grid, .pricing, .faq, .vs').forEach((g) => $$('.reveal', g).forEach((el, i) => el.style.setProperty('--d', (i % 4) * 0.08 + 's')));
  $$('.reveal, .split').forEach((el) => io.observe(el));

  /* ---------- rotující slova v hero ---------- */
  const rot = $('.rotator');
  if (rot) {
    const words = $$('span', rot);
    let k = 0;
    words[0].classList.add('on');
    if (!reduced && words.length > 1) {
      setInterval(() => {
        const cur = words[k];
        cur.classList.remove('on');
        cur.classList.add('out');
        setTimeout(() => cur.classList.remove('out'), 700);
        k = (k + 1) % words.length;
        words[k].classList.add('on');
      }, 2600);
    }
  }

  /* ---------- počítadla ---------- */
  const countIO = new IntersectionObserver((entries) => {
    entries.forEach((en) => {
      if (!en.isIntersecting) return;
      countIO.unobserve(en.target);
      const el = en.target;
      const to = +el.dataset.count || 0;
      if (reduced) { el.textContent = to.toLocaleString('cs-CZ'); return; }
      const t0 = performance.now();
      const dur = 1600;
      const tick = (t) => {
        const p = Math.min(1, (t - t0) / dur);
        const eased = p === 1 ? 1 : 1 - Math.pow(2, -10 * p);
        el.textContent = Math.round(to * eased).toLocaleString('cs-CZ');
        if (p < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    });
  }, { threshold: .5 });
  $$('[data-count]').forEach((el) => countIO.observe(el));

  /* ---------- efekty pro myš ---------- */
  if (finePointer && !reduced) {
    // Pozadí hero reaguje na pohyb myši
    const hero = $('.hero');
    hero?.addEventListener('pointermove', (e) => {
      const r = hero.getBoundingClientRect();
      hero.style.setProperty('--mx', ((e.clientX - r.left) / r.width - .5).toFixed(3));
      hero.style.setProperty('--my', ((e.clientY - r.top) / r.height - .5).toFixed(3));
    });

    // 3D náklon plovoucích karet
    const group = $('[data-tilt-group]');
    if (group) {
      addEventListener('pointermove', (e) => {
        const x = e.clientX / innerWidth - .5;
        const y = e.clientY / innerHeight - .5;
        $$('.tilt', group).forEach((c, i) => {
          const depth = 1 + i * .35;
          c.style.transform = `rotateY(${x * 14 * depth}deg) rotateX(${-y * 12 * depth}deg) translate3d(${x * 16 * depth}px, ${y * 16 * depth}px, 0)`;
        });
      });
    }

    // Světelný kužel na kartách
    $$('.spot').forEach((card) => card.addEventListener('pointermove', (e) => {
      const r = card.getBoundingClientRect();
      card.style.setProperty('--x', e.clientX - r.left + 'px');
      card.style.setProperty('--y', e.clientY - r.top + 'px');
    }));

    // Magnetická tlačítka
    $$('.magnetic').forEach((el) => {
      const inner = el.querySelector('span');
      el.addEventListener('pointermove', (e) => {
        const r = el.getBoundingClientRect();
        const dx = e.clientX - (r.left + r.width / 2);
        const dy = e.clientY - (r.top + r.height / 2);
        el.style.transform = `translate(${dx * .25}px, ${dy * .35}px)`;
        if (inner) inner.style.transform = `translate(${dx * .1}px, ${dy * .15}px)`;
      });
      el.addEventListener('pointerleave', () => {
        el.style.transform = '';
        if (inner) inner.style.transform = '';
      });
    });

    // Vlastní kurzor (nativní kurzor zůstává viditelný)
    const cursor = $('.cursor');
    const dot = $('span', cursor);
    cursor.style.opacity = 0;
    // Plynulé dotahování nezávislé na snímkové frekvenci; smyčka běží jen při pohybu
    let cx = 0, cy = 0, tx = 0, ty = 0, last = 0, running = false, seen = false;
    const place = () => { cursor.style.transform = `translate3d(${cx.toFixed(1)}px, ${cy.toFixed(1)}px, 0)`; };
    const loop = (now) => {
      const dt = Math.min(now - last, 64); last = now;
      const k = 1 - Math.exp(-dt / 45);
      cx += (tx - cx) * k;
      cy += (ty - cy) * k;
      place();
      if (Math.abs(tx - cx) + Math.abs(ty - cy) > .1) requestAnimationFrame(loop);
      else { cx = tx; cy = ty; place(); running = false; }
    };
    addEventListener('pointermove', (e) => {
      tx = e.clientX; ty = e.clientY;
      if (!seen) { seen = true; cx = tx; cy = ty; place(); cursor.style.opacity = 1; }
      if (!running) { running = true; last = performance.now(); requestAnimationFrame(loop); }
    }, { passive: true });
    document.addEventListener('pointerover', (e) => {
      const labelled = e.target.closest('[data-cursor]');
      const link = e.target.closest('a, button, label, summary');
      cursor.classList.toggle('label', !!labelled);
      cursor.classList.toggle('link', !labelled && !!link);
      dot.textContent = labelled ? labelled.dataset.cursor : '';
    });
    document.addEventListener('pointerleave', () => cursor.style.opacity = 0);
    document.addEventListener('pointerenter', () => { if (seen) cursor.style.opacity = 1; });
  }

  /* ---------- vyhledávání domén (dlaždice Kompletní servis) ---------- */
  const dm = $('.domain');
  if (dm) {
    const out = $('.dm-type', dm);
    const names = out.dataset.domains.split(',');
    if (reduced) { out.textContent = names[0]; dm.classList.add('found'); }
    else {
      let n = 0;
      const typeName = async () => {
        const name = names[n++ % names.length];
        for (let i = 1; i <= name.length; i++) { out.textContent = name.slice(0, i); await new Promise((r) => setTimeout(r, 90)); }
        dm.classList.add('found');
        await new Promise((r) => setTimeout(r, 2600));
        dm.classList.remove('found');
        for (let i = name.length; i >= 0; i--) { out.textContent = name.slice(0, i); await new Promise((r) => setTimeout(r, 35)); }
        setTimeout(typeName, 400);
      };
      const dio = new IntersectionObserver(([en]) => { if (en.isIntersecting) { dio.disconnect(); typeName(); } });
      dio.observe(dm);
    }
  }

  /* ---------- filtr referencí ---------- */
  const filterBtns = $$('.filters button');
  filterBtns.forEach((btn) => btn.addEventListener('click', () => {
    const f = btn.dataset.filter;
    const apply = () => {
      filterBtns.forEach((b) => {
        b.classList.toggle('on', b === btn);
        b.setAttribute('aria-selected', b === btn);
      });
      $$('.work').forEach((w) => {
        w.classList.toggle('hide', f !== 'all' && w.dataset.cat !== f);
        w.classList.add('in');
      });
    };
    document.startViewTransition && !reduced ? document.startViewTransition(apply) : apply();
  }));
  $$('.work').forEach((w, i) => w.style.viewTransitionName = 'work-' + i);

  /* ---------- ukázka šablon ---------- */
  const frame = $('.tpl-frame');
  const tplData = frame ? JSON.parse(frame.dataset.templates || '{}') : {};
  $$('.tpl-tabs button').forEach((btn) => btn.addEventListener('click', () => {
    const d = tplData[btn.dataset.tpl];
    if (!d || !frame) return;
    $$('.tpl-tabs button').forEach((b) => b.classList.toggle('on', b === btn));
    frame.dataset.style = btn.dataset.tpl;
    $('.tf-logo-a', frame).textContent = d.logo;
    $('.tf-logo-b', frame).textContent = d.logoSub;
    $('.tf-links', frame).innerHTML = d.nav.map((n) => `<span>${n}</span>`).join('');
    $('.tf-kicker', frame).textContent = d.kicker;
    $('.tf-title', frame).textContent = d.title;
    $('.tf-sub', frame).textContent = d.sub;
    $('.tf-btn', frame).textContent = d.btn;
    $('.tf-url b', frame).textContent = d.url;
    const img = $('.tf-img', frame);
    img.classList.toggle('has-photo', !!d.photo);
    img.style.backgroundImage = d.photo ? `url(${d.photo})` : '';
    img.innerHTML = d.factsHtml;
    $('.tf-cards', frame).innerHTML = d.cards.map(([t, s], i) => `<div><span class="tf-ci">${i + 1}</span><b>${t}</b><small>${s}</small></div>`).join('');
    const page = $('.tf-page', frame);
    page.classList.remove('tf-swap');
    void page.offsetWidth;
    page.classList.add('tf-swap');
  }));

  /* ---------- weby v úvodu (střídání referencí) ---------- */
  const slides = $$('.hv-slide');
  const dots = $$('.hv-dots button');
  if (slides.length > 1) {
    let cur = 0;
    let timer = null;
    const go = (i) => {
      slides[cur].classList.remove('on');
      dots[cur]?.classList.remove('on');
      cur = (i + slides.length) % slides.length;
      slides[cur].classList.add('on');
      dots[cur]?.classList.add('on');
    };
    const start = () => { if (!reduced) timer = setInterval(() => go(cur + 1), 5200); };
    dots.forEach((d, i) => d.addEventListener('click', () => { clearInterval(timer); go(i); start(); }));
    start();
  }

  /* ---------- předvyplnění formuláře z ceníku ---------- */
  $$('[data-pick]').forEach((a) => a.addEventListener('click', () => {
    const val = a.dataset.pick;
    $$('#cform input[name="services[]"]').forEach((i) => { if (i.value === val) i.checked = true; });
  }));

  /* ---------- kontaktní formulář ---------- */
  const form = $('#cform');
  if (form) {
    // čas načtení formuláře – ochrana proti robotům, kteří formulář odešlou okamžitě
    if (form.elements.ts) form.elements.ts.value = Math.floor(Date.now() / 1000);
    const msg = $('.form-msg', form);
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      msg.className = 'form-msg';
      msg.textContent = '';
      let ok = true;
      ['name', 'email'].forEach((n) => {
        const inp = form.elements[n];
        const bad = !inp.value.trim() || (n === 'email' && !/^\S+@\S+\.\S+$/.test(inp.value));
        inp.closest('.fl').classList.toggle('err', bad);
        if (bad) ok = false;
      });
      if (!ok) {
        msg.classList.add('err');
        msg.textContent = 'Vyplňte prosím jméno a platný e-mail.';
        return;
      }
      const btn = $('button[type=submit] span', form);
      const label = btn.textContent;
      btn.textContent = 'Odesílám…';
      try {
        const res = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
        const data = await res.json();
        msg.classList.add(data.ok ? 'ok' : 'err');
        msg.textContent = data.message;
        if (data.ok) {
          form.classList.add('sent');
          btn.textContent = '✓ Odesláno';
          form.reset();
          setTimeout(() => { form.classList.remove('sent'); btn.textContent = label; }, 5000);
          return;
        }
      } catch (err) {
        msg.classList.add('err');
        msg.textContent = 'Odeslání se nepovedlo. Napište nám prosím přímo e-mail.';
      }
      btn.textContent = label;
    });
  }

  /* ---------- lightbox galerie ---------- */
  const lb = $('.lightbox');
  if (lb) {
    const items = $$('[data-lightbox]');
    const img = $('img', lb);
    let cur = 0;
    const show = (i) => {
      cur = (i + items.length) % items.length;
      img.src = items[cur].href;
      img.alt = $('img', items[cur]).alt;
      lb.hidden = false;
    };
    items.forEach((a, i) => a.addEventListener('click', (e) => { e.preventDefault(); show(i); }));
    $('.lb-close', lb).addEventListener('click', () => lb.hidden = true);
    $('.lb-prev', lb).addEventListener('click', () => show(cur - 1));
    $('.lb-next', lb).addEventListener('click', () => show(cur + 1));
    lb.addEventListener('click', (e) => { if (e.target === lb) lb.hidden = true; });
    addEventListener('keydown', (e) => {
      if (lb.hidden) return;
      if (e.key === 'Escape') lb.hidden = true;
      if (e.key === 'ArrowLeft') show(cur - 1);
      if (e.key === 'ArrowRight') show(cur + 1);
    });
  }
})();
