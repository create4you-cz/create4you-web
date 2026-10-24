# create4you.cz

Web agentury create4you – weby, aplikace a CRM na míru, šablonové weby do 48 h, e-shopy (Shoptet i na míru).
Obsahuje administraci pro správu referencí (včetně fotogalerie), hodnocení klientů, poptávek a nastavení.

**Technologie:** PHP 8.1+ · SQLite (bez nutnosti MySQL) · čisté HTML/CSS/JS bez frameworků a buildů.
Běží na běžném českém webhostingu (Wedos, Forpsi, Active24, Websupport…).

## Nasazení na hosting

1. Nahrajte celý obsah složky přes FTP do kořene webu (`www/`).
2. Ověřte, že hosting má **PHP 8.1 nebo novější** s rozšířeními `pdo_sqlite` a `gd` (bývají standardně zapnuté).
3. Složky `data/` a `uploads/` musí být zapisovatelné (práva 775 / 755 dle hostingu).
4. Otevřete `https://create4you.cz/admin/` – při prvním spuštění si založíte administrátorský účet (e-mail + heslo).
5. V **Nastavení** doplňte telefon, e-mail, DIČ (pokud jste plátce DPH), sídlo a odkazy na sociální sítě.

Databáze se vytvoří automaticky při první návštěvě a naplní se výchozími referencemi.

### Volitelná konfigurace
Vytvořte `includes/config.local.php` a přepište libovolnou hodnotu z `includes/config.php`, např.:

```php
<?php
return [
    'notify_email' => 'daniel@create4you.cz', // kam chodí upozornění na nové poptávky
    'mail_from'    => 'web@create4you.cz',     // odesílatel (adresa na vaší doméně)
];
```

### Nginx
Soubory `.htaccess` platí pro Apache. Na Nginx přidejte ekvivalent:

```nginx
location ~ ^/(data|includes)/ { deny all; }
location ~ ^/uploads/.*\.(php|phtml|phar)$ { deny all; }
```

## Administrace (`/admin/`)

| Sekce | Co umí |
|---|---|
| **Přehled** | statistiky, nové poptávky, úkoly před spuštěním |
| **Reference** | přidání / úprava / skrytí projektu, titulní fotka, galerie s popisky, řazení přetažením, zvýraznění („velká dlaždice“), stav „Právě děláme“ |
| **Hodnocení** | jméno, firma, text, hvězdičky, fotka, propojení s referencí, řazení |
| **Poptávky** | vše z kontaktního formuláře, stavy (nová / řeší se / zakázka / uzavřeno), interní poznámky |
| **Nastavení** | kontakty, fakturační údaje, čísla na úvodní stránce, ceník, sociální sítě, změna hesla |

Nahrané obrázky se automaticky zmenší (max. 2000 px) a převedou do WebP.

## Screenshoty referencí
Reference se zobrazují v rámečku notebooku a telefonu; screenshot celé stránky se při najetí myší sám roluje.
Screenshoty lze nahrát ručně v administraci (počítač + mobil), nebo je pořídit automaticky ze zadaných odkazů:

```bash
npm i playwright && npx playwright install chromium
php tools/screenshots.php          # reference, které ještě screenshot nemají
php tools/screenshots.php --all    # přefotit všechny
```

## Bezpečnost
- hesla hashovaná (`password_hash`), ochrana proti hádání hesla (8 pokusů / 15 min)
- CSRF tokeny na všech formulářích, výstupy escapované
- nahrané soubory ověřené jako obrázky a znovu zakódované přes GD, v `uploads/` je zakázané spouštění skriptů
- kontaktní formulář: honeypot, kontrola doby vyplnění, limit 5 poptávek / IP / hodinu

## Lokální spuštění
```bash
php -S localhost:8080
```
