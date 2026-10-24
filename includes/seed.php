<?php
declare(strict_types=1);

/**
 * Výchozí obsah při prvním spuštění. Vše lze upravit v administraci.
 */
function seed(PDO $pdo): void
{
    // Screenshoty referencí jsou v assets/img/refs/ (citlivé údaje zamaskované); chybějící se přeskočí.
    $ref = static fn (string $f): string => is_file(__DIR__ . '/../assets/img/refs/' . $f) ? 'assets/img/refs/' . $f : '';

    $projects = [
        [
            'accuwise', 'AccuWise', 'web', 'AccuWise s.r.o.', 'https://accuwise.cz', '2025', 'done',
            'Web účetní a poradenské kanceláře z Ostravy ve čtyřech jazycích – česky, slovensky, anglicky a korejsky. S klientskou zónou a online konzultací.',
            "Moderní web účetní kanceláře, který mluví jazykem svých klientů – doslova.\n\nKromě české, slovenské a anglické verze má web i samostatnou korejskou mutaci pro korejské firmy působící v Česku a na Slovensku.\n\nSoučástí je klientská zóna pro sběr dokladů, světlý i tmavý režim, firemní e-maily a kompletní správa.",
            'Web na míru, 4 jazykové verze, Klientská zóna, Tmavý režim, E-maily', '#1f3fe0', 1, $ref('accuwise-cz.webp'),
        ],
        [
            'accuflow', 'AccuFlow', 'app', 'AccuWise s.r.o.', '', '2026', 'done',
            'Systém pro řízení účetní kanceláře – průběh zpracování klientů, úkoly týmu, sběr podkladů, daňový kalendář a AI asistent.',
            "Interní aplikace, ve které účetní kancelář řídí celý měsíc.\n\nNa jednom místě vidí, kolik klientů dodalo podklady, co je zpracované a co čeká. Každý člen týmu má přehled svých úkolů a termínů, aplikace hlídá daňový kalendář a zálohy daní.\n\nŠablony e-mailů klientům (měsíční přehled, mzdy, urgence podkladů, daňové přiznání) a AI asistent šetří hodiny ruční práce každý týden.",
            'Aplikace na míru, Řízení týmu, Úkoly a termíny, AI asistent, E-maily klientům', '#2747d9', 1, $ref('accuflow.webp'),
        ],
        [
            'arcus-platby', 'Arcus Platby', 'app', '', '', '2026', 'done',
            'Jednoduchá aplikace pro schvalování plateb – faktury dodavatelů, schválení klientem, přehled po splatnosti a stav zaúčtování.',
            "Účetní nahraje faktury dodavatelů, klient je jedním kliknutím schválí nebo zamítne a vše se propíše do zaúčtování.\n\nPřehled ukazuje, kolik faktur čeká na schválení, co je po splatnosti a jaké částky jsou v jakém stavu. Vyhledávání podle dodavatele nebo čísla faktury, filtry podle stavu a přílohy u každé faktury.\n\nKonec přeposílání faktur e-mailem a dohledávání, kdo co schválil.",
            'Webová aplikace, Schvalování faktur, Přehledy, Klientský přístup', '#4b3fb5', 0, $ref('arcus.webp'),
        ],
        [
            'artlux-flow', 'Artlux Flow', 'app', 'Artlux', '', '2026', 'progress',
            'CRM a systém zakázek pro stavební firmu – zakázky, rozpočty, faktury, plánování, výkazy práce, docházka, sklad a týmový chat.',
            "Kompletní systém, ve kterém stavební firma řídí zakázky od poptávky až po záruku.\n\nRozpočty a nabídky, faktury, klienti a ceník. Na stavbě plánování, výkazy práce, docházka, mapa zakázek a sklad. Přehled financí zakázek v realizaci – výnosy, náklady a mzdy, zisk.\n\nNástěnka ukazuje, co je v realizaci, v záruce i aktivní reklamace, a tým komunikuje přímo v aplikaci.",
            'CRM na míru, Zakázky a rozpočty, Docházka, Sklad, Týmový chat', '#ff5a1f', 1, $ref('artlux-flow.webp'),
        ],
        [
            'montig-weld', 'Montig Weld', 'web', 'Montig Weld s.r.o.', 'https://montigweld.cz', '2025', 'done',
            'Prezentace svářečské a montážní firmy – služby, realizace a rychlý kontakt pro poptávky.',
            "Web pro firmu zaměřenou na svařování, zámečnické práce a montáže.\n\nCílem bylo ukázat realizace a usnadnit poptávku – návštěvník se během pár vteřin dostane ke kontaktu.\n\nSoučástí je nastavení firemních e-mailů a domény.",
            'Web na míru, Galerie realizací, Poptávkový formulář, E-maily', '#ff7a2f', 0, $ref('montigweld.webp'),
        ],
        [
            'artlux-investment', 'Artlux Investment', 'web', 'Artlux Investment', '', '2025', 'done',
            'Reprezentativní web investiční společnosti s důrazem na důvěryhodnost a čistý design.',
            "Prémiová prezentace investiční společnosti.\n\nElegantní design, přehledná struktura a kompletní technické zázemí – doména, e-maily a administrace.",
            'Web na míru, Prémiový design, E-maily', '#c9a45c', 0, $ref('artlux-investment.webp'),
        ],
        [
            'dokly-ai', 'Dokly.ai', 'app', 'Dokly', 'https://dokly.ai', '2026', 'progress',
            'Chytrá aplikace s umělou inteligencí pro práci s dokumenty.',
            "Produkt postavený na AI, který zjednodušuje práci s dokumenty.\n\nNávrh, vývoj i provoz – od prvního prototypu až po ostrou verzi.",
            'AI, SaaS, Webová aplikace', '#7c5cff', 0, $ref('dokly.webp'),
        ],
        [
            'aplikace-rizeni-firem', 'Aplikace pro řízení firem', 'app', 'Různí klienti', '', '2024', 'done',
            'Jednoduché aplikace a CRM na míru – zakázky, klienti, úkoly a přehledy pro malé firmy a kanceláře.',
            "Pro menší firmy a kanceláře stavíme jednoduché nástroje, které nahradí tabulky a papíry.\n\nEvidence zakázek a klientů, úkoly, docházka, přehledy pro majitele – vždy jen to, co firma opravdu potřebuje.",
            'CRM, Řízení firmy, Zakázky, Úkoly', '#c6f432', 0, '',
        ],
    ];
    $st = $pdo->prepare('INSERT INTO projects(slug, title, category, client, url, year, status, excerpt, body, tags, accent, featured, cover, sort)
                         VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    foreach ($projects as $i => $p) {
        $st->execute([...$p, $i]);
    }

    // Další screenshoty do galerií
    $gallery = [
        'accuwise' => [['accuwise-kr.webp', 'Korejská jazyková verze pro korejské firmy v ČR a SR'], ['accuwise-cz.webp', 'Česká verze – úvodní stránka']],
    ];
    $gi = $pdo->prepare('INSERT INTO project_images(project_id, file, caption, sort) SELECT id, ?, ?, ? FROM projects WHERE slug = ?');
    foreach ($gallery as $slug => $imgs) {
        foreach ($imgs as $k => [$file, $caption]) {
            if ($ref($file)) {
                $gi->execute([$ref($file), $caption, $k, $slug]);
            }
        }
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
