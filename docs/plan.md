# TerminLock Implementation Plan (`docs/plan.md`)

Execution framework: **TDD RED-first**, verified via `tests/Feature/` and `frontend/e2e/`.

---

## Task Breakdown

### Task 1: Foundation, Workspace, Client & Project Management (AC-1, AC-2)
- Database schema: `workspaces`, `users`, `clients`, `projects`, `milestones`.
- Models, relations, and Integer Rupiah casting.
- Project creation endpoint (`POST /api/projects`) with unique contract numbers.
- Strict 100% milestone percentage / amount validation rule.
- Verification: `tests/Feature/ProjectApiTest.php` and `tests/Feature/MilestoneApiTest.php`.

### Task 2: Milestone FSM & Deliverables Attachment Subsystem (AC-3, AC-4)
- Milestone Finite State Machine (`App\Domain\MilestoneState`).
- Deliverable upload and storage (`POST /api/milestones/:id/deliverables`).
- Sign-off request token generation (`POST /api/milestones/:id/request-signoff`).
- Cryptographic SHA-256 token hashing and 7-day expiration logic.
- Verification: `tests/Feature/DeliverableApiTest.php` and `tests/Feature/SignoffRequestTest.php`.

### Task 3: Zero-Login Client Portal & Immutable BAST Sign-off (AC-5, AC-6, AC-7, AC-8)
- Public client portal route: `GET /sign/{token}`.
- Token validation, tamper detection, and expiration handling (404/410).
- Digital sign-off endpoint: `POST /sign/{token}/approve`.
- Immutable BAST certificate generator (`BAST_CERTIFICATE` with JSON snapshot, IP, UA, SHA-256 hash).
- Client revision request endpoint: `POST /sign/{token}/revise`.
- Verification: `tests/Feature/PublicSignoffTest.php`.

### Task 4: Invoicing, Payment Settlement & Retention Watcher Engine (AC-9, AC-10, AC-11)
- Invoice issuance for signed BAST: `POST /api/milestones/:id/invoice`.
- Payment confirmation and status progression to `PAID`.
- Final milestone detection triggering `RETENTION_HOLD` countdown.
- Scheduled Artisan command `terminlock:check-retentions` to mature expired holds.
- Verification: `tests/Feature/InvoiceApiTest.php` and `tests/Feature/RetentionWatcherTest.php`.

### Task 5: Inertia.js React UI, Cash-at-Risk Dashboard & E2E Tests (AC-12)
- Executive Dashboard: Cash-at-Risk KPI cards (`Total Kontrak`, `Kas Cair`, `Cash-at-Risk`, `Retensi`).
- Vertical Milestone Timeline Spine component (`DESIGN.md` token spine compliance).
- Client Sign-off Portal page (mobile-responsive, clean legal seal).
- Playwright E2E full scenario: Project Create → Deliverable Upload → Client Magic Sign-off → BAST Certificate Generated → Invoice Paid → Retention Active.
- Verification: `tests/Feature/DashboardMetricsTest.php` and `frontend/e2e/terminlock.spec.ts`.
