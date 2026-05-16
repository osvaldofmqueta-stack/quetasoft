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
    const href = this.getAttribute('href');
    if (!href || href === '#') return;
    try {
      const target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    } catch (err) {}
  });
});

// Carousel
let carouselIndex = 0;
let carouselTimer = null;

function initCarousel() {
  const track = document.getElementById('carouselTrack');
  const dotsWrap = document.getElementById('carouselDots');
  if (!track) return;
  const slides = track.querySelectorAll('.carousel-slide');
  const total = slides.length;

  dotsWrap.innerHTML = '';
  slides.forEach((_, i) => {
    const d = document.createElement('button');
    d.className = 'carousel-dot' + (i === 0 ? ' active' : '');
    d.onclick = () => goToSlide(i);
    dotsWrap.appendChild(d);
  });

  goToSlide(0);
  startCarouselTimer();
}

function goToSlide(index) {
  const track = document.getElementById('carouselTrack');
  if (!track) return;
  const slides = track.querySelectorAll('.carousel-slide');
  carouselIndex = (index + slides.length) % slides.length;
  track.style.transform = `translateX(-${carouselIndex * 100}%)`;
  document.querySelectorAll('.carousel-dot').forEach((d, i) => {
    d.classList.toggle('active', i === carouselIndex);
  });
  document.querySelectorAll('.carousel-slide').forEach((s, i) => {
    s.classList.toggle('active', i === carouselIndex);
  });
}

function moveCarousel(dir) {
  goToSlide(carouselIndex + dir);
  resetCarouselTimer();
}

function startCarouselTimer() {
  carouselTimer = setInterval(() => goToSlide(carouselIndex + 1), 5000);
}

function resetCarouselTimer() {
  clearInterval(carouselTimer);
  startCarouselTimer();
}

document.addEventListener('DOMContentLoaded', initCarousel);

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
    const res = await fetch('/api/submit-lead/', { method: 'POST', body: data });
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
