<?php
/** Sekce „Vyzkoušejte si“ – interaktivní ukázky toho, co stavíme. Logika je v assets/js/demos.js. */
$demoTabs = [
    'crm'    => ['CRM a řízení zakázek', 'Přetáhněte zakázku do dalšího sloupce.', 'app.vasefirma.cz/zakazky'],
    'pay'    => ['Klientský platební portál', 'Zaplaťte fakturu QR kódem.', 'portal.vasefirma.cz/platby'],
    'book'   => ['Online rezervace', 'Vyberte službu, den a čas.', 'vasefirma.cz/rezervace'],
    'shop'   => ['E-shop', 'Vyberte barvu a vložte do košíku.', 'shop.vasefirma.cz/hrnek'],
    'ai'     => ['AI čtení dokladů', 'Nechte AI vytěžit fakturu.', 'app.vasefirma.cz/doklady'],
    'editor' => ['Administrace webu', 'Upravte web a sledujte změny živě.', 'vasefirma.cz/admin'],
];
?>
<section class="section s-demos" id="ukazky">
  <div class="wrap">
    <div class="sec-head reveal">
      <span class="kicker">02 — Vyzkoušejte si</span>
      <h2>Tohle umíme <em>postavit</em></h2>
      <p>Nemusíte nám věřit na slovo. Klikněte si – každá ukázka je skutečně funkční, stejně jako aplikace, které stavíme pro klienty.</p>
    </div>

    <div class="demos reveal">
      <div class="demo-tabs" role="tablist" aria-label="Ukázky aplikací">
        <?php $i = 0; foreach ($demoTabs as $id => [$title, $hint]): ?>
          <button role="tab" id="tab-<?= $id ?>" aria-controls="demo-<?= $id ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" class="<?= $i++ === 0 ? 'on' : '' ?>" data-demo="<?= $id ?>">
            <b><?= e($title) ?></b><small><?= e($hint) ?></small>
            <span class="dt-progress" aria-hidden="true"></span>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="demo-window">
        <div class="dw-bar"><i></i><i></i><i></i><span class="dw-url">https://<b><?= e(reset($demoTabs)[2]) ?></b></span><em class="dw-live">● živá ukázka</em></div>
        <div class="dw-body">

          <!-- CRM -->
          <div class="demo on" id="demo-crm" role="tabpanel" aria-labelledby="tab-crm" data-url="<?= e($demoTabs['crm'][2]) ?>">
            <div class="crm-top">
              <div><small>Rozpracováno</small><b data-crm-sum="open">0 Kč</b></div>
              <div><small>Dokončeno tento měsíc</small><b data-crm-sum="done" class="pos">0 Kč</b></div>
              <div class="hide-sm"><small>Úspěšnost nabídek</small><b data-crm-rate>0 %</b></div>
            </div>
            <div class="kb">
              <?php
              $cols = ['new' => 'Poptávka', 'offer' => 'Nabídka', 'work' => 'Realizace', 'done' => 'Hotovo'];
              $cards = [
                  ['new', 'Novák stavby s.r.o.', 'Web + firemní e-maily', 38000, 'JN', '#4f8bff'],
                  ['new', 'Kavárna U Mlýna', 'Šablonový web 48 h', 8400, 'KM', '#ff7a2f'],
                  ['offer', 'Účetní kancelář Bílá', 'CRM pro kancelář', 126000, 'PB', '#2fd6a3'],
                  ['offer', 'AutoServis Plus', 'Rezervační systém', 54000, 'AS', '#c6f432'],
                  ['work', 'Montáže Horák', 'Aplikace na zakázky', 92000, 'MH', '#7c5cff'],
                  ['done', 'Studio Aura', 'E-shop na Shoptetu', 46000, 'SA', '#d4607e'],
                  ['done', 'Elektro Šimek', 'Web na míru', 41000, 'EŠ', '#ffc23d'],
              ];
              foreach ($cols as $cid => $cname): ?>
                <div class="kb-col" data-col="<?= $cid ?>">
                  <div class="kb-head"><?= $cname ?> <span class="kb-count">0</span></div>
                  <div class="kb-list">
                  <?php foreach ($cards as [$c, $client, $what, $price, $ini, $col]): if ($c !== $cid) continue; ?>
                    <article class="kb-card" draggable="true" data-price="<?= $price ?>" tabindex="0">
                      <b><?= e($client) ?></b>
                      <span><?= e($what) ?></span>
                      <footer><em style="--c:<?= $col ?>"><?= e($ini) ?></em><strong><?= number_format($price, 0, ',', ' ') ?> Kč</strong><button type="button" class="kb-next" aria-label="Posunout do dalšího stavu">→</button></footer>
                    </article>
                  <?php endforeach; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- PLATBY -->
          <div class="demo" id="demo-pay" role="tabpanel" aria-labelledby="tab-pay" hidden data-url="<?= e($demoTabs['pay'][2]) ?>">
            <div class="pay-head">
              <div><small>Klient</small><b>Novák stavby s.r.o.</b></div>
              <div class="pay-due"><small>Zbývá uhradit</small><b data-pay-due>0 Kč</b></div>
            </div>
            <table class="pay-table">
              <thead><tr><th>Faktura</th><th class="hide-sm">Splatnost</th><th>Částka</th><th>Stav</th><th></th></tr></thead>
              <tbody>
                <tr data-amount="12400"><td>2026-118<small>Správa webu – říjen</small></td><td class="hide-sm">31. 10.</td><td>12 400 Kč</td><td><span class="st wait">Čeká</span></td><td><button type="button" class="pay-btn">Zaplatit</button></td></tr>
                <tr data-amount="8900"><td>2026-104<small>E-mailové schránky</small></td><td class="hide-sm">15. 10.</td><td>8 900 Kč</td><td><span class="st late">Po splatnosti</span></td><td><button type="button" class="pay-btn">Zaplatit</button></td></tr>
                <tr data-amount="0" class="paid"><td>2026-087<small>Web na míru – 2. část</small></td><td class="hide-sm">30. 9.</td><td>19 000 Kč</td><td><span class="st ok">Uhrazeno</span></td><td></td></tr>
              </tbody>
            </table>
            <div class="pay-modal" hidden>
              <div class="pm-card">
                <canvas class="pm-qr" width="132" height="132" aria-label="QR platba (ukázka)"></canvas>
                <div>
                  <b>QR platba</b>
                  <dl><dt>Účet</dt><dd>2001234567/2010</dd><dt>VS</dt><dd data-pm-vs></dd><dt>Částka</dt><dd data-pm-amount></dd></dl>
                  <p class="pm-wait"><span class="spin"></span> Čekáme na platbu z banky…</p>
                </div>
              </div>
            </div>
            <p class="demo-note">Platby se párují automaticky z bankovního výpisu. Klientovi i vám přijde potvrzení.</p>
          </div>

          <!-- REZERVACE -->
          <div class="demo" id="demo-book" role="tabpanel" aria-labelledby="tab-book" hidden data-url="<?= e($demoTabs['book'][2]) ?>">
            <div class="book">
              <div class="book-services">
                <small class="lbl">1. Služba</small>
                <label><input type="radio" name="bk-svc" value="Konzultace webu" checked><span><b>Konzultace webu</b><em>30 min · zdarma</em></span></label>
                <label><input type="radio" name="bk-svc" value="Školení administrace"><span><b>Školení administrace</b><em>60 min · 990 Kč</em></span></label>
                <label><input type="radio" name="bk-svc" value="Analýza aplikace"><span><b>Analýza aplikace</b><em>90 min · 1 900 Kč</em></span></label>
              </div>
              <div class="book-when">
                <small class="lbl">2. Den</small>
                <div class="book-days" data-book-days></div>
                <small class="lbl">3. Čas</small>
                <div class="book-slots" data-book-slots></div>
                <button type="button" class="book-go" disabled>Rezervovat termín</button>
              </div>
            </div>
            <div class="book-done" hidden>
              <span class="big-check">✓</span>
              <b>Rezervace potvrzena</b>
              <p data-book-summary></p>
              <small>Potvrzení odešlo e-mailem i SMS. Termín se propsal do vašeho kalendáře.</small>
              <button type="button" class="book-again">Nová rezervace</button>
            </div>
          </div>

          <!-- E-SHOP -->
          <div class="demo" id="demo-shop" role="tabpanel" aria-labelledby="tab-shop" hidden data-url="<?= e($demoTabs['shop'][2]) ?>">
            <div class="shop-top"><span class="shop-logo">Keramika<b>.</b></span><button type="button" class="cart-btn" aria-label="Košík">🛒 <span data-cart-count>0</span></button></div>
            <div class="shop">
              <div class="shop-img" style="--mug:#2f6df6">
                <svg viewBox="0 0 200 200" aria-hidden="true"><ellipse cx="100" cy="176" rx="62" ry="9" fill="currentColor" opacity=".12"/><path d="M48 52h96v92a26 26 0 01-26 26H74a26 26 0 01-26-26z" fill="var(--mug)"/><ellipse cx="96" cy="52" rx="48" ry="10" fill="var(--mug)" style="filter:brightness(1.25)"/><ellipse cx="96" cy="52" rx="40" ry="7" fill="#2a1d14" opacity=".8"/><path d="M144 76h10a20 20 0 010 40h-10" fill="none" stroke="var(--mug)" stroke-width="11"/><path d="M60 70v64" stroke="#fff" stroke-opacity=".25" stroke-width="7" stroke-linecap="round"/></svg>
                <span class="shop-badge">Ručně točený</span>
              </div>
              <div class="shop-info">
                <small>Hrnky · Ostrava</small>
                <h4>Hrnek Ostravák 350 ml</h4>
                <div class="shop-price"><b>490 Kč</b><s>590 Kč</s></div>
                <small class="lbl">Barva glazury</small>
                <div class="swatches">
                  <button type="button" class="on" style="--c:#2f6df6" data-color="Kobaltová" aria-label="Kobaltová"></button>
                  <button type="button" style="--c:#e0661b" data-color="Cihlová" aria-label="Cihlová"></button>
                  <button type="button" style="--c:#2fae7a" data-color="Mechová" aria-label="Mechová"></button>
                  <button type="button" style="--c:#1d1d22" data-color="Uhlová" aria-label="Uhlová"></button>
                </div>
                <div class="shop-buy">
                  <div class="qty"><button type="button" data-q="-1" aria-label="Méně">−</button><span data-qty>1</span><button type="button" data-q="1" aria-label="Více">+</button></div>
                  <button type="button" class="add-cart">Do košíku</button>
                </div>
                <div class="ship"><span data-ship-text>Do dopravy zdarma zbývá 1 500 Kč</span><i><b data-ship-bar></b></i></div>
                <ul class="shop-usp"><li>Skladem – odesíláme zítra</li><li>Platba kartou, Apple Pay, převodem</li><li>Zásilkovna, PPL, osobní odběr</li></ul>
              </div>
            </div>
          </div>

          <!-- AI DOKLADY -->
          <div class="demo" id="demo-ai" role="tabpanel" aria-labelledby="tab-ai" hidden data-url="<?= e($demoTabs['ai'][2]) ?>">
            <div class="ai">
              <div class="invoice">
                <div class="inv-scan" aria-hidden="true"></div>
                <div class="inv-head"><b>FAKTURA</b><span>č. 2026-0412</span></div>
                <div class="inv-cols">
                  <div><small>Dodavatel</small><p>Elektro Šimek s.r.o.<br>Hlavní 12, 702 00 Ostrava<br>IČO: 12345678</p></div>
                  <div><small>Odběratel</small><p>Vaše firma s.r.o.<br>Nádražní 5, Ostrava</p></div>
                </div>
                <table><tr><td>Revize elektroinstalace</td><td>8 200 Kč</td></tr><tr><td>Výměna jističů (4 ks)</td><td>3 960 Kč</td></tr><tr class="sum"><td>Celkem k úhradě</td><td>14 714 Kč</td></tr></table>
                <div class="inv-foot"><span>Datum splatnosti: 7. 11. 2026</span><span>VS: 20260412</span></div>
              </div>
              <div class="ai-panel">
                <button type="button" class="ai-go">✦ Vytěžit pomocí AI</button>
                <dl class="ai-fields">
                  <div><dt>Dodavatel</dt><dd data-v="Elektro Šimek s.r.o."></dd></div>
                  <div><dt>IČO</dt><dd data-v="12345678"></dd></div>
                  <div><dt>Částka vč. DPH</dt><dd data-v="14 714 Kč"></dd></div>
                  <div><dt>Splatnost</dt><dd data-v="7. 11. 2026"></dd></div>
                  <div><dt>Variabilní symbol</dt><dd data-v="20260412"></dd></div>
                  <div><dt>Kategorie</dt><dd data-v="Služby – údržba budov"></dd></div>
                </dl>
                <p class="ai-done" hidden>✓ Doklad zařazen do účetnictví a naplánován k úhradě.</p>
              </div>
            </div>
          </div>

          <!-- EDITOR -->
          <div class="demo" id="demo-editor" role="tabpanel" aria-labelledby="tab-editor" hidden data-url="<?= e($demoTabs['editor'][2]) ?>">
            <div class="ed">
              <form class="ed-panel" onsubmit="return false">
                <label for="ed-title">Nadpis</label>
                <input id="ed-title" value="Truhlářství Dvořák">
                <label for="ed-sub">Podnadpis</label>
                <input id="ed-sub" value="Kuchyně a nábytek na míru z masivu">
                <label for="ed-btn">Tlačítko</label>
                <input id="ed-btn" value="Nezávazná poptávka">
                <span class="ed-lbl">Barva značky</span>
                <div class="swatches" data-ed-colors>
                  <button type="button" class="on" style="--c:#8a5a2b" aria-label="Ořech"></button>
                  <button type="button" style="--c:#2f6df6" aria-label="Modrá"></button>
                  <button type="button" style="--c:#2fae7a" aria-label="Zelená"></button>
                  <button type="button" style="--c:#c2185b" aria-label="Malinová"></button>
                </div>
                <span class="ed-saved">✓ Uloženo automaticky</span>
              </form>
              <div class="ed-site" style="--brand:#8a5a2b">
                <div class="es-nav"><b data-ed="title">Truhlářství Dvořák</b><span>Realizace</span><span>Ceník</span><span>Kontakt</span></div>
                <div class="es-hero">
                  <h4 data-ed="title">Truhlářství Dvořák</h4>
                  <p data-ed="sub">Kuchyně a nábytek na míru z masivu</p>
                  <span class="es-btn" data-ed="btn">Nezávazná poptávka</span>
                </div>
                <div class="es-grid"><div><b>25 let</b><small>tradice</small></div><div><b>480+</b><small>realizací</small></div><div><b>5 let</b><small>záruka</small></div></div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>
