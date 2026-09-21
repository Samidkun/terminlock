# TerminLock Execution Ledger

Started: Phase 2 (`sop-execution`)
Spec: `docs/spec.md` (spec-anchored)
Handoff: `HANDOFF.md`

## Task & AC Traceability Matrix

| Task | Title | ACs Covered | Status | Evidence |
|---|---|---|---|---|
| T1 | Foundation, Workspace, Client & Project Schema | AC-1, AC-2 | DONE | tests/Feature/ProjectApiTest.php, tests/Feature/MilestoneApiTest.php (4 passed, 12 assertions, mutation confirmed) |
| T2 | Milestone FSM & Deliverables Engine | AC-3, AC-4 | DONE | tests/Feature/DeliverableApiTest.php, tests/Feature/SignoffRequestTest.php (3 passed, 14 assertions, mutation confirmed) |
| T3 | Zero-Login Client Sign-off Portal & Immutable BAST | AC-5, AC-6, AC-7, AC-8 | PENDING | - |
| T4 | Invoicing, Payment Settlement & Retention Watcher | AC-9, AC-10, AC-11 | PENDING | - |
| T5 | Inertia.js React UI, Dashboard & E2E Tests | AC-12 | PENDING | - |

---

## Log of Rulings & Amendments
- **2026-09-21:** Initial Phase 2 kick-off. Database assigned to port 5434 (Postgres 18) and Redis to port 6381 to prevent port conflicts with FormForge.
