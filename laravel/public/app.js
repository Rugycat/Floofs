const API_URL = 'http://localhost:8000/api';
let authToken = localStorage.getItem('auth_token');

function init() {
    setupEvents();
    if (authToken) loadPets();
}

function setupEvents() {
    document.getElementById('menuToggle')
        .addEventListener('click', () =>
            document.getElementById('headerNav').classList.toggle('active')
        );

    document.getElementById('btnLogin')
        .addEventListener('click', () => openModal('loginModal'));

    document.getElementById('btnCancelPet')
        .addEventListener('click', () =>
            document.getElementById('addPetSection').style.display = 'none'
        );

    document.getElementById('addPetForm')
        .addEventListener('submit', addPet);

    document.querySelectorAll('[data-modal]').forEach(btn =>
        btn.addEventListener('click', e =>
            closeModal(e.target.dataset.modal)
        )
    );
}

function openModal(id) {
    document.getElementById(id).classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

async function loadPets() {
    const res = await fetch(`${API_URL}/pets`, {
        headers: { Authorization: `Bearer ${authToken}` }
    });

    if (!res.ok) return;

    const pets = await res.json();
    const list = document.getElementById('petsList');

    list.innerHTML = pets.map(p =>
        `<div class="pet-item">${p.name} (${p.species})</div>`
    ).join('');
}

async function addPet(e) {
    e.preventDefault();

    const pet = {
        name: petName.value,
        species: petSpecies.value,
        age: petAge.value
    };

    await fetch(`${API_URL}/pets`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`
        },
        body: JSON.stringify(pet)
    });

    loadPets();
    e.target.reset();
    document.getElementById('addPetSection').style.display = 'none';
}

init();
