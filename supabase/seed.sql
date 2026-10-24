-- Výchozí obsah webu create4you.cz (vygenerováno z data/seed.json)
begin;

insert into public.projects (slug, title, category, client, url, year, status, excerpt, body, tags, accent, links, cover, cover_w, cover_h, cover_mobile, mobile_w, mobile_h, mobile_top, featured, visible, sort) values ('accuwise', 'AccuWise', 'web', 'AccuWise s.r.o.', 'https://accuwise.cz', '2025', 'done', 'Web účetní a poradenské kanceláře z Ostravy ve čtyřech jazycích – česky, slovensky, anglicky a korejsky. S klientskou zónou a online konzultací.', 'Moderní web účetní kanceláře, který mluví jazykem svých klientů – doslova.

Kromě české, slovenské a anglické verze má web i samostatnou korejskou mutaci pro korejské firmy působící v Česku a na Slovensku.

Součástí je klientská zóna pro sběr dokladů, světlý i tmavý režim, firemní e-maily a kompletní správa.', 'Web na míru, 4 jazykové verze, Klientská zóna, Tmavý režim, E-maily', '#1f3fe0', 'Čeština | https://accuwise.cz
Slovenčina | https://accuwise.cz/sk
English | https://accuwise.cz/en
한국어 (korejština) | https://accuwise.cz/kr', '/assets/img/refs/accuwise-cz.webp', 1896, 929, '/assets/img/refs/accuwise-m.webp', 660, 1208, '#f9fafe', true, true, 0) on conflict (slug) do nothing;
insert into public.project_images (project_id, file, caption, w, h, sort) select id, '/assets/img/refs/accuwise-kr.webp', 'Korejská jazyková verze pro korejské firmy v ČR a SR', 1898, 930, 0 from public.projects where slug = 'accuwise';
insert into public.projects (slug, title, category, client, url, year, status, excerpt, body, tags, accent, links, cover, cover_w, cover_h, cover_mobile, mobile_w, mobile_h, mobile_top, featured, visible, sort) values ('accuflow', 'AccuFlow', 'app', 'AccuWise s.r.o.', '', '2026', 'done', 'Systém pro řízení účetní kanceláře – průběh zpracování klientů, úkoly týmu, sběr podkladů, daňový kalendář a AI asistent.', 'Interní aplikace, ve které účetní kancelář řídí celý měsíc.

Na jednom místě vidí, kolik klientů dodalo podklady, co je zpracované a co čeká. Každý člen týmu má přehled svých úkolů a termínů, aplikace hlídá daňový kalendář a zálohy daní.

Šablony e-mailů klientům (měsíční přehled, mzdy, urgence podkladů, daňové přiznání) a AI asistent šetří hodiny ruční práce každý týden.', 'Aplikace na míru, Řízení týmu, Úkoly a termíny, AI asistent, E-maily klientům', '#2747d9', '', '/assets/img/refs/accuflow.webp', 1200, 1001, '/assets/img/refs/accuflow-m.webp', 660, 1346, '#fdfcff', true, true, 1) on conflict (slug) do nothing;
insert into public.projects (slug, title, category, client, url, year, status, excerpt, body, tags, accent, links, cover, cover_w, cover_h, cover_mobile, mobile_w, mobile_h, mobile_top, featured, visible, sort) values ('arcus-platby', 'Arcus Platby', 'app', '', '', '2026', 'done', 'Jednoduchá aplikace pro schvalování plateb – faktury dodavatelů, schválení klientem, přehled po splatnosti a stav zaúčtování.', 'Účetní nahraje faktury dodavatelů, klient je jedním kliknutím schválí nebo zamítne a vše se propíše do zaúčtování.

Přehled ukazuje, kolik faktur čeká na schválení, co je po splatnosti a jaké částky jsou v jakém stavu. Vyhledávání podle dodavatele nebo čísla faktury, filtry podle stavu a přílohy u každé faktury.

Konec přeposílání faktur e-mailem a dohledávání, kdo co schválil.', 'Webová aplikace, Schvalování faktur, Přehledy, Klientský přístup', '#4b3fb5', '', '/assets/img/refs/arcus.webp', 1225, 935, '', null, null, '', false, true, 2) on conflict (slug) do nothing;
insert into public.projects (slug, title, category, client, url, year, status, excerpt, body, tags, accent, links, cover, cover_w, cover_h, cover_mobile, mobile_w, mobile_h, mobile_top, featured, visible, sort) values ('artlux-flow', 'Artlux Flow', 'app', 'Artlux', '', '2026', 'progress', 'CRM a systém zakázek pro stavební firmu – zakázky, rozpočty, faktury, plánování, výkazy práce, docházka, sklad a týmový chat.', 'Kompletní systém, ve kterém stavební firma řídí zakázky od poptávky až po záruku.

Rozpočty a nabídky, faktury, klienti a ceník. Na stavbě plánování, výkazy práce, docházka, mapa zakázek a sklad. Přehled financí zakázek v realizaci – výnosy, náklady a mzdy, zisk.

Nástěnka ukazuje, co je v realizaci, v záruce i aktivní reklamace, a tým komunikuje přímo v aplikaci.', 'CRM na míru, Zakázky a rozpočty, Docházka, Sklad, Týmový chat', '#ff5a1f', '', '/assets/img/refs/artlux-flow.webp', 1902, 903, '/assets/img/refs/artlux-flow-m.webp', 660, 1346, '#eff1f3', true, true, 3) on conflict (slug) do nothing;
insert into public.projects (slug, title, category, client, url, year, status, excerpt, body, tags, accent, links, cover, cover_w, cover_h, cover_mobile, mobile_w, mobile_h, mobile_top, featured, visible, sort) values ('montig-weld', 'Montig Weld', 'sablona', 'Montig Weld s.r.o.', 'https://montigweld.cz', '2025', 'done', 'Web firmy pro rekonstrukce a opravy energetických zařízení – kotle, potrubní rozvody a ocelové konstrukce v Česku i v EU.', 'Šablonový web hotový do 48 hodin – a přitom vypadá jako web na míru.

Web pro specialisty na energetiku a průmyslové potrubí. Od prvního pohledu ukazuje, co firma umí: opravy a montáže kotlů, potrubních rozvodů a ocelových konstrukcí, svařovací metody TIG, MMA, MAG i orbitální svařování a kvalifikaci dle ČSN EN ISO 9606-1.

Sekce služeb, referencí, certifikací a kariéry, rychlý kontakt v horní liště a nezávazná poptávka na jedno kliknutí. Včetně firemních e-mailů a domény.', 'Šablona 48 h, Reference, Certifikace, Kariéra, E-maily', '#e3001b', '', '/assets/img/refs/montigweld.webp', 1911, 878, '', null, null, '', true, true, 4) on conflict (slug) do nothing;
insert into public.projects (slug, title, category, client, url, year, status, excerpt, body, tags, accent, links, cover, cover_w, cover_h, cover_mobile, mobile_w, mobile_h, mobile_top, featured, visible, sort) values ('artlux-investment', 'Artlux Invest', 'web', 'Artlux Investment', 'https://artluxinvest.cz', '2025', 'done', 'Web rekonstrukčního a interiérového studia – kompletní rekonstrukce bytů a domů, kuchyně a vlastní truhlárna.', 'Prezentace studia, které dělá kompletní rekonstrukce bytů a rodinných domů ve vyšší kvalitativní třídě.

Výrazná typografie, sekce rekonstrukcí, kuchyní a truhlárny, realizace, kariéra a blog. Světlý i tmavý režim.

Jasné sdělení: jedna firma, jedna odpovědnost – od demolice po poslední úchyt.', 'Web na míru, Realizace, Kariéra, Blog, Tmavý režim', '#ff4f1f', '', '/assets/img/refs/artlux-investment.webp', 1910, 933, '/assets/img/refs/artlux-investment-m.webp', 660, 1242, '#fdfdfd', false, true, 5) on conflict (slug) do nothing;
insert into public.projects (slug, title, category, client, url, year, status, excerpt, body, tags, accent, links, cover, cover_w, cover_h, cover_mobile, mobile_w, mobile_h, mobile_top, featured, visible, sort) values ('artlux-vykopy', 'Artlux Výkopy', 'web', 'Artlux Investment', 'https://vykopy.artluxinvest.cz', '2026', 'done', 'Samostatný web pro zemní práce a výkopy v Moravskoslezském kraji – služby, technika, galerie a rychlá poptávka.', 'Microsite pro divizi zemních prací.

Výkopy pro základy, přípojky i bazény, terénní úpravy, odvoz a likvidace materiálu. Výrazný industriální design, přehled vlastní techniky, galerie a poptávka na jedno kliknutí.

Web běží na subdoméně hlavního webu Artlux, takže sdílí značku, ale mluví přímo k zákazníkům, kteří hledají výkopové práce.', 'Web na míru, Microsite, Technika, Galerie, Poptávka', '#ff5a1f', '', '/assets/img/refs/artlux-vykopy.webp', 1898, 939, '', null, null, '', false, true, 6) on conflict (slug) do nothing;
insert into public.projects (slug, title, category, client, url, year, status, excerpt, body, tags, accent, links, cover, cover_w, cover_h, cover_mobile, mobile_w, mobile_h, mobile_top, featured, visible, sort) values ('dokly-ai', 'Dokly.ai', 'app', 'Dokly', 'https://dokly.ai', '2026', 'progress', 'Operační systém účetní kanceláře – doklady od klientů s AI vytěžováním, agendy a termíny, datové schránky, banky, mzdy a fakturace.', 'SaaS pro účetní kanceláře v Česku a na Slovensku, který právě stavíme.

Klienti nahrají doklady v portálu nebo je pošlou e-mailem a údaje se z nich načtou samy. Kancelář vidí po agendách, co je u kterého klienta hotové, hlídá termíny a urgence a posílá klientům zprávy a faktury ze šablon.

Na jednom místě jsou doklady, vytěžování, datové schránky, banky, mzdy, fakturace i komunikace s klienty. Klienta stačí přidat přes IČO – zbytek se doplní z rejstříků.', 'SaaS, AI vytěžování dokladů, Klientský portál, Datové schránky, Fakturace', '#1e3a64', '', '/assets/img/refs/dokly.webp', 1904, 938, '', null, null, '', true, true, 7) on conflict (slug) do nothing;
insert into public.project_images (project_id, file, caption, w, h, sort) select id, '/assets/img/refs/dokly-login.webp', 'Přihlášení a založení kanceláře', 1911, 939, 0 from public.projects where slug = 'dokly-ai';
insert into public.projects (slug, title, category, client, url, year, status, excerpt, body, tags, accent, links, cover, cover_w, cover_h, cover_mobile, mobile_w, mobile_h, mobile_top, featured, visible, sort) values ('aplikace-rizeni-firem', 'Aplikace pro řízení firem', 'app', 'Různí klienti', '', '2024', 'done', 'Jednoduché aplikace a CRM na míru – zakázky, klienti, úkoly a přehledy pro malé firmy a kanceláře.', 'Pro menší firmy a kanceláře stavíme jednoduché nástroje, které nahradí tabulky a papíry.

Evidence zakázek a klientů, úkoly, docházka, přehledy pro majitele – vždy jen to, co firma opravdu potřebuje.', 'CRM, Řízení firmy, Zakázky, Úkoly', '#c6f432', '', '', null, null, '', null, null, '', false, true, 8) on conflict (slug) do nothing;

insert into public.reviews (author, role, company, text, rating, photo, is_demo, visible, sort) values ('Ukázkové hodnocení', 'jednatel', 'Vaše firma s.r.o.', 'Web byl hotový rychleji, než jsme čekali, a o e-maily ani doménu jsme se nemuseli vůbec starat. Když něco potřebujeme, stačí zavolat.', 5, '', true, true, 0);
insert into public.reviews (author, role, company, text, rating, photo, is_demo, visible, sort) values ('Ukázkové hodnocení', 'majitelka', 'Kancelář', 'Aplikace nám nahradila tři různé tabulky. Konečně vidíme všechny zakázky a platby na jednom místě.', 5, '', true, true, 1);
insert into public.reviews (author, role, company, text, rating, photo, is_demo, visible, sort) values ('Ukázkové hodnocení', 'OSVČ', 'Řemeslník', 'Šablonový web za pár stovek měsíčně byl pro mě ideální start. Za dva dny jsem byl online i s vlastním e-mailem.', 5, '', true, true, 2);
insert into public.reviews (author, role, company, text, rating, photo, is_demo, visible, sort) values ('Ukázkové hodnocení', 'ředitel', 'Obchodní společnost', 'Oceňuji, že mluvím přímo s tím, kdo web dělá. Žádné přeposílání mezi obchodníky a projektovými manažery.', 5, '', true, true, 3);

insert into public.settings (key, value) values ('contact_name', 'Daniel Peterek') on conflict (key) do nothing;
insert into public.settings (key, value) values ('contact_email', 'info@create4you.cz') on conflict (key) do nothing;
insert into public.settings (key, value) values ('contact_phone', '+420 775 241 298') on conflict (key) do nothing;
insert into public.settings (key, value) values ('contact_city', 'Ostrava · celá ČR a SK') on conflict (key) do nothing;
insert into public.settings (key, value) values ('company_name', 'Daniel Peterek') on conflict (key) do nothing;
insert into public.settings (key, value) values ('company_ico', '08096589') on conflict (key) do nothing;
insert into public.settings (key, value) values ('company_dic', '') on conflict (key) do nothing;
insert into public.settings (key, value) values ('company_addr', 'Ostrava') on conflict (key) do nothing;
insert into public.settings (key, value) values ('company_note', 'Fyzická osoba podnikající dle živnostenského zákona.') on conflict (key) do nothing;
insert into public.settings (key, value) values ('stat_projects', '40') on conflict (key) do nothing;
insert into public.settings (key, value) values ('stat_years', '') on conflict (key) do nothing;
insert into public.settings (key, value) values ('stat_response', '24') on conflict (key) do nothing;
insert into public.settings (key, value) values ('price_template', '700') on conflict (key) do nothing;
insert into public.settings (key, value) values ('price_template_note', 'bez vstupní investice · min. 12 měsíců') on conflict (key) do nothing;
insert into public.settings (key, value) values ('price_custom', 'individuálně') on conflict (key) do nothing;
insert into public.settings (key, value) values ('price_app', 'individuálně') on conflict (key) do nothing;
insert into public.settings (key, value) values ('price_eshop', 'individuálně') on conflict (key) do nothing;
insert into public.settings (key, value) values ('social_facebook', '') on conflict (key) do nothing;
insert into public.settings (key, value) values ('social_instagram', '') on conflict (key) do nothing;
insert into public.settings (key, value) values ('social_linkedin', '') on conflict (key) do nothing;

commit;
