/**
 * Floofs - Pet Health Management System
 * Enhanced JavaScript with Authentication Protection
 */

// API Configuration
const API_URL = 'https://floofs-web.onrender.com/api';
let authToken = localStorage.getItem('auth_token');
let currentUser = null;

// ============================================================================
// INITIALIZATION
// ============================================================================

function init() {
    setupEventListeners();
    updateAuthUI();
    
    if (authToken) {
        validateToken();
    }
}

// ============================================================================
// AUTHENTICATION UI
// ============================================================================

/**
 * Update UI based on auth status
 */
function updateAuthUI() {
    const btnLogin = document.getElementById('btnLogin');
    const btnRegister = document.getElementById('btnRegister');
    const btnAddPet = document.getElementById('btnAddPet');
    const mainContent = document.querySelectorAll('main section:not(#dashboard)');
    const dashboardCard = document.querySelector('[style*="grid-column: 1 / -1"]');

    if (authToken && currentUser) {
        // User is logged in
        btnLogin.style.display = 'none';
        btnRegister.textContent = `${currentUser.name} (Atsijungti)`;
        btnRegister.style.background = 'rgba(255, 107, 157, 0.9)';
        btnRegister.onclick = handleLogout;
        
        // Show all content
        mainContent.forEach(section => {
            section.style.display = 'block';
        });
        
        // Show welcome message
        if (dashboardCard) {
            dashboardCard.style.display = 'block';
        }
    } else {
        // User is NOT logged in
        btnLogin.style.display = 'block';
        btnRegister.textContent = 'Registracija';
        btnRegister.style.background = 'white';
        btnRegister.style.color = 'var(--primary)';
        btnRegister.onclick = () => openModal('registerModal');
        
        // Hide all content sections
        mainContent.forEach(section => {
            section.style.display = 'none';
        });
        
        // Show only dashboard stats but hide Add Pet button
        if (dashboardCard) {
            dashboardCard.innerHTML = `
                <div class="card" style="grid-column: 1 / -1;">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-lock"></i>
                            Prašome Prisijungti
                        </div>
                    </div>
                    <p style="color: var(--text-secondary); line-height: 1.8; text-align: center;">
                        Norint naudotis Floofs sistema, prašome prisijungti arba registruotis.
                        <br><br>
                        <strong>Floofs</strong> sistemoje jūs galite lengvai valdyti savo augintinių sveikatos duomenis. 
                        Sekite jų sveikatos pokyčius, žymėkite atliktus procedūras ir niekada neprarastite svarbios informacijos.
                    </p>
                </div>
                <div class="card" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-sign-in-alt" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem; display: block;"></i>
                    <h3 style="margin-bottom: 1rem;">Pradėkite čia</h3>
                    <button class="btn-action btn-primary" onclick="openModal('loginModal')" style="margin-right: 0.5rem;">
                        <i class="fas fa-sign-in-alt"></i> Prisijungti
                    </button>
                    <button class="btn-action btn-secondary" onclick="openModal('registerModal')">
                        <i class="fas fa-user-plus"></i> Registruotis
                    </button>
                </div>
            `;
        }
    }
}

// ============================================================================
// SETUP EVENT LISTENERS
// ============================================================================

function setupEventListeners() {
    // Mobile Menu Toggle
    document.getElementById('menuToggle').addEventListener('click', () => {
        document.getElementById('headerNav').classList.toggle('active');
    });

    // Modal Triggers
    document.getElementById('btnLogin').addEventListener('click', () => openModal('loginModal'));
    document.getElementById('btnRegister').addEventListener('click', () => openModal('registerModal'));

    // Modal Close Buttons
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const modal = e.target.closest('.modal-close');
            if (modal && modal.dataset.modal) {
                closeModal(modal.dataset.modal);
            }
        });
    });

    // Modal Footer Close Buttons
    document.querySelectorAll('.modal-footer [data-modal]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            closeModal(e.target.dataset.modal);
        });
    });

    // Forms
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
    document.getElementById('registerForm').addEventListener('submit', handleRegister);
    document.getElementById('addPetForm').addEventListener('submit', handleAddPet);
    document.getElementById('addHealthForm').addEventListener('submit', handleAddHealth);
    document.getElementById('addProcedureForm').addEventListener('submit', handleAddProcedure);

    // Button Toggles
    document.getElementById('btnAddPet').addEventListener('click', () => {
        const section = document.getElementById('addPetSection');
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
    });

    document.getElementById('btnCancelPet').addEventListener('click', () => {
        document.getElementById('addPetSection').style.display = 'none';
        document.getElementById('addPetForm').reset();
    });

    document.getElementById('btnAddHealth').addEventListener('click', () => {
        const section = document.getElementById('addHealthSection');
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
    });

    document.getElementById('btnCancelHealth').addEventListener('click', () => {
        document.getElementById('addHealthSection').style.display = 'none';
        document.getElementById('addHealthForm').reset();
    });

    document.getElementById('btnAddProcedure').addEventListener('click', () => {
        const section = document.getElementById('addProcedureSection');
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
    });

    document.getElementById('btnCancelProcedure').addEventListener('click', () => {
        document.getElementById('addProcedureSection').style.display = 'none';
        document.getElementById('addProcedureForm').reset();
    });

    // Refresh Button
    document.getElementById('btnRefreshPets').addEventListener('click', loadPets);

    // Modal Submit Buttons
    document.getElementById('btnSubmitLogin').addEventListener('click', () => {
        document.getElementById('loginForm').dispatchEvent(new Event('submit'));
    });

    document.getElementById('btnSubmitRegister').addEventListener('click', () => {
        document.getElementById('registerForm').dispatchEvent(new Event('submit'));
    });
}

// ============================================================================
// MODAL FUNCTIONS
// ============================================================================

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// ============================================================================
// AUTHENTICATION FUNCTIONS
// ============================================================================

/**
 * Handle User Login
 */
async function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;

    if (!email || !password) {
        showAlert('Prašome užpildyti visus laukus', 'warning');
        return;
    }

    try {
        const response = await fetch(`${API_URL}/auth/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();
        
        if (response.ok && data.access_token) {
            authToken = data.access_token;
            localStorage.setItem('auth_token', authToken);
            
            // Fetch current user
            await fetchCurrentUser();
            
            showAlert('✓ Sėkmingai prisijungta!', 'success');
            closeModal('loginModal');
            document.getElementById('loginForm').reset();
            
            // Update UI
            updateAuthUI();
            loadUserData();
        } else {
            showAlert('✗ Neteisingi prisijungimo duomenys', 'error');
        }
    } catch (error) {
        showAlert('✗ Prisijungimo klaida', 'error');
        console.error('Login error:', error);
    }
}

/**
 * Handle User Registration
 */
async function handleRegister(e) {
    e.preventDefault();
    
    const name = document.getElementById('registerName').value.trim();
    const email = document.getElementById('registerEmail').value.trim();
    const password = document.getElementById('registerPassword').value;
    const passwordConfirm = document.getElementById('registerPasswordConfirm').value;

    // Validation
    if (!name || !email || !password) {
        showAlert('✗ Prašome užpildyti visus laukus', 'warning');
        return;
    }

    if (password.length < 6) {
        showAlert('✗ Slaptažodis turi būti mažiausiai 6 simboliai', 'warning');
        return;
    }

    if (password !== passwordConfirm) {
        showAlert('✗ Slaptažodžiai nesutampa', 'error');
        return;
    }

    if (!email.includes('@')) {
        showAlert('✗ Neteisingas el. pašto formatas', 'warning');
        return;
    }

    try {
        const response = await fetch(`${API_URL}/auth/register`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                name, 
                email, 
                password,
                role: 'user'
            })
        });

        const data = await response.json();
        
        if (response.ok) {
            showAlert('✓ Registracija sėkminga! Dabar prisijunkite.', 'success');
            closeModal('registerModal');
            document.getElementById('registerForm').reset();
            
            // Auto-fill login
            document.getElementById('loginEmail').value = email;
            openModal('loginModal');
        } else {
            const errorMsg = data.error || data.message || 'Registracijos klaida';
            if (errorMsg.includes('unique') || errorMsg.includes('exists')) {
                showAlert('✗ Šis el. paštas jau naudojamas', 'error');
            } else {
                showAlert(`✗ ${errorMsg}`, 'error');
            }
        }
    } catch (error) {
        showAlert('✗ Registracijos klaida', 'error');
        console.error('Register error:', error);
    }
}

/**
 * Fetch Current User Info
 */
async function fetchCurrentUser() {
    if (!authToken) return;

    try {
        const response = await fetch(`${API_URL}/auth/me`, {
            headers: { 'Authorization': `Bearer ${authToken}` }
        });

        if (response.ok) {
            currentUser = await response.json();
            localStorage.setItem('current_user', JSON.stringify(currentUser));
            return true;
        } else {
            clearAuth();
            return false;
        }
    } catch (error) {
        console.error('Error fetching current user:', error);
        return false;
    }
}

/**
 * Validate Token on App Load
 */
async function validateToken() {
    if (!authToken) return;

    try {
        const response = await fetch(`${API_URL}/auth/me`, {
            headers: { 'Authorization': `Bearer ${authToken}` }
        });

        if (response.ok) {
            currentUser = await response.json();
            loadUserData();
            return true;
        } else {
            clearAuth();
            return false;
        }
    } catch (error) {
        console.error('Token validation error:', error);
        clearAuth();
        return false;
    }
}

/**
 * Handle Logout
 */
async function handleLogout() {
    if (!confirm('Ar tikrai norite atsijungti?')) return;

    try {
        // Try to logout on backend
        if (authToken) {
            await fetch(`${API_URL}/auth/logout`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${authToken}`
                }
            });
        }
    } catch (error) {
        console.error('Logout error:', error);
    }

    clearAuth();
    updateAuthUI();
    showAlert('✓ Sėkmingai atsijungta', 'success');
}

/**
 * Clear Auth State
 */
function clearAuth() {
    authToken = null;
    currentUser = null;
    localStorage.removeItem('auth_token');
    localStorage.removeItem('current_user');
}

// ============================================================================
// USER DATA LOADING
// ============================================================================

/**
 * Load all user data
 */
async function loadUserData() {
    if (!authToken) return;
    
    try {
        await loadPets();
        await loadProcedures();
    } catch (error) {
        console.error('Error loading user data:', error);
    }
}

// ============================================================================
// PET MANAGEMENT
// ============================================================================

/**
 * Load all pets
 */
async function loadPets() {
    if (!authToken) return;

    try {
        const response = await fetch(`${API_URL}/pets`, {
            headers: { 'Authorization': `Bearer ${authToken}` }
        });

        if (response.ok) {
            const pets = await response.json();
            displayPets(pets);
            updateHealthRecordSelect(pets);
            updateStats(pets, []);
        }
    } catch (error) {
        console.error('Error loading pets:', error);
        showAlert('✗ Klaida kraunant gyvūnus', 'error');
    }
}

/**
 * Display pets in UI
 */
function displayPets(pets) {
    const petsList = document.getElementById('petsList');
    
    if (pets.length === 0) {
        petsList.innerHTML = `
            <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">
                <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                Jūs neturite registruotų gyvūnų. Pradėkite nuo naujo gyvūnio pridėjimo!
            </p>
        `;
        return;
    }

    petsList.innerHTML = pets.map(pet => `
        <div class="pet-item" data-pet-id="${pet.id}">
            <div class="pet-avatar">
                <i class="fas fa-${getAnimalIcon(pet.species)}"></i>
            </div>
            <div class="pet-info">
                <h3>${pet.name}</h3>
                <p>${pet.species}${pet.breed ? ' - ' + pet.breed : ''} • Amžius: ${pet.age} metai</p>
            </div>
            <div class="pet-actions">
                <button class="btn-action btn-primary" onclick="loadHealthRecords(${pet.id})">
                    <i class="fas fa-heartbeat"></i> Sveikata
                </button>
                <button class="btn-action btn-secondary" onclick="editPet(${pet.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-action btn-danger" onclick="deletePet(${pet.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');

    // Update stats
    document.querySelector('[data-stat="pets"]').textContent = pets.length;
}

/**
 * Add new pet
 */
async function handleAddPet(e) {
    e.preventDefault();
    
    if (!currentUser) {
        showAlert('✗ Prašome prisijungti', 'warning');
        return;
    }

    const pet = {
        user_id: currentUser.id,
        name: document.getElementById('petName').value.trim(),
        species: document.getElementById('petSpecies').value,
        breed: document.getElementById('petBreed').value.trim() || null,
        age: parseInt(document.getElementById('petAge').value),
        photo_path: document.getElementById('petPhoto').value.trim() || null
    };

    if (!pet.name || !pet.species) {
        showAlert('✗ Prašome užpildyti reikalingus laukus', 'warning');
        return;
    }

    try {
        const response = await fetch(`${API_URL}/pets`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify(pet)
        });

        if (response.ok) {
            showAlert('✓ Gyvūnas sėkmingai pridėtas!', 'success');
            document.getElementById('addPetSection').style.display = 'none';
            document.getElementById('addPetForm').reset();
            await loadPets();
        } else {
            showAlert('✗ Klaida pridėjant gyvūnį', 'error');
        }
    } catch (error) {
        showAlert('✗ Klaida pridėjant gyvūnį', 'error');
        console.error(error);
    }
}

/**
 * Delete pet
 */
async function deletePet(petId) {
    if (!confirm('Ar tikrai norite ištrinti šį gyvūnį?')) return;

    try {
        const response = await fetch(`${API_URL}/pets/${petId}`, {
            method: 'DELETE',
            headers: { 'Authorization': `Bearer ${authToken}` }
        });

        if (response.ok) {
            showAlert('✓ Gyvūnas ištrintas', 'success');
            await loadPets();
        } else {
            showAlert('✗ Klaida trinant gyvūnį', 'error');
        }
    } catch (error) {
        showAlert('✗ Klaida trinant gyvūnį', 'error');
        console.error(error);
    }
}

/**
 * Edit pet (placeholder)
 */
function editPet(petId) {
    showAlert('ℹ Redagavimo funkcija neImplementuota', 'info');
}

/**
 * Get animal icon
 */
function getAnimalIcon(species) {
    const icons = {
        'Šuo': 'dog',
        'Katė': 'cat',
        'Triušis': 'rabbit',
        'Žiurkė': 'mouse',
        'Paukštis': 'dove'
    };
    return icons[species] || 'paw';
}

// ============================================================================
// HEALTH RECORDS
// ============================================================================

/**
 * Load health records for pet
 */
async function loadHealthRecords(petId) {
    if (!authToken) return;

    try {
        const response = await fetch(`${API_URL}/pets/${petId}/health-records`, {
            headers: { 'Authorization': `Bearer ${authToken}` }
        });

        if (response.ok) {
            const records = await response.json();
            displayHealthRecords(records);
            
            // Scroll to health section
            const healthSection = document.getElementById('health');
            healthSection.scrollIntoView({ behavior: 'smooth' });
        }
    } catch (error) {
        console.error('Error loading health records:', error);
        showAlert('✗ Klaida kraunant sveikatės duomenis', 'error');
    }
}

/**
 * Display health records
 */
function displayHealthRecords(records) {
    const container = document.getElementById('healthRecordsList');
    
    if (records.length === 0) {
        container.innerHTML = `
            <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">
                <i class="fas fa-file-medical" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                Nėra šio gyvūno sveikatės įrašų.
            </p>
        `;
        return;
    }

    container.innerHTML = records.map(record => `
        <div class="health-record">
            <h4><i class="fas fa-check-circle" style="color: var(--success);"></i> Sveikatos įrašas #${record.id}</h4>
            <p><strong>Svoris:</strong> ${record.weight ? record.weight + ' kg' : 'Nenurodyta'}</p>
            <p><strong>Skiepai:</strong> ${record.vaccines || 'Nenurodyta'}</p>
            <p><strong>Ligų istorija:</strong> ${record.illness_history || 'Nenurodyta'}</p>
            <p><strong>Rekomendacijos:</strong> ${record.recommendations || 'Nenurodyta'}</p>
            <p style="color: var(--text-secondary); font-size: 0.8rem; margin-top: 0.5rem;">
                <i class="fas fa-calendar"></i> ${formatDate(record.created_at)}
            </p>
            <button class="btn-action btn-danger" onclick="deleteHealthRecord(${record.id})" style="margin-top: 1rem;">
                <i class="fas fa-trash"></i> Ištrinti
            </button>
        </div>
    `).join('');
}

/**
 * Add health record
 */
async function handleAddHealth(e) {
    e.preventDefault();
    
    const petId = document.getElementById('healthPet').value;

    if (!petId) {
        showAlert('✗ Prašome pasirinkti gyvūnį', 'warning');
        return;
    }

    const health = {
        pet_id: parseInt(petId),
        weight: document.getElementById('healthWeight').value || null,
        vaccines: document.getElementById('healthVaccines').value.trim() || null,
        illness_history: document.getElementById('healthIllness').value.trim() || null,
        recommendations: document.getElementById('healthRecommendations').value.trim() || null
    };

    try {
        const response = await fetch(`${API_URL}/health-records`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify(health)
        });

        if (response.ok) {
            showAlert('✓ Sveikatės įrašas sėkmingai pridėtas!', 'success');
            document.getElementById('addHealthSection').style.display = 'none';
            document.getElementById('addHealthForm').reset();
            await loadHealthRecords(petId);
        } else {
            showAlert('✗ Klaida pridėjant sveikatės įrašą', 'error');
        }
    } catch (error) {
        showAlert('✗ Klaida pridėjant sveikatės įrašą', 'error');
        console.error(error);
    }
}

/**
 * Delete health record
 */
async function deleteHealthRecord(recordId) {
    if (!confirm('Ar tikrai norite ištrinti šį sveikatės įrašą?')) return;

    try {
        const response = await fetch(`${API_URL}/health-records/${recordId}`, {
            method: 'DELETE',
            headers: { 'Authorization': `Bearer ${authToken}` }
        });

        if (response.ok) {
            showAlert('✓ Sveikatės įrašas ištrintas', 'success');
            // Reload health records
            const petId = document.querySelector('[data-pet-id]')?.getAttribute('data-pet-id');
            if (petId) await loadHealthRecords(petId);
        }
    } catch (error) {
        showAlert('✗ Klaida trinant sveikatės įrašą', 'error');
    }
}

/**
 * Update health record pet select
 */
function updateHealthRecordSelect(pets) {
    const select = document.getElementById('healthPet');
    select.innerHTML = '<option value="">Pasirinkite gyvūnį</option>' + 
        pets.map(pet => `<option value="${pet.id}">${pet.name}</option>`).join('');
}

// ============================================================================
// PROCEDURES
// ============================================================================

/**
 * Load all procedures
 */
async function loadProcedures() {
    if (!authToken) return;

    try {
        const response = await fetch(`${API_URL}/procedures`, {
            headers: { 'Authorization': `Bearer ${authToken}` }
        });

        if (response.ok) {
            const procedures = await response.json();
            displayProcedures(procedures);
            updateProcedureSelect();
        }
    } catch (error) {
        console.error('Error loading procedures:', error);
        showAlert('✗ Klaida kraunant procedūras', 'error');
    }
}

/**
 * Display procedures
 */
function displayProcedures(procedures) {
    const container = document.getElementById('proceduresList');
    
    if (procedures.length === 0) {
        container.innerHTML = `
            <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">
                <i class="fas fa-tasks" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; opacity: 0.5;"></i>
                Nėra procedūrų. Pradėkite nuo naujos procedūros!
            </p>
        `;
        return;
    }

    container.innerHTML = procedures.map(proc => {
        const statusClass = {
            'planned': 'badge-info',
            'done': 'badge-success',
            'canceled': 'badge-danger'
        }[proc.status] || 'badge-info';

        const statusText = {
            'planned': 'Numatyta',
            'done': 'Baigta',
            'canceled': 'Atšaukta'
        }[proc.status] || proc.status;

        return `
            <div class="procedure-item">
                <div class="procedure-info">
                    <h5>${proc.title}</h5>
                    <p>${proc.description || 'Nėra aprašymo'}</p>
                    ${proc.scheduled_at ? `<p><i class="fas fa-calendar"></i> ${formatDate(proc.scheduled_at)}</p>` : ''}
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <span class="badge ${statusClass}">${statusText}</span>
                    <button class="btn-action btn-danger" onclick="deleteProcedure(${proc.id})" style="width: auto; padding: 0.5rem;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');

    // Update stats
    document.querySelector('[data-stat="procedures"]').textContent = procedures.length;
}

/**
 * Add procedure
 */
async function handleAddProcedure(e) {
    e.preventDefault();
    
    const healthRecordId = document.getElementById('procHealthRecord').value;
    const title = document.getElementById('procTitle').value.trim();

    if (!healthRecordId || !title) {
        showAlert('✗ Prašome užpildyti reikalingus laukus', 'warning');
        return;
    }

    const procedure = {
        health_record_id: parseInt(healthRecordId),
        title,
        description: document.getElementById('procDescription').value.trim() || null,
        scheduled_at: document.getElementById('procDate').value || null,
        status: document.getElementById('procStatus').value
    };

    try {
        const response = await fetch(`${API_URL}/procedures`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify(procedure)
        });

        if (response.ok) {
            showAlert('✓ Procedūra sėkmingai pridėta!', 'success');
            document.getElementById('addProcedureSection').style.display = 'none';
            document.getElementById('addProcedureForm').reset();
            await loadProcedures();
        } else {
            showAlert('✗ Klaida pridėjant procedūrą', 'error');
        }
    } catch (error) {
        showAlert('✗ Klaida pridėjant procedūrą', 'error');
        console.error(error);
    }
}

/**
 * Delete procedure
 */
async function deleteProcedure(procId) {
    if (!confirm('Ar tikrai norite ištrinti šią procedūrą?')) return;

    try {
        const response = await fetch(`${API_URL}/procedures/${procId}`, {
            method: 'DELETE',
            headers: { 'Authorization': `Bearer ${authToken}` }
        });

        if (response.ok) {
            showAlert('✓ Procedūra ištrinta', 'success');
            await loadProcedures();
        }
    } catch (error) {
        showAlert('✗ Klaida trinant procedūrą', 'error');
    }
}

/**
 * Update procedure select
 */
function updateProcedureSelect() {
    const select = document.getElementById('procHealthRecord');
    // This would be populated from health records if needed
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Format date
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('lt-LT');
}

/**
 * Update statistics
 */
function updateStats(pets, procedures) {
    const petsCount = pets?.length || 0;
    const procCount = procedures?.length || 0;
    
    const petsEl = document.querySelector('[data-stat="pets"]');
    const procEl = document.querySelector('[data-stat="procedures"]');
    
    if (petsEl) petsEl.textContent = petsCount;
    if (procEl) procEl.textContent = procCount;
}

/**
 * Show alert notification
 */
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alertContainer');
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };

    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="${icons[type]}"></i>
        <span>${message}</span>
    `;

    alertContainer.appendChild(alert);

    setTimeout(() => {
        alert.style.animation = 'slideUp 0.3s ease-out';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    }, 4000);
}

// ============================================================================
// AUTO-INITIALIZE
// ============================================================================

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}