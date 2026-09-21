import React, { useState } from 'react';
import { formatIDR } from '@/Components/MilestoneSpine';

interface DeliverableItem {
  id: string;
  title: string;
  description?: string | null;
  file_url?: string | null;
  staging_url?: string | null;
}

interface SignoffPortalProps {
  token: string;
  data: {
    project_name: string;
    contract_number: string;
    milestone_name: string;
    amount: number;
    deliverables: DeliverableItem[];
    expires_at: string;
    status: string;
  };
}

export default function SignoffPortal({ token, data }: SignoffPortalProps) {
  const [signatoryName, setSignatoryName] = useState('');
  const [signatoryTitle, setSignatoryTitle] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [signedResult, setSignedResult] = useState<{
    bast_number: string;
    signed_at: string;
    sha256_checksum: string;
  } | null>(null);

  const [isRevising, setIsRevising] = useState(false);
  const [rejectionNotes, setRejectionNotes] = useState('');
  const [revisedSubmitted, setRevisedSubmitted] = useState(false);

  const handleApprove = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!signatoryName || !signatoryTitle) {
      setErrorMsg('Mohon isi nama lengkap dan jabatan penandatangan.');
      return;
    }

    setIsSubmitting(true);
    setErrorMsg(null);

    try {
      const res = await fetch(`/sign/${token}/approve`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({
          signatory_name: signatoryName,
          signatory_title: signatoryTitle,
        }),
      });

      const json = await res.json();
      if (!res.ok || !json.success) {
        throw new Error(json?.error?.message || 'Gagal menyetujui BAST.');
      }

      setSignedResult(json.data);
    } catch (err: any) {
      setErrorMsg(err?.message || 'Terjadi kesalahan sistem.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleRevise = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!rejectionNotes) {
      setErrorMsg('Mohon masukkan catatan revisi yang jelas.');
      return;
    }

    setIsSubmitting(true);
    setErrorMsg(null);

    try {
      const res = await fetch(`/sign/${token}/revise`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({
          rejection_notes: rejectionNotes,
        }),
      });

      const json = await res.json();
      if (!res.ok || !json.success) {
        throw new Error(json?.error?.message || 'Gagal mengirim catatan revisi.');
      }

      setRevisedSubmitted(true);
    } catch (err: any) {
      setErrorMsg(err?.message || 'Terjadi kesalahan sistem.');
    } finally {
      setIsSubmitting(false);
    }
  };

  if (signedResult) {
    return (
      <main className="min-h-screen bg-[#0B0F17] text-slate-100 flex items-center justify-center p-6">
        <div className="w-full max-w-lg rounded-2xl border border-emerald-800/60 bg-[#161F2E] p-8 text-center shadow-2xl">
          <div className="h-16 w-16 mx-auto rounded-full bg-emerald-950 border border-emerald-500/40 flex items-center justify-center text-emerald-400 text-2xl font-bold mb-4">
            ✓
          </div>
          <h1 className="text-2xl font-bold text-slate-100">Berita Acara Disetujui</h1>
          <p className="text-sm text-slate-400 mt-2">
            Terima kasih. Berita Acara Serah Terima (BAST) telah berhasil ditandatangani secara digital.
          </p>

          <div className="mt-6 rounded-lg bg-slate-900 border border-slate-800 p-4 text-left space-y-2 font-mono text-xs">
            <div className="flex justify-between">
              <span className="text-slate-500">Nomor BAST:</span>
              <span className="text-slate-200 font-semibold">{signedResult.bast_number}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-slate-500">Waktu TTD:</span>
              <span className="text-slate-300">{signedResult.signed_at}</span>
            </div>
            <div>
              <span className="text-slate-500 block mb-1">Integritas Checksum (SHA-256):</span>
              <span className="text-[10px] text-emerald-400 break-all">{signedResult.sha256_checksum}</span>
            </div>
          </div>
        </div>
      </main>
    );
  }

  if (revisedSubmitted) {
    return (
      <main className="min-h-screen bg-[#0B0F17] text-slate-100 flex items-center justify-center p-6">
        <div className="w-full max-w-lg rounded-2xl border border-amber-800/60 bg-[#161F2E] p-8 text-center shadow-2xl">
          <div className="h-16 w-16 mx-auto rounded-full bg-amber-950 border border-amber-500/40 flex items-center justify-center text-amber-400 text-2xl font-bold mb-4">
            ↩
          </div>
          <h1 className="text-2xl font-bold text-slate-100">Catatan Revisi Terkirim</h1>
          <p className="text-sm text-slate-400 mt-2">
            Catatan revisi Anda telah diteruskan ke Project Manager vendor. Status milestone telah dikembalikan ke tahap pengerjaan.
          </p>
        </div>
      </main>
    );
  }

  return (
    <main className="min-h-screen bg-[#0B0F17] text-slate-100 py-12 px-4 sm:px-6">
      <div className="max-w-2xl mx-auto rounded-2xl border border-slate-800 bg-[#161F2E] p-6 sm:p-10 shadow-2xl">
        {/* Header Contract Info */}
        <div className="border-b border-slate-800 pb-6 mb-6">
          <div className="flex items-center justify-between">
            <span className="text-xs font-mono font-semibold tracking-wider text-blue-400 uppercase bg-blue-950/60 border border-blue-800/60 px-2.5 py-1 rounded">
              Persetujuan BAST Resmi
            </span>
            <span className="text-xs text-slate-500 font-mono">No. {data.contract_number}</span>
          </div>
          <h1 className="text-2xl font-bold text-slate-100 mt-3">{data.project_name}</h1>
          <p className="text-sm text-slate-400 mt-1">
            Termin: <strong className="text-slate-200">{data.milestone_name}</strong>
          </p>
          <div className="mt-3 inline-block rounded bg-slate-900 border border-slate-800 px-3 py-1 font-mono text-sm text-slate-100">
            Nilai Termin: <strong className="text-emerald-400 font-bold">{formatIDR(data.amount)}</strong>
          </div>
        </div>

        {/* Deliverables Section */}
        <section className="mb-8">
          <h2 className="text-sm font-semibold text-slate-300 mb-3 uppercase tracking-wider">
            Daftar Bukti Hasil Kerja (Deliverables)
          </h2>
          {(!data.deliverables || data.deliverables.length === 0) ? (
            <p className="text-xs text-slate-500">Tidak ada lampiran.</p>
          ) : (
            <div className="space-y-3">
              {data.deliverables.map((d) => (
                <div
                  key={d.id}
                  className="rounded-lg border border-slate-800 bg-slate-900/60 p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2"
                >
                  <div>
                    <h3 className="text-sm font-semibold text-slate-200">{d.title}</h3>
                    {d.description && <p className="text-xs text-slate-400 mt-0.5">{d.description}</p>}
                  </div>
                  <div className="flex items-center gap-2 shrink-0">
                    {d.file_url && (
                      <a
                        href={d.file_url}
                        target="_blank"
                        rel="noreferrer"
                        className="text-xs px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-blue-400 border border-slate-700 transition-colors"
                      >
                        Buka Berkas
                      </a>
                    )}
                    {d.staging_url && (
                      <a
                        href={d.staging_url}
                        target="_blank"
                        rel="noreferrer"
                        className="text-xs px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition-colors"
                      >
                        Tautan Demo
                      </a>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </section>

        {errorMsg && (
          <div className="mb-6 rounded-lg border border-rose-800/60 bg-rose-950/40 p-3 text-xs text-rose-300">
            {errorMsg}
          </div>
        )}

        {/* Approval Form */}
        {!isRevising ? (
          <form onSubmit={handleApprove} className="space-y-4 pt-4 border-t border-slate-800">
            <h2 className="text-sm font-semibold text-slate-200">Konfirmasi Tanda Tangan Digital</h2>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label htmlFor="signatory_name" className="block text-xs text-slate-400 mb-1">
                  Nama Lengkap Penandatangan
                </label>
                <input
                  id="signatory_name"
                  type="text"
                  required
                  placeholder="Contoh: Budi Santoso"
                  value={signatoryName}
                  onChange={(e) => setSignatoryName(e.target.value)}
                  className="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder-slate-600 focus:border-blue-500 focus:outline-none"
                />
              </div>

              <div>
                <label htmlFor="signatory_title" className="block text-xs text-slate-400 mb-1">
                  Jabatan / Posisi di Perusahaan
                </label>
                <input
                  id="signatory_title"
                  type="text"
                  required
                  placeholder="Contoh: Direktur Operasional"
                  value={signatoryTitle}
                  onChange={(e) => setSignatoryTitle(e.target.value)}
                  className="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder-slate-600 focus:border-blue-500 focus:outline-none"
                />
              </div>
            </div>

            <p className="text-[11px] text-slate-500 italic pt-1">
              Dengan menekan tombol di bawah, Anda menyatakan telah menerima dan menyetujui hasil kerja di atas untuk diterbitkan tagihan resmi.
            </p>

            <div className="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
              <button
                type="button"
                onClick={() => setIsRevising(true)}
                className="w-full sm:w-auto text-xs text-slate-400 hover:text-slate-200 py-2 transition-colors"
              >
                Minta Revisi Deliverable
              </button>

              <button
                type="submit"
                disabled={isSubmitting}
                className="w-full sm:w-auto rounded-md bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm px-5 py-2.5 shadow-lg transition-colors disabled:opacity-50"
              >
                {isSubmitting ? 'Memproses BAST...' : 'Setujui BAST & Terbitkan Tagihan'}
              </button>
            </div>
          </form>
        ) : (
          <form onSubmit={handleRevise} className="space-y-4 pt-4 border-t border-slate-800">
            <h2 className="text-sm font-semibold text-slate-200">Permintaan Revisi Hasil Kerja</h2>
            <div>
              <label htmlFor="rejection_notes" className="block text-xs text-slate-400 mb-1">
                Catatan Perbaikan untuk Vendor
              </label>
              <textarea
                id="rejection_notes"
                required
                rows={4}
                placeholder="Jelaskan bagian hasil kerja yang belum sesuai kontrak..."
                value={rejectionNotes}
                onChange={(e) => setRejectionNotes(e.target.value)}
                className="w-full rounded-md border border-slate-700 bg-slate-900 p-3 text-sm text-slate-100 placeholder-slate-600 focus:border-blue-500 focus:outline-none"
              />
            </div>

            <div className="flex items-center justify-end gap-3 pt-2">
              <button
                type="button"
                onClick={() => setIsRevising(false)}
                className="text-xs text-slate-400 hover:text-slate-200 px-3 py-2 transition-colors"
              >
                Batal
              </button>
              <button
                type="submit"
                disabled={isSubmitting}
                className="rounded-md bg-amber-600 hover:bg-amber-500 text-white font-medium text-sm px-4 py-2 transition-colors disabled:opacity-50"
              >
                {isSubmitting ? 'Mengirim...' : 'Kirim Catatan Revisi'}
              </button>
            </div>
          </form>
        )}
      </div>
    </main>
  );
}
