// ฟังก์ชันสำหรับการแสดงรายละเอียดสคริปต์
document.addEventListener('DOMContentLoaded', function() {
    // ดึง ID ของสคริปต์จาก URL
    const urlParams = new URLSearchParams(window.location.search);
    const scriptId = parseInt(urlParams.get('id'));
    
    if (!scriptId) {
        // ถ้าไม่มี ID ให้กลับไปหน้าสคริปต์
        window.location.href = 'scripts.html';
        return;
    }
    
    // โหลดข้อมูล
    const data = DataManager.loadData();
    
    // ค้นหาสคริปต์ตาม ID
    const script = data.scripts.find(s => s.id === scriptId);
    
    if (!script) {
        // ถ้าไม่พบสคริปต์ให้กลับไปหน้าสคริปต์
        window.location.href = 'scripts.html';
        return;
    }
    
    // เพิ่มยอดเข้าชม
    DataManager.incrementViews(scriptId);
    
    // แสดงข้อมูลสคริปต์
    displayScriptDetails(script);
    
    // แสดงสคริปต์ที่เกี่ยวข้อง
    displayRelatedScripts(script);
    
    // ตั้งค่าปุ่มดาวน์โหลด
    setupDownloadButton(script);
    
    // ตั้งค่าปุ่มคัดลอกโค้ด
    setupCopyButtons(script);
});

// ฟังก์ชันสำหรับการแสดงรายละเอียดสคริปต์
function displayScriptDetails(script) {
    // แสดงชื่อสคริปต์
    document.getElementById('script-title').textContent = script.title;
    document.title = `${script.title} - Roblox Scripts`;
    
    // แสดงหมวดหมู่
    document.getElementById('script-category').textContent = script.category;
    
    // แสดงวันที่
    document.getElementById('script-date').textContent = script.date;
    
    // แสดงยอดดาวน์โหลด
    document.getElementById('script-downloads').textContent = script.downloads >= 1000 ? 
        (script.downloads / 1000).toFixed(1) + 'K' : 
        script.downloads;
    
    // แสดงยอดเข้าชม
    document.getElementById('script-views').textContent = script.views >= 1000 ? 
        (script.views / 1000).toFixed(1) + 'K' : 
        script.views;
    
    // แสดงคะแนน
    document.getElementById('script-rating').textContent = script.rating.toFixed(1);
    
    // แสดงรูปภาพ
    document.getElementById('script-image').src = script.image;
    document.getElementById('script-image').alt = script.title;
    
    // แสดงคำอธิบาย
    document.getElementById('script-description').textContent = script.description;
    
    // แสดงโค้ด
    document.getElementById('script-code').textContent = script.code;
    
    // ใช้ highlight.js เพื่อทำให้โค้ดสวยงาม
    hljs.highlightAll();
    
    // แสดงแท็ก
    const tagsContainer = document.getElementById('script-tags');
    tagsContainer.innerHTML = '';
    
    if (script.tags && script.tags.length > 0) {
        script.tags.forEach(tag => {
            const tagElement = document.createElement('span');
            tagElement.className = 'tag';
            tagElement.textContent = tag;
            tagsContainer.appendChild(tagElement);
        });
    } else {
        tagsContainer.innerHTML = '<span class="no-tags">ไม่มีแท็ก</span>';
    }
}

// ฟังก์ชันสำหรับการแสดงสคริปต์ที่เกี่ยวข้อง
function displayRelatedScripts(currentScript) {
    const data = DataManager.loadData();
    
    // ค้นหาสคริปต์ในหมวดหมู่เดียวกัน (ยกเว้นสคริปต์ปัจจุบัน)
    let relatedScripts = data.scripts.filter(script => 
        script.published && 
        script.category === currentScript.category && 
        script.id !== currentScript.id
    );
    
    // ถ้าไม่มีสคริปต์ในหมวดหมู่เดียวกัน ให้ใช้สคริปต์ยอดนิยมแทน
    if (relatedScripts.length === 0) {
        relatedScripts = data.scripts.filter(script => 
            script.published && 
            script.id !== currentScript.id
        );
        
        // เรียงตามยอดดาวน์โหลด (มากไปน้อย)
        relatedScripts.sort((a, b) => b.downloads - a.downloads);
    }
    
    // จำกัดจำนวนสคริปต์ที่เกี่ยวข้อง
    relatedScripts = relatedScripts.slice(0, 3);
    
    // แสดงสคริปต์ที่เกี่ยวข้อง
    const relatedScriptsContainer = document.getElementById('related-scripts');
    relatedScriptsContainer.innerHTML = '';
    
    if (relatedScripts.length > 0) {
        relatedScripts.forEach(script => {
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
                    <a href="script-detail.html?id=${script.id}" class="script-button">ดูรายละเอียด</a>
                </div>
            `;
            
            relatedScriptsContainer.appendChild(scriptCard);
        });
    } else {
        relatedScriptsContainer.innerHTML = '<div class="no-results">ไม่พบสคริปต์ที่เกี่ยวข้อง</div>';
    }
}

// ฟังก์ชันสำหรับการตั้งค่าปุ่มดาวน์โหลด
function setupDownloadButton(script) {
    const downloadBtn = document.getElementById('download-btn');
    
    downloadBtn.addEventListener('click', function() {
        // เพิ่มยอดดาวน์โหลด
        DataManager.incrementDownloads(script.id);
        
        // อัพเดตยอดดาวน์โหลดในหน้า
        const downloadsElement = document.getElementById('script-downloads');
        const newDownloads = script.downloads + 1;
        downloadsElement.textContent = newDownloads >= 1000 ? 
            (newDownloads / 1000).toFixed(1) + 'K' : 
            newDownloads;
        
        // สร้างไฟล์สำหรับดาวน์โหลด
        const blob = new Blob([script.code], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        
        // สร้างลิงก์สำหรับดาวน์โหลด
        const a = document.createElement('a');
        a.href = url;
        a.download = `${script.title.replace(/\s+/g, '_')}.lua`;
        document.body.appendChild(a);
        a.click();
        
        // ลบลิงก์
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
}

// ฟังก์ชันสำหรับการตั้งค่าปุ่มคัดลอกโค้ด
function setupCopyButtons(script) {
    const copyBtn = document.getElementById('copy-btn');
    const copyCodeBtn = document.getElementById('copy-code-btn');
    const copySuccess = document.getElementById('copy-success');
    
    // ฟังก์ชันสำหรับการคัดลอกโค้ด
    function copyCode() {
        navigator.clipboard.writeText(script.code).then(() => {
            // แสดงข้อความสำเร็จ
            copySuccess.classList.add('show');
            
            // ซ่อนข้อความสำเร็จหลังจาก 3 วินาที
            setTimeout(() => {
                copySuccess.classList.remove('show');
            }, 3000);
        });
    }
    
    // ตั้งค่าปุ่มคัดลอกโค้ด
    copyBtn.addEventListener('click', copyCode);
    copyCodeBtn.addEventListener('click', copyCode);
}
