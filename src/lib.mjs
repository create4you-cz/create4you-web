// Sdílené funkce pro generování stránek webu create4you.cz

export const CATEGORIES = {
  web: 'Weby na míru',
  app: 'Aplikace & CRM',
  eshop: 'E-shopy',
  sablona: 'Šablonové weby',
};

export const STATUSES = {
  done: 'Dokončeno',
  progress: 'Právě děláme',
};

export const SETTING_DEFAULTS = {
  contact_name: 'Daniel Peterek',
  contact_email: 'info@create4you.cz',
  contact_phone: '+420 775 241 298',
  contact_city: 'Ostrava · celá ČR a SK',
  company_name: 'Daniel Peterek',
  company_ico: '08096589',
  company_dic: '',
  company_addr: 'Ostrava',
  company_note: 'Fyzická osoba podnikající dle živnostenského zákona.',
  stat_projects: '40',
  stat_years: '',
  stat_response: '24',
  price_template: '700',
  price_template_note: 'bez vstupní investice · min. 12 měsíců',
  price_custom: 'individuálně',
  price_app: 'individuálně',
  price_eshop: 'individuálně',
  social_facebook: '',
  social_instagram: '',
  social_linkedin: '',
};

const ESC = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
export const e = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ESC[c]);

/** Text s prázdnými řádky → odstavce */
export const nl2p = (text) =>
  String(text || '').trim().split(/\n{2,}/).filter(Boolean)
    .map((p) => `<p>${e(p).replace(/\n/g, '<br>')}</p>`).join('');

export const host = (url) => String(url || '').replace(/^https?:\/\/(www\.)?/i, '').replace(/\/$/, '');
export const tel = (phone) => String(phone || '').replace(/\s+/g, '');
export const formatRating = (n) => n.toFixed(1).replace('.', ',');

/** Kontext stránky: base = relativní cesta ke kořeni webu ('' na úvodní stránce, '../../' v detailu reference) */
export function makeCtx({ base = '', settings, siteUrl, version }) {
  const setting = (key) => (settings[key] ?? '') !== '' ? settings[key] : (SETTING_DEFAULTS[key] ?? '');
  /** URL obrázku: absolutní adresy beze změny, cesty od kořene webu relativně ke stránce */
  const asset = (path) => !path ? '' : /^https?:\/\//.test(path) ? path : base + path.replace(/^\//, '');
  return { base, setting, siteUrl, version, asset, home: base || './' };
}

/** Odkazy reference: hlavní URL + řádky „Popisek | https://…“ */
export function projectLinks(p) {
  const out = [];
  for (const line of String(p.links || '').split(/\r?\n/)) {
    const i = line.indexOf('|');
    if (i < 0) continue;
    const label = line.slice(0, i).trim();
    const url = line.slice(i + 1).trim();
    if (/^https?:\/\//i.test(url)) out.push([label || url, url]);
  }
  if (!out.length && p.url) out.push([host(p.url), p.url]);
  return out;
}

/** Krátký kód jazykové verze z URL: …/sk → SK, kořen webu → CZ */
export function linkCode(url) {
  try {
    const m = new URL(url).pathname.match(/\/([a-z]{2})\/?$/i);
    return m ? m[1].toUpperCase() : 'CZ';
  } catch {
    return 'CZ';
  }
}

export function stars(rating) {
  const full = Math.round(rating);
  return `<span class="stars" aria-label="${formatRating(rating)} z 5">${'★'.repeat(full)}<span class="off">${'★'.repeat(5 - full)}</span></span>`;
}

const isDark = (hex) => {
  const m = /^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i.exec(hex || '');
  if (!m) return false;
  const [r, g, b] = m.slice(1).map((x) => parseInt(x, 16));
  return 0.299 * r + 0.587 * g + 0.114 * b < 140;
};

const LOCK = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 11V8a5 5 0 0110 0v3M5 11h14v10H5z" fill="none" stroke="currentColor" stroke-width="2"/></svg>';
const SIGNAL = '<svg viewBox="0 0 18 12"><rect x="0" y="8" width="3" height="4" rx="1"/><rect x="5" y="5" width="3" height="7" rx="1"/><rect x="10" y="2" width="3" height="10" rx="1"/><rect x="15" y="0" width="3" height="12" rx="1"/></svg>';
const BATTERY = '<svg viewBox="0 0 26 12"><rect x=".5" y=".5" width="22" height="11" rx="3" fill="none" stroke="currentColor" opacity=".45"/><rect x="2" y="2" width="16" height="8" rx="1.8"/><rect x="23.6" y="4" width="1.6" height="4" rx=".8" opacity=".45"/></svg>';

/**
 * Náhled projektu: okno prohlížeče se screenshotem v přesném poměru stran (nic se neořízne)
 * a telefon s mobilní verzí. Bez screenshotu: typografický plakát v barvách projektu.
 */
export function projectVisual(ctx, p, cls = '', { phone = true } = {}) {
  let screen;
  if (p.cover && p.cover_w && p.cover_h) {
    const ratio = Math.max(p.cover_w / p.cover_h, 1.6); // vyšší screenshot se roluje
    screen = `<div class="scroller" style="aspect-ratio:${ratio.toFixed(4)}"><img src="${e(ctx.asset(p.cover))}" alt="${e(p.title)} – počítač" loading="lazy"></div>`;
  } else {
    const tags = String(p.tags || '').split(',').map((t) => t.trim()).filter(Boolean).slice(0, 3);
    screen = `<div class="poster"><span class="poster-cat">${e(CATEGORIES[p.category] || '')}</span><strong>${e(p.title)}</strong>`
      + `<span class="poster-tags">${tags.map((t) => `<i>${e(t)}</i>`).join('')}</span></div>`;
  }
  const browser = `<div class="browser"><div class="br-bar"><i></i><i></i><i></i><span class="br-url">${LOCK}${e(host(p.url) || p.title.toLowerCase())}</span></div>${screen}</div>`;

  let ph = '';
  if (phone && p.cover_mobile && p.mobile_w && p.mobile_h) {
    const top = p.mobile_top || '#ffffff';
    const ratio = Math.max(p.mobile_w / p.mobile_h, 0.46);
    ph = `<div class="phone"><div class="ph-frame"><div class="ph-screen">`
      + `<div class="ph-status ${isDark(top) ? 'light' : ''}" style="background:${e(top)}"><b>9:41</b><span class="ph-island"></span><span class="ph-icons">${SIGNAL}${BATTERY}</span></div>`
      + `<div class="scroller" style="aspect-ratio:${ratio.toFixed(4)}"><img src="${e(ctx.asset(p.cover_mobile))}" alt="${e(p.title)} – mobil" loading="lazy"></div>`
      + `<span class="ph-home"></span></div></div></div>`;
  }
  return `<div class="devices ${ph ? 'has-phone ' : ''}${cls}" style="--accent:${e(p.accent)}">${browser}${ph}</div>`;
}

const ICONS = {
  star: '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z" fill="currentColor"/>',
  calendar: '<rect x="3.5" y="5" width="17" height="15" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
  shield: '<path d="M12 3l7.5 3v5.5c0 4.4-3.2 8.2-7.5 9.5-4.3-1.3-7.5-5.1-7.5-9.5V6z"/><path d="M8.8 12l2.2 2.2 4.2-4.4"/>',
  check: '<circle cx="12" cy="12" r="8.5"/><path d="M8.3 12.2l2.5 2.5 5-5.2"/>',
  clock: '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
  cup: '<path d="M4.5 9h12v5a5 5 0 01-5 5h-2a5 5 0 01-5-5zM16.5 10.5h1.5a2.5 2.5 0 010 5h-1.8M8 3.5c0 1.5 1.5 1.5 1.5 3M12 3.5c0 1.5 1.5 1.5 1.5 3"/>',
  gift: '<rect x="3.5" y="8.5" width="17" height="4" rx="1"/><path d="M5 12.5V20h14v-7.5M12 8.5V20M12 8.5C10.5 4.5 6.5 5 7.5 7.4 8.2 8.5 12 8.5 12 8.5zM12 8.5c1.5-4 5.5-3.5 4.5-1.1-.7 1.1-4.5 1.1-4.5 1.1z"/>',
};

/** Informační karty v ukázce šablonového webu (hodnocení, otevírací doba, termín…) */
export const tplFacts = (facts) => facts.map(([icon, title, sub], i) =>
  `<div class="tf-fact" style="--i:${i}"><span class="tf-fi"><svg viewBox="0 0 24 24">${ICONS[icon] || ''}</svg></span><span><b>${e(title)}</b><small>${e(sub)}</small></span></div>`).join('');

export function head(ctx, { title, description, path = '', image = '' }) {
  const canonical = `${ctx.siteUrl}/${path}`;
  const og = image ? (/^https?:/.test(image) ? image : ctx.siteUrl + '/' + image.replace(/^\//, '')) : `${ctx.siteUrl}/assets/img/og.jpg`;
  return `<!doctype html>
<html lang="cs">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${e(title)}</title>
<meta name="description" content="${e(description)}">
<link rel="canonical" href="${e(canonical)}">
<meta property="og:type" content="website">
<meta property="og:locale" content="cs_CZ">
<meta property="og:title" content="${e(title)}">
<meta property="og:description" content="${e(description)}">
<meta property="og:url" content="${e(canonical)}">
<meta property="og:image" content="${e(og)}">
<meta name="theme-color" content="#0b0b0f">
<link rel="icon" href="${ctx.base}assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="${ctx.base}assets/css/style.css?v=${ctx.version}">
<script>try{var t=localStorage.getItem('theme');if(t)document.documentElement.dataset.theme=t}catch(e){}</script>
</head>
<body>
<div class="progress" aria-hidden="true"></div>
<div class="cursor" aria-hidden="true"><span></span></div>
`;
}

export function nav(ctx, { home = true } = {}) {
  const p = home ? '' : ctx.home;
  const links = { sluzby: 'Služby', ukazky: 'Ukázky', reference: 'Reference', sablony: 'Web do 48 h', eshopy: 'E-shopy', cenik: 'Ceník' };
  return `
<header class="nav" id="top">
  <div class="wrap nav-in">
    <a class="logo" href="${ctx.home}" aria-label="create4you – úvod">
      <span class="logo-mark" aria-hidden="true"><i></i><i></i></span><span>create<b>4</b>you</span>
    </a>
    <nav class="nav-links" aria-label="Hlavní navigace">
      ${Object.entries(links).map(([id, label]) => `<a href="${p}#${id}" data-spy="${id}">${label}</a>`).join('')}
      <span class="nav-ink" aria-hidden="true"></span>
    </nav>
    <div class="nav-cta">
      <button class="theme-toggle" type="button" aria-label="Přepnout světlý / tmavý režim">
        <svg class="i-sun" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        <svg class="i-moon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"/></svg>
      </button>
      <a class="btn btn-primary magnetic" href="${p}#kontakt"><span>Nezávazná poptávka</span></a>
      <button class="burger" type="button" aria-label="Menu" aria-expanded="false"><span></span><span></span></button>
    </div>
  </div>
</header>
`;
}

export function footer(ctx, { scripts = ['main.js', 'demos.js'] } = {}) {
  const s = ctx.setting;
  const socials = [['Facebook', s('social_facebook')], ['Instagram', s('social_instagram')], ['LinkedIn', s('social_linkedin')]].filter(([, u]) => u);
  return `
<footer class="footer">
  <div class="wrap">
    <div class="foot-grid">
      <div>
        <a class="logo" href="${ctx.home}"><span class="logo-mark" aria-hidden="true"><i></i><i></i></span><span>create<b>4</b>you</span></a>
        <p class="muted">Weby, aplikace a e-shopy na míru. Od domény a e-mailů až po správu – vše pod jednou střechou.</p>
      </div>
      <div>
        <h4>Služby</h4>
        <a href="${ctx.home}#sluzby">Weby na míru</a>
        <a href="${ctx.home}#sluzby">Aplikace &amp; CRM</a>
        <a href="${ctx.home}#sablony">Web do 48 hodin</a>
        <a href="${ctx.home}#eshopy">E-shopy &amp; Shoptet</a>
      </div>
      <div>
        <h4>Kontakt</h4>
        <a href="mailto:${e(s('contact_email'))}">${e(s('contact_email'))}</a>
        ${s('contact_phone') ? `<a href="tel:${e(tel(s('contact_phone')))}">${e(s('contact_phone'))}</a>` : ''}
        <span class="muted">${e(s('contact_city'))}</span>
        ${socials.map(([n, u]) => `<a href="${e(u)}" target="_blank" rel="noopener">${n}</a>`).join('')}
      </div>
      <div>
        <h4>Provozovatel</h4>
        <span>${e(s('company_name'))}</span>
        <span class="muted">IČO: ${e(s('company_ico'))}${s('company_dic') ? ` · DIČ: ${e(s('company_dic'))}` : ''}</span>
        <span class="muted">${e(s('company_addr'))}</span>
        <span class="muted small">${e(s('company_note'))}</span>
      </div>
    </div>
    <div class="wordmark" aria-hidden="true">create4you</div>
    <div class="foot-bottom">
      <span>© ${new Date().getFullYear()} create4you.cz</span>
      <span><a href="${ctx.base}zasady/">Ochrana osobních údajů</a></span>
    </div>
  </div>
</footer>
${scripts.map((f) => `<script src="${ctx.base}assets/js/${f}?v=${ctx.version}" defer></script>`).join('\n')}
</body>
</html>
`;
}
