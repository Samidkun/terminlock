# TerminLock Design System (`DESIGN.md`)

## 1. Product Identity & Audience
- **Subject:** Milestone billing, digital Berita Acara Serah Terima (BAST) sign-off, and retention payment tracking.
- **Audience:** 
  1. Agency Owners & Finance Managers (need immediate cash visibility and overdue alerts).
  2. Project Managers (need clear deliverable proofs and sign-off status).
  3. External Corporate Clients (need a simple, credible, 1-click mobile-friendly sign-off page).
- **Primary Job:** Eliminate billing limbo by transforming completed deliverables into legally defensible BAST sign-offs and on-time cash payments.

## 2. Voice & Tone
- **Voice:** Decisive, Defensible, Grounded.
- **Copy Rules:**
  - Sentence case everywhere (no ALL-CAPS headers).
  - Concrete financial actions: "Terbitkan BAST", "Kirim Tagihan", "Konfirmasi Bayar" (never "Submit" or "Click Here").
  - Clear error explanations: "Klien belum menyetujui BAST Termin 2. Invoice belum dapat diterbitkan."
  - Zero AI marketing hype ("seamlessly empower" strictly forbidden).

## 3. The Signature Bet
**"The Cash-at-Risk Milestone Spine"**:
A dense, high-contrast vertical timeline tracking project milestones. Each milestone block directly ties contract deliverables to real rupiah values with live risk-state coloring (Amber for sign-off pending, Emerald for signed/paid, Crimson for overdue retentions). A persistent sticky executive bar highlights `Total Kontrak`, `Kas Cair`, and `Cash-at-Risk`.

## 4. Design Knobs
- `DESIGN_VARIANCE`: **3 / 10** (Corporate-grade financial utility; familiar, structured, zero eccentric layouts).
- `VISUAL_DENSITY`: **8 / 10** (Compact tables, sticky metrics, data-rich cards fitting modern desktop viewports).
- `MOTION_INTENSITY`: **2 / 10** (Restrained 150ms ease-out transitions for modals and drawer expansions; respects `prefers-reduced-motion`).

## 5. Token Spine

### Color System (Semantic Slate & Audit Palette)
- **Canvas / Background:** `#0B0F17` (Deep corporate slate, never pure `#000000`)
- **Surface Primary (Cards/Panels):** `#161F2E`
- **Surface Secondary (Tables/Inputs):** `#1E293B`
- **Border Hairline:** `#334155`
- **Text Primary:** `#F8FAFC`
- **Text Secondary:** `#94A3B8`
- **Text Muted:** `#64748B`
- **Status & Risk Accents:**
  - **Success / Liquid Cash:** `#10B981` (Emerald) / `#059669`
  - **Warning / Cash-at-Risk / Pending Sign-off:** `#F59E0B` (Amber) / `#D97706`
  - **Danger / Overdue Retention:** `#EF4444` (Crimson)
  - **Brand Primary:** `#3B82F6` (Royal Blue for primary buttons/nav)
- **BANNED:** Vibecoding purple/cyan gradients (`#8B5CF6` to `#06B6D4`), neon glowing cards, glassmorphism blur.

### Typography
- **Primary Body & Headings:** `Plus Jakarta Sans`, sans-serif (clean, professional Indonesian corporate readability).
- **Monospace (Numbers, Hashes, Currency):** `JetBrains Mono` or `ui-monospace` for all IDR figures (`Rp 45.000.000`) and signed token fingerprints.
- **Scale:**
  - Executive Metrics: `24px` / bold (`1.5rem`)
  - Section Headings: `18px` / semibold (`1.125rem`)
  - Body Text: `14px` / regular (`0.875rem`)
  - Subtext & Badges: `12px` / medium (`0.75rem`)

### Spatial & Shape Rules
- **Border Radius:**
  - Buttons & Inputs: `6px` (`rounded-md`)
  - Cards & Modals: `8px` (`rounded-lg`)
  - Never `rounded-full` pill buttons for business actions.
- **Elevation / Depth:** Flat borders (`1px solid #334155`) with subtle dark ambient shadow (`0 4px 12px rgba(0,0,0,0.25)`).

## 6. Required UI States (5 Mandatory States per Screen)
1. **Empty State:** Explains what is missing and provides the primary CTA (e.g. "Belum ada milestone proyek. Tambah termin pertama untuk mengaktifkan penagihan.").
2. **Loading State:** Content-preserving skeletons matching exact table/card dimensions; no full-screen blank spinners.
3. **Error Boundary:** Human-readable problem description, error trace id, and a recovery action button.
4. **Success State:** Instant optimistic feedback + toast notification with undo capability where appropriate.
5. **Edge-Case / Overflow:** Truncation with tooltip for long project titles, responsive tables with horizontal scroll indicators, formatted IDR up to 12 digits (`Rp 999.999.999.999`).
