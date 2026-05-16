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

// ===================== SCROLL ANIMATIONS =====================

// Fade-up (cards, items)
const fadeObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('anim-visible');
      fadeObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

// Slide-in from left
const slideLeftObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('anim-visible');
      slideLeftObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

// Slide-in from right
const slideRightObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('anim-visible');
      slideRightObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

// Scale-in
const scaleObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('anim-visible');
      scaleObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.1 });

function applyStagger(elements, baseClass, delay = 100) {
  elements.forEach((el, i) => {
    el.classList.add(baseClass);
    el.style.transitionDelay = (i * delay) + 'ms';
    fadeObserver.observe(el);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  // Feature cards — staggered fade-up
  applyStagger(document.querySelectorAll('.feature-card'), 'anim-fade-up', 90);

  // Segment cards — staggered fade-up
  applyStagger(document.querySelectorAll('.segment-card'), 'anim-fade-up', 80);

  // Testimonial cards — staggered scale-in
  document.querySelectorAll('.testimonial-card').forEach((el, i) => {
    el.classList.add('anim-scale-in');
    el.style.transitionDelay = (i * 100) + 'ms';
    scaleObserver.observe(el);
  });

  // Benefit items — staggered slide-left
  document.querySelectorAll('.benefit-item').forEach((el, i) => {
    el.classList.add('anim-slide-left');
    el.style.transitionDelay = (i * 110) + 'ms';
    slideLeftObserver.observe(el);
  });

  // Benefits visual — slide-right
  document.querySelectorAll('.benefits-visual, .hero-visual').forEach(el => {
    el.classList.add('anim-slide-right');
    slideRightObserver.observe(el);
  });

  // Section headers — fade-up
  document.querySelectorAll('.section-header').forEach(el => {
    el.classList.add('anim-fade-up');
    fadeObserver.observe(el);
  });

  // Blog cards — staggered fade-up
  applyStagger(document.querySelectorAll('.blog-card'), 'anim-fade-up', 80);

  // Pricing cards — staggered scale-in
  document.querySelectorAll('.pricing-card').forEach((el, i) => {
    el.classList.add('anim-scale-in');
    el.style.transitionDelay = (i * 110) + 'ms';
    scaleObserver.observe(el);
  });

  // Trial form — fade-up
  document.querySelectorAll('.trial-form-wrap, .trial-text').forEach(el => {
    el.classList.add('anim-fade-up');
    fadeObserver.observe(el);
  });

  // CTA inner
  document.querySelectorAll('.cta-inner').forEach(el => {
    el.classList.add('anim-scale-in');
    scaleObserver.observe(el);
  });

  // School logo strip items
  applyStagger(document.querySelectorAll('.school-logo-item'), 'anim-fade-up', 60);
});

// ===================== STAT COUNTER =====================
const statObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      animateCounter(entry.target);
      statObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.5 });

function animateCounter(el) {
  const text = el.textContent.trim();
  const num = parseFloat(text.replace(/[^0-9.]/g, ''));
  if (isNaN(num) || num === 0) return;
  const prefix = text.match(/^[^0-9]*/)?.[0] || '';
  const suffix = text.replace(/^[^0-9]*/, '').replace(/[0-9.]+/, '') || '';
  const isDecimal = text.includes('.');
  const duration = 1400;
  const start = performance.now();

  function step(now) {
    const p = Math.min((now - start) / duration, 1);
    const ease = 1 - Math.pow(1 - p, 3);
    const val = num * ease;
    el.textContent = prefix + (isDecimal ? val.toFixed(1) : Math.round(val)) + suffix;
    if (p < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.stat-num').forEach(el => statObserver.observe(el));
});
