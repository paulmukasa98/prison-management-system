# PMS frontend handoff

Historical frontend handoff: 12 November 2026. See the backend checkpoint and deployment/recovery procedure at the end of this file for the 17 November 2026 implementation. This remains an academic project without institutional production approval.

## Historical frontend delivery boundary (12 November)

The application uses Laravel 13.40, PHP 8.5, Blade, Tailwind 4, Alpine 3, Chart.js and Vite 8. Existing navigation and form previews must remain stable during backend implementation. No production data, operational approval, credential verification, persistent custody operations, export authorization, or offline synchronization is delivered by these previews. Do not publish this unauthenticated preview with real records.

At that checkpoint, frontend stabilization and automated QA were delivered for frontend acceptance and backend phases B1–B8 were unimplemented. The backend checkpoint below supersedes that implementation status. User authorization to continue development is not UPS operational signoff or production acceptance.

## Architecture and inventory

- `routes/web.php`: 59 GET route patterns, including the root redirect. Numeric business IDs and six allowed print-report names are constrained.
- `app/Http/Controllers`: Dashboard, Inmate, Transfer, Personnel, Facility, Visitor, Report, User, Audit, Settings and Auth/Auth controllers.
- `resources/views/layouts/app.blade.php`: application navigation, context, account menu and connection indicator. Guest layout hosts authentication previews. Report print previews use a separate printable layout.
- `resources/views/components`: reusable cards, badges, tables, inputs, alerts, breadcrumbs, timelines, verification summaries, occupancy bars and static/reactive step progress.
- `app/Support/Demo`: Inmate, Transfer, Personnel, Assignment, Facility, Visitor, Visit, User, Role, Audit and Report fixture providers.
- `app/Support/DemoContext.php`: fixture-derived dashboard periods, current headcounts, facility totals and recent audit illustrations. Shared by AppServiceProvider.
- `resources/js/app.js`: Alpine registration, navigation and Chart.js dashboard behavior. Screen-specific Alpine factories remain in their Blade screens.
- `qa/routes.json`: exact Artisan route inventory with names/actions. `qa_urls.txt`: concrete URLs used by the browser crawl.
- `qa/crawl.cjs`: actual HTTP status, runtime errors, visible dead links and document overflow at 1440/768/390px.
- `qa/interactions.cjs`: browser-driven filters, account previews, six multi-step workflows, visit check-in and mobile keyboard navigation.

## File and navigation inventory

All names in the following table are relative to `resources/views/` and end in `.blade.php`; 103 templates are present, including 36 components.

| Directory | Templates |
| --- | --- |
| Root / layouts | `welcome` (unused scaffold); `layouts/app`, `layouts/guest` |
| auth | `login`, `forgot-password`, `reset-password` |
| dashboard / settings / audit | `dashboard/index`; `settings/index`; `audit/index`, `audit/show` |
| inmates | `index`, `create`, `show`, `edit`, `admissions`, `discharges`, `discharge-initiate` |
| transfers | `index`, `create`, `show`, `review`, `dispatch`, `receive`, `history` |
| personnel | `index`, `create`, `show`, `edit`, `assignments`, `assignment-create`, `assignment-history` |
| facilities | `index`, `create`, `show`, `edit`, `capacity`, `personnel` |
| visitors | `index`, `create`, `show`, `edit`, `records`, `schedule`, `visit-show`, `check-in`, `check-out`, `cancel` |
| reports | `index`, `population`, `movement`, `transfers`, `visitation`, `personnel`, `occupancy`, `print` |
| reports/print | `population`, `movement`, `transfers`, `visitation`, `personnel`, `occupancy` |
| users | `index`, `create`, `show`, `status`, `assign-role`, `roles` |
| components/button | `primary`, `secondary`, `danger` |
| components/form | `input`, `select`, `textarea`, `search` |
| components | `card`, `badge`, `alert`, `modal`, `table`, `empty-state`, `page-heading`, `loading-spinner`, `placeholder-module`, `inmate-status-badge`, `custody-badge`, `detail-row`, `section-heading`, `activity-timeline`, `transfer-status-badge`, `facility-movement`, `transfer-timeline`, `personnel-status-badge`, `facility-status-badge`, `occupancy-bar`, `assignment-status-badge`, `visit-status-badge`, `visit-timeline`, `breadcrumb`, `schedule-summary`, `account-status-badge`, `stats-card`, `report-filters`, `step-indicator` |

Controller inventory under `app/Http/Controllers/`: base `Controller`; `Auth/AuthController`; `DashboardController`, `InmateController`, `TransferController`, `PersonnelController`, `FacilityController`, `VisitorController`, `ReportController`, `UserController`, `AuditController`, `SettingsController`.

Fixture inventory under `app/Support/Demo/`: `InmateDemoData`, `TransferDemoData`, `PersonnelDemoData`, `AssignmentDemoData`, `FacilityDemoData`, `VisitorDemoData`, `VisitDemoData`, `UserDemoData`, `RoleDemoData`, `AuditDemoData`, `ReportDemoData` (all `.php`). `app/Support/DemoContext.php` provides shared context.

Status definitions: `InmateDemoData::$statuses` / `$classifications` / `$dischargeReasons`; `TransferDemoData::STATUSES`; `PersonnelDemoData::EMPLOYMENT_STATUSES`; `AssignmentDemoData::STATUSES`; `FacilityDemoData::STATUSES`; `VisitorDemoData::VISITOR_STATUSES`; `VisitDemoData::STATUSES`; `UserDemoData::ACCOUNT_STATUSES`; `AuditDemoData::OUTCOMES`. Role grants are in `RoleDemoData`; badge mappings are in the matching components above. These catalogs describe previews, not approved state-transition policies.

Sidebar links in `layouts/app`: `dashboard`; `inmates.index`, `inmates.create`, `inmates.discharges`; `transfers.index`, `transfers.history`; `personnel.index`, `personnel.assignments`; `facilities.index`; `visitors.index`, `visitors.records`, `visitors.schedule`; `reports.index`; `audit.index`; `users.index`, `users.roles.index`; `settings.index`. The account dropdown also links `settings.index`; profile/logout are disabled, not dead anchors. Collapsed navigation stores only non-sensitive presentation state in sessionStorage.

## Route inventory

All entries below are GET previews. There are no business POST/PATCH/DELETE endpoints yet.

| Area | Paths | Route-name prefix |
| --- | --- | --- |
| Entry | `/`, `/dashboard` | unnamed root redirect; `dashboard` |
| Authentication | `/auth/login`, `/auth/forgot-password`, `/auth/reset-password` | `login`, `password.request`, `password.reset` |
| Inmates | `/inmates`, `/inmates/new`, `/inmates/discharges`, `/inmates/{id}`, `/inmates/{id}/edit`, `/inmates/{id}/admissions`, `/inmates/{id}/discharge` | `inmates.*` |
| Transfers | `/transfers`, `/transfers/new`, `/transfers/history`, `/transfers/{id}`, `/transfers/{id}/review`, `/transfers/{id}/dispatch`, `/transfers/{id}/receive` | `transfers.*` |
| Personnel | `/personnel`, `/personnel/new`, `/personnel/assignments`, `/personnel/assignments/new`, `/personnel/{id}`, `/personnel/{id}/edit`, `/personnel/{id}/assignments` | `personnel.*` |
| Facilities | `/facilities`, `/facilities/new`, `/facilities/capacity`, `/facilities/{id}`, `/facilities/{id}/edit`, `/facilities/{id}/personnel` | `facilities.*` |
| Visitors | `/visitors`, `/visitors/new`, `/visitors/records`, `/visitors/schedule`, `/visitors/{id}`, `/visitors/{id}/edit` | `visitors.*` |
| Visit occurrences | `/visitors/visits/{id}`, `/visitors/visits/{id}/check-in`, `/visitors/visits/{id}/check-out`, `/visitors/visits/{id}/cancel` | `visitors.visits.*` |
| Reports | `/reports`, `/reports/population`, `/reports/movement`, `/reports/transfers`, `/reports/visitation`, `/reports/personnel`, `/reports/occupancy`, `/reports/{report}/print` | `reports.*` |
| Accounts | `/users`, `/users/roles`, `/users/new`, `/users/{id}`, `/users/{id}/status`, `/users/{id}/assign-role` | `users.*` |
| Audit | `/audit`, `/audit/{id}` | `audit.*` |
| Settings | `/settings` | `settings.index` |

`{report}` allows population, movement, transfers, visitation, personnel and occupancy. Missing numeric entities currently redirect with a preview error message; backend route binding should adopt an explicit scoped not-found contract.

## Form-to-backend contracts

These are proposed future web endpoints, not existing capabilities. Preserve GET navigation; add validated, CSRF-protected writes through FormRequests, policies and transactional services. If a separate API is introduced, version it rather than exposing preview arrays.

| Page/form | Proposed write | Entities | Required boundary checks | Proposed permission | Audit events |
| --- | --- | --- | --- | --- | --- |
| Login/logout | `POST /auth/login`, `POST /auth/logout` | User, session | hashed password, active status, rate limit, session rotation/invalidation, CSRF | authenticated account | authentication success/failure, logout |
| Password recovery | password email/reset POST handlers | User, reset token | generic response, token expiry/single use, password policy, throttling | token-based recovery | reset requested/completed without secrets |
| Account invitation | `POST /users` | User, Personnel, role/scope assignments | unique normalized email, active unlinked personnel, permitted grant/scope, expiring invitation; no administrator-assigned password | `users.manage` | invitation created/redeemed |
| Account status | `PATCH /users/{id}/status` | User, sessions | allowed transition, reason, protect last administrator, review self-targeting, invalidate sessions | `users.manage` | requested/changed/denied |
| Role proposal | `POST /users/{id}/role-requests` | Role/Permission, User, approval | default deny, scope, separation of duties, every newly added grant counts as escalation | `roles.assign` | requested/approved/denied with grant diff |
| Inmate admission | `POST /inmates` and admission-episode endpoint | Inmate identity, AdmissionEpisode, LegalReference, Facility | identifiers distinct, dates, authorized legal basis, duplicate review, active-episode invariant, private document metadata | `inmates.admit` | identity/admission created |
| Inmate correction | `PATCH /inmates/{id}` | Inmate, legal/custody history | scoped access, correction reason, preserved historical identity; no silent replacement of legal authority | `inmates.edit` | correction with safe field diff |
| Discharge | `POST /inmates/{id}/discharges` | AdmissionEpisode, release authority, document | active custody, verified legal authority, required reason, transaction, double-submit protection; second approval unresolved | `inmates.discharge` | requested/authorized/executed/denied |
| Transfer request | `POST /transfers` | Transfer, inmate/admission, origin/destination | correct origin, distinct active destination, current eligibility, reason/date, idempotency | `transfers.request` | transfer requested |
| Transfer review | `POST /transfers/{id}/reviews` | Transfer, transition event | authorized actor/scope, legal transition, reason, independent approver where required | `transfers.review`, `transfers.authorize` | clarification/approval/rejection |
| Dispatch/receipt | transition POST handlers | Transfer, event, custody location | current state, actor scope, lock/version check, duplicate receipt prevention, atomic location update on receipt | `transfers.dispatch`, `transfers.receive` | dispatch/receipt recorded |
| Personnel create/edit | `POST /personnel`, `PATCH /personnel/{id}` | Personnel | staff reference uniqueness, employment/date/contact validation, sensitive field policy; account creation is separate | `personnel.manage` | personnel created/corrected |
| Posting | `POST /personnel/assignments` | PersonnelAssignment, Personnel, Facility | dates, overlap policy, active employment/facility, preserve completed history | `personnel.manage` | posting created/ended |
| Facility create/edit | `POST /facilities`, `PATCH /facilities/{id}` | Facility, capacity history | unique code, active/closed state, nullable capacity distinct from zero, authority and effective date | `facilities.manage` | facility/capacity changed |
| Visitor registration/edit | `POST /visitors`, `PATCH /visitors/{id}` | Visitor, private documents | identity verification, duplicate review, contact validation, scoped sensitive access, retention | `visitors.register` | visitor registered/corrected |
| Schedule | `POST /visitors/visits` | Visit, Visitor, Inmate/admission, Facility | visitor and inmate eligible, facility match, validated hours/duration, overlap policy, reservation lock, idempotency | `visits.schedule` | requested/scheduled/denied |
| Check-in/out/cancel | visit transition POST handlers | Visit, event/pass metadata | legal transition, scope, verified identity, arrival/departure ordering, required reason for cancellation | `visits.checkinout` or separate validated cancel grant | checked in/out/cancelled |
| Reports/print/export | scoped GET reports; audited export endpoint | reporting projections, AuditEvent | scope before aggregation, filter validation, private fields removed, spreadsheet-safe CSV, export authorization | `reports.view`, `reports.export` | report viewed/export requested/completed/denied |
| Audit directory/detail | scoped GET endpoints | append-only AuditEvent | separate audit grant, redaction, immutable actor/time/correlation, retention, tamper evidence | `audit.view` | sensitive log read/export |
| Settings | settings PATCH endpoint | approved configuration | explicit administrative grant, validated values, no secret display, audited change | unresolved; do not reuse an unrelated grant | configuration changed |

## Fixture reconciliation and reporting meanings

Current fixtures contain 30 inmate identities, 25 active inmates, 35 personnel records (32 active), 40 posting records, 8 facilities, 25 visitors, 45 visit occurrences, 10 accounts, 6 proposed roles and 18 audit illustrations. Pending transfers are derived from Pending Approval/Awaiting Clarification rather than an invented headline.

Dashboard gender is 15 male / 10 female (60% / 40%); custody is 14 convicted / 9 remand / 2 civil debtor (56% / 36% / 8%). These are preview categories, not verified official definitions.

- Current headcounts and facility occupancy do not change when dashboard event-period filters change. They are explicitly current, not historical snapshots.
- Admissions and discharges come from dated fixture records; completed visits use actual fixture occurrence dates and terminal status. This Month and This Year end at the current date, not a fabricated historical month.
- Calendar reports default to 2024. Invalid filters normalize both displayed selection and dataset to the same fallback.
- Report movement series respect facility filters. All-time series span actual fixture event months rather than discarding historical events in favor of future empty years.
- Population snapshots rely on fixture admission/discharge dates but do not reconstruct historical transfer location, repeated admissions or posting history reliably. B1/B3/B4/B7 must replace that approximation with episode and movement history.
- Current occupancy uses current fixtures. Unknown capacity remains null, never silently zero. Capacity figures are fictional.
- Accounts, employment records and posting records are separate entities. Employment changes must not silently grant login rights.

## States and history

Central definitions currently live in the respective fixture providers and status badge components. Replace them with server-owned state models once operational rules are validated; never trust browser state or client-supplied actor/time.

- Inmate identity is distinct from admission episode. Discharge closes an episode without deleting identity/legal history. Transfers change physical custody location only after authorized receipt.
- Transfer request/review/clarification/authorization/dispatch/receipt/rejection/cancellation events must be retained, scoped and ordered. Final transition authority remains unverified.
- Personnel employment state is distinct from posting state and account state. Posting history must survive reassignment.
- Visitor identity is distinct from visit occurrence. Requested/review/scheduled/checked-in/terminal outcomes require explicit transition rules.
- Account statuses are Active, Invited, Suspended and Deactivated. Restoration is a proposed administrator-reviewed action, not a verified UPS policy; invitation redemption is not an ordinary Active status toggle.
- Audit outcomes are Success, Failed and Denied; audit events must not be editable business records.

## Privacy, security and offline boundaries

Fixtures are declared fictional, but realistic names, phone/address shapes and UPS-style email domains are not proof that no real person could match them. Human provenance review is still required before external sharing. Do not send development invitations or treat fixture addresses as authorized recipients. No passwords are represented in account fixture arrays. Standard scaffolding factories/seeding are not a production administrator provisioning strategy.

There is no authentication or RBAC enforcement in preview routes. Disabled account/global-search/notification/settings controls communicate unavailable behavior. The connection badge reflects `navigator.onLine`, not server health, authorized offline access, pending synchronization or durable storage.

No custody release, transfer approval/receipt, account/role changes, visitor eligibility decision or sensitive export may become an offline queued operation without an approved threat model. Default to blocked writes when offline. Do not cache identity documents, tokens, legal records or raw personal datasets in localStorage. Future permitted drafts require encryption, expiry, bounded storage, signed actor context, idempotency, conflict review and revocation handling.

## Verification evidence

- `composer show --direct`: Laravel 13.40.0, Pint 1.32.1, PHPUnit 12.5.43; no dependency changes made for stabilization.
- `npm run build`: passed on 12 November 2026.
- `php artisan view:cache --no-interaction`: passed on 12 November 2026.
- Final desktop/tablet/mobile crawl passed: 177 navigations (59 routes × 1440/768/390px), all final HTTP responses 200, no document overflow, visible dead anchors or captured JavaScript errors. Hidden accessibility labels are contained by the roles matrix scroll wrapper, not by hiding document overflow. Evidence: `qa/crawl.json`.
- Sequential targeted regression verification passed: 34 tests, 345 assertions (200.56 seconds). Covers GET route patterns, account/report previews, snapshot-date age bands, facility-scoped ratios/series and fixture invariants. The earlier concurrent-cache Windows rename failure was resolved by sequential execution, without changing permissions. Final full suite passed: 52 tests, 485 assertions (47.10 seconds). `vendor/bin/pint --dirty --format agent`, `php artisan optimize:clear --no-interaction`, and subsequent `php artisan view:cache --no-interaction` passed.
- All 15 actual browser scenarios passed with no captured console/page errors. Includes required/past-date/invalid-email/empty-filter states, scheduling conflict warnings for the same visitor, reactive progress and review back-navigation, six multi-step flows, check-in, account previews and mobile focus trapping/restoration/resizing. Results: `qa/interactions.json`; driver: `qa/interactions.cjs`.
- `qa/dashboard-1440.png` and `qa/dashboard-390.png`: full-page browser captures inspected; gender and custody percentages reconcile. Earlier `screenshots/` captures predate stabilization.
- GET route regression: `tests/Feature/FrontendRouteTest.php` covers application patterns and invalid route constraints; existing tests cover visitor and account/report previews and fixture linkage.

## Historical frontend risks and acceptance (12 November)

1. **Production blocker:** real database workflows, authentication, scoped default-deny RBAC, transaction locks, legal release authority, private uploads, audited exports, immutable audit and security/deployment hardening are not implemented (B1–B8).
2. **Operational blocker:** identifier formats, legal custody definitions, approver separation, restoration policy, posting overlaps, visit hours/durations/eligibility, cancellation permission and retention rules need UPS validation.
3. **Privacy blocker:** fixture provenance review and document-storage/retention approval are not completed.
4. **Reporting limitation:** historical facility population and repeated-admission reporting need persisted episodes/movement history; preview snapshots must not be represented as official reports.
5. **Acceptance and repeatability:** automated QA passed; frontend acceptance remains open. Browser scenarios cover representative paths, not every combination or a certified WCAG audit. QA drivers currently use locally cached Playwright without adding a project dependency; CI setup needs approved dependency/tooling configuration.
6. **Accessibility follow-up:** audit label associations/focus/error announcements across every legacy form, screen-reader testing and contrast review before acceptance. Color-independent status text and reactive progress are present; that is not a certification.

Next development phase is B1 relational schema, constraints, factories and idempotent fictional seeding, without replacing previews or resetting an existing database. No production launch is authorized by this document.


## Backend implementation checkpoint — 17 November 2026

The frontend inventory above is historical. The application now uses real database operations for visitors, reports, audit, user administration, inmates, transfers, personnel and facilities. The user supplied B1–B5 as the accepted starting checkpoint. Work continued from B6 and corrected integration and authorization defects discovered in the earlier modules.

B6 separates visitor identities from admission-linked visit occurrences. Its lifecycle is Requested → Approved → Scheduled → Checked In → Completed, with denial and cancellation paths. Visit approval requires the separate `visits.authorize` permission; registration or reception authority alone does not approve a visit. Registration has a facility context, identity documents are encrypted and displayed masked, document fingerprints prevent duplicates, and corrections/redaction are audited. Scheduling and transitions lock custody episodes and visitor rows. Transfer dispatch and discharge cancel pending visits and refuse custody movement while a visitor remains checked in.

B7 uses database aggregates for the six reports and the dashboard. Population snapshots follow recorded custody locations at the selected time; missing and zero capacity are distinct; capacity records use their effective dates. Reports and exports enforce permission-specific facility scopes, including the intersection of read and export scopes. Reports exclude contact details and document numbers. Printing requires export authority. CSV downloads are implemented but disabled until explicitly configured with `REPORT_CSV_EXPORTS_ENABLED`. CSV cells are protected against spreadsheet formula interpretation. Staffing uses posting dates and current employment status; historical employment status cannot be reconstructed from the existing schema.

Audit records and visit/transfer events have database controls that reject updates and deletes. New audit records carry keyed integrity signatures and request correlation IDs. Historical events are retained unsigned: `audit:verify --allow-legacy` reports them separately and does not manufacture historical integrity evidence. Audit views are read-only, facility scoped and redact credential/contact metadata. Key retention and independent archival remain operational responsibilities.

B8 adds private/no-store responses, framing/content-type restrictions, correlation IDs, production cookie/session encryption defaults, offline form submission blocking, a production approval gate, readiness checks and bounded encrypted backup tooling. No sensitive offline storage, synchronization queue, external justice-system integration or production deployment has been enabled. Existing account provisioning and role/status endpoints now enforce national management authority; changes revoke sessions and preserve role history. Demo seeding is restricted to local/testing and no longer creates accounts with a fixed shared password.

### Local operation and recovery

Use PHP 8.5. On this workstation, `artisan.bat` already selects that runtime; the default XAMPP `php` command may point to an older version.

- `php artisan pms:readiness` checks deployment configuration and approval references without printing secrets. A failure in the development environment is expected.
- `php artisan audit:verify --allow-legacy` checks new audit signatures and reports unsigned history.
- `php artisan pms:backup --database=mysql` writes an AES-256-GCM encrypted backup under `storage/app/private/backups`. Set a separate base64-encoded 32-byte `PMS_BACKUP_KEY` through secret management. Preserve it separately from the backup and never commit it.
- `php artisan pms:backup-verify <filename.pmsbackup>` authenticates/decrypts a backup and checks its SQL checksum without exposing its contents.
- A restore drill additionally requires the testing environment and `--restore-test --confirm-database=ups_pms_b1_test`. It restores only into the existing guarded disposable database on port 3311. Never run it concurrently with the test suite. Operational databases cannot be selected as restore targets by this command.
- The application backup command is capped at 32 MiB of SQL to bound memory. Larger installations require an approved infrastructure backup service with off-host retention and monitored restore drills.
- Scheduled application backups remain disabled unless `PMS_SCHEDULED_BACKUPS=true`; the deployment scheduler must run every minute. Audit verification is registered daily.
- Local recovery testing uses a private, uncommitted key file at `storage/app/private/local-backup-key`. This is local test material, not a production secret-management arrangement.

### Deployment and rollback procedure

1. Obtain approved numbering, visitor eligibility/hours/limits, approval authority, retention and access rules. Complete security review and user acceptance. Record real approval references; placeholders are not signoff.
2. Provision HTTPS, a least-privilege MySQL account, durable storage, protected secrets, a delivery mailer, queue workers, scheduler, monitoring and off-host backups. Configure production environment values, `APP_DEBUG=false`, secure/encrypted sessions, and the new settings listed in `.env.example`.
3. Install the locked PHP/JS dependencies, build assets, take and verify an encrypted backup, then place the application in maintenance mode. Apply migrations with `php artisan migrate --force --no-interaction`; cache configuration/routes/views; restart workers. Check `pms:readiness`, authentication, facility isolation and representative workflows before opening access.
4. Set production approval only after authorized institutional signoff. Production requests are refused while `PMS_PRODUCTION_APPROVED=false`. The configuration check is not penetration testing or institutional approval.
5. Prefer forward fixes. For rollback, keep maintenance mode active and restore an approved snapshot together with its matching application version and keys using the reviewed infrastructure recovery procedure. Rehearse in isolation first. Do not blindly roll back the B6 encryption migration or remove B7 append-only controls on a live database.
6. Monitor health, failed jobs, application errors, audit verification and backup results. Define named incident owners and escalation channels before launch. No institutional contacts or deployment destination have been supplied or assumed.

### Operator training and acceptance

Registrars search for an existing visitor before creating an identity. Schedulers select the current custody episode and facility and propose a time window. An authorized approver reviews eligibility; a scheduler confirms the approved window. Reception verifies identity and records check-in/check-out. Cancellation and corrections require reasons. A changed custody location requires a new visit request; historical visit references remain unchanged.

Administrators provision login accounts separately from personnel records, select scope explicitly and communicate initial credentials through an approved secure channel. Status/role changes revoke sessions. Report users select dates and authorized facilities; export permissions are separately enforced. Auditors inspect immutable event metadata and signature status.

| Acceptance area | Required evidence | Status |
|---|---|---|
| Institutional visitor and numbering rules | Authorized policy references | Pending |
| Browser and device acceptance | Desktop/tablet/mobile workflow checks | Pending current backend browser acceptance |
| Security acceptance | Independent review, dependency audit and infrastructure checks | Pending |
| Recovery acceptance | Verified isolated restore plus approved off-host production procedure | Local encrypted restore and reconciliation passed 17 November; production procedure pending |
| Production launch | Authorized deployment destination, UAT and institutional signoff | Not authorized |

Asset installation/build and dependency advisory checks were attempted locally but registry requests were blocked by the network sandbox. Existing built assets remain available; current browser behavior and a fresh asset build must be checked in an environment with dependency access.

### Current backend UAT script (execution pending)

Run with fictional records in a designated acceptance environment and record tester, date, evidence and outcome for each case. Use separate registrar, approver, reception, reporting, auditor and national administrator accounts with explicitly assigned facility scopes. The expectations below describe intended behavior, not completed institutional acceptance.

| Case | Actions | Expected result |
|---|---|---|
| Identity registration | Register a visitor at the assigned facility; repeat the document; open another facility's restricted record | First registration persists, duplicate is rejected, restricted record is unavailable, identity numbers remain masked |
| Visit approval | Request a future visit for an active admission; attempt approval as reception staff; approve as the designated approver; schedule | Reception approval is denied; authorized approval and scheduling persist with actor/event history |
| Reception | Attempt early check-in, check in during the configured window with identity verification, repeat check-in, then check out twice | Window and verification rules apply; exactly one check-in and one completion persist |
| Custody movement | Attempt dispatch or discharge with an active checked-in visitor; check out and perform an otherwise authorized movement | Checked-in presence blocks movement; eligible pending visits are cancelled when custody moves; previous history remains |
| Scope and reports | Select facility/date filters; compare recorded admissions, locations, transit, visits and capacities with report totals | Scope is enforced; transit counts toward custody but not occupancy; missing capacity stays unknown |
| Export control | Try print/CSV without export authority; grant the correct scope; enable approved CSV configuration | Unauthorized exports fail; configured exports contain only permitted aggregates and create audit events |
| Account administration | Create a personnel record, provision its separate login as national administrator, then suspend or change roles | Personnel creation grants no login; authorized provisioning works; status/role changes revoke sessions and preserve history |
| Audit review | Open permitted and restricted audit records; inspect request references and integrity status | Permitted history is read-only; restricted history is unavailable; legacy unsigned history is identified explicitly |
| Connectivity | Disconnect during a form workflow and attempt submission, then reconnect and submit once | Offline submission requires reconnection; no sensitive browser queue silently applies a change |
| Recovery | Have the recovery operator authenticate a backup and restore it into the approved isolated destination | Source remains untouched; schema and record counts reconcile; keys, duration and evidence are recorded privately |

Institutional signoff must also cover numbering, visitor rules, retention/redaction, segregation of duties, accessibility/device coverage and incident ownership. Actual security testing, delivery-mail testing, capacity/load tests and infrastructure recovery remain separate acceptance evidence.

### Final local validation — 17 November 2026

The resumed full regression run completed successfully: **232 tests passed, 1,311 assertions, zero failures, errors or skipped tests**, in 592.98 seconds. Evidence is stored locally in `storage/logs/implementation-resumed-tests.log`, `implementation-resumed-tests.xml` and `implementation-resumed-events.log`. The interrupted earlier run is not used as final evidence.

Coverage includes B1–B5 regression, authorization and facility isolation, the complete visitor lifecycle, encrypted identity privacy, overlapping schedules, custody-change cancellation, report reconciliation (including transit), export controls, immutable/signed audit behavior, account/session administration and readiness controls. The separate-process concurrency test passed simultaneous visit requests, check-ins and check-outs with one accepted action and one rejected duplicate each. Twelve existing operational forms rendered with real submission actions and passed Node JavaScript syntax checks; this does not substitute for browser interaction testing.

Pint passed on the changed PHP paths. The required `--dirty` mode could not identify changes because this workspace has no Git repository; explicit paths were formatted instead. `optimize:clear`, `route:list --except-vendor` (93 application routes) and `view:cache` passed. B6/B7 migrations were applied to the operational database after taking an encrypted backup and also exercised by disposable rollback/reapply tests.

The fresh backup `20261117-061154-f059ee45-f604-4395-ae67-77549e6df2ba.pmsbackup` authenticated successfully and restored into the guarded `b1_test` database on port 3311. SQL payload size was 742,356 bytes. Read-only source/target reconciliation matched:

| Item | Operational source | Disposable restore |
|---|---:|---:|
| Inmates | 250 | 250 |
| Admission episodes | 265 | 265 |
| Visitors | 27 | 27 |
| Visits | 48 | 48 |
| Audit events | 490 | 490 |
| Applied migrations | 12 | 12 |
| Append-only database triggers | 6 | 6 |

The operational source was not restored or reset. `audit:verify --allow-legacy` reported 490 unsigned historical events and zero invalid signatures; signature verification for newly created events passed in the test suite. Historical events cannot be retrospectively authenticated.

`pms:readiness` correctly fails against the current development configuration: production approvals/references, HTTPS, production session settings, asynchronous queue, delivery mailer and confirmed visiting rules are not configured. The separate local backup key was supplied only to backup commands and is not installed as a production secret. Registry access blocked dependency installation/advisory scanning, and an offline npm retry failed on an uncached package. A fresh Vite build, current browser/device acceptance, load testing and independent security assessment remain unverified. General private document uploads still need their own reviewed storage and scanning workflow. Inmate photo persistence was subsequently implemented as described below. No production launch or external integration is authorized by these local results.

### Inmate photograph uploads — 17 November 2026

Admission and Edit Inmate now accept optional JPEG, PNG and WebP photographs up to 2 MiB and 3000 × 3000 pixels. The server validates image contents and dimensions, re-encodes the raster as JPEG (longest side capped at 1600 pixels), and stores only the normalized image on the private local disk. Original client filenames and embedded metadata are not retained. The existing documents/document_links schema records ownership, checksum, size and upload audit metadata; no schema or dependency changes were required.

The inmate profile and directory use the authorized `inmates.photo` route. Authentication, inmate read permission and facility scope apply to every image request, with no-store responses and read audits. Edit Inmate can add a missing photo or replace the current photo; earlier versions remain private. Failed storage or audit writes roll back the admission/update and remove uncommitted image files. Native multipart submission preserves the selected file across the admission wizard; no sensitive offline queue is introduced.

Validation passed: 20 targeted tests, 190 assertions, including admission/custody regression, private upload/read authorization, invalid image/size/dimension rejection, replacement selection, failure rollback and operational form JavaScript/Alpine expression syntax. View compilation and route discovery passed. PHP GD JPEG/PNG/WebP support is required and was verified on the local PHP 8.5 runtime.

The encrypted database backup commands cover SQL and document metadata; they do not include photograph binaries. Infrastructure backups and restore procedures must separately protect and reconcile `storage/app/private/inmate-photos` with its matching database snapshot. Files must remain outside public storage, including during deployment and recovery.
### Selective fixture cleanup — 17 November 2026

The local dataset was cleaned by archiving obvious placeholder identities, generated test-name suffixes and test facilities without retained inmate custody links. Realistic names, including Robert Ssemakula, were retained even when their references or provenance notes identify demonstration data. The operation archived 212 inmates, 12 personnel, two visitors, 70 facilities, one unused placeholder role and their linked test admissions, locations, legal references, discharges, transfers, visits, postings and capacities. It did not rewrite custody states or physically delete recorded events, documents or photographs.

Operational models now support soft deletion. Default model queries and the corresponding archived child rows exclude these fixtures from directories, selections and report aggregates. Historical parent relationships remain resolvable for recorded events. Validation rejects archived facilities, inmates, staff and roles; old admission forms cannot silently fall back to another facility. Local staff-number generation includes archived sequences to avoid reusing existing numbers.

`ups:demo-cleanup` previews candidates; `--force` applies the local-only transactional operation and records an audit summary. Active login identities and roles are protected. Facilities still referenced by retained inmates' custody or transfer history are preserved. Two such entries remain pending an operator-supplied correction: Fictional North Development Facility and Assignment End Facility 3cCx. Their retained inmates' custody assignments were not changed.

The default main-database seeder now runs only the realistic canonical dataset. B1 low-level fixtures require the explicitly isolated b1_dev/b1_test connections; they cannot seed the main database. The pre-cleanup encrypted backup is `20261117-113143-35bb7301-c4dc-4cc3-93c8-6812ec908286.pmsbackup` and its 782,082-byte SQL payload authenticated successfully. Existing private photo files were left untouched.

Verification: nine focused cleanup tests passed; the full suite then passed 255 tests and 1,483 assertions, including migration rollback/reapply, database foreign-key retention, authorization, photo uploads, report accuracy and visitor concurrency. Evidence: `storage/logs/seed-cleanup-tests.log`, `seed-cleanup-regression.log`, `seed-cleanup-regression.xml` and `seed-cleanup-events.log`. Audit verification reported zero invalid signatures; 490 historical unsigned events remain explicitly identified. Realistic records added through the application during this work were retained.