    <script>
        function listingWizard() {
            return {
                currentStep: 1,
                propertyId: null,
                isLoading: false,
                showSuccess: false,
                agreedTerms: false,
                hasDraft: false,
                draftUuid: null,
                draftSaveState: '',
                draftSaveError: '',
                draftButtonState: 'unsaved',
                draftEditRevision: 0,
                draftSaveTimer: null,
                draftSaveInProgress: false,
                draftSavePending: false,
                draftSavePromise: null,
                draftSaveEndpoint: @json(route('listing.drafts.store')),

                // AI auto-fill state
                showAiModal: false,
                aiInput: '',
                aiLoading: false,
                aiError: '',
                aiNotice: '',
                aiExtractEndpoint: @json(route('listing.ai.extract')),
                aiFieldLabels: {
                    title: 'Judul properti',
                    price: 'Harga',
                    property_type: 'Tipe properti',
                    building_type: 'Jenis bangunan',
                    land_type: 'Jenis tanah',
                    transaction_type: 'Tujuan transaksi',
                    rent_period: 'Periode sewa',
                    address: 'Alamat',
                    land_area_sqm: 'Luas tanah',
                    building_area_sqm: 'Luas bangunan',
                    bedrooms: 'Kamar tidur',
                    bathrooms: 'Kamar mandi',
                    floors: 'Jumlah lantai',
                    condition: 'Kondisi bangunan',
                    negotiable: 'Harga nego',
                    front_width: 'Lebar depan',
                    land_contour: 'Kontur tanah',
                    road_access: 'Akses jalan',
                    zone_type: 'Zona',
                },

                // AI description state
                isGeneratingDescription: false,
                descriptionError: '',
                descriptionNotice: '',
                descriptionEndpoint: @json(route('listing.ai.description')),
                showDescriptionConfirm: false,
                pendingDescription: '',

                // Search state
                searchQuery: '',
                isSearching: false,
                searchError: '',
                osmUrl: '',
                mapInstance: null,
                mapMarker: null,
                mapContainerId: null,
                locationQuery: {
                    province: '',
                    city: '',
                    district: ''
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

                form: {
                    property_type: '',
                    building_type: '',
                    land_type: '',
                    transaction_type: '',
                    rent_period: '',
                    title: '',
                    description: '',
                    price: '',
                    negotiable: false,
                    land_area_sqm: '',
                    building_area_sqm: '',
                    bedrooms: '',
                    bathrooms: '',
                    floors: '',
                    condition: '',
                    province: '',
                    province_code: '',
                    city: '',
                    city_code: '',
                    district: '',
                    district_code: '',
                    postal_code: '',
                    address: '',
                    latitude: '',
                    longitude: '',
                    // Tanah-specific
                    front_width: '',
                    land_contour: '',
                    road_access: '',
                    zone_type: '',
                },

                errors: {},
                photoFiles: [],
                photoPreviews: [],
                savedPhotoPreviews: [],
                photoUploadError: '',

                // Certificate & legal document state
                maxCertificates: 5,
                certificateKeySeq: 1,
                certificates: [
                    { key: 0, jenis_sertifikat: '', file: null, errors: {} },
                ],
                optionalDocuments: {
                    imb: { file: null, error: '' },
                    pbb: { file: null, error: '' },
                },
                optionalDocumentInputIds: {
                    imb: 'imb-document-input',
                    pbb: 'pbb-document-input',
                },
                dragging: '',

                formatCurrency(value) {
                    let num = value.replace(/\D/g, '');
                    return num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                },

                isRentTransaction() {
                    return this.form.transaction_type === 'disewakan';
                },

                syncRentPeriodState() {
                    if (!this.isRentTransaction()) {
                        this.form.rent_period = '';
                    }
                },

                openAiModal() {
                    this.aiError = '';
                    this.showAiModal = true;
                    this.$nextTick(() => this.$refs.aiTextarea?.focus());
                },

                closeAiModal() {
                    if (this.aiLoading || !this.showAiModal) return;

                    this.showAiModal = false;
                    this.$nextTick(() => this.$refs.aiTrigger?.focus());
                },

                async submitAiExtraction() {
                    if (this.aiLoading) return;

                    const text = this.aiInput.trim();

                    if (text.length < 10) {
                        this.aiError = 'Tuliskan informasi properti minimal 10 karakter.';
                        return;
                    }

                    this.aiLoading = true;
                    this.aiError = '';

                    try {
                        const response = await fetch(this.aiExtractEndpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                text
                            }),
                        });

                        const payload = await response.json().catch(() => null);

                        if (!response.ok) {
                            this.aiError = this.apiErrorMessage(payload,
                                'Data properti gagal diekstrak. Silakan coba lagi.');
                            return;
                        }

                        const data = payload?.data;

                        if (!data || typeof data !== 'object' || Array.isArray(data) || Object.keys(data).length === 0) {
                            this.aiError = 'Tidak ada data properti yang dapat dikenali. Coba sebutkan tipe, lokasi, luas, atau harga properti.';
                            return;
                        }

                        this.applyAiExtraction(data);

                        const fields = Array.isArray(payload.fields) ? payload.fields : Object.keys(data);
                        const labels = fields.map((field) => this.aiFieldLabels[field]).filter(Boolean);

                        this.aiNotice = labels.length > 0 ?
                            `${labels.length} data berhasil diisi: ${labels.join(', ')}. Periksa kembali dan lengkapi kolom yang belum terisi.` :
                            'Data properti berhasil diisi. Periksa kembali dan lengkapi kolom yang belum terisi.';

                        this.showAiModal = false;
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                        this.$nextTick(() => this.$refs.aiTrigger?.focus());
                    } catch (error) {
                        console.error('AI extraction error:', error);
                        this.aiError = 'Terjadi kesalahan koneksi. Silakan coba lagi.';
                    } finally {
                        this.aiLoading = false;
                    }
                },

                applyAiExtraction(data) {
                    const allowedFields = [
                        'title',
                        'price',
                        'property_type',
                        'building_type',
                        'land_type',
                        'transaction_type',
                        'rent_period',
                        'address',
                        'land_area_sqm',
                        'building_area_sqm',
                        'bedrooms',
                        'bathrooms',
                        'floors',
                        'condition',
                        'negotiable',
                        'front_width',
                        'land_contour',
                        'road_access',
                        'zone_type',
                    ];
                    const priorityFields = [
                        'property_type',
                        'building_type',
                        'land_type',
                        'transaction_type',
                        'rent_period',
                    ];
                    const orderedFields = [
                        ...priorityFields,
                        ...allowedFields.filter((field) => !priorityFields.includes(field)),
                    ];
                    const appliedFields = [];

                    orderedFields.forEach((field) => {
                        if (!Object.prototype.hasOwnProperty.call(data, field)) return;

                        if (field === 'price') {
                            const price = Number(data[field]);

                            if (!Number.isFinite(price)) return;

                            this.form.price = this.formatCurrency(String(Math.round(price)));
                        } else if (field === 'negotiable') {
                            this.form.negotiable = data[field] === true || data[field] === 1 || data[field] === '1';
                        } else {
                            this.form[field] = data[field];
                        }

                        appliedFields.push(field);
                    });

                    appliedFields.forEach((field) => {
                        delete this.errors[field];
                    });

                    this.syncRentPeriodState();
                    this.currentStep = 1;

                    this.$nextTick(() => this.dispatchFieldEvents(appliedFields));
                },

                dispatchFieldEvents(fields) {
                    fields.forEach((field) => {
                        const controls = document.querySelectorAll(`[x-model="form.${field}"]`);
                        const value = this.form[field];

                        controls.forEach((element) => {
                            if (element.type === 'radio') {
                                if (element.value !== String(value ?? '')) return;
                            } else if (element.type === 'checkbox') {
                                element.checked = Boolean(value);
                            } else if (element.tagName === 'SELECT') {
                                element.value = String(value ?? '');
                            } else if (element.type !== 'file') {
                                element.value = String(value ?? '');
                            }

                            element.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));
                            element.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
                        });
                    });
                },

                collectDescriptionPayload() {
                    const fields = [
                        'property_type',
                        'building_type',
                        'land_type',
                        'transaction_type',
                        'rent_period',
                        'title',
                        'price',
                        'negotiable',
                        'land_area_sqm',
                        'building_area_sqm',
                        'front_width',
                        'bedrooms',
                        'bathrooms',
                        'floors',
                        'condition',
                        'land_contour',
                        'road_access',
                        'zone_type',
                        'address',
                        'province',
                        'city',
                        'district',
                    ];
                    const payload = {};

                    fields.forEach((field) => {
                        let value = this.form[field];

                        if (field === 'price') {
                            value = String(value ?? '').replace(/\D/g, '');
                        }

                        if (field === 'negotiable' && value !== true) return;
                        if (value === null || value === undefined || value === '') return;

                        payload[field] = value;
                    });

                    if (!payload.property_type) {
                        if (payload.building_type) {
                            payload.property_type = 'bangunan';
                        } else if (payload.land_type) {
                            payload.property_type = 'tanah';
                        }
                    }

                    if (payload.property_type === 'bangunan') {
                        ['land_type', 'front_width', 'land_contour', 'road_access', 'zone_type'].forEach((field) => {
                            delete payload[field];
                        });
                    }

                    if (payload.property_type === 'tanah') {
                        ['building_type', 'building_area_sqm', 'bedrooms', 'bathrooms', 'floors', 'condition']
                        .forEach((field) => {
                            delete payload[field];
                        });
                    }

                    return payload;
                },

                async generateDescription() {
                    if (this.isGeneratingDescription) return;

                    const payload = this.collectDescriptionPayload();

                    const hasUsefulData = payload.title || payload.price || payload.building_type || payload.land_type ||
                        payload.address;

                    if (!hasUsefulData) {
                        this.descriptionError =
                            'Isi minimal judul, harga, jenis, atau lokasi properti sebelum membuat deskripsi.';
                        return;
                    }

                    this.isGeneratingDescription = true;
                    this.descriptionError = '';
                    this.descriptionNotice = '';

                    try {
                        const response = await fetch(this.descriptionEndpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(payload),
                        });

                        const result = await response.json().catch(() => null);

                        if (!response.ok) {
                            this.descriptionError = this.apiErrorMessage(result,
                                'Deskripsi gagal dibuat. Silakan coba lagi.');
                            return;
                        }

                        const description = typeof result?.description === 'string' ? result.description.trim() : '';

                        if (description === '') {
                            this.descriptionError =
                                'AI belum dapat membuat deskripsi. Lengkapi data properti lalu coba lagi.';
                            return;
                        }

                        if (this.form.description.trim() !== '') {
                            this.pendingDescription = description;
                            this.showDescriptionConfirm = true;
                            this.$nextTick(() => this.$refs.descriptionConfirmButton?.focus());
                            return;
                        }

                        this.applyGeneratedDescription(description);
                    } catch (error) {
                        console.error('AI description error:', error);
                        this.descriptionError = 'Terjadi kesalahan koneksi. Silakan coba lagi.';
                    } finally {
                        this.isGeneratingDescription = false;
                    }
                },

                applyGeneratedDescription(description) {
                    this.form.description = description;
                    this.descriptionNotice =
                        'Deskripsi berhasil dibuat. Periksa dan sesuaikan sebelum melanjutkan.';
                    this.descriptionError = '';
                    delete this.errors.description;

                    this.$nextTick(() => {
                        document.querySelectorAll('[x-model="form.description"]').forEach((element) => {
                            element.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));
                        });
                    });
                },

                confirmDescriptionReplacement() {
                    if (this.pendingDescription === '') return;

                    this.applyGeneratedDescription(this.pendingDescription);
                    this.pendingDescription = '';
                    this.showDescriptionConfirm = false;
                },

                cancelDescriptionReplacement() {
                    if (!this.showDescriptionConfirm) return;

                    this.pendingDescription = '';
                    this.showDescriptionConfirm = false;
                },

                apiErrorMessage(payload, fallback) {
                    if (payload && typeof payload.message === 'string' && payload.message !== '' &&
                        payload.message !== 'The given data was invalid.') {
                        return payload.message;
                    }

                    if (payload && payload.errors && typeof payload.errors === 'object') {
                        const firstError = Object.values(payload.errors)[0];

                        if (Array.isArray(firstError) && typeof firstError[0] === 'string') {
                            return firstError[0];
                        }
                    }

                    return fallback;
                },

                formatFileSize(bytes) {
                    if (bytes < 1024) return bytes + ' B';
                    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                    return (bytes / 1048576).toFixed(1) + ' MB';
                },

                init() {
                    this.form = {
                        ...this.form,
                        ...@json($initialForm)
                    };
                    this.draftUuid = @json($initialDraftUuid);
                    this.hasDraft = Boolean(this.draftUuid);
                    this.draftButtonState = this.draftUuid ? 'saved' : 'unsaved';
                    this.currentStep = this.draftUuid ? @json($initialDraftStep) : 1;
                    const savedUploads = @json($initialUploads);
                    this.savedPhotoPreviews = savedUploads.photos || [];
                    if (Array.isArray(savedUploads.certificates) && savedUploads.certificates.length > 0) {
                        this.certificates = savedUploads.certificates.map((certificate, index) => ({
                            key: index,
                            jenis_sertifikat: certificate.type,
                            file: null,
                            savedName: certificate.name,
                            errors: {},
                        }));
                        this.certificateKeySeq = this.certificates.length;
                    }
                    this.optionalDocuments.imb.savedName = savedUploads.imb?.name || '';
                    this.optionalDocuments.pbb.savedName = savedUploads.pbb?.name || '';
                    this.osmUrl = this.form.url_maps || '';
                    this.locationQuery.province = this.form.province || '';
                    this.locationQuery.city = this.form.city || '';
                    this.locationQuery.district = this.form.district || '';

                    this.$watch('form', () => this.scheduleDraftSave(true));
                    this.$watch('currentStep', () => this.scheduleDraftSave());
                    window.addEventListener('pagehide', () => {
                        if (this.draftSaveTimer) {
                            clearTimeout(this.draftSaveTimer);
                            this.draftSaveTimer = null;
                        }
                        this.persistDraft();
                    });

                    this.$watch('form.property_type', (value) => {
                        if (this.mapInstance) {
                            this.mapInstance.remove();
                            this.mapInstance = null;
                            this.mapMarker = null;
                        }
                    });

                    this.$watch('form.transaction_type', () => {
                        this.syncRentPeriodState();
                    });

                    this.syncRentPeriodState();

                    this.loadRegionOptions('provinces');
                    if (this.form.province_code) {
                        this.loadRegionOptions('regencies');
                    }
                    if (this.form.city_code) {
                        this.loadRegionOptions('districts');
                    }
                    if (this.hasDraftData()) {
                        this.scheduleDraftSave();
                    }
                },

                regionParentCode(level) {
                    return level === 'regencies' ? this.form.province_code : this.form.city_code;
                },

                async loadRegionOptions(level, retry = false) {
                    const parentCode = this.regionParentCode(level);
                    const cacheKey = level === 'provinces' ? 'provinces' : `${level}:${parentCode}`;

                    if (level !== 'provinces' && !parentCode) return;

                    if (!retry && (level === 'provinces' ? this.regionCache.provinces : this.regionCache[level][
                            parentCode
                        ])) {
                        this.regionOptions[level] = level === 'provinces' ?
                            this.regionCache.provinces :
                            this.regionCache[level][parentCode];
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

                            if (level === 'provinces') {
                                this.regionCache.provinces = payload.data;
                            } else {
                                this.regionCache[level][parentCode] = payload.data;
                            }

                            if (level === 'provinces' || this.regionParentCode(level) === parentCode) {
                                this.regionOptions[level] = payload.data;
                            }
                        })
                        .catch((error) => {
                            console.error('Region lookup error:', error);
                            if (sequence === this.regionRequestSequence[level]) {
                                this.regionErrors[level] = 'Data wilayah gagal dimuat.';
                            }
                        })
                        .finally(() => {
                            delete this.regionRequests[cacheKey];
                            if (sequence === this.regionRequestSequence[level]) {
                                this.regionLoading[level] = false;
                            }
                        });

                    this.regionRequests[cacheKey] = request;
                    return request;
                },

                filteredRegionOptions(level) {
                    const queryKey = {
                        provinces: 'province',
                        regencies: 'city',
                        districts: 'district'
                    } [level];
                    const query = this.locationQuery[queryKey].trim().toLocaleLowerCase('id');

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
                    const codeField = `${field}_code`;
                    this.locationQuery[field] = value;
                    this.locationOpen[level] = true;
                    this.locationActiveIndex[level] = 0;

                    if (this.form[codeField] && value.trim().toLocaleLowerCase('id') !== this.form[field].toLocaleLowerCase(
                            'id')) {
                        this.form[field] = '';
                        this.form[codeField] = '';
                        if (level === 'provinces') this.clearRegency();
                        if (level === 'regencies') this.clearDistrict();
                    }
                },

                moveRegionSelection(level, direction) {
                    const optionCount = this.filteredRegionOptions(level).length;
                    if (!optionCount) return;

                    this.locationActiveIndex[level] = (this.locationActiveIndex[level] + direction + optionCount) %
                        optionCount;
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
                    const codeField = `${field}_code`;
                    const changed = this.form[codeField] !== region.code;

                    this.form[field] = region.name;
                    this.form[codeField] = region.code;
                    this.locationQuery[field] = region.name;
                    this.locationOpen[level] = false;

                    if (changed && level === 'provinces') {
                        this.clearRegency();
                        this.loadRegionOptions('regencies');
                    } else if (changed && level === 'regencies') {
                        this.clearDistrict();
                        this.loadRegionOptions('districts');
                    }
                },

                clearRegency() {
                    this.form.city = '';
                    this.form.city_code = '';
                    this.locationQuery.city = '';
                    this.locationOpen.regencies = false;
                    this.regionOptions.regencies = [];
                    this.clearDistrict();
                },

                clearDistrict() {
                    this.form.district = '';
                    this.form.district_code = '';
                    this.locationQuery.district = '';
                    this.locationOpen.districts = false;
                    this.regionOptions.districts = [];
                },

                async searchLocation() {
                    if (!this.searchQuery.trim()) return;

                    this.isSearching = true;
                    this.searchError = '';

                    try {
                        const response = await fetch(
                            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery)}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'User-Agent': 'LaporPakApp/1.0 (Contact: admin@laporpak.com)'
                                }
                            });

                        if (!response.ok) throw new Error('Network response was not ok');

                        const data = await response.json();

                        if (data && data.length > 0) {
                            const result = data[0];
                            this.form.latitude = parseFloat(result.lat).toFixed(8);
                            this.form.longitude = parseFloat(result.lon).toFixed(8);
                        } else {
                            this.searchError = 'Lokasi tidak ditemukan. Coba kata kunci yang lebih spesifik.';
                        }
                    } catch (error) {
                        console.error('Geocoding error:', error);
                        this.searchError = 'Terjadi kesalahan koneksi saat mencari lokasi.';
                    } finally {
                        this.isSearching = false;
                    }
                },

                handlePhotoUpload(event) {
                    const files = Array.from(event.target.files);
                    const remaining = 10 - this.savedPhotoPreviews.length - this.photoPreviews.length;
                    const toAdd = files.slice(0, remaining);
                    let fileError = files.length > remaining ? 'Maksimal 10 foto yang dapat diunggah.' : '';

                    toAdd.forEach(file => {
                        if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
                            if (!fileError) fileError = 'Format foto yang didukung: JPEG, PNG, atau WebP.';
                            return;
                        }
                        if (file.size > 2 * 1024 * 1024) {
                            if (!fileError) fileError = 'Ukuran foto maksimal 2MB.';
                            return;
                        }

                        this.photoFiles.push(file);
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.photoPreviews.push(e.target.result);
                        };
                        reader.readAsDataURL(file);
                    });

                    if (fileError) {
                        this.photoUploadError = fileError;
                        this.errors.photos = fileError;
                    } else if (toAdd.length > 0) {
                        this.photoUploadError = '';
                        delete this.errors.photos;
                    }

                    event.target.value = '';
                },

                removePhoto(index) {
                    this.photoFiles.splice(index, 1);
                    this.photoPreviews.splice(index, 1);
                },

                handleCertificateUpload(event, index) {
                    const file = event.target.files?.[0];
                    if (!file) return;

                    const error = this.validateDocumentFile(file);

                    if (error) {
                        this.setCertificateError(index, 'certificate_document', error);
                        event.target.value = '';
                        return;
                    }

                    this.applyCertificateFile(index, file);
                },

                handleCertificateDrop(event, index) {
                    this.dragging = '';

                    const file = event.dataTransfer?.files?.[0];
                    if (!file) return;

                    const error = this.validateDocumentFile(file);

                    if (error) {
                        this.setCertificateError(index, 'certificate_document', error);
                        return;
                    }

                    this.applyCertificateFile(index, file);
                },

                applyCertificateFile(index, file) {
                    const certificate = this.certificates[index];
                    if (!certificate) return;

                    certificate.file = file;
                    certificate.errors = {};
                    delete this.errors[this.serverCertificateErrorKey(index, 'certificate_document')];
                },

                setCertificateError(index, field, message) {
                    const certificate = this.certificates[index];
                    if (!certificate) return;

                    certificate.errors = { ...certificate.errors, [field]: message };
                },

                removeCertificateFile(index, event = null) {
                    const certificate = this.certificates[index];
                    if (certificate) {
                        certificate.file = null;
                        certificate.errors = {};
                    }

                    const entry = event?.currentTarget?.closest('[data-certificate-entry]') ??
                        event?.target?.closest('[data-certificate-entry]') ??
                        document.querySelectorAll('[data-certificate-entry]')[index];
                    const input = entry?.querySelector('input[type="file"]');
                    if (input) input.value = '';

                    delete this.errors[this.serverCertificateErrorKey(index, 'certificate_document')];
                },

                addCertificateEntry() {
                    if (this.certificates.length >= this.maxCertificates) return;

                    this.certificates.push({
                        key: this.certificateKeySeq++,
                        jenis_sertifikat: '',
                        file: null,
                        errors: {},
                    });

                    this.$nextTick(() => {
                        const entries = document.querySelectorAll('[data-certificate-entry]');
                        entries[entries.length - 1]?.querySelector('select')?.focus();
                    });
                },

                removeCertificateEntry(index) {
                    if (index === 0) {
                        this.removeCertificateFile(0);
                        return;
                    }

                    this.certificates.splice(index, 1);

                    Object.keys(this.errors)
                        .filter((key) => key.startsWith('additional_certificates.'))
                        .forEach((key) => delete this.errors[key]);
                },

                handleOptionalDocumentUpload(event, key) {
                    const file = event.target.files?.[0];
                    if (!file) return;

                    const error = this.validateDocumentFile(file);

                    if (error) {
                        this.optionalDocuments[key].error = error;
                        event.target.value = '';
                        return;
                    }

                    this.applyOptionalDocument(key, file);
                },

                handleOptionalDocumentDrop(event, key) {
                    this.dragging = '';

                    const file = event.dataTransfer?.files?.[0];
                    if (!file) return;

                    const error = this.validateDocumentFile(file);

                    if (error) {
                        this.optionalDocuments[key].error = error;
                        return;
                    }

                    this.applyOptionalDocument(key, file);
                },

                applyOptionalDocument(key, file) {
                    if (!this.optionalDocuments[key]) return;

                    this.optionalDocuments[key].file = file;
                    this.optionalDocuments[key].error = '';
                    delete this.errors[`${key}_document`];
                },

                removeOptionalDocument(key) {
                    if (!this.optionalDocuments[key]) return;

                    this.optionalDocuments[key].file = null;
                    this.optionalDocuments[key].error = '';
                    delete this.errors[`${key}_document`];

                    const input = document.getElementById(this.optionalDocumentInputIds[key]);
                    if (input) input.value = '';
                },

                validateDocumentFile(file) {
                    if (!file) return 'Pilih file terlebih dahulu.';
                    if (!this.isAcceptedDocument(file)) return 'Format file harus PDF, JPG, atau PNG.';
                    if (file.size > 10 * 1024 * 1024) return 'Ukuran file maksimal 10MB.';

                    return '';
                },

                isAcceptedDocument(file) {
                    const extension = (file.name.split('.').pop() || '').toLowerCase();
                    const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                    const allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];

                    return allowedExtensions.includes(extension) &&
                        (file.type === '' || allowedMimeTypes.includes(file.type));
                },

                certificateFieldName(index, field) {
                    return index === 0 ? field : `additional_certificates[${index - 1}][${field}]`;
                },

                serverCertificateErrorKey(index, field) {
                    return index === 0 ? field : `additional_certificates.${index - 1}.${field}`;
                },

                certificateError(index, field) {
                    const localError = this.certificates[index]?.errors?.[field];

                    if (localError) return localError;

                    return this.errors?.[this.serverCertificateErrorKey(index, field)] || '';
                },

                certificateTypeLabel() {
                    const labels = {
                        SHM: 'SHM — Hak Milik',
                        SHGB: 'SHGB — Hak Guna Bangunan',
                        GIRIK: 'Girik',
                        AJB: 'AJB — Akta Jual Beli',
                        OTHER: 'Lainnya',
                    };

                    return labels[this.certificates[0]?.jenis_sertifikat] || '-';
                },

                hasCertificateFile() {
                    return this.certificates.some((certificate) => certificate.file || certificate.savedName);
                },

                fileMeta(file) {
                    if (!file) return '';

                    return `${this.formatFileSize(file.size)} • ${this.fileTypeLabel(file)}`;
                },

                fileTypeLabel(file) {
                    if (!file?.name) return 'FILE';

                    const extension = (file.name.split('.').pop() || '').toUpperCase();

                    return extension === 'JPEG' ? 'JPG' : extension;
                },

                goToStep(step) {
                    if (step < this.currentStep) {
                        this.currentStep = step;
                    }
                },

                async scrollToFirstError(errorFields) {
                    const field = errorFields[0];
                    if (!field) return;

                    await this.$nextTick();

                    let control = null;
                    let container = null;

                    if (field === 'general') {
                        container = document.getElementById('listing-validation-summary');
                    } else if (field === 'agreedTerms') {
                        container = document.getElementById('listing-agreement-field');
                    } else if (field === 'photos' || field.startsWith('photos.')) {
                        const photoGrid = document.getElementById('photo-preview-grid');
                        container = photoGrid?.parentElement?.querySelector('.wz-error') || photoGrid;
                    } else if (field === 'additional_certificates') {
                        container = document.querySelector('.wz-cert-list');
                    } else if (field === 'url_maps') {
                        const map = Array.from(document.querySelectorAll('.wz-map'))
                            .find((element) => element.offsetParent !== null);
                        container = map?.closest('.wizard-field') || map;
                    } else {
                        const certificateError = field.match(
                            /^(?:certificate\.(\d+)\.|additional_certificates\.(\d+)\.|)(jenis_sertifikat|certificate_document)$/
                        );

                        if (certificateError) {
                            const uploadedCertificateIndices = this.certificates
                                .map((certificate, index) => certificate.file ? index : -1)
                                .filter((index) => index >= 0);
                            const entryIndex = certificateError[1] !== undefined
                                ? Number(certificateError[1])
                                : certificateError[2] !== undefined
                                    ? uploadedCertificateIndices[Number(certificateError[2]) + 1] ??
                                        Number(certificateError[2]) + 1
                                    : uploadedCertificateIndices[0] ?? 0;
                            const entries = document.querySelectorAll('[data-certificate-entry]');
                            const entry = entries[entryIndex];
                            const certificateField = certificateError[3];

                            if (entry) {
                                container = entry;
                                control = certificateField === 'jenis_sertifikat'
                                    ? entry.querySelector('select')
                                    : entry.querySelector('input[type="file"]');
                            }
                        }
                    }

                    const fieldIds = {
                        province: 'province-search',
                        province_code: 'province-search',
                        city: 'city-search',
                        city_code: 'city-search',
                        district: 'district-search',
                        district_code: 'district-search',
                        imb_document: 'imb-document-input',
                        pbb_document: 'pbb-document-input',
                    };

                    if (!control && fieldIds[field]) {
                        control = document.getElementById(fieldIds[field]);
                    }

                    if (!control) {
                        control = Array.from(document.querySelectorAll('[x-model]'))
                            .find((element) => element.getAttribute('x-model') === `form.${field}`) || null;
                    }

                    if (!control) {
                        control = document.getElementsByName(field)[0] || null;
                    }

                    if (!container && control) {
                        container = control.closest('.wizard-field, .wz-region-field, .wizard-type-card') || control;
                    }

                    const target = container || control || document.getElementById('listing-validation-summary');
                    if (!target) return;

                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    if (control && !control.disabled) {
                        control.focus({ preventScroll: true });
                    }
                },

                hasDraftData() {
                    return Object.entries(this.form).some(([key, value]) => {
                        if (key === 'negotiable') return false;
                        return typeof value === 'string' ? value.trim() !== '' : value !== null && value !== undefined;
                    });
                },

                scheduleDraftSave(markAsEdited = false) {
                    if (markAsEdited) {
                        this.draftEditRevision += 1;
                        this.draftButtonState = 'unsaved';
                    }

                    if (this.draftSaveTimer) {
                        clearTimeout(this.draftSaveTimer);
                        this.draftSaveTimer = null;
                    }

                    if (!this.hasDraftData() && !this.draftUuid) return;

                    this.draftSaveState = 'saving';
                    this.draftSaveTimer = setTimeout(() => {
                        this.draftSaveTimer = null;
                        this.persistDraft();
                    }, 800);
                },

                async saveDraft() {
                    if (this.draftSaveTimer) {
                        clearTimeout(this.draftSaveTimer);
                        this.draftSaveTimer = null;
                    }

                    const editRevision = this.draftEditRevision;
                    this.draftButtonState = 'saving';
                    await this.persistDraft();
                    this.draftButtonState = this.draftEditRevision === editRevision &&
                        this.draftSaveState === 'saved' && this.draftUuid
                        ? 'saved'
                        : 'unsaved';
                },

                async persistDraft() {
                    if (!this.hasDraftData() && !this.draftUuid) return;

                    if (this.draftSavePromise) {
                        this.draftSavePending = true;
                        await this.draftSavePromise;
                        if (this.draftSavePending) {
                            this.draftSavePending = false;
                            await this.persistDraft();
                        }
                        return;
                    }

                    this.draftSaveInProgress = true;
                    this.draftSaveState = 'saving';
                    this.draftSaveError = '';

                    const data = {
                        ...this.form,
                        url_maps: this.osmUrl,
                        price: String(this.form.price || '').replace(/\D/g, ''),
                    };

                    this.draftSavePromise = (async () => {
                        try {
                            const response = await fetch(this.draftSaveEndpoint, {
                                method: 'POST',
                                keepalive: true,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    draft_uuid: this.draftUuid,
                                    current_step: this.currentStep,
                                    data,
                                }),
                            });
                            const payload = await response.json().catch(() => null);

                            if (!response.ok || !payload?.success) {
                                throw new Error(payload?.message ||
                                    'Draf gagal disimpan. Periksa koneksi lalu coba lagi.');
                            }

                            this.draftUuid = payload.draft_uuid || this.draftUuid;
                            this.hasDraft = Boolean(this.draftUuid);
                            this.draftSaveState = this.hasDraft ? 'saved' : '';
                        } catch (error) {
                            console.error('Draft save error:', error);
                            this.draftSaveState = 'error';
                            this.draftSaveError = error.message ||
                                'Draf gagal disimpan. Periksa koneksi lalu coba lagi.';
                        }
                    })();

                    try {
                        await this.draftSavePromise;
                    } finally {
                        this.draftSavePromise = null;
                        this.draftSaveInProgress = false;
                    }

                    if (this.draftSavePending) {
                        this.draftSavePending = false;
                        await this.persistDraft();
                    }
                },

                async submitStepOne() {
                    this.errors = {};
                    if (!this.draftUuid && this.hasDraftData()) {
                        await this.saveDraft();
                        if (!this.draftUuid) {
                            this.errors.general = this.draftSaveError ||
                                'Draf gagal disimpan. Periksa koneksi lalu coba lagi.';
                            await this.scrollToFirstError(['general']);
                            return;
                        }
                    }
                    this.isLoading = true;

                    const priceNumeric = this.form.price.replace(/\./g, '');

                    try {
                        const response = await fetch('{{ route('listing.step-one') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                ...this.form,
                                price: priceNumeric,
                                url_maps: this.osmUrl,
                                draft_uuid: this.draftUuid,
                            }),
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            if (data.errors) {
                                const flatErrors = {};
                                for (const [key, messages] of Object.entries(data.errors)) {
                                    flatErrors[key] = messages[0];
                                }
                                flatErrors.summary = Object.values(flatErrors)[0];
                                this.errors = flatErrors;
                                await this.scrollToFirstError(Object.keys(data.errors));
                            } else {
                                this.errors.general = data.message || 'Periksa kembali data properti Anda.';
                                await this.scrollToFirstError(['general']);
                            }
                            return;
                        }

                        this.draftUuid = data.draft_uuid;
                        this.hasDraft = Boolean(this.draftUuid);
                        this.draftSaveState = 'saved';
                        this.currentStep = 2;
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });

                    } catch (error) {
                        console.error('Step 1 error:', error);
                        this.errors.general = 'Terjadi kesalahan. Silakan coba lagi.';
                        await this.scrollToFirstError(['general']);
                    } finally {
                        this.isLoading = false;
                    }
                },

                async submitStepTwo() {
                    this.errors = {};

                    if (this.photoUploadError) {
                        this.errors.photos = this.photoUploadError;
                        this.errors.summary = this.photoUploadError;
                        await this.scrollToFirstError(['photos']);
                        return;
                    }

                    if (this.photoFiles.length + this.savedPhotoPreviews.length === 0) {
                        this.errors.photos = 'Minimal 1 foto properti wajib diunggah.';
                        this.errors.summary = this.errors.photos;
                        await this.scrollToFirstError(['photos']);
                        return;
                    }

                    const certificateErrors = [];

                    this.certificates.forEach((certificate, index) => {
                        if (!certificate.jenis_sertifikat) {
                            certificate.errors = {
                                ...certificate.errors,
                                jenis_sertifikat: 'Jenis surat wajib dipilih.',
                            };
                            certificateErrors.push(`certificate.${index}.jenis_sertifikat`);
                        }

                        if (!certificate.file && !certificate.savedName) {
                            certificate.errors = {
                                ...certificate.errors,
                                certificate_document: certificate.errors.certificate_document ||
                                    'File sertifikat wajib diunggah.',
                            };
                            certificateErrors.push(`certificate.${index}.certificate_document`);
                        }
                    });

                    if (certificateErrors.length > 0) {
                        this.errors.summary = this.certificates
                            .flatMap((certificate) => Object.values(certificate.errors))
                            .find(Boolean);
                        await this.scrollToFirstError(certificateErrors);
                        return;
                    }

                    if (!this.agreedTerms) {
                        this.errors.agreedTerms = 'Anda harus menyetujui pernyataan sebelum melanjutkan.';
                        this.errors.summary = this.errors.agreedTerms;
                        await this.scrollToFirstError(['agreedTerms']);
                        return;
                    }

                    this.isLoading = true;

                    try {
                        const formData = new FormData();
                        formData.append('draft_uuid', this.draftUuid || '');
                        this.photoFiles.forEach(file => formData.append('photos[]', file));

                        this.certificates.filter((certificate) => certificate.file).forEach((certificate, index) => {
                            formData.append(
                                this.certificateFieldName(index, 'jenis_sertifikat'),
                                certificate.jenis_sertifikat
                            );
                            formData.append(
                                this.certificateFieldName(index, 'certificate_document'),
                                certificate.file
                            );
                        });

                        if (this.optionalDocuments.imb.file) {
                            formData.append('imb_document', this.optionalDocuments.imb.file);
                        }

                        if (this.optionalDocuments.pbb.file) {
                            formData.append('pbb_document', this.optionalDocuments.pbb.file);
                        }

                        const url = '{{ route('listing.step-two') }}';

                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            if (data.errors) {
                                const flatErrors = {};
                                for (const [key, messages] of Object.entries(data.errors)) {
                                    flatErrors[key] = messages[0];
                                    if (key.startsWith('photos.')) {
                                        flatErrors.photos = messages[0];
                                    }
                                }
                                flatErrors.summary = Object.values(flatErrors)[0];
                                this.errors = flatErrors;
                                await this.scrollToFirstError(Object.keys(data.errors));
                            } else {
                                this.errors.general = data.message || 'Foto dan dokumen gagal disimpan.';
                                await this.scrollToFirstError(['general']);
                            }
                            return;
                        }

                        if (data.uploads) {
                            this.savedPhotoPreviews = data.uploads.photos || [];
                            this.photoFiles = [];
                            this.photoPreviews = [];
                            this.certificates = (data.uploads.certificates || []).map((certificate, index) => ({
                                key: index,
                                jenis_sertifikat: certificate.type,
                                file: null,
                                savedName: certificate.name,
                                errors: {},
                            }));
                            this.certificateKeySeq = this.certificates.length;
                            this.optionalDocuments.imb.file = null;
                            this.optionalDocuments.imb.savedName = data.uploads.imb?.name || '';
                            this.optionalDocuments.pbb.file = null;
                            this.optionalDocuments.pbb.savedName = data.uploads.pbb?.name || '';
                        }

                        this.draftSaveState = 'saved';
                        this.currentStep = 3;
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });

                    } catch (error) {
                        console.error('Step 2 error:', error);
                        this.errors.general = 'Terjadi kesalahan saat upload. Silakan coba lagi.';
                        await this.scrollToFirstError(['general']);
                    } finally {
                        this.isLoading = false;
                    }
                },

                async submitListing() {
                    this.errors = {};
                    this.isLoading = true;

                    try {
                        const url = '{{ route('listing.submit') }}';

                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                draft_uuid: this.draftUuid,
                            }),
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            console.error('Submit error:', data);
                            if (data.errors) {
                                const flatErrors = {};
                                for (const [key, messages] of Object.entries(data.errors)) {
                                    flatErrors[key] = messages[0];
                                    if (key.startsWith('photos.')) {
                                        flatErrors.photos = messages[0];
                                    }
                                }
                                flatErrors.summary = Object.values(flatErrors)[0];
                                this.errors = flatErrors;
                                const errorFields = Object.keys(data.errors);
                                this.currentStep = errorFields.some((field) =>
                                    field === 'photos' || field.startsWith('photos.') ||
                                    field === 'certificate_document' || field === 'jenis_sertifikat' ||
                                    field.startsWith('additional_certificates.')
                                ) ? 2 : 1;
                                await this.scrollToFirstError(errorFields);
                            } else {
                                this.errors.general = data.message || 'Listing gagal dikirim. Silakan coba lagi.';
                                await this.scrollToFirstError(['general']);
                            }
                            return;
                        }

                        this.showSuccess = true;

                    } catch (error) {
                        console.error('Submit error:', error);
                        this.errors.general = 'Terjadi kesalahan saat mengirim listing. Silakan coba lagi.';
                        await this.scrollToFirstError(['general']);
                    } finally {
                        this.isLoading = false;
                    }
                },

                initMap(containerId) {
                    if (this.mapInstance) {
                        this.mapInstance.remove();
                        this.mapInstance = null;
                    }

                    this.mapContainerId = containerId || 'wizard-map';
                    const defaultLat = -6.2088;
                    const defaultLng = 106.8456;

                    setTimeout(() => {
                        const container = document.getElementById(this.mapContainerId);
                        if (!container) return;

                        this.mapInstance = L.map(this.mapContainerId).setView([defaultLat, defaultLng], 12);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                        }).addTo(this.mapInstance);

                        setTimeout(() => {
                            if (this.mapInstance) {
                                this.mapInstance.invalidateSize();
                            }
                        }, 100);

                        if (this.form.latitude && this.form.longitude) {
                            this.createDraggableMarker([this.form.latitude, this.form.longitude]);
                            this.mapInstance.setView([this.form.latitude, this.form.longitude], 15);
                            this.updateOsmUrl(this.form.latitude, this.form.longitude);
                        }

                        this.mapInstance.on('click', (e) => {
                            this.setSelectedLocation(e.latlng);
                        });

                        this.$watch('form.latitude', (val) => this.updateMarkerFromInputs());
                        this.$watch('form.longitude', (val) => this.updateMarkerFromInputs());

                    }, 200);
                },

                updateMarkerFromInputs() {
                    const lat = parseFloat(this.form.latitude);
                    const lng = parseFloat(this.form.longitude);

                    if (!isNaN(lat) && !isNaN(lng)) {
                        const latlng = [lat, lng];
                        if (this.mapMarker) {
                            this.mapMarker.setLatLng(latlng);
                        } else {
                            this.createDraggableMarker(latlng);
                        }
                        this.updateOsmUrl(lat, lng);
                        if (this.mapInstance) {
                            this.mapInstance.setView(latlng, 15);
                        }
                    }
                },

                createDraggableMarker(latlng) {
                    if (!this.mapInstance) return;
                    this.mapMarker = L.marker(latlng, {
                        draggable: true
                    }).addTo(this.mapInstance);
                    this.mapMarker.on('dragend', (event) => {
                        this.setSelectedLocation(event.target.getLatLng());
                    });
                },

                setSelectedLocation(latlng) {
                    const latitude = latlng.lat;
                    const longitude = latlng.lng;

                    this.form.latitude = latitude.toFixed(8);
                    this.form.longitude = longitude.toFixed(8);

                    if (this.mapMarker) {
                        this.mapMarker.setLatLng(latlng);
                    } else {
                        this.createDraggableMarker(latlng);
                    }

                    this.updateOsmUrl(latitude, longitude);
                },

                updateOsmUrl(latitude, longitude) {
                    this.osmUrl =
                        `https://www.openstreetmap.org/?mlat=${latitude}&mlon=${longitude}#map=16/${latitude}/${longitude}`;
                }
            };
        }
    </script>