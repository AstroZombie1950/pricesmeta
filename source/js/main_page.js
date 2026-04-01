
// листание стрелками
const track = document.getElementById('articlesTrack');
document.querySelector('.articles__arrow--prev').addEventListener('click', () => {
	const card = track.querySelector('.article-card');
	track.scrollBy({ left: -(card.offsetWidth + 24), behavior: 'smooth' });
});
document.querySelector('.articles__arrow--next').addEventListener('click', () => {
	const card = track.querySelector('.article-card');
	track.scrollBy({ left: card.offsetWidth + 24, behavior: 'smooth' });
});

// раскрытие текста
const more = document.getElementById('seotextMore');
const toggle = document.getElementById('seotextToggle');
const icon = toggle.querySelector('.seotext__toggle-icon');

toggle.addEventListener('click', () => {
	const isOpen = more.classList.toggle('is-open');
	toggle.childNodes[0].textContent = isOpen ? 'Скрыть ' : 'Читать полностью ';
	icon.textContent = isOpen ? '∧' : '∨';
});