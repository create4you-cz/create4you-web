import { CATEGORIES, STATUSES, e, nl2p, host, stars, projectVisual, projectLinks, head, nav, footer } from '../lib.mjs';

/** Detail reference – stránka /reference/<slug>/ */
export function renderReference(ctx, { project: p, next, reviews }) {
  const tags = String(p.tags || '').split(',').map((t) => t.trim()).filter(Boolean);
  const links = projectLinks(p);
  const images = p.images || [];
  return head(ctx, {
    title: `${p.title} – reference · create4you`,
    description: p.excerpt || `Reference ${p.title} od create4you.`,
    path: `reference/${p.slug}/`,
    image: p.cover || '',
  }) + nav(ctx, { home: false }) + `
<main class="case" style="--accent:${e(p.accent)}">
  <section class="case-hero">
    <div class="mesh soft" aria-hidden="true"><i></i><i></i></div>
    <div class="wrap">
      <a class="back" href="${ctx.home}#reference">← Všechny reference</a>
      <span class="kicker">${e(CATEGORIES[p.category] || '')}${p.status === 'progress' ? ' · <span class="live-dot"></span> právě děláme' : ''}</span>
      <h1 class="split"><span class="line">${e(p.title)}</span></h1>
      <p class="lead reveal">${e(p.excerpt)}</p>
      <dl class="case-meta reveal">
        ${p.client ? `<div><dt>Klient</dt><dd>${e(p.client)}</dd></div>` : ''}
        ${p.year ? `<div><dt>Rok</dt><dd>${e(p.year)}</dd></div>` : ''}
        <div><dt>Stav</dt><dd>${e(STATUSES[p.status] || '')}</dd></div>
        ${p.url ? `<div><dt>Odkaz</dt><dd><a href="${e(p.url)}" target="_blank" rel="noopener">${e(host(p.url))} ↗</a></dd></div>` : ''}
      </dl>
    </div>
  </section>

  <section class="wrap case-cover reveal">${projectVisual(ctx, p, 'large')}</section>

  <section class="wrap case-body">
    <div class="case-text reveal">${nl2p(p.body)}</div>
    <aside class="reveal">
      ${tags.length ? `<h4>Co jsme dodali</h4><ul class="chips">${tags.map((t) => `<li>${e(t)}</li>`).join('')}</ul>` : ''}
      ${links.length ? `<h4>${links.length > 1 ? 'Jazykové verze a odkazy' : 'Odkaz'}</h4>
        <div class="case-links">${links.map(([label, url]) => `<a class="btn btn-ghost wide magnetic" href="${e(url)}" target="_blank" rel="noopener"><span>${e(label)} ↗</span></a>`).join('')}</div>` : ''}
      <a class="btn btn-primary wide magnetic" href="${ctx.home}#kontakt"><span>Chci něco podobného</span></a>
    </aside>
  </section>

  ${images.length ? `<section class="wrap gallery">
    ${images.map((img, i) => `<figure class="reveal">
        <a href="${e(ctx.asset(img.file))}" data-lightbox="${i}" data-cursor="Zvětšit">
          <img src="${e(ctx.asset(img.file))}" alt="${e(img.caption || p.title)}" loading="lazy">
        </a>
        ${img.caption ? `<figcaption>${e(img.caption)}</figcaption>` : ''}
      </figure>`).join('\n    ')}
  </section>` : ''}

  ${reviews.map((r) => `<section class="wrap case-quote reveal">
    ${stars(r.rating)}
    <blockquote>„${e(r.text)}“</blockquote>
    <p><b>${e(r.author)}</b> · ${e([r.role, r.company].filter(Boolean).join(', '))}${r.is_demo ? ' <em class="demo-tag">ukázka</em>' : ''}</p>
  </section>`).join('\n  ')}

  ${next && next.slug !== p.slug ? `<a class="next-case" href="${ctx.base}reference/${e(next.slug)}/" data-cursor="Další" style="--accent:${e(next.accent)}">
    <div class="wrap"><small>Další reference</small><span>${e(next.title)} →</span></div>
  </a>` : ''}
</main>

<div class="lightbox" hidden>
  <button class="lb-close" aria-label="Zavřít">×</button>
  <button class="lb-prev" aria-label="Předchozí">‹</button>
  <img alt="">
  <button class="lb-next" aria-label="Další">›</button>
</div>
` + footer(ctx, { scripts: ['main.js'] });
}

/** Ochrana osobních údajů – /zasady/ */
export function renderPrivacy(ctx) {
  const s = ctx.setting;
  return head(ctx, { title: 'Ochrana osobních údajů · create4you', description: 'Informace o zpracování osobních údajů na webu create4you.cz.', path: 'zasady/' })
    + nav(ctx, { home: false }) + `
<main class="section">
  <div class="wrap prose">
    <a class="back" href="${ctx.home}">← Zpět na úvod</a>
    <h1>Ochrana osobních údajů</h1>
    <p><b>Správce:</b> ${e(s('company_name'))}, IČO ${e(s('company_ico'))}, ${e(s('company_addr'))}. Kontakt: <a href="mailto:${e(s('contact_email'))}">${e(s('contact_email'))}</a>.</p>
    <h2>Jaké údaje zpracováváme</h2>
    <p>Pokud nám pošlete poptávku přes formulář, zpracováváme jméno, e-mail, případně telefon, název firmy a obsah zprávy. Pro ochranu před zneužitím formuláře ukládáme také IP adresu.</p>
    <h2>Proč a jak dlouho</h2>
    <p>Údaje používáme pouze k vyřízení vaší poptávky a navazující komunikaci (oprávněný zájem a jednání o smlouvě). Uchováváme je po dobu nezbytnou k vyřízení poptávky, nejdéle 3 roky, pokud nevznikne smluvní vztah.</p>
    <h2>Komu je předáváme</h2>
    <p>Údaje zpracovávají poskytovatelé, na jejichž službách web běží: Netlify (hosting webu) a Supabase (databáze, servery v EU). Jiným třetím stranám je nepředáváme.</p>
    <h2>Vaše práva</h2>
    <p>Máte právo na přístup k údajům, jejich opravu nebo výmaz, omezení zpracování, vznesení námitky a podání stížnosti u Úřadu pro ochranu osobních údajů (uoou.cz). Stačí nám napsat e-mail.</p>
    <h2>Cookies</h2>
    <p>Web nepoužívá reklamní ani analytické cookies. V prohlížeči si pamatuje pouze zvolený světlý nebo tmavý režim.</p>
  </div>
</main>
` + footer(ctx, { scripts: ['main.js'] });
}

/** Stránka 404 */
export function renderNotFound(ctx) {
  return head(ctx, { title: 'Stránka nenalezena · create4you', description: 'Tato stránka neexistuje.', path: '404.html' })
    + nav(ctx, { home: false }) + `
<main class="section"><div class="wrap center-box"><h1>Stránka nenalezena</h1><p class="muted">Možná byla přesunuta nebo už neexistuje.</p><a class="btn btn-primary" href="${ctx.home}"><span>Zpět na úvod</span></a></div></main>
` + footer(ctx, { scripts: ['main.js'] });
}
