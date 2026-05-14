// Mobile Menu Toggle Function
function toggleMobileMenu() {
  const menu = document.getElementById('mobileMenu');
  const toggle = document.querySelector('.mobile-menu-toggle');
  
  if (!menu || !toggle) return;
  
  if (menu.classList.contains('active')) {
    menu.classList.remove('active');
    document.body.classList.remove('menu-open');
    toggle.innerHTML = '☰';
    document.body.style.overflow = '';
    toggle.setAttribute('aria-expanded', 'false');
  } else {
    menu.classList.add('active');
    document.body.classList.add('menu-open');
    toggle.innerHTML = '✕';
    document.body.style.overflow = 'hidden';
    toggle.setAttribute('aria-expanded', 'true');
  }
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
  const menu = document.getElementById('mobileMenu');
  const toggle = document.querySelector('.mobile-menu-toggle');
  
  if (menu && menu.classList.contains('active')) {
    if (!menu.contains(event.target) && toggle && !toggle.contains(event.target)) {
      menu.classList.remove('active');
      document.body.classList.remove('menu-open');
      if (toggle) {
        toggle.innerHTML = '☰';
        toggle.setAttribute('aria-expanded', 'false');
      }
      document.body.style.overflow = '';
    }
  }
});

// Close mobile menu when clicking a link
document.addEventListener('DOMContentLoaded', function() {
  const mobileMenuLinks = document.querySelectorAll('.mobile-menu a');
  mobileMenuLinks.forEach(link => {
    link.addEventListener('click', function() {
      const menu = document.getElementById('mobileMenu');
      const toggle = document.querySelector('.mobile-menu-toggle');
      if (menu) {
        menu.classList.remove('active');
        document.body.classList.remove('menu-open');
      }
      if (toggle) {
        toggle.innerHTML = '☰';
        toggle.setAttribute('aria-expanded', 'false');
      }
      document.body.style.overflow = '';
    });
  });
  
  // Close menu on escape key
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      const menu = document.getElementById('mobileMenu');
      const toggle = document.querySelector('.mobile-menu-toggle');
      if (menu && menu.classList.contains('active')) {
        menu.classList.remove('active');
        document.body.classList.remove('menu-open');
        if (toggle) {
          toggle.innerHTML = '☰';
          toggle.setAttribute('aria-expanded', 'false');
        }
        document.body.style.overflow = '';
      }
    }
  });
});

