// ใช้ข้อมูลจาก DataManager แทนข้อมูลที่กำหนดไว้แบบคงที่

// ฟังก์ชันค้นหาสคริปต์
function searchScripts(query, category = 'all') {
    // ใช้ DataManager ในการค้นหาสคริปต์
    return DataManager.searchScripts(query, category);
}

// ฟังก์ชันแสดงผลการค้นหา
function displaySearchResults(results, container = null, limit = null) {
    // ถ้าไม่ระบุ container ให้ใช้ scripts-container เป็นค่าเริ่มต้น
    if (!container) {
        container = document.getElementById('scripts-container');
    }

    if (!container) return;

    // ล้างเนื้อหาเดิม
    container.innerHTML = '';

    if (results.length === 0) {
        container.innerHTML = '<div class="no-results">ไม่พบสคริปต์ที่ค้นหา</div>';
        return;
    }

    // จำกัดจำนวนผลลัพธ์ถ้ามีการระบุ
    const scriptsToShow = limit ? results.slice(0, limit) : results;

    // แสดงผลการค้นหา
    scriptsToShow.forEach(script => {
        const scriptCard = document.createElement('div');
        scriptCard.className = 'script-card';

        scriptCard.innerHTML = `
            <div class="script-image">
                <img src="${script.image}" alt="${script.title}">
                <div class="script-category">${script.category}</div>
            </div>
            <div class="script-info">
                <h3>${script.title}</h3>
                <p class="script-description">${script.description}</p>
                <div class="script-meta">
                    <span><i class="fas fa-download"></i> ${script.downloads >= 1000 ? (script.downloads / 1000).toFixed(1) + 'K' : script.downloads}</span>
                    <span><i class="fas fa-star"></i> ${script.rating}</span>
                    <span><i class="fas fa-calendar"></i> ${script.date}</span>
                </div>
                <a href="#" class="script-button" data-id="${script.id}">ดูรายละเอียด</a>
            </div>
        `;

        container.appendChild(scriptCard);
    });
}

// จัดการการส่งฟอร์มค้นหา
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');

    // ตรวจสอบ URL parameters สำหรับหน้า scripts.html
    if (window.location.pathname.includes('scripts.html')) {
        const urlParams = new URLSearchParams(window.location.search);
        const categoryParam = urlParams.get('category');

        // ถ้ามีการระบุหมวดหมู่ใน URL ให้แสดงสคริปต์ตามหมวดหมู่
        if (categoryParam) {
            const results = searchScripts('', categoryParam);
            displaySearchResults(results);

            // เปลี่ยนหัวข้อส่วนสคริปต์เป็นชื่อหมวดหมู่
            const sectionHeader = document.querySelector('.scripts-section .section-header h2');
            if (sectionHeader) {
                sectionHeader.textContent = `สคริปต์หมวดหมู่ ${categoryParam}`;
            }

            // ตั้งค่าปุ่มกรองที่ active
            const filterButtons = document.querySelectorAll('.filter-btn');
            filterButtons.forEach(button => {
                button.classList.remove('active');
                if (button.getAttribute('data-category') === categoryParam) {
                    button.classList.add('active');
                }
            });
        }
    }

    // แสดงสคริปต์ในหน้าหลัก
    if (window.location.pathname.includes('home.html') || window.location.pathname === '/' || window.location.pathname.endsWith('/')) {
        const container = document.getElementById('scripts-container');
        if (container) {
            // แสดงสคริปต์ล่าสุด 6 รายการ
            const latestScripts = DataManager.getLatestScripts(6);
            displaySearchResults(latestScripts, container);
        }
    }

    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = searchInput.value.trim();

            if (query) {
                // ตรวจสอบหมวดหมู่ที่กำลังกรองอยู่ (ถ้าอยู่ในหน้า scripts.html)
                let category = 'all';
                if (window.location.pathname.includes('scripts.html')) {
                    const activeFilterBtn = document.querySelector('.filter-btn.active');
                    if (activeFilterBtn) {
                        category = activeFilterBtn.getAttribute('data-category');
                    }
                }

                const results = searchScripts(query, category);
                displaySearchResults(results);

                // เปลี่ยนหัวข้อส่วนสคริปต์เป็น "ผลการค้นหา"
                const sectionHeader = document.querySelector('.scripts-section .section-header h2');
                if (sectionHeader) {
                    sectionHeader.textContent = `ผลการค้นหาสำหรับ "${query}"`;
                }
            }
        });
    }

    // เพิ่ม event listener สำหรับปุ่มดูรายละเอียด
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('script-button') || e.target.classList.contains('popular-script-button')) {
            e.preventDefault();
            const scriptId = parseInt(e.target.getAttribute('data-id'));
            if (scriptId) {
                // เปิดหน้ารายละเอียดสคริปต์
                window.location.href = `script-detail.html?id=${scriptId}`;
            }
        }
    });
});
