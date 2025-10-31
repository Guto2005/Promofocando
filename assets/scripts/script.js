const slides = document.querySelector('.slides');
const imagens = document.querySelectorAll('.slides img');
const dots = document.querySelectorAll('.dot');

let index = 0;

function mostrarSlide(novoIndex) {
  index = novoIndex;
  slides.style.transform = `translateX(${-index * 100}%)`;
  atualizarDots();
}

function atualizarDots() {
  dots.forEach((dot, i) => {
    dot.classList.toggle('active', i === index);
  });
}

// Clique nos indicadores
dots.forEach((dot, i) => {
  dot.addEventListener('click', () => {
    mostrarSlide(i);
  });
});

// Autoplay
setInterval(() => {
  index = (index + 1) % imagens.length;
  mostrarSlide(index);
}, 5000);

// Inicializa
mostrarSlide(0);
