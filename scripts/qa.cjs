// Optional development QA. Uses an externally installed Playwright, never shipped to visitors.
const assert = require('node:assert/strict');
const fs = require('node:fs/promises');
const path = require('node:path');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');

(async () => {
  const output = path.join(__dirname, '../qa/screenshots');
  await fs.mkdir(output, { recursive: true });
  const browser = await chromium.launch({ headless: true, ...(process.env.CHROME_EXECUTABLE ? { executablePath: process.env.CHROME_EXECUTABLE } : {}) });
  const reports = [];
  try {
    for (const width of [1440, 1024, 768, 390, 320]) {
      const context = await browser.newContext({ viewport: { width, height: 1000 }, reducedMotion: 'reduce' });
      const page = await context.newPage();
      const failures = [];
      page.on('pageerror', error => failures.push(error.message));
      page.on('console', message => { if (message.type() === 'error') failures.push(message.text()); });
      page.on('response', response => { if (response.status() >= 400) failures.push(`${response.status()} ${response.url()}`); });
      const response = await page.goto('http://127.0.0.1:4173/', { waitUntil: 'networkidle' });
      assert.equal(response.status(), 200);
      assert.match(response.headers()['content-security-policy'], /connect-src 'none'/);
      await page.evaluate(() => document.fonts.ready);
      // Load lazy assets so a full-page capture contains every reference photo.
      for (const image of await page.locator('img[loading=lazy]').all()) await image.scrollIntoViewIfNeeded();
      await page.evaluate(() => window.scrollTo(0, 0));
      await page.waitForFunction(() => Array.from(document.images).every(image => image.complete && image.naturalWidth > 0));
      const layout = await page.evaluate(() => ({
        width: innerWidth,
        documentWidth: document.documentElement.scrollWidth,
        images: Array.from(document.images).map(image => ({ source: image.getAttribute('src'), width: image.getBoundingClientRect().width, height: image.getBoundingClientRect().height, naturalWidth: image.naturalWidth })),
        sections: Array.from(document.querySelectorAll('section')).map(section => ({ id: section.id || 'facts', y: section.offsetTop, height: section.offsetHeight })),
        overflowingText: Array.from(document.querySelectorAll('h1,h2,h3,p,dd')).filter(element => element.scrollWidth > element.clientWidth + 1).map(element => element.textContent.slice(0, 70)),
        header: Array.from(document.querySelectorAll('.header-inner > *')).filter(element => getComputedStyle(element).display !== 'none').map(element => ({ name: element.className, x: element.getBoundingClientRect().x, y: element.getBoundingClientRect().y, width: element.getBoundingClientRect().width })),
      }));
      assert.ok(layout.documentWidth <= width, `Desbordamiento horizontal a ${width}: ${layout.documentWidth}`);
      assert.deepEqual(layout.overflowingText, [], `Texto desbordado a ${width}`);
      assert.ok(layout.images.every(image => image.naturalWidth > 0));
      assert.ok(layout.images.filter(image => image.width > 0).every(image => image.height > 0));
      const visibleHeader = layout.header.filter(element => element.name !== 'main-nav');
      assert.ok(visibleHeader.every(element => element.y < 35), `Cabecera en varias líneas a ${width}: ${JSON.stringify(visibleHeader)}`);
      await page.screenshot({ path: path.join(output, `desktop-${width}.png`), fullPage: true });
      if (width < 768) {
        const menu = page.getByRole('button', { name: 'Menú', exact: true });
        await menu.click();
        assert.equal(await menu.getAttribute('aria-expanded'), 'true');
        await page.getByRole('navigation', { name: 'Navegación principal' }).getByRole('link', { name: 'Programa', exact: true }).click();
        assert.equal(await menu.getAttribute('aria-expanded'), 'false');
        assert.match(page.url(), /#programa$/);
        await page.evaluate(() => window.scrollTo(0, 0));
      } else {
        await page.getByRole('navigation', { name: 'Navegación principal' }).getByRole('link', { name: 'Programa', exact: true }).click();
        assert.match(page.url(), /#programa$/);
      }
      const faq = page.locator('details').first();
      await faq.locator('summary').click();
      assert.equal(await faq.getAttribute('open'), null);
      await faq.locator('summary').press('Enter');
      assert.notEqual(await faq.getAttribute('open'), null);
      for (const action of ['inscripcion', 'kit', 'clinics', 'ubicacion', 'privacidad', 'terminos', 'cookies', 'contacto', 'redes']) {
        const trigger = page.locator(`[data-dialog="${action}"]`).filter({ visible: true }).first();
        await trigger.click();
        assert.equal(await page.locator('dialog').evaluate(element => element.open), true);
        assert.ok((await page.locator('#dialog-title').textContent()).length > 0);
        if (action === 'inscripcion' && width === 390) await page.screenshot({ path: path.join(output, 'mobile-inscripcion.png') });
        await page.keyboard.press('Escape');
        assert.equal(await page.locator('dialog').evaluate(element => element.open), false);
        assert.equal(await trigger.evaluate(element => element === document.activeElement), true);
      }
      assert.deepEqual(await context.cookies(), []);
      assert.deepEqual(await page.evaluate(() => ({ local: localStorage.length, session: sessionStorage.length })), { local: 0, session: 0 });
      assert.deepEqual(failures, [], `Errores de navegador a ${width}`);
      reports.push({ ...layout, browserErrors: failures, interactions: 'passed', privateStorage: 'empty' });
      await context.close();
    }
    await fs.writeFile(path.join(__dirname, '../qa/report.json'), JSON.stringify(reports, null, 2) + '\n');
    console.log('QA correcta: 1440, 1024, 768, 390 y 320 px; imágenes, cabecera, navegación, menú, FAQ, diálogos, foco y almacenamiento.');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
