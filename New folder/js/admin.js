// ข้อมูลสคริปต์ตัวอย่าง (ในแอปพลิเคชันจริงข้อมูลนี้จะมาจากฐานข้อมูล)
const adminScripts = [
    {
        id: 1,
        title: "Blox Fruits Auto Farm",
        description: "สคริปต์ออโต้ฟาร์มสำหรับ Blox Fruits ใช้งานง่าย ไม่มีแบน",
        category: "Fighting",
        image: "https://tr.rbxcdn.com/e9b0640f26e3c602a1f6082f26f09691/768/432/Image/Png",
        downloads: 1200,
        rating: 4.8,
        date: "12/04/2023",
        code: "-- Blox Fruits Auto Farm Script\nlocal Library = loadstring(game:HttpGet(\"https://raw.githubusercontent.com/example/BloxFruits/main/Library.lua\"))();\nlocal Window = Library.CreateLib(\"Blox Fruits Auto Farm\", \"Ocean\");\nlocal Tab = Window:NewTab(\"Auto Farm\");\nlocal Section = Tab:NewSection(\"Farming\");\n\nSection:NewToggle(\"Auto Farm Level\", \"Auto farms levels for you\", function(state)\n    getgenv().AutoFarm = state;\n    while getgenv().AutoFarm do\n        -- Auto farm code here\n        wait();\n    end;\nend);"
    },
    {
        id: 2,
        title: "Adopt Me Auto Farm",
        description: "สคริปต์ช่วยเก็บไอเทมและเลี้ยงสัตว์อัตโนมัติใน Adopt Me",
        category: "Simulator",
        image: "https://tr.rbxcdn.com/5aa5b9b7a25c88e1b0f2306f8ab3fdaf/768/432/Image/Png",
        downloads: 985,
        rating: 4.5,
        date: "10/04/2023",
        code: "-- Adopt Me Auto Farm Script\nlocal Library = loadstring(game:HttpGet(\"https://raw.githubusercontent.com/example/AdoptMe/main/Library.lua\"))();\nlocal Window = Library.CreateLib(\"Adopt Me Auto Farm\", \"Midnight\");\nlocal Tab = Window:NewTab(\"Auto Farm\");\nlocal Section = Tab:NewSection(\"Pets\");\n\nSection:NewToggle(\"Auto Collect Items\", \"Automatically collects items\", function(state)\n    getgenv().AutoCollect = state;\n    while getgenv().AutoCollect do\n        -- Auto collect code here\n        wait();\n    end;\nend);"
    },
    {
        id: 3,
        title: "Bedwars Aimbot & ESP",
        description: "สคริปต์ช่วยเล็ง และมองทะลุกำแพงสำหรับเกม Bedwars",
        category: "FPS",
        image: "https://tr.rbxcdn.com/c8d3bad9e2b5d9d3c06985d724ff4e6d/768/432/Image/Png",
        downloads: 1500,
        rating: 4.9,
        date: "15/04/2023",
        code: "-- Bedwars Aimbot & ESP Script\nlocal Library = loadstring(game:HttpGet(\"https://raw.githubusercontent.com/example/Bedwars/main/Library.lua\"))();\nlocal Window = Library.CreateLib(\"Bedwars Aimbot & ESP\", \"Synapse\");\nlocal Tab = Window:NewTab(\"Aimbot\");\nlocal Section = Tab:NewSection(\"Settings\");\n\nSection:NewToggle(\"Aimbot\", \"Automatically aims at enemies\", function(state)\n    getgenv().Aimbot = state;\n    while getgenv().Aimbot do\n        -- Aimbot code here\n        wait();\n    end;\nend);"
    },
    {
        id: 4,
        title: "Ninja Legends Auto Farm",
        description: "สคริปต์ออโต้ฟาร์มสำหรับ Ninja Legends ฟาร์มเงินและอัพเกรดอัตโนมัติ",
        category: "Parkour",
        image: "https://tr.rbxcdn.com/f0269c40fadfe6f8e44a1299a9563054/768/432/Image/Png",
        downloads: 876,
        rating: 4.3,
        date: "08/04/2023",
        code: "-- Ninja Legends Auto Farm Script\nlocal Library = loadstring(game:HttpGet(\"https://raw.githubusercontent.com/example/NinjaLegends/main/Library.lua\"))();\nlocal Window = Library.CreateLib(\"Ninja Legends Auto Farm\", \"DarkTheme\");\nlocal Tab = Window:NewTab(\"Auto Farm\");\nlocal Section = Tab:NewSection(\"Farming\");\n\nSection:NewToggle(\"Auto Swing\", \"Automatically swings your sword\", function(state)\n    getgenv().AutoSwing = state;\n    while getgenv().AutoSwing do\n        -- Auto swing code here\n        wait();\n    end;\nend);"
    },
    {
        id: 5,
        title: "Pet Simulator X Auto Farm",
        description: "สคริปต์ออโต้ฟาร์มสำหรับ Pet Simulator X ฟาร์มเพชรและเปิดไข่อัตโนมัติ",
        category: "Simulator",
        image: "https://tr.rbxcdn.com/e9b0640f26e3c602a1f6082f26f09691/768/432/Image/Png",
        downloads: 2100,
        rating: 4.7,
        date: "18/04/2023",
        code: "-- Pet Simulator X Auto Farm Script\nlocal Library = loadstring(game:HttpGet(\"https://raw.githubusercontent.com/example/PetSimX/main/Library.lua\"))();\nlocal Window = Library.CreateLib(\"Pet Simulator X Auto Farm\", \"BloodTheme\");\nlocal Tab = Window:NewTab(\"Auto Farm\");\nlocal Section = Tab:NewSection(\"Farming\");\n\nSection:NewToggle(\"Auto Farm Coins\", \"Automatically farms coins\", function(state)\n    getgenv().AutoFarmCoins = state;\n    while getgenv().AutoFarmCoins do\n        -- Auto farm coins code here\n        wait();\n    end;\nend);"
    },
    {
        id: 6,
        title: "Jailbreak Auto Rob",
        description: "สคริปต์ปล้นอัตโนมัติสำหรับ Jailbreak ปล้นทุกที่โดยไม่ต้องขับรถเอง",
        category: "Adventure",
        image: "https://tr.rbxcdn.com/5aa5b9b7a25c88e1b0f2306f8ab3fdaf/768/432/Image/Png",
        downloads: 1800,
        rating: 4.6,
        date: "14/04/2023",
        code: "-- Jailbreak Auto Rob Script\nlocal Library = loadstring(game:HttpGet(\"https://raw.githubusercontent.com/example/Jailbreak/main/Library.lua\"))();\nlocal Window = Library.CreateLib(\"Jailbreak Auto Rob\", \"LightTheme\");\nlocal Tab = Window:NewTab(\"Auto Rob\");\nlocal Section = Tab:NewSection(\"Robbery\");\n\nSection:NewToggle(\"Auto Rob All\", \"Automatically robs all locations\", function(state)\n    getgenv().AutoRobAll = state;\n    while getgenv().AutoRobAll do\n        -- Auto rob code here\n        wait();\n    end;\nend);"
    }
];

// ข้อมูลผู้ใช้ตัวอย่าง
const adminUsers = [
    {
        id: 1,
        username: "admin",
        displayName: "แอดมิน",
        password: "password123",
        isAdmin: true,
        registerDate: "01/04/2023"
    },
    {
        id: 2,
        username: "user",
        displayName: "ผู้ใช้ทั่วไป",
        password: "user123",
        isAdmin: false,
        registerDate: "05/04/2023"
    }
];

// ฟังก์ชันสำหรับการจัดการแท็บ
function setupTabs() {
    const menuItems = document.querySelectorAll('.admin-menu li');
    const tabs = document.querySelectorAll('.admin-tab');

    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            // ลบคลาส active จากทุกเมนู
            menuItems.forEach(i => i.classList.remove('active'));
            // เพิ่มคลาส active ให้กับเมนูที่คลิก
            this.classList.add('active');

            // ซ่อนทุกแท็บ
            tabs.forEach(tab => tab.classList.remove('active'));

            // แสดงแท็บที่เกี่ยวข้อง
            const tabId = `${this.getAttribute('data-tab')}-tab`;
            document.getElementById(tabId).classList.add('active');
        });
    });

    // ตั้งค่าการคลิกปุ่ม "ดูทั้งหมด"
    const viewAllButtons = document.querySelectorAll('.view-all');
    viewAllButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const tabName = this.getAttribute('data-tab');

            // ลบคลาส active จากทุกเมนู
            menuItems.forEach(i => i.classList.remove('active'));

            // เพิ่มคลาส active ให้กับเมนูที่เกี่ยวข้อง
            document.querySelector(`.admin-menu li[data-tab="${tabName}"]`).classList.add('active');

            // ซ่อนทุกแท็บ
            tabs.forEach(tab => tab.classList.remove('active'));

            // แสดงแท็บที่เกี่ยวข้อง
            document.getElementById(`${tabName}-tab`).classList.add('active');
        });
    });
}

// ฟังก์ชันสำหรับการแสดงข้อมูลสคริปต์
function displayScripts() {
    const tableBody = document.getElementById('scripts-table-body');
    if (!tableBody) return;

    tableBody.innerHTML = '';

    adminScripts.forEach(script => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${script.id}</td>
            <td><img src="${script.image}" alt="${script.title}" width="50" height="30"></td>
            <td>${script.title}</td>
            <td>${script.category}</td>
            <td>${script.date}</td>
            <td>${script.downloads >= 1000 ? (script.downloads / 1000).toFixed(1) + 'K' : script.downloads}</td>
            <td>${script.rating}</td>
            <td>
                <button class="action-btn edit-btn" data-id="${script.id}"><i class="fas fa-edit"></i></button>
                <button class="action-btn delete-btn" data-id="${script.id}"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tableBody.appendChild(row);
    });

    // เพิ่ม event listener สำหรับปุ่มแก้ไขและลบ
    setupScriptActions();
}

// ฟังก์ชันสำหรับการแสดงข้อมูลผู้ใช้
function displayUsers() {
    const tableBody = document.getElementById('users-table-body');
    if (!tableBody) return;

    tableBody.innerHTML = '';

    adminUsers.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.id}</td>
            <td>${user.username}</td>
            <td>${user.displayName}</td>
            <td><span class="status ${user.isAdmin ? 'admin' : 'user'}">${user.isAdmin ? 'แอดมิน' : 'ผู้ใช้'}</span></td>
            <td>${user.registerDate}</td>
            <td>
                <button class="action-btn edit-btn" data-id="${user.id}"><i class="fas fa-edit"></i></button>
                <button class="action-btn delete-btn" data-id="${user.id}"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tableBody.appendChild(row);
    });

    // เพิ่ม event listener สำหรับปุ่มแก้ไขและลบ
    setupUserActions();
}

// ฟังก์ชันสำหรับการจัดการ Modal
function setupModals() {
    // Script Modal
    const scriptModal = document.getElementById('script-modal');
    const addScriptBtn = document.getElementById('add-script-btn');
    const scriptModalClose = scriptModal.querySelector('.admin-modal-close');
    const scriptCancelBtn = scriptModal.querySelector('.cancel-btn');
    const previewImageBtn = document.getElementById('preview-image-btn');
    const imagePreview = document.getElementById('image-preview');
    const scriptImageInput = document.getElementById('script-image');

    // ตั้งค่าเริ่มต้นสำหรับพื้นที่แสดงตัวอย่างรูปภาพ
    imagePreview.classList.add('empty');
    imagePreview.textContent = 'ยังไม่มีรูปภาพตัวอย่าง';

    // ฟังก์ชันแสดงตัวอย่างรูปภาพ
    function previewImage(url) {
        if (!url) {
            imagePreview.innerHTML = 'ยังไม่มีรูปภาพตัวอย่าง';
            imagePreview.classList.add('empty');
            imagePreview.classList.remove('error');
            return;
        }

        // สร้างออบเจ็คต์รูปภาพ
        const img = new Image();

        // เมื่อโหลดรูปภาพสำเร็จ
        img.onload = function() {
            imagePreview.innerHTML = '';
            imagePreview.appendChild(img);
            imagePreview.classList.remove('empty', 'error');
        };

        // เมื่อโหลดรูปภาพไม่สำเร็จ
        img.onerror = function() {
            imagePreview.innerHTML = 'ไม่สามารถโหลดรูปภาพได้ กรุณาตรวจสอบ URL';
            imagePreview.classList.add('error');
            imagePreview.classList.remove('empty');
        };

        // กำหนด URL ของรูปภาพ
        img.src = url;
    }

    // เพิ่ม event listener สำหรับปุ่มแสดงตัวอย่างรูปภาพ
    previewImageBtn.addEventListener('click', function() {
        const imageUrl = scriptImageInput.value.trim();
        previewImage(imageUrl);
    });

    // เพิ่ม event listener สำหรับการเปลี่ยนแปลงในช่อง URL รูปภาพ
    scriptImageInput.addEventListener('input', function() {
        // ถ้ามีการพิมพ์ URL แล้วหยุดพิมพ์ 1 วินาที ให้แสดงตัวอย่างรูปภาพอัตโนมัติ
        clearTimeout(this.timer);
        this.timer = setTimeout(() => {
            const imageUrl = this.value.trim();
            previewImage(imageUrl);
        }, 1000);
    });

    addScriptBtn.addEventListener('click', function() {
        document.getElementById('script-modal-title').textContent = 'เพิ่มสคริปต์ใหม่';
        document.getElementById('script-id').value = '';
        document.getElementById('script-form').reset();
        imagePreview.innerHTML = 'ยังไม่มีรูปภาพตัวอย่าง';
        imagePreview.classList.add('empty');
        imagePreview.classList.remove('error');
        scriptModal.classList.add('show');
    });

    scriptModalClose.addEventListener('click', function() {
        scriptModal.classList.remove('show');
    });

    scriptCancelBtn.addEventListener('click', function() {
        scriptModal.classList.remove('show');
    });

    // Category Modal
    const categoryModal = document.getElementById('category-modal');
    const addCategoryBtn = document.getElementById('add-category-btn');
    const categoryModalClose = categoryModal.querySelector('.admin-modal-close');
    const categoryCancelBtn = categoryModal.querySelector('.cancel-btn');

    addCategoryBtn.addEventListener('click', function() {
        document.getElementById('category-modal-title').textContent = 'เพิ่มหมวดหมู่ใหม่';
        document.getElementById('category-id').value = '';
        document.getElementById('category-form').reset();
        categoryModal.classList.add('show');
    });

    categoryModalClose.addEventListener('click', function() {
        categoryModal.classList.remove('show');
    });

    categoryCancelBtn.addEventListener('click', function() {
        categoryModal.classList.remove('show');
    });

    // User Modal
    const userModal = document.getElementById('user-modal');
    const addUserBtn = document.getElementById('add-user-btn');
    const userModalClose = userModal.querySelector('.admin-modal-close');
    const userCancelBtn = userModal.querySelector('.cancel-btn');

    addUserBtn.addEventListener('click', function() {
        document.getElementById('user-modal-title').textContent = 'เพิ่มผู้ใช้ใหม่';
        document.getElementById('user-id').value = '';
        document.getElementById('user-form').reset();
        userModal.classList.add('show');
    });

    userModalClose.addEventListener('click', function() {
        userModal.classList.remove('show');
    });

    userCancelBtn.addEventListener('click', function() {
        userModal.classList.remove('show');
    });
}

// ฟังก์ชันสำหรับการจัดการฟอร์ม
function setupForms() {
    // Script Form
    const scriptForm = document.getElementById('script-form');
    scriptForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const scriptId = document.getElementById('script-id').value;
        const title = document.getElementById('script-title').value;
        const description = document.getElementById('script-description').value;
        const category = document.getElementById('script-category').value;
        const image = document.getElementById('script-image').value;
        const code = document.getElementById('script-code').value;

        // ตรวจสอบว่า URL รูปภาพถูกต้องหรือไม่
        const imagePreview = document.getElementById('image-preview');
        if (imagePreview.classList.contains('error')) {
            alert('กรุณาตรวจสอบ URL รูปภาพให้ถูกต้อง');
            return;
        }

        if (scriptId) {
            // แก้ไขสคริปต์ที่มีอยู่
            const index = adminScripts.findIndex(s => s.id == scriptId);
            if (index !== -1) {
                adminScripts[index].title = title;
                adminScripts[index].description = description;
                adminScripts[index].category = category;
                adminScripts[index].image = image;
                adminScripts[index].code = code;

                // อัพเดตตารางสคริปต์ล่าสุดในแดชบอร์ด
                updateRecentScripts();

                // แสดงข้อความสำเร็จ
                showNotification('แก้ไขสคริปต์สำเร็จ', 'success');
            }
        } else {
            // เพิ่มสคริปต์ใหม่
            const newId = adminScripts.length > 0 ? Math.max(...adminScripts.map(s => s.id)) + 1 : 1;
            const newScript = {
                id: newId,
                title,
                description,
                category,
                image,
                code,
                downloads: 0,
                rating: 0,
                date: new Date().toLocaleDateString('en-GB')
            };

            // เพิ่มสคริปต์ใหม่ในรายการ
            adminScripts.unshift(newScript);

            // อัพเดตตารางสคริปต์ล่าสุดในแดชบอร์ด
            updateRecentScripts();

            // แสดงข้อความสำเร็จ
            showNotification('เพิ่มสคริปต์ใหม่สำเร็จ', 'success');
        }

        // อัพเดตตาราง
        displayScripts();

        // ปิด Modal
        document.getElementById('script-modal').classList.remove('show');
    });

    // User Form
    const userForm = document.getElementById('user-form');
    userForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const userId = document.getElementById('user-id').value;
        const username = document.getElementById('user-username').value;
        const displayName = document.getElementById('user-displayname').value;
        const password = document.getElementById('user-password').value;
        const role = document.getElementById('user-role').value;

        // ตรวจสอบว่าชื่อผู้ใช้ซ้ำกันหรือไม่
        const isUsernameExists = adminUsers.some(u => u.username === username && u.id != userId);
        if (isUsernameExists) {
            showNotification('ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว กรุณาใช้ชื่อผู้ใช้อื่น', 'error');
            return;
        }

        // ตรวจสอบว่ากำลังแก้ไขผู้ใช้ที่กำลังใช้งานอยู่หรือไม่
        const currentUsername = localStorage.getItem('username');
        const isCurrentUser = userId && adminUsers.find(u => u.id == userId)?.username === currentUsername;

        if (userId) {
            // แก้ไขผู้ใช้ที่มีอยู่
            const index = adminUsers.findIndex(u => u.id == userId);
            if (index !== -1) {
                // ถ้าเป็นผู้ใช้ปัจจุบัน ให้อัพเดตข้อมูลใน localStorage ด้วย
                if (isCurrentUser) {
                    localStorage.setItem('displayName', displayName);
                    localStorage.setItem('isAdmin', role === 'admin' ? 'true' : 'false');
                }

                adminUsers[index].username = username;
                adminUsers[index].displayName = displayName;
                adminUsers[index].password = password;
                adminUsers[index].isAdmin = role === 'admin';

                // อัพเดตข้อมูลผู้ใช้ในหน้าแอดมิน
                updateAdminInfo();
                updateRecentUsers();

                showNotification('แก้ไขผู้ใช้สำเร็จ', 'success');
            }
        } else {
            // เพิ่มผู้ใช้ใหม่
            const newId = adminUsers.length > 0 ? Math.max(...adminUsers.map(u => u.id)) + 1 : 1;
            const newUser = {
                id: newId,
                username,
                displayName,
                password,
                isAdmin: role === 'admin',
                registerDate: new Date().toLocaleDateString('en-GB')
            };

            // เพิ่มผู้ใช้ใหม่ในรายการ
            adminUsers.unshift(newUser);

            // อัพเดตผู้ใช้ล่าสุด
            updateRecentUsers();

            showNotification('เพิ่มผู้ใช้ใหม่สำเร็จ', 'success');
        }

        // อัพเดตตาราง
        displayUsers();

        // ปิด Modal
        document.getElementById('user-modal').classList.remove('show');
    });

    // Category Form
    const categoryForm = document.getElementById('category-form');
    if (categoryForm) {
        categoryForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const categoryId = document.getElementById('category-id').value;
            const categoryName = document.getElementById('category-name').value;
            const categoryIcon = document.getElementById('category-icon').value;

            // จำลองการบันทึกหมวดหมู่
            showNotification(`บันทึกหมวดหมู่ "${categoryName}" สำเร็จ`, 'success');

            // ปิด Modal
            document.getElementById('category-modal').classList.remove('show');
        });
    }

    // Settings Form
    const settingsForm = document.getElementById('settings-form');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // ดึงข้อมูลจากฟอร์ม
            const siteTitle = document.getElementById('site-title').value;
            const siteDescription = document.getElementById('site-description').value;
            const homeTitle = document.getElementById('home-title').value;
            const homeSubtitle = document.getElementById('home-subtitle').value;

            // จำลองการบันทึกการตั้งค่า
            showNotification('บันทึกการตั้งค่าสำเร็จ', 'success');

            // อัพเดตชื่อเว็บไซต์และคำอธิบายในหน้าเว็บ
            const titleElements = document.querySelectorAll('title');
            titleElements.forEach(el => {
                if (el.textContent.includes('Roblox Scripts')) {
                    el.textContent = el.textContent.replace('Roblox Scripts', siteTitle);
                }
            });

            // อัพเดตหัวข้อและคำอธิบายในหน้าหลัก
            const heroTitle = document.querySelector('.hero-section h1');
            const heroSubtitle = document.querySelector('.hero-section p');

            if (heroTitle && homeTitle) {
                heroTitle.textContent = homeTitle;
            }

            if (heroSubtitle && homeSubtitle) {
                heroSubtitle.textContent = homeSubtitle;
            }
        });
    }
}

// ฟังก์ชันสำหรับการแสดงข้อความแจ้งเตือน
 function showNotification(message, type = 'info') {
    // สร้างองค์ประกอบข้อความแจ้งเตือน
    const notification = document.createElement('div');
    notification.className = `admin-notification ${type}`;

    // กำหนดไอคอนตามประเภท
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-circle';
    if (type === 'warning') icon = 'exclamation-triangle';

    notification.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
        <button class="close-notification"><i class="fas fa-times"></i></button>
    `;

    // เพิ่มข้อความแจ้งเตือนในหน้าเว็บ
    document.body.appendChild(notification);

    // เพิ่ม event listener สำหรับปุ่มปิด
    const closeBtn = notification.querySelector('.close-notification');
    closeBtn.addEventListener('click', function() {
        notification.remove();
    });

    // ตั้งเวลาให้ข้อความแจ้งเตือนหายไปหลังจาก 5 วินาที
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 500);
    }, 5000);
}

// ฟังก์ชันสำหรับการอัพเดตตารางสคริปต์ล่าสุด
function updateRecentScripts() {
    const recentScriptsTable = document.querySelector('.admin-recent-scripts .admin-table tbody');
    if (!recentScriptsTable) return;

    // เรียงลำดับตามวันที่ (ล่าสุดก่อน)
    const sortedScripts = [...adminScripts].sort((a, b) => {
        const dateA = parseDate(a.date);
        const dateB = parseDate(b.date);
        return dateB - dateA;
    }).slice(0, 4); // แสดงแค่ 4 รายการ

    // ล้างตารางและเพิ่มข้อมูลใหม่
    recentScriptsTable.innerHTML = '';

    sortedScripts.forEach(script => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${script.title}</td>
            <td>${script.category}</td>
            <td>${script.date}</td>
            <td>${script.downloads >= 1000 ? (script.downloads / 1000).toFixed(1) + 'K' : script.downloads}</td>
        `;
        recentScriptsTable.appendChild(row);
    });
}

// ฟังก์ชันสำหรับการจัดการปุ่มแก้ไขและลบสคริปต์
function setupScriptActions() {
    // ปุ่มแก้ไข
    const editButtons = document.querySelectorAll('#scripts-table-body .edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const scriptId = this.getAttribute('data-id');
            const script = adminScripts.find(s => s.id == scriptId);

            if (script) {
                document.getElementById('script-modal-title').textContent = 'แก้ไขสคริปต์';
                document.getElementById('script-id').value = script.id;
                document.getElementById('script-title').value = script.title;
                document.getElementById('script-description').value = script.description;
                document.getElementById('script-category').value = script.category;
                document.getElementById('script-image').value = script.image;
                document.getElementById('script-code').value = script.code;

                // แสดงตัวอย่างรูปภาพ
                const imagePreview = document.getElementById('image-preview');
                const img = new Image();
                img.onload = function() {
                    imagePreview.innerHTML = '';
                    imagePreview.appendChild(img);
                    imagePreview.classList.remove('empty', 'error');
                };
                img.onerror = function() {
                    imagePreview.innerHTML = 'ไม่สามารถโหลดรูปภาพได้ กรุณาตรวจสอบ URL';
                    imagePreview.classList.add('error');
                    imagePreview.classList.remove('empty');
                };
                img.src = script.image;

                document.getElementById('script-modal').classList.add('show');
            }
        });
    });

    // ปุ่มลบ
    const deleteButtons = document.querySelectorAll('#scripts-table-body .delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const scriptId = this.getAttribute('data-id');

            if (confirm('คุณแน่ใจหรือไม่ที่จะลบสคริปต์นี้?')) {
                const index = adminScripts.findIndex(s => s.id == scriptId);
                if (index !== -1) {
                    adminScripts.splice(index, 1);
                    displayScripts();
                    updateRecentScripts();
                    showNotification('ลบสคริปต์สำเร็จ', 'success');
                }
            }
        });
    });
}

// ฟังก์ชันสำหรับการจัดการปุ่มแก้ไขและลบผู้ใช้
function setupUserActions() {
    // ปุ่มแก้ไข
    const editButtons = document.querySelectorAll('#users-table-body .edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const user = adminUsers.find(u => u.id == userId);

            if (user) {
                document.getElementById('user-modal-title').textContent = 'แก้ไขผู้ใช้';
                document.getElementById('user-id').value = user.id;
                document.getElementById('user-username').value = user.username;
                document.getElementById('user-displayname').value = user.displayName;
                document.getElementById('user-password').value = user.password;
                document.getElementById('user-role').value = user.isAdmin ? 'admin' : 'user';

                document.getElementById('user-modal').classList.add('show');
            }
        });
    });

    // ปุ่มลบ
    const deleteButtons = document.querySelectorAll('#users-table-body .delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const user = adminUsers.find(u => u.id == userId);

            // ไม่อนุญาตให้ลบผู้ใช้ที่กำลังใช้งานอยู่
            const currentUsername = localStorage.getItem('username');
            if (user && user.username === currentUsername) {
                showNotification('ไม่สามารถลบผู้ใช้ที่กำลังใช้งานอยู่ได้', 'error');
                return;
            }

            if (confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้?')) {
                const index = adminUsers.findIndex(u => u.id == userId);
                if (index !== -1) {
                    adminUsers.splice(index, 1);
                    displayUsers();
                    updateRecentUsers();
                    showNotification('ลบผู้ใช้สำเร็จ', 'success');
                }
            }
        });
    });
}

// ฟังก์ชันสำหรับการอัพเดตข้อมูลผู้ใช้ในหน้าแอดมิน
function updateAdminInfo() {
    const adminNameElement = document.getElementById('admin-name');
    if (adminNameElement) {
        adminNameElement.textContent = localStorage.getItem('displayName') || 'แอดมิน';
    }
}

// ข้อมูลสถิติแบบ Real-time
let stats = {
    totalDownloads: 0,
    dailyViews: 0,
    lastViewTime: Date.now(),
    lastUsers: []
};

// ฟังก์ชันสำหรับการอัพเดตข้อมูลแบบ Real-time
function updateRealTimeStats() {
    // คำนวณยอดดาวน์โหลดทั้งหมด
    stats.totalDownloads = adminScripts.reduce((total, script) => total + script.downloads, 0);

    // จำลองการเพิ่มยอดเข้าชมแบบสุ่ม
    if (Date.now() - stats.lastViewTime > 5000) { // ทุก 5 วินาที
        stats.dailyViews += Math.floor(Math.random() * 3); // เพิ่ม 0-2 วิว
        stats.lastViewTime = Date.now();
    }

    // อัพเดตข้อมูลในหน้าแดชบอร์ด
    const totalDownloadsElement = document.querySelector('.stat-card:nth-child(3) .stat-number');
    const dailyViewsElement = document.querySelector('.stat-card:nth-child(4) .stat-number');

    if (totalDownloadsElement) {
        totalDownloadsElement.textContent = stats.totalDownloads >= 1000 ?
            (stats.totalDownloads / 1000).toFixed(1) + 'K' :
            stats.totalDownloads.toString();
    }

    if (dailyViewsElement) {
        dailyViewsElement.textContent = stats.dailyViews.toString();
    }
}

// ฟังก์ชันสำหรับการจำลองผู้ใช้ใหม่แบบ Real-time
function simulateNewUsers() {
    // รายชื่อผู้ใช้ตัวอย่าง
    const sampleNames = [
        { username: 'user123', displayName: 'ผู้ใช้ใหม่', isAdmin: false },
        { username: 'gamer456', displayName: 'เกมเมอร์', isAdmin: false },
        { username: 'scripter789', displayName: 'นักเขียนสคริปต์', isAdmin: false },
        { username: 'robloxfan', displayName: 'แฟนรอบล็อกซ์', isAdmin: false },
        { username: 'hackerpro', displayName: 'โปรแกรมเมอร์', isAdmin: false }
    ];

    // สุ่มเลือกผู้ใช้ใหม่
    if (Math.random() < 0.3 && stats.lastUsers.length < 5) { // 30% โอกาสที่จะมีผู้ใช้ใหม่
        const randomUser = sampleNames[Math.floor(Math.random() * sampleNames.length)];
        const today = new Date();
        const formattedDate = `${today.getDate().toString().padStart(2, '0')}/${(today.getMonth() + 1).toString().padStart(2, '0')}/${today.getFullYear()}`;

        // สร้างผู้ใช้ใหม่
        const newUser = {
            id: adminUsers.length + stats.lastUsers.length + 1,
            username: randomUser.username + Math.floor(Math.random() * 100),
            displayName: randomUser.displayName,
            password: 'password123',
            isAdmin: randomUser.isAdmin,
            registerDate: formattedDate
        };

        // เพิ่มผู้ใช้ใหม่ในรายการ
        stats.lastUsers.unshift(newUser);

        // จำกัดจำนวนผู้ใช้ใหม่ให้ไม่เกิน 5 คน
        if (stats.lastUsers.length > 5) {
            stats.lastUsers.pop();
        }

        // อัพเดตตารางผู้ใช้ล่าสุด
        updateRecentUsers();
    }
}

// ฟังก์ชันอัพเดตตารางผู้ใช้ล่าสุด
 function updateRecentUsers() {
    const recentUsersTable = document.querySelector('.admin-recent-users .admin-table tbody');
    if (!recentUsersTable) return;

    // รวมผู้ใช้ทั้งหมด
    const allUsers = [...adminUsers, ...stats.lastUsers];

    // เรียงลำดับตามวันที่สมัคร (ล่าสุดก่อน)
    const sortedUsers = allUsers.sort((a, b) => {
        const dateA = parseDate(a.registerDate);
        const dateB = parseDate(b.registerDate);
        return dateB - dateA;
    }).slice(0, 4); // แสดงแค่ 4 คน

    // ล้างตารางและเพิ่มข้อมูลใหม่
    recentUsersTable.innerHTML = '';

    sortedUsers.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.username}</td>
            <td>${user.displayName}</td>
            <td><span class="status ${user.isAdmin ? 'admin' : 'user'}">${user.isAdmin ? 'แอดมิน' : 'ผู้ใช้'}</span></td>
            <td>${user.registerDate}</td>
        `;
        recentUsersTable.appendChild(row);
    });
}

// ฟังก์ชันสำหรับการแปลงวันที่
function parseDate(dateStr) {
    const parts = dateStr.split('/');
    // วันที่ในรูปแบบ DD/MM/YYYY
    return new Date(parts[2], parts[1] - 1, parts[0]);
}

// เริ่มต้นการทำงานเมื่อโหลดหน้าเว็บ
document.addEventListener('DOMContentLoaded', function() {
    // ตรวจสอบว่าผู้ใช้เป็นแอดมินหรือไม่
    const isAdmin = localStorage.getItem('isAdmin') === 'true';
    if (!isAdmin) {
        alert('คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        window.location.href = 'home.html';
        return;
    }

    // อัพเดตข้อมูลผู้ใช้
    updateAdminInfo();

    // ตั้งค่าแท็บ
    setupTabs();

    // แสดงข้อมูลสคริปต์
    displayScripts();

    // แสดงข้อมูลผู้ใช้
    displayUsers();

    // ตั้งค่า Modal
    setupModals();

    // ตั้งค่าฟอร์ม
    setupForms();

    // เริ่มต้นการอัพเดตข้อมูลแบบ Real-time
    updateRealTimeStats();
    updateRecentUsers();

    // ตั้งเวลาอัพเดตข้อมูลแบบ Real-time ทุก 3 วินาที
    setInterval(function() {
        updateRealTimeStats();
        simulateNewUsers();
    }, 3000);
});
