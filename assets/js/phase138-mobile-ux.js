(() => {
  'use strict';

  function enhanceAdminTables(root = document) {
    if (!document.body.classList.contains('admin-body')) return;
    root.querySelectorAll('.table-wrap table, table.admin-table').forEach((table) => {
      const headers = Array.from(table.querySelectorAll('thead th')).map((th) => th.textContent.trim());
      if (!headers.length) return;
      table.querySelectorAll('tbody tr').forEach((row) => {
        Array.from(row.children).forEach((cell, index) => {
          if (cell.tagName !== 'TD' || cell.hasAttribute('data-label') || cell.hasAttribute('colspan')) return;
          cell.setAttribute('data-label', headers[index] || `Column ${index + 1}`);
        });
      });
    });
  }

  function syncAdminMenu(open) {
    if (!document.body.classList.contains('admin-body')) return;
    document.body.classList.toggle('admin-menu-open', open);
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (!document.body.classList.contains('admin-body')) return;
    enhanceAdminTables();

    const adminSide = document.getElementById('adminSide');
    document.querySelectorAll('[data-admin-menu-open]').forEach((button) => {
      button.addEventListener('click', () => syncAdminMenu(true));
    });
    document.querySelectorAll('[data-admin-menu-close]').forEach((button) => {
      button.addEventListener('click', () => syncAdminMenu(false));
    });
    adminSide?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => syncAdminMenu(false)));

    document.addEventListener('click', (event) => {
      if (!document.body.classList.contains('admin-menu-open') || !adminSide) return;
      if (adminSide.contains(event.target) || event.target.closest('[data-admin-menu-open]')) return;
      adminSide.classList.remove('open');
      syncAdminMenu(false);
    });

    document.addEventListener('keydown', (event) => {
      if (event.key !== 'Escape') return;
      adminSide?.classList.remove('open');
      syncAdminMenu(false);
    });

    const observer = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        for (const node of mutation.addedNodes) {
          if (!(node instanceof Element)) continue;
          enhanceAdminTables(node);
        }
      }
    });
    observer.observe(document.body, { childList: true, subtree: true });
  });
})();
