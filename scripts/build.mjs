// Sestavení webu: načte obsah ze Supabase (nebo z data/seed.json) a vygeneruje statické stránky do dist/
import { cp, mkdir, readFile, rm, writeFile } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { makeCtx } from '../src/lib.mjs';
import { renderHome } from '../src/pages/home.mjs';
import { renderReference, renderPrivacy, renderNotFound } from '../src/pages/other.mjs';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const DIST = join(ROOT, 'dist');
const SITE_URL = (process.env.SITE_URL || 'https://create4you.cz').replace(/\/$/, '');
const SUPABASE_URL = process.env.SUPABASE_URL || '';
const SUPABASE_ANON_KEY = process.env.SUPABASE_ANON_KEY || '';

const bySort = (a, b) => (a.sort ?? 0) - (b.sort ?? 0) || (a.id ?? 0) - (b.id ?? 0);

async function loadFromSupabase() {
  const { createClient } = await import('@supabase/supabase-js');
  const db = createClient(SUPABASE_URL, SUPABASE_ANON_KEY, { auth: { persistSession: false } });
  const [p, r, s] = await Promise.all([
    db.from('projects').select('*, images:project_images(*)').eq('visible', true),
    db.from('reviews').select('*, project:projects(slug)').eq('visible', true),
    db.from('settings').select('key, value'),
  ]);
  for (const res of [p, r, s]) if (res.error) throw new Error('Supabase: ' + res.error.message);
  return {
    projects: p.data.sort(bySort).map((x) => ({ ...x, images: (x.images || []).sort(bySort) })),
    reviews: r.data.sort(bySort).map((x) => ({ ...x, project_slug: x.project?.slug || null })),
    settings: Object.fromEntries(s.data.map((x) => [x.key, x.value])),
  };
}

async function loadFromSeed() {
  const seed = JSON.parse(await readFile(join(ROOT, 'data/seed.json'), 'utf8'));
  return {
    projects: seed.projects.filter((p) => p.visible).sort(bySort),
    reviews: seed.reviews.filter((r) => r.visible).sort(bySort),
    settings: seed.settings,
  };
}

async function write(path, content) {
  const full = join(DIST, path);
  await mkdir(dirname(full), { recursive: true });
  await writeFile(full, content);
}

const source = SUPABASE_URL && SUPABASE_ANON_KEY ? 'supabase' : 'seed';
const data = source === 'supabase' ? await loadFromSupabase() : await loadFromSeed();
const version = Date.now().toString(36);
const ctxFor = (base) => makeCtx({ base, settings: data.settings, siteUrl: SITE_URL, version });

await rm(DIST, { recursive: true, force: true });
await cp(join(ROOT, 'public'), DIST, { recursive: true });

// Stránky
await write('index.html', renderHome(ctxFor(''), data));
for (const [i, project] of data.projects.entries()) {
  const next = data.projects[(i + 1) % data.projects.length];
  const reviews = data.reviews.filter((r) => r.project_slug === project.slug);
  await write(`reference/${project.slug}/index.html`, renderReference(ctxFor('../../'), { project, next, reviews }));
}
await write('zasady/index.html', renderPrivacy(ctxFor('../')));
await write('404.html', renderNotFound(ctxFor('/')));

// Mapa webu
const urls = ['', ...data.projects.map((p) => `reference/${p.slug}/`), 'zasady/'];
await write('sitemap.xml', `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${urls.map((u) => `  <url><loc>${SITE_URL}/${u}</loc></url>`).join('\n')}
</urlset>
`);

// Administrace: připojení k Supabase (veřejný anon klíč – data chrání pravidla RLS v databázi)
await write('admin/config.js', `window.C4Y_CONFIG = ${JSON.stringify({
  supabaseUrl: SUPABASE_URL,
  supabaseAnonKey: SUPABASE_ANON_KEY,
  demo: source !== 'supabase',
})};\n`);
await mkdir(join(DIST, 'admin/vendor'), { recursive: true });
const umd = join(ROOT, 'node_modules/@supabase/supabase-js/dist/umd/supabase.js');
if (existsSync(umd)) await cp(umd, join(DIST, 'admin/vendor/supabase.js'));
await cp(join(ROOT, 'data/seed.json'), join(DIST, 'admin/seed.json'));

console.log(`Hotovo: ${data.projects.length} referencí, ${data.reviews.length} hodnocení (zdroj: ${source}) → dist/`);
