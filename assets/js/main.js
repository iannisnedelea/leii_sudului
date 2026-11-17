// LEII Sudului Craiova – interacțiuni în browser (fără biblioteci externe)

// 1. Meniu mobil
const menuBtn = document.querySelector('.menu-btn');
const nav = document.getElementById('nav');
if (menuBtn && nav) {
    menuBtn.addEventListener('click', () => {
        const open = nav.classList.toggle('open');
        menuBtn.setAttribute('aria-expanded', String(open));
    });
}

// 2. Filtrare meciuri după grupa de vârstă (FR-04)
document.querySelectorAll('.filter .chip').forEach(chip => {
    chip.addEventListener('click', () => {
        const g = chip.dataset.filter;
        document.querySelectorAll('.filter .chip').forEach(c => c.setAttribute('aria-pressed', String(c === chip)));
        document.querySelectorAll('table.matches tbody').forEach(tbody => {
            let visible = 0;
            tbody.querySelectorAll('tr[data-group]').forEach(tr => {
                const show = g === 'all' || tr.dataset.group === g;
                tr.hidden = !show;
                if (show) visible++;
            });
            tbody.querySelector('tr.none').hidden = visible > 0;
        });
    });
});

// 3. Validare client pentru formularul de contact (UC-02) – serverul validează oricum din nou
const form = document.querySelector('form[data-validate]');
if (form) {
    const rules = {
        name:  v => v.trim() !== '' || 'Introduceți numele.',
        email: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) || 'Adresa de email nu este validă.',
        text:  v => v.trim().length >= 10 || 'Mesajul trebuie să aibă cel puțin 10 caractere.',
    };
    form.addEventListener('submit', ev => {
        let first = null;
        for (const [name, rule] of Object.entries(rules)) {
            const input = form.elements[name];
            const res = rule(input.value);
            let msg = form.querySelector('#err-' + name);
            if (res === true) {
                input.removeAttribute('aria-invalid');
                msg?.remove();
            } else {
                input.setAttribute('aria-invalid', 'true');
                input.setAttribute('aria-describedby', 'err-' + name);
                if (!msg) {
                    msg = document.createElement('p');
                    msg.className = 'err';
                    msg.id = 'err-' + name;
                    input.after(msg);
                }
                msg.textContent = res;
                first ??= input;
            }
        }
        if (first) { ev.preventDefault(); first.focus(); }
    });
}

// 4. Confirmare înainte de ștergere
document.querySelectorAll('form[data-confirm]').forEach(f => {
    f.addEventListener('submit', ev => { if (!confirm(f.dataset.confirm)) ev.preventDefault(); });
});
