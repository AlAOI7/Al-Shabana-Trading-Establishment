// بيانات المنتجات (سيتم تحميلها من ملف JSON)
let products = [];
let filteredProducts = [];

// تهيئة التطبيق
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

async function initializeApp() {
    // تحميل المنتجات من ملف JSON
    await loadProducts();
    
    // تهيئة المكونات
    initializeNavigation();
    initializeAnimations();
    initializeCharts();
    initializeProductFilter();
    initializeContactForm();
    initializeScrollToTop();
    initializeModal();
    
    // عرض المنتجات
    displayProducts();
}

// تحميل المنتجات من ملف JSON
async function loadProducts() {
    try {
        const response = await fetch('products.json');
        const data = await response.json();
        products = data.products;
        filteredProducts = [...products];
        
        // إخفاء مؤشر التحميل
        document.querySelector('.products-loading').style.display = 'none';
    } catch (error) {
        console.error('Error loading products:', error);
        document.querySelector('.products-loading').innerHTML = 
            '<p>⚠️ حدث خطأ في تحميل المنتجات. يرجى المحاولة مرة أخرى.</p>';
    }
}
// تهيئة شريط التنقل
function initializeNavigation() {
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    const navLinks = document.querySelectorAll('.nav-link');
    const languageToggle = document.getElementById('languageToggle');
    const body = document.body;

    // تبديل قائمة الجوال
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        mainNav.classList.toggle('active');
        menuToggle.classList.toggle('active');
        body.classList.toggle('menu-open');
    });

    // إغلاق القائمة عند النقر على رابط
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // إزالة النشط من جميع الروابط
            navLinks.forEach(l => l.classList.remove('active'));
            // إضافة النشط للرابط الحالي
            this.classList.add('active');
            
            // إغلاق القائمة على الجوال
            if (window.innerWidth <= 768) {
                mainNav.classList.remove('active');
                menuToggle.classList.remove('active');
                body.classList.remove('menu-open');
            }
        });
    });

    // إغلاق القائمة عند النقر خارجها
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && 
            mainNav.classList.contains('active') && 
            !mainNav.contains(e.target) && 
            !menuToggle.contains(e.target)) {
            mainNav.classList.remove('active');
            menuToggle.classList.remove('active');
            body.classList.remove('menu-open');
        }
    });

    // إغلاق القائمة عند تغيير حجم النافذة
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            mainNav.classList.remove('active');
            menuToggle.classList.remove('active');
            body.classList.remove('menu-open');
        }
    });

    // تبديل اللغة
    languageToggle.addEventListener('click', function() {
        const langText = this.querySelector('.lang-text');
        const currentLang = langText.textContent;
        
        if (currentLang === 'EN') {
            langText.textContent = 'AR';
            showNotification('Language changed to English', 'success');
        } else {
            langText.textContent = 'EN';
            showNotification('تم تغيير اللغة إلى العربية', 'success');
        }
    });

    // تأثير التمرير على الهيدر
    let lastScrollY = window.scrollY;
    const header = document.querySelector('.main-header');

    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            header.style.background = 'rgba(255, 255, 255, 0.95)';
            header.style.backdropFilter = 'blur(10px)';
            header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
        } else {
            header.style.background = 'var(--text-light)';
            header.style.backdropFilter = 'none';
            header.style.boxShadow = 'var(--shadow-md)';
        }

        // إخفاء/إظهار الهيدر عند التمرير
        if (window.scrollY > lastScrollY && window.scrollY > 100) {
            header.style.transform = 'translateY(-100%)';
        } else {
            header.style.transform = 'translateY(0)';
        }
        lastScrollY = window.scrollY;
    });

    // تفعيل الروابط النشطة عند التمرير
    const sections = document.querySelectorAll('section');
    const navLi = document.querySelectorAll('.nav-link');

    window.addEventListener('scroll', function() {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (scrollY >= (sectionTop - 200)) {
                current = section.getAttribute('id');
            }
        });

        navLi.forEach(li => {
            li.classList.remove('active');
            if (li.getAttribute('href') === `#${current}`) {
                li.classList.add('active');
            }
        });
    });
}

// تهيئة الأنيميشن
function initializeAnimations() {
    // أنيميشن العدادات
    const counters = document.querySelectorAll('.stat-number');
    const speed = 200;

    const animateCounter = (counter) => {
        const target = +counter.getAttribute('data-count');
        const count = +counter.innerText;
        const increment = target / speed;

        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(() => animateCounter(counter), 1);
        } else {
            counter.innerText = target;
        }
    };

    // تفعيل العدادات عند التمرير
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                counters.forEach(counter => {
                    animateCounter(counter);
                });
            }
        });
    }, { threshold: 0.5 });

    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        observer.observe(statsSection);
    }

    // أنيميشن العناصر عند التمرير
    const animatedElements = document.querySelectorAll('.product-card, .mission-card, .vision-card, .values-card, .contact-card');
    
    const elementObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    animatedElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        elementObserver.observe(element);
    });
}

// تهيئة الرسوم البيانية
function initializeCharts() {
    // مخطط المبيعات
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
            datasets: [{
                label: 'المبيعات الشهرية',
                data: [12000, 19000, 15000, 25000, 22000, 30000],
                borderColor: '#FFC300',
                backgroundColor: 'rgba(255, 195, 0, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#FFFFFF',
                        font: {
                            family: 'Tajawal'
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#FFFFFF'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#FFFFFF'
                    }
                }
            }
        }
    });

    // مخطط توزيع المنتجات
    const productsCtx = document.getElementById('productsChart').getContext('2d');
    const productsChart = new Chart(productsCtx, {
        type: 'doughnut',
        data: {
            labels: ['الطاقة', 'تنقية المياه', 'مواد التنظيف', 'المستلزمات'],
            datasets: [{
                data: [35, 25, 20, 20],
                backgroundColor: [
                    '#FFC300',
                    '#0A1E4A',
                    '#FF6B00',
                    '#64748B'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#FFFFFF',
                        font: {
                            family: 'Tajawal',
                            size: 12
                        }
                    }
                }
            }
        }
    });
}

// تهيئة عامل تصفية المنتجات
function initializeProductFilter() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // إزالة النشط من جميع الأزرار
            filterBtns.forEach(b => b.classList.remove('active'));
            // إضافة النشط للزر المختار
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            filterProducts(filter);
        });
    });
}

// تصفية المنتجات
function filterProducts(filter) {
    if (filter === 'all') {
        filteredProducts = [...products];
    } else {
        filteredProducts = products.filter(product => {
            return product.Item_Group === filter || 
                   product.Brand === filter ||
                   (filter === 'Cleaning' && product.Item_Name.includes('تنظيف')) ||
                   (filter === 'Energy' && product.Item_Name.includes('طاقة'));
        });
    }
    
    displayProducts();
}

// عرض المنتجات
function displayProducts() {
    const container = document.getElementById('productsContainer');
    
    if (filteredProducts.length === 0) {
        container.innerHTML = '<div class="no-products">⚠️ لا توجد منتجات تطابق معايير البحث</div>';
        return;
    }
    
    container.innerHTML = filteredProducts.map(product => {
        // تحديد مسار الصورة - إذا كانت الصورة غير متوفرة نستخدم أيقونة افتراضية
        let imageContent;
        
        if (product.Image && product.Image !== '' && product.Image !== 'N/A') {
            // استخدام الصورة الفعلية
            imageContent = `<img src="${product.Image}" alt="${product.Item_Name}" class="product-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                           <div class="product-icon fallback-icon" style="display: none;">
                               <i class="fas fa-box"></i>
                           </div>`;
        } else {
            // استخدام الأيقونة الافتراضية
            imageContent = `<div class="product-icon">
                               <i class="fas fa-box"></i>
                           </div>`;
        }
        
        return `
        <div class="product-card" data-product-id="${product.S_NO}">
            <div class="product-image">
                ${imageContent}
            </div>
            <div class="product-info">
                <div class="product-code">${product.Item_Code}</div>
                <h3 class="product-name">${product.Item_Name}</h3>
                <div class="product-group">${product.Item_Group}</div>
                <div class="product-brand">${product.Brand}</div>
                <div class="product-packing" style="margin-top: 10px; font-size: 0.9rem; color: #666;">
                    ${product.Packing}
                </div>
            </div>
        </div>
        `;
    }).join('');
    
    // إضافة event listeners للبطاقات
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            openProductModal(productId);
        });
    });
}
// function displayProducts() {
//     const container = document.getElementById('productsContainer');
    
//     if (filteredProducts.length === 0) {
//         container.innerHTML = '<div class="no-products">⚠️ لا توجد منتجات تطابق معايير البحث</div>';
//         return;
//     }
    
//     container.innerHTML = filteredProducts.map(product => 
//         `
        
//         <div class="product-card" data-product-id="${product.S_NO}">
//             // <div class="product-image">
//             //     <i class="fas fa-box"></i>
//             // </div>
//               <div class="product-image">
//                 ${hasImage 
//                     ? `<img src="${product.Image}" alt="${product.Item_Name}" class="product-img" />`
//                     : `<div class="product-icon"><i class="fas fa-box"></i></div>`
//                 }
//             </div>
//             <div class="product-info">
//                 <div class="product-code">${product.Item_Code}</div>
//                 <h3 class="product-name">${product.Item_Name}</h3>
//                 <div class="product-group">${product.Item_Group}</div>
//                 <div class="product-brand">${product.Brand}</div>
//                 <div class="product-packing" style="margin-top: 10px; font-size: 0.9rem; color: #666;">
//                     ${product.Packing}
//                 </div>
//             </div>
//         </div>
//     `).join('');
    
//     // إضافة event listeners للبطاقات
//     document.querySelectorAll('.product-card').forEach(card => {
//         card.addEventListener('click', function() {
//             const productId = this.getAttribute('data-product-id');
//             openProductModal(productId);
//         });
//     });
// }

// تهيئة نموذج الاتصال
function initializeContactForm() {
    const contactForm = document.getElementById('contactForm');
    
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // محاكاة إرسال النموذج
        const formData = new FormData(this);
        const formProps = Object.fromEntries(formData);
        
        showNotification('شكراً لك! تم استلام رسالتك وسنقوم بالرد في أقرب وقت.', 'success');
        this.reset();
    });
}

// تهيئة زر العودة للأعلى
function initializeScrollToTop() {
    const scrollBtn = document.getElementById('scrollToTop');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollBtn.classList.add('show');
        } else {
            scrollBtn.classList.remove('show');
        }
    });
    
    scrollBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// تهيئة المودال
function initializeModal() {
    const modal = document.getElementById('productModal');
    const closeBtn = document.querySelector('.close-btn');
    
    // إغلاق المودال
    closeBtn.addEventListener('click', closeModal);
    
    // إغلاق عند النقر خارج المحتوى
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // إغلاق بالزر Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
}

// فتح مودال المنتج
function openProductModal(productId) {
    const product = products.find(p => p.S_NO == productId);
    if (!product) return;
    
    const modal = document.getElementById('productModal');
    const modalImg = document.getElementById('modal-img');
    const modalName = document.getElementById('modal-name');
    const modalDescription = document.getElementById('modal-description');
    const modalCode = document.getElementById('modal-code');
    const modalGroup = document.getElementById('modal-group');
    const modalBrand = document.getElementById('modal-brand');
    const modalPacking = document.getElementById('modal-packing');
    
    // تعبئة البيانات
    // modalImg.src = product.Image || 'placeholder.jpg';
    // modalImg.alt = product.Item_Name;
     // التحقق من وجود صورة للمنتج
    if (product.Image && product.Image !== '' && product.Image !== 'N/A') {
        modalImg.src = product.Image;
        modalImg.style.display = 'block';
        modalImg.alt = product.Item_Name;
    } else {
        modalImg.style.display = 'none';
    }
    modalName.textContent = product.Item_Name;
    modalDescription.textContent = product.Item_Name; // يمكن إضافة وصف مفصل لاحقاً
    modalCode.textContent = product.Item_Code;
    modalGroup.textContent = product.Item_Group;
    modalBrand.textContent = product.Brand;
    modalPacking.textContent = product.Packing;
    
    // عرض المودال
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

// إغلاق المودال
function closeModal() {
    const modal = document.getElementById('productModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// عرض الإشعارات
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // إضافة الأنيميشن
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${getNotificationColor(type)};
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 3000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        max-width: 400px;
    `;
    
    document.body.appendChild(notification);
    
    // أنيميشن الدخول
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // إزالة تلقائية بعد 5 ثوان
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// الحصول على أيقونة الإشعار
function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// الحصول على لون الإشعار
function getNotificationColor(type) {
    const colors = {
        success: '#10B981',
        error: '#EF4444',
        warning: '#F59E0B',
        info: '#3B82F6'
    };
    return colors[type] || '#3B82F6';
}

// تحسين أداء التمرير
let scrollTimeout;
window.addEventListener('scroll', function() {
    if (!scrollTimeout) {
        scrollTimeout = setTimeout(function() {
            scrollTimeout = null;
            // أي كود يحتاج للتشغيل بعد التمرير
        }, 100);
    }
});
// نظام التبديل بين اللغات
let currentLanguage = 'ar';

// تهيئة نظام اللغات
function initializeLanguageSystem() {
    const languageToggle = document.getElementById('languageToggle');
    
    languageToggle.addEventListener('click', function() {
        toggleLanguage();
    });
    
    // تحميل اللغة المحفوظة
    const savedLanguage = localStorage.getItem('preferredLanguage');
    if (savedLanguage && savedLanguage !== currentLanguage) {
        currentLanguage = savedLanguage;
        updateLanguage();
    }
}

// تبديل اللغة
function toggleLanguage() {
    currentLanguage = currentLanguage === 'ar' ? 'en' : 'ar';
    localStorage.setItem('preferredLanguage', currentLanguage);
    updateLanguage();
    updateLanguageToggle();
}

// تحديث النصوص حسب اللغة
function updateLanguage() {
    const elements = document.querySelectorAll('[data-ar], [data-en]');
    
    elements.forEach(element => {
        const arabicText = element.getAttribute('data-ar');
        const englishText = element.getAttribute('data-en');
        
        if (arabicText && englishText) {
            element.textContent = currentLanguage === 'ar' ? arabicText : englishText;
        }
    });
    
    // تحديث العناصر الخاصة
    updatePlaceholders();
    updatePageDirection();
    updateChartLabels();
    showLanguageNotification();
}

// تحديث الـ placeholders
function updatePlaceholders() {
    const inputs = document.querySelectorAll('input[data-ar-placeholder], textarea[data-ar-placeholder]');
    
    inputs.forEach(input => {
        const arabicPlaceholder = input.getAttribute('data-ar-placeholder');
        const englishPlaceholder = input.getAttribute('data-en-placeholder');
        
        if (arabicPlaceholder && englishPlaceholder) {
            input.placeholder = currentLanguage === 'ar' ? arabicPlaceholder : englishPlaceholder;
        }
    });
}

// تحديث اتجاه الصفحة
function updatePageDirection() {
    const html = document.documentElement;
    const body = document.body;
    
    if (currentLanguage === 'ar') {
        html.setAttribute('dir', 'rtl');
        html.setAttribute('lang', 'ar');
        body.classList.add('rtl');
        body.classList.remove('ltr');
    } else {
        html.setAttribute('dir', 'ltr');
        html.setAttribute('lang', 'en');
        body.classList.add('ltr');
        body.classList.remove('rtl');
    }
}

// تحديث تسميات الرسوم البيانية
function updateChartLabels() {
    // سيتم تحديث الرسوم البيانية عند إعادة تهيئتها
    if (window.salesChart && window.productsChart) {
        initializeCharts();
    }
}

// تحديث زر تبديل اللغة
function updateLanguageToggle() {
    const langText = document.querySelector('.lang-text');
    langText.textContent = currentLanguage === 'ar' ? 'EN' : 'AR';
}

// عرض إشعار تغيير اللغة
function showLanguageNotification() {
    const message = currentLanguage === 'ar' 
        ? 'تم تغيير اللغة إلى العربية' 
        : 'Language changed to English';
    
    showNotification(message, 'success');
}

// تحديث دالة initializeApp لإضافة نظام اللغات
async function initializeApp() {
    // تحميل المنتجات من ملف JSON
    await loadProducts();
    
    // تهيئة المكونات
    initializeLanguageSystem(); // إضافة هذا السطر
    initializeNavigation();
    initializeAnimations();
    initializeCharts();
    initializeProductFilter();
    initializeContactForm();
    initializeScrollToTop();
    initializeModal();
    
    // عرض المنتجات
    displayProducts();
}// تأثيرات العدادات للإحصائيات
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-count');
        const duration = 2000; // 2 seconds
        const step = target / (duration / 16); // 60fps
        let current = 0;
        
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            counter.textContent = Math.floor(current);
        }, 16);
    });
}

// تفعيل العدادات عند التمرير
function isElementInViewport(el) {
    const rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

let countersAnimated = false;

function checkCountersAnimation() {
    const statsSection = document.querySelector('.stats-section');
    if (statsSection && isElementInViewport(statsSection) && !countersAnimated) {
        animateCounters();
        countersAnimated = true;
    }
}

// استدعاء الوظائف عند التمرير
window.addEventListener('scroll', checkCountersAnimation);
window.addEventListener('load', checkCountersAnimation);

// تفعيل الرسوم المتحركة عند التحميل
document.addEventListener('DOMContentLoaded', function() {
    // إضافة كلاس للعناصر المتحركة
    const animatedElements = document.querySelectorAll('.animate-fade-in, .animate-fade-in-delay, .animate-fade-in-delay-2, .animate-fade-in-delay-3');
    
    animatedElements.forEach(element => {
        element.style.opacity = '1';
        element.style.transform = 'translateY(0)';
    });
});
