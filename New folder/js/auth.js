// Demo user credentials
const demoUser = {
    username: 'admin',
    password: 'password123',
    displayName: 'แอดมิน',
    isAdmin: true
};

// Regular user demo
const regularUser = {
    username: 'user',
    password: 'user123',
    displayName: 'ผู้ใช้ทั่วไป',
    isAdmin: false
};

// Get registered users from localStorage
function getRegisteredUsers() {
    const users = localStorage.getItem('registeredUsers');
    return users ? JSON.parse(users) : [];
}

// Initialize default users if none exist
function initializeDefaultUsers() {
    const users = getRegisteredUsers();

    // If no users exist, add demo users
    if (users.length === 0) {
        users.push({
            username: regularUser.username,
            displayName: regularUser.displayName,
            password: regularUser.password,
            isAdmin: regularUser.isAdmin
        });
        saveRegisteredUsers(users);
    }

    // Check if admin exists in localStorage
    const adminExists = localStorage.getItem('adminInitialized') === 'true';
    if (!adminExists) {
        localStorage.setItem('adminInitialized', 'true');
    }
}

// Save registered users to localStorage
function saveRegisteredUsers(users) {
    localStorage.setItem('registeredUsers', JSON.stringify(users));
}

// Check if user is logged in
function isLoggedIn() {
    return localStorage.getItem('isLoggedIn') === 'true';
}

// Redirect to login if not authenticated
function checkAuth() {
    if (!isLoggedIn() && (window.location.pathname.includes('home.html') ||
                          window.location.pathname.includes('dashboard.html') ||
                          window.location.pathname.includes('admin.html'))) {
        window.location.href = 'index.html';
        return;
    }

    // Check admin access for admin pages
    if (window.location.pathname.includes('admin.html')) {
        const isAdmin = localStorage.getItem('isAdmin') === 'true';
        if (!isAdmin) {
            alert('คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            window.location.href = 'home.html';
        }
    }
}

// Redirect to home if already logged in
function checkAlreadyLoggedIn() {
    if (isLoggedIn() && (window.location.pathname.includes('index.html') || window.location.pathname.includes('register.html'))) {
        window.location.href = 'home.html';
    }
}

// Handle login form submission
function handleLogin(event) {
    event.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const errorMessage = document.getElementById('error-message');

    // Simple validation
    if (!username || !password) {
        errorMessage.textContent = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
        return;
    }

    // Check if it's the demo admin user
    if (username === demoUser.username && password === demoUser.password) {
        // Store login state
        localStorage.setItem('isLoggedIn', 'true');
        localStorage.setItem('username', username);
        localStorage.setItem('displayName', demoUser.displayName);
        localStorage.setItem('isAdmin', 'true');

        // Redirect to home
        window.location.href = 'home.html';
        return;
    }

    // Check if it's the demo regular user
    if (username === regularUser.username && password === regularUser.password) {
        // Store login state
        localStorage.setItem('isLoggedIn', 'true');
        localStorage.setItem('username', username);
        localStorage.setItem('displayName', regularUser.displayName);
        localStorage.setItem('isAdmin', 'false');

        // Redirect to home
        window.location.href = 'home.html';
        return;
    }

    // Check registered users
    const users = getRegisteredUsers();
    const user = users.find(u => u.username === username && u.password === password);

    if (user) {
        // Store login state
        localStorage.setItem('isLoggedIn', 'true');
        localStorage.setItem('username', username);
        localStorage.setItem('displayName', user.displayName);
        localStorage.setItem('isAdmin', user.isAdmin ? 'true' : 'false');

        // Redirect to home
        window.location.href = 'home.html';
    } else {
        errorMessage.textContent = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}

// Handle registration form submission
function handleRegistration(event) {
    event.preventDefault();

    const username = document.getElementById('username').value;
    const displayName = document.getElementById('display-name').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const errorMessage = document.getElementById('error-message');

    // Simple validation
    if (!username || !displayName || !password || !confirmPassword) {
        errorMessage.textContent = 'กรุณากรอกข้อมูลให้ครบทุกช่อง';
        return;
    }

    if (password !== confirmPassword) {
        errorMessage.textContent = 'รหัสผ่านไม่ตรงกัน';
        return;
    }

    // Check if username already exists
    const users = getRegisteredUsers();
    if (username === demoUser.username || users.some(u => u.username === username)) {
        errorMessage.textContent = 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว';
        return;
    }

    // Add new user (regular user by default)
    users.push({
        username,
        displayName,
        password,
        isAdmin: false
    });

    // Save updated users
    saveRegisteredUsers(users);

    // Show success message and redirect to login
    alert('สมัครสมาชิกสำเร็จ! คุณสามารถเข้าสู่ระบบได้ทันที');
    window.location.href = 'index.html';
}

// Handle logout
function handleLogout() {
    // Clear login state
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('username');
    localStorage.removeItem('displayName');
    localStorage.removeItem('isAdmin');

    // Redirect to login page
    window.location.href = 'index.html';
}

// Update home page with user info
function updateHomePage() {
    const userNameElements = document.querySelectorAll('#user-name');
    const displayName = localStorage.getItem('displayName') || 'ผู้ใช้';

    userNameElements.forEach(element => {
        if (element) {
            element.textContent = displayName;
        }
    });
}

// Check if user is admin
function isAdmin() {
    return localStorage.getItem('isAdmin') === 'true';
}

// Update navigation based on admin status
function updateNavigation() {
    const adminNavLink = document.getElementById('admin-nav-link');
    if (adminNavLink) {
        if (isAdmin()) {
            adminNavLink.style.display = 'block';
        } else {
            adminNavLink.style.display = 'none';
        }
    }
}

// Initialize page
function init() {
    // Initialize default users
    initializeDefaultUsers();

    // Check authentication status
    checkAuth();
    checkAlreadyLoggedIn();

    // Set up event listeners
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegistration);
    }

    const logoutBtns = document.querySelectorAll('.logout-btn, .logout-btn-small');
    logoutBtns.forEach(btn => {
        if (btn) {
            btn.addEventListener('click', handleLogout);
        }
    });

    // Update home page if on home page
    if (window.location.pathname.includes('home.html') || window.location.pathname.includes('dashboard.html')) {
        updateHomePage();
    }

    // Update navigation based on admin status
    updateNavigation();
}

// Run initialization when DOM is loaded
document.addEventListener('DOMContentLoaded', init);
