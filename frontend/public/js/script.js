// ═══════════════════════════════════════════
// Faculty Evaluation System — script.js
// Shared JS: progress bar, autosave, validation
// ═══════════════════════════════════════════

// ── Utilities ──────────────────────────────
function debounce(fn, delay) {
    let t;
    return function(...args) { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
}

function clamp(n, min, max) { return Math.min(Math.max(n, min), max); }

// ── Progress Bar ───────────────────────────
// FIX: use document.getElementById instead of form.querySelector
// so elements outside the <form> tag are found correctly.

function getFieldValue(field) {
    if (field.type === 'checkbox' || field.type === 'radio') return field.checked ? field.value : '';
    return field.value || '';
}

function calculateFormProgress(form) {
    const fields = Array.from(form.querySelectorAll('input, select, textarea')).filter(f => f.required && !f.disabled);
    if (!fields.length) return 0;
    const filled = fields.reduce((n, f) => n + (getFieldValue(f).toString().trim() !== '' ? 1 : 0), 0);
    return Math.round((filled / fields.length) * 100);
}

function updateProgressUI(form) {
    const percent = calculateFormProgress(form);
    // Use document-level lookup so the progress banner can be outside <form>
    const label = document.getElementById('progressPercent');
    const fill  = document.getElementById('progressFill');
    if (label) label.textContent = `${percent}%`;
    if (fill)  fill.style.width  = `${clamp(percent, 0, 100)}%`;
}

function setProgressComplete() {
    const label = document.getElementById('progressPercent');
    const fill  = document.getElementById('progressFill');
    if (label) label.textContent = '100%';
    if (fill)  fill.style.width  = '100%';
}

// ── Auto-save ──────────────────────────────
function loadAutoSave(form) {
    const key = form.dataset.autosaveId;
    if (!key) return;
    try {
        const data = JSON.parse(localStorage.getItem(`autosave:${key}`) || 'null');
        if (!data) return;
        Object.entries(data).forEach(([name, value]) => {
            const field = form.querySelector(`[name="${name}"]`);
            if (!field) return;
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = value === field.value || value === true;
            } else {
                field.value = value;
            }
        });
    } catch(e) { console.warn('AutoSave load failed', e); }
}

function saveAutoSave(form) {
    const key = form.dataset.autosaveId;
    if (!key) return;
    const data = {};
    form.querySelectorAll('input[name], select[name], textarea[name]').forEach(f => {
        const n = f.getAttribute('name');
        if (!n) return;
        data[n] = (f.type === 'checkbox' || f.type === 'radio') ? (f.checked ? f.value : '') : f.value;
    });
    localStorage.setItem(`autosave:${key}`, JSON.stringify(data));
}

function clearAutoSave(form) {
    const key = form.dataset.autosaveId;
    if (key) localStorage.removeItem(`autosave:${key}`);
}

function setupAutoSaveAndProgress() {
    document.querySelectorAll('form[data-autosave-id]').forEach(form => {
        loadAutoSave(form);
        updateProgressUI(form);
        const update = debounce(() => { saveAutoSave(form); updateProgressUI(form); }, 200);
        form.addEventListener('input', update);
        form.addEventListener('change', update);
        form.addEventListener('submit', function() {
            clearAutoSave(form);
            setTimeout(() => {
                form.reset();
                updateProgressUI(form);
            }, 0);
        });
    });
}

window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        document.querySelectorAll('form[data-autosave-id]').forEach(form => {
            form.reset();
            clearAutoSave(form);
            updateProgressUI(form);
        });
    }
});

// ── Faculty ID Validation ──────────────────
function formatFacultyId(value) {
    const d = value.replace(/\D/g, '').slice(0, 11);
    const g = [];
    if (d.length > 0) g.push(d.slice(0, Math.min(4, d.length)));
    if (d.length > 4) g.push(d.slice(4, Math.min(6, d.length)));
    if (d.length > 6) g.push(d.slice(6));
    return g.join('-');
}

function validateFacultyId(input, errorEl) {
    const val     = input.value;
    const isValid = /^\d{4}-\d{2}-\d{5}$/.test(val);
    if (!val.trim()) {
        showError(input, errorEl, 'Faculty ID is required (format: 0000-00-00000).');
        return false;
    }
    if (!isValid) {
        showError(input, errorEl, 'Invalid format. Use: 0000-00-00000.');
        return false;
    }
    clearError(input, errorEl);
    return true;
}

function showError(input, errorEl, msg) {
    if (errorEl) { errorEl.textContent = msg; errorEl.classList.add('show'); }
    input.classList.add('input-error');
    input.setAttribute('aria-invalid', 'true');
    input.setCustomValidity(msg);
}

function clearError(input, errorEl) {
    if (errorEl) { errorEl.textContent = ''; errorEl.classList.remove('show'); }
    input.classList.remove('input-error');
    input.removeAttribute('aria-invalid');
    input.setCustomValidity('');
}

function setupFacultyIdValidation() {
    const inputs = document.querySelectorAll('.faculty-id-input');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            input.value = formatFacultyId(input.value);
            if (input.id === 'faculty_code') {
                const errorEl = document.getElementById('faculty_code_error');
                if (input.value.length > 0) validateFacultyId(input, errorEl);
            }
        });
    });

    const facultyCode = document.getElementById('faculty_code');
    if (facultyCode) {
        const errorEl = document.getElementById('faculty_code_error');
        facultyCode.addEventListener('blur', () => validateFacultyId(facultyCode, errorEl));

        const form = facultyCode.closest('form');
        if (form) {
            form.addEventListener('submit', e => {
                if (!validateFacultyId(facultyCode, errorEl)) { e.preventDefault(); facultyCode.focus(); }
            });
        }
    }
}

// ── Faculty Member Auto-fill ──────────────
function setupMemberAutoFill() {
    const member = document.getElementById('member');
    const facultyCode = document.getElementById('faculty_code');
    const college = document.getElementById('college');
    if (!member || !facultyCode || !college) return;

    const placeholder = member.querySelector('option[value=""]');

    function updateMemberOptions() {
        const selectedCollege = college.value;
        member.disabled = !selectedCollege;
        member.value = '';
        facultyCode.value = '';

        Array.from(member.options).forEach(option => {
            if (!option.value) return;
            option.hidden = selectedCollege ? option.dataset.college !== selectedCollege : true;
        });

        if (placeholder) placeholder.textContent = selectedCollege ? 'Select faculty member…' : 'Select department first…';
        if (placeholder) placeholder.selected = true;
    }

    college.addEventListener('change', updateMemberOptions);
    updateMemberOptions();

    member.addEventListener('change', () => {
        const selected = member.options[member.selectedIndex];
        const id = selected ? selected.value : '';
        const collegeValue = selected ? selected.dataset.college : '';

        facultyCode.value = id;
        if (college && collegeValue) {
            const match = Array.from(college.options).find(o => o.value === collegeValue);
            if (match) match.selected = true;
        }

        const errorEl = document.getElementById('faculty_code_error');
        if (id && errorEl) {
            clearError(facultyCode, errorEl);
        }
    });
}

// ── Score Validation (1-5 only) ───────────
function validateScore(input) {
    let value = input.value.trim();
    
    // If empty, clear it
    if (value === '') {
        input.value = '';
        return;
    }
    
    // Convert to number
    let num = parseInt(value, 10);
    
    // Ensure it's in range 1-5, restrict to single digit
    if (num < 1 || num > 5 || value.length > 1) {
        input.value = '';
        return;
    }
    
    input.value = num;
}

// ── Form Submit Confirm ────────────────────
function setupConfirmSubmits() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            updateProgressUI(form);
            if (!form.reportValidity()) { e.preventDefault(); return; }
            const idInput = form.querySelector('input[name="faculty_code"]');
            const errEl   = document.getElementById('faculty_code_error');
            if (idInput && !validateFacultyId(idInput, errEl)) { e.preventDefault(); return; }
            setProgressComplete();
            if (!confirm('Are you sure you want to submit this evaluation?')) { e.preventDefault(); }
        });
    });
}

// ── Faculty List Text Search (fallback) ───
function setupFacultySearch() {
    const input = document.getElementById('facultySearch');
    const list  = document.getElementById('facultyList');
    if (!input || !list) return;
    input.addEventListener('input', debounce(() => {
        const q = input.value.toLowerCase();
        list.querySelectorAll('li').forEach(li => {
            li.style.display = li.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }, 250));
}

// ── Init ──────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    setupFacultySearch();
    setupFacultyIdValidation();
    setupMemberAutoFill();
    setupAutoSaveAndProgress();
    setupConfirmSubmits();
});
