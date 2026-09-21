/**
 * Auth pages — OTP input and live registration validation
 */

document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-auth-page]');
    if (!page) return;

    page.querySelectorAll('[data-otp-input]').forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(0, 6);
        });
    });

    const registerForm = page.querySelector('[data-register-form]');
    if (registerForm) {
        bindRegisterValidation(registerForm);
    }
});

function bindRegisterValidation(form) {
    const checkUrl = form.getAttribute('data-check-url') || '';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const nameInput = form.querySelector('#register-name');
    const emailInput = form.querySelector('#register-email');
    const mobileInput = form.querySelector('#register-mobile');
    const passwordInput = form.querySelector('#register-password');
    const confirmInput = form.querySelector('#register-password-confirmation');
    const termsInput = form.querySelector('input[name="terms"]');
    const uniqueControllers = {};
    const uniqueTimers = {};

    function wrapFor(input) {
        return input ? input.closest('[data-field-wrap]') : null;
    }

    function rawValue(input) {
        if (!input) {
            return '';
        }
        return input.type === 'checkbox' ? input.checked : String(input.value || '');
    }

    function trimmed(input) {
        return String(rawValue(input) || '').trim();
    }

    function isEmpty(input) {
        if (!input) {
            return true;
        }
        if (input.type === 'checkbox') {
            return !input.checked;
        }
        return trimmed(input) === '';
    }

    function setFieldError(input, message) {
        const wrap = wrapFor(input);
        if (!wrap) {
            return;
        }
        const error = wrap.querySelector('[data-field-error]');
        if (message) {
            wrap.classList.add('is-invalid');
            if (error) {
                error.textContent = message;
                error.hidden = false;
            }
            input.setAttribute('aria-invalid', 'true');
            return;
        }
        wrap.classList.remove('is-invalid');
        if (error) {
            error.textContent = '';
            error.hidden = true;
        }
        input.removeAttribute('aria-invalid');
    }

    function fieldError(input) {
        if (input === nameInput) {
            const value = trimmed(nameInput);
            if (value === '') {
                return 'Please enter your full name.';
            }
            return value.length > 120 ? 'Name cannot be longer than 120 characters.' : '';
        }
        if (input === emailInput) {
            const value = trimmed(emailInput).toLowerCase();
            if (value === '') {
                return 'Please enter your email address.';
            }
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) ? '' : 'Enter a valid email address.';
        }
        if (input === mobileInput) {
            const value = String(mobileInput.value || '').replace(/\D/g, '');
            if (value === '') {
                return 'Please enter your mobile number.';
            }
            return /^[6-9]\d{9}$/.test(value) ? '' : 'Enter a valid 10-digit Indian mobile number.';
        }
        if (input === passwordInput) {
            const value = String(passwordInput.value || '');
            if (value === '') {
                return 'Please create a password.';
            }
            return value.length < 8 ? 'Password must be at least 8 characters.' : '';
        }
        if (input === confirmInput) {
            const value = String(confirmInput.value || '');
            if (value === '') {
                return 'Please confirm your password.';
            }
            return value !== String(passwordInput?.value || '') ? 'Password confirmation does not match.' : '';
        }
        if (input === termsInput) {
            return termsInput.checked ? '' : 'Please agree to the Terms & Conditions and Privacy Policy.';
        }
        return '';
    }

    function validate(input) {
        const message = fieldError(input);
        setFieldError(input, message);
        return message === '';
    }

    function checkUnique(field, input) {
        if (!checkUrl || !csrf || fieldError(input)) {
            return;
        }

        if (uniqueTimers[field]) {
            clearTimeout(uniqueTimers[field]);
        }

        uniqueTimers[field] = setTimeout(() => {
            if (uniqueControllers[field]) {
                uniqueControllers[field].abort();
            }
            const controller = new AbortController();
            uniqueControllers[field] = controller;

            fetch(checkUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    field: field,
                    value: field === 'mobile'
                        ? String(input.value || '').replace(/\D/g, '')
                        : trimmed(input).toLowerCase(),
                }),
                signal: controller.signal,
            })
                .then((response) => (response.ok ? response.json() : null))
                .then((data) => {
                    if (!data || fieldError(input)) {
                        return;
                    }
                    if (data.taken && data.message) {
                        setFieldError(input, data.message);
                    }
                })
                .catch(() => {});
        }, 280);
    }

    function handleBlur(input, uniqueField) {
        input.dataset.touched = '1';
        if (validate(input) && uniqueField) {
            checkUnique(uniqueField, input);
        }
    }

    function handleInput(input, uniqueField) {
        if (input.dataset.touched !== '1' && isEmpty(input)) {
            setFieldError(input, '');
            return;
        }
        if (validate(input) && uniqueField) {
            checkUnique(uniqueField, input);
        }
    }

    if (nameInput) {
        nameInput.addEventListener('blur', () => handleBlur(nameInput));
        nameInput.addEventListener('input', () => handleInput(nameInput));
    }

    if (emailInput) {
        emailInput.addEventListener('blur', () => handleBlur(emailInput, 'email'));
        emailInput.addEventListener('input', () => handleInput(emailInput, 'email'));
    }

    if (mobileInput) {
        mobileInput.addEventListener('blur', () => handleBlur(mobileInput, 'mobile'));
        mobileInput.addEventListener('input', () => {
            mobileInput.value = mobileInput.value.replace(/\D/g, '').slice(0, 10);
            handleInput(mobileInput, 'mobile');
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('blur', () => {
            handleBlur(passwordInput);
            if (confirmInput && (confirmInput.dataset.touched === '1' || !isEmpty(confirmInput))) {
                validate(confirmInput);
            }
        });
        passwordInput.addEventListener('input', () => {
            handleInput(passwordInput);
            if (confirmInput && (confirmInput.dataset.touched === '1' || !isEmpty(confirmInput))) {
                validate(confirmInput);
            }
        });
    }

    if (confirmInput) {
        confirmInput.addEventListener('blur', () => handleBlur(confirmInput));
        confirmInput.addEventListener('input', () => handleInput(confirmInput));
    }

    if (termsInput) {
        termsInput.addEventListener('change', () => {
            termsInput.dataset.touched = '1';
            validate(termsInput);
        });
        termsInput.addEventListener('blur', () => handleBlur(termsInput));
    }

    form.addEventListener('submit', (event) => {
        let firstInvalid = null;
        [nameInput, emailInput, mobileInput, passwordInput, confirmInput, termsInput].forEach((input) => {
            if (!input) {
                return;
            }
            input.dataset.touched = '1';
            if (!validate(input) && !firstInvalid) {
                firstInvalid = input;
            }
        });
        if (firstInvalid) {
            event.preventDefault();
            firstInvalid.focus();
        }
    });
}
