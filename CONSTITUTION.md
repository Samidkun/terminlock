# TerminLock Constitution

These are the non-negotiable architectural and domain invariants for TerminLock. All specifications, implementations, and reviews must enforce these rules.

## 1. Zero Client Login Friction
Clients approving deliverables must never be forced to register, remember passwords, or navigate a portal. 
- All client interactions use cryptographically signed tokens (`sha256`) with expiration.
- Tamper-proofing: Any modification to the milestone or token invalidates access immediately.

## 2. Integer-Only Financial Precision
Floating-point numbers are strictly forbidden for currency values.
- All monetary amounts in Indonesian Rupiah (IDR) are stored as unsigned 64-bit integers (`bigInteger` in DB, `int` in PHP/TypeScript).
- Contract total = Sum of all milestone amounts + retention amount. A contract cannot be saved if milestone sums diverge by even 1 Rupiah.

## 3. Strict Finite State Machine (FSM)
Milestone statuses are strictly bounded by a unidirectional transition graph:
`DRAFT` → `ACTIVE` → `AWAITING_SIGNOFF` → `BAST_SIGNED` → `INVOICED` → `PAID` → `RETENTION_HOLD` → `RETENTION_MATURED` → `SETTLED`.
- Direct manual overriding of statuses is forbidden.
- Transitions must be backed by domain events (e.g. deliverable upload, client sign-off, invoice generation, payment confirmation).

## 4. Immutable BAST Audit Record
Once a client approves a milestone:
- The system generates an immutable cryptographic snapshot containing: signatory name, job title, company, IP address, user-agent, UTC timestamp, and SHA-256 hash of all attached deliverable files.
- Once signed, milestone deliverables and amounts can never be modified or deleted.

## 5. Fail-Closed Security & Ownership
- Multi-tenancy: Every project, client, and milestone belongs to an authenticated agency `workspace_id`.
- Foreign key cascading: Deleting a workspace purges internal data; signed BAST certificates retain cryptographic integrity in cold storage.
- External magic links are strictly rate-limited and single-purpose.
