// ฟังก์ชันสำหรับการกรองสคริปต์ตามหมวดหมู่
document.addEventListener('DOMContentLoaded', function() {
    // ตรวจสอบว่าอยู่ในหน้า scripts.html
    if (!window.location.pathname.includes('scripts.html')) return;
    
    // ตัวแปรสำหรับการจัดการหน้า
    let currentPage = 1;
    const scriptsPerPage = 6;
    let filteredScripts = [];
    let currentCategory = 'all';
    let currentSort = 'newest';
    
    // ดึงข้อมูลจาก URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const categoryParam = urlParams.get('category');
    if (categoryParam) {
        currentCategory = categoryParam;
    }
    
    // ตั้งค่าปุ่มกรอง
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        // ตั้งค่าปุ่มที่ active ตาม URL parameter
        if (button.getAttribute('data-category') === currentCategory) {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
        }
        
        button.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            
            // ลบคลาส active จากทุกปุ่ม
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // เพิ่มคลาส active ให้กับปุ่มที่คลิก
            this.classList.add('active');
            
            // อัพเดตหมวดหมู่ปัจจุบัน
            currentCategory = category;
            
            // รีเซ็ตหน้าปัจจุบัน
            currentPage = 1;
            
            // กรองและแสดงสคริปต์
            filterAndDisplayScripts();
            
            // อัพเดต URL parameter
            updateUrlParameter('category', category);
        });
    });
    
    // ตั้งค่าตัวเลือกการเรียงลำดับ
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            currentSort = this.value;
            filterAndDisplayScripts();
        });
    }
    
    // ตั้งค่าปุ่มเปลี่ยนหน้า
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    
    if (prevPageBtn && nextPageBtn) {
        prevPageBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                filterAndDisplayScripts();
                window.scrollTo(0, 0);
            }
        });
        
        nextPageBtn.addEventListener('click', function() {
            const totalPages = Math.ceil(filteredScripts.length / scriptsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                filterAndDisplayScripts();
                window.scrollTo(0, 0);
            }
        });
    }
    
    // ฟังก์ชันสำหรับการกรองและแสดงสคริปต์
    function filterAndDisplayScripts() {
        // กรองสคริปต์ตามหมวดหมู่
        if (currentCategory === 'all') {
            filteredScripts = [...sampleScripts, ...popularScripts];
        } else {
            filteredScripts = [...sampleScripts, ...popularScripts].filter(script => 
                script.category === currentCategory
            );
        }
        
        // เรียงลำดับสคริปต์
        sortScripts();
        
        // อัพเดตหัวข้อ
        updateScriptsTitle();
        
        // แสดงสคริปต์
        displayScripts();
        
        // อัพเดตการแบ่งหน้า
        updatePagination();
    }
    
    // ฟังก์ชันสำหรับการเรียงลำดับสคริปต์
    function sortScripts() {
        switch (currentSort) {
            case 'newest':
                // เรียงตามวันที่ (ล่าสุดก่อน)
                filteredScripts.sort((a, b) => {
                    const dateA = parseDate(a.date);
                    const dateB = parseDate(b.date);
                    return dateB - dateA;
                });
                break;
            case 'popular':
                // เรียงตามจำนวนดาวน์โหลด (มากไปน้อย)
                filteredScripts.sort((a, b) => b.downloads - a.downloads);
                break;
            case 'rating':
                // เรียงตามคะแนน (มากไปน้อย)
                filteredScripts.sort((a, b) => b.rating - a.rating);
                break;
        }
    }
    
    // ฟังก์ชันสำหรับการแปลงวันที่
    function parseDate(dateStr) {
        const parts = dateStr.split('/');
        // วันที่ในรูปแบบ DD/MM/YYYY
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }
    
    // ฟังก์ชันสำหรับการอัพเดตหัวข้อ
    function updateScriptsTitle() {
        const scriptsTitle = document.getElementById('scripts-title');
        if (scriptsTitle) {
            if (currentCategory === 'all') {
                scriptsTitle.textContent = 'สคริปต์ทั้งหมด';
            } else {
                scriptsTitle.textContent = `สคริปต์หมวดหมู่ ${currentCategory}`;
            }
        }
    }
    
    // ฟังก์ชันสำหรับการแสดงสคริปต์
    function displayScripts() {
        const container = document.getElementById('scripts-container');
        if (!container) return;
        
        // คำนวณสคริปต์ที่จะแสดงในหน้าปัจจุบัน
        const startIndex = (currentPage - 1) * scriptsPerPage;
        const endIndex = startIndex + scriptsPerPage;
        const scriptsToShow = filteredScripts.slice(startIndex, endIndex);
        
        // ล้างเนื้อหาเดิม
        container.innerHTML = '';
        
        if (scriptsToShow.length === 0) {
            container.innerHTML = '<div class="no-results">ไม่พบสคริปต์ในหมวดหมู่นี้</div>';
            return;
        }
        
        // แสดงสคริปต์
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
    
    // ฟังก์ชันสำหรับการอัพเดตการแบ่งหน้า
    function updatePagination() {
        const totalPages = Math.ceil(filteredScripts.length / scriptsPerPage);
        const pageNumbers = document.getElementById('page-numbers');
        const prevPageBtn = document.getElementById('prev-page');
        const nextPageBtn = document.getElementById('next-page');
        
        if (!pageNumbers || !prevPageBtn || !nextPageBtn) return;
        
        // อัพเดตปุ่มก่อนหน้า/ถัดไป
        prevPageBtn.disabled = currentPage <= 1;
        nextPageBtn.disabled = currentPage >= totalPages;
        
        // แสดงหมายเลขหน้า
        pageNumbers.innerHTML = '';
        
        // แสดงหมายเลขหน้าไม่เกิน 5 หน้า
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const pageNumber = document.createElement('span');
            pageNumber.className = `page-number${i === currentPage ? ' active' : ''}`;
            pageNumber.textContent = i;
            
            pageNumber.addEventListener('click', function() {
                currentPage = i;
                filterAndDisplayScripts();
                window.scrollTo(0, 0);
            });
            
            pageNumbers.appendChild(pageNumber);
        }
    }
    
    // ฟังก์ชันสำหรับการอัพเดต URL parameter
    function updateUrlParameter(key, value) {
        const url = new URL(window.location.href);
        if (value === 'all') {
            url.searchParams.delete(key);
        } else {
            url.searchParams.set(key, value);
        }
        window.history.replaceState({}, '', url);
    }
    
    // เริ่มต้นกรองและแสดงสคริปต์
    filterAndDisplayScripts();
});
