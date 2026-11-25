@auth
    @php($emailVerificationRequired = auth()->user()->email_verified_at === null && ! auth()->user()->hasRole('Superadmin'))
    @if ($emailVerificationRequired)
        <div id="email-verification-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/50 p-4"
            role="dialog" aria-modal="true" aria-labelledby="email-verification-title">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-5">
                    <p class="text-xs font-bold uppercase tracking-widest text-primary">Verifikasi Email</p>
                    <h2 id="email-verification-title" class="mt-1 text-xl font-bold text-secondary">Aktifkan fitur Penjual</h2>
                    <p class="mt-2 text-sm text-slate-500">Masukkan email dan kode OTP 6 digit untuk melanjutkan.</p>
                </div>

                <div id="email-verification-error" class="mb-4 hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>

                <div id="email-verification-email-step">
                    <label for="email-verification-email" class="mb-2 block text-sm font-semibold text-slate-700">Alamat Email</label>
                    <input id="email-verification-email" type="email" value="{{ auth()->user()->email }}"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <button id="email-verification-send" type="button"
                        class="mt-4 w-full rounded-lg bg-primary px-4 py-3 text-sm font-bold text-white hover:bg-primary-hover">
                        Kirim Kode OTP
                    </button>
                </div>

                <div id="email-verification-code-step" class="hidden">
                    <label for="email-verification-code" class="mb-2 block text-sm font-semibold text-slate-700">Kode OTP</label>
                    <input id="email-verification-code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                        class="w-full rounded-lg border border-slate-300 px-3 py-3 text-center text-xl font-bold tracking-[0.45em] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <button id="email-verification-submit" type="button"
                        class="mt-4 w-full rounded-lg bg-primary px-4 py-3 text-sm font-bold text-white hover:bg-primary-hover">
                        Verifikasi Email
                    </button>
                    <button id="email-verification-resend" type="button" disabled
                        class="mt-3 w-full text-sm font-semibold text-primary disabled:text-slate-400">
                        Kirim ulang dalam 60 detik
                    </button>
                </div>
            </div>
        </div>

        <script>
            window.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('email-verification-modal');
                const emailInput = document.getElementById('email-verification-email');
                const codeInput = document.getElementById('email-verification-code');
                const emailStep = document.getElementById('email-verification-email-step');
                const codeStep = document.getElementById('email-verification-code-step');
                const sendButton = document.getElementById('email-verification-send');
                const submitButton = document.getElementById('email-verification-submit');
                const resendButton = document.getElementById('email-verification-resend');
                const errorBox = document.getElementById('email-verification-error');
                let intendedUrl = @json(session('email_verification_intended_url') ?: route('seller.listing'));
                let resendSeconds = 0;
                let resendTimer = null;

                const openModal = (url = intendedUrl) => {
                    intendedUrl = url || intendedUrl;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                };

                const showError = (message) => {
                    errorBox.textContent = message;
                    errorBox.classList.remove('hidden');
                };

                const clearError = () => errorBox.classList.add('hidden');

                const startResendCooldown = (seconds) => {
                    resendSeconds = seconds;
                    resendButton.disabled = true;
                    clearInterval(resendTimer);
                    resendTimer = setInterval(() => {
                        resendSeconds -= 1;
                        if (resendSeconds <= 0) {
                            clearInterval(resendTimer);
                            resendButton.disabled = false;
                            resendButton.textContent = 'Kirim ulang kode';
                            return;
                        }
                        resendButton.textContent = `Kirim ulang dalam ${resendSeconds} detik`;
                    }, 1000);
                };

                const sendOtp = async () => {
                    clearError();
                    sendButton.disabled = true;
                    resendButton.disabled = true;
                    try {
                        const response = await fetch(@json(route('email-verification.send')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ email: emailInput.value }),
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            showError(data.message || 'Kode OTP gagal dikirim.');
                            if (data.retry_after) {
                                startResendCooldown(data.retry_after);
                            }
                            return;
                        }
                        emailStep.classList.add('hidden');
                        codeStep.classList.remove('hidden');
                        codeInput.focus();
                        startResendCooldown(data.resend_in || 60);
                    } catch (error) {
                        showError('Tidak dapat menghubungi server.');
                    } finally {
                        sendButton.disabled = false;
                    }
                };

                sendButton.addEventListener('click', sendOtp);
                resendButton.addEventListener('click', sendOtp);

                submitButton.addEventListener('click', async () => {
                    clearError();
                    submitButton.disabled = true;
                    try {
                        const response = await fetch(@json(route('email-verification.verify')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ code: codeInput.value }),
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            showError(data.message || 'Verifikasi gagal.');
                            if (data.attempts_remaining === 0) {
                                emailStep.classList.remove('hidden');
                                codeStep.classList.add('hidden');
                            }
                            return;
                        }
                        window.location.href = intendedUrl;
                    } catch (error) {
                        showError('Tidak dapat menghubungi server.');
                    } finally {
                        submitButton.disabled = false;
                    }
                });

                document.querySelectorAll('[data-seller-feature]').forEach((link) => {
                    link.addEventListener('click', (event) => {
                        event.preventDefault();
                        openModal(link.href);
                    });
                });

                @if (session('email_verification_required'))
                    openModal();
                @endif
            });
        </script>
    @endif
@endauth
