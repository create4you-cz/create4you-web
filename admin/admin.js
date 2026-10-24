// Náhledy obrázků před nahráním
document.querySelectorAll('input[type=file][data-preview]').forEach((input) => {
  const box = document.getElementById(input.dataset.preview);
  const drop = input.closest('.drop');
  input.addEventListener('change', () => {
    box.innerHTML = '';
    [...input.files].forEach((f) => {
      if (!f.type.startsWith('image/')) return;
      const img = document.createElement('img');
      img.src = URL.createObjectURL(f);
      box.appendChild(img);
    });
  });
  if (drop) {
    ['dragenter', 'dragover'].forEach((ev) => drop.addEventListener(ev, () => drop.classList.add('over')));
    ['dragleave', 'drop'].forEach((ev) => drop.addEventListener(ev, () => drop.classList.remove('over')));
  }
});

// Řazení řádků tabulky přetažením (uloží se automaticky)
document.querySelectorAll('table.sortable tbody').forEach((tbody) => {
  const table = tbody.closest('table');
  let dragged = null;
  tbody.addEventListener('dragstart', (e) => {
    dragged = e.target.closest('tr');
    dragged.classList.add('dragging');
  });
  tbody.addEventListener('dragend', () => {
    dragged.classList.remove('dragging');
    dragged = null;
    const body = new FormData();
    body.append('action', 'order');
    body.append('csrf', table.dataset.csrf);
    body.append('ids', [...tbody.rows].map((r) => r.dataset.id).join(','));
    fetch(table.dataset.endpoint, { method: 'POST', body });
  });
  tbody.addEventListener('dragover', (e) => {
    e.preventDefault();
    const over = e.target.closest('tr');
    if (!dragged || !over || over === dragged) return;
    const rect = over.getBoundingClientRect();
    tbody.insertBefore(dragged, e.clientY > rect.top + rect.height / 2 ? over.nextSibling : over);
  });
});
