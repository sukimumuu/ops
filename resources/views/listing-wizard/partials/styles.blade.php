    <style>
        /* ===== PAGE LAYOUT ===== */
        .wizard-page {
            max-width: 720px;
            margin: 0 auto;
            padding: 16px 8px 48px;
            font-family: 'Manrope', sans-serif;
        }

        @media (min-width: 640px) {
            .wizard-page {
                padding: 32px 0 64px;
            }
        }

        /* ===== PAGE HEADER ===== */
        .wizard-page-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .wizard-back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s;
        }

        .wizard-back-link:hover {
            color: #2C3E50;
        }

        .wizard-page-title {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: #2C3E50;
            flex: 1;
            text-align: center;
        }

        @media (min-width: 640px) {
            .wizard-page-title {
                font-size: 26px;
            }
        }

        .wizard-draft-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
        }

        /* ===== STEP INDICATOR ===== */
        .wizard-steps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 32px;
        }

        .wizard-step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            cursor: default;
            transition: all 0.3s;
        }

        .wizard-step-item.completed {
            cursor: pointer;
        }

        .wizard-step-number {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 700;
            background: #e2e8f0;
            color: #94a3b8;
            transition: all 0.3s;
        }

        .wizard-step-item.active .wizard-step-number {
            background: #FC4907;
            color: #fff;
            box-shadow: 0 4px 14px rgba(252, 73, 7, 0.3);
        }

        .wizard-step-item.completed .wizard-step-number {
            background: #10b981;
            color: #fff;
        }

        .wizard-step-label {
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
            white-space: nowrap;
        }

        .wizard-step-item.active .wizard-step-label {
            color: #FC4907;
            font-weight: 700;
        }

        .wizard-step-item.completed .wizard-step-label {
            color: #10b981;
        }

        @media (max-width: 639px) {
            .wizard-step-label {
                font-size: 11px;
            }
        }

        .wizard-step-connector {
            width: 48px;
            height: 3px;
            border-radius: 3px;
            background: #e2e8f0;
            margin: 0 8px;
            margin-bottom: 24px;
            transition: background 0.4s;
        }

        .wizard-step-connector.active {
            background: #10b981;
        }

        @media (min-width: 640px) {
            .wizard-step-connector {
                width: 80px;
            }
        }

        /* ===== CARD ===== */
        .wizard-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 4px 12px rgba(0, 0, 0, 0.02);
            overflow: hidden;
        }

        .wizard-card-body {
            padding: 24px;
        }

        @media (min-width: 640px) {
            .wizard-card-body {
                padding: 32px;
            }
        }

        /* ===== CARD FOOTER ===== */
        .wizard-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
        }

        @media (min-width: 640px) {
            .wizard-card-footer {
                padding: 16px 32px;
            }
        }

        /* ===== SECTIONS ===== */
        .wizard-section {
            margin-bottom: 24px;
        }

        .wizard-section:last-child {
            margin-bottom: 0;
        }

        .wizard-sections-wrapper {
            display: contents;
        }

        .wizard-section-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .wizard-section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #2C3E50;
        }

        .wizard-section-subtitle {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 4px;
            line-height: 1.5;
        }

        .wizard-divider {
            height: 1px;
            background: #f1f5f9;
            margin: 28px 0;
        }

        /* ===== TYPE SELECTOR CARDS ===== */
        .wizard-type-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .wizard-type-card {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 24px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.25s ease;
            background: #fff;
        }

        .wizard-type-card:hover {
            border-color: #cbd5e1;
            background: #fafbfc;
            transform: translateY(-1px);
        }

        .wizard-type-card.selected {
            border-color: #FC4907;
            background: #FFF7F4;
            box-shadow: 0 0 0 3px rgba(252, 73, 7, 0.1);
        }

        .wizard-type-check {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #FC4907;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wizard-type-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            transition: all 0.25s;
        }

        .wizard-type-icon.bangunan {
            background: #FFF0EB;
            color: #FC4907;
        }

        .wizard-type-card.selected .wizard-type-icon.bangunan {
            background: #FC4907;
            color: #fff;
        }

        .wizard-type-icon.tanah {
            background: #f0f4f8;
            color: #64748b;
        }

        .wizard-type-card.selected .wizard-type-icon.tanah {
            background: #FC4907;
            color: #fff;
        }

        .wizard-type-title {
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: #2C3E50;
            margin-bottom: 4px;
        }

        .wizard-type-card.selected .wizard-type-title {
            color: #FC4907;
        }

        .wizard-type-desc {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.4;
        }

        /* ===== FORM GRIDS ===== */
        .wizard-form-grid {
            display: grid;
            gap: 16px;
        }

        .wizard-form-grid.grid-2 {
            grid-template-columns: 1fr 1fr;
        }

        .wizard-form-grid.grid-3 {
            grid-template-columns: 1fr 1fr 1fr;
        }

        @media (max-width: 639px) {
            .wizard-form-grid.grid-3 {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* ===== FORM FIELDS ===== */
        .wizard-field {
            display: flex;
            flex-direction: column;
        }

        .wz-label {
            font-size: 14px;
            font-weight: 600;
            color: #2C3E50;
            margin-bottom: 6px;
        }

        .wz-label-sm {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 4px;
        }

        .wz-required {
            color: #FC4907;
        }

        .wz-hint {
            font-size: 13px;
            color: #94a3b8;
        }

        /* ===== INPUT ===== */
        .wz-input {
            display: block;
            width: 100%;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 500;
            font-family: 'Manrope', sans-serif;
            color: #2C3E50;
            outline: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .wz-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .wz-input:focus {
            border-color: #FC4907;
            box-shadow: 0 0 0 3px rgba(252, 73, 7, 0.1), 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .wz-input:disabled {
            cursor: not-allowed;
            background: #f8fafc;
            color: #94a3b8;
        }

        select.wz-input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 20px 20px;
            padding-right: 40px;
        }

        .wz-textarea {
            resize: none;
            line-height: 1.6;
        }

        /* ===== INPUT GROUP ===== */
        .wz-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .wz-input-prefix {
            position: absolute;
            left: 14px;
            font-size: 14px;
            font-weight: 600;
            color: #94a3b8;
            pointer-events: none;
            z-index: 1;
        }

        .wz-input.has-prefix {
            padding-left: 40px;
        }

        .wz-input-suffix {
            position: absolute;
            right: 14px;
            font-size: 13px;
            font-weight: 600;
            color: #94a3b8;
            pointer-events: none;
            z-index: 1;
        }

        .wz-input.has-suffix {
            padding-right: 40px;
        }

        .wz-price-input-group {
            display: flex;
            align-items: stretch;
            gap: 12px;
            width: 100%;
        }

        .wz-price-input-shell {
            flex: 1 1 auto;
            min-width: 0;
        }

        .wz-rent-period-shell {
            width: 160px;
            flex-shrink: 0;
        }

        @media (max-width: 639px) {
            .wz-price-input-group {
                flex-direction: column;
            }

            .wz-rent-period-shell {
                width: 100%;
            }
        }

        /* ===== HELPER TEXT ===== */
        .wz-helper {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 6px;
            line-height: 1.5;
        }

        .wz-helper-icon {
            display: flex;
            align-items: flex-start;
            gap: 6px;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
        }

        .wz-error {
            font-size: 12px;
            font-weight: 600;
            color: #ef4444;
            margin-top: 4px;
        }

        /* ===== CHECKBOX ===== */
        .wz-checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
            user-select: none;
        }

        .wz-checkbox {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 1.5px solid #cbd5e1;
            accent-color: #FC4907;
            cursor: pointer;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .wz-checkbox-label span {
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
        }

        .wz-agreement span {
            font-size: 13px;
        }

        .wz-agreement strong {
            color: #2C3E50;
        }

        /* ===== MAP ===== */
        .wz-map-search {
            display: flex;
            gap: 8px;
        }

        .wz-btn-search {
            padding: 10px 14px;
            background: #2C3E50;
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .wz-btn-search:hover {
            background: #1a252f;
        }

        .wz-btn-search:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .wz-map-container {
            border-radius: 12px;
            overflow: hidden;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
        }

        .wz-map {
            width: 100%;
            height: 256px;
        }

        .wz-region-field {
            position: relative;
        }

        .wz-region-loading {
            position: absolute;
            top: 38px;
            right: 12px;
            width: 16px;
            height: 16px;
            color: #64748b;
        }

        .wz-region-options {
            position: absolute;
            z-index: 30;
            top: calc(100% + 4px);
            right: 0;
            left: 0;
            max-height: 220px;
            overflow-y: auto;
            padding: 4px;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        }

        .wz-region-option {
            display: block;
            width: 100%;
            padding: 9px 10px;
            border: 0;
            border-radius: 5px;
            background: transparent;
            color: #2C3E50;
            text-align: left;
            font: inherit;
            font-size: 13px;
            cursor: pointer;
        }

        .wz-region-option:hover,
        .wz-region-option.active {
            background: #fff0eb;
            color: #c83b05;
        }

        .wz-region-message {
            padding: 10px;
            color: #64748b;
            font-size: 12px;
        }

        .wz-region-retry {
            margin: 0 10px 10px;
            padding: 0;
            border: 0;
            background: transparent;
            color: #c83b05;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        @media (min-width: 640px) {
            .wz-map {
                height: 320px;
            }
        }

        /* ===== BUTTONS ===== */
        .wz-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: #FC4907;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Manrope', sans-serif;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 4px 12px rgba(252, 73, 7, 0.25);
            white-space: nowrap;
        }

        .wz-btn-primary:hover {
            background: #e04006;
            box-shadow: 0 6px 20px rgba(252, 73, 7, 0.35);
            transform: translateY(-1px);
        }

        .wz-btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .wz-btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: transparent;
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Manrope', sans-serif;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .wz-btn-back:hover {
            background: #f1f5f9;
            color: #2C3E50;
        }

        .wz-btn-draft {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: transparent;
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Manrope', sans-serif;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .wz-btn-draft:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #2C3E50;
        }

        /* ===== PHOTO GRID ===== */
        .wz-photo-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        @media (min-width: 640px) {
            .wz-photo-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .wz-photo-item {
            position: relative;
            aspect-ratio: 4/3;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
        }

        .wz-photo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .wz-photo-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .wz-photo-item:hover .wz-photo-overlay {
            opacity: 1;
        }

        .wz-photo-remove {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            color: #ef4444;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .wz-photo-remove:hover {
            background: #ef4444;
            color: #fff;
        }

        .wz-photo-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            padding: 2px 8px;
            background: #FC4907;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .wz-photo-add {
            aspect-ratio: 4/3;
            border-radius: 12px;
            border: 2px dashed #cbd5e1;
            background: #fafbfc;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .wz-photo-add:hover {
            border-color: #FC4907;
            background: #FFF7F4;
        }

        .wz-photo-add-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(252, 73, 7, 0.08);
            color: #FC4907;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .wz-photo-add:hover .wz-photo-add-icon {
            background: rgba(252, 73, 7, 0.15);
        }

        .wz-photo-add span {
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
            transition: color 0.2s;
        }

        .wz-photo-add:hover span {
            color: #FC4907;
        }

        /* ===== CERTIFICATE & DOCUMENT UPLOAD ===== */
        .wz-cert-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .wz-cert-card {
            position: relative;
            padding: 18px;
            border: 1px solid rgba(44, 62, 80, 0.16);
            border-radius: 16px;
            background: #fff;
        }

        .wz-cert-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        .wz-cert-col {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .wz-cert-col + .wz-cert-col {
            padding-left: 24px;
            border-left: 1px solid rgba(44, 62, 80, 0.12);
        }

        @media (max-width: 639px) {
            .wz-cert-form {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .wz-cert-col + .wz-cert-col {
                padding-left: 0;
                padding-top: 16px;
                border-left: none;
                border-top: 1px solid rgba(44, 62, 80, 0.12);
            }
        }

        .wz-cert-cancel {
            grid-column: 1 / -1;
            justify-self: start;
            padding: 0;
            border: none;
            background: transparent;
            color: #64748b;
            font-family: 'Manrope', sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .wz-cert-cancel:hover {
            color: #FC4907;
        }

        .wz-cert-cancel:focus-visible {
            outline: 2px solid rgba(252, 73, 7, 0.4);
            outline-offset: 2px;
            border-radius: 4px;
        }

        .wz-dropzone {
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
            padding: 16px;
            border: 1.5px dashed rgba(44, 62, 80, 0.28);
            border-radius: 12px;
            background: #F8FAFC;
            cursor: pointer;
            transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
        }

        .wz-cert-dropzone {
            cursor: default;
        }

        .wz-dropzone:hover,
        .wz-dropzone.is-dragging {
            border-color: #FC4907;
            background: #FFF7F4;
        }

        .wz-dropzone.has-file {
            border-color: #FC4907;
            background: rgba(252, 73, 7, 0.06);
        }

        .wz-dropzone.has-file:hover,
        .wz-dropzone.has-file.is-dragging {
            background: rgba(252, 73, 7, 0.1);
        }

        .wz-dropzone:focus-within {
            border-color: #FC4907;
            box-shadow: 0 0 0 3px rgba(252, 73, 7, 0.12);
        }

        .wz-dropzone-label {
            display: flex;
            align-items: center;
            gap: 14px;
            flex: 1 1 auto;
            min-width: 0;
            cursor: pointer;
        }

        .wz-dropzone.has-file .wz-dropzone-label {
            cursor: default;
        }

        .wz-dropzone-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            color: #FC4907;
        }

        .wz-dropzone-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .wz-cert-dropzone .wz-dropzone-text {
            flex: 1 1 auto;
        }

        .wz-dropzone-title {
            font-family: 'Manrope', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #2C3E50;
        }

        .wz-dropzone-desc {
            font-family: 'Manrope', sans-serif;
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
        }

        .wz-dropzone-filename {
            display: block;
            overflow-wrap: anywhere;
            font-family: 'Manrope', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #2C3E50;
        }

        .wz-dropzone-remove {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 32px;
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 8px;
            background: rgba(252, 73, 7, 0.1);
            color: #FC4907;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .wz-dropzone-remove:hover {
            background: #FC4907;
            color: #fff;
        }

        .wz-dropzone-remove:focus-visible {
            outline: 2px solid rgba(252, 73, 7, 0.5);
            outline-offset: 2px;
        }

        .wz-file-hint {
            margin-top: 6px;
            font-family: 'Manrope', sans-serif;
            font-size: 11px;
            color: #94a3b8;
        }

        .wz-file-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 14px;
            border: 1px solid rgba(44, 62, 80, 0.14);
            border-radius: 12px;
            background: #F8FAFC;
        }

        .wz-file-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: rgba(252, 73, 7, 0.1);
            color: #FC4907;
        }

        .wz-file-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex: 1 1 auto;
            min-width: 0;
        }

        .wz-file-name {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-family: 'Manrope', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #2C3E50;
        }

        .wz-file-meta {
            font-family: 'Manrope', sans-serif;
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
        }

        .wz-file-remove {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .wz-file-remove:hover {
            background: #fff;
            color: #FC4907;
        }

        .wz-file-remove:focus-visible {
            outline: 2px solid rgba(252, 73, 7, 0.5);
            outline-offset: 2px;
        }

        .wz-cert-add {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            width: 100%;
            padding: 14px 16px;
            border: none;
            border-radius: 12px;
            background: #FC4907;
            color: #fff;
            font-family: 'Manrope', sans-serif;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .wz-cert-add:hover {
            background: #e04006;
        }

        .wz-cert-add:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .wz-cert-add:disabled:hover {
            background: #FC4907;
        }

        .wz-cert-add:active {
            transform: translateY(1px);
        }

        .wz-cert-add:focus-visible {
            outline: 2px solid #2C3E50;
            outline-offset: 2px;
        }

        .wz-cert-add-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            text-align: left;
        }

        .wz-cert-add-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            color: #fff;
        }

        .wz-cert-add-text {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
        }

        .wz-cert-add-title {
            font-size: 14px;
            font-weight: 700;
        }

        .wz-cert-add-desc {
            font-size: 11px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
        }

        .wz-cert-add-plus {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .wz-doc-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 16px;
        }

        .wz-doc-upload {
            display: flex;
            flex-direction: column;
        }

        /* ===== INFO BOX ===== */
        .wz-info-box {
            display: flex;
            gap: 12px;
            padding: 14px 16px;
            background: linear-gradient(135deg, rgba(252, 73, 7, 0.04), rgba(252, 73, 7, 0.08));
            border: 1px solid rgba(252, 73, 7, 0.12);
            border-radius: 12px;
        }

        .wz-info-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(252, 73, 7, 0.12);
            color: #FC4907;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .wz-info-title {
            font-size: 13px;
            font-weight: 700;
            color: #2C3E50;
        }

        .wz-info-desc {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
            line-height: 1.5;
        }

        .wz-info-desc strong {
            color: #FC4907;
        }

        /* ===== REVIEW BLOCKS ===== */
        .wz-review-block {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .wz-review-block:last-of-type {
            margin-bottom: 0;
        }

        .wz-review-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
        }

        .wz-review-header h4 {
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #2C3E50;
        }

        .wz-review-edit {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 600;
            color: #FC4907;
            background: none;
            border: none;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .wz-review-edit:hover {
            opacity: 0.7;
            text-decoration: underline;
        }

        .wz-review-body {
            padding: 16px 20px;
        }

        .wz-review-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 32px;
        }

        .wz-review-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .wz-review-value {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #2C3E50;
            margin-top: 2px;
        }

        .wz-review-value.wz-price {
            color: #FC4907;
        }

        .wz-review-desc {
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
            margin-top: 4px;
        }

        .wz-review-photos {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .wz-review-photo-thumb {
            flex-shrink: 0;
            width: 72px;
            height: 56px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .wz-review-photo-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .wz-review-photo-count {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 6px;
        }

        .wz-review-cert-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            margin-top: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #166534;
        }

        .wz-review-cert-badge svg {
            color: #22c55e;
        }

        .wz-review-empty {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 4px;
            display: block;
        }

        /* ===== STATUS BOX ===== */
        .wz-status-box {
            display: flex;
            gap: 12px;
            padding: 14px 16px;
            background: linear-gradient(135deg, #fffbeb, rgba(252, 73, 7, 0.04));
            border: 1px solid rgba(217, 119, 6, 0.15);
            border-radius: 12px;
            margin-top: 20px;
        }

        .wz-status-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #fef3c7;
            color: #d97706;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .wz-status-title {
            font-size: 13px;
            font-weight: 700;
            color: #2C3E50;
        }

        .wz-status-pending {
            color: #d97706;
        }

        .wz-status-desc {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
            line-height: 1.5;
        }

        /* ===== SUCCESS MODAL ===== */
        .wz-success-modal {
            background: #fff;
            border-radius: 20px;
            padding: 40px 32px;
            max-width: 400px;
            width: calc(100% - 32px);
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .wz-success-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #dcfce7;
            color: #22c55e;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wz-success-title {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #2C3E50;
        }

        .wz-success-desc {
            font-size: 14px;
            color: #64748b;
            margin-top: 8px;
            line-height: 1.6;
        }

        /* ===== FOOTER NOTE ===== */
        .wizard-footer-note {
            text-align: center;
            padding: 24px 0 0;
        }

        .wizard-footer-note p {
            font-size: 12px;
            color: #94a3b8;
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .wizard-sections-wrapper>.wizard-section {
            animation: fadeInUp 0.35s ease-out forwards;
        }

        .wizard-sections-wrapper>.wizard-section:nth-child(2) {
            animation-delay: 0.05s;
        }

        .wizard-sections-wrapper>.wizard-section:nth-child(3) {
            animation-delay: 0.1s;
        }

        .wizard-sections-wrapper>.wizard-divider+.wizard-section {
            animation-delay: 0.15s;
        }
    </style>