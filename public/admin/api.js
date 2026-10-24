// Datová vrstva administrace: Supabase (ostrý provoz), nebo ukázkový režim v prohlížeči
const TABLE_ORDER = (a, b) => (a.sort ?? 0) - (b.sort ?? 0) || (a.id ?? 0) - (b.id ?? 0);

/** Zmenší obrázek v prohlížeči, převede do WebP a zjistí rozměry a barvu horního okraje. */
export async function processImage(file, { maxW = 2000, maxH = 12000 } = {}) {
  if (!file.type.startsWith('image/')) throw new Error('Nahrávejte prosím obrázky (JPG, PNG, WebP).');
  if (file.size > 25 * 1024 * 1024) throw new Error('Obrázek je příliš velký (max. 25 MB).');
  const bmp = await createImageBitmap(file);
  const scale = Math.min(1, maxW / bmp.width, maxH / bmp.height);
  const w = Math.round(bmp.width * scale);
  const h = Math.round(bmp.height * scale);
  const canvas = document.createElement('canvas');
  canvas.width = w;
  canvas.height = h;
  const g = canvas.getContext('2d');
  g.drawImage(bmp, 0, 0, w, h);
  const [r, gg, b] = g.getImageData(Math.floor(w / 2), 1, 1, 1).data;
  const top = '#' + [r, gg, b].map((x) => x.toString(16).padStart(2, '0')).join('');
  const blob = await new Promise((res) => canvas.toBlob(res, 'image/webp', 0.85));
  return { blob, w, h, top };
}

const slugify = (s) => String(s).normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase()
  .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') || 'projekt';

/* ------------------------------------------------------------------ */
/* Supabase                                                            */
/* ------------------------------------------------------------------ */
function supabaseApi(cfg) {
  const sb = window.supabase.createClient(cfg.supabaseUrl, cfg.supabaseAnonKey);
  const must = ({ data, error }) => {
    if (error) throw new Error(error.message);
    return data;
  };

  const api = {
    demo: false,
    async user() {
      const { data } = await sb.auth.getSession();
      return data.session?.user || null;
    },
    async signIn(email, password) {
      const { error } = await sb.auth.signInWithPassword({ email, password });
      if (error) throw new Error(error.message === 'Invalid login credentials' ? 'Nesprávný e-mail nebo heslo.' : error.message);
      const user = await api.user();
      const admin = await sb.from('admins').select('user_id').eq('user_id', user.id);
      if (!admin.data?.length) {
        await sb.auth.signOut();
        throw new Error('Tento účet nemá přístup do administrace.');
      }
      return user;
    },
    signOut: () => sb.auth.signOut(),
    async changePassword(password) {
      const { error } = await sb.auth.updateUser({ password });
      if (error) throw new Error(error.message);
    },

    async listProjects() {
      return must(await sb.from('projects').select('*, images:project_images(id)')).sort(TABLE_ORDER);
    },
    async getProject(id) {
      const p = must(await sb.from('projects').select('*, images:project_images(*)').eq('id', id).single());
      p.images.sort(TABLE_ORDER);
      return p;
    },
    async saveProject(p) {
      const { id, images, ...row } = p;
      row.slug = await api.uniqueSlug(row.slug || slugify(row.title), id);
      if (id) return must(await sb.from('projects').update(row).eq('id', id).select().single());
      const first = must(await sb.from('projects').select('sort').order('sort').limit(1));
      row.sort = (first[0]?.sort ?? 0) - 1;
      return must(await sb.from('projects').insert(row).select().single());
    },
    async uniqueSlug(base, ignoreId) {
      base = slugify(base);
      const taken = must(await sb.from('projects').select('id, slug').like('slug', `${base}%`))
        .filter((r) => r.id !== ignoreId).map((r) => r.slug);
      let slug = base;
      for (let i = 2; taken.includes(slug); i++) slug = `${base}-${i}`;
      return slug;
    },
    deleteProject: async (id) => must(await sb.from('projects').delete().eq('id', id)),
    setProject: async (id, patch) => must(await sb.from('projects').update(patch).eq('id', id)),
    async reorder(table, ids) {
      await Promise.all(ids.map((id, i) => sb.from(table).update({ sort: i }).eq('id', id)));
    },
    addImage: async (projectId, img) => must(await sb.from('project_images').insert({ project_id: projectId, ...img }).select().single()),
    setImage: async (id, patch) => must(await sb.from('project_images').update(patch).eq('id', id)),
    deleteImage: async (id) => must(await sb.from('project_images').delete().eq('id', id)),

    listReviews: async () => must(await sb.from('reviews').select('*, project:projects(title)')).sort(TABLE_ORDER),
    getReview: async (id) => must(await sb.from('reviews').select('*').eq('id', id).single()),
    async saveReview(r) {
      const { id, project, ...row } = r;
      if (id) return must(await sb.from('reviews').update(row).eq('id', id).select().single());
      const first = must(await sb.from('reviews').select('sort').order('sort').limit(1));
      row.sort = (first[0]?.sort ?? 0) - 1;
      return must(await sb.from('reviews').insert(row).select().single());
    },
    deleteReview: async (id) => must(await sb.from('reviews').delete().eq('id', id)),
    setReview: async (id, patch) => must(await sb.from('reviews').update(patch).eq('id', id)),

    listInquiries: async () => must(await sb.from('inquiries').select('*').order('id', { ascending: false })),
    setInquiry: async (id, patch) => must(await sb.from('inquiries').update(patch).eq('id', id)),
    deleteInquiry: async (id) => must(await sb.from('inquiries').delete().eq('id', id)),

    async getSettings() {
      return Object.fromEntries(must(await sb.from('settings').select('key, value')).map((r) => [r.key, r.value]));
    },
    async saveSettings(map) {
      must(await sb.from('settings').upsert(Object.entries(map).map(([key, value]) => ({ key, value }))));
    },

    /** Nahraje zpracovaný obrázek do Supabase Storage, vrací veřejnou adresu */
    async upload(file, folder) {
      const img = await processImage(file);
      const path = `${folder}/${new Date().toISOString().slice(0, 10)}-${crypto.randomUUID().slice(0, 8)}.webp`;
      must(await sb.storage.from('media').upload(path, img.blob, { contentType: 'image/webp', cacheControl: '31536000' }));
      return { url: sb.storage.from('media').getPublicUrl(path).data.publicUrl, w: img.w, h: img.h, top: img.top };
    },
    /** Smaže soubor z úložiště (jen soubory nahrané přes administraci) */
    async removeFile(url) {
      const marker = '/storage/v1/object/public/media/';
      if (url && url.includes(marker)) await sb.storage.from('media').remove([url.split(marker)[1]]);
    },

    /** Spustí nové sestavení webu na Netlify */
    async publish() {
      const { data } = await sb.auth.getSession();
      const res = await fetch('/api/publish', { method: 'POST', headers: { Authorization: `Bearer ${data.session?.access_token}` } });
      const out = await res.json().catch(() => ({ ok: false, message: 'Aktualizaci webu se nepodařilo spustit.' }));
      if (!out.ok) throw new Error(out.message);
      return out.message;
    },
  };
  return api;
}

/* ------------------------------------------------------------------ */
/* Ukázkový režim – data v prohlížeči (localStorage), výchozí obsah ze seed.json */
/* ------------------------------------------------------------------ */
async function demoApi() {
  const KEY = 'c4y-demo-v1';
  let db;
  try {
    db = JSON.parse(localStorage.getItem(KEY) || 'null');
  } catch {
    db = null;
  }
  if (!db) {
    const seed = await (await fetch('seed.json')).json();
    let id = 1;
    db = { projects: [], images: [], reviews: [], inquiries: [], settings: seed.settings, nextId: 0 };
    for (const p of seed.projects) {
      const { images = [], ...row } = p;
      const pid = id++;
      db.projects.push({ id: pid, ...row });
      images.forEach((img, i) => db.images.push({ id: id++, project_id: pid, sort: i, ...img }));
    }
    seed.reviews.forEach((r) => db.reviews.push({ id: id++, project_id: null, ...r }));
    const now = Date.now();
    db.inquiries = [
      { id: id++, name: 'Jana Nováková', email: 'jana@novak-stavby.cz', phone: '+420 777 111 222', company: 'Novák stavby s.r.o.', services: 'Šablonový web 48 h', budget: 'Do 20 tis.', message: 'Dobrý den, potřebovali bychom jednoduchý web pro naši stavební firmu, ideálně do konce měsíce.', status: 'new', note: '', created_at: new Date(now - 36e5).toISOString() },
      { id: id++, name: 'Petr Svoboda', email: 'petr@ucetniplus.cz', phone: '', company: 'Účetní Plus', services: 'Aplikace / CRM', budget: '60–150 tis.', message: 'Hledáme CRM pro účetní kancelář s napojením na datové schránky.', status: 'progress', note: 'Schůzka ve čtvrtek', created_at: new Date(now - 3 * 864e5).toISOString() },
    ];
    db.nextId = id;
  }
  const save = () => {
    try {
      localStorage.setItem(KEY, JSON.stringify(db));
    } catch {
      /* plné úložiště (velké obrázky) – data zůstanou do obnovení stránky */
    }
  };
  const nid = () => db.nextId++;
  const clone = (x) => JSON.parse(JSON.stringify(x));
  const byId = (arr, id) => arr.find((x) => x.id === Number(id));
  let user = null;
  try {
    user = JSON.parse(sessionStorage.getItem('c4y-demo-user') || 'null');
  } catch { /* soukromý režim */ }

  const api = {
    demo: true,
    user: async () => user,
    async signIn(email) {
      user = { email: email || 'demo@create4you.cz' };
      try { sessionStorage.setItem('c4y-demo-user', JSON.stringify(user)); } catch { /* soukromý režim */ }
      return user;
    },
    async signOut() {
      user = null;
      try { sessionStorage.removeItem('c4y-demo-user'); } catch { /* soukromý režim */ }
    },
    async changePassword() {},

    listProjects: async () => clone(db.projects.map((p) => ({ ...p, images: db.images.filter((i) => i.project_id === p.id) })).sort(TABLE_ORDER)),
    getProject: async (id) => { const p = byId(db.projects, id); return p && clone({ ...p, images: db.images.filter((i) => i.project_id === p.id).sort(TABLE_ORDER) }); },
    async saveProject(p) {
      const { images, ...row } = p;
      let slug = slugify(row.slug || row.title);
      const base = slug;
      for (let i = 2; db.projects.some((x) => x.slug === slug && x.id !== row.id); i++) slug = `${base}-${i}`;
      row.slug = slug;
      if (row.id) Object.assign(byId(db.projects, row.id), row);
      else db.projects.push({ ...row, id: nid(), sort: Math.min(0, ...db.projects.map((x) => x.sort)) - 1 });
      save();
      return clone(row.id ? byId(db.projects, row.id) : db.projects[db.projects.length - 1]);
    },
    async deleteProject(id) { db.projects = db.projects.filter((p) => p.id !== id); db.images = db.images.filter((i) => i.project_id !== id); save(); },
    async setProject(id, patch) { Object.assign(byId(db.projects, id), patch); save(); },
    async reorder(table, ids) {
      const arr = table === 'projects' ? db.projects : db.reviews;
      ids.forEach((id, i) => { const x = byId(arr, id); if (x) x.sort = i; });
      save();
    },
    async addImage(projectId, img) { const row = { id: nid(), project_id: projectId, sort: db.images.length, caption: '', ...img }; db.images.push(row); save(); return row; },
    async setImage(id, patch) { Object.assign(byId(db.images, id), patch); save(); },
    async deleteImage(id) { db.images = db.images.filter((i) => i.id !== id); save(); },

    listReviews: async () => clone(db.reviews.map((r) => ({ ...r, project: r.project_id ? { title: byId(db.projects, r.project_id)?.title } : null })).sort(TABLE_ORDER)),
    getReview: async (id) => clone(byId(db.reviews, id)),
    async saveReview(r) {
      const { project, ...row } = r;
      if (row.id) Object.assign(byId(db.reviews, row.id), row);
      else db.reviews.push({ ...row, id: nid(), sort: Math.min(0, ...db.reviews.map((x) => x.sort)) - 1 });
      save();
    },
    async deleteReview(id) { db.reviews = db.reviews.filter((r) => r.id !== id); save(); },
    async setReview(id, patch) { Object.assign(byId(db.reviews, id), patch); save(); },

    listInquiries: async () => clone([...db.inquiries].sort((a, b) => b.id - a.id)),
    async setInquiry(id, patch) { Object.assign(byId(db.inquiries, id), patch); save(); },
    async deleteInquiry(id) { db.inquiries = db.inquiries.filter((q) => q.id !== id); save(); },

    getSettings: async () => clone(db.settings),
    async saveSettings(map) { Object.assign(db.settings, map); save(); },

    async upload(file) {
      const img = await processImage(file, { maxW: 1400, maxH: 6000 });
      const url = await new Promise((res) => { const r = new FileReader(); r.onload = () => res(r.result); r.readAsDataURL(img.blob); });
      return { url, w: img.w, h: img.h, top: img.top };
    },
    async removeFile() {},
    async publish() { return 'Ukázkový režim – na ostrém webu by se teď spustila aktualizace (asi 1 minuta).'; },
    async reset() { localStorage.removeItem(KEY); },
  };
  return api;
}

export async function createApi() {
  const cfg = window.C4Y_CONFIG || {};
  const forceDemo = new URLSearchParams(location.search).has('demo');
  if (!forceDemo && !cfg.demo && cfg.supabaseUrl && window.supabase) return supabaseApi(cfg);
  return demoApi();
}
