(function () {
  'use strict';
  const dialog = document.getElementById('wfGalleryLightbox');
  const dataNode = document.getElementById('wfGalleryData');
  if (!dialog || !dataNode) return;
  let items = [];
  try { items = JSON.parse(dataNode.textContent || '[]'); } catch (_) { return; }
  if (!Array.isArray(items) || !items.length) return;

  const image = document.getElementById('wfGalleryImage');
  const title = document.getElementById('wfGalleryTitle');
  const description = document.getElementById('wfGalleryDescription');
  const counter = document.getElementById('wfGalleryCounter');
  const photoWrap = dialog.querySelector('[data-gallery-photo-wrap]');
  let index = 0;
  let zoomed = false;
  let startX = 0;

  function render(nextIndex) {
    index = (nextIndex + items.length) % items.length;
    const item = items[index] || {};
    image.src = String(item.image || '');
    image.alt = String(item.title || 'Gallery photo');
    title.textContent = String(item.title || 'Gallery photo');
    description.textContent = String(item.description || '');
    description.hidden = !String(item.description || '').trim();
    counter.textContent = (index + 1) + ' / ' + items.length;
    zoomed = false;
    photoWrap.classList.remove('is-zoomed');
  }
  function openAt(nextIndex) {
    render(nextIndex);
    if (typeof dialog.showModal === 'function') dialog.showModal(); else dialog.setAttribute('open', '');
    document.documentElement.classList.add('wf-gallery-open');
  }
  function close() {
    if (dialog.open && typeof dialog.close === 'function') dialog.close(); else dialog.removeAttribute('open');
    document.documentElement.classList.remove('wf-gallery-open');
  }
  function move(amount) { render(index + amount); }
  function toggleZoom() {
    zoomed = !zoomed;
    photoWrap.classList.toggle('is-zoomed', zoomed);
  }

  document.querySelectorAll('[data-gallery-open]').forEach(button => button.addEventListener('click', () => openAt(Number(button.dataset.galleryOpen || 0))));
  dialog.querySelectorAll('[data-gallery-close]').forEach(button => button.addEventListener('click', close));
  dialog.querySelector('[data-gallery-prev]')?.addEventListener('click', () => move(-1));
  dialog.querySelector('[data-gallery-next]')?.addEventListener('click', () => move(1));
  dialog.querySelector('[data-gallery-zoom]')?.addEventListener('click', toggleZoom);
  image?.addEventListener('dblclick', toggleZoom);

  dialog.addEventListener('click', event => { if (event.target === dialog) close(); });
  dialog.addEventListener('cancel', event => { event.preventDefault(); close(); });
  dialog.addEventListener('close', () => document.documentElement.classList.remove('wf-gallery-open'));
  document.addEventListener('keydown', event => {
    if (!dialog.open) return;
    if (event.key === 'ArrowLeft') move(-1);
    if (event.key === 'ArrowRight') move(1);
    if (event.key === 'Escape') close();
  });
  dialog.querySelector('[data-gallery-stage]')?.addEventListener('touchstart', event => { startX = event.changedTouches[0]?.clientX || 0; }, { passive: true });
  dialog.querySelector('[data-gallery-stage]')?.addEventListener('touchend', event => {
    const endX = event.changedTouches[0]?.clientX || 0;
    const distance = endX - startX;
    if (Math.abs(distance) < 45) return;
    move(distance > 0 ? -1 : 1);
  }, { passive: true });
})();
