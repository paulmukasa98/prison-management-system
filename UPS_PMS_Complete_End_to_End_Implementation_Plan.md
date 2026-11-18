# Uganda Prisons Service — Prison Management System (PMS)
## Complete End-to-End Development Plan and AI Coding Agent Prompts

**Document status:** Working development roadmap  
**Updated:** 11 November 2026  
**Approach:** Implement the frontend first in successive, directly executable AI-agent prompts; review each phase before starting the next. Implement backend workflows only after the frontend is complete.

> This is a project development plan, not an official Uganda Prisons Service operational policy. Proposed approval sequences, roles, legal classifications, and identifier conventions require validation with authorized UPS stakeholders.

## 1. Technology and design decisions

| Area | Approved choice |
|---|---|
| Application framework | Laravel 13 (existing project) |
| Server-side views | Laravel Blade; standard `@extends('layouts.app')` inheritance |
| Styling | Tailwind CSS |
| Lightweight interactivity | Alpine.js and vanilla JavaScript |
| Charts | Chart.js |
| Frontend build | Vite and npm |
| Backend language | PHP (local environment currently PHP 8.5) |
| Database | MySQL using XAMPP locally |
| Authentication architecture | Laravel session authentication, implemented in backend phase |
| Authorization architecture | Laravel Gates/Policies and role-based permissions, enforced in backend phase |
| UI direction | Government administration aesthetic: navy sidebar, light background, restrained blue accents, clear tables and forms |
| Primary usage | Desktop and tablet first; responsive mobile support |
| Not used | React, Vue, Inertia, separate SPA |

### Scope: seven approved modules

1. **Inmate Management:** identity records, admissions, profiles, custody information, admission history, discharges.
2. **Inmate Transfers:** requests, review, dispatch, receipt, transfer history.
3. **Personnel Management:** staff records, profiles, facility assignments and posting history.
4. **Prison Facilities:** facility registry, profiles, population, capacity and personnel allocation.
5. **Dashboard & Reporting:** statistics, trends, gender demographics, population and operational reporting.
6. **User Access & Audit:** system users, roles/permissions, secure access and auditable events.
7. **Visitor Management:** visitor records, visit scheduling, check-in/out and visit history.

**Out of scope for the current frontend:** payroll, recruitment, full medical records, AI surveillance, blockchain implementation, actual integration with justice-sector systems, and other features not agreed as part of these seven modules.

### Cross-cutting rules

- Use **prisoner number as the primary visible identifier**, but retain a distinct immutable internal inmate identity and separate admission/custody episodes. The official UPS numbering convention has **not been verified**; never invent it.
- Keep **personnel records distinct from system login accounts**.
- Preserve admission, transfer, discharge and assignment histories; do not silently overwrite historical events.
- Use fictional, clearly labeled demo records during frontend development.
- Do not treat frontend role visibility as security enforcement.
- Actual admission, transfer approval, dispatch, receiving confirmation and discharge require backend authorization, validation, transactions and audit logging.
- Prepare interfaces for offline status and pending synchronization; implement real offline storage, conflict resolution and synchronization only after backend design and security review.
- Keep facility references centralized and ensure dashboard totals reconcile with underlying records.
- Any proposed UPS operational workflow must be validated before production use.

---

## 2. Frontend implementation roadmap — eight phases

### Phase 1 — Laravel setup and UI foundation
**Status: Completed and visually verified.**

**Deliverables**
- Initialize existing Laravel project and local XAMPP MySQL configuration.
- Install and configure Tailwind CSS, Alpine.js, Vite and required packages.
- Implement master Blade layout, navy sidebar, responsive mobile drawer, top navigation, breadcrumbs and footer.
- Create reusable Blade components: buttons, inputs, cards, badges, tables, modals, alerts, pagination, empty states and placeholders.
- Add all module navigation destinations and named routes.
- Establish central demo context and basic dashboard layout.
- Add connection/synchronization status display as a visual component.

**Acceptance:** Application loads; all navigation resolves; assets compile; layout works on desktop, tablet and mobile.

**Recorded completion:** Blade layout inheritance corrected; `npm run build`, `php artisan view:cache` and route checks passed; dashboard/sidebar/styling confirmed in browser.

### Phase 2 — Authentication UI and complete dashboard
**Status: Completed according to agent report.**

**Deliverables**
- Guest layout and frontend-only Login, Forgot Password and Reset Password screens; no public registration.
- Password visibility controls, validation and password-strength guidance.
- Dashboard KPI cards: active inmates, personnel, pending transfers and visits.
- Admissions versus discharges chart, inmate population by gender, population by facility and custody-classification breakdown.
- Reporting-period selector, recent activities, quick actions and facility context.
- Centralized, internally consistent demo statistics.

**Acceptance:** Auth screens render; dashboard charts/filter interactions work; no fake successful login, reset or protected session is claimed.

**Recorded completion:** Agent reported 21 routes, Chart.js integration and clean build. Authentication remains frontend-only and dashboard routes remain unprotected until backend work.

### Phase 3 — Inmate Management frontend
**Status: Completed according to agent report.**

**Deliverables**
- Searchable inmate directory with sorting, filtering, pagination and status badges.
- Five-step new admission form: personal details, custody/legal details, facility, additional information, review.
- Detailed inmate profile with overview, personal, legal/custody, admission history, transfers, visits and activity tabs.
- Edit-personal-details preview with protected legal/custody fields and before/after review.
- Admission history timeline, discharge directory and controlled discharge-initiation preview.
- Central fictional inmate dataset, using non-official `DEMO-PN-...` identifiers.

**Acceptance:** All pages and interactions render; no database changes; custody and admission histories remain distinct.

**Recorded completion:** Agent reported 30 fictional inmate records and 25 routes. **Follow-up (closed 11 Nov 2026):** Custody/discharge counts verified consistent — convicted 14 + remand 9 = 25 active, 5 discharged; the original report's discrepancy was a reporting artifact. Guarded by `DemoDataReconciliationTest`.

### Phase 4 — Inmate Transfer Management frontend
**Status: Completed according to agent report.**

**Deliverables**
- Transfer directory with search, status/facility filters, sorting and pagination.
- Five-step new transfer request preview with inmate preselection and origin/destination validation.
- Transfer details, review decisions (approve/reject/clarification), dispatch/handover and receiving confirmation preview screens.
- Transfer history, lifecycle timeline and status badges.
- Link transfer records to inmate profiles and reconcile dashboard pending-transfer counts.

**Provisional lifecycle:** Draft → Pending Approval → Approved → Ready for Dispatch → In Transit → Arrival Confirmation Pending → Completed; also clarification, rejection and cancellation paths. This is **not a confirmed UPS process**.

**Acceptance:** Every stage is navigable and correctly labeled as preview; no real approval or facility reassignment occurs.

**Recorded completion:** Agent reported 15 fictional transfer records and 30 routes. **Follow-up (closed 11 Nov 2026):** Both figures were correct under different definitions — 3 records are "Pending Approval"; the dashboard's `pendingCount()` also includes "Awaiting Clarification" (4). All 5 completed transfers agree with each inmate's current facility. Guarded by `DemoDataReconciliationTest`.

### Phase 5 — Personnel and Prison Facilities Management frontend
**Status: Completed and verified (11 November 2026).**

**Personnel deliverables**
- Directory, registration preview, profiles and edit preview.
- Facility assignment directory, new posting preview and assignment history.
- Search, filters, sorting, pagination and reusable personnel components.
- Separate employee identity from application user credentials and permissions.

**Facility deliverables**
- Facility directory, registration preview, profile and edit preview.
- Population/capacity monitoring, occupancy rate, facility-specific personnel listing and facility statistics.
- Canonical facility IDs and names shared across inmates, transfers, personnel and dashboard.
- Missing capacity displayed as unknown, never silently treated as zero.

**Suggested demo fixtures:** At least 35 fictional staff, eight aligned fictional facilities and 40 assignments.

**Acceptance:** Personnel/facility pages work, figures reconcile across modules, historical postings are preserved and no real personnel or facility changes occur.

**Recorded completion (11 November 2026):** Verified — 35 personnel, 40 assignments, 8 canonical facilities (`DEMO-FAC-…`), zero dangling facility references. Reconciliation follow-up fixed: per-facility population, gender/custody splits and personnel counts are now **derived live** from the inmate/transfer/assignment fixtures (`FacilityDemoData::derived()`); dashboard current-period headcounts, gender/custody breakdowns and the facility chart derive from the same records (25 active / 32 active staff / 4 pending transfers). Historical reporting periods remain clearly-labeled fictional aggregates until Phase 7 reworks them. Also fixed a latent Phase 5 bug where `/facilities/{id}` and `/personnel/{id}` returned 500 (timeline producers used `ts` instead of the component's `timestamp` key). Guarded by `tests/Feature/DemoDataReconciliationTest.php`; full suite green, Pint clean, all module pages smoke-tested at 200.

### Phase 6 — Visitor Management frontend
**Status: Planned.**

**Deliverables**
- Visitor directory and individual visitor profile/history.
- Visitor registration preview with appropriate identity and contact fields.
- Visit scheduling/request interface linking visitor, inmate, facility, date and purpose.
- Visit records directory with filters by date, facility, inmate, visitor and status.
- Check-in and check-out preview screens with time/status indicators.
- Visit status model (e.g., requested, scheduled, checked in, completed, cancelled, denied) pending operational validation.
- Duplicate-visitor awareness and clear separation of visitor identity from each visit occurrence.
- Integration with inmate profile Visits tab and dashboard visit counts.

**Privacy requirements:** Minimize displayed personal identifiers; never treat a visitor as approved merely because a demo form was filled. Avoid exposing visits outside authorized facility context.

**Acceptance:** Visitor/visit screens are fully navigable and responsive; scheduling and check-in/out are clearly previews; cross-module links and counts are consistent.

### Phase 7 — Reports, User Management and Audit frontend
**Status: Planned.**

**Reporting deliverables**
- Reports landing page and reusable report filters: date range, facility and authorized scope.
- Inmate population and gender demographics, custody classifications, admissions/discharges, transfers, visits, staffing and occupancy reports.
- Accessible charts and data tables; clear empty, loading and error states.
- Export/print UI only when explicitly labeled preview until actual export services exist.

**User administration deliverables**
- System user directory, account details and account status UI.
- User invitation/provisioning preview, role assignment preview, permission matrix and account deactivation preview.
- Clear distinction between personnel profile, user account, role and permission.
- Proposed permissions for read/create/review/approve/dispatch/receive/discharge and facility-scoped access; final policy must be validated.

**Audit frontend deliverables**
- Audit-log viewer with filters for actor, action, resource, facility and date.
- Event detail view showing timestamp, action, record reference and permitted metadata.
- Immutable/read-only presentation; do not offer ordinary edit/delete audit controls.

**Acceptance:** All administrative/reporting screens render and share coherent demo data; no frontend permission toggle grants real authority.

### Phase 8 — Frontend integration, QA and handoff
**Status: Planned.**

**Deliverables**
- End-to-end route and navigation audit across all seven modules.
- Desktop, tablet and mobile layout testing.
- Form validation, multi-step flows, search/filter/sort/pagination and chart interaction checks.
- Accessibility review: keyboard navigation, labels, focus, contrast and screen-reader summaries.
- Consistent shared components, statuses, naming, fictional data and facility references.
- Error/empty/loading states, no dead links, no misleading success messages.
- Frontend security review: no real data, no passwords in browser storage, no mock authorization presented as secure.
- Final frontend acceptance checklist and explicit backend integration handoff.

**Acceptance:** Clean asset build, Blade compilation, route validation and actual browser interaction checks; known gaps documented.

---

## 3. Backend roadmap — proposed phases after frontend approval

> These phases are **proposed next steps**, not yet approved or implemented. Complete ready-to-use prompts are included below, but start backend work only after Phase 8 review and stakeholder approval of relevant operating rules.

### Backend Phase B1 — Database architecture and migrations
- Design normalized MySQL schema and Eloquent models for facilities, personnel, accounts, inmates, custody episodes, legal references, transfers, visitors, visits and audit events.
- Use internal primary keys with separately constrained business identifiers.
- Define foreign keys, unique constraints, indexes, soft-delete/retention policy where appropriate, and historical records.
- Build seeders with fictional data and migrate frontend fixtures carefully.
- Write database tests and migration rollback checks.

### Backend Phase B2 — Authentication and authorization
- Implement secure Laravel session authentication, login throttling, password reset and secure session lifecycle.
- Protect administrative routes with middleware.
- Implement roles/permissions and Laravel Policies/Gates with facility-scoped access and least privilege.
- Separate account provisioning from personnel registration.
- Test unauthorized access and privilege escalation scenarios.

### Backend Phase B3 — Inmate records, admissions and discharge
- Implement validated admission processing, custody episode lifecycle, controlled corrections and document references.
- Confirm prisoner-number rules with UPS before implementing numbering logic.
- Add authorized discharge workflow, immutable event history, transaction safety and auditing.
- Never calculate legally binding release dates from unverified rules.

### Backend Phase B4 — Transfer workflows
- Implement request/review/approval, dispatch, receipt and completion state transitions with authorized roles.
- Prevent conflicting transfers and invalid transitions using transactions and concurrency controls.
- Update facility assignment only at the correct verified workflow point.
- Add notifications and full event audit trail.

### Backend Phase B5 — Personnel and facility operations
- Implement staff and facility CRUD with validated, restricted updates.
- Maintain assignment history and prevent contradictory active postings.
- Calculate capacity/occupancy from authoritative records and handle missing values.

### Backend Phase B6 — Visitor operations
- Implement visitor identity records, visit requests, scheduling, authorization, check-in/out and history.
- Apply facility and inmate restrictions with privacy-conscious views.
- Prevent duplicate or conflicting visit events and record audit history.

### Backend Phase B7 — Reporting, audit and integrations
- Replace demonstration metrics with authorized database queries.
- Implement secure report exports and facility-scoped reporting.
- Implement append-only/tamper-evident audit strategy as appropriate, retention, backups and recovery procedures.
- Scope any Judiciary/Police/DPP or other external integrations only after confirmed API availability and formal approval.

### Backend Phase B8 — Offline readiness, security testing and deployment
- Design offline capability around the security sensitivity of custody actions; define which operations may be queued and which must require online authorization.
- Implement encryption, device controls, synchronization IDs, conflict resolution and reconciliation where approved.
- Conduct functional, authorization, concurrency, security, backup/restore and user-acceptance testing.
- Establish production deployment, monitoring, incident response and operational training plans.

---



## 4. Complete ready-to-copy implementation prompts (F1–F8, B1–B8)

Each subsection below is a standalone prompt for the AI coding agent. **Give the agent one phase at a time.** Completed phases F1–F5 are included for reference or recovery; do not rerun them on an already functioning codebase. Backend phases require Phase F8 signoff and relevant UPS operational validation.


### F1 — Laravel foundation and design system

**Status:** COMPLETED — reference/recovery prompt

#### AI coding agent prompt — F1

> **Task:** Implement Laravel foundation and design system in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Inspect the existing Laravel 13 project and preserve any completed work. Implement or repair the Blade/Tailwind/Alpine/Vite foundation: `resources/views/layouts/app.blade.php` using `@yield('content')`, navy responsive sidebar, mobile drawer, top navigation, breadcrumbs, footer, navigation for all seven modules, and reusable Blade components (cards, tables, forms, badges, alerts, modal, empty/loading states, pagination). Establish named GET routes and placeholder views for dashboard, inmates, transfers, personnel, facilities, visitors, reports, audit, users/roles, settings. Establish a central fictional demo context and dashboard placeholder. Configure local XAMPP MySQL without requiring it for placeholder rendering. Use a neutral shield, not an invented UPS logo. Never use `<x-layouts.app>` unless an actual component exists; prefer `@extends('layouts.app')`. Do not implement business persistence. Verify all routes and views, responsive navigation, npm build, Blade cache and browser rendering. Report actual checks and stop.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### F2 — Authentication screens and operational dashboard

**Status:** COMPLETED — reference/recovery prompt

#### AI coding agent prompt — F2

> **Task:** Implement Authentication screens and operational dashboard in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Preserve Phase 1. Implement a separate guest layout and Blade login, forgot-password and reset-password pages with accessible labels, show/hide password, validation, strength guidance and loading UI. No public registration; no fake login success, password reset delivery or hardcoded credentials. Add GET preview routes, keeping any existing route paths stable. Build dashboard with period selector (this month, last month, last three months, this year), KPI cards (active inmates, personnel, pending transfers, visits), Chart.js admissions/discharges, gender doughnut, facility population, custody classification, recent activity and quick actions. Use centralized fictional demo data and reconcile totals; period selector must change charts/KPIs. Protect nothing via fake JavaScript authorization; real Laravel sessions come in B2. Test charts in browser, build, Blade compilation, routes and responsive layout. Stop.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### F3 — Inmate management frontend

**Status:** COMPLETED — reference/recovery prompt

#### AI coding agent prompt — F3

> **Task:** Implement Inmate management frontend in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Preserve earlier modules. Implement inmate directory with prisoner-number/name search, facility/classification/status/gender/date filters, sorting, pagination, statuses and empty states. Implement five-step admission preview (personal, custody/legal, facility, other details, review) with classification-dependent fields, validation, photo local preview and unsaved-changes warning. Build inmate profile with overview, personal, legal/custody, admission episodes, transfers, visits and activity tabs; edit-details review with protected legal fields; admission-history timeline; discharge directory and controlled discharge preview. Use centralized fictional InmateDemoData with unique internal identity IDs, separate prisoner numbers and admission episodes; never invent an official UPS numbering convention or release-date calculation. No actual POST/persistence. Link to transfers/visits; test forms, profiles, routes, builds and responsive layouts. Stop.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### F4 — Inmate transfer management frontend

**Status:** COMPLETED — reference/recovery prompt

#### AI coding agent prompt — F4

> **Task:** Implement Inmate transfer management frontend in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Implement centralized fictional transfer fixtures referencing inmate and facility IDs. Build transfer directory with search/filter/sort/pagination; five-step request preview with inmate preselection and origin != destination validation; transfer detail tabs; authorization review (approve/reject/clarification preview); dispatch/handover preview; receiving confirmation preview; transfer history and lifecycle timeline. Proposed states: draft, pending approval, awaiting clarification, approved, ready for dispatch, in transit, arrival confirmation pending, completed, rejected, cancelled. This is NOT verified UPS procedure; keep stages configurable. Never mutate inmate facility during preview. Integrate profile transfer tab and dashboard pending counts, checking fixtures against latest completed movement. Avoid exposing actual escort plans. Test routes, actions, validation, browser UI and consistency. Stop.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### F5 — Personnel and prison facilities frontend

**Status:** COMPLETED AND VERIFIED 11 November 2026 — reference/recovery prompt

#### AI coding agent prompt — F5

> **Task:** Implement Personnel and prison facilities frontend in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Implement Personnel Directory, five-step registration preview, profile, edit review, assignment directory, new assignment preview and assignment history. Keep employee records separate from login accounts. Use fictional staff numbers and no automatic role grants. Implement Facility Directory, registration preview, profile, edit/capacity-change review, occupancy dashboard, assigned-personnel listing and facility statistics. Create centralized PersonnelDemoData, FacilityDemoData and AssignmentDemoData (suggest 35 staff, 8 aligned facilities, 40 assignments); canonical facility IDs must be reused across inmates, transfers and dashboard. Support search, filters, sorting, pagination, responsive tabs, timelines and empty states. Occupancy = population/capacity; handle unknown/zero capacity safely and label mock capacities. Preserve history and do not actually reassign staff or change facility records. Reconcile Phase 3 inmate counts and Phase 4 pending-transfer discrepancy using fixtures rather than display hacks. Test all routes, views, charts, integrations and build. Stop.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### F6 — Visitor management frontend

**Status:** NEXT FRONTEND PHASE

#### AI coding agent prompt — F6

> **Task:** Implement Visitor management frontend in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Implement the entire Visitor Management frontend immediately in the existing Laravel 13 Blade application. Inspect current routes, VisitorController, inmate profiles, dashboard DemoContext, and canonical facility/inmate fixtures first. Create centralized fictional VisitorDemoData and VisitDemoData with stable IDs, at least 25 fictional visitor identities and 45 distinct visit occurrences, spanning facilities, inmates, dates and states. A visitor identity is not a visit; multiple visits must reference one visitor. Never use real IDs, photos or visitor information.

Create screens: (1) visitor directory with search by name/demo visitor reference, facility and status filters, sorting, pagination, summary cards; (2) visitor registration multi-step preview collecting name, identity-document type/reference (optional/appropriately masked), contact, relationship/purpose context, review; (3) visitor profile with identity summary, masked identifiers, visit history, activity; (4) edit visitor preview with before/after review; (5) visit records directory with inmate, visitor, facility, date range, status filters; (6) five-step visit scheduling preview: select visitor, select eligible inmate, verify facility, propose date/time and visit purpose, review; (7) visit details with status, linked visitor/inmate, request/schedule information, timeline; (8) check-in preview; (9) check-out preview; (10) cancellation/denial/clarification preview as appropriate. Use named GET routes; keep existing `visitors.index`, `visitors.records`, `visitors.schedule` names stable; order static routes before numeric dynamic routes. Create status badge, visit timeline and schedule-summary components.

Provisional visit states: requested, pending review, scheduled, denied, cancelled, checked in, completed, no-show. Treat these as design assumptions, not official UPS visitation rules. Do not invent eligibility, visitor limits, hours or approval policy. Show possible scheduling conflicts from demo fixtures, but do not claim legal eligibility or actually reserve a slot. Check-in must not imply a visit is approved; check-out must not imply completion without backend validation. Use preview-only actions and never POST, store sensitive details in localStorage or generate actual access passes. Link inmate profile Visits tab to canonical visits and dashboard visits metric to the same fixture data with explicit reporting period. Respect facility context; frontend scoping is only illustrative. Ensure accessibility, responsive mobile forms, empty/error states, and test interactions, routes, Blade cache, npm build and no regressions. Report files, routes, fixture reconciliation, tested interactions, limitations and stop.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### F7 — Reports, user management, roles and audit frontend

**Status:** PLANNED

#### AI coding agent prompt — F7

> **Task:** Implement Reports, user management, roles and audit frontend in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Implement Phase 7 frontend only in existing Laravel Blade app. Build Reports & Analytics landing page, report categories and dedicated report screens for inmate population/gender/custody, admissions/discharges, transfers, visitation, personnel staffing, and facility occupancy. Use Chart.js and accessible tables, shared date-range/facility filters, summary totals, no-data/loading/error states. Reuse canonical fixtures and reconcile totals and periods. Export/print buttons may create clearly marked previews; never claim to export official records unless an actual frontend-only mock download is intentionally generated and labeled.

Build System Users directory, account detail, create/invite-account preview, status/deactivation preview, role assignment preview, Roles & Permissions matrix, and settings for proposed permission catalog. Keep staff identity distinct from login accounts. Show facility-scope and least-privilege concepts, but do not implement real access control, password management, role persistence or security assertions. Use fictional accounts and clearly label all role previews. Permissions should cover inmates (read/admit/edit/discharge), transfers (request/review/authorize/dispatch/receive), personnel/facilities, visitors, reporting, users and audit. Keep approval roles configurable and unverified.

Build read-only Audit Logs directory with filters for actor, action, record, facility, date and outcome; audit event detail with timestamp, actor, resource, correlation reference, before/after metadata where appropriate and redacted sensitive fields; event timeline. Use fictional events, never expose credentials or unmasked identity documents. No edit/delete audit UI. Maintain existing route names `reports.index`, `users.index`, `users.roles.index`, `audit.index`, `settings.index`, adding GET routes as needed. Ensure all sidebar links and quick actions work. Test all views, filters, role matrix, reports/chart consistency, responsive layouts, accessibility, builds and routes. Stop after completion report.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### F8 — Frontend integration, QA and backend handoff

**Status:** PLANNED

#### AI coding agent prompt — F8

> **Task:** Implement Frontend integration, QA and backend handoff in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Perform full frontend stabilization across seven modules without beginning backend persistence. Inventory every route, controller, view, component, fixture provider, status definition and sidebar link. Fix dead links, wrong route names, duplicate Blade layout patterns, unbound buttons, console errors, inconsistent table behaviors, broken mobile overflow, form validation gaps, keyboard-focus issues and misleading preview messages. Reconcile inmate counts, admission episodes, transfer movements, personnel postings, canonical facilities, visits and dashboard/report totals. Verify no real personal data or credentials are embedded in fixtures and no fake security or offline synchronization claims are made.

Run and record `composer test` or available test suite, `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, formatting checks, and automated/browser manual checks for desktop/tablet/mobile. Add Laravel feature tests for GET routes and fixture invariants; add browser interaction tests if tooling supports them. Check accessibility (labels, errors, focus order, keyboard tabs, color-independent statuses, chart text alternatives), security boundaries, loading/empty/error states, and UX of every multi-step preview. Do not assert browser checks were done if no browser tool is available.

Produce `docs/FRONTEND_HANDOFF.md` mapping pages/forms to future API endpoints, entities, required validations, roles, audit events, state transitions, offline constraints and unresolved stakeholder questions; include a route inventory, test evidence and prioritized defects. Do not alter core architecture or start backend. Stop for frontend acceptance.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### B1 — Database schema, migrations and data integrity

**Status:** PLANNED — start only after frontend signoff

#### AI coding agent prompt — B1

> **Task:** Implement Database schema, migrations and data integrity in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Implement normalized MySQL schema and Eloquent models after Phase F8 acceptance. First inventory all frontend fixtures and domain relationships; create ERD in `docs/DATABASE_DESIGN.md`. Tables should cover facilities, personnel, personnel assignments, users/account-personnel links, roles/permissions and facility scope, inmate identities, custody/admission episodes, legal/court references, transfers and transition events, visitors, visits, supporting document metadata and audit events. Keep prisoner number separate from immutable internal inmate ID and admission reference; define official identifier rules as configurable and pending UPS validation. Do not guess real legal classification constraints.

Use Laravel migrations with foreign keys, unique constraints, indexed search fields, explicit nullable columns, timestamp conventions, state fields and restrictive deletion/retention rules. Preserve admission, transfer and discharge history. Use database transactions where data invariants require them. Create factories/seeders with clearly fictional data only; never seed default production admin passwords. Provide idempotent development seed process, migration fresh/rollback verification and feature tests for constraints and historical linkage. Do not yet replace all frontend previews or implement real custody operations. Run migrations on a dedicated local development DB only; never destroy an existing database without explicit confirmation. Document schema decisions and unresolved operational requirements. Stop.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### B2 — Secure authentication, account provisioning and RBAC

**Status:** PLANNED

#### AI coding agent prompt — B2

> **Task:** Implement Secure authentication, account provisioning and RBAC in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Implement actual Laravel session-based authentication, password hashing, CSRF protection, login rate limiting, secure logout, password reset via Laravel password broker and verified email delivery configuration as applicable. Inspect existing guest views and preserve their design. Choose maintained Laravel authentication primitives compatible with Laravel 13; do not force a frontend framework. Configure session cookies securely and protect all administrative routes with auth middleware. Remove public access to operational data. Create account provisioning through authorized administrative workflows; personnel record creation must never automatically grant login access.

Implement centrally defined roles/permissions, facility scopes and Laravel Policies/Gates. Explicitly distinguish `view`, `create`, `update`, `approve`, `dispatch`, `receive`, `discharge`, `export`, `manage_users`, and `view_audit`. Default deny, enforce checks server-side on routes/controllers/queries, and prevent IDOR/cross-facility access. Do not derive permissions solely from rank or client-side toggles. Bootstrap first privileged account securely using a documented controlled CLI path with no hardcoded password. Audit login/security events without storing passwords or tokens. Write tests for successful/failed login, throttling, reset, CSRF, session expiration, unauthorized access, privilege escalation, cross-facility access, account suspension and logout. Configure mailer safely for development and do not claim email delivered without testing. Stop.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### B3 — Inmate admissions, custody records and discharge

**Status:** PLANNED

#### AI coding agent prompt — B3

> **Task:** Implement Inmate admissions, custody records and discharge in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Wire the existing inmate frontend to real authorized Laravel controllers, FormRequests, policies, services and Eloquent models. Implement inmate identity search/deduplication, admission episode creation, linked legal references, optional document metadata and restricted correction workflows. Validate inputs server-side; preserve unique internal identity and episode history; never assume official prisoner-number format. Require approved UPS numbering policy before production generation. Use transactions and idempotency/collision handling for admission creation. Protect sensitive files with private storage, malware/type/size checks, and authorized access; avoid public storage for prisoner records.

Implement discharge as a controlled authorization workflow using validated legal basis, documentary reference, explicit authorized decision, timestamps and custody episode closure; never auto-release based on unverified sentencing calculations. Ensure discharged records remain historically accessible only to authorized users. Add structured audit events with actor, facility, record reference and safe before/after metadata. Prevent overlapping active custody episodes and invalid transitions, including concurrent requests. Replace preview-only buttons with real operations only after permissions, validations and tests are in place. Write unit/feature tests for admission, duplicate handling, corrections, discharge authorization, rejection, concurrency, rollback, privacy and facility scoping. Stop.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### B4 — Transfer approvals, dispatch, receipt and concurrency

**Status:** PLANNED

#### AI coding agent prompt — B4

> **Task:** Implement Transfer approvals, dispatch, receipt and concurrency in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Implement server-authorized transfer lifecycle using a configurable state machine and policy-backed transitions, subject to UPS workflow validation. Support request, clarification, approval/rejection, cancellation, dispatch, receipt and completion with a full immutable transition history. Use Laravel FormRequests, service classes, policies and transactions; require supporting authorization references where policy requires. Prevent same-origin/destination, conflicting active transfers, dispatch before authorization, receipt before dispatch and duplicate completion. Handle race conditions with row locks/optimistic versioning and idempotency keys.

Keep inmate facility assignment consistent: never change it at mere request or approval; define and document the verified transition point for official assignment, including in-transit representation. Ensure receiving facility staff cannot confirm a transfer outside their permitted scope. Implement in-app/email notifications through queues where configured; never send sensitive inmate details in notification subject/body. Integrate existing transfer views and dashboard counts with database queries. Write state-transition matrix tests, cross-facility authorization tests, concurrent approval/dispatch/receipt tests, rollback tests and full audit tests. Stop.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### B5 — Personnel records, assignments and facility operations

**Status:** PLANNED

#### AI coding agent prompt — B5

> **Task:** Implement Personnel records, assignments and facility operations in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Implement server-side personnel CRUD, facility CRUD, assignment workflows and authorized historical corrections using Laravel models, FormRequests, policies, services and audit. Keep staff number unique under configurable organizational rules and separate employee records from login credentials. Prevent overlapping active primary postings where the business rule requires it; preserve historical assignments and require explicit authorization for reassignment. Handle effective dates and future postings without overwriting history.

Use canonical facility IDs throughout inmate, transfer, personnel and visitor modules. Validate capacity as an optional recorded figure with effective date and source; missing capacity must remain unknown. Derive occupancy and assigned personnel from authoritative queries, not manually entered summary fields. Apply facility-scope access to listing, edits, reporting and exports. Integrate existing Blade views with real validation errors and confirmation states. Write tests for assignment overlaps, facility status constraints, capacity null/zero handling, permissions, history preservation and reporting consistency. Stop.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### B6 — Visitor identity, scheduling, check-in and check-out

**Status:** PLANNED

#### AI coding agent prompt — B6

> **Task:** Implement Visitor identity, scheduling, check-in and check-out in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Implement real visitor identity management and separate visit occurrences using authorized Laravel endpoints, models, policies, FormRequests and service layer. Support registration, identity corrections, visit request, approval/denial/cancellation, scheduling, check-in and check-out. Actual eligibility rules, visiting hours, limits, identity verification and approval authority must be confirmed by UPS before enabling production enforcement; implement configurable policy rather than invented legal rules. Link visits to inmate custody episodes and facility context, and handle inmate transfer/discharge impacts on future visits.

Prevent duplicate check-in, check-out before check-in, scheduling collisions where policy forbids them, unauthorized facility access and inappropriate exposure of visitor contact/ID data. Apply retention/redaction rules to personal identifiers. Add immutable visit event history and audit actor/role. Connect inmate Visits tab, visitor screens and dashboard reports to database queries. Write tests for schedule conflicts, status transitions, cross-facility restrictions, concurrent check-in/out, cancellations, privacy and data integrity. Stop.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### B7 — Real reporting, audit, exports and approved integrations

**Status:** PLANNED

#### AI coding agent prompt — B7

> **Task:** Implement Real reporting, audit, exports and approved integrations in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

Replace all dashboard and report fixtures with authorized database queries, consistent date/time zone definitions, facility scope and reconciled custody/visit/transfer counts. Implement reports for inmate population, admissions/discharges, gender/custody classification, facility occupancy, transfers, personnel and visits; provide filtered exports (CSV/PDF only where explicitly approved) with authorization, watermark/classification where required, minimal data exposure and export audit events. Avoid large unbounded synchronous queries; paginate, index and queue long-running reports as needed.

Implement append-only audit logging for sensitive reads/changes/approvals, with actor, action, resource, facility, timestamp, request/correlation ID and safe metadata. Enforce read-only audit UI, restricted access, retention and integrity controls; do not log passwords, secrets or unnecessary sensitive values. Provide backup/restore and data-reconciliation checks. Treat Judiciary/Police/DPP integration as separate gated work: do not connect external systems without written approval, documented API contracts, security review and test credentials. Add report accuracy, export authorization, audit integrity, redaction and performance tests. Stop.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


### B8 — Offline policy, security hardening, UAT and deployment

**Status:** PLANNED

#### AI coding agent prompt — B8

> **Task:** Implement Offline policy, security hardening, UAT and deployment in the existing Uganda Prisons Service Prison Management System (PMS) Laravel application. This is an academic development project, not an officially approved production UPS deployment.

First document a security-reviewed offline operations policy with UPS stakeholders: classify actions that may be cached, queued or must require online authorization. Custody-critical approvals, release/discharge, transfer dispatch and receipt must not silently become effective offline. If approved, implement encrypted local data handling, minimal retention, device registration, signed/idempotent sync events, conflict detection, server-side revalidation, replay protection and explicit reconciliation status; never trust a browser queue as proof of authorization. Provide safe handling for lost/stolen devices and expired credentials.

Perform threat modeling, OWASP-style web security review, penetration testing with permission, dependency scanning, RBAC/IDOR tests, CSRF/XSS/SQL injection defenses, file upload testing, concurrency/load tests, backups and restore drills, log monitoring, alerting, incident response and disaster recovery exercises. Configure production secrets outside repo, HTTPS, secure cookies, queue workers, scheduler, mail, database backups, least-privilege DB users and infrastructure hardening. Produce deployment runbook, rollback plan, administrator/user training, UAT scripts and signoff matrix. No production launch without authorized UPS review, data-protection assessment and operational approval. Stop with evidence and outstanding risks.

### Mandatory execution and verification contract
1. Inspect existing code and preserve completed functionality; use Laravel Blade, Tailwind, Alpine.js, Chart.js and Vite, not React/Vue/Inertia.
2. **Implement code immediately**; do not substitute a design document for functioning features. Where operational rules are unverified, implement configurable placeholders or safe previews and list the question for stakeholder confirmation.
3. Keep names/routes stable where possible; use FormRequests, policies and tests when backend functionality is in scope. No real data in development fixtures.
4. Run the relevant commands: `php artisan route:list`, `php artisan view:cache`, `php artisan optimize:clear`, `npm run build`, `php artisan test` (or available test suite), plus migrations for backend phases. Inspect browser interactions when tools permit. Do not claim unperformed tests.
5. Report files created/changed, routes, dependencies, fixtures or migrations, actual test results, unresolved risks and handoff notes. **Stop after this phase and wait for approval.**


## 5. Required approvals, unresolved decisions and safety gates

- **UPS validation required:** prisoner-number conventions, custody classifications, admission and discharge legal documents, transfer approval hierarchy and state transitions, facility authority/scope, visitor approval rules, retention and record access policy.
- **Data protection:** no real prisoner, visitor or officer information in demo fixtures. Confirm legal authority, security classification, access controls, retention, and incident reporting before production use.
- **Production authorization:** academic prototype completion does not imply permission to deploy in an actual prison. Real-world use requires institutional review, security testing, data-protection compliance and operational signoff.
- **Demo reconciliation issues from reports (closed 11 Nov 2026):** Phase 3 custody/discharge counts verified consistent; Phase 4's three-vs-four pending approvals explained (`pendingCount()` includes "Awaiting Clarification"); all completed transfers agree with recorded current facility. Cross-module figures now derive from a single source of truth (`FacilityDemoData::derived()` + live `DemoContext` stats), guarded by `tests/Feature/DemoDataReconciliationTest.php`. Remaining synthetic values: visits/admissions/discharges aggregates, to be replaced in F7/B7.
- **No fictitious legal automation:** never calculate binding release eligibility or assert official facility capacities, staff ranks, prison IDs or legal requirements without authoritative validation.

## 6. Phase gate checklist

For each phase, record: date, branch/commit, owner, scope delivered, routes/pages, tests actually run, screenshots or browser evidence if available, security/privacy checks, unresolved defects, stakeholder questions, and explicit approval to proceed. A clean build alone is not evidence of complete functional or security testing.

## 7. Current checkpoint

The checkpoint below supersedes the historical statuses in the phase prompts. The user authorized continuing implementation from B6, with B1–B5 supplied as completed.

| Phase | Status as of 17 November 2026 |
|---|---|
| F1 Laravel foundation | Completed; user verified browser layout |
| F2 Authentication UI + dashboard | Completed according to agent report |
| F3 Inmate management frontend | Completed according to agent report |
| F4 Transfers frontend | Completed according to agent report |
| F5 Personnel + facilities frontend | Completed and verified 11 November 2026 |
| F6 Visitor management frontend | Built 11 November 2026 — 25 visitors / 45 visits fixtures, 10 screens, 23 tests passing; awaiting user review |
| F7 Reports, user management, audit frontend | Completed 11 November 2026 — 6 reports + landing + print previews, 10 users screens, audit directory/detail, settings catalog; 4 new fixture classes; 42 tests passing (376 assertions); verified by user directive to finish all phases |
| F8 Frontend integration/QA | Historical frontend QA completed; current backend browser acceptance remains pending |
| B1–B5 Schema, authentication, custody, transfers, personnel and facilities | User-confirmed starting checkpoint; additional authorization, form submission, scoping and integration defects corrected during B6–B8 work |
| B6 Visitor identity, scheduling, check-in and check-out | Implemented: encrypted identities, scoped registration/correction/redaction, approval and scheduling, reception transitions, immutable events, custody-change cancellation and concurrent-action protection |
| B7 Reporting, audit and approved exports | Implemented: database dashboard and six scoped reports, historical snapshots, restricted print/optional CSV, append-only signed audit records and verification. External integrations remain gated pending approval/contracts |
| B8 Offline policy, hardening, recovery and deployment | Local safeguards, approval/readiness gates, encrypted backups and recovery tooling implemented. Current browser acceptance, independent security assessment, infrastructure and institutional signoff remain outstanding; no production deployment |

**Current verification:** The latest full regression suite passed (255 tests, 1,483 assertions), including selective fixture archival, migration rollback/reapply, authentication, reporting, photo uploads and concurrency. Earlier encrypted backup authentication, isolated restore and reconciliation evidence is retained in `docs/FRONTEND_HANDOFF.md`, alongside the latest cleanup checkpoint and its two linked-facility exceptions. Formatting and view compilation passed. Dependency installation, a fresh asset build and advisory scanning were previously blocked by registry access; historical browser screenshots do not establish current backend acceptance.

**Remaining acceptance:** Confirm operational rules, run current browser/device and accessibility checks, complete independent security review and off-host recovery arrangements, and obtain authorized institutional UAT and deployment approval. See the backend checkpoint and runbook in `docs/FRONTEND_HANDOFF.md` for operator steps and limitations.
