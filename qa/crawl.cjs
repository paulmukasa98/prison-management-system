const { chromium } = require('playwright');
const fs = require('node:fs');
const paths = fs.readFileSync('qa_urls.txt', 'utf8').trim().split(/\r?\n/);
const base = process.env.PMS_QA_BASE || 'http://127.0.0.1:8099/';

(async () => {
    const browser = await chromium.launch();
    const results = [];
    for (const width of [1440, 768, 390]) {
        const context = await browser.newContext({ viewport: { width, height: 900 } });
        for (const path of paths) {
            const page = await context.newPage();
            const errors = [];
            page.on('console', message => { if (message.type() === 'error') errors.push(message.text()); });
            page.on('pageerror', error => errors.push(String(error)));
            const result = { path, width, status: null, overflow: false, deadLinks: [], errors };
            try {
                const response = await page.goto(new URL(path.replace(/^\/+/, ''), base).href, { waitUntil: 'domcontentloaded', timeout: 30000 });
                result.status = response.status();
                await page.waitForFunction(() => window.Alpine && [...document.querySelectorAll('[x-data]')].every(element => element._x_dataStack), null, { timeout: 10000 });
                await page.evaluate(async () => { await window.Alpine.nextTick(); await document.fonts.ready; });
                result.overflow = await page.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth + 2);
                result.deadLinks = await page.evaluate(() => [...document.querySelectorAll('a')].filter(link => link.getClientRects().length && (!link.getAttribute('href') || link.getAttribute('href') === '#')).map(link => link.textContent.trim()));
                if (path === 'dashboard') await page.screenshot({ path: `qa/dashboard-${width}.png`, fullPage: true });
            } catch (error) {
                result.failure = String(error);
            }
            results.push(result);
            await page.close();
        }
        await context.close();
    }
    await browser.close();
    fs.writeFileSync('qa/crawl.json', JSON.stringify(results, null, 2));
    const failures = results.filter(result => result.failure || result.status !== 200 || result.overflow || result.deadLinks.length || result.errors.length);
    console.log(JSON.stringify({ checks: results.length, failures }, null, 2));
    process.exitCode = failures.length ? 1 : 0;
})().catch(error => { console.error(error); process.exitCode = 1; });
