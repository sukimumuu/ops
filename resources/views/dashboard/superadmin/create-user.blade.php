@extends('layouts.app')
@section('page-title', 'Tambah Pengguna')
@section('content')
    <div class="mb-5">
        <a href="{{ route('superadmin.user-management') }}"
            class="text-sm font-semibold text-primary hover:text-primary-hover">
            &larr; Kembali ke Manajemen Pengguna
        </a>
    </div>

    <x-card-dashboard title="Tambah Pengguna" subtitle="Lengkapi informasi akun dan pilih peran pengguna.">
        <form action="{{ route('superadmin.user-management.store') }}" method="POST" class="space-y-6" x-data="ppatUserForm()">
            @csrf

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-form-text-input-dashboard id="name" name="name" label="Nama Lengkap"
                    placeholder="Masukkan nama lengkap" :value="old('name')" :error="$errors->first('name')" required />

                <x-form-text-input-dashboard id="email" name="email" type="email" label="Email"
                    placeholder="nama@example.com" :value="old('email')" :error="$errors->first('email')" />

                <x-form-text-input-dashboard id="phone" name="phone" type="tel" label="Nomor Telepon"
                    placeholder="08xxxxxxxxxx" :value="old('phone')" :error="$errors->first('phone')" required />

                <x-form-select-dashboard id="role" name="role" label="Peran" :error="$errors->first('role')" required
                    x-model="role" @change="handleRoleChange()">
                    <option selected value="">Pilih peran</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    <option value="ppat" @selected(old('role') === 'ppat')>PPAT</option>
                    <option value="buyer" @selected(old('role') === 'buyer')>Buyer</option>
                    <option value="seller" @selected(old('role') === 'seller')>Seller</option>
                </x-form-select-dashboard>

                <x-form-text-input-dashboard id="password" name="password" type="password" label="Kata Sandi"
                    :error="$errors->first('password')" hint="Minimal 8 karakter." required />

                <x-form-text-input-dashboard id="password_confirmation" name="password_confirmation" type="password"
                    label="Konfirmasi Kata Sandi" :error="$errors->first('password_confirmation')" required />
            </div>

            <div x-show="role === 'ppat'" x-cloak
                class="grid grid-cols-1 gap-5 border-t border-slate-100 pt-5 md:grid-cols-2">
                <input type="hidden" name="province_code" x-model="selected.provinceCode" :disabled="role !== 'ppat'">
                <input type="hidden" name="city_code" x-model="selected.regencyCode" :disabled="role !== 'ppat'">
                <input type="hidden" name="district_code" x-model="selected.districtCode" :disabled="role !== 'ppat'">
                <div class="relative" @click.outside="locationOpen.provinces = false">
                    <label for="ppat-province" class="mb-1.5 block text-sm font-semibold text-secondary">Provinsi <span
                            class="text-red-600">*</span></label>
                    <input id="ppat-province" type="text" name="province" role="combobox" aria-autocomplete="list"
                        aria-controls="ppat-province-options" :aria-expanded="locationOpen.provinces"
                        :disabled="role !== 'ppat'" autocomplete="off" x-model="locationQuery.province"
                        @focus="openRegionOptions('provinces')" @input="handleRegionInput('provinces', $event.target.value)"
                        @keydown.arrow-down.prevent="moveRegionSelection('provinces', 1)"
                        @keydown.arrow-up.prevent="moveRegionSelection('provinces', -1)"
                        @keydown.enter.prevent="chooseActiveRegion('provinces')"
                        @keydown.escape="locationOpen.provinces = false" placeholder="Cari provinsi..."
                        class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 pr-10 text-sm font-medium text-secondary shadow-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400">
                    <span x-show="regionLoading.provinces" class="absolute right-3 top-10 h-4 w-4 text-slate-500"
                        aria-label="Memuat provinsi">
                        <svg class="animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                    </span>
                    <div id="ppat-province-options" x-show="locationOpen.provinces" x-cloak role="listbox"
                        class="absolute z-30 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white p-1 shadow-lg">
                        <template x-for="(region, index) in filteredRegionOptions('provinces')" :key="region.code">
                            <button type="button" role="option" :aria-selected="locationActiveIndex.provinces === index"
                                @mousedown.prevent="selectRegion('provinces', region)"
                                :class="{ 'bg-slate-100': locationActiveIndex.provinces === index }"
                                class="block w-full rounded-md px-3 py-2 text-left text-sm text-secondary hover:bg-slate-100"
                                x-text="region.name"></button>
                        </template>
                        <p x-show="regionLoading.provinces" class="px-3 py-2 text-sm text-slate-500">Memuat provinsi...
                        </p>
                        <p x-show="!regionLoading.provinces && !regionErrors.provinces && regionOptions.provinces.length && !filteredRegionOptions('provinces').length"
                            class="px-3 py-2 text-sm text-slate-500">Provinsi tidak ditemukan.</p>
                        <p x-show="!regionLoading.provinces && !regionErrors.provinces && !regionOptions.provinces.length"
                            class="px-3 py-2 text-sm text-slate-500">Tidak ada data provinsi.</p>
                        <p x-show="regionErrors.provinces" x-text="regionErrors.provinces"
                            class="px-3 py-2 text-sm text-red-600"></p>
                        <button type="button" x-show="regionErrors.provinces"
                            @mousedown.prevent="loadRegionOptions('provinces', true)"
                            class="px-3 pb-2 text-sm font-semibold text-primary">Coba lagi</button>
                    </div>
                    @error('province')
                        <p class="mt-1.5 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="relative" @click.outside="locationOpen.regencies = false">
                    <label for="ppat-city" class="mb-1.5 block text-sm font-semibold text-secondary">Kabupaten / kota
                        <span class="text-red-600">*</span></label>
                    <input id="ppat-city" type="text" name="city" role="combobox" aria-autocomplete="list"
                        aria-controls="ppat-city-options" :aria-expanded="locationOpen.regencies"
                        :disabled="role !== 'ppat' || !selected.provinceCode" autocomplete="off"
                        x-model="locationQuery.city" @focus="openRegionOptions('regencies')"
                        @input="handleRegionInput('regencies', $event.target.value)"
                        @keydown.arrow-down.prevent="moveRegionSelection('regencies', 1)"
                        @keydown.arrow-up.prevent="moveRegionSelection('regencies', -1)"
                        @keydown.enter.prevent="chooseActiveRegion('regencies')"
                        @keydown.escape="locationOpen.regencies = false" placeholder="Pilih kabupaten/kota..."
                        class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 pr-10 text-sm font-medium text-secondary shadow-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400">
                    <span x-show="regionLoading.regencies" class="absolute right-3 top-10 h-4 w-4 text-slate-500"
                        aria-label="Memuat kabupaten/kota">
                        <svg class="animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                    </span>
                    <div id="ppat-city-options" x-show="locationOpen.regencies" x-cloak role="listbox"
                        class="absolute z-30 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white p-1 shadow-lg">
                        <template x-for="(region, index) in filteredRegionOptions('regencies')" :key="region.code">
                            <button type="button" role="option"
                                :aria-selected="locationActiveIndex.regencies === index"
                                @mousedown.prevent="selectRegion('regencies', region)"
                                :class="{ 'bg-slate-100': locationActiveIndex.regencies === index }"
                                class="block w-full rounded-md px-3 py-2 text-left text-sm text-secondary hover:bg-slate-100"
                                x-text="region.name"></button>
                        </template>
                        <p x-show="regionLoading.regencies" class="px-3 py-2 text-sm text-slate-500">Memuat
                            kabupaten/kota...</p>
                        <p x-show="!regionLoading.regencies && !regionErrors.regencies && regionOptions.regencies.length && !filteredRegionOptions('regencies').length"
                            class="px-3 py-2 text-sm text-slate-500">Kabupaten/kota tidak ditemukan.</p>
                        <p x-show="!regionLoading.regencies && !regionErrors.regencies && !regionOptions.regencies.length"
                            class="px-3 py-2 text-sm text-slate-500">Tidak ada kabupaten/kota untuk provinsi ini.</p>
                        <p x-show="regionErrors.regencies" x-text="regionErrors.regencies"
                            class="px-3 py-2 text-sm text-red-600"></p>
                        <button type="button" x-show="regionErrors.regencies"
                            @mousedown.prevent="loadRegionOptions('regencies', true)"
                            class="px-3 pb-2 text-sm font-semibold text-primary">Coba lagi</button>
                    </div>
                    @error('city')
                        <p class="mt-1.5 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="relative" @click.outside="locationOpen.districts = false">
                    <label for="ppat-district" class="mb-1.5 block text-sm font-semibold text-secondary">Kecamatan <span
                            class="text-red-600">*</span></label>
                    <input id="ppat-district" type="text" name="district" role="combobox" aria-autocomplete="list"
                        aria-controls="ppat-district-options" :aria-expanded="locationOpen.districts"
                        :disabled="role !== 'ppat' || !selected.regencyCode" autocomplete="off"
                        x-model="locationQuery.district" @focus="openRegionOptions('districts')"
                        @input="handleRegionInput('districts', $event.target.value)"
                        @keydown.arrow-down.prevent="moveRegionSelection('districts', 1)"
                        @keydown.arrow-up.prevent="moveRegionSelection('districts', -1)"
                        @keydown.enter.prevent="chooseActiveRegion('districts')"
                        @keydown.escape="locationOpen.districts = false" placeholder="Pilih kecamatan..."
                        class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 pr-10 text-sm font-medium text-secondary shadow-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400">
                    <span x-show="regionLoading.districts" class="absolute right-3 top-10 h-4 w-4 text-slate-500"
                        aria-label="Memuat kecamatan">
                        <svg class="animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                    </span>
                    <div id="ppat-district-options" x-show="locationOpen.districts" x-cloak role="listbox"
                        class="absolute z-30 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white p-1 shadow-lg">
                        <template x-for="(region, index) in filteredRegionOptions('districts')" :key="region.code">
                            <button type="button" role="option"
                                :aria-selected="locationActiveIndex.districts === index"
                                @mousedown.prevent="selectRegion('districts', region)"
                                :class="{ 'bg-slate-100': locationActiveIndex.districts === index }"
                                class="block w-full rounded-md px-3 py-2 text-left text-sm text-secondary hover:bg-slate-100"
                                x-text="region.name"></button>
                        </template>
                        <p x-show="regionLoading.districts" class="px-3 py-2 text-sm text-slate-500">Memuat kecamatan...
                        </p>
                        <p x-show="!regionLoading.districts && !regionErrors.districts && regionOptions.districts.length && !filteredRegionOptions('districts').length"
                            class="px-3 py-2 text-sm text-slate-500">Kecamatan tidak ditemukan.</p>
                        <p x-show="!regionLoading.districts && !regionErrors.districts && !regionOptions.districts.length"
                            class="px-3 py-2 text-sm text-slate-500">Tidak ada kecamatan untuk kabupaten/kota ini.</p>
                        <p x-show="regionErrors.districts" x-text="regionErrors.districts"
                            class="px-3 py-2 text-sm text-red-600"></p>
                        <button type="button" x-show="regionErrors.districts"
                            @mousedown.prevent="loadRegionOptions('districts', true)"
                            class="px-3 pb-2 text-sm font-semibold text-primary">Coba lagi</button>
                    </div>
                    @error('district')
                        <p class="mt-1.5 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('superadmin.user-management') }}"
                    class="inline-flex items-center justify-center rounded-lg border-2 border-primary px-5 py-2.5 text-sm font-heading font-semibold tracking-tight text-primary transition hover:bg-primary-soft">
                    Batal
                </a>
                <x-button type="submit" variant="primary">
                    Simpan Pengguna
                </x-button>
            </div>
        </form>
    </x-card-dashboard>

    @push('scripts')
        <script>
            function ppatUserForm() {
                return {
                    role: @json(old('role', '')),
                    selected: {
                        provinceCode: '',
                        regencyCode: '',
                        districtCode: ''
                    },
                    locationQuery: {
                        province: @json(old('province', '')),
                        city: @json(old('city', '')),
                        district: @json(old('district', '')),
                    },
                    locationOpen: {
                        provinces: false,
                        regencies: false,
                        districts: false
                    },
                    locationActiveIndex: {
                        provinces: 0,
                        regencies: 0,
                        districts: 0
                    },
                    regionOptions: {
                        provinces: [],
                        regencies: [],
                        districts: []
                    },
                    regionLoading: {
                        provinces: false,
                        regencies: false,
                        districts: false
                    },
                    regionErrors: {
                        provinces: '',
                        regencies: '',
                        districts: ''
                    },
                    regionCache: {
                        provinces: null,
                        regencies: {},
                        districts: {}
                    },
                    regionRequests: {},
                    regionRequestSequence: {
                        provinces: 0,
                        regencies: 0,
                        districts: 0
                    },
                    regionEndpoints: {
                        provinces: @json(route('listing.regions.provinces')),
                        regencies: @json(route('listing.regions.regencies', ['provinceCode' => '__CODE__'])),
                        districts: @json(route('listing.regions.districts', ['regencyCode' => '__CODE__'])),
                    },

                    init() {
                        this.loadRegionOptions('provinces').then(async () => {
                            if (this.role !== 'ppat') return;

                            const province = this.regionOptions.provinces.find((item) => item.name
                                .toLocaleLowerCase('id') === this.locationQuery.province.toLocaleLowerCase('id')
                                );
                            if (!province) return;
                            this.selectRegion('provinces', province);
                            await this.loadRegionOptions('regencies');

                            const regency = this.regionOptions.regencies.find((item) => item.name.toLocaleLowerCase(
                                'id') === this.locationQuery.city.toLocaleLowerCase('id'));
                            if (!regency) return;
                            this.selectRegion('regencies', regency);
                            await this.loadRegionOptions('districts');

                            const district = this.regionOptions.districts.find((item) => item.name
                                .toLocaleLowerCase('id') === this.locationQuery.district.toLocaleLowerCase('id')
                                );
                            if (district) this.selectRegion('districts', district);
                        });
                    },

                    handleRoleChange() {
                        if (this.role !== 'ppat') this.clearLocations();
                    },

                    clearLocations() {
                        this.locationQuery = {
                            province: '',
                            city: '',
                            district: ''
                        };
                        this.selected = {
                            provinceCode: '',
                            regencyCode: '',
                            districtCode: ''
                        };
                        this.locationOpen = {
                            provinces: false,
                            regencies: false,
                            districts: false
                        };
                        this.regionOptions.regencies = [];
                        this.regionOptions.districts = [];
                    },

                    regionParentCode(level) {
                        return level === 'regencies' ? this.selected.provinceCode : this.selected.regencyCode;
                    },

                    async loadRegionOptions(level, retry = false) {
                        const parentCode = this.regionParentCode(level);
                        const cacheKey = level === 'provinces' ? 'provinces' : `${level}:${parentCode}`;
                        if (level !== 'provinces' && !parentCode) return;

                        if (!retry && (level === 'provinces' ? this.regionCache.provinces : this.regionCache[level][
                                parentCode
                            ])) {
                            this.regionOptions[level] = level === 'provinces' ? this.regionCache.provinces : this
                                .regionCache[level][parentCode];
                            this.regionErrors[level] = '';
                            return;
                        }
                        if (this.regionRequests[cacheKey]) return this.regionRequests[cacheKey];

                        const sequence = ++this.regionRequestSequence[level];
                        this.regionLoading[level] = true;
                        this.regionErrors[level] = '';
                        const url = level === 'provinces' ?
                            this.regionEndpoints.provinces :
                            this.regionEndpoints[level].replace('__CODE__', encodeURIComponent(parentCode));

                        const request = fetch(url, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            })
                            .then(async (response) => {
                                const payload = await response.json();
                                if (!response.ok || !Array.isArray(payload.data) ||
                                    payload.data.some((region) => typeof region.code !== 'string' || typeof region
                                        .name !== 'string')) {
                                    throw new Error(payload.message || 'Respons data wilayah tidak valid.');
                                }
                                if (level === 'provinces') this.regionCache.provinces = payload.data;
                                else this.regionCache[level][parentCode] = payload.data;

                                if (level === 'provinces' || this.regionParentCode(level) === parentCode) {
                                    this.regionOptions[level] = payload.data;
                                }
                            })
                            .catch((error) => {
                                console.error('Region lookup error:', error);
                                if (sequence === this.regionRequestSequence[level]) this.regionErrors[level] =
                                    'Data wilayah gagal dimuat.';
                            })
                            .finally(() => {
                                delete this.regionRequests[cacheKey];
                                if (sequence === this.regionRequestSequence[level]) this.regionLoading[level] = false;
                            });
                        this.regionRequests[cacheKey] = request;
                        return request;
                    },

                    filteredRegionOptions(level) {
                        const field = {
                            provinces: 'province',
                            regencies: 'city',
                            districts: 'district'
                        } [level];
                        const query = this.locationQuery[field].trim().toLocaleLowerCase('id');
                        return this.regionOptions[level].filter((region) => region.name.toLocaleLowerCase('id').includes(
                        query));
                    },

                    openRegionOptions(level) {
                        this.locationOpen[level] = true;
                        this.locationActiveIndex[level] = 0;
                        this.loadRegionOptions(level);
                    },

                    handleRegionInput(level, value) {
                        const field = {
                            provinces: 'province',
                            regencies: 'city',
                            districts: 'district'
                        } [level];
                        this.locationQuery[field] = value;
                        this.locationOpen[level] = true;
                        this.locationActiveIndex[level] = 0;

                        if (level === 'provinces' && this.selected.provinceCode) {
                            const selected = this.regionCache.provinces.find((item) => item.code === this.selected
                            .provinceCode);
                            if (value.toLocaleLowerCase('id') !== selected.name.toLocaleLowerCase('id')) {
                                this.selected.provinceCode = '';
                                this.selected.regencyCode = '';
                                this.locationQuery.city = '';
                                this.locationQuery.district = '';
                                this.regionOptions.regencies = [];
                                this.regionOptions.districts = [];
                            }
                        } else if (level === 'regencies' && this.selected.regencyCode) {
                            const selected = this.regionCache.regencies[this.selected.provinceCode].find((item) => item.code ===
                                this.selected.regencyCode);
                            if (value.toLocaleLowerCase('id') !== selected.name.toLocaleLowerCase('id')) {
                                this.selected.regencyCode = '';
                                this.locationQuery.district = '';
                                this.regionOptions.districts = [];
                            }
                        } else if (level === 'districts') {
                            const selected = this.regionCache.districts[this.selected.regencyCode]?.find((item) => item.code ===
                                this.selected.districtCode);
                            if (selected && value.toLocaleLowerCase('id') !== selected.name.toLocaleLowerCase('id')) this
                                .selected.districtCode = '';
                        }
                    },

                    moveRegionSelection(level, direction) {
                        const count = this.filteredRegionOptions(level).length;
                        if (count) this.locationActiveIndex[level] = (this.locationActiveIndex[level] + direction + count) %
                            count;
                    },

                    chooseActiveRegion(level) {
                        const region = this.filteredRegionOptions(level)[this.locationActiveIndex[level]];
                        if (region) this.selectRegion(level, region);
                    },

                    selectRegion(level, region) {
                        const field = {
                            provinces: 'province',
                            regencies: 'city',
                            districts: 'district'
                        } [level];
                        const changed = level === 'provinces' ?
                            this.selected.provinceCode !== region.code :
                            level === 'regencies' && this.selected.regencyCode !== region.code;
                        this.locationQuery[field] = region.name;
                        this.locationOpen[level] = false;

                        if (level === 'provinces') {
                            this.selected.provinceCode = region.code;
                            if (changed) {
                                this.selected.regencyCode = '';
                                this.selected.districtCode = '';
                                this.locationQuery.city = '';
                                this.locationQuery.district = '';
                                this.regionOptions.regencies = [];
                                this.regionOptions.districts = [];
                                this.loadRegionOptions('regencies');
                            }
                        } else if (level === 'regencies') {
                            this.selected.regencyCode = region.code;
                            if (changed) {
                                this.selected.districtCode = '';
                                this.locationQuery.district = '';
                                this.regionOptions.districts = [];
                                this.loadRegionOptions('districts');
                            }
                        } else {
                            this.selected.districtCode = region.code;
                        }
                    },
                };
            }
        </script>
    @endpush
@endsection
