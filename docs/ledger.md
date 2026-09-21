# TerminLock Execution Ledger

Started: Phase 2 (`sop-execution`)
Spec: `docs/spec.md` (spec-anchored)
Handoff: `HANDOFF.md`

## Task & AC Traceability Matrix

| Task | Title | ACs Covered | Status | Evidence |
|---|---|---|---|---|
| T1 | Foundation, Workspace, Client & Project Schema | AC-1, AC-2 | DONE | tests/Feature/ProjectApiTest.php, tests/Feature/MilestoneApiTest.php (4 passed, 12 assertions, mutation confirmed) |
| T2 | Milestone FSM & Deliverables Engine | AC-3, AC-4 | DONE | tests/Feature/DeliverableApiTest.php, tests/Feature/SignoffRequestTest.php (3 passed, 14 assertions, mutation confirmed) |
| T3 | Zero-Login Client Sign-off Portal & Immutable BAST | AC-5, AC-6, AC-7, AC-8 | DONE | tests/Feature/PublicSignoffTest.php (4 passed, 22 assertions, mutation confirmed) |
| T4 | Invoicing, Payment Settlement & Retention Watcher | AC-9, AC-10, AC-11 | DONE | tests/Feature/InvoiceApiTest.php, tests/Feature/RetentionWatcherTest.php (4 passed, 15 assertions, mutation confirmed) |
| T5 | Inertia.js React UI, Dashboard & E2E Tests | AC-12 | DONE | tests/Feature/DashboardMetricsTest.php, e2e/terminlock.spec.ts (2 passed in 1.4s, drift gate 12/12 PASS, local-ci ALL GREEN) |

---

## Log of Rulings & Amendments
- **2026-09-21:** Initial Phase 2 kick-off. Database assigned to port 5434 (Postgres 18) and Redis to port 6381 to prevent port conflicts with FormForge.
