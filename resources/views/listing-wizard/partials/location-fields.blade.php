<div class="wizard-form-grid grid-2 mt-5">
    <div class="wizard-field wz-region-field" @click.outside="locationOpen.provinces = false">
        <label class="wz-label" for="province-search">Provinsi <span class="wz-required">*</span></label>
        <input id="province-search" type="text" role="combobox" aria-autocomplete="list" aria-controls="province-options"
            :aria-expanded="locationOpen.provinces" autocomplete="off" x-model="locationQuery.province"
            @focus="openRegionOptions('provinces')" @input="handleRegionInput('provinces', $event.target.value)"
            @keydown.arrow-down.prevent="moveRegionSelection('provinces', 1)"
            @keydown.arrow-up.prevent="moveRegionSelection('provinces', -1)"
            @keydown.enter.prevent="chooseActiveRegion('provinces')" @keydown.escape="locationOpen.provinces = false"
            placeholder="Cari provinsi..." class="wz-input">
        <span class="wz-region-loading" x-show="regionLoading.provinces" aria-label="Memuat provinsi">
            <svg class="animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
        </span>
        <div id="province-options" class="wz-region-options" role="listbox" x-show="locationOpen.provinces" x-cloak>
            <template x-for="(region, index) in filteredRegionOptions('provinces')" :key="region.code">
                <button type="button" role="option" :id="`province-option-${index}`"
                    :aria-selected="locationActiveIndex.provinces === index" class="wz-region-option"
                    :class="{ 'active': locationActiveIndex.provinces === index }"
                    @mousedown.prevent="selectRegion('provinces', region)" x-text="region.name"></button>
            </template>
            <p class="wz-region-message" x-show="regionLoading.provinces">Memuat provinsi...</p>
            <p class="wz-region-message"
                x-show="!regionLoading.provinces && !regionErrors.provinces && regionOptions.provinces.length && !filteredRegionOptions('provinces').length">
                Provinsi tidak ditemukan.</p>
            <p class="wz-region-message"
                x-show="!regionLoading.provinces && !regionErrors.provinces && !regionOptions.provinces.length">Belum
                ada data provinsi.</p>
            <p class="wz-region-message" x-show="regionErrors.provinces" x-text="regionErrors.provinces"></p>
            <button type="button" class="wz-region-retry" x-show="regionErrors.provinces"
                @mousedown.prevent="loadRegionOptions('provinces', true)">Coba lagi</button>
        </div>
        <template x-if="errors.province">
            <p class="wz-error" x-text="errors.province"></p>
        </template>
        <template x-if="errors.province_code">
            <p class="wz-error" x-text="errors.province_code"></p>
        </template>
    </div>

    <div class="wizard-field wz-region-field" @click.outside="locationOpen.regencies = false">
        <label class="wz-label" for="city-search">Kabupaten / kota <span class="wz-required">*</span></label>
        <input id="city-search" type="text" role="combobox" aria-autocomplete="list" aria-controls="city-options"
            :aria-expanded="locationOpen.regencies" :disabled="!form.province_code" autocomplete="off"
            x-model="locationQuery.city" @focus="openRegionOptions('regencies')"
            @input="handleRegionInput('regencies', $event.target.value)"
            @keydown.arrow-down.prevent="moveRegionSelection('regencies', 1)"
            @keydown.arrow-up.prevent="moveRegionSelection('regencies', -1)"
            @keydown.enter.prevent="chooseActiveRegion('regencies')" @keydown.escape="locationOpen.regencies = false"
            placeholder="Pilih kabupaten/kota..." class="wz-input">
        <span class="wz-region-loading" x-show="regionLoading.regencies" aria-label="Memuat kabupaten/kota">
            <svg class="animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
        </span>
        <div id="city-options" class="wz-region-options" role="listbox" x-show="locationOpen.regencies" x-cloak>
            <template x-for="(region, index) in filteredRegionOptions('regencies')" :key="region.code">
                <button type="button" role="option" :id="`city-option-${index}`"
                    :aria-selected="locationActiveIndex.regencies === index" class="wz-region-option"
                    :class="{ 'active': locationActiveIndex.regencies === index }"
                    @mousedown.prevent="selectRegion('regencies', region)" x-text="region.name"></button>
            </template>
            <p class="wz-region-message" x-show="regionLoading.regencies">Memuat kabupaten/kota...</p>
            <p class="wz-region-message"
                x-show="!regionLoading.regencies && !regionErrors.regencies && regionOptions.regencies.length && !filteredRegionOptions('regencies').length">
                Kabupaten/kota tidak ditemukan.</p>
            <p class="wz-region-message"
                x-show="!regionLoading.regencies && !regionErrors.regencies && !regionOptions.regencies.length">Tidak
                ada kabupaten/kota untuk provinsi ini.</p>
            <p class="wz-region-message" x-show="regionErrors.regencies" x-text="regionErrors.regencies"></p>
            <button type="button" class="wz-region-retry" x-show="regionErrors.regencies"
                @mousedown.prevent="loadRegionOptions('regencies', true)">Coba lagi</button>
        </div>
        <template x-if="errors.city">
            <p class="wz-error" x-text="errors.city"></p>
        </template>
        <template x-if="errors.city_code">
            <p class="wz-error" x-text="errors.city_code"></p>
        </template>
    </div>

    <div class="wizard-field wz-region-field" @click.outside="locationOpen.districts = false">
        <label class="wz-label" for="district-search">Kecamatan <span class="wz-required">*</span></label>
        <input id="district-search" type="text" role="combobox" aria-autocomplete="list"
            aria-controls="district-options" :aria-expanded="locationOpen.districts" :disabled="!form.city_code"
            autocomplete="off" x-model="locationQuery.district" @focus="openRegionOptions('districts')"
            @input="handleRegionInput('districts', $event.target.value)"
            @keydown.arrow-down.prevent="moveRegionSelection('districts', 1)"
            @keydown.arrow-up.prevent="moveRegionSelection('districts', -1)"
            @keydown.enter.prevent="chooseActiveRegion('districts')" @keydown.escape="locationOpen.districts = false"
            placeholder="Pilih kecamatan..." class="wz-input">
        <span class="wz-region-loading" x-show="regionLoading.districts" aria-label="Memuat kecamatan">
            <svg class="animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
        </span>
        <div id="district-options" class="wz-region-options" role="listbox" x-show="locationOpen.districts" x-cloak>
            <template x-for="(region, index) in filteredRegionOptions('districts')" :key="region.code">
                <button type="button" role="option" :id="`district-option-${index}`"
                    :aria-selected="locationActiveIndex.districts === index" class="wz-region-option"
                    :class="{ 'active': locationActiveIndex.districts === index }"
                    @mousedown.prevent="selectRegion('districts', region)" x-text="region.name"></button>
            </template>
            <p class="wz-region-message" x-show="regionLoading.districts">Memuat kecamatan...</p>
            <p class="wz-region-message"
                x-show="!regionLoading.districts && !regionErrors.districts && regionOptions.districts.length && !filteredRegionOptions('districts').length">
                Kecamatan tidak ditemukan.</p>
            <p class="wz-region-message"
                x-show="!regionLoading.districts && !regionErrors.districts && !regionOptions.districts.length">Tidak
                ada kecamatan untuk kabupaten/kota ini.</p>
            <p class="wz-region-message" x-show="regionErrors.districts" x-text="regionErrors.districts"></p>
            <button type="button" class="wz-region-retry" x-show="regionErrors.districts"
                @mousedown.prevent="loadRegionOptions('districts', true)">Coba lagi</button>
        </div>
        <template x-if="errors.district">
            <p class="wz-error" x-text="errors.district"></p>
        </template>
        <template x-if="errors.district_code">
            <p class="wz-error" x-text="errors.district_code"></p>
        </template>
    </div>
    <div class="wizard-field">
        <label class="wz-label">Kode pos</label>
        <input type="text" x-model="form.postal_code" placeholder="15224" class="wz-input">
    </div>
</div>
