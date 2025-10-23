 <!-- الفوتر -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                 <div class="footer-section">
                        <div class="footer-logo">
                            <img src="2.png" alt="شعار مؤسسة الشبانات">
                            <span><?php echo getTranslatedText('footer_logo_text'); ?></span>
                        </div>
                        <p class="footer-description"><?php echo getTranslatedText('footer_description'); ?></p>
                    </div>
                
                <div class="footer-section">
                    <h3 data-ar="روابط سريعة" data-en="Quick Links">روابط سريعة</h3>
                    <ul class="footer-links">
                        <li><a href="#about" data-ar="من نحن" data-en="About Us">من نحن</a></li>
                        <li><a href="#products" data-ar="المنتجات" data-en="Products">المنتجات</a></li>
                        <li><a href="#brands" data-ar="العلامات" data-en="Brands">العلامات</a></li>
                        <li><a href="#contact" data-ar="اتصل بنا" data-en="Contact">اتصل بنا</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3 data-ar="المنتجات" data-en="Products">المنتجات</h3>
                    <ul class="footer-links">
                        <li><a href="#" data-ar="الطاقة والوقود" data-en="Energy & Fuel">الطاقة والوقود</a></li>
                        <li><a href="#" data-ar="تنقية المياه" data-en="Water Purification">تنقية المياه</a></li>
                        <li><a href="#" data-ar="مواد التنظيف" data-en="Cleaning Materials">مواد التنظيف</a></li>
                        <li><a href="#" data-ar="المستلزمات" data-en="Supplies">المستلزمات</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3 data-ar="اشترك في النشرة" data-en="Subscribe to Newsletter">اشترك في النشرة</h3>
                    <form class="newsletter-form">
                        <input type="email" placeholder="بريدك الإلكتروني" data-ar-placeholder="بريدك الإلكتروني" data-en-placeholder="Your Email" required>
                        <button type="submit">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            
               <div class="footer-bottom">
                        <p><?php echo getTranslatedText('copyright_ar'); ?></p>
                    </div>
        </div>
    </footer>
        <!-- زر العودة للأعلى -->
    <button id="scrollToTop" class="scroll-to-top">
        <i class="fas fa-chevron-up"></i>
    </button>
       <script>// تهيئة شريط التنقل - الإصدار المعدل
           // تهيئة القائمة المتنقلة
        function initMobileMenu() {
            const menuToggle = document.querySelector('.menu-toggle');
            const mainNav = document.querySelector('.main-nav');
            
            if (menuToggle && mainNav) {
                menuToggle.addEventListener('click', function() {
                    mainNav.classList.toggle('active');
                    menuToggle.classList.toggle('active');
                });
            }
        }

// حل بديل للتأكد من عمل القائمة
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            console.log('تم النقر على زر القائمة');
            console.log('الحالة قبل:', mainNav.classList.contains('active'));
            
            mainNav.classList.toggle('active');
            menuToggle.classList.toggle('active');
            document.body.classList.toggle('menu-open');
            
            console.log('الحالة بعد:', mainNav.classList.contains('active'));
            console.log('أنماط القائمة:', window.getComputedStyle(mainNav).display);
        });
        
        // إغلاق القائمة عند النقر على أي رابط
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    mainNav.classList.remove('active');
                    menuToggle.classList.remove('active');
                    document.body.classList.remove('menu-open');
                }
            });
        });
    }
});
    </script>
<script>
// دالة لتغيير اللغة
// function changeLanguage(lang) {
//     // تحديث النصوص في معلومات الاتصال
//     document.querySelectorAll('[data-ar][data-en]').forEach(element => {
//         element.textContent = element.getAttribute(`data-${lang}`);
//     });
    
//     // تحديث النصوص في الفورم
//     document.querySelectorAll('label[data-ar][data-en]').forEach(label => {
//         label.textContent = label.getAttribute(`data-${lang}`);
//     });
    
//     // تحديد اتجاه الصفحة
//     document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
//     document.documentElement.lang = lang;
// }

// دالة لتغيير اللغة
function changeLanguage(lang) {
    // حفظ اللغة في الكوكيز
    document.cookie = `site_language=${lang}; path=/; max-age=31536000`;
    
    // إعادة تحميل الصفحة لتطبيق التغييرات
    window.location.reload();
}

// حدث النقر على زر تغيير اللغة
document.addEventListener('DOMContentLoaded', function() {
    const languageToggle = document.getElementById('languageToggle');
    
    if (languageToggle) {
        languageToggle.addEventListener('click', function() {
            // تحديد اللغة الحالية من الزر
            const currentLangText = this.querySelector('.lang-text').textContent;
            const newLang = currentLangText === 'EN' ? 'en' : 'ar';
            
            changeLanguage(newLang);
        });
    }
});

// دالة مساعدة لقراءة الكوكيز
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}
        // تهيئة الفورم
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // هنا يمكنك إضافة كود إرسال البيانات
    const submitBtn = this.querySelector('.submit-btn');
    const originalText = submitBtn.querySelector('span').textContent;
    
    submitBtn.querySelector('span').textContent = 'جاري الإرسال...';
    submitBtn.disabled = true;
    
    // محاكاة إرسال البيانات
    setTimeout(() => {
        alert('تم إرسال رسالتك بنجاح!');
        submitBtn.querySelector('span').textContent = originalText;
        submitBtn.disabled = false;
        this.reset();
    }, 2000);
});
</script>
      <script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const mainNav = document.querySelector('.main-nav');
        const navLinks = document.querySelectorAll('.nav-list a');

        // وظيفة التبديل لفتح وإغلاق القائمة
        function toggleMenu() {
            mainNav.classList.toggle('open');
            menuToggle.classList.toggle('active');
            // تبديل منع التمرير في الخلفية عند فتح القائمة
            document.body.classList.toggle('menu-open');
        }

        // 1. فتح/إغلاق القائمة عند النقر على زر التبديل
        menuToggle.addEventListener('click', toggleMenu);

        // 2. إغلاق القائمة عند النقر على أي رابط (للتنقل)
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (mainNav.classList.contains('open')) {
                    toggleMenu();
                }
            });
        });
    });
</script>
    <script src="script.js"></script>
</body>
</html>