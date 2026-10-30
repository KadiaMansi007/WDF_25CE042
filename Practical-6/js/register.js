'use strict';
 
const PATTERNS = {
    name: /^[A-Za-z]+(?:[ .'-][A-Za-z]+)*$/,
    email: /^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/,
    mobile: /^[6-9]\d{9}$/,
    password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/
};
 
const form = document.getElementById('register-form');
const result = document.getElementById('form-result');
let captchaCode = '';
 
const validators = {
    fullname(value) {
        value = value.trim();
        if (!value) return 'Full name is required.';
        if (value.length < 3) return 'Name must be at least 3 characters.';
        if (!PATTERNS.name.test(value)) return 'Use letters only (spaces, . \' and - are allowed).';
        return '';
    },
    email(value) {
        value = value.trim();
        if (!value) return 'Email address is required.';
        if (!PATTERNS.email.test(value)) return 'Enter a valid email, e.g. name@example.com.';
        return '';
    },
    mobile(value) {
        value = value.trim();
        if (!value) return 'Mobile number is required.';
        if (!/^\d+$/.test(value)) return 'Mobile number must contain digits only.';
        if (!PATTERNS.mobile.test(value)) return 'Enter a 10-digit number starting with 6, 7, 8 or 9.';
        return '';
    },
    course(value) {
        return value ? '' : 'Please select your course.';
    },
    year(value) {
        return value ? '' : 'Please select your year.';
    },
    gender() {
        return form.querySelector('input[name="gender"]:checked') ? '' : 'Please select your gender.';
    },
    password(value) {
        if (!value) return 'Password is required.';
        if (value.length < 8) return 'Password must be at least 8 characters.';
        if (!PATTERNS.password.test(value)) return 'Include an uppercase letter, a lowercase letter, a number and a symbol.';
        return '';
    },
    'confirm-password'(value) {
        if (!value) return 'Please confirm your password.';
        if (value !== form.elements.password.value) return 'Passwords do not match.';
        return '';
    },
    captcha(value) {
        if (!value.trim()) return 'Enter the verification code.';
        if (value.trim().toUpperCase() !== captchaCode) return 'Code does not match. Try again or get a new code.';
        return '';
    },
    terms() {
        return form.elements.terms.checked ? '' : 'You must accept the terms and conditions.';
    }
};
 
function validateField(name) {
    const control = name === 'gender' ? form.querySelector('input[name="gender"]') : form.elements[name];
    const value = name === 'gender' || name === 'terms' ? '' : control.value;
    const message = validators[name](value);
    const errorBox = document.getElementById(name + '-error');
 
    errorBox.textContent = message;
    if (name !== 'gender' && name !== 'terms') {
        control.classList.toggle('is-invalid', Boolean(message));
        control.classList.toggle('is-valid', !message);
    }
    if (name === 'gender') {
        form.querySelectorAll('input[name="gender"]').forEach(function (radio) {
            radio.setAttribute('aria-invalid', String(Boolean(message)));
        });
    } else {
        control.setAttribute('aria-invalid', String(Boolean(message)));
    }
    return !message;
}
 
function updateStrength() {
    const value = form.elements.password.value;
    const bar = document.getElementById('strength-bar');
    const text = document.getElementById('strength-text');
    let score = 0;
    if (value.length >= 8) score++;
    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
    if (/\d/.test(value)) score++;
    if (/[^A-Za-z0-9]/.test(value)) score++;
    if (value.length >= 12) score++;
 
    bar.className = 'strength-bar';
    if (!value) {
        text.textContent = '–';
    } else if (score <= 2) {
        bar.classList.add('weak');
        text.textContent = 'Weak';
    } else if (score === 3) {
        bar.classList.add('medium');
        text.textContent = 'Medium';
    } else {
        bar.classList.add('strong');
        text.textContent = 'Strong';
    }
}
 
function drawCaptcha() {
    const canvas = document.getElementById('captcha-canvas');
    const ctx = canvas.getContext('2d');
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    captchaCode = '';
    for (let i = 0; i < 5; i++) {
        captchaCode += chars.charAt(Math.floor(Math.random() * chars.length));
    }
 
    ctx.fillStyle = '#eef3fb';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
 
    for (let i = 0; i < 6; i++) {
        ctx.strokeStyle = 'hsl(' + Math.random() * 360 + ', 50%, 60%)';
        ctx.beginPath();
        ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
        ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
        ctx.stroke();
    }
 
    ctx.font = 'bold 30px Arial';
    ctx.textBaseline = 'middle';
    for (let i = 0; i < captchaCode.length; i++) {
        ctx.save();
        ctx.translate(18 + i * 30, canvas.height / 2 + (Math.random() * 10 - 5));
        ctx.rotate((Math.random() - 0.5) * 0.6);
        ctx.fillStyle = '#17365d';
        ctx.fillText(captchaCode[i], -10, 0);
        ctx.restore();
    }
 
    for (let i = 0; i < 30; i++) {
        ctx.fillStyle = 'rgba(23, 54, 93, 0.35)';
        ctx.fillRect(Math.random() * canvas.width, Math.random() * canvas.height, 2, 2);
    }
}
 
const fieldNames = Object.keys(validators);
 
fieldNames.forEach(function (name) {
    if (name === 'gender') {
        form.querySelectorAll('input[name="gender"]').forEach(function (radio) {
            radio.addEventListener('change', function () { validateField('gender'); });
        });
        return;
    }
    const control = form.elements[name];
 
    control.addEventListener(control.type === 'checkbox' || control.tagName === 'SELECT' ? 'change' : 'input',
        function () {
            validateField(name);
            if (name === 'password') {
                updateStrength();
                if (form.elements['confirm-password'].value) validateField('confirm-password');
            }
        });
    control.addEventListener('blur', function () { validateField(name); });
});
 
form.elements.mobile.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
});
 
document.getElementById('captcha-refresh').addEventListener('click', function () {
    drawCaptcha();
    form.elements.captcha.value = '';
    form.elements.captcha.focus();
});
 
form.addEventListener('submit', function (event) {
    event.preventDefault();
    let firstInvalid = null;
 
    fieldNames.forEach(function (name) {
        const ok = validateField(name);
        if (!ok && !firstInvalid) {
            firstInvalid = name === 'gender' ? form.querySelector('input[name="gender"]') : form.elements[name];
        }
    });
 
    if (firstInvalid) {
        result.hidden = true;
        firstInvalid.focus();
        return;
    }
 
    result.className = 'message success';
    result.innerHTML = '<strong>Registration successful!</strong> Your details were validated.' +
        '<dl><dt>Name</dt><dd></dd><dt>Email</dt><dd></dd><dt>Mobile</dt><dd></dd><dt>Course</dt><dd></dd></dl>';
    const values = result.querySelectorAll('dd');
    values[0].textContent = form.elements.fullname.value.trim();
    values[1].textContent = form.elements.email.value.trim();
    values[2].textContent = form.elements.mobile.value;
    values[3].textContent = form.elements.course.value + ', Year ' + form.elements.year.value;
    result.hidden = false;
    result.focus();
});
 
form.addEventListener('reset', function () {
    setTimeout(function () {
        form.querySelectorAll('.error-msg').forEach(function (box) { box.textContent = ''; });
        form.querySelectorAll('.is-valid, .is-invalid').forEach(function (el) {
            el.classList.remove('is-valid', 'is-invalid');
        });
        result.hidden = true;
        updateStrength();
        drawCaptcha();
    }, 0);
});
 
drawCaptcha();