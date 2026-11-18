const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const base = process.env.PMS_QA_BASE || 'http://127.0.0.1:8099/';

(async () => {
    const browser = await chromium.launch();
    const results = [];
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 }, timezoneId: 'Africa/Kampala' });
    async function run(name, path, drive) {
        const page = await context.newPage();
        const errors = [];
        const result = { name, passed: false, errors };
        page.on('pageerror', error => errors.push(String(error)));
        page.on('console', message => { if (message.type() === 'error') errors.push(message.text()); });
        page.on('dialog', dialog => dialog.dismiss());
        try {
            const response = await page.goto(new URL(path, base).href, { waitUntil: 'domcontentloaded' });
            assert.equal(response.status(), 200);
            await page.waitForFunction(() => window.Alpine && [...document.querySelectorAll('[x-data]')].every(element => element._x_dataStack));
            await drive(page);
            await page.evaluate(() => window.Alpine.nextTick());
            assert.deepEqual(errors, []);
            result.passed = true;
        } catch (error) {
            result.failure = error.stack || String(error);
            await page.screenshot({ path: `qa/failure-${name}.png`, fullPage: true });
        }
        results.push(result);
        await page.close();
    }
    const visible = locator => locator.waitFor({ state: 'visible' });
    const model = (page, field) => page.locator(`[x-model="form.${field}"]`);
    const next = async page => {
        await page.getByRole('button', { name: /^(Next|Continue)/ }).filter({ visible: true }).click();
        await page.evaluate(() => window.Alpine.nextTick());
    };
    async function progress(page, step) {
        await page.waitForFunction(number => document.querySelector('[aria-label="Form progress"] [aria-current="step"]')?.textContent.trim() === String(number), step);
    }
    async function choose(page, field, value) {
        const control = model(page, field);
        if (await control.evaluate(element => element.tagName === 'SELECT')) {
            await control.selectOption(value || { index: 1 });
        } else {
            await control.fill(value);
        }
    }

    await run('dashboard-periods', 'dashboard', async page => {
        assert.match(await page.locator('[aria-label="Gender breakdown summary"]').innerText(), /60%/);
        assert.match(await page.locator('[aria-label="Gender breakdown summary"]').innerText(), /40%/);
        for (const period of ['last_month', 'last_3_months', 'this_year', 'this_month']) {
            await page.getByLabel('Select reporting period').selectOption(period);
            await page.waitForFunction(() => document.querySelector('[x-text="fmt(activeInmates)"]').textContent === '25');
            assert.equal(await page.locator('[x-text="fmt(newAdmissions)"]').textContent(), '0');
        }
        assert.equal(await page.locator('canvas').count(), 3);
    });

    await run('report-filters', 'reports/population', async page => {
        assert.equal(await page.locator('#rep-range').inputValue(), '2024');
        await page.locator('#rep-range').selectOption('2026');
        await page.locator('#rep-facility').selectOption('Kigo Prison');
        await page.getByRole('button', { name: 'Apply', exact: true }).click();
        await page.waitForURL(/range=2026/);
        assert.equal(await page.locator('#rep-range').inputValue(), '2026');
        assert.equal(await page.locator('#rep-facility').inputValue(), 'Kigo Prison');
        assert.match(await page.locator('main').innerText(), /Calendar 2026/);
    });

    await run('audit-filters', 'audit', async page => {
        await page.locator('#aq').fill('definitely-no-such-fixture');
        await visible(page.getByText('No events match the current filters.'));
        await page.getByRole('button', { name: 'Clear filters' }).click();
        await page.locator('#audit-from').fill('2099-01-01');
        await visible(page.getByText('No events match the current filters.'));
        await page.getByRole('button', { name: 'Clear filters' }).click();
        assert.equal(await page.locator('#audit-from').inputValue(), '');
        assert.equal(await page.locator('[x-text="filtered.length"]').textContent(), '18');
    });

    await run('visit-checkin', 'visitors/visits/1/check-in', async page => {
        const confirm = page.getByRole('button', { name: 'Confirm Check-in Preview' });
        assert.equal(await confirm.isDisabled(), true);
        for (const checkbox of await page.getByRole('checkbox').all()) await checkbox.check();
        await page.getByLabel('Arrival Time').fill('09:00');
        await confirm.click();
        await visible(page.getByText('Preview complete — nothing was recorded.', { exact: true }));
        assert.equal(await confirm.isDisabled(), true);
    });

    await run('account-invitation', 'users/new', async page => {
        assert.equal(await page.getByRole('button', { name: 'Submit Preview', exact: true }).isDisabled(), true);
        const first = page.getByRole('listbox', { name: 'Candidate officers' }).getByRole('option').first();
        assert.doesNotMatch(await first.innerText(), /undefined/);
        await first.click();
        await page.locator('#role').selectOption('records_officer');
        await page.locator('#email').fill('invalid');
        await page.locator('#email').blur();
        await visible(page.getByText('Enter a valid email address.'));
        await page.locator('#email').fill('fictional-officer@example.test');
        await page.locator('#email').blur();
        await model(page, 'confirmed').check();
        await page.getByRole('button', { name: 'Submit Preview', exact: true }).click();
        await visible(page.getByText('Preview submitted — no account exists'));
        await page.getByRole('button', { name: 'Invite Another' }).click();
        assert.equal(await page.locator('#email').inputValue(), '');
    });

    await run('account-status', 'users/3/status', async page => {
        assert.deepEqual(await page.locator('input[name="target"]').evaluateAll(inputs => inputs.map(input => input.value)), ['Suspended', 'Deactivated']);
        await page.getByRole('radio', { name: 'Suspended' }).check();
        await page.locator('#reason').fill('Fictional administrative review');
        await page.getByRole('checkbox').check();
        await page.getByRole('button', { name: 'Submit Preview' }).click();
        await visible(page.getByText('Preview submitted — nothing changed'));
    });

    await run('mixed-role-escalation', 'users/2/assign-role', async page => {
        assert.equal(await page.getByText('Manage personnel records', { exact: true }).count(), 0);
        await page.locator('#newrole').selectOption('auditor');
        await visible(page.getByText('permission increase — would require approval in production', { exact: true }));
        assert.match(await page.locator('[x-text="`${removed.length} removed / ${added.length} added`"] ').textContent(), /\d+ removed \/ 1 added/);
        await page.locator('#justification').fill('Fictional audit assignment for access review');
        await page.getByRole('checkbox').check();
        await page.getByRole('button', { name: 'Submit Preview' }).click();
        await visible(page.getByText('Preview submitted — role unchanged'));
    });

    await run('visit-schedule', 'visitors/schedule', async page => {
        await next(page);
        await visible(page.getByText('Select a visitor.', { exact: true }));
        await page.getByLabel('Search visitors').fill('definitely-no-such-visitor');
        await visible(page.getByText('No matching visitors.', { exact: false }));
        await page.getByLabel('Search visitors').fill('');
        await page.getByRole('listbox', { name: 'Visitor options' }).getByRole('option').first().click();
        await next(page);
        await progress(page, 2);
        await page.getByRole('listbox', { name: 'Inmate options' }).getByRole('option').first().click();
        await next(page);
        await progress(page, 3);
        await page.locator('#schedule-date').fill('2000-01-01');
        await page.locator('#schedule-time').selectOption('09:00');
        await next(page);
        await visible(page.getByText('Choose today or a later date.'));
        const today = await page.locator('#schedule-date').getAttribute('min');
        await page.locator('#schedule-date').fill(today);
        await next(page);
        await progress(page, 4);
        await page.locator('#schedule-purpose').selectOption({ index: 1 });
        await next(page);
        await progress(page, 5);
        await page.getByRole('button', { name: /Previous/ }).click();
        await progress(page, 4);
        await next(page);
        assert.equal(await page.getByRole('button', { name: 'Submit Preview' }).isDisabled(), true);
        await model(page, 'confirmed').check();
        await page.getByRole('button', { name: 'Submit Preview' }).click();
        await visible(page.getByText('Preview submitted — nothing was scheduled'));
        await page.getByText('Step 5 — Review Request', { exact: true }).waitFor({ state: 'hidden' });
        await page.getByRole('button', { name: 'Schedule Another' }).click();
        await progress(page, 1);
        assert.equal(await page.getByLabel('Search visitors').inputValue(), '');
        await page.setViewportSize({ width: 390, height: 844 });
        await page.evaluate(() => { window.scrollTo(0, 0); return new Promise(resolve => requestAnimationFrame(resolve)); });
        await page.screenshot({ path: 'qa/schedule-mobile.png', fullPage: true });
    });

    await run('schedule-conflict-warning', 'visitors/schedule', async page => {
        const data = JSON.parse(await page.locator('#sched-data').textContent());
        assert.equal(Array.isArray(data.upcoming), true);
        const visit = data.upcoming.find(visit => data.inmates.some(inmate => inmate.id === visit.inmate_id));
        assert.ok(visit);
        const visitor = data.visitors.find(visitor => visitor.id === visit.visitor_id);
        const inmate = data.inmates.find(inmate => inmate.id === visit.inmate_id);
        await page.getByLabel('Search visitors').fill(visitor.visitor_ref);
        await page.getByRole('listbox', { name: 'Visitor options' }).getByRole('option').first().click();
        await next(page);
        await page.getByLabel('Search inmates').fill(inmate.prisoner_number);
        await page.getByRole('listbox', { name: 'Inmate options' }).getByRole('option').first().click();
        await next(page);
        await progress(page, 3);
        await page.locator('#schedule-date').fill(visit.visit_date);
        await page.locator('#schedule-time').selectOption(visit.start_time);
        await visible(page.getByText('Potential scheduling conflicts at this facility and time:', { exact: true }));
        await visible(page.locator('[x-show="slotConflicts.length>0"]').getByText(visit.visit_ref, { exact: true }));
    });

    await run('admission-wizard', 'inmates/new', async page => {
        await next(page);
        await visible(page.getByText('This field is required.').first());
        for (const [field, value] of Object.entries({ first_name: 'Fictional', last_name: 'Preview', gender: 'Male', date_of_birth: '2000-01-01' })) await choose(page, field, value);
        await next(page);
        await progress(page, 2);
        for (const [field, value] of Object.entries({ prisoner_number: 'DEMO-QA-0001', admission_ref: 'DEMO-ADM-QA', classification: '', legal_basis: 'Fictional preview court order', warrant_ref: 'DEMO-WARRANT-QA' })) await choose(page, field, value);
        await next(page);
        await progress(page, 3);
        await choose(page, 'facility');
        await next(page);
        await next(page);
        await progress(page, 5);
        await model(page, 'confirmed').check();
        await page.getByRole('button', { name: 'Preview Admission Record' }).click();
        await visible(page.getByText('Admission preview complete.', { exact: true }));
    });

    await run('transfer-wizard', 'transfers/new?inmate_id=1', async page => {
        await progress(page, 2);
        await next(page);
        await visible(page.getByText('Please select a destination facility.'));
        const origin = await model(page, 'origin_facility').count() ? await model(page, 'origin_facility').inputValue() : await page.locator('input[disabled]').first().inputValue();
        const destination = await page.locator('#destination option').evaluateAll((options, source) => options.find(option => option.value && option.value !== source && !option.disabled)?.value, origin);
        await page.locator('#destination').selectOption(destination);
        await page.locator('#reason').selectOption({ index: 1 });
        const today = new Intl.DateTimeFormat('en-CA', { timeZone: 'Africa/Kampala', year: 'numeric', month: '2-digit', day: '2-digit' }).format(new Date());
        await page.locator('#proposed_date').fill(today);
        await next(page);
        await next(page);
        await progress(page, 4);
        await page.locator('#confirm_review').check();
        await page.getByRole('button', { name: /^Continue/ }).click();
        await progress(page, 5);
        await page.getByRole('button', { name: /Previous/ }).click();
        await progress(page, 4);
        await page.getByRole('button', { name: /^Continue/ }).click();
        await page.getByRole('button', { name: 'Preview Transfer Request' }).click();
        await visible(page.locator('[x-show="submitted"]'));
    });

    await run('personnel-wizard', 'personnel/new', async page => {
        for (const [field, value] of Object.entries({ first_name: 'Fictional', last_name: 'Officer', gender: 'Female' })) await choose(page, field, value);
        await next(page);
        await progress(page, 2);
        await choose(page, 'staff_number', 'DEMO-QA-OFFICER');
        await choose(page, 'rank');
        await choose(page, 'department');
        await next(page);
        await next(page);
        await next(page);
        await progress(page, 5);
        await page.getByRole('button', { name: /Previous/ }).click();
        await progress(page, 4);
        await next(page);
        await page.locator('#confirm_reg').check();
        await page.getByRole('button', { name: 'Preview Registration' }).click();
        await visible(page.getByText('Preview complete.', { exact: true }));
    });

    await run('visitor-registration', 'visitors/new', async page => {
        for (const [field, value] of Object.entries({ first_name: 'Fictional', last_name: 'Visitor', gender: 'Female' })) await choose(page, field, value);
        await next(page);
        await progress(page, 2);
        await choose(page, 'phone', 'DEMO-PHONE-QA');
        await choose(page, 'residence', 'Fictional demonstration residence');
        await next(page);
        await choose(page, 'relationship');
        await next(page);
        await next(page);
        await progress(page, 5);
        await model(page, 'confirmed').check();
        await page.getByRole('button', { name: 'Submit Preview' }).click();
        await visible(page.getByText('Preview submitted — nothing was saved'));
        await page.getByRole('button', { name: 'Register Another' }).click();
        await progress(page, 1);
    });

    await run('discharge-wizard', 'inmates/1/discharge', async page => {
        assert.equal(await page.getByRole('button', { name: /^Continue/ }).filter({ visible: true }).isDisabled(), true);
        await page.locator('#confirm_identity').check();
        await next(page);
        await progress(page, 2);
        await next(page);
        await progress(page, 3);
        await page.locator('#discharge_reason').selectOption({ index: 1 });
        await next(page);
        await progress(page, 4);
        await page.locator('#auth_ref').fill('DEMO-AUTH-QA');
        await page.locator('#auth_authority').fill('Fictional preview authority');
        await next(page);
        await progress(page, 5);
        await page.locator('#final_confirm').check();
        await page.getByRole('button', { name: /Preview Discharge/ }).click();
        await visible(page.locator('[x-show="submitted"]'));
    });

    await run('mobile-navigation', 'dashboard', async page => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.getByRole('button', { name: 'Open navigation menu' }).click();
        const close = page.getByRole('button', { name: 'Close sidebar' });
        await close.waitFor({ state: 'visible' });
        assert.equal(await page.locator('main').evaluate(element => Boolean(element.closest('[inert]'))), true);
        await page.keyboard.press('Shift+Tab');
        assert.equal(await page.evaluate(() => Boolean(document.activeElement.closest('#sidebar'))), true);
        await page.keyboard.press('Tab');
        assert.equal(await close.evaluate(element => element === document.activeElement), true);
        await page.keyboard.press('Escape');
        assert.equal(await page.getByRole('button', { name: 'Open navigation menu' }).getAttribute('aria-expanded'), 'false');
        await page.waitForFunction(() => document.activeElement.getAttribute('aria-label') === 'Open navigation menu');
        await page.getByRole('button', { name: 'Open navigation menu' }).click();
        await page.setViewportSize({ width: 1440, height: 900 });
        await page.waitForFunction(() => !document.querySelector('main').closest('[inert]'));
    });

    await browser.close();
    fs.writeFileSync('qa/interactions.json', JSON.stringify(results, null, 2));
    console.log(JSON.stringify(results, null, 2));
    process.exitCode = results.some(result => !result.passed) ? 1 : 0;
})().catch(error => { console.error(error); process.exitCode = 1; });
