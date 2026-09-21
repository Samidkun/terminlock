# TerminLock Diagrams as Code (`docs/diagrams/diagrams.md`)

## 1. Entity Relationship Diagram (Mermaid ERD)

```mermaid
erDiagram
    WORKSPACE ||--o{ USER : contains
    WORKSPACE ||--o{ CLIENT : manages
    WORKSPACE ||--o{ PROJECT : owns
    CLIENT ||--o{ PROJECT : commissions
    PROJECT ||--|{ MILESTONE : partitions
    MILESTONE ||--o{ DELIVERABLE : proves
    MILESTONE ||--o{ SIGNOFF_REQUEST : issues
    SIGNOFF_REQUEST ||--o| BAST_CERTIFICATE : certifies
    MILESTONE ||--o| INVOICE : bills

    WORKSPACE {
        uuid id PK
        string name
        timestamps created_at
    }

    USER {
        uuid id PK
        uuid workspace_id FK
        string name
        string email
        string role
        timestamps created_at
    }

    CLIENT {
        uuid id PK
        uuid workspace_id FK
        string name
        string company_name
        string email
        string phone
        timestamps created_at
    }

    PROJECT {
        uuid id PK
        uuid workspace_id FK
        uuid client_id FK
        string name
        string contract_number
        bigint total_amount "Integer Rupiah"
        integer retention_percentage
        integer retention_days
        string status "active, completed, archived"
        timestamps created_at
    }

    MILESTONE {
        uuid id PK
        uuid project_id FK
        integer order
        string name
        bigint amount "Integer Rupiah"
        integer percentage
        string status "draft, active, awaiting_signoff, bast_signed, invoiced, paid, retention_hold, retention_matured, settled"
        date due_date
        boolean is_retention
        timestamps created_at
    }

    DELIVERABLE {
        uuid id PK
        uuid milestone_id FK
        string title
        text description
        string file_path
        string staging_url
        timestamps created_at
    }

    SIGNOFF_REQUEST {
        uuid id PK
        uuid milestone_id FK
        string token_hash "SHA-256 unique"
        timestamp expires_at
        string status "pending, signed, rejected, expired"
        string signatory_name
        string signatory_title
        string client_ip
        text client_user_agent
        timestamp signed_at
        text rejection_notes
        timestamps created_at
    }

    BAST_CERTIFICATE {
        uuid id PK
        uuid signoff_request_id FK
        uuid milestone_id FK
        string bast_number "Unique BAST Reference"
        json snapshot_data "Immutable state snapshot"
        string sha256_checksum "Integrity fingerprint"
        string pdf_path
        timestamps created_at
    }

    INVOICE {
        uuid id PK
        uuid milestone_id FK
        string invoice_number
        bigint amount "Integer Rupiah"
        date issued_at
        date due_at
        date paid_at
        string payment_proof_path
        string status "draft, issued, paid, overdue"
        timestamps created_at
    }
```

---

## 2. High-Level Architecture Diagram

```mermaid
flowchart TD
    ClientBrowser[Client Mobile/Desktop Browser] -->|Magic Link /sign/:token| PublicRouter[Laravel Public Route & RateLimiter]
    AgencyUser[Agency PM / Finance / Owner] -->|Auth Session / Sanctum| InertiaRouter[Inertia.js React App]

    InertiaRouter --> PMController[Project & Milestone Controller]
    InertiaRouter --> FinanceController[Invoice & Retention Controller]
    PublicRouter --> SignoffController[Public Client Signoff Controller]

    PMController --> MilestoneFSM[Milestone Finite State Machine]
    SignoffController --> MilestoneFSM
    FinanceController --> MilestoneFSM

    MilestoneFSM --> DB[(PostgreSQL 18)]
    SignoffController --> AuditLogger[Immutable Cryptographic Audit Logger]
    AuditLogger --> DB

    CronWorker[Laravel Schedule Runner] --> RetentionWatcher[Retention Maturity Watcher Job]
    RetentionWatcher --> DB
    RetentionWatcher --> NotificationEngine[Email & Dashboard Alert Service]
```

---

## 3. Sequence Diagram: BAST Digital Sign-off & Billing Transition

```mermaid
sequenceDiagram
    autonumber
    actor PM as Project Manager
    participant App as TerminLock Core
    participant DB as PostgreSQL
    actor Client as Client PIC (Signatory)
    actor Finance as Agency Finance

    PM->>App: Upload Deliverable & click "Ajukan BAST"
    App->>DB: Update Milestone (status = AWAITING_SIGNOFF)
    App->>DB: Create SignoffRequest (token_hash, expires_in = 7d)
    App-->>PM: Display copyable sign-off link & WhatsApp template
    PM->>Client: Send magic sign-off link
    Client->>App: Open /sign/:token
    App->>DB: Validate token validity & non-expiration
    App-->>Client: Render Deliverables, Contract Scope & Sign-off Form
    Client->>App: Input Name, Title & click "Setujui BAST"
    App->>DB: Record IP, Timestamp, SHA-256 Checksum into BAST_CERTIFICATE
    App->>DB: Transition Milestone to BAST_SIGNED
    App-->>Client: Render Verified Seal & Download BAST PDF button
    App->>Finance: Notify "BAST Termin Ditandatangani! Invoice Siap Terbit"
    Finance->>App: Input Invoice Number & Issue Invoice
    App->>DB: Update Milestone to INVOICED
    Finance->>App: Confirm Client Bank Transfer
    App->>DB: Update Milestone to PAID
    opt Is Final Milestone
        App->>DB: Transition Retention Milestone to RETENTION_HOLD (start countdown)
    end
```
