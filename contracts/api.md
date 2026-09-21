# TerminLock API Contract (`contracts/api.md`)

## 1. Authentication & Workspace
All `/api/*` endpoints require `Authorization: Bearer <sanctum_token>` unless marked `[Public]`.

### POST /api/auth/login
- **Request:** `{ "email": "user@agency.com", "password": "securepassword" }`
- **Response 200:** `{ "success": true, "data": { "token": "1|abc...", "user": { "id": "uuid", "name": "...", "role": "..." } } }`

---

## 2. Projects & Milestones

### POST /api/projects
- **Request:**
  ```json
  {
    "client_id": "uuid",
    "name": "E-Commerce Replatforming",
    "contract_number": "CTR/2026/09/001",
    "total_amount": 150000000,
    "retention_percentage": 5,
    "retention_days": 180
  }
  ```
- **Response 201:** `{ "success": true, "data": { "id": "uuid", "name": "...", "total_amount": 150000000 } }`

### POST /api/projects/{id}/milestones
- **Request:**
  ```json
  {
    "milestones": [
      { "name": "DP Kickoff", "percentage": 30, "amount": 45000000, "due_date": "2026-10-01" },
      { "name": "Fase 1: Backend Architecture", "percentage": 40, "amount": 60000000, "due_date": "2026-11-01" },
      { "name": "Fase 2: UAT & Launch", "percentage": 25, "amount": 37500000, "due_date": "2026-12-01" },
      { "name": "Garansi Pemeliharaan (Retensi)", "percentage": 5, "amount": 7500000, "is_retention": true, "due_date": "2027-06-01" }
    ]
  }
  ```
- **Validation:** Sum of amounts must equal `project.total_amount` (strict 100%).
- **Response 201:** `{ "success": true, "data": [ ... ] }`

### POST /api/milestones/{id}/deliverables
- **Request:** multipart/form-data with `title`, `description`, optional `staging_url`, and `file`.
- **Response 201:** `{ "success": true, "data": { "id": "uuid", "title": "...", "file_path": "..." } }`

### POST /api/milestones/{id}/request-signoff
- **Request:** `{ "expires_in_days": 7 }`
- **Response 200:**
  ```json
  {
    "success": true,
    "data": {
      "signoff_url": "http://localhost:8000/sign/9f8e7d6c5b4a...",
      "token": "raw_token_for_display_only",
      "expires_at": "2026-10-08T00:00:00Z"
    }
  }
  ```

---

## 3. Public Client Sign-off Portal [Public / Zero-Login]

### GET /sign/{token}
- **Response 200:**
  ```json
  {
    "success": true,
    "data": {
      "project_name": "E-Commerce Replatforming",
      "contract_number": "CTR/2026/09/001",
      "milestone_name": "Fase 1: Backend Architecture",
      "amount": 60000000,
      "deliverables": [
        { "title": "API Documentation", "file_url": "...", "staging_url": "..." }
      ],
      "expires_at": "2026-10-08T00:00:00Z"
    }
  }
  ```
- **Response 404/410:** `{ "success": false, "error": { "code": "INVALID_TOKEN", "message": "Link persetujuan tidak valid atau sudah kedaluwarsa." } }`

### POST /sign/{token}/approve
- **Request:**
  ```json
  {
    "signatory_name": "Budi Santoso",
    "signatory_title": "Direktur Teknologi"
  }
  ```
- **Response 200:**
  ```json
  {
    "success": true,
    "data": {
      "bast_number": "BAST/2026/09/001-M1",
      "signed_at": "2026-09-21T10:00:00Z",
      "sha256_checksum": "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855",
      "certificate_url": "/bast/download/..."
    }
  }
  ```

### POST /sign/{token}/revise
- **Request:** `{ "rejection_notes": "Mohon perbaiki modul checkout sebelum BAST disetujui." }`
- **Response 200:** `{ "success": true, "message": "Catatan revisi telah dikirim ke tim vendor." }`

---

## 4. Invoicing & Cash-at-Risk Dashboard

### POST /api/milestones/{id}/invoice
- **Request:** `{ "invoice_number": "INV/2026/09/101", "due_date": "2026-10-15" }`
- **Response 201:** `{ "success": true, "data": { "id": "uuid", "status": "issued" } }`

### POST /api/invoices/{id}/pay
- **Request:** `{ "paid_at": "2026-10-10", "notes": "Transfer BCA" }`
- **Response 200:** `{ "success": true, "data": { "status": "paid" } }`

### GET /api/dashboard/metrics
- **Response 200:**
  ```json
  {
    "success": true,
    "data": {
      "total_contract_value": 450000000,
      "total_cash_collected": 180000000,
      "cash_at_risk": 97500000,
      "retention_held": 22500000,
      "retention_matured": 7500000
    }
  }
  ```
