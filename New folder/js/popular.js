// ฟังก์ชันสำหรับการแสดงสคริปต์ยอดนิยม
document.addEventListener('DOMContentLoaded', function() {
    // ตรวจสอบว่าอยู่ในหน้า popular.html
    if (!window.location.pathname.includes('popular.html')) return;

    // แสดงสคริปต์ยอดนิยมทั้งหมด
    displayTopScripts();

    // แสดงสคริปต์ยอดนิยมตามหมวดหมู่
    displayPopularByCategory();

    // ฟังก์ชันสำหรับการแสดงสคริปต์ยอดนิยมทั้งหมด
    function displayTopScripts() {
        const container = document.getElementById('top-scripts-container');
        if (!container) return;

        // ใช้ DataManager ในการดึงสคริปต์ยอดนิยม
        const topScripts = DataManager.getPopularScripts(10);

        // แสดงสคริปต์ยอดนิยม
        topScripts.forEach((script, index) => {
            const scriptCard = document.createElement('div');
            scriptCard.className = 'popular-script-card';

            scriptCard.innerHTML = `
                <div class="rank">${index + 1}</div>
                <div class="popular-script-image">
                    <img src="${script.image}" alt="${script.title}">
                </div>
                <div class="popular-script-info">
                    <h3>${script.title}</h3>
                    <p>${script.description}</p>
                    <div class="popular-script-meta">
                        <span><i class="fas fa-download"></i> ${script.downloads >= 1000 ? (script.downloads / 1000).toFixed(1) + 'K' : script.downloads}</span>
                        <span><i class="fas fa-star"></i> ${script.rating}</span>
                        <span><i class="fas fa-tag"></i> ${script.category}</span>
                    </div>
                </div>
                <a href="script-detail.html?id=${script.id}" class="popular-script-button" data-id="${script.id}">ดูรายละเอียด</a>
            `;

            container.appendChild(scriptCard);
        });
    }

    // ฟังก์ชันสำหรับการแสดงสคริปต์ยอดนิยมตามหมวดหมู่
    function displayPopularByCategory() {
        // หมวดหมู่ที่ต้องการแสดง
        const categories = ['Fighting', 'Simulator', 'FPS'];

        categories.forEach(category => {
            const container = document.getElementById(`${category.toLowerCase()}-scripts`);
            if (!container) return;

            // ใช้ DataManager ในการดึงสคริปต์ยอดนิยมตามหมวดหมู่
            const categoryScripts = DataManager.getPopularScripts(3, category);

            if (categoryScripts.length === 0) {
                container.innerHTML = '<div class="no-results">ไม่พบสคริปต์ในหมวดหมู่นี้</div>';
                return;
            }

            // แสดงสคริปต์ยอดนิยมตามหมวดหมู่
            categoryScripts.forEach(script => {
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
                        <a href="script-detail.html?id=${script.id}" class="script-button" data-id="${script.id}">ดูรายละเอียด</a>
                    </div>
                `;

                container.appendChild(scriptCard);
            });
        });
    }
});
