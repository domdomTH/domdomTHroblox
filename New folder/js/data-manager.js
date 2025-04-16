// ระบบจัดการข้อมูลสำหรับเว็บไซต์ Roblox Scripts
const DataManager = {
    // ข้อมูลเริ่มต้น
    initialData: {
        scripts: [
            {
                id: 1,
                title: "Blox Fruits Auto Farm",
                description: "สคริปต์ออโต้ฟาร์มสำหรับ Blox Fruits ใช้งานง่าย ไม่มีแบน",
                category: "Fighting",
                image: "https://tr.rbxcdn.com/e9b0640f26e3c602a1f6082f26f09691/768/432/Image/Png",
                downloads: 1200,
                rating: 4.8,
                date: "12/04/2023",
                code: "-- Blox Fruits Auto Farm Script\nlocal Library = loadstring(game:HttpGet(\"https://raw.githubusercontent.com/example/BloxFruits/main/Library.lua\"))();\nlocal Window = Library.CreateLib(\"Blox Fruits Auto Farm\", \"Ocean\");\nlocal Tab = Window:NewTab(\"Auto Farm\");\nlocal Section = Tab:NewSection(\"Farming\");\n\nSection:NewToggle(\"Auto Farm Level\", \"Auto farms levels for you\", function(state)\n    getgenv().AutoFarm = state;\n    while getgenv().AutoFarm do\n        -- Auto farm code here\n        wait();\n    end;\nend);",
                published: true,
                views: 2500,
                featured: true,
                tags: ["farming", "blox fruits", "auto"]
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
                code: "-- Adopt Me Auto Farm Script\nlocal Library = loadstring(game:HttpGet(\"https://raw.githubusercontent.com/example/AdoptMe/main/Library.lua\"))();\nlocal Window = Library.CreateLib(\"Adopt Me Auto Farm\", \"Midnight\");\nlocal Tab = Window:NewTab(\"Auto Farm\");\nlocal Section = Tab:NewSection(\"Pets\");\n\nSection:NewToggle(\"Auto Collect Items\", \"Automatically collects items\", function(state)\n    getgenv().AutoCollect = state;\n    while getgenv().AutoCollect do\n        -- Auto collect code here\n        wait();\n    end;\nend);",
                published: true,
                views: 1800,
                featured: false,
                tags: ["adopt me", "pets", "auto farm"]
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
                code: "-- Bedwars Aimbot & ESP Script\nlocal Library = loadstring(game:HttpGet(\"https://raw.githubusercontent.com/example/Bedwars/main/Library.lua\"))();\nlocal Window = Library.CreateLib(\"Bedwars Aimbot & ESP\", \"Synapse\");\nlocal Tab = Window:NewTab(\"Aimbot\");\nlocal Section = Tab:NewSection(\"Settings\");\n\nSection:NewToggle(\"Aimbot\", \"Automatically aims at enemies\", function(state)\n    getgenv().Aimbot = state;\n    while getgenv().Aimbot do\n        -- Aimbot code here\n        wait();\n    end;\nend);",
                published: true,
                views: 3200,
                featured: true,
                tags: ["fps", "aimbot", "esp", "bedwars"]
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
                code: "-- Ninja Legends Auto Farm Script\nlocal Library = loadstring(game:HttpGet(\"https://raw.githubusercontent.com/example/NinjaLegends/main/Library.lua\"))();\nlocal Window = Library.CreateLib(\"Ninja Legends Auto Farm\", \"DarkTheme\");\nlocal Tab = Window:NewTab(\"Auto Farm\");\nlocal Section = Tab:NewSection(\"Farming\");\n\nSection:NewToggle(\"Auto Swing\", \"Automatically swings your sword\", function(state)\n    getgenv().AutoSwing = state;\n    while getgenv().AutoSwing do\n        -- Auto swing code here\n        wait();\n    end;\nend);",
                published: true,
                views: 1500,
                featured: false,
                tags: ["ninja legends", "auto farm", "parkour"]
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
                code: "-- Pet Simulator X Auto Farm Script\nlocal Library = loadstring(game:HttpGet(\"https://raw.githubusercontent.com/example/PetSimX/main/Library.lua\"))();\nlocal Window = Library.CreateLib(\"Pet Simulator X Auto Farm\", \"BloodTheme\");\nlocal Tab = Window:NewTab(\"Auto Farm\");\nlocal Section = Tab:NewSection(\"Farming\");\n\nSection:NewToggle(\"Auto Farm Coins\", \"Automatically farms coins\", function(state)\n    getgenv().AutoFarmCoins = state;\n    while getgenv().AutoFarmCoins do\n        -- Auto farm coins code here\n        wait();\n    end;\nend);",
                published: true,
                views: 4200,
                featured: true,
                tags: ["pet simulator", "auto farm", "simulator"]
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
                code: "-- Jailbreak Auto Rob Script\nlocal Library = loadstring(game:HttpGet(\"https://raw.githubusercontent.com/example/Jailbreak/main/Library.lua\"))();\nlocal Window = Library.CreateLib(\"Jailbreak Auto Rob\", \"LightTheme\");\nlocal Tab = Window:NewTab(\"Auto Rob\");\nlocal Section = Tab:NewSection(\"Robbery\");\n\nSection:NewToggle(\"Auto Rob All\", \"Automatically robs all locations\", function(state)\n    getgenv().AutoRobAll = state;\n    while getgenv().AutoRobAll do\n        -- Auto rob code here\n        wait();\n    end;\nend);",
                published: true,
                views: 3800,
                featured: false,
                tags: ["jailbreak", "auto rob", "adventure"]
            }
        ],
        categories: [
            {
                id: 1,
                name: "เกมส์ทั้งหมด",
                icon: "fas fa-gamepad",
                count: 6
            },
            {
                id: 2,
                name: "Fighting",
                icon: "fas fa-fist-raised",
                count: 1
            },
            {
                id: 3,
                name: "Simulator",
                icon: "fas fa-building",
                count: 2
            },
            {
                id: 4,
                name: "FPS",
                icon: "fas fa-crosshairs",
                count: 1
            },
            {
                id: 5,
                name: "Parkour",
                icon: "fas fa-running",
                count: 1
            },
            {
                id: 6,
                name: "Adventure",
                icon: "fas fa-mountain",
                count: 1
            }
        ],
        users: [
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
        ],
        settings: {
            siteTitle: "Roblox Scripts",
            siteDescription: "แหล่งรวมสคริปต์ Roblox คุณภาพสูง ฟรี และอัพเดทล่าสุด",
            homeTitle: "ยินดีต้อนรับสู่ Roblox Scripts",
            homeSubtitle: "แหล่งรวมสคริปต์ Roblox คุณภาพสูง ฟรี และอัพเดทล่าสุด",
            scriptsPerPage: 6,
            contactEmail: "contact@robloxscripts.com",
            contactDiscord: "discord.gg/robloxscripts"
        },
        stats: {
            totalDownloads: 0,
            dailyViews: 0,
            lastViewTime: Date.now()
        }
    },
    
    // ฟังก์ชันสำหรับการโหลดข้อมูล
    loadData: function() {
        // ตรวจสอบว่ามีข้อมูลใน localStorage หรือไม่
        const storedData = localStorage.getItem('robloxScriptsData');
        
        if (storedData) {
            // ถ้ามีข้อมูลใน localStorage ให้ใช้ข้อมูลนั้น
            return JSON.parse(storedData);
        } else {
            // ถ้าไม่มีข้อมูลใน localStorage ให้ใช้ข้อมูลเริ่มต้น
            this.saveData(this.initialData);
            return this.initialData;
        }
    },
    
    // ฟังก์ชันสำหรับการบันทึกข้อมูล
    saveData: function(data) {
        localStorage.setItem('robloxScriptsData', JSON.stringify(data));
    },
    
    // ฟังก์ชันสำหรับการอัพเดตข้อมูลสคริปต์
    updateScript: function(script) {
        const data = this.loadData();
        const index = data.scripts.findIndex(s => s.id === script.id);
        
        if (index !== -1) {
            // อัพเดตสคริปต์ที่มีอยู่
            data.scripts[index] = script;
        } else {
            // เพิ่มสคริปต์ใหม่
            script.id = data.scripts.length > 0 ? Math.max(...data.scripts.map(s => s.id)) + 1 : 1;
            script.date = new Date().toLocaleDateString('en-GB');
            script.downloads = 0;
            script.views = 0;
            script.rating = 0;
            data.scripts.unshift(script);
        }
        
        // อัพเดตจำนวนสคริปต์ในหมวดหมู่
        this.updateCategoryCounts(data);
        
        // บันทึกข้อมูล
        this.saveData(data);
        return script;
    },
    
    // ฟังก์ชันสำหรับการลบสคริปต์
    deleteScript: function(scriptId) {
        const data = this.loadData();
        const index = data.scripts.findIndex(s => s.id === scriptId);
        
        if (index !== -1) {
            // ลบสคริปต์
            data.scripts.splice(index, 1);
            
            // อัพเดตจำนวนสคริปต์ในหมวดหมู่
            this.updateCategoryCounts(data);
            
            // บันทึกข้อมูล
            this.saveData(data);
            return true;
        }
        
        return false;
    },
    
    // ฟังก์ชันสำหรับการอัพเดตจำนวนสคริปต์ในหมวดหมู่
    updateCategoryCounts: function(data) {
        // นับจำนวนสคริปต์ในแต่ละหมวดหมู่
        const categoryCounts = {};
        data.scripts.forEach(script => {
            if (script.published) {
                if (categoryCounts[script.category]) {
                    categoryCounts[script.category]++;
                } else {
                    categoryCounts[script.category] = 1;
                }
            }
        });
        
        // อัพเดตจำนวนสคริปต์ในแต่ละหมวดหมู่
        data.categories.forEach(category => {
            if (category.name === "เกมส์ทั้งหมด") {
                category.count = data.scripts.filter(s => s.published).length;
            } else {
                category.count = categoryCounts[category.name] || 0;
            }
        });
    },
    
    // ฟังก์ชันสำหรับการอัพเดตหมวดหมู่
    updateCategory: function(category) {
        const data = this.loadData();
        const index = data.categories.findIndex(c => c.id === category.id);
        
        if (index !== -1) {
            // อัพเดตหมวดหมู่ที่มีอยู่
            data.categories[index] = category;
        } else {
            // เพิ่มหมวดหมู่ใหม่
            category.id = data.categories.length > 0 ? Math.max(...data.categories.map(c => c.id)) + 1 : 1;
            category.count = 0;
            data.categories.push(category);
        }
        
        // บันทึกข้อมูล
        this.saveData(data);
        return category;
    },
    
    // ฟังก์ชันสำหรับการลบหมวดหมู่
    deleteCategory: function(categoryId) {
        const data = this.loadData();
        const index = data.categories.findIndex(c => c.id === categoryId);
        
        // ไม่อนุญาตให้ลบหมวดหมู่ "เกมส์ทั้งหมด"
        if (index !== -1 && data.categories[index].name !== "เกมส์ทั้งหมด") {
            // ลบหมวดหมู่
            data.categories.splice(index, 1);
            
            // บันทึกข้อมูล
            this.saveData(data);
            return true;
        }
        
        return false;
    },
    
    // ฟังก์ชันสำหรับการอัพเดตผู้ใช้
    updateUser: function(user) {
        const data = this.loadData();
        const index = data.users.findIndex(u => u.id === user.id);
        
        if (index !== -1) {
            // อัพเดตผู้ใช้ที่มีอยู่
            data.users[index] = user;
        } else {
            // เพิ่มผู้ใช้ใหม่
            user.id = data.users.length > 0 ? Math.max(...data.users.map(u => u.id)) + 1 : 1;
            user.registerDate = new Date().toLocaleDateString('en-GB');
            data.users.unshift(user);
        }
        
        // บันทึกข้อมูล
        this.saveData(data);
        return user;
    },
    
    // ฟังก์ชันสำหรับการลบผู้ใช้
    deleteUser: function(userId) {
        const data = this.loadData();
        const index = data.users.findIndex(u => u.id === userId);
        
        if (index !== -1) {
            // ลบผู้ใช้
            data.users.splice(index, 1);
            
            // บันทึกข้อมูล
            this.saveData(data);
            return true;
        }
        
        return false;
    },
    
    // ฟังก์ชันสำหรับการอัพเดตการตั้งค่า
    updateSettings: function(settings) {
        const data = this.loadData();
        data.settings = {...data.settings, ...settings};
        
        // บันทึกข้อมูล
        this.saveData(data);
        return data.settings;
    },
    
    // ฟังก์ชันสำหรับการเพิ่มยอดดาวน์โหลด
    incrementDownloads: function(scriptId) {
        const data = this.loadData();
        const script = data.scripts.find(s => s.id === scriptId);
        
        if (script) {
            script.downloads++;
            data.stats.totalDownloads = data.scripts.reduce((total, s) => total + s.downloads, 0);
            
            // บันทึกข้อมูล
            this.saveData(data);
            return script.downloads;
        }
        
        return 0;
    },
    
    // ฟังก์ชันสำหรับการเพิ่มยอดเข้าชม
    incrementViews: function(scriptId) {
        const data = this.loadData();
        const script = data.scripts.find(s => s.id === scriptId);
        
        if (script) {
            script.views++;
            data.stats.dailyViews++;
            data.stats.lastViewTime = Date.now();
            
            // บันทึกข้อมูล
            this.saveData(data);
            return script.views;
        }
        
        return 0;
    },
    
    // ฟังก์ชันสำหรับการค้นหาสคริปต์
    searchScripts: function(query, category = 'all', onlyPublished = true) {
        const data = this.loadData();
        let results = data.scripts;
        
        // กรองเฉพาะสคริปต์ที่เผยแพร่แล้ว (ถ้าต้องการ)
        if (onlyPublished) {
            results = results.filter(script => script.published);
        }
        
        // กรองตามหมวดหมู่
        if (category !== 'all') {
            results = results.filter(script => script.category === category);
        }
        
        // กรองตามคำค้นหา
        if (query && query.trim() !== '') {
            query = query.toLowerCase();
            results = results.filter(script => 
                script.title.toLowerCase().includes(query) || 
                script.description.toLowerCase().includes(query) ||
                script.tags.some(tag => tag.toLowerCase().includes(query))
            );
        }
        
        return results;
    },
    
    // ฟังก์ชันสำหรับการดึงสคริปต์ยอดนิยม
    getPopularScripts: function(limit = 10, category = 'all') {
        const data = this.loadData();
        let results = data.scripts.filter(script => script.published);
        
        // กรองตามหมวดหมู่
        if (category !== 'all') {
            results = results.filter(script => script.category === category);
        }
        
        // เรียงตามยอดดาวน์โหลด (มากไปน้อย)
        results.sort((a, b) => b.downloads - a.downloads);
        
        // จำกัดจำนวนผลลัพธ์
        return results.slice(0, limit);
    },
    
    // ฟังก์ชันสำหรับการดึงสคริปต์ล่าสุด
    getLatestScripts: function(limit = 10, category = 'all') {
        const data = this.loadData();
        let results = data.scripts.filter(script => script.published);
        
        // กรองตามหมวดหมู่
        if (category !== 'all') {
            results = results.filter(script => script.category === category);
        }
        
        // เรียงตามวันที่ (ล่าสุดก่อน)
        results.sort((a, b) => {
            const dateA = this.parseDate(a.date);
            const dateB = this.parseDate(b.date);
            return dateB - dateA;
        });
        
        // จำกัดจำนวนผลลัพธ์
        return results.slice(0, limit);
    },
    
    // ฟังก์ชันสำหรับการดึงสคริปต์แนะนำ
    getFeaturedScripts: function(limit = 10) {
        const data = this.loadData();
        let results = data.scripts.filter(script => script.published && script.featured);
        
        // เรียงตามยอดดาวน์โหลด (มากไปน้อย)
        results.sort((a, b) => b.downloads - a.downloads);
        
        // จำกัดจำนวนผลลัพธ์
        return results.slice(0, limit);
    },
    
    // ฟังก์ชันสำหรับการแปลงวันที่
    parseDate: function(dateStr) {
        const parts = dateStr.split('/');
        // วันที่ในรูปแบบ DD/MM/YYYY
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }
};

// เริ่มต้นข้อมูลเมื่อโหลดไฟล์
document.addEventListener('DOMContentLoaded', function() {
    // โหลดข้อมูลเมื่อเริ่มต้น
    DataManager.loadData();
});
