<?php
declare(strict_types=1);

/**
 * Výchozí obsah při prvním spuštění. Vše lze upravit v administraci.
 */
function seed(PDO $pdo): void
{
    $projects = [
        [
            'accuwise', 'AccuWise', 'web', 'AccuWise s.r.o.', 'https://accuwise.cz', '2024', 'done',
            'Firemní web pro technologickou firmu z Ostravy – včetně firemních e-mailů, administrace a kompletní správy.',
            "Kompletní firemní prezentace od návrhu až po spuštění.\n\nPostarali jsme se o doménu, firemní e-maily, nastavení komunikace i administraci, ve které si klient sám upravuje obsah.\n\nWeb běží na rychlém hostingu s SSL a pravidelnými zálohami a dál ho spravujeme.",
            'Web na míru, Firemní e-maily, Administrace, Správa', '#4f8bff', 1,
        ],
        [
            'montig-weld', 'Montig Weld', 'web', 'Montig Weld s.r.o.', 'https://montigweld.cz', '2025', 'done',
            'Prezentace svářečské a montážní firmy – služby, realizace a rychlý kontakt pro poptávky.',
            "Web pro firmu zaměřenou na svařování, zámečnické práce a montáže.\n\nCílem bylo ukázat realizace a usnadnit poptávku – návštěvník se během pár vteřin dostane ke kontaktu.\n\nSoučástí je nastavení firemních e-mailů a domény.",
            'Web na míru, Galerie realizací, Poptávkový formulář, E-maily', '#ff7a2f', 1,
        ],
        [
            'artlux-investment', 'Artlux Investment', 'web', 'Artlux Investment', '', '2025', 'done',
            'Reprezentativní web investiční společnosti s důrazem na důvěryhodnost a čistý design.',
            "Prémiová prezentace investiční společnosti.\n\nElegantní design, přehledná struktura a kompletní technické zázemí – doména, e-maily a administrace.",
            'Web na míru, Prémiový design, E-maily', '#c9a45c', 0,
        ],
        [
            'artlux-aplikace', 'Artlux – interní aplikace', 'app', 'Artlux Investment', '', '2025', 'progress',
            'Aplikace na míru pro řízení interních procesů – přehledy, evidence a práce s klienty na jednom místě.',
            "Webová aplikace navržená přesně podle toho, jak tým Artlux pracuje.\n\nEvidence, přehledy a role uživatelů – vše dostupné z prohlížeče i mobilu.",
            'Aplikace na míru, Dashboard, Role uživatelů', '#c9a45c', 0,
        ],
        [
            'dokly-ai', 'Dokly.ai', 'app', 'Dokly', 'https://dokly.ai', '2026', 'progress',
            'Chytrá aplikace s umělou inteligencí pro práci s dokumenty.',
            "Produkt postavený na AI, který zjednodušuje práci s dokumenty.\n\nNávrh, vývoj i provoz – od prvního prototypu až po ostrou verzi.",
            'AI, SaaS, Webová aplikace', '#7c5cff', 1,
        ],
        [
            'platby-pro-klienty', 'Platby pro klienty', 'app', '', '', '2025', 'done',
            'Klientský portál pro přehled plateb – faktury, stav úhrad a automatické upomínky.',
            "Aplikace, ve které klienti vidí své platby a faktury na jednom místě.\n\nAutomatické upomínky, přehled o stavu úhrad a export pro účetnictví šetří hodiny ruční práce každý měsíc.",
            'Aplikace na míru, Platby, Klientský portál, Automatizace', '#2fd6a3', 0,
        ],
        [
            'aplikace-rizeni-firem', 'Aplikace pro řízení firem', 'app', 'Různí klienti', '', '2024', 'done',
            'Jednoduché aplikace a CRM na míru – zakázky, klienti, úkoly a přehledy pro malé firmy a kanceláře.',
            "Pro menší firmy a kanceláře stavíme jednoduché nástroje, které nahradí tabulky a papíry.\n\nEvidence zakázek a klientů, úkoly, docházka, přehledy pro majitele – vždy jen to, co firma opravdu potřebuje.",
            'CRM, Řízení firmy, Zakázky, Úkoly', '#c6f432', 0,
        ],
    ];
    $st = $pdo->prepare('INSERT INTO projects(slug, title, category, client, url, year, status, excerpt, body, tags, accent, featured, sort)
                         VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)');
    foreach ($projects as $i => $p) {
        $st->execute([...$p, $i]);
    }

    // Ukázková hodnocení – na webu jsou označena jako „ukázka“, dokud je v administraci nepřepíšete skutečnými.
    $reviews = [
        ['Ukázkové hodnocení', 'jednatel', 'Vaše firma s.r.o.', 'Web byl hotový rychleji, než jsme čekali, a o e-maily ani doménu jsme se nemuseli vůbec starat. Když něco potřebujeme, stačí zavolat.', 5],
        ['Ukázkové hodnocení', 'majitelka', 'Kancelář', 'Aplikace nám nahradila tři různé tabulky. Konečně vidíme všechny zakázky a platby na jednom místě.', 5],
        ['Ukázkové hodnocení', 'OSVČ', 'Řemeslník', 'Šablonový web za pár stovek měsíčně byl pro mě ideální start. Za dva dny jsem byl online i s vlastním e-mailem.', 5],
        ['Ukázkové hodnocení', 'ředitel', 'Obchodní společnost', 'Oceňuji, že mluvím přímo s tím, kdo web dělá. Žádné přeposílání mezi obchodníky a projektovými manažery.', 5],
    ];
    $st = $pdo->prepare('INSERT INTO reviews(author, role, company, text, rating, is_demo, sort) VALUES(?,?,?,?,?,1,?)');
    foreach ($reviews as $i => $r) {
        $st->execute([...$r, $i]);
    }
}
