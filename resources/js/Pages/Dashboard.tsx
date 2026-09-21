import React from 'react';
import { MilestoneSpine, formatIDR, type MilestoneItem } from '@/Components/MilestoneSpine';

interface ProjectItem {
  id: string;
  name: string;
  contract_number: string;
  total_amount: number;
  client?: {
    name: string;
    company_name: string;
  };
  milestones: MilestoneItem[];
}

interface DashboardMetrics {
  total_contract_value: number;
  total_cash_collected: number;
  cash_at_risk: number;
  retention_held: number;
  retention_matured: number;
}

interface DashboardProps {
  metrics: DashboardMetrics;
  projects: ProjectItem[];
}

export default function Dashboard({ metrics, projects }: DashboardProps) {
  return (
    <div className="min-h-screen bg-[#0B0F17] text-slate-100 font-sans pb-16">
      {/* Top Navbar */}
      <header className="border-b border-slate-800 bg-[#161F2E]/80 backdrop-blur sticky top-0 z-20 px-6 py-4">
        <div className="max-w-7xl mx-auto flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="h-7 w-7 rounded bg-blue-600 flex items-center justify-center font-black text-white text-sm">
              TL
            </div>
            <span className="font-bold tracking-tight text-lg">TerminLock</span>
          </div>
          <span className="text-xs font-mono text-slate-400 bg-slate-800/80 px-2.5 py-1 rounded border border-slate-700">
            Agency Cash-at-Risk Radar
          </span>
        </div>
      </header>

      {/* Sticky Executive Metrics Radar */}
      <section className="border-b border-slate-800 bg-[#0E1522] py-6 px-6">
        <div className="max-w-7xl mx-auto grid grid-cols-2 lg:grid-cols-5 gap-4">
          <div className="rounded-lg border border-slate-800 bg-[#161F2E] p-4">
            <span className="text-xs text-slate-400 font-medium">Total Kontrak</span>
            <p className="text-xl font-bold font-mono text-slate-100 mt-1">
              {formatIDR(metrics?.total_contract_value || 0)}
            </p>
          </div>

          <div className="rounded-lg border border-slate-800 bg-[#161F2E] p-4">
            <span className="text-xs text-emerald-400 font-medium">Kas Cair (Lunas)</span>
            <p className="text-xl font-bold font-mono text-emerald-400 mt-1">
              {formatIDR(metrics?.total_cash_collected || 0)}
            </p>
          </div>

          <div className="rounded-lg border border-amber-900/50 bg-amber-950/20 p-4">
            <span className="text-xs text-amber-400 font-medium">Cash-at-Risk (Tertahan)</span>
            <p className="text-xl font-bold font-mono text-amber-300 mt-1">
              {formatIDR(metrics?.cash_at_risk || 0)}
            </p>
          </div>

          <div className="rounded-lg border border-slate-800 bg-[#161F2E] p-4">
            <span className="text-xs text-indigo-400 font-medium">Uang Retensi (Hold)</span>
            <p className="text-xl font-bold font-mono text-indigo-300 mt-1">
              {formatIDR(metrics?.retention_held || 0)}
            </p>
          </div>

          <div className="rounded-lg border border-rose-900/50 bg-rose-950/20 p-4">
            <span className="text-xs text-rose-400 font-medium">Retensi Siap Tagih</span>
            <p className="text-xl font-bold font-mono text-rose-300 mt-1">
              {formatIDR(metrics?.retention_matured || 0)}
            </p>
          </div>
        </div>
      </section>

      {/* Main Project & Milestone Spines */}
      <main className="max-w-7xl mx-auto px-6 mt-8">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-xl font-bold text-slate-100">Daftar Kontrak & Termin Aktif</h2>
        </div>

        {(!projects || projects.length === 0) ? (
          <div className="rounded-lg border border-slate-800 bg-[#161F2E] p-12 text-center">
            <h3 className="text-lg font-semibold text-slate-200">Belum Ada Proyek Aktif</h3>
            <p className="text-sm text-slate-400 mt-2 max-w-md mx-auto">
              Konfigurasi proyek dan termin pertama Anda untuk memonitor serah-terima BAST dan penagihan termin.
            </p>
          </div>
        ) : (
          <div className="space-y-8">
            {projects.map((p) => (
              <div key={p.id} className="rounded-xl border border-slate-800 bg-[#161F2E] p-6 shadow-xl">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-800 pb-4 mb-6 gap-2">
                  <div>
                    <span className="text-xs font-mono text-slate-400">{p.contract_number}</span>
                    <h3 className="text-lg font-bold text-slate-100">{p.name}</h3>
                    {p.client && (
                      <p className="text-xs text-slate-400 mt-0.5">Klien: {p.client.company_name}</p>
                    )}
                  </div>
                  <div className="text-right">
                    <span className="text-xs text-slate-400 block">Total Nilai Kontrak</span>
                    <span className="text-lg font-bold font-mono text-slate-100">
                      {formatIDR(p.total_amount)}
                    </span>
                  </div>
                </div>

                {/* Milestone Spine for this Project */}
                <MilestoneSpine milestones={p.milestones || []} />
              </div>
            ))}
          </div>
        )}
      </main>
    </div>
  );
}
