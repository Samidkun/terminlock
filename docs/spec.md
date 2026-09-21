# TerminLock Specification (`docs/spec.md`)

Specification level: **spec-anchored** (enforced by `spec-drift-gate`).

## 1. Overview
TerminLock is a specialized milestone billing, digital BAST sign-off, and retention payment tracking system for agency and IT project vendors.

---

## 2. Acceptance Criteria (EARS Syntax)

### AC-1: Project Creation & Contract Sum Invariant
- **Trigger:** When an agency user creates a new project with client details, contract number, and total amount.
- **System Behavior:** The system SHALL validate that `total_amount` is an integer greater than 0 and unique for the contract number within the workspace.
- **Verify:** `tests/Feature/ProjectApiTest.php::test_creates_project_with_integer_contract_amount`

### AC-2: Milestone Breakdown Must Equal 100% of Contract Value
- **Trigger:** When milestones are defined for a project.
- **System Behavior:** The system SHALL reject the milestone configuration if the sum of all milestone amounts does not strictly equal the project's `total_amount`.
- **Verify:** `tests/Feature/MilestoneApiTest.php::test_rejects_milestone_sum_mismatch`

### AC-3: Milestone Deliverable Attachment
- **Trigger:** When a Project Manager uploads a deliverable to an `ACTIVE` milestone.
- **System Behavior:** The system SHALL store the file in storage disk, generate a secure attachment record, and allow marking the milestone as ready for sign-off.
- **Verify:** `tests/Feature/DeliverableApiTest.php::test_attaches_deliverable_to_milestone`

### AC-4: Cryptographic Sign-off Token Generation
- **Trigger:** When a PM transitions a milestone to `AWAITING_SIGNOFF`.
- **System Behavior:** The system SHALL generate a unique, cryptographically random token (minimum 32 bytes entropy), store its SHA-256 hash in `signoff_requests`, set an expiration date (default 7 days), and produce a public sign-off URL.
- **Verify:** `tests/Feature/SignoffRequestTest.php::test_generates_secure_signoff_token`

### AC-5: Zero-Login Client Sign-off Portal Access
- **Trigger:** When an external client accesses `GET /sign/:token`.
- **System Behavior:** The system SHALL validate the token hash and expiration without requiring authentication, and render the contract summary, milestone deliverables, and attached files.
- **Verify:** `tests/Feature/PublicSignoffTest.php::test_client_accesses_signoff_portal_without_auth`

### AC-6: Digital BAST Sign-off and Immutable Snapshot
- **Trigger:** When a client submits signatory name, title, and confirmation on a valid sign-off portal.
- **System Behavior:** The system SHALL record the client's IP address, user-agent, and timestamp; generate a unique BAST certificate number; freeze an immutable JSON snapshot of all deliverables; calculate a SHA-256 checksum; and advance the milestone status to `BAST_SIGNED`.
- **Verify:** `tests/Feature/PublicSignoffTest.php::test_client_approves_bast_generates_certificate`

### AC-7: Client Revision Request
- **Trigger:** When a client submits revision notes instead of approval.
- **System Behavior:** The system SHALL record the rejection notes, revert the milestone status back to `ACTIVE`, and notify the agency PM.
- **Verify:** `tests/Feature/PublicSignoffTest.php::test_client_requests_revision_reverts_milestone`

### AC-8: Token Expiration and Tamper Invalidation
- **Trigger:** When an expired, malformed, or previously signed token is accessed.
- **System Behavior:** The system SHALL return a 404 or 410 error state explaining that the link is no longer valid, and prevent any approval action.
- **Verify:** `tests/Feature/PublicSignoffTest.php::test_expired_or_invalid_token_is_rejected`

### AC-9: Invoice Issuance and Milestone Transition
- **Trigger:** When Finance creates an invoice for a `BAST_SIGNED` milestone.
- **System Behavior:** The system SHALL require an invoice reference number and due date, transition milestone status to `INVOICED`, and update the project's cash-at-risk totals.
- **Verify:** `tests/Feature/InvoiceApiTest.php::test_issues_invoice_for_signed_bast`

### AC-10: Payment Settlement and Retention Hold Activation
- **Trigger:** When Finance confirms receipt of payment for a milestone.
- **System Behavior:** The system SHALL mark the invoice as `PAID`, advance the milestone status to `PAID`, and if the milestone is the final delivery milestone, automatically transition the retention milestone to `RETENTION_HOLD` with a countdown date.
- **Verify:** `tests/Feature/InvoiceApiTest.php::test_confirm_payment_triggers_retention_hold`

### AC-11: Retention Maturity Watcher Engine
- **Trigger:** When the scheduled command `php artisan terminlock:check-retentions` executes.
- **System Behavior:** The system SHALL scan all milestones in `RETENTION_HOLD`. For milestones where `now() >= due_date`, it SHALL update status to `RETENTION_MATURED` and emit an alert for Finance.
- **Verify:** `tests/Feature/RetentionWatcherTest.php::test_retention_watcher_matures_expired_holds`

### AC-12: Cash-at-Risk Radar Aggregation
- **Trigger:** When an agency user visits the executive dashboard.
- **System Behavior:** The system SHALL compute in real time: `Total Kontrak`, `Kas Diterima`, `Cash-at-Risk` (sum of milestones in `AWAITING_SIGNOFF`, `BAST_SIGNED`, and `INVOICED`), and `Uang Retensi Tertahan`.
- **Verify:** `tests/Feature/DashboardMetricsTest.php::test_calculates_cash_at_risk_metrics_correctly`
