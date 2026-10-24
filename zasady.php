<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

site_head('Ochrana osobních údajů · create4you', 'Informace o zpracování osobních údajů na webu create4you.cz.', 'zasady.php');
site_nav(false);
?>
<main class="section">
  <div class="wrap prose">
    <a class="back" href="./">← Zpět na úvod</a>
    <h1>Ochrana osobních údajů</h1>
    <p><b>Správce:</b> <?= e(setting('company_name')) ?>, IČO <?= e(setting('company_ico')) ?>, <?= e(setting('company_addr')) ?>. Kontakt: <a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a>.</p>
    <h2>Jaké údaje zpracováváme</h2>
    <p>Pokud nám pošlete poptávku přes formulář, zpracováváme jméno, e-mail, případně telefon, název firmy a obsah zprávy. Pro ochranu před zneužitím formuláře ukládáme také IP adresu.</p>
    <h2>Proč a jak dlouho</h2>
    <p>Údaje používáme pouze k vyřízení vaší poptávky a navazující komunikaci (oprávněný zájem a jednání o smlouvě). Uchováváme je po dobu nezbytnou k vyřízení poptávky, nejdéle 3 roky, pokud nevznikne smluvní vztah.</p>
    <h2>Komu je předáváme</h2>
    <p>Údaje nepředáváme třetím stranám kromě poskytovatele hostingu, na jehož serverech web běží.</p>
    <h2>Vaše práva</h2>
    <p>Máte právo na přístup k údajům, jejich opravu nebo výmaz, omezení zpracování, vznesení námitky a podání stížnosti u Úřadu pro ochranu osobních údajů (uoou.cz). Stačí nám napsat e-mail.</p>
    <h2>Cookies</h2>
    <p>Web používá pouze technicky nezbytné cookies (např. pro zabezpečení formuláře). Nepoužíváme reklamní ani analytické cookies třetích stran.</p>
  </div>
</main>
<?php site_footer();
