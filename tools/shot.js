// Pořídí screenshot celé stránky: node tools/shot.js <url> <výstup.png> <desktop|mobile>
const { chromium } = require('playwright');
const [url, out, mode = 'desktop'] = process.argv.slice(2);
(async () => {
  const browser = await chromium.launch();
  const vp = mode === 'mobile'
    ? { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true }
    : { viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 };
  const page = await browser.newPage(vp);
  await page.goto(url, { waitUntil: 'networkidle', timeout: 45000 });
  // Projet stránku kvůli lazy-load obrázkům a animacím při scrollu
  await page.evaluate(async () => {
    for (let y = 0; y < document.body.scrollHeight; y += 400) { scrollTo(0, y); await new Promise(r => setTimeout(r, 120)); }
    scrollTo(0, 0);
  });
  await page.waitForTimeout(1200);
  const h = Math.min(await page.evaluate(() => document.documentElement.scrollHeight), mode === 'mobile' ? 5000 : 4200);
  await page.screenshot({ path: out, clip: { x: 0, y: 0, width: vp.viewport.width, height: h } });
  await browser.close();
})().catch((e) => { console.error(e.message); process.exit(1); });
