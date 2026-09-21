# TerminLock UX Floor & Information Architecture (`docs/ux-floor.md`)

## 1. Primary User Flows

### Flow A: Agency PM Delivers Milestone & Generates BAST Link
1. **Entry:** PM navigates to Project Detail → Milestones Tab.
2. **Action:** Clicks "Kirim Deliverable" on an `ACTIVE` milestone.
3. **Input:** Attaches proof documents (PDF/Zip/Screenshots) or Staging URL + Release notes.
4. **Transition:** Clicks "Minta Tanda Tangan Klien" → Status updates to `AWAITING_SIGNOFF`.
5. **Output:** System displays a copyable secure sign-off link + WhatsApp template message.

### Flow B: Client Review & 1-Click Digital Sign-off (External, Zero Login)
1. **Entry:** Client opens `https://terminlock.app/sign/:token` from email/WhatsApp on mobile or desktop.
2. **Verification:** System validates token validity, IP, and non-expiration.
3. **Inspection:** Client reviews the contract details, milestone deliverables, and attached files.
4. **Decision:** 
   - Option 1 (Approve): Client inputs Signatory Name & Designation → clicks "Setujui BAST & Terbitkan Tagihan".
   - Option 2 (Revision): Client inputs revision notes → clicks "Minta Revisi Deliverable" (milestone reverts to `ACTIVE` with notes).
5. **Confirmation:** System displays a verified seal, records cryptographic audit trail, and offers instant download of the PDF BAST.

### Flow C: Finance Invoice & Payment Settlement
1. **Notification:** Finance receives alert: "Milestone 1 disetujui oleh PT Klien. Invoice siap terbit."
2. **Action:** Finance inputs Invoice Number & Due Date → clicks "Tandai Invoice Terbit" (`INVOICED`).
3. **Settlement:** Client pays via bank transfer → Finance confirms "Konfirmasi Dana Masuk" (`PAID`).
4. **Automation:** If the milestone was final, the system automatically starts the **Retention Hold Countdown** (e.g. 180 days).

---

## 2. Nielsen's 10 Usability Heuristics Mapping

1. **Visibility of System Status:**
   - Sticky executive bar shows live sums: `Total Kontrak`, `Kas Cair`, and `Cash-at-Risk`.
   - Milestone badges show clear status pills with distinct semantic colors.
2. **Match Between System and Real World:**
   - Terminology uses standard Indonesian business terminology: *Termin, BAST (Berita Acara Serah Terima), Uang Retensi, Uang Muka (DP), Sisa Tagihan*.
3. **User Control and Freedom:**
   - Draft milestones can be reordered and edited before activation.
   - Accidental actions trigger confirmation dialogues with explicit consequences.
4. **Consistency and Standards:**
   - Currency is consistently formatted in Indonesian format: `Rp 150.000.000`.
   - Primary action buttons are always located at top-right or bottom-right of forms.
5. **Error Prevention:**
   - Validation prevents saving milestone breakdowns that do not equal 100% of the total contract value.
   - Deletion of signed projects is permanently blocked.
6. **Recognition Rather Than Recall:**
   - The sign-off screen clearly outlines past completed milestones so the client sees full context of what came before.
7. **Flexibility and Efficiency of Use:**
   - 1-click WhatsApp copy button for PMs to ping clients.
   - Keyboard shortcuts for navigating table rows.
8. **Aesthetic and Minimalist Design:**
   - Information density: eliminates decorative cards; prioritizes contract figures, dates, and sign-off status.
9. **Help Users Recognize, Diagnose, and Recover from Errors:**
   - Invalid token shows: "Tautan persetujuan ini sudah kedaluwarsa atau telah ditandatangani. Hubungi Project Manager Anda untuk mendapatkan tautan baru."
10. **Help and Documentation:**
    - Contextual tooltips explaining legal implications of digital BAST sign-off under UU ITE.

---

## 3. Microcopy Rules
- **CTA Buttons:**
  - Good: "Ajukan BAST ke Klien", "Setujui BAST & Kunci Termin", "Catat Dana Masuk"
  - Forbidden: "Submit", "OK", "Next", "Click Here"
- **Empty States:**
  - Projects: "Belum ada kontrak proyek. Buat proyek pertama untuk mengatur termin penagihan."
  - Deliverables: "Belum ada dokumen deliverable. Unggah laporan atau lampiran hasil kerja sebelum meminta persetujuan klien."
- **Confirmation Modals:**
  - "Tanda tangani Berita Acara Serah Terima untuk Termin 1 senilai Rp 50.000.000? Tindakan ini akan mengunci deliverable dan menerbitkan tagihan resmi."
