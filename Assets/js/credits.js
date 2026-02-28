document.addEventListener('DOMContentLoaded', function () {
  const url = baseUrl + 'Assets/credits.json';
  const container = document.getElementById('creditsContent');

  function buildCredits(list) {
    container.innerHTML = '';
    // Intro title
    const title = document.createElement('div');
    title.className = 'credits-item credits-role';
    title.textContent = list.title || 'Créditos';
    container.appendChild(title);

    if (Array.isArray(list.items)) {
      list.items.forEach(group => {
        if (group.role) {
          const roleEl = document.createElement('div');
          roleEl.className = 'credits-item credits-role';
          roleEl.textContent = group.role;
          container.appendChild(roleEl);
        }
        if (Array.isArray(group.names)) {
          group.names.forEach(name => {
            const nameEl = document.createElement('div');
            nameEl.className = 'credits-item credits-name';
            nameEl.textContent = name;
            container.appendChild(nameEl);
          });
        }
        // small gap
        const gap = document.createElement('div');
        gap.className = 'credits-item';
        gap.innerHTML = '&nbsp;';
        container.appendChild(gap);
      });
    }

    // duplicate content so scrolling is smooth (optional)
    const clone = container.cloneNode(true);
    clone.id = 'creditsClone';
    container.parentNode.appendChild(clone);

    // compute animation duration based on total height
    setTimeout(() => {
      const parent = container.parentNode;
      const contentHeight = container.offsetHeight + (clone ? clone.offsetHeight : 0);
      const viewportHeight = parent.offsetHeight;
      // pixels per second (speed): adjust to taste
      const speed = 30; // px per second
      const duration = Math.max(10, Math.ceil((contentHeight + viewportHeight) / speed));
      // apply animation duration
      container.style.animationDuration = duration + 's';
      if (clone) clone.style.animationDuration = duration + 's';
    }, 50);
  }

  fetch(url)
    .then(res => res.json())
    .then(json => buildCredits(json))
    .catch(err => {
      container.innerHTML = '<div class="credits-item">No se pudo cargar créditos.</div>';
      console.error('Error cargando credits.json', err);
    });
});
