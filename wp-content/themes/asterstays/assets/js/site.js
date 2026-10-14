(() => {
  const toggle = document.querySelector('.aster-menu-toggle');
  const nav = document.querySelector('#aster-primary-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', String(!open));
      nav.classList.toggle('is-open', !open);
    });
  }
  document.querySelectorAll('.aster-booking-form').forEach((form) => {
    const checkIn = form.querySelector('[name="aster_check_in"]');
    const checkOut = form.querySelector('[name="aster_check_out"]');
    if (!checkIn || !checkOut) return;
    const iso = (date) => date.toISOString().slice(0, 10);
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    checkIn.min = iso(new Date());
    checkOut.min = iso(tomorrow);
    checkIn.addEventListener('change', () => {
      if (!checkIn.value) return;
      const earliest = new Date(checkIn.value + 'T00:00:00');
      earliest.setDate(earliest.getDate() + 1);
      checkOut.min = iso(earliest);
      if (checkOut.value && checkOut.value <= checkIn.value) checkOut.value = '';
    });
  });
})();
