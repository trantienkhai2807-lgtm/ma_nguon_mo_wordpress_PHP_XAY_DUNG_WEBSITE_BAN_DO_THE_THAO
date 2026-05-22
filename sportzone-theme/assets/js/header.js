(function () {
  var header = document.querySelector('.market-site-header');
  var panel = document.querySelector('.market-catalog__panel');
  var toggles = document.querySelectorAll('[aria-controls="market-catalog-panel"]');

  if (!header || !panel || toggles.length === 0) {
    return;
  }

  function setOpen(isOpen) {
    header.classList.toggle('is-catalog-open', isOpen);
    toggles.forEach(function (toggle) {
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }

  toggles.forEach(function (toggle) {
    toggle.addEventListener('click', function (event) {
      event.preventDefault();
      setOpen(!header.classList.contains('is-catalog-open'));
    });
  });

  document.addEventListener('click', function (event) {
    if (!header.contains(event.target)) {
      setOpen(false);
    }
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      setOpen(false);
    }
  });
})();
