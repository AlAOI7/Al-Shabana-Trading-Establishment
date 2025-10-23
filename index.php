
  <?php include 'header.php'; ?>

<!-- قسم الهيرو المتحرك -->
<section id="hero" class="hero-slider">
    <div class="hero-background">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>
    </div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title animate-fade-in">
                    <span class="title-main">
                        <?php echo getTranslatedText('hero_title_main'); ?>
                    </span>
                    <span class="title-sub">
                        <?php echo getTranslatedText('hero_title_sub'); ?>
                    </span>
                </h1>
                <h2 class="hero-subtitle animate-fade-in-delay">
                    <?php echo getTranslatedText('hero_subtitle'); ?>
                </h2>
                <p class="hero-description animate-fade-in-delay-2">
                    <?php echo getTranslatedText('hero_description'); ?>
                </p>
                <div class="hero-actions animate-fade-in-delay-3">
                    <a href="products.html" class="cta-button primary">
                        <span><?php echo getTranslatedText('hero_button_products'); ?></span>
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <a href="about.html" class="cta-button secondary">
                        <span><?php echo getTranslatedText('hero_button_about'); ?></span>
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="contact.html" class="cta-button secondary">
                        <span><?php echo getTranslatedText('hero_button_contact'); ?></span>
                        <i class="fas fa-phone"></i>
                    </a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="product-showcase">
                    <div class="showcase-item item-1">
                        <i class="fas fa-fire"></i>
                        <span><?php echo getTranslatedText('hero_category_energy'); ?></span>
                    </div>
                    <div class="showcase-item item-2">
                        <i class="fas fa-tint"></i>
                        <span><?php echo getTranslatedText('hero_category_purification'); ?></span>
                    </div>
                    <div class="showcase-item item-3">
                        <i class="fas fa-spray-can"></i>
                        <span><?php echo getTranslatedText('hero_category_cleaning'); ?></span>
                    </div>
                    <div class="showcase-item item-4">
                        <i class="fas fa-box"></i>
                        <span><?php echo getTranslatedText('hero_category_supplies'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="scroll-indicator">
        <div class="scroll-arrow"></div>
    </div>
</section>
    <main>
        <!-- قسم من نحن -->
                
            <!-- قسم من نحن -->
            <section id="about" class="about-section">
                <div class="container">
                    <h2 class="section-title">
                            <p><?php echo getTranslatedText('about_description'); ?></p>
                  </h2>
                    <div class="about-content">
                        <div class="about-text">
                            <div class="company-description">
                                <p><?php echo getTranslatedText('about_description'); ?></p>
                            </div>
                            
                            <div class="mission-vision-grid">
                                <div class="mission-card">
                                    <div class="card-icon">
                                        <i class="fas fa-bullseye"></i>
                                    </div>
                                    <h3><?php echo getTranslatedText('mission_title'); ?></h3>
                                    <p><?php echo getTranslatedText('mission_description'); ?></p>
                                </div>
                                <div class="vision-card">
                                    <div class="card-icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <h3><?php echo getTranslatedText('vision_title'); ?></h3>
                                    <p><?php echo getTranslatedText('vision_description'); ?></p>
                                </div>
                                <div class="values-card">
                                    <div class="card-icon">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <h3><?php echo getTranslatedText('values_title'); ?></h3>
                                    <p><?php echo getTranslatedText('values_description'); ?></p>
                                </div>
                            </div>
                        </div>
                           <div class="about-visual">
                        <div class="floating-cards">
                            <div class="floating-card card-1">
                                <i class="fas fa-award"></i>
                                <span data-ar="جودة عالية" data-en="High Quality">جودة عالية</span>
                            </div>
                            <div class="floating-card card-2">
                                <i class="fas fa-users"></i>
                                <span data-ar="ثقة العملاء" data-en="Customer Trust">ثقة العملاء</span>
                            </div>
                            <div class="floating-card card-3">
                                <i class="fas fa-shipping-fast"></i>
                                <span data-ar="توصيل سريع" data-en="Fast Delivery">توصيل سريع</span>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </section>

            
            <!-- قسم الخدمات -->
            <section id="services" class="services-section">
                <div class="container">
                    <h2 class="section-title">
                        <span class="title-ar"><?php echo getTranslatedText('services_title'); ?></span>
                    </h2>
                    
                    <div class="services-grid">
                        <?php
                        // جلب الخدمات من قاعدة البيانات
                        try {
                            $stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
                            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($services) > 0) {
                                foreach ($services as $service) {
                                    // تحديد النص حسب اللغة
                                    $title = $current_lang == 'ar' ? $service['title'] : $service['title_en'];
                                    $description = $current_lang == 'ar' ? $service['description'] : $service['description_en'];
                                    
                                    // إذا لم يكن هناك نص بالإنجليزية، نستخدم النص العربي
                                    if (empty($title)) $title = $service['title'];
                                    if (empty($description)) $description = $service['description'];
                                    
                                    echo '
                                    <div class="service-card">
                                        <div class="service-icon">
                                            <i class="' . htmlspecialchars($service['icon']) . '"></i>
                                        </div>
                                        <div class="service-content">
                                            <h3 class="service-title">' . htmlspecialchars($title) . '</h3>
                                            <p class="service-description">' . htmlspecialchars($description) . '</p>
                                        </div>
                                    </div>';
                                }
                            } else {
                                echo '<div class="no-services">' . ($current_lang == 'ar' ? 'لا توجد خدمات مضافة حالياً' : 'No services added yet') . '</div>';
                            }
                        } catch (PDOException $e) {
                            echo '<div class="error-message">' . ($current_lang == 'ar' ? 'خطأ في تحميل الخدمات' : 'Error loading services') . '</div>';
                        }
                        ?>
                    </div>
                </div>
            </section>
                    <!-- قسم الإحصائيات والرسوم البيانية -->
                <!-- قسم الإحصائيات -->
                    <h2 class="section-title">
                        <span class="title"><?php echo getTranslatedText('stats_title'); ?></span>
                      </h2>
            <section id="stats" class="stats-section">
               
                <div class="container">
                    
                 
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" data-count="<?php echo getTranslatedText('products_count'); ?>">
                                    <?php echo getTranslatedText('products_count'); ?>
                                </div>
                                <div class="stat-label"><?php echo getTranslatedText('products_label'); ?></div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" data-count="<?php echo getTranslatedText('customers_count'); ?>">
                                    <?php echo getTranslatedText('customers_count'); ?>
                                </div>
                                <div class="stat-label"><?php echo getTranslatedText('customers_label'); ?></div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" data-count="<?php echo getTranslatedText('experience_count'); ?>">
                                    <?php echo getTranslatedText('experience_count'); ?>
                                </div>
                                <div class="stat-label"><?php echo getTranslatedText('experience_label'); ?></div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" data-count="<?php echo getTranslatedText('deliveries_count'); ?>">
                                    <?php echo getTranslatedText('deliveries_count'); ?>
                                </div>
                                <div class="stat-label"><?php echo getTranslatedText('deliveries_label'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="charts-container">
                        <div class="chart-wrapper">
                            <canvas id="salesChart"></canvas>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="productsChart"></canvas>
                        </div>
                    </div>
                    
                </div>
            </section>


            <!-- قسم المنتجات -->
            <section id="products" class="products-section">
                <div class="container">
                    <h2 class="section-title">
                    <span class="title-ar"><?php echo getTranslatedText('products_title'); ?></span>
                <span class="title-en"><?php echo getTranslatedText('products_title_en'); ?></span>
        
                    </h2>
                    
                    <div class="products-filter">
                                    <button class="filter-btn active" data-filter="all">
                                <?php echo getTranslatedText('filter_all'); ?>
                            </button>
                            <button class="filter-btn" data-filter="Double Class">
                                <?php echo getTranslatedText('brand_double_class'); ?>
                            </button>
                            <button class="filter-btn" data-filter="Handles">Handles</button>
                            <button class="filter-btn" data-filter="Cleaning">
                                <?php echo getTranslatedText('filter_cleaning'); ?>
                            </button>
                            <button class="filter-btn" data-filter="Energy">
                                <?php echo getTranslatedText('filter_energy'); ?>
                            </button>
                        </div>
                    
                    <div class="products-grid" id="productsContainer">
                        <!-- سيتم تعبئة المنتجات ديناميكياً من JavaScript -->
                    </div>
                    
                    <div class="products-loading">
                        <div class="loading-spinner"></div>
                        <p data-ar="جاري تحميل المنتجات..." data-en="Loading products...">جاري تحميل المنتجات...</p>
                    </div>
                </div>
            </section>

            <!-- قسم العلامات التجارية -->
            <section id="brands" class="brands-section">
                <div class="container">
                    <h2 class="section-title">
                        <span class="title-ar"><?php echo getTranslatedText('brands_title'); ?></span>
                    </h2>
                    
                    <div class="brands-slider">
                        <div class="brands-track">
                            <div class="brand-item">
                                <div class="brand-logo">
                                    <i class="fas fa-crown"></i>
                                </div>
                                <span class="brand-name"><?php echo getTranslatedText('brand_double_class'); ?></span>
                            </div>
                            <div class="brand-item">
                                <div class="brand-logo">
                                    <i class="fas fa-gem"></i>
                                </div>
                                <span class="brand-name"><?php echo getTranslatedText('brand_gator'); ?></span>
                            </div>
                            <div class="brand-item">
                                <div class="brand-logo">
                                    <i class="fas fa-star"></i>
                                </div>
                                <span class="brand-name"><?php echo getTranslatedText('brand_premium'); ?></span>
                            </div>
                            <div class="brand-item">
                                <div class="brand-logo">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <span class="brand-name"><?php echo getTranslatedText('brand_shield'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


                    <!-- قسم اتصل بنا -->
                <!-- قسم اتصل بنا -->
            <section id="contact" class="contact-section">
                <div class="container">
                    <h2 class="section-title">
                        <span class="title-ar"><?php echo getTranslatedText('contact_title'); ?></span>
                        <span class="title-en"><?php echo getTranslatedText('contact_title_en'); ?></span>
                    </h2>
                    
                    <div class="contact-grid">
                        <div class="contact-info">
                            <div class="contact-card">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-details">
                                     <h3 data-ar="العنوان" data-en="Address">العنوان</h3>
                                    <p><?php echo getTranslatedText('address'); ?></p>
                                </div>
                            </div>
                            
                            <div class="contact-card">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="contact-details">
                                  <h3 data-ar="هاتف" data-en="Phone">هاتف</h3>
                                    <p><?php echo getTranslatedText('phone'); ?></p>
                                </div>
                            </div>
                            
                            <div class="contact-card">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-details">
                                       <h3 data-ar="البريد الإلكتروني" data-en="Email">البريد الإلكتروني</h3>
                                    <p><?php echo getTranslatedText('email'); ?></p>
                                </div>
                            </div>
                            
                            <div class="contact-card">
                                <div class="contact-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="contact-details">
                                   <h3 data-ar="أوقات العمل" data-en="Working Hours">أوقات العمل</h3>
                                    <p><?php echo getTranslatedText('working_hours'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <form class="contact-form" id="contactForm">
                            <div class="form-group">
                                <input type="text" id="name" name="name" required>
                                <label for="name"><?php echo getTranslatedText('form_name'); ?></label>
                            </div>
                            
                            <div class="form-group">
                                <input type="email" id="email" name="email" required>
                                <label for="email"><?php echo getTranslatedText('form_email'); ?></label>
                            </div>
                            
                            <div class="form-group">
                                <input type="tel" id="phone" name="phone">
                                <label for="phone"><?php echo getTranslatedText('form_phone'); ?></label>
                            </div>
                            
                            <div class="form-group">
                                <textarea id="message" name="message" rows="5" required></textarea>
                                <label for="message"><?php echo getTranslatedText('form_message'); ?></label>
                            </div>
                            
                            <button type="submit" class="submit-btn">
                                <span><?php echo getTranslatedText('submit_btn'); ?></span>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </section>

     </main>
  <?php include 'footer.php'; ?>
   

    <!-- مودال المنتج -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <div class="modal-body">
                <div class="modal-image">
                    <img id="modal-img" src="" alt="صورة المنتج">
                </div>
                <div class="modal-details">
                    <h3 id="modal-name">اسم المنتج</h3>
                    <p id="modal-description">وصف المنتج</p>
                    <div class="product-specs">
                        <div class="spec-item">
                            <strong data-ar="الكود:" data-en="Code:">الكود:</strong>
                            <span id="modal-code"></span>
                        </div>
                        <div class="spec-item">
                            <strong data-ar="المجموعة:" data-en="Group:">المجموعة:</strong>
                            <span id="modal-group"></span>
                        </div>
                        <div class="spec-item">
                            <strong data-ar="العلامة:" data-en="Brand:">العلامة:</strong>
                            <span id="modal-brand"></span>
                        </div>
                        <div class="spec-item">
                            <strong data-ar="التغليف:" data-en="Packing:">التغليف:</strong>
                            <span id="modal-packing"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


