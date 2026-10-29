{{--
    Optional supporting document upload (IMB/PBG, SPPT/STTS PBB).

    Expected variables:
    - $key      Alpine state key inside `optionalDocuments` (imb, pbb)
    - $inputId  Unique DOM id for the native file input
    - $name     Request field name
    - $label    Visible field label
--}}
@php($documentFile = "optionalDocuments.{$key}.file")

<div class="wz-doc-upload">
    <label class="wz-label" for="{{ $inputId }}">{{ $label }}</label>

    <label for="{{ $inputId }}" class="wz-dropzone" x-show="!{{ $documentFile }}"
        :class="{ 'is-dragging': dragging === '{{ $key }}' }" @dragenter.prevent="dragging = '{{ $key }}'"
        @dragover.prevent="dragging = '{{ $key }}'" @dragleave.prevent="dragging = ''"
        @drop.prevent="handleOptionalDocumentDrop($event, '{{ $key }}')">
        <span class="wz-dropzone-icon" aria-hidden="true">
            <x-cloud-upload-icon class="h-10 w-10" />
        </span>
        <span class="wz-dropzone-text">
            <span class="wz-dropzone-title">Pilih file</span>
            <span class="wz-dropzone-desc">atau drag &amp; drop · PDF, JPG, PNG · Maks. 10MB</span>
        </span>
        <input type="file" id="{{ $inputId }}" name="{{ $name }}"
            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="sr-only"
            :disabled="!!{{ $documentFile }}" @change="handleOptionalDocumentUpload($event, '{{ $key }}')">
    </label>

    <div class="wz-file-item" x-show="{{ $documentFile }}">
        <span class="wz-file-icon" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8m-5-5l5 5m-5-5v5h5M9 13h6M9 17h4" />
            </svg>
        </span>
        <span class="wz-file-info">
            <span class="wz-file-name" x-text="{{ $documentFile }}?.name"></span>
            <span class="wz-file-meta" x-text="fileMeta({{ $documentFile }})"></span>
        </span>
        <button type="button" class="wz-file-remove" @click="removeOptionalDocument('{{ $key }}')"
            :aria-label="'Hapus file ' + ({{ $documentFile }}?.name || '{{ $label }}')">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <template x-if="optionalDocuments.{{ $key }}.error">
        <p class="wz-error mt-2" x-text="optionalDocuments.{{ $key }}.error"></p>
    </template>
    <template x-if="errors.{{ $name }}">
        <p class="wz-error mt-2" x-text="errors.{{ $name }}"></p>
    </template>
</div>
