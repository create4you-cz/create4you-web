# create4you.cz

Web agentury create4you – weby, aplikace a CRM na míru, šablonové weby do 48 h a e-shopy.

**Jak to funguje**

- **Netlify** hostuje hotové statické stránky. Web je rychlý, dobrý pro SEO a funguje i ve chvíli, kdy databáze spí.
- **Supabase** drží obsah (reference, hodnocení, poptávky, nastavení), fotky (Storage) a přihlášení do administrace.
- Po každé změně v administraci se web na Netlify sám přegeneruje, změny jsou vidět asi za minutu.
- Kontaktní formulář posílá poptávky přes funkci na Netlify do Supabase. Každá poptávka zároveň přijde e-mailem a zákazník dostane potvrzení (odesílá se přes schránku na Seznamu).

```
public/            statické soubory (CSS, JS, obrázky, administrace)
  admin/           administrace (přihlášení, reference, hodnocení, poptávky, nastavení)
src/               šablony stránek (úvod, detail reference, zásady)
scripts/build.mjs  sestavení webu do dist/ (data ze Supabase, nebo z data/seed.json)
netlify/functions/ contact (formulář) a publish (zveřejnění změn z administrace)
supabase/          schema.sql (tabulky + zabezpečení) a seed.sql (výchozí obsah)
podpis/            e-mailový podpis (index.html = náhled + kopírování)
```

---

## Spuštění krok za krokem

### 1. GitHub
Vytvořte soukromý repozitář (např. `create4you-web`) a nahrajte do něj tento kód.

### 2. Supabase
1. Na [supabase.com](https://supabase.com) založte projekt. Region **Central EU (Frankfurt)**, heslo databáze si uložte.
2. **SQL Editor → New query**: vložte obsah `supabase/schema.sql` a klikněte na **Run**. Totéž udělejte se `supabase/seed.sql`, ten nahraje výchozí obsah webu.
3. **Authentication → Users → Add user → Create new user**: zadejte svůj e-mail a heslo a zaškrtněte *Auto Confirm User*.
4. V **SQL Editoru** z účtu udělejte administrátora (doplňte svůj e-mail):
   ```sql
   insert into public.admins (user_id) select id from auth.users where email = 'vas@email.cz';
   ```
5. **Authentication → Sign In / Providers**: vypněte *Allow new users to sign up*, aby se nikdo cizí nemohl registrovat.
6. **Project Settings → API**: zkopírujte *Project URL*, klíč *anon / publishable* a klíč *service_role / secret*. Klíč service_role nikomu neposílejte a nikam nevkládejte, patří jen do nastavení Netlify.

### 3. Netlify
1. **Add new site → Import an existing project → GitHub** a vyberte repozitář. Nastavení sestavení se načte samo z `netlify.toml`.
2. **Site configuration → Environment variables** přidejte:

   | Proměnná | Hodnota |
   |---|---|
   | `SUPABASE_URL` | Project URL ze Supabase |
   | `SUPABASE_ANON_KEY` | klíč anon / publishable |
   | `SUPABASE_SERVICE_ROLE_KEY` | klíč service_role (označte jako *secret*) |
   | `SITE_URL` | `https://create4you.cz` |
   | `BUILD_HOOK_URL` | viz krok 3 |
   | `SMTP_PASS` | heslo schránky info@create4you.cz (nebo heslo pro aplikace, pokud máte dvoufázové ověření), označte jako *secret*. Bez něj se e-maily o poptávkách neposílají |
   | `SMTP_USER` *(volitelné)* | schránka, ze které se posílá, výchozí `info@create4you.cz` |
   | `NOTIFY_EMAIL` *(volitelné)* | kam posílat upozornění na poptávky (více adres oddělte čárkou), výchozí je kontaktní e-mail z nastavení |
   | `MAIL_FROM` *(volitelné)* | odesílatel, výchozí `create4you <info@create4you.cz>` |
   | `RESEND_API_KEY` *(volitelné)* | místo SMTP lze posílat přes [resend.com](https://resend.com) (doména musí být v Resendu ověřená) |

3. **Site configuration → Build & deploy → Build hooks → Add build hook** (název „Administrace“). Zkopírovanou adresu vložte do proměnné `BUILD_HOOK_URL`.
4. **Deploys → Trigger deploy**.
5. **Domain management → Add a domain**: `create4you.cz`. U registrátora nastavte DNS podle pokynů Netlify. Certifikát HTTPS se vystaví sám.

### 4. Hotovo
- Web: `https://create4you.cz`
- Administrace: `https://create4you.cz/admin/` (přihlášení e-mailem a heslem ze Supabase). Na webu na ni záměrně nevede žádný odkaz a má zakázané indexování vyhledávači.

> Supabase na bezplatném tarifu po týdnu bez aktivity uspí projekt. **Web běží dál**, protože stránky jsou hotové na Netlify. Uspaný projekt probudíte v dashboardu Supabase tlačítkem *Restore*. Bez toho nepůjde administrace ani formulář.

---

## Administrace

| Sekce | Co umí |
|---|---|
| **Přehled** | statistiky, poslední poptávky, co je potřeba dokončit |
| **Reference** | úpravy textů, screenshot pro počítač a mobil, galerie s popisky, odkazy (jazykové verze), řazení přetažením, skrytí, zvýraznění |
| **Hodnocení** | autor, firma, text, hvězdičky, fotka, propojení s referencí |
| **Poptávky** | vše z formuláře, stavy (nová / řeší se / zakázka / uzavřeno), interní poznámky. Každá nová poptávka přijde i e-mailem a zákazník dostane potvrzení |
| **Nastavení** | kontakty, fakturační údaje, čísla v úvodu, ceník, sociální sítě, změna hesla |

Obrázky se zmenší a převedou do WebP už v prohlížeči. U screenshotů se uloží rozměry, takže se na webu zobrazí v přesném poměru stran a nic se neořízne. Změny se na web propíšou automaticky. Tlačítkem *Web je aktuální / Čeká na zveřejnění* v levém panelu je můžete zveřejnit hned.

Dokud Supabase není nastavený, běží administrace v **ukázkovém režimu**. Data se v něm ukládají jen v prohlížeči.

## Bezpečnost
- Zabezpečení přímo v databázi (Row Level Security): veřejnost čte jen zveřejněný obsah, zapisovat smí jen účty v tabulce `admins`.
- Poptávky ukládá pouze serverová funkce. Formulář chrání skryté pole, kontrola doby vyplnění a limit 5 poptávek za hodinu z jedné IP adresy.
- Zveřejnění změn ověřuje přihlášení i oprávnění administrátora.

## Lokální vývoj
```bash
npm install
npm run build      # bez proměnných SUPABASE_* použije data/seed.json
npm run serve      # http://localhost:8090
npm run seed-sql   # přegeneruje supabase/seed.sql z data/seed.json
```

## E-mailový podpis
Otevřete `podpis/index.html` a postupujte podle návodu. V Seznamu se logo (`podpis/logo-podpis.png`) nahraje tlačítkem obrázku přímo do editoru podpisu, v aplikaci Gmail v telefonu jde jen textový podpis. `podpis/podpis.html` je plná verze, která logo načítá z webu.
