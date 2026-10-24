// Vygeneruje supabase/seed.sql (výchozí obsah webu) z data/seed.json
import { readFile, writeFile } from 'node:fs/promises';

const seed = JSON.parse(await readFile(new URL('../data/seed.json', import.meta.url), 'utf8'));
const q = (v) => v === null || v === undefined ? 'null' : typeof v === 'boolean' || typeof v === 'number' ? String(v) : `'${String(v).replace(/'/g, "''")}'`;

const out = ['-- Výchozí obsah webu create4you.cz (vygenerováno z data/seed.json)', 'begin;', ''];

const cols = ['slug', 'title', 'category', 'client', 'url', 'year', 'status', 'excerpt', 'body', 'tags', 'accent', 'links',
  'cover', 'cover_w', 'cover_h', 'cover_mobile', 'mobile_w', 'mobile_h', 'mobile_top', 'featured', 'visible', 'sort'];
for (const p of seed.projects) {
  out.push(`insert into public.projects (${cols.join(', ')}) values (${cols.map((c) => q(p[c])).join(', ')}) on conflict (slug) do nothing;`);
  for (const [i, img] of (p.images || []).entries()) {
    out.push(`insert into public.project_images (project_id, file, caption, w, h, sort) select id, ${q(img.file)}, ${q(img.caption)}, ${q(img.w)}, ${q(img.h)}, ${i} from public.projects where slug = ${q(p.slug)};`);
  }
}
out.push('');
for (const r of seed.reviews) {
  out.push(`insert into public.reviews (author, role, company, text, rating, photo, is_demo, visible, sort) values (${[r.author, r.role, r.company, r.text, r.rating, r.photo, r.is_demo, r.visible, r.sort].map(q).join(', ')});`);
}
out.push('');
for (const [k, v] of Object.entries(seed.settings)) {
  out.push(`insert into public.settings (key, value) values (${q(k)}, ${q(v)}) on conflict (key) do nothing;`);
}
out.push('', 'commit;', '');
await writeFile(new URL('../supabase/seed.sql', import.meta.url), out.join('\n'));
console.log('supabase/seed.sql vygenerován');
