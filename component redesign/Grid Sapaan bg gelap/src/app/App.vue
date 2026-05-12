<script setup lang="ts">
const menuItems = [
  'Dashboard',
  'Tabel Task',
  'Kanban Board',
  'Audit Trail',
  'Document',
  'Faskes / Client',
  'Team',
  'User',
]

const overdueTasks = [
  'Laporan Dashboard Analytics',
  'Laporan Bug Harian/Seni',
  'Laporan Bugfix/Ashen',
]

const upcomingTasks = ['PO Task', 'Bug', 'Audit']
</script>

<template>
  <main class="min-h-screen bg-slate-100 p-4 md:p-6">
    <section class="mx-auto grid w-full max-w-[1360px] gap-4 xl:grid-cols-[280px_1fr_1.25fr]">
      <aside class="rounded-3xl bg-[#003a84] p-5 text-white shadow-[4px_8px_16px_rgba(0,0,0,0.2)]">
        <div class="mb-8 rounded-2xl bg-white/95 p-4 text-center text-[#003a84]">
          <p class="text-3xl font-black leading-none">trustmedis</p>
          <p class="text-xs font-semibold opacity-80">healthtech solution</p>
        </div>

        <nav class="space-y-3 text-2xl font-bold leading-none">
          <a
            v-for="item in menuItems"
            :key="item"
            href="#"
            class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-white/10"
          >
            <span class="inline-block h-4 w-4 rounded-sm border border-white/80" />
            <span>{{ item }}</span>
          </a>
        </nav>

        <div class="mt-8 space-y-3 text-2xl font-bold">
          <a href="#" class="block rounded-xl px-3 py-2 transition hover:bg-white/10">Pengaturan Sistem</a>
          <a href="#" class="block rounded-xl px-3 py-2 transition hover:bg-white/10">Admin PO</a>
        </div>
      </aside>

      <section class="space-y-4">
        <article class="relative flex h-[360px] flex-col overflow-hidden rounded-3xl border-2 border-black bg-[#0f172a] p-4 text-white shadow-[3px_6px_0_rgba(0,0,0,0.65)] sm:h-[420px] sm:p-5 lg:h-[500px]">
          <p class="text-3xl font-semibold leading-[1.05] sm:text-[40px]">Good Morning,</p>
          <p class="bg-gradient-to-r from-white to-emerald-400 bg-clip-text text-[34px] font-semibold leading-[1.05] text-transparent sm:text-[42px]">Admin PO</p>
          <p class="mt-2 text-base text-slate-300 sm:mt-3 sm:text-xl">
            kamu mempunyai
            <span class="mx-1 bg-[#7a6116]/60 px-2 text-amber-300">14 Pending</span>
            dan
            <span class="mx-1 bg-[#6d1a1a]/60 px-2 text-red-400">58 Overdue</span>
          </p>

          <div class="mt-auto flex items-end justify-between pt-6 sm:pt-10">
            <div>
              <p class="text-sm uppercase tracking-[0.2em] text-slate-400">Total Tasks</p>
              <p class="text-4xl font-semibold sm:text-5xl">109</p>
            </div>

            <div class="flex items-end gap-1.5 sm:gap-2">
              <span class="h-8 w-5 rounded-md bg-slate-700/80 sm:h-16 sm:w-6" />
              <span class="h-10 w-5 rounded-md bg-slate-600/80 sm:h-20 sm:w-6" />
              <span class="h-14 w-5 rounded-md bg-teal-800/80 sm:h-28 sm:w-6" />
              <span class="h-18 w-5 rounded-md bg-emerald-600/85 sm:h-36 sm:w-6" />
              <span class="h-22 w-5 rounded-md bg-emerald-400 sm:h-44 sm:w-6" />
            </div>
          </div>
        </article>

        <article class="rounded-3xl border-2 border-black bg-white p-4 shadow-[3px_6px_0_rgba(0,0,0,0.45)]">
          <div class="mb-3 flex items-center justify-between">
            <h3 class="text-2xl font-semibold text-[#003a84]">Task Overdue</h3>
            <span class="rounded-full border border-slate-500 px-2 py-0.5 text-xs text-slate-600">Filter</span>
          </div>
          <div class="space-y-2">
            <div
              v-for="task in overdueTasks"
              :key="task"
              class="rounded-lg border border-red-300 bg-red-50 p-2"
            >
              <p class="text-sm font-medium text-slate-700">{{ task }}</p>
            </div>
          </div>
          <p class="mt-2 text-right text-2xl font-bold text-red-500">58</p>
        </article>

        <article class="rounded-3xl border-2 border-black bg-white p-4 shadow-[3px_6px_0_rgba(0,0,0,0.45)]">
          <h3 class="text-2xl font-semibold text-[#003a84]">Ringkasan Performa Team</h3>
          <div class="mt-2 h-40 rounded-md border border-slate-400 bg-slate-100 text-center text-lg font-semibold text-slate-700">
            Tabel
          </div>
        </article>
      </section>

      <section class="space-y-4">
        <article class="rounded-3xl border-2 border-black bg-white p-4 shadow-[3px_6px_0_rgba(0,0,0,0.45)]">
          <div class="grid gap-4 lg:grid-cols-[240px_1fr]">
            <div class="space-y-3">
              <div class="rounded-2xl border-2 border-[#003a84] p-3">
                <div class="mb-2 flex items-center justify-between">
                  <h3 class="text-xl font-semibold text-[#003a84]">Task Due Soon (H-7)</h3>
                  <span class="rounded-full border border-slate-500 px-2 py-0.5 text-xs text-slate-600">Filter</span>
                </div>
                <div class="space-y-2">
                  <div
                    v-for="task in upcomingTasks"
                    :key="task"
                    class="rounded-md border border-amber-300 p-2 text-sm"
                  >
                    {{ task }}
                  </div>
                </div>
              </div>

              <div class="rounded-2xl border-2 border-[#003a84] p-3">
                <h3 class="mb-2 text-xl font-semibold text-[#003a84]">Actions</h3>
                <div class="grid grid-cols-2 gap-2 text-sm">
                  <button class="rounded-md border border-slate-400 px-2 py-1 text-left">New PO</button>
                  <button class="rounded-md border border-slate-400 px-2 py-1 text-left">New Task</button>
                  <button class="rounded-md border border-slate-400 px-2 py-1 text-left">New Faskes</button>
                  <button class="rounded-md border border-slate-400 px-2 py-1 text-left">Export Data</button>
                </div>
              </div>
            </div>

            <div class="space-y-3">
              <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border-2 border-[#00a884] p-3">
                  <p class="text-sm text-slate-600">Total Tasks</p>
                  <p class="text-4xl font-semibold">109</p>
                </div>
                <div class="rounded-2xl border-2 border-[#00a884] p-3">
                  <p class="text-sm text-slate-600">Total Tim</p>
                  <p class="text-4xl font-semibold">12</p>
                </div>
                <div class="rounded-2xl border-2 border-[#00a884] p-3">
                  <p class="text-sm text-slate-600">Menunggu Dikerjakan</p>
                  <p class="text-4xl font-semibold">14</p>
                </div>
                <div class="rounded-2xl border-2 border-[#00a884] p-3">
                  <p class="text-sm text-slate-600">Total Faskes</p>
                  <p class="text-4xl font-semibold">91</p>
                </div>
              </div>

              <div class="rounded-2xl border-2 border-black p-3">
                <div class="mb-2 flex items-center justify-between">
                  <h3 class="text-2xl font-semibold text-[#003a84]">Tren Task 7 Days</h3>
                  <span class="rounded-full border border-slate-500 px-2 py-0.5 text-xs text-slate-600">Week</span>
                </div>
                <svg viewBox="0 0 560 150" class="h-36 w-full">
                  <polyline
                    fill="none"
                    stroke="#111827"
                    stroke-width="3"
                    points="8,120 90,84 172,102 254,48 336,94 418,58 500,74"
                  />
                </svg>
              </div>
            </div>
          </div>
        </article>

        <article class="grid gap-4 xl:grid-cols-2">
          <div class="rounded-3xl border-2 border-black bg-white p-4 shadow-[3px_6px_0_rgba(0,0,0,0.45)]">
            <h3 class="text-2xl font-semibold text-[#003a84]">Task Terbaru</h3>
            <div class="mt-2 h-40 rounded-md border border-slate-400 bg-slate-100 text-center text-lg font-semibold text-slate-700">
              Tabel
            </div>
          </div>

          <div class="rounded-3xl border-2 border-black bg-white p-4 shadow-[3px_6px_0_rgba(0,0,0,0.45)]">
            <h3 class="text-2xl font-semibold text-[#003a84]">Task Terbaru</h3>
            <div class="mt-2 h-40 rounded-md border border-slate-400 bg-slate-100 text-center text-lg font-semibold text-slate-700">
              Tabel
            </div>
          </div>
        </article>
      </section>
    </section>
  </main>
</template>
