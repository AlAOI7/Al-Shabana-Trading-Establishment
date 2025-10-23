  <?php include 'header.php'; ?>

<?php
// جلب بيانات صفحة من نحن
try {
    $stmt = $pdo->query("SELECT * FROM about_page ORDER BY display_order ASC");
    $about_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // تحويل البيانات إلى مصفوفة سهلة الاستخدام
    $about_sections = [];
    foreach ($about_data as $section) {
        $about_sections[$section['section_type']] = $section;
    }
} catch (PDOException $e) {
    $about_sections = [];
}
?>

<!-- قسم المقدمة -->
<section class="page-intro">
    <div class="container">
        <div class="intro-content animate-fade-in">
            <?php if (isset($about_sections['intro'])): ?>
                <h1 class="intro-title">
                    <?php echo $current_lang == 'ar' ? 
                        htmlspecialchars($about_sections['intro']['title_ar']) : 
                        htmlspecialchars($about_sections['intro']['title_en']); ?>
                </h1>
                <p class="intro-subtitle">
                    <?php echo $current_lang == 'ar' ? 
                        htmlspecialchars($about_sections['intro']['content_ar']) : 
                        htmlspecialchars($about_sections['intro']['content_en']); ?>
                </p>
            <?php else: ?>
                <h1 class="intro-title">نحن مؤسسة الشبانات: التزام بالجودة والموثوقية</h1>
                <p class="intro-subtitle">أكثر من عقد من الخبرة في توفير مستلزمات المنزل الأساسية</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- قسم من نحن الرئيسي -->
<section id="about-page" class="about-section large-padding">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <!-- القصة -->
                <h2 class="section-title">
                    <span class="title-ar">
                        <?php echo isset($about_sections['story']) ? 
                            htmlspecialchars($about_sections['story']['title_ar']) : 'قصتنا'; ?>
                    </span>
                </h2>
                
                <div class="company-description animate-fade-in-delay">
                    <?php if (isset($about_sections['story'])): ?>
                        <p>
                            <?php echo $current_lang == 'ar' ? 
                                htmlspecialchars($about_sections['story']['content_ar']) : 
                                htmlspecialchars($about_sections['story']['content_en']); ?>
                        </p>
                    <?php else: ?>
                        <p>تأسست مؤسسة عبدالرحمن محمد الشبانات التجارية لتكون رائدة في توفير مستلزمات المنزل الأساسية...</p>
                    <?php endif; ?>
                </div>

                <!-- البيانات الرسمية -->
                <div class="official-details-container animate-fade-in-delay-2">
                    <h3 class="details-title">
                        <i class="fas fa-map-marked-alt"></i> 
                        <span>
                            <?php echo isset($about_sections['official_details']) ? 
                                ($current_lang == 'ar' ? 
                                    htmlspecialchars($about_sections['official_details']['title_ar']) : 
                                    htmlspecialchars($about_sections['official_details']['title_en'])) : 
                                'بيانات السجل الرسمي والموقع'; ?>
                        </span>
                    </h3>
                    <div class="official-details-grid">
                        <div class="detail-item">
                            <p><strong><i class="fas fa-building"></i> <?php echo $current_lang == 'ar' ? 'الاسم الرسمي:' : 'Official Name:'; ?></strong> 
                                <?php echo isset($about_sections['official_details']) ? 
                                    ($current_lang == 'ar' ? 
                                        htmlspecialchars($about_sections['official_details']['content_ar']) : 
                                        htmlspecialchars($about_sections['official_details']['content_en'])) : 
                                    'مؤسسة عبدالرحمن محمد الشبانات للمقاولات'; ?>
                            </p>
                            <p><strong><i class="fas fa-calendar-alt"></i> <?php echo $current_lang == 'ar' ? 'تاريخ التأسيس:' : 'Establishment Date:'; ?></strong> 19/1/2014</p>
                        </div>
                        <div class="detail-item">
                            <p><strong><i class="fas fa-tag"></i> <?php echo $current_lang == 'ar' ? 'العنوان المختصر:' : 'Short Address:'; ?></strong> RQSA7577</p>
                            <p><strong><i class="fas fa-map-pin"></i> <?php echo $current_lang == 'ar' ? 'المدينة:' : 'City:'; ?></strong> <?php echo $current_lang == 'ar' ? 'الرياض، المملكة العربية السعودية' : 'Riyadh, Saudi Arabia'; ?></p>
                        </div>
                    </div>
                   <p class="address-line">
                        <strong><?php echo $current_lang == 'ar' ? 'العنوان التفصيلي:' : 'Detailed Address:'; ?></strong> 
                        <?php echo $current_lang == 'ar' ? 
                            'حي السالم، شارع الإمام الشافعي، مبنى 7577، الرقم الفرعي 2454، الرمز البريدي 14224.' : 
                            "Al Salim District, Imam Al-Shafi'i Street, Building 7577, Sub-number 2454, Postal Code 14224."; ?>
                    </p>
                </div>

                <!-- الرسالة والرؤية والقيم -->
                <div class="mission-vision-grid">
                    <!-- الرسالة -->
                    <div class="mission-card animate-fade-in-delay">
                        <div class="card-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3>
                            <?php echo isset($about_sections['mission']) ? 
                                ($current_lang == 'ar' ? 
                                    htmlspecialchars($about_sections['mission']['title_ar']) : 
                                    htmlspecialchars($about_sections['mission']['title_en'])) : 
                                'رسالتنا'; ?>
                        </h3>
                        <p>
                            <?php echo isset($about_sections['mission']) ? 
                                ($current_lang == 'ar' ? 
                                    htmlspecialchars($about_sections['mission']['content_ar']) : 
                                    htmlspecialchars($about_sections['mission']['content_en'])) : 
                                'توفير تشكيلة متكاملة وموثوقة من المنتجات بأسعار تنافسية تلبي احتياجات العملاء اليومية.'; ?>
                        </p>
                    </div>
                    
                    <!-- الرؤية -->
                    <div class="vision-card animate-fade-in-delay-2">
                        <div class="card-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3>
                            <?php echo isset($about_sections['vision']) ? 
                                ($current_lang == 'ar' ? 
                                    htmlspecialchars($about_sections['vision']['title_ar']) : 
                                    htmlspecialchars($about_sections['vision']['title_en'])) : 
                                'رؤيتنا'; ?>
                        </h3>
                        <p>
                            <?php echo isset($about_sections['vision']) ? 
                                ($current_lang == 'ar' ? 
                                    htmlspecialchars($about_sections['vision']['content_ar']) : 
                                    htmlspecialchars($about_sections['vision']['content_en'])) : 
                                'أن نكون الخيار الأول في قطاع البيع بالتجزئة للمستلزمات المنزلية في المنطقة.'; ?>
                        </p>
                    </div>
                    
                    <!-- القيم -->
                    <div class="values-card animate-fade-in-delay-3">
                        <div class="card-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3>
                            <?php echo isset($about_sections['values']) ? 
                                ($current_lang == 'ar' ? 
                                    htmlspecialchars($about_sections['values']['title_ar']) : 
                                    htmlspecialchars($about_sections['values']['title_en'])) : 
                                'قيمنا'; ?>
                        </h3>
                        <p>
                            <?php echo isset($about_sections['values']) ? 
                                ($current_lang == 'ar' ? 
                                    htmlspecialchars($about_sections['values']['content_ar']) : 
                                    htmlspecialchars($about_sections['values']['content_en'])) : 
                                'الجودة، الموثوقية، الابتكار، ورضا العملاء هي أساس كل ما نقوم به.'; ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="about-image-placeholder full-image animate-fade-in">
                <div class="image-frame">
                    <?php
                    $about_image = isset($about_sections['story']['image']) ? $about_sections['story']['image'] : '2.png';
                    ?>
                    <img src="<?php echo htmlspecialchars($about_image); ?>" alt="صورة للمؤسسة أو أحد الأقسام الرئيسية">
                    <div class="image-overlay">
                        <div class="overlay-content">
                            <h3><?php echo $current_lang == 'ar' ? 'منذ 2014' : 'Since 2014'; ?></h3>
                            <p><?php echo $current_lang == 'ar' ? 'نخدم عملائنا بأفضل المنتجات' : 'Serving our customers with the best products'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
   
   <?php include 'footer.php'; ?>
    <script>
        // نظام الترجمة
        document.addEventListener('DOMContentLoaded', function() {
            // تهيئة وظائف الترجمة
            initLanguageToggle();
            
            // تهيئة زر العودة للأعلى
            initScrollToTop();
            
            // تهيئة القائمة المتنقلة
            initMobileMenu();
            
            // تهيئة عداد الإحصائيات
            initStatsCounter();
        });

        // وظيفة الترجمة
        function initLanguageToggle() {
            const languageToggle = document.getElementById('languageToggle');
            const langText = document.querySelector('.lang-text');
            
            // التحقق من اللغة المحفوظة في التخزين المحلي
            const savedLanguage = localStorage.getItem('siteLanguage') || 'ar';
            setLanguage(savedLanguage);
            
            // إضافة حدث النقر على زر الترجمة
            if (languageToggle) {
                languageToggle.addEventListener('click', function() {
                    const currentLang = document.documentElement.lang;
                    const newLang = currentLang === 'ar' ? 'en' : 'ar';
                    
                    setLanguage(newLang);
                    localStorage.setItem('siteLanguage', newLang);
                });
            }
        }

        // تعيين اللغة للموقع
        function setLanguage(lang) {
            // تحديث سمة اللغة في العنصر الرئيسي
            document.documentElement.lang = lang;
            document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
            
            // تحديث نص زر الترجمة
            const langText = document.querySelector('.lang-text');
            if (langText) {
                langText.textContent = lang === 'ar' ? 'EN' : 'AR';
            }
            
            // تحديث جميع النصوص ذات سمة البيانات
            updateTextsByLanguage(lang);
            
            // تحديث العناصر الخاصة بالنماذج
            updateFormElementsLanguage(lang);
            
            // تحديث العناوين المزدوجة
            updateDualTitles(lang);
        }

        // تحديث النصوص بناءً على اللغة
        function updateTextsByLanguage(lang) {
            // تحديث النصوص مع سمة data-ar و data-en
            const elements = document.querySelectorAll('[data-ar], [data-en]');
            
            elements.forEach(element => {
                const text = lang === 'ar' ? element.getAttribute('data-ar') : element.getAttribute('data-en');
                if (text) {
                    element.textContent = text;
                }
            });
            
            // تحديث العناصر النائبة في حقول الإدخال
            const inputElements = document.querySelectorAll('input[data-ar-placeholder], input[data-en-placeholder]');
            inputElements.forEach(input => {
                const placeholder = lang === 'ar' ? input.getAttribute('data-ar-placeholder') : input.getAttribute('data-en-placeholder');
                if (placeholder) {
                    input.placeholder = placeholder;
                }
            });
            
            // تحديث خيارات القائمة المنسدلة
            const optionElements = document.querySelectorAll('option[data-ar], option[data-en]');
            optionElements.forEach(option => {
                const text = lang === 'ar' ? option.getAttribute('data-ar') : option.getAttribute('data-en');
                if (text) {
                    option.textContent = text;
                }
            });
        }

        // تحديث عناصر النماذج بناءً على اللغة
        function updateFormElementsLanguage(lang) {
            // تحديث تسميات النموذج
            const labels = document.querySelectorAll('label[data-ar], label[data-en]');
            labels.forEach(label => {
                const text = lang === 'ar' ? label.getAttribute('data-ar') : label.getAttribute('data-en');
                if (text) {
                    label.textContent = text;
                }
            });
            
            // تحديث نص أزرار الإرسال
            const buttons = document.querySelectorAll('button[data-ar], button[data-en]');
            buttons.forEach(button => {
                const text = lang === 'ar' ? button.getAttribute('data-ar') : button.getAttribute('data-en');
                if (text) {
                    button.textContent = text;
                }
            });
        }

        // تحديث العناوين المزدوجة (العربية والإنجليزية)
        function updateDualTitles(lang) {
            const sectionTitles = document.querySelectorAll('.section-title');
            
            sectionTitles.forEach(title => {
                const arabicTitle = title.querySelector('.title-ar');
                const englishTitle = title.querySelector('.title-en');
                
                if (arabicTitle && englishTitle) {
                    if (lang === 'ar') {
                        arabicTitle.style.display = 'block';
                        englishTitle.style.display = 'none';
                    } else {
                        arabicTitle.style.display = 'none';
                        englishTitle.style.display = 'block';
                    }
                }
            });
        }

        // تهيئة زر العودة للأعلى
        function initScrollToTop() {
            const scrollButton = document.querySelector('.scroll-to-top');
            
            // إظهار/إخفاء الزر عند التمرير
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    scrollButton.classList.add('visible');
                } else {
                    scrollButton.classList.remove('visible');
                }
            });
            
            // النقر للعودة للأعلى
            scrollButton.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

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

        // تهيئة عداد الإحصائيات
        function initStatsCounter() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            // التحقق مما إذا كانت العدادات موجودة في الصفحة
            if (statNumbers.length > 0) {
                // إنشاء Intersection Observer لتشغيل العدادات عند ظهورها
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            startCounters();
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.5 });
                
                // مراقبة قسم الإحصائيات
                const statsSection = document.querySelector('.stats-section');
                if (statsSection) {
                    observer.observe(statsSection);
                }
            }
        }

        // تشغيل العدادات
        function startCounters() {
            const counters = document.querySelectorAll('.stat-number');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-count'));
                const duration = 2000; // 2 ثانية
                const step = target / (duration / 16); // 60 إطار في الثانية
                let current = 0;
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                }, 16);
            });
        }
    </script>