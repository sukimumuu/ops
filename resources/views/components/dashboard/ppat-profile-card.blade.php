<section x-data="{
    usersUrl: @js(route('property-review.ppats', $property)),
    assignUrl: @js(route('property-review.assign-ppat', $property)),
    assigned: @js($property->assignedPpat?->only(['id', 'name', 'email', 'phone', 'province', 'city', 'district'])),
    users: [],
    search: '',
    page: 1,
    hasMore: false,
    loading: false,
    initialized: false,
    requestId: 0,
    assigningId: null,
    error: '',
    notice: '',
    async loadUsers(page = 1) {
        const requestId = ++this.requestId;
        this.loading = true;
        this.error = '';
        this.users = [];
        try {
            const params = new URLSearchParams({ page, search: this.search });
            const response = await fetch(`${this.usersUrl}?${params}`, { headers: { Accept: 'application/json' } });
            const result = await response.json();
            if (requestId !== this.requestId) return;
            if (!response.ok) throw new Error(result.message || 'Daftar PPAT tidak dapat dimuat.');
            this.users = result.data;
            this.page = result.current_page;
            this.hasMore = result.has_more;
        } catch (exception) {
            if (requestId === this.requestId) this.error = exception.message || 'Daftar PPAT tidak dapat dimuat.';
        } finally {
            if (requestId === this.requestId) this.loading = false;
        }
    },
    async assignPpat(ppat) {
        this.assigningId = ppat.id;
        this.error = '';
        this.notice = '';
        try {
            const response = await fetch(this.assignUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ ppat_id: ppat.id }),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'PPAT gagal ditugaskan.');
            this.assigned = result.assigned;
            this.notice = result.message;
            await this.loadUsers(1);
        } catch (exception) {
            this.error = exception.message || 'PPAT gagal ditugaskan.';
        } finally {
            this.assigningId = null;
        }
    },
    location(ppat) {
        return [ppat.district, ppat.city, ppat.province].filter(Boolean).join(', ') || 'Wilayah belum dilengkapi';
    },
    initials(name) {
        return name ? name.trim().charAt(0).toUpperCase() : '?';
    },
}" x-effect="if (open && !initialized) { initialized = true; loadUsers(); }"
    class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <header class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3">
        <div>
            <h4 class="text-sm font-bold text-secondary">PPAT terkait</h4>
            <p class="mt-0.5 text-xs text-slate-500">Prioritas kecamatan: {{ $property->district ?: 'Belum ditentukan' }}
            </p>
        </div>
        <span class="shrink-0 rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary"
            x-text="assigned ? '1 ditugaskan' : 'Belum ditugaskan'"></span>
    </header>

    <div class="space-y-3 p-4">
        <p x-show="notice" x-text="notice" role="status"
            class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800"></p>
        <p x-show="error" x-text="error" role="alert"
            class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></p>

        <template x-if="assigned">
            <article class="flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50/60 p-3">
                <span
                    class="flex size-9 shrink-0 items-center justify-center rounded-full bg-white text-sm font-bold text-secondary"
                    x-text="initials(assigned.name)"></span>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-bold uppercase text-emerald-700">PPAT saat ini</p>
                    <h5 class="truncate text-sm font-semibold text-secondary" x-text="assigned.name"></h5>
                    <p class="truncate text-xs text-slate-500" x-text="location(assigned)"></p>
                    <p class="truncate text-xs text-slate-500"
                        x-text="assigned.email || assigned.phone || 'Kontak belum tersedia'"></p>
                </div>
            </article>
        </template>

        <label class="relative block">
            <span class="sr-only">Cari PPAT</span>
            <input type="search" x-model="search" @input="loadUsers(1)"
                placeholder="Cari nama, email, telepon, atau wilayah"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-secondary placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
        </label>

        <div x-show="loading" role="status" class="py-5 text-center text-sm text-slate-500">Memuat PPAT...</div>
        <div x-show="!loading && !error && users.length === 0" class="py-5 text-center text-sm text-slate-500">
            Tidak ada PPAT yang cocok dengan pencarian.
        </div>

        <div x-show="!loading && !error && users.length > 0"
            class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">
            <template x-for="ppat in users" :key="ppat.id">
                <article class="flex min-w-0 flex-col justify-between gap-3 rounded-lg border border-slate-200 p-3">
                    <div class="flex min-w-0 items-start gap-2">
                        <span
                            class="flex size-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-secondary"
                            x-text="initials(ppat.name)"></span>
                        <div class="min-w-0 flex-1">
                            <h5 class="truncate text-sm font-semibold text-secondary" x-text="ppat.name"></h5>
                            <p class="mt-0.5 truncate text-[11px] text-slate-500" x-text="location(ppat)"></p>
                            <p class="truncate text-[11px] text-slate-500"
                                x-text="ppat.email || 'Email tidak tersedia'"></p>
                            <a x-show="ppat.phone" :href="`tel:+${ppat.phone.replace(/\D+/g, '')}`"
                                class="mt-0.5 inline-block truncate text-[11px] font-semibold text-primary hover:underline"
                                x-text="ppat.phone"></a>
                        </div>
                    </div>
                    <button type="button" @click="assignPpat(ppat)" :disabled="assigningId !== null"
                        class="inline-flex min-h-9 items-center justify-center rounded-lg bg-secondary px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-60"
                        x-text="assigningId === ppat.id ? 'Menyimpan...' : (assigned ? 'Ganti PPAT' : 'Tugaskan')"></button>
                </article>
            </template>
        </div>

        <div x-show="!loading && !error && users.length > 0"
            class="flex items-center justify-between border-t border-slate-100 pt-3">
            <button type="button" @click="loadUsers(page - 1)" :disabled="page <= 1 || loading"
                class="rounded-md px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40">
                Sebelumnya
            </button>
            <span class="text-xs text-slate-500" x-text="`Halaman ${page}`"></span>
            <button type="button" @click="loadUsers(page + 1)" :disabled="!hasMore || loading"
                class="rounded-md px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40">
                Berikutnya
            </button>
        </div>
    </div>
</section>
