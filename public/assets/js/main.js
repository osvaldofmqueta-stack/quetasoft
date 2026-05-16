// Sticky header
const header = document.getElementById('header');
window.addEventListener('scroll', () => {
  header.classList.toggle('scrolled', window.scrollY > 50);
});

// Mobile menu
const menuToggle = document.getElementById('menuToggle');
const nav = document.getElementById('nav');
menuToggle.addEventListener('click', () => {
  nav.classList.toggle('open');
  menuToggle.classList.toggle('active');
});

// Close menu on nav link click
document.querySelectorAll('.nav-link').forEach(link => {
  link.addEventListener('click', () => {
    nav.classList.remove('open');
    menuToggle.classList.remove('active');
  });
});

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});

// Showcase tab switching
function switchTab(btn, id) {
  document.querySelectorAll('.stab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.spanel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  const panel = document.getElementById('tab-' + id);
  if (panel) panel.classList.add('active');
}

// FAQ toggle
function toggleFaq(btn) {
  const item = btn.parentElement;
  const isOpen = item.classList.contains('open');
  document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
  if (!isOpen) item.classList.add('open');
}

// Lead form submission
async function submitLead(e) {
  e.preventDefault();
  const form = document.getElementById('trialForm');
  const btn = document.getElementById('submitBtn');
  const success = document.getElementById('formSuccess');
  const error = document.getElementById('formError');

  btn.disabled = true;
  btn.textContent = 'A enviar...';
  error.style.display = 'none';

  const data = new FormData(form);
  try {
    const res = await fetch('/api/submit-lead.php', { method: 'POST', body: data });
    const json = await res.json();
    if (json.success) {
      form.querySelectorAll('input, textarea, button').forEach(el => el.style.display = 'none');
      form.querySelector('h3').style.display = 'none';
      success.style.display = 'block';
    } else {
      error.textContent = json.message || 'Erro ao enviar. Tente novamente.';
      error.style.display = 'block';
      btn.disabled = false;
      btn.textContent = '🚀 Quero a minha demonstração gratuita';
    }
  } catch {
    error.textContent = 'Erro de ligação. Verifique a internet e tente novamente.';
    error.style.display = 'block';
    btn.disabled = false;
    btn.textContent = '🚀 Quero a minha demonstração gratuita';
  }
}

// Animate on scroll
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.feature-card, .segment-card, .testimonial-card, .benefit-item').forEach(el => {
  el.classList.add('animate-on-scroll');
  observer.observe(el);
});
