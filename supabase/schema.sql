-- =====================================================================
-- create4you.cz – databáze pro Supabase
-- Spusťte celý soubor v Supabase → SQL Editor → New query → Run.
-- Potom spusťte supabase/seed.sql (výchozí obsah webu).
-- =====================================================================

-- Administrátoři (uživatelé ze Supabase Auth, kteří smí upravovat web)
create table if not exists public.admins (
  user_id uuid primary key references auth.users (id) on delete cascade,
  created_at timestamptz not null default now()
);

create or replace function public.is_admin() returns boolean
language sql stable security definer set search_path = public as $$
  select exists (select 1 from public.admins where user_id = auth.uid());
$$;

-- Reference
create table if not exists public.projects (
  id bigint generated always as identity primary key,
  slug text not null unique check (slug ~ '^[a-z0-9-]+$'),
  title text not null,
  category text not null default 'web' check (category in ('web', 'app', 'eshop', 'sablona')),
  client text not null default '',
  url text not null default '',
  year text not null default '',
  status text not null default 'done' check (status in ('done', 'progress')),
  excerpt text not null default '',
  body text not null default '',
  tags text not null default '',
  accent text not null default '#7c5cff',
  links text not null default '',          -- další odkazy, řádek „Popisek | https://…“
  cover text not null default '',          -- screenshot pro počítač (URL nebo cesta od kořene webu)
  cover_w int,
  cover_h int,
  cover_mobile text not null default '',   -- screenshot mobilní verze
  mobile_w int,
  mobile_h int,
  mobile_top text not null default '',     -- barva horního okraje (stavový řádek telefonu)
  featured boolean not null default false,
  visible boolean not null default true,
  sort int not null default 0,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.project_images (
  id bigint generated always as identity primary key,
  project_id bigint not null references public.projects (id) on delete cascade,
  file text not null,
  caption text not null default '',
  w int,
  h int,
  sort int not null default 0
);

-- Hodnocení klientů
create table if not exists public.reviews (
  id bigint generated always as identity primary key,
  author text not null,
  role text not null default '',
  company text not null default '',
  text text not null,
  rating int not null default 5 check (rating between 1 and 5),
  photo text not null default '',
  project_id bigint references public.projects (id) on delete set null,
  is_demo boolean not null default false,
  visible boolean not null default true,
  sort int not null default 0,
  created_at timestamptz not null default now()
);

-- Poptávky z kontaktního formuláře (zapisuje je funkce na Netlify)
create table if not exists public.inquiries (
  id bigint generated always as identity primary key,
  name text not null,
  email text not null,
  phone text not null default '',
  company text not null default '',
  services text not null default '',
  budget text not null default '',
  message text not null default '',
  status text not null default 'new' check (status in ('new', 'progress', 'won', 'closed')),
  note text not null default '',
  ip text not null default '',
  created_at timestamptz not null default now()
);
create index if not exists inquiries_ip_created on public.inquiries (ip, created_at);

-- Nastavení webu (kontakty, ceník, čísla…)
create table if not exists public.settings (
  key text primary key,
  value text not null default ''
);

-- Automatická aktualizace updated_at
create or replace function public.touch_updated_at() returns trigger language plpgsql as $$
begin new.updated_at = now(); return new; end $$;
drop trigger if exists projects_touch on public.projects;
create trigger projects_touch before update on public.projects for each row execute function public.touch_updated_at();

-- ---------------------------------------------------------------------
-- Zabezpečení (Row Level Security)
-- Veřejnost smí číst jen zveřejněný obsah, zapisovat smí jen administrátor.
-- ---------------------------------------------------------------------
alter table public.admins enable row level security;
alter table public.projects enable row level security;
alter table public.project_images enable row level security;
alter table public.reviews enable row level security;
alter table public.inquiries enable row level security;
alter table public.settings enable row level security;

drop policy if exists "admins: vlastní záznam" on public.admins;
create policy "admins: vlastní záznam" on public.admins for select using (user_id = auth.uid());

drop policy if exists "projects: čtení" on public.projects;
create policy "projects: čtení" on public.projects for select using (visible or public.is_admin());
drop policy if exists "projects: správa" on public.projects;
create policy "projects: správa" on public.projects for all using (public.is_admin()) with check (public.is_admin());

drop policy if exists "project_images: čtení" on public.project_images;
create policy "project_images: čtení" on public.project_images for select
  using (exists (select 1 from public.projects p where p.id = project_id and (p.visible or public.is_admin())));
drop policy if exists "project_images: správa" on public.project_images;
create policy "project_images: správa" on public.project_images for all using (public.is_admin()) with check (public.is_admin());

drop policy if exists "reviews: čtení" on public.reviews;
create policy "reviews: čtení" on public.reviews for select using (visible or public.is_admin());
drop policy if exists "reviews: správa" on public.reviews;
create policy "reviews: správa" on public.reviews for all using (public.is_admin()) with check (public.is_admin());

drop policy if exists "settings: čtení" on public.settings;
create policy "settings: čtení" on public.settings for select using (true);
drop policy if exists "settings: správa" on public.settings;
create policy "settings: správa" on public.settings for all using (public.is_admin()) with check (public.is_admin());

-- Poptávky vidí a upravují jen administrátoři; vkládá je funkce na Netlify (service role)
drop policy if exists "inquiries: správa" on public.inquiries;
create policy "inquiries: správa" on public.inquiries for all using (public.is_admin()) with check (public.is_admin());

-- ---------------------------------------------------------------------
-- Úložiště fotek a screenshotů (veřejně čitelné, zápis jen administrátor)
-- ---------------------------------------------------------------------
insert into storage.buckets (id, name, public) values ('media', 'media', true)
  on conflict (id) do update set public = true;

drop policy if exists "media: čtení" on storage.objects;
create policy "media: čtení" on storage.objects for select using (bucket_id = 'media');
drop policy if exists "media: nahrávání" on storage.objects;
create policy "media: nahrávání" on storage.objects for insert with check (bucket_id = 'media' and public.is_admin());
drop policy if exists "media: úpravy" on storage.objects;
create policy "media: úpravy" on storage.objects for update using (bucket_id = 'media' and public.is_admin());
drop policy if exists "media: mazání" on storage.objects;
create policy "media: mazání" on storage.objects for delete using (bucket_id = 'media' and public.is_admin());
