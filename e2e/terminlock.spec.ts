import { test, expect } from '@playwright/test';

test.describe('TerminLock End-to-End Suite', () => {
  test('Executive Dashboard displays Cash-at-Risk radar and milestone spine', async ({ page }) => {
    await page.goto('/');

    // 1. Verify Branding & Executive Radar Bar
    await expect(page.getByText('TerminLock')).toBeVisible();
    await expect(page.getByText('Agency Cash-at-Risk Radar')).toBeVisible();

    // 2. Check Metric Cards
    await expect(page.getByText('Total Kontrak')).toBeVisible();
    await expect(page.getByText('Kas Cair (Lunas)')).toBeVisible();
    await expect(page.getByText('Cash-at-Risk (Tertahan)')).toBeVisible();

    // 3. Verify Project and Milestone Spine
    await expect(page.getByText('Core Banking Transformation')).toBeVisible();
    await expect(page.getByText('CTR/BANK/2026/09')).toBeVisible();
    await expect(page.getByText('Termin 1 (DP 30%)')).toBeVisible();
    await expect(page.getByText('Lunas', { exact: true })).toBeVisible();
    await expect(page.getByText('Menunggu BAST Klien')).toBeVisible();
  });

  test('Public Client Portal allows zero-login digital BAST sign-off with audit fingerprint', async ({ page }) => {
    const token = 'e2e-token-valid-12345';
    await page.goto(`/sign/${token}`);

    // 1. Verify Portal Branding and Scope
    await expect(page.getByText('Persetujuan BAST Resmi')).toBeVisible();
    await expect(page.getByText('Core Banking Transformation')).toBeVisible();
    await expect(page.getByText('Termin 2 (Backend Core & Schema 40%)')).toBeVisible();
    await expect(page.getByText('Core Banking Schema v1 & OpenAPI Specs')).toBeVisible();

    // 2. Complete Sign-off Form
    const nameInput = page.locator('#signatory_name');
    const titleInput = page.locator('#signatory_title');

    await nameInput.fill('Hendra Wijaya');
    await titleInput.fill('Head of Digital Transformation');

    // 3. Submit Approval
    const approveBtn = page.getByRole('button', { name: /setujui bast/i });
    await approveBtn.click();

    // 4. Verify Legally Defensible BAST Certificate Screen
    await expect(page.getByText('Berita Acara Disetujui')).toBeVisible();
    await expect(page.getByText(/BAST\/\d{4}\/\d{2}\//)).toBeVisible();
    await expect(page.getByText('Integritas Checksum (SHA-256):')).toBeVisible();
  });
});
