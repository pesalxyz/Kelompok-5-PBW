// Animasi ketika scroll ke elemen
const sections = document.querySelectorAll('.about, .vision, .mission');

window.addEventListener('scroll', () => {
  const triggerBottom = window.innerHeight * 0.9;

  sections.forEach(section => {
    const sectionTop = section.getBoundingClientRect().top;

    if (sectionTop < triggerBottom) {
      section.classList.add('show');
    }
  });
});