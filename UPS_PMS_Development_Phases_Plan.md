# Uganda Prisons Service — Prison Management System (PMS)
## Phased Development Plan

**Document status:** Working development roadmap  
**Updated:** 10 November 2026  
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
**Status: Complete (11 November 2026).**

**Recorded completion (11 November 2026):** Reports landing + 6 report screens with range/facility filters, Chart.js visuals, data tables and labeled print previews ("NOT AN OFFICIAL RECORD"); user directory/detail/invite/status/assign-role previews; read-only permission matrix over a proposed catalog (RoleDemoData); audit directory with actor/action/outcome/facility/search filters plus event detail with before/after metadata and per-resource trail; settings shows the catalog. New fixtures: RoleDemoData, UserDemoData (10 accounts), AuditDemoData (18 events, referential integrity enforced by AdminFixtureReconciliationTest), ReportDemoData (all figures derived from canonical fixtures). Full suite 42 tests / 376 assertions green; Pint clean; all 18 F7 URLs smoke-tested 200 (unknown ids 302, bogus print 404). No real auth/RBAC/enforcement — that is backend B2.

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

> These phases are **proposed next steps**, not yet approved or implemented. They should be divided into individual AI-agent implementation prompts only after Phase 8 is accepted.

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

## 4. Phase delivery protocol for the AI coding agent

For **each** phase:

1. Inspect the existing Laravel codebase and preserve previous work.
2. **Implement immediately**; do not produce specifications in place of working code.
3. Use Blade, Tailwind and Alpine.js; no React/Vue/Inertia.
4. Reuse components and central data providers; avoid duplicating fixture data.
5. Keep frontend previews honest: no fake persistence, approvals, authentication or synchronization.
6. Run relevant checks, typically `npm run build`, `php artisan optimize:clear`, `php artisan view:cache`, `php artisan route:list` and appropriate tests.
7. Verify actual browser behavior where tooling permits; distinguish checked behavior from unverified claims.
8. Provide a concise completion report: features, files, routes, tests, limitations and next-phase readiness.
9. **Stop after the assigned phase** and await review/approval before proceeding.

## 5. Current checkpoint and next action

| Phase | Status |
|---|---|
| 1 — Laravel foundation | Completed |
| 2 — Authentication UI and dashboard | Completed |
| 3 — Inmate Management UI | Completed |
| 4 — Transfers UI | Completed |
| 5 — Personnel and Facilities UI | Completed and verified |
| 6 — Visitor Management UI | Built (preview only); awaiting review |
| 7 — Reports, User Access and Audit UI | Not started |
| 8 — Frontend QA and handoff | Not started |
| Backend phases B1–B8 | Proposed; not started |

**Immediate next step:** Phases F6 and F7 are implemented and verified (full suite green). Per the user directive to finish everything, Phase 8 (integration QA + handoff) then backend B1–B8 proceed; stakeholder validation items remain flagged throughout.
