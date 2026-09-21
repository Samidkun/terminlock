import React from 'react';

export interface MilestoneItem {
  id: string;
  order: number;
  name: string;
  amount: number;
  percentage: number;
  status:
    | 'draft'
    | 'active'
    | 'awaiting_signoff'
    | 'bast_signed'
    | 'invoiced'
    | 'paid'
    | 'retention_hold'
    | 'retention_matured'
    | 'settled';
  due_date?: string | null;
  is_retention?: boolean;
}

interface MilestoneSpineProps {
  milestones: MilestoneItem[];
  onRequestSignoff?: (milestoneId: string) => void;
  onIssueInvoice?: (milestoneId: string) => void;
}

export function formatIDR(amount: number): string {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(amount);
}

export function getStatusBadge(status: MilestoneItem['status']): { label: string; colorClass: string } {
  switch (status) {
    case 'paid':
    case 'settled':
      return { label: 'Lunas', colorClass: 'bg-emerald-950 text-emerald-400 border-emerald-800' };
    case 'bast_signed':
      return { label: 'BAST Ditandatangani', colorClass: 'bg-blue-950 text-blue-400 border-blue-800' };
    case 'invoiced':
      return { label: 'Invoice Terbit', colorClass: 'bg-amber-950 text-amber-300 border-amber-800' };
    case 'awaiting_signoff':
      return { label: 'Menunggu BAST Klien', colorClass: 'bg-amber-950 text-amber-400 border-amber-800' };
    case 'retention_hold':
      return { label: 'Masa Garansi (Hold)', colorClass: 'bg-indigo-950 text-indigo-300 border-indigo-800' };
    case 'retention_matured':
      return { label: 'Retensi Matang (Tagih)', colorClass: 'bg-rose-950 text-rose-400 border-rose-800' };
    case 'active':
      return { label: 'Pengerjaan', colorClass: 'bg-slate-800 text-slate-300 border-slate-700' };
    default:
      return { label: 'Draft', colorClass: 'bg-slate-900 text-slate-400 border-slate-800' };
  }
}

export function MilestoneSpine({
  milestones,
  onRequestSignoff,
  onIssueInvoice,
}: MilestoneSpineProps) {
  if (!milestones || milestones.length === 0) {
    return (
      <div className="rounded-lg border border-slate-800 bg-slate-900/50 p-6 text-center text-slate-400">
        Belum ada termin milestone yang dikonfigurasi.
      </div>
    );
  }

  return (
    <div className="relative pl-6 sm:pl-8 border-l-2 border-slate-800 space-y-6">
      {milestones.map((m, idx) => {
        const badge = getStatusBadge(m.status);
        const isCompleted = m.status === 'paid' || m.status === 'settled';
        const isAtRisk =
          m.status === 'awaiting_signoff' ||
          m.status === 'bast_signed' ||
          m.status === 'invoiced';

        return (
          <div key={m.id || idx} className="relative group">
            {/* Timeline Node Dot */}
            <div
              className={`absolute -left-[31px] sm:-left-[39px] top-1.5 h-4 w-4 rounded-full border-2 transition-colors ${
                isCompleted
                  ? 'bg-emerald-500 border-emerald-300'
                  : isAtRisk
                  ? 'bg-amber-500 border-amber-300'
                  : 'bg-slate-800 border-slate-600'
              }`}
            />

            {/* Milestone Card */}
            <div className="rounded-lg border border-slate-800 bg-slate-900 p-4 shadow-md transition-all hover:border-slate-700">
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                  <div className="flex items-center gap-2">
                    <span className="text-xs font-mono text-slate-500">#{m.order}</span>
                    <h4 className="text-base font-semibold text-slate-100">{m.name}</h4>
                    {m.is_retention && (
                      <span className="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-indigo-950 text-indigo-400 border border-indigo-800">
                        Retensi
                      </span>
                    )}
                  </div>
                  {m.due_date && (
                    <p className="text-xs text-slate-400 mt-1">
                      Jatuh Tempo: <span className="font-mono text-slate-300">{m.due_date}</span>
                    </p>
                  )}
                </div>

                <div className="flex sm:flex-col items-end justify-between sm:justify-center gap-2">
                  <span className="font-mono font-bold text-slate-100 text-sm sm:text-base">
                    {formatIDR(m.amount)}
                  </span>
                  <span
                    className={`inline-block text-xs px-2.5 py-0.5 rounded-full border font-medium ${badge.colorClass}`}
                  >
                    {badge.label}
                  </span>
                </div>
              </div>

              {/* Action Triggers */}
              <div className="mt-4 pt-3 border-t border-slate-800 flex items-center justify-end gap-2">
                {m.status === 'active' && onRequestSignoff && (
                  <button
                    onClick={() => onRequestSignoff(m.id)}
                    className="text-xs font-medium px-3 py-1.5 rounded bg-blue-600 hover:bg-blue-500 text-white transition-colors"
                  >
                    Ajukan BAST ke Klien
                  </button>
                )}
                {m.status === 'bast_signed' && onIssueInvoice && (
                  <button
                    onClick={() => onIssueInvoice(m.id)}
                    className="text-xs font-medium px-3 py-1.5 rounded bg-amber-600 hover:bg-amber-500 text-white transition-colors"
                  >
                    Terbitkan Tagihan
                  </button>
                )}
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );
}
