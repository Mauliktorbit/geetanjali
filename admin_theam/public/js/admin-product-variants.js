document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('generate-variants');
    if (!btn) return;

    function cartesian(groups) {
        return groups.reduce((acc, group) => {
            const next = [];
            acc.forEach(prefix => group.forEach(item => next.push(prefix.concat([item]))));
            return next;
        }, [[]]);
    }

    btn.addEventListener('click', function () {
        const groups = [];
        document.querySelectorAll('.attr-values').forEach(select => {
            const selected = Array.from(select.selectedOptions).map(o => ({ id: o.value, label: o.textContent.trim() }));
            if (selected.length) groups.push(selected);
        });
        if (!groups.length) {
            alert('Select at least one attribute value.');
            return;
        }

        const combos = cartesian(groups);
        const tbody = document.querySelector('#variants-table tbody');
        tbody.innerHTML = '';
        const matrixInputs = document.getElementById('attr-matrix-hidden') || (() => {
            const el = document.createElement('div');
            el.id = 'attr-matrix-hidden';
            btn.parentNode.insertBefore(el, btn.nextSibling);
            return el;
        })();
        matrixInputs.innerHTML = '';

        groups.forEach((group, gi) => {
            group.forEach(item => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `attribute_matrix[${gi}][]`;
                input.value = item.id;
                matrixInputs.appendChild(input);
            });
        });

        combos.forEach((combo, i) => {
            const name = combo.map(c => c.label).join(' / ');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input type="text" name="variants[${i}][name]" class="form-control" value="${name}">
                    ${combo.map(c => `<input type="hidden" name="variants[${i}][attribute_value_ids][]" value="${c.id}">`).join('')}
                </td>
                <td><input type="text" name="variants[${i}][sku]" class="form-control" value=""></td>
                <td><input type="number" step="0.01" name="variants[${i}][price]" class="form-control" value=""></td>
                <td><input type="number" step="0.01" name="variants[${i}][sale_price]" class="form-control" value=""></td>
                <td><input type="number" step="0.01" name="variants[${i}][cost]" class="form-control" value=""></td>
                <td><input type="number" name="variants[${i}][stock]" class="form-control" value="0"></td>
                <td><input type="checkbox" name="variants[${i}][is_active]" value="1" checked></td>`;
            tbody.appendChild(tr);
        });
    });
});
