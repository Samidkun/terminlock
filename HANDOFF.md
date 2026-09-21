# HANDOFF — TerminLock

This is the frozen contract between `sop-planning` (Phase 1) and `sop-execution` (Phase 2).
It is validated fail-closed at E0 before any production code is written.

---

## 1. Identity
- **Project:** TerminLock
- **Tier:** T0 — Personal / Portfolio Berkualitas Tinggi
- **Team Size:** Solo developer (no PR ceremony / no branch protection overhead)
- **Ceremony Level:** M — Standard
- **Spec Level:** spec-anchored (enforced by `spec-drift-gate`)
- **Constitution:** `CONSTITUTION.md` (Integer money, zero client login, strict FSM, immutable BAST)

---

## 2. Goal & Scope
- **Goal:** Build an agency-grade milestone billing, digital BAST sign-off, and retention tracking system to eliminate billing limbo and uncollected retention revenue.
- **In Scope:**
  1. Contract & milestone configuration with strict 100% sum validation.
  2. Deliverables attachment (PDF/image uploads, staging URLs).
  3. Zero-login public client sign-off portal accessed via secure SHA-256 magic token.
  4. Immutable BAST digital certificate snapshot with client IP, UA, timestamp, and checksum.
  5. Invoicing and payment recording with automatic retention hold countdown.
  6. Retention watcher engine (`php artisan terminlock:check-retentions`) alerting matured warranty funds.
  7. Executive Cash-at-Risk radar metrics dashboard.

---

## 2b. Boundaries
- **Always:** Use integer for IDR money; enforce unidirectional milestone FSM; record cryptographic audit on client BAST approvals; write tests before implementation (RED-first).
- **Ask First:** Adding external payment gateways (Midtrans/Xendit); adding complex multi-currency support; changing database engine from PostgreSQL.
- **Never:** Use floating-point numbers for currency; force external clients to register an account; allow editing or deleting signed BAST deliverables; introduce generic AI-slop purple gradients.

---

## 2c. Capability Map
`N/A — Single integrated business domain (Milestone & Retention Billing Engine)`

---

## 3. Prompt-Roast Result
- **Classification:** Green-field B2B business information system.
- **Roast Score:** 9/10 (High real-world pain, strong alignment with SIB thesis topics, clear operational moat).
- **Assumptions & Rulings:**
  - *Ruling 1:* Clients will not register or log in. Access must be via cryptographically signed tokens (`sha256(token)` in DB, raw token sent in URL).
  - *Ruling 2:* Payment gateway integration is out-of-scope for v1. Payment confirmation is recorded manually by Finance with reference proof.
  - *Ruling 3:* Database is PostgreSQL 18 with integer IDR cents/rupiah.

---

## 4. Design Contract
- **4a. `DESIGN.md` spine:** `DESIGN.md`
  - Colors: Deep Corporate Slate (`#0B0F17`, `#161F2E`, `#334155`), Emerald Cash (`#10B981`), Amber Risk (`#F59E0B`). Zero purple slop.
  - Typography: `Plus Jakarta Sans` for UI, `JetBrains Mono` for currency & token hashes.
  - Signature Bet: "The Cash-at-Risk Milestone Spine".
  - Knobs: `DESIGN_VARIANCE` = 3, `VISUAL_DENSITY` = 8, `MOTION_INTENSITY` = 2.
- **4b. UI Detail Checklist:** 5 mandatory states (empty, loading skeleton, error boundary, success, edge-case) defined in `DESIGN.md`.
- **4c. UX Floor (P2.4):** `docs/ux-floor.md` (User flows, Nielsen 10 heuristics, Indonesian microcopy rules).
- **4d. Diagrams as code (P2.6):** `docs/diagrams/diagrams.md`
  - ERD: Mermaid entity relationship diagram.
  - Architecture: Flowchart of client, PM, Finance, Laravel, and PostgreSQL.
  - Sequence: Digital BAST sign-off and billing transition.

---

## 5. Acceptance Criteria — EARS, binary-testable
- **AC-1:** Project creation validates integer `total_amount` > 0 and unique contract number.
- **AC-2:** Milestone breakdown sum must equal exactly 100% of project total amount.
- **AC-3:** Milestone deliverable attachment stores file and allows readying milestone for sign-off.
- **AC-4:** Transition to `AWAITING_SIGNOFF` produces secure token with SHA-256 hash and 7-day expiration.
- **AC-5:** Public sign-off portal `GET /sign/{token}` displays deliverables without client login.
- **AC-6:** Client digital sign-off records IP/UA/timestamp and generates immutable BAST certificate.
- **AC-7:** Client revision request records notes and reverts milestone to `ACTIVE`.
- **AC-8:** Expired or tampered tokens return 404/410 and reject sign-off attempts.
- **AC-9:** Invoice creation advances milestone to `INVOICED` and updates cash-at-risk.
- **AC-10:** Payment confirmation advances milestone to `PAID` and triggers retention hold countdown.
- **AC-11:** Scheduled command `terminlock:check-retentions` matures expired holds and alerts Finance.
- **AC-12:** Executive dashboard aggregates Total Kontrak, Kas Diterima, and Cash-at-Risk in real time.

---

## 5b. Drift Contract (spec-drift-gate)
- Spec file: `docs/spec.md`
- Drift gate command: `bash scripts/drift-gate.sh`
- Coupling rule: Spec change and code change land in the same commit. Acceptance tests map 1:1 to AC ids.

---

## 6. Feature Completeness
- All 12 acceptance criteria defined and binary-testable.
- Exclusions explicitly stated: No payment gateway, no multi-currency, no task kanban board.

---

## 7. Interfaces & File Paths
- Backend Core: `backend/app/Models/`, `backend/app/Http/Controllers/`, `backend/routes/api.php`, `backend/routes/web.php`
- State Machine: `backend/app/Domain/MilestoneState.php`
- Retention Watcher: `backend/app/Console/Commands/CheckRetentionsCommand.php`
- Frontend Inertia: `frontend/resources/js/Pages/`, `frontend/resources/js/Components/MilestoneSpine.tsx`
- Database Migrations: `backend/database/migrations/`
- Tests: `backend/tests/Feature/` and `e2e/terminlock.spec.ts`

---

## 7b. Interface Contracts
- API specification: `contracts/api.md`
- Enforced via: PHP feature tests asserting JSON structures and status codes.

---

## 8. Constraints
- Framework: Laravel 12 + Inertia.js + React 19 + TypeScript.
- Database: PostgreSQL 18.
- Styling: Tailwind CSS v4.
- Currency: IDR integer representation only.

---

## 9. Plan Artifact
- Plan file: `docs/plan.md`
- 5 sequential implementation tasks (TDD RED-first).

---

## 10. Factory Bootstrap
- Docker Compose: PostgreSQL 18 on port 5434 (avoid collision with FormForge on 5433).
- Test suite: PHPUnit 12 + Vitest + Playwright.
- Local CI: `scripts/local-ci.sh`.

---

## 11. Accepted Risks
- Single currency (IDR only) accepted for v1 portfolio scope.
- Manual bank transfer confirmation accepted instead of webhook payment gateway.

---

## 12. Amendments
- None. Initial frozen baseline.

---

## 13. P7 Readiness Gate
- [x] Tier + team + ceremony level written (§1)
- [x] Spec level declared (spec-anchored) (§1)
- [x] Constitution present (`CONSTITUTION.md`) (§1)
- [x] Boundaries recorded (§2b)
- [x] Capability map approved or marked N/A (§2c)
- [x] Prompt-roast scored; gaps answered (§3)
- [x] False premise checked against requirements (§3)
- [x] Assumptions surfaced; rulings recorded (§3)
- [x] Clarify gate passed (§3)
- [x] Brainstorm approval obtained, in writing (§2/P1)
- [x] `DESIGN.md` present; token spine + signature bet established (§4)
- [x] UX floor present (`docs/ux-floor.md`) (§4)
- [x] Diagrams as code present (`docs/diagrams/diagrams.md`) (§4)
- [x] Interface contracts frozen (`contracts/api.md`) (§7b)
- [x] EARS acceptance criteria written with Verify targets (§5)
- [x] Drift contract recorded (§5b)
- [x] Feature completeness decided (§6)
- [x] Interfaces & file paths named (§7)
- [x] Constraints verbatim recorded (§8)
- [x] Plan artifact written (`docs/plan.md`) (§9)
- [x] Accepted risks section reflects reality (§11)
