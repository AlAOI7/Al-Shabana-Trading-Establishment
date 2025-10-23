<?php
// جلب بيانات الاتصال
try {
    $stmt = $pdo->query("SELECT * FROM contact_info LIMIT 1");
    $contact_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$contact_info) {
        // إذا لم توجد بيانات، استخدم القيم الافتراضية
        $contact_info = [
            'phone' => '+966555382875',
            'email' => 'amnt.est.sa@gmail.com',
            'address' => 'المملكة العربية السعودية - الرياض - حي العليا',
            'address_en' => 'Saudi Arabia - Riyadh - Al Olaya District',
            'working_hours_ar' => 'السبت - الخميس: 8:00 ص - 6:00 م',
            'working_hours_en' => 'Saturday - Thursday: 8:00 AM - 6:00 PM',
            'social_facebook' => 'https://facebook.com/alshabana',
            'social_twitter' => 'https://twitter.com/alshabana',
            'social_instagram' => 'https://instagram.com/alshabana',
            'social_whatsapp' => 'https://wa.me/966555382875'
        ];
    }
} catch (PDOException $e) {
    // في حالة الخطأ، استخدم القيم الافتراضية
    $contact_info = [
        'phone' => '+966555382875',
        'email' => 'amnt.est.sa@gmail.com',
        'address' => 'المملكة العربية السعودية - الرياض - حي العليا',
        'address_en' => 'Saudi Arabia - Riyadh - Al Olaya District',
        'working_hours_ar' => 'السبت - الخميس: 8:00 ص - 6:00 م',
        'working_hours_en' => 'Saturday - Thursday: 8:00 AM - 6:00 PM',
        'social_facebook' => 'https://facebook.com/alshabana',
        'social_twitter' => 'https://twitter.com/alshabana',
        'social_instagram' => 'https://instagram.com/alshabana',
        'social_whatsapp' => 'https://wa.me/966555382875'
    ];
}
?>

<!-- وسائل التواصل الاجتماعي -->
<div class="social-contact-section">
    <div class="section-header">
        <h2><?php echo $current_lang == 'ar' ? 'تواصل معنا على وسائل التواصل' : 'Connect with us on Social Media'; ?></h2>
        <p><?php echo $current_lang == 'ar' ? 'تابعنا للحصول على آخر الأخبار والعروض' : 'Follow us for the latest news and offers'; ?></p>
    </div>

    <div class="social-links-grid">
        <!-- واتساب -->
        <a href="<?php echo htmlspecialchars($contact_info['social_whatsapp']); ?>" class="social-link whatsapp" target="_blank">
            <div class="social-icon">
                <i class="fab fa-whatsapp"></i>
            </div>
            <div class="social-info">
                <h3><?php echo $current_lang == 'ar' ? 'واتساب' : 'WhatsApp'; ?></h3>
                <p><?php echo $current_lang == 'ar' ? 'راسلنا مباشرة' : 'Message us directly'; ?></p>
                <span class="social-handle"><?php echo htmlspecialchars($contact_info['phone']); ?></span>
            </div>
        </a>

        <!-- تويتر -->
        <a href="<?php echo htmlspecialchars($contact_info['social_twitter']); ?>" class="social-link twitter" target="_blank">
            <div class="social-icon">
                <i class="fab fa-twitter"></i>
            </div>
            <div class="social-info">
                <h3><?php echo $current_lang == 'ar' ? 'تويتر' : 'Twitter'; ?></h3>
                <p><?php echo $current_lang == 'ar' ? 'تابع آخر التغريدات' : 'Follow latest tweets'; ?></p>
                <span class="social-handle">@alshabana</span>
            </div>
        </a>

        <!-- انستغرام -->
        <a href="<?php echo htmlspecialchars($contact_info['social_instagram']); ?>" class="social-link instagram" target="_blank">
            <div class="social-icon">
                <i class="fab fa-instagram"></i>
            </div>
            <div class="social-info">
                <h3><?php echo $current_lang == 'ar' ? 'انستغرام' : 'Instagram'; ?></h3>
                <p><?php echo $current_lang == 'ar' ? 'شاهد منتجاتنا' : 'View our products'; ?></p>
                <span class="social-handle">@alshabana</span>
            </div>
        </a>

        <!-- فيسبوك -->
        <a href="<?php echo htmlspecialchars($contact_info['social_facebook']); ?>" class="social-link facebook" target="_blank">
            <div class="social-icon">
                <i class="fab fa-facebook-f"></i>
            </div>
            <div class="social-info">
                <h3><?php echo $current_lang == 'ar' ? 'فيسبوك' : 'Facebook'; ?></h3>
                <p><?php echo $current_lang == 'ar' ? 'انضم إلى مجتمعنا' : 'Join our community'; ?></p>
                <span class="social-handle">AlShabanaTrading</span>
            </div>
        </a>
    </div>
</div>