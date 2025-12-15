import './bootstrap';
// Hamburger toggle
const hamburger = document.querySelector('.hamburger');
const navLinks = document.querySelector('.nav-links');

hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('open');
});

// Simple page loader via fetch
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', async (e) => {
        e.preventDefault();
        const page = link.dataset.page;
        const res = await fetch(`/api/${page}`);
        const data = await res.json();
        renderPage(page, data);
    });
});

function renderPage(page, data) {
    const main = document.getElementById('app-content');
    main.innerHTML = `<h2>${page.charAt(0).toUpperCase() + page.slice(1)}</h2>
                      <pre>${JSON.stringify(data, null, 2)}</pre>`;
}
