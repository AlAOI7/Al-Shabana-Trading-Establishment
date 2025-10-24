 <?php
require_once 'config.php';
include 'config/database.php';
// تحديد اللغة الحالية
// تحديد اللغة الحالية
$current_lang = isset($_COOKIE['site_language']) ? $_COOKIE['site_language'] : 'ar';


// تحديد نص زر اللغة
$lang_toggle_text = $current_lang == 'ar' ? 'EN' : 'AR';
?>

<?php
    // جلب بيانات الاتصال
    try {
        $stmt = $pdo->query("SELECT * FROM contact_info LIMIT 1");
        $contact_info = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $contact_info = [];
    }

    // جلب الأسئلة الشائعة
    try {
        $stmt = $pdo->query("SELECT * FROM faqs WHERE is_active = 1 ORDER BY display_order ASC");
        $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $faqs = [];
    }
?>
 <!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اتصل بنا - مؤسسة عبدالرحمن محمد الشبانات التجارية</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* متغيرات الألوان والتنسيقات */
        :root {
            --primary-blue: #0A1E4A;
            --primary-dark: #06122E;
            --primary-light: #1E3A8A;
            --accent-gold: #FFC300;
            --accent-orange: #FF6B00;
            --secondary-light: #F4F7FC;
            --secondary-gray: #64748B;
            --text-dark: #1E293B;
            --text-light: #FFFFFF;
            --success: #10B981;
            --warning: #F59E0B;
            --error: #EF4444;
            --border-radius: 16px;
            --border-radius-sm: 8px;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.15);
            --shadow-lg: 0 8px 30px rgba(0,0,0,0.2);
            --transition-fast: 0.3s;
            --transition-medium: 0.5s;
            --transition-slow: 0.8s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--secondary-light);
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* شريط التنقل العلوي */
        .top-bar {
            background-color: var(--primary-blue);
            color: var(--text-light);
            padding: 8px 0;
            font-size: 0.875rem;
        }

        .top-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .lang-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: var(--text-light);
            padding: 5px 10px;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all var(--transition-fast);
        }

        .lang-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .contact-top a {
            color: var(--text-light);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* الهيدر الرئيسي */
        .main-header {
            background-color: var(--text-light);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo-container {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-dark);
            gap: 10px;
        }

        .logo-image {
            height: 50px;
            width: auto;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-main {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-blue);
        }

        .logo-sub {
            font-size: 0.8rem;
            color: var(--secondary-gray);
        }

        .main-nav .nav-list {
            display: flex;
            list-style: none;
            gap: 25px;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 12px;
            border-radius: var(--border-radius-sm);
            transition: all var(--transition-fast);
        }

        .nav-link:hover, .nav-link.active {
            background-color: var(--primary-blue);
            color: var(--text-light);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-btn, .menu-toggle {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--text-dark);
            cursor: pointer;
            padding: 5px;
        }

        .menu-toggle {
            display: none;
            flex-direction: column;
            gap: 4px;
        }

        .menu-toggle span {
            width: 25px;
            height: 3px;
            background-color: var(--text-dark);
            transition: all var(--transition-fast);
        }

        /* قسم الهيرو للاتصال */
        .contact-hero {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
            color: var(--text-light);
            padding: 80px 0;
            text-align: center;
        }

        .hero-title {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 800;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .contact-breadcrumb {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .contact-breadcrumb a {
            color: var(--text-light);
            text-decoration: none;
            transition: opacity var(--transition-fast);
        }

        .contact-breadcrumb a:hover {
            opacity: 0.8;
        }

        .contact-breadcrumb span {
            color: rgba(255, 255, 255, 0.8);
        }

        /* قسم معلومات الاتصال */
        .contact-info-section {
            margin: 80px 0;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
            font-weight: 800;
        }

        .section-header p {
            font-size: 1.2rem;
            color: var(--secondary-gray);
            max-width: 600px;
            margin: 0 auto;
        }

        .contact-methods-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .contact-method-card {
            background: var(--text-light);
            padding: 40px 30px;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: var(--shadow-md);
            transition: all var(--transition-fast);
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .contact-method-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-blue), var(--accent-gold));
            transform: scaleX(0);
            transition: transform var(--transition-fast);
        }

        .contact-method-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-blue);
        }

        .contact-method-card:hover::before {
            transform: scaleX(1);
        }

        .method-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: var(--text-light);
            font-size: 2rem;
        }

        .method-content h3 {
            color: var(--text-dark);
            font-size: 1.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .contact-numbers,
        .contact-emails {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        .contact-link {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-dark);
            text-decoration: none;
            padding: 10px 15px;
            border-radius: var(--border-radius-sm);
            transition: all var(--transition-fast);
            background: var(--secondary-light);
        }

        .contact-link:hover {
            background: var(--primary-blue);
            color: var(--text-light);
            transform: translateX(-5px);
        }

        .contact-link i {
            width: 20px;
            text-align: center;
        }

        .contact-address {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
            text-align: right;
        }

        .contact-address i {
            color: var(--primary-blue);
            font-size: 1.5rem;
            margin-top: 5px;
        }

        .contact-address div {
            flex: 1;
        }

        .contact-address strong {
            display: block;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .contact-address p {
            margin: 2px 0;
            color: var(--secondary-gray);
            font-size: 0.95rem;
        }

        .map-btn {
            background: var(--primary-blue);
            color: var(--text-light);
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Tajawal', sans-serif;
            font-weight: 600;
        }

        .map-btn:hover {
            background: var(--accent-gold);
            transform: scale(1.05);
        }

        .working-hours {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }

        .time-slot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: var(--secondary-light);
            border-radius: var(--border-radius-sm);
        }

        .time-slot span:first-child {
            font-weight: 600;
            color: var(--text-dark);
        }

        .time-slot span:last-child {
            color: var(--secondary-gray);
        }

        .method-description {
            color: var(--secondary-gray);
            font-size: 0.95rem;
            margin-top: 15px;
            line-height: 1.5;
        }

        /* قسم النموذج والخريطة */
        .contact-form-map-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin: 80px 0;
            align-items: start;
        }

        .contact-form-container {
            background: var(--text-light);
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
        }

        .form-header {
            margin-bottom: 40px;
            text-align: right;
        }

        .form-header h2 {
            font-size: 2.2rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
            font-weight: 800;
        }

        .form-header p {
            color: var(--secondary-gray);
            font-size: 1.1rem;
        }

        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            position: relative;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .contact-form input,
        .contact-form select,
        .contact-form textarea {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid var(--secondary-light);
            border-radius: var(--border-radius-sm);
            background: var(--secondary-light);
            color: var(--text-dark);
            font-family: 'Tajawal', sans-serif;
            font-size: 1rem;
            transition: all var(--transition-fast);
            outline: none;
        }

        .contact-form input:focus,
        .contact-form select:focus,
        .contact-form textarea:focus {
            border-color: var(--primary-blue);
            background: var(--text-light);
            box-shadow: 0 0 0 3px rgba(10, 30, 74, 0.1);
        }

        .contact-form label {
            position: absolute;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            color: var(--secondary-gray);
            transition: all var(--transition-fast);
            pointer-events: none;
            background: var(--secondary-light);
            padding: 0 5px;
        }

        .contact-form textarea + label {
            top: 25px;
            transform: none;
        }

        .contact-form input:focus + label,
        .contact-form input:not(:placeholder-shown) + label,
        .contact-form select:focus + label,
        .contact-form select:not([value=""]) + label,
        .contact-form textarea:focus + label,
        .contact-form textarea:not(:placeholder-shown) + label {
            top: 0;
            font-size: 0.8rem;
            color: var(--primary-blue);
            background: var(--text-light);
        }

        .contact-form textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-error {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 5px;
            text-align: right;
            display: none;
        }

        .form-error.show {
            display: block;
        }

        .form-actions {
            text-align: left;
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-light));
            color: var(--text-light);
            border: none;
            padding: 18px 40px;
            border-radius: 25px;
            cursor: pointer;
            transition: all var(--transition-fast);
            font-family: 'Tajawal', sans-serif;
            font-size: 1.1rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(10, 30, 74, 0.3);
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-loading {
            display: none;
            align-items: center;
            gap: 8px;
        }

        .submit-btn.loading .btn-text {
            display: none;
        }

        .submit-btn.loading .btn-loading {
            display: flex;
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid var(--text-light);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* قسم الخريطة */
        .map-container {
            background: var(--text-light);
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            height: fit-content;
        }

        .map-header {
            margin-bottom: 30px;
            text-align: right;
        }

        .map-header h2 {
            font-size: 2.2rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
            font-weight: 800;
        }

        .map-header p {
            color: var(--secondary-gray);
            font-size: 1.1rem;
        }

        .map-wrapper {
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .static-map {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
            height: 300px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            position: relative;
            overflow: hidden;
        }

        .map-placeholder {
            text-align: center;
            z-index: 2;
        }

        .map-placeholder i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .map-placeholder h3 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .map-placeholder p {
            font-size: 1.1rem;
            margin-bottom: 25px;
            opacity: 0.9;
        }

        .open-map-btn {
            background: rgba(255, 255, 255, 0.2);
            color: var(--text-light);
            border: 2px solid var(--text-light);
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Tajawal', sans-serif;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .open-map-btn:hover {
            background: var(--text-light);
            color: var(--primary-blue);
            transform: scale(1.05);
        }

        .location-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-top: 25px;
        }

        .location-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: var(--secondary-light);
            border-radius: var(--border-radius-sm);
            transition: all var(--transition-fast);
        }

        .location-item:hover {
            background: var(--primary-blue);
            color: var(--text-light);
            transform: translateX(-5px);
        }

        .location-item:hover i,
        .location-item:hover strong,
        .location-item:hover span {
            color: var(--text-light);
        }

        .location-item i {
            color: var(--primary-blue);
            font-size: 1.3rem;
            width: 30px;
            text-align: center;
        }

        .location-item div {
            flex: 1;
        }

        .location-item strong {
            display: block;
            color: var(--text-dark);
            margin-bottom: 5px;
            font-size: 0.95rem;
        }

        .location-item span {
            color: var(--secondary-gray);
            font-size: 0.85rem;
        }

        /* وسائل التواصل الاجتماعي */
        .social-contact-section {
            margin: 80px 0;
        }

        .social-links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 50px;
        }

        .social-link {
            background: var(--text-light);
            padding: 30px 25px;
            border-radius: var(--border-radius);
            text-decoration: none;
            color: inherit;
            transition: all var(--transition-fast);
            border: 2px solid transparent;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .social-link:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .social-link.whatsapp:hover {
            border-color: #25D366;
            background: rgba(37, 211, 102, 0.05);
        }

        .social-link.twitter:hover {
            border-color: #1DA1F2;
            background: rgba(29, 161, 242, 0.05);
        }

        .social-link.instagram:hover {
            border-color: #E4405F;
            background: rgba(228, 64, 95, 0.05);
        }

        .social-link.facebook:hover {
            border-color: #1877F2;
            background: rgba(24, 119, 242, 0.05);
        }

        .social-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--text-light);
        }

        .social-link.whatsapp .social-icon {
            background: #25D366;
        }

        .social-link.twitter .social-icon {
            background: #1DA1F2;
        }

        .social-link.instagram .social-icon {
            background: linear-gradient(45deg, #405DE6, #5851DB, #833AB4, #C13584, #E1306C, #FD1D1D);
        }

        .social-link.facebook .social-icon {
            background: #1877F2;
        }

        .social-info {
            flex: 1;
            text-align: right;
        }

        .social-info h3 {
            color: var(--text-dark);
            font-size: 1.3rem;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .social-info p {
            color: var(--secondary-gray);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .social-handle {
            color: var(--primary-blue);
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* الأسئلة الشائعة */
        .faq-section {
            margin: 80px 0;
        }

        .faq-grid {
            max-width: 800px;
            margin: 50px auto 0;
        }

        .faq-item {
            background: var(--text-light);
            border-radius: var(--border-radius);
            margin-bottom: 15px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 2px solid transparent;
            transition: all var(--transition-fast);
        }

        .faq-item:hover {
            border-color: var(--primary-blue);
        }

        .faq-question {
            padding: 25px 30px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background var(--transition-fast);
        }

        .faq-question:hover {
            background: var(--secondary-light);
        }

        .faq-question h3 {
            color: var(--text-dark);
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
            flex: 1;
        }

        .faq-question i {
            color: var(--primary-blue);
            transition: transform var(--transition-fast);
            margin-right: 15px;
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 30px;
            max-height: 0;
            overflow: hidden;
            transition: all var(--transition-fast);
        }

        .faq-item.active .faq-answer {
            padding: 0 30px 25px;
            max-height: 500px;
        }

        .faq-answer p {
            color: var(--secondary-gray);
            line-height: 1.6;
            margin: 0;
        }

        /* الفوتر */
        .main-footer {
            background: var(--primary-dark);
            color: var(--text-light);
            padding: 60px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            margin-bottom: 20px;
            font-size: 1.2rem;
            color: var(--text-light);
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .footer-logo img {
            height: 40px;
            width: auto;
        }

        .footer-description {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color var(--transition-fast);
        }

        .footer-links a:hover {
            color: var(--text-light);
        }

        .newsletter-form {
            display: flex;
            margin-top: 15px;
        }

        .newsletter-form input {
            flex: 1;
            padding: 12px 15px;
            border: none;
            border-radius: var(--border-radius-sm) 0 0 var(--border-radius-sm);
            font-family: 'Tajawal', sans-serif;
        }

        .newsletter-form button {
            background: var(--accent-gold);
            color: var(--text-dark);
            border: none;
            padding: 0 20px;
            border-radius: 0 var(--border-radius-sm) var(--border-radius-sm) 0;
            cursor: pointer;
            transition: background var(--transition-fast);
        }

        .newsletter-form button:hover {
            background: var(--accent-orange);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
        }

        /* زر العودة للأعلى */
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            left: 30px;
            width: 50px;
            height: 50px;
            background-color: var(--primary-blue);
            color: var(--text-light);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-fast);
            z-index: 1000;
            box-shadow: var(--shadow-md);
        }

        .scroll-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .scroll-to-top:hover {
            background-color: var(--accent-gold);
            transform: translateY(-2px);
        }

        /* الإشعارات */
        .notification {
            position: fixed;
            top: 100px;
            right: 20px;
            padding: 15px 20px;
            border-radius: var(--border-radius-sm);
            color: var(--text-light);
            font-weight: 500;
            z-index: 10000;
            transform: translateX(400px);
            opacity: 0;
            transition: all var(--transition-fast);
            max-width: 350px;
            box-shadow: var(--shadow-md);
        }

        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }

        .notification.success {
            background-color: var(--success);
            border-left: 4px solid #059669;
        }

        .notification.error {
            background-color: var(--error);
            border-left: 4px solid #dc2626;
        }

        /* تنسيقات الاستجابة للغة */
        [dir="ltr"] .scroll-to-top {
            left: auto;
            right: 30px;
        }

        [dir="ltr"] .faq-question {
            flex-direction: row-reverse;
        }

        [dir="ltr"] .contact-form label {
            right: auto;
            left: 20px;
        }

        [dir="ltr"] .contact-breadcrumb i {
            transform: rotate(180deg);
        }

        [dir="ltr"] .contact-address {
            text-align: left;
        }

        [dir="ltr"] .form-actions {
            text-align: right;
        }

        [dir="ltr"] .location-item {
            text-align: left;
        }

        [dir="ltr"] .contact-link {
            justify-content: flex-start;
        }

        [dir="ltr"] .social-info {
            text-align: left;
        }

        /* تنسيقات للأجهزة المحمولة */
        @media (max-width: 768px) {
            .menu-toggle {
                display: flex;
            }

            .main-nav {
                position: fixed;
                top: 0;
                right: -100%;
                width: 80%;
                height: 100vh;
                background: var(--text-light);
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
                transition: right var(--transition-fast);
                z-index: 1001;
                padding: 80px 20px 20px;
            }

            .main-nav.active {
                right: 0;
            }

            .nav-list {
                flex-direction: column;
            }

            .contact-form-map-section {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .top-bar-content {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }

            .hero-title {
                font-size: 2rem;
            }

            .notification {
                right: 10px;
                left: 10px;
                max-width: none;
            }

            .scroll-to-top {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
            }

            [dir="ltr"] .main-nav {
                right: auto;
                left: -100%;
            }

            [dir="ltr"] .main-nav.active {
                left: 0;
            }
        }

        @media (max-width: 480px) {
            .contact-hero {
                padding: 80px 0 40px;
            }
            
            .contact-hero .hero-title {
                font-size: 2rem;
            }
            
            .section-header h2 {
                font-size: 2rem;
            }
            
            .method-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .contact-link {
                flex-direction: column;
                gap: 5px;
                text-align: center;
            }
            
            .social-link {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .social-info {
                text-align: center;
            }
        }
    </style>

    <body>
            <!-- شريط التنقل العلوي -->
            <div class="top-bar">
                <div class="container">
                    <div class="top-bar-content">
                        <div class="welcome-text">
                                                <?php echo getTranslatedText('welcome_text'); ?>


                            
                        </div>
                        <?php if (isLoggedIn()): ?>
                                <li><span style="color: #fff;">مرحباً، <?php echo $_SESSION['full_name']; ?></span></li>
                                <?php if (isAdmin()): ?>
                                    <li><a href="admin/dashboard.php">لوحة التحكم</a></li>
                                <?php else: ?>
                                    <li><a href="client/dashboard.php">حسابي</a></li>
                                <?php endif; ?>
                                <li><a href="logout.php">تسجيل الخروج</a></li>
                            <?php else: ?>
                                <li><a href="login.php">تسجيل الدخول</a></li>
                                <li><a href="register.php">إنشاء حساب</a></li>
                            <?php endif; ?>
                        <div class="top-bar-actions">
                        <button id="languageToggle" class="lang-btn">
                                <i class="fas fa-globe"></i>
                                <span class="lang-text"><?php echo $lang_toggle_text; ?></span>
                            </button>
                            
                            <div class="contact-top">
                            <a href="tel:<?php echo getSetting('contact_top_phone'); ?>">
                                    <i class="fas fa-phone"></i> 
                                    <?php echo getTranslatedText('contact_top_phone_display'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <!-- الهيدر الرئيسي -->
<header class="main-header">
        <div class="container">
            <div class="header-content">
                  <div class="logo-container">
                    <img src="2.png" alt="شعار مؤسسة الشبانات" class="logo-image">
                    <div class="logo-text">
                      <span class="logo-main">
                            <?php echo getTranslatedText('header_logo_text'); ?>
                        </span>
                        <span class="logo-sub">
                            <?php echo getSetting('header_logo_subtext'); ?>
                        </span></div>
                </div>
                
                <nav class="main-nav">
                    <ul class="nav-list">
                        <li><a href="index.php" class="nav-link">
                            <i class="fas fa-home"></i> 
                            <span data-ar="الرئيسية" data-en="Home">الرئيسية</span>
                        </a></li>
                        <li><a href="about.php" class="nav-link">
                            <i class="fas fa-building"></i> 
                            <span data-ar="من نحن" data-en="About">عنا</span>
                        </a></li>
                        <li><a href="products.php" class="nav-link">
                            <i class="fas fa-box-open"></i> 
                            <span data-ar="المنتجات" data-en="Products">المنتجات</span>
                        </a></li>
                        <li><a href="#stats" class="nav-link">
                            <i class="fas fa-chart-bar"></i> 
                            <span data-ar="الإحصائيات" data-en="Statistics">الإحصائيات</span>
                        </a></li>
                        <li><a href="#brands" class="nav-link">
                            <i class="fas fa-tags"></i> 
                            <span data-ar="العلامات" data-en="Brands">العلامات</span>
                        </a></li>
                        <li><a href="contact.php" class="nav-link active">
                            <i class="fas fa-envelope"></i> 
                            <span data-ar="اتصل بنا" data-en="Contact">اتصل بنا</span>
                        </a></li>
                    </ul>
                </nav>

                <div class="header-actions">
                    <button class="search-btn" aria-label="بحث">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="menu-toggle" aria-label="قائمة التنقل">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
    </header>

<!-- قسم الهيرو للاتصال -->
<section class="contact-hero">
    <div class="container">
        <div class="hero-contednt">
            <h1 class="hero-title"  style="text-align: center;"><?php echo $current_lang == 'ar' ? 'اتصل بنا' : 'Contact Us'; ?></h1>
            <p class="hero-subtitle"  style="text-align: center;"><?php echo $current_lang == 'ar' ? 'نحن هنا للإجابة على استفساراتك وتقديم أفضل الخدمات' : 'We\'re here to answer your inquiries and provide the best services'; ?></p>
            
            <div class="contact-breadcrumb">
                <a href="index.php" m style="text-align: center;"><?php echo $current_lang == 'ar' ? 'الرئيسية' : 'Home'; ?></a>
                <i class="fas fa-chevron-left"></i>
                <span  style="text-align: center;"><?php echo $current_lang == 'ar' ? 'اتصل بنا' : 'Contact Us'; ?></span>
            </div>
        </div>
    </div>
</section>

<main class="contact-main">
    <div class="container">
        <!-- معلومات الاتصال -->
        <div class="contact-info-section">
            <div class="section-header">
                <h2><?php echo $current_lang == 'ar' ? 'معلومات الاتصال' : 'Contact Information'; ?></h2>
                <p><?php echo $current_lang == 'ar' ? 'تواصل معنا عبر القنوات المتاحة التالية' : 'Contact us through the following available channels'; ?></p>
            </div>

            <div class="contact-methods-grid">
                <!-- هاتف -->
                <div class="contact-method-card">
                    <div class="method-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="method-content">
                        <h3><?php echo $current_lang == 'ar' ? 'هاتف' : 'Phone'; ?></h3>
                        <div class="contact-numbers">
                            <a href="tel:<?php echo htmlspecialchars($contact_info['phone'] ?? '+966555382875'); ?>" class="contact-link">
                                <i class="fas fa-mobile-alt"></i>
                                <?php echo htmlspecialchars($contact_info['phone'] ?? '+966555382875'); ?>
                            </a>
                        </div>
                        <p class="method-description"><?php echo $current_lang == 'ar' ? 'متاحون للرد على مكالماتكم طوال أيام الأسبوع' : 'Available to answer your calls throughout the week'; ?></p>
                    </div>
                </div>

                <!-- البريد الإلكتروني -->
                <div class="contact-method-card">
                    <div class="method-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="method-content">
                        <h3><?php echo $current_lang == 'ar' ? 'البريد الإلكتروني' : 'Email'; ?></h3>
                        <div class="contact-emails">
                            <a href="mailto:<?php echo htmlspecialchars($contact_info['email'] ?? 'amnt.est.sa@gmail.com'); ?>" class="contact-link">
                                <i class="fas fa-inbox"></i>
                                <?php echo htmlspecialchars($contact_info['email'] ?? 'amnt.est.sa@gmail.com'); ?>
                            </a>
                        </div>
                        <p class="method-description"><?php echo $current_lang == 'ar' ? 'سنرد على رسائلكم خلال 24 ساعة' : 'We will respond to your messages within 24 hours'; ?></p>
                    </div>
                </div>

                <!-- العنوان -->
                <div class="contact-method-card">
                    <div class="method-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="method-content">
                        <h3><?php echo $current_lang == 'ar' ? 'العنوان' : 'Address'; ?></h3>
                        <div class="contact-address">
                            <i class="fas fa-building"></i>
                            <div>
                                <strong><?php echo $current_lang == 'ar' ? 'المقر الرئيسي' : 'Headquarters'; ?></strong>
                                <?php if ($contact_info): ?>
                                    <p><?php echo $current_lang == 'ar' ? 
                                        nl2br(htmlspecialchars($contact_info['address'])) : 
                                        nl2br(htmlspecialchars($contact_info['address_en'])); ?></p>
                                <?php else: ?>
                                    <p><?php echo $current_lang == 'ar' ? 
                                        'المملكة العربية السعودية - الرياض - حي العليا' : 
                                        'Saudi Arabia - Riyadh - Al Olaya District'; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button class="map-btn" onclick="openMap()">
                            <i class="fas fa-map"></i>
                            <span><?php echo $current_lang == 'ar' ? 'فتح الخريطة' : 'Open Map'; ?></span>
                        </button>
                    </div>
                </div>

                <!-- أوقات العمل -->
                <div class="contact-method-card">
                    <div class="method-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="method-content">
                        <h3><?php echo $current_lang == 'ar' ? 'أوقات العمل' : 'Working Hours'; ?></h3>
                        <div class="working-hours">
                            <?php if ($contact_info): ?>
                                <div class="time-slot">
                                    <span><?php echo $current_lang == 'ar' ? 
                                        nl2br(htmlspecialchars($contact_info['working_hours_ar'])) : 
                                        nl2br(htmlspecialchars($contact_info['working_hours_en'])); ?></span>
                                </div>
                            <?php else: ?>
                                <div class="time-slot">
                                    <span><?php echo $current_lang == 'ar' ? 'السبت - الخميس' : 'Saturday - Thursday'; ?></span>
                                    <span>8:00 ص - 6:00 م</span>
                                </div>
                                <div class="time-slot">
                                    <span><?php echo $current_lang == 'ar' ? 'الجمعة' : 'Friday'; ?></span>
                                    <span><?php echo $current_lang == 'ar' ? 'إجازة' : 'Day Off'; ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="method-description"><?php echo $current_lang == 'ar' ? 'خدمة العملاء متاحة خلال أوقات العمل' : 'Customer service is available during working hours'; ?></p>
                    </div>
                </div>
            </div>
        </div>

        
            <!-- الخريطة -->
            
            <!-- نموذج الاتصال والخرائط -->
            <div class="contact-form-map-section">
                <!-- نموذج الاتصال -->
                <div class="contact-form-container">
                    <div class="form-header">
                        <h2 data-ar="أرسل رسالة" data-en="Send Message">أرسل رسالة</h2>
                        <p data-ar="املأ النموذج وسنقوم بالرد عليك في أقرب وقت" data-en="Fill out the form and we will get back to you as soon as possible">املأ النموذج وسنقوم بالرد عليك في أقرب وقت</p>
                    </div>

                    <form class="contact-form" id="contactForm">
                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" id="name" name="name" required>
                                <label for="name" data-ar="الاسم الكريم *" data-en="Your Name *">الاسم الكريم *</label>
                                <div class="form-error" id="nameError"></div>
                            </div>
                            
                            <div class="form-group">
                                <input type="email" id="email" name="email" required>
                                <label for="email" data-ar="البريد الإلكتروني *" data-en="Email Address *">البريد الإلكتروني *</label>
                                <div class="form-error" id="emailError"></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <input type="tel" id="phone" name="phone">
                                <label for="phone" data-ar="رقم الهاتف" data-en="Phone Number">رقم الهاتف</label>
                            </div>
                            
                            <div class="form-group">
                                <select id="subject" name="subject" required>
                                    <option value="" data-ar="اختر الموضوع" data-en="Select Subject">اختر الموضوع</option>
                                    <option value="inquiry" data-ar="استفسار عام" data-en="General Inquiry">استفسار عام</option>
                                    <option value="products" data-ar="استفسار عن المنتجات" data-en="Products Inquiry">استفسار عن المنتجات</option>
                                    <option value="order" data-ar="متابعة طلب" data-en="Order Follow-up">متابعة طلب</option>
                                    <option value="complaint" data-ar="شكوى" data-en="Complaint">شكوى</option>
                                    <option value="partnership" data-ar="شراكة" data-en="Partnership">شراكة</option>
                                    <option value="other" data-ar="أخرى" data-en="Other">أخرى</option>
                                </select>
                                <div class="form-error" id="subjectError"></div>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <textarea id="message" name="message" rows="6" required placeholder=" "></textarea>
                            <label for="message" data-ar="رسالتك *" data-en="Your Message *">رسالتك *</label>
                            <div class="form-error" id="messageError"></div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="submit-btn" id="submitBtn">
                                <span class="btn-text" data-ar="إرسال الرسالة" data-en="Send Message">إرسال الرسالة</span>
                                <div class="btn-loading">
                                    <div class="spinner"></div>
                                    <span data-ar="جاري الإرسال..." data-en="Sending...">جاري الإرسال...</span>
                                </div>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- الخريطة -->
                <div class="map-container">
                    <div class="map-header">
                        <h2 data-ar="موقعنا على الخريطة" data-en="Our Location on Map">موقعنا على الخريطة</h2>
                        <p data-ar="يمكنكم زيارتنا في مقرنا الرئيسي بالرياض" data-en="You can visit us at our headquarters in Riyadh">يمكنكم زيارتنا في مقرنا الرئيسي بالرياض</p>
                    </div>
                    
                    <div class="map-wrapper">
                        <!-- خريطة جوجل مبدئية -->
                        <div class="static-map">
                            <div class="map-placeholder">
                                <i class="fas fa-map-marked-alt"></i>
                                <h3 data-ar="خريطة الموقع" data-en="Location Map">خريطة الموقع</h3>
                                <p data-ar="المملكة العربية السعودية - الرياض" data-en="Saudi Arabia - Riyadh">المملكة العربية السعودية - الرياض</p>
                                <button class="open-map-btn" onclick="openGoogleMaps()">
                                    <i class="fas fa-external-link-alt"></i>
                                    <span data-ar="فتح في خرائط جوجل" data-en="Open in Google Maps">فتح في خرائط جوجل</span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="location-details">
                            <div class="location-item">
                                <i class="fas fa-car"></i>
                                <div>
                                    <strong data-ar="مواقف سيارات" data-en="Parking">مواقف سيارات</strong>
                                    <span data-ar="متاحة للعملاء" data-en="Available for customers">متاحة للعملاء</span>
                                </div>
                            </div>
                            <div class="location-item">
                                <i class="fas fa-wheelchair"></i>
                                <div>
                                    <strong data-ar="مدخل مناسب" data-en="Accessible Entrance">مدخل مناسب</strong>
                                    <span data-ar="لذوي الاحتياجات الخاصة" data-en="For people with disabilities">لذوي الاحتياجات الخاصة</span>
                                </div>
                            </div>
                            <div class="location-item">
                                <i class="fas fa-subway"></i>
                                <div>
                                    <strong data-ar="مواصلات عامة" data-en="Public Transport">مواصلات عامة</strong>
                                    <span data-ar="قريب من محطات المترو" data-en="Near metro stations">قريب من محطات المترو</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        

        <!-- وسائل التواصل الاجتماعي -->
        <div class="social-contact-section">
            <div class="section-header">
                <h2><?php echo $current_lang == 'ar' ? 'تواصل معنا على وسائل التواصل' : 'Connect with us on Social Media'; ?></h2>
                <p><?php echo $current_lang == 'ar' ? 'تابعنا للحصول على آخر الأخبار والعروض' : 'Follow us for the latest news and offers'; ?></p>
            </div>

            <div class="social-links-grid">
                <?php if ($contact_info && !empty($contact_info['social_whatsapp'])): ?>
                <a href="<?php echo htmlspecialchars($contact_info['social_whatsapp']); ?>" class="social-link whatsapp" target="_blank">
                    <div class="social-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div class="social-info">
                        <h3><?php echo $current_lang == 'ar' ? 'واتساب' : 'WhatsApp'; ?></h3>
                        <p><?php echo $current_lang == 'ar' ? 'راسلنا مباشرة' : 'Message us directly'; ?></p>
                        <span class="social-handle"><?php echo htmlspecialchars($contact_info['phone'] ?? '+966555382875'); ?></span>
                    </div>
                </a>
                <?php endif; ?>

                <?php if ($contact_info && !empty($contact_info['social_twitter'])): ?>
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
                <?php endif; ?>

                <?php if ($contact_info && !empty($contact_info['social_instagram'])): ?>
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
                <?php endif; ?>

                <?php if ($contact_info && !empty($contact_info['social_facebook'])): ?>
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
                <?php endif; ?>
            </div>
        </div>

        <!-- الأسئلة الشائعة -->
        <div class="faq-section">
            <div class="section-header">
                <h2><?php echo $current_lang == 'ar' ? 'أسئلة متكررة' : 'Frequently Asked Questions'; ?></h2>
                <p><?php echo $current_lang == 'ar' ? 'إجابات على الأسئلة الأكثر شيوعاً' : 'Answers to the most common questions'; ?></p>
            </div>

            <div class="faq-grid">
                <?php if (count($faqs) > 0): ?>
                    <?php foreach ($faqs as $faq): ?>
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3><?php echo $current_lang == 'ar' ? 
                                htmlspecialchars($faq['question_ar']) : 
                                htmlspecialchars($faq['question_en']); ?></h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p><?php echo $current_lang == 'ar' ? 
                                htmlspecialchars($faq['answer_ar']) : 
                                htmlspecialchars($faq['answer_en']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- الأسئلة الافتراضية -->
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3><?php echo $current_lang == 'ar' ? 'ما هي أوقات استلام الطلبات؟' : 'What are the order receiving times?'; ?></h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p><?php echo $current_lang == 'ar' ? 'نستقبل الطلبات خلال أيام العمل من السبت إلى الخميس من الساعة 8:00 صباحاً حتى 6:00 مساءً.' : 'We receive orders during working days from Saturday to Thursday from 8:00 AM to 6:00 PM.'; ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
      
    </div>
</main>
   
     <script>
        // JavaScript للترجمة والوظائف التفاعلية
        document.addEventListener('DOMContentLoaded', function() {
            // تهيئة وظائف الترجمة
            initLanguageToggle();
            
            // تهيئة نموذج الاتصال
            initContactForm();
            
            // تهيئة الأسئلة الشائعة
            initFAQ();
            
            // تهيئة زر العودة للأعلى
            initScrollToTop();
            
            // تهيئة القائمة المتنقلة
            initMobileMenu();
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
            
            // تحديث نص زر الإرسال
            const submitBtn = document.querySelector('.submit-btn .btn-text');
            if (submitBtn) {
                const text = lang === 'ar' ? submitBtn.getAttribute('data-ar') : submitBtn.getAttribute('data-en');
                if (text) {
                    submitBtn.textContent = text;
                }
            }
            
            // تحديث نص حالة التحميل
            const loadingText = document.querySelector('.btn-loading span');
            if (loadingText) {
                const text = lang === 'ar' ? loadingText.getAttribute('data-ar') : loadingText.getAttribute('data-en');
                if (text) {
                    loadingText.textContent = text;
                }
            }
        }

        // تهيئة نموذج الاتصال
        function initContactForm() {
            const contactForm = document.getElementById('contactForm');
            const submitBtn = document.getElementById('submitBtn');
            
            if (contactForm) {
                // إضافة أحداث للحقول
                const formInputs = contactForm.querySelectorAll('input, textarea, select');
                formInputs.forEach(input => {
                    // التحقق من الصحة أثناء الكتابة
                    input.addEventListener('input', function() {
                        validateField(this);
                    });
                    
                    // التحقق من الصحة عند فقدان التركيز
                    input.addEventListener('blur', function() {
                        validateField(this);
                    });
                    
                    // إضافة تأثير التركيز للحقول
                    input.addEventListener('focus', function() {
                        this.parentElement.classList.add('focused');
                    });
                    
                    input.addEventListener('blur', function() {
                        if (this.value === '') {
                            this.parentElement.classList.remove('focused');
                        }
                    });
                    
                    // التحقق من الحقول المملوءة مسبقاً
                    if (input.value !== '') {
                        input.parentElement.classList.add('focused');
                    }
                });
                
                // إرسال النموذج
                contactForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    if (validateForm()) {
                        submitForm();
                    }
                });
            }
        }

        // التحقق من حقل معين
        function validateField(field) {
            const errorElement = document.getElementById(field.id + 'Error');
            let isValid = true;
            let errorMessage = '';
            
            // إزالة أي رسالة خطأ سابقة
            if (errorElement) {
                errorElement.textContent = '';
            }
            
            // التحقق بناءً على نوع الحقل
            switch(field.type) {
                case 'text':
                    if (field.value.trim() === '' && field.required) {
                        isValid = false;
                        errorMessage = getErrorMessage('required', field.id);
                    }
                    break;
                    
                case 'email':
                    if (field.value.trim() === '' && field.required) {
                        isValid = false;
                        errorMessage = getErrorMessage('required', field.id);
                    } else if (field.value.trim() !== '' && !isValidEmail(field.value)) {
                        isValid = false;
                        errorMessage = getErrorMessage('email', field.id);
                    }
                    break;
                    
                case 'select-one':
                    if (field.value === '' && field.required) {
                        isValid = false;
                        errorMessage = getErrorMessage('required', field.id);
                    }
                    break;
                    
                case 'textarea':
                    if (field.value.trim() === '' && field.required) {
                        isValid = false;
                        errorMessage = getErrorMessage('required', field.id);
                    }
                    break;
            }
            
            // عرض رسالة الخطأ أو إزالتها
            if (errorElement) {
                if (!isValid) {
                    errorElement.textContent = errorMessage;
                    field.classList.add('error');
                } else {
                    errorElement.textContent = '';
                    field.classList.remove('error');
                }
            }
            
            return isValid;
        }

        // التحقق من صحة النموذج بأكمله
        function validateForm() {
            const form = document.getElementById('contactForm');
            const fields = form.querySelectorAll('input, textarea, select');
            let isValid = true;
            
            fields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });
            
            return isValid;
        }

        // الحصول على رسالة الخطأ المناسبة
        function getErrorMessage(type, fieldId) {
            const currentLang = document.documentElement.lang;
            
            const errorMessages = {
                ar: {
                    required: {
                        name: 'الرجاء إدخال الاسم',
                        email: 'الرجاء إدخال البريد الإلكتروني',
                        subject: 'الرجاء اختيار الموضوع',
                        message: 'الرجاء إدخال الرسالة'
                    },
                    email: 'البريد الإلكتروني غير صحيح'
                },
                en: {
                    required: {
                        name: 'Please enter your name',
                        email: 'Please enter your email',
                        subject: 'Please select a subject',
                        message: 'Please enter your message'
                    },
                    email: 'Email address is invalid'
                }
            };
            
            if (type === 'required') {
                return errorMessages[currentLang].required[fieldId] || errorMessages[currentLang].required.general;
            } else {
                return errorMessages[currentLang][type];
            }
        }

        // التحقق من صحة البريد الإلكتروني
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // إرسال النموذج
        function submitForm() {
            const form = document.getElementById('contactForm');
            const submitBtn = document.getElementById('submitBtn');
            const currentLang = document.documentElement.lang;
            
            // عرض حالة التحميل
            submitBtn.classList.add('loading');
            
            // محاكاة إرسال النموذج (في الواقع الفعلي، سيتم إرساله إلى الخادم)
            setTimeout(() => {
                // إخفاء حالة التحميل
                submitBtn.classList.remove('loading');
                
                // عرض رسالة النجاح
                showNotification(
                    currentLang === 'ar' ? 'تم إرسال رسالتك بنجاح!' : 'Your message has been sent successfully!',
                    'success'
                );
                
                // إعادة تعيين النموذج
                form.reset();
                
                // إزالة حالة التركيز من الحقول
                const formGroups = form.querySelectorAll('.form-group');
                formGroups.forEach(group => {
                    group.classList.remove('focused');
                });
                
            }, 2000);
        }

        // عرض الإشعارات
        function showNotification(message, type) {
            // إنشاء عنصر الإشعار
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            
            // إضافة الإشعار إلى الصفحة
            document.body.appendChild(notification);
            
            // إظهار الإشعار
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // إخفاء الإشعار بعد 5 ثوانٍ
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 5000);
        }

        // تهيئة الأسئلة الشائعة
        function initFAQ() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                
                question.addEventListener('click', function() {
                    // إغلاق جميع الأسئلة الأخرى
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('active');
                        }
                    });
                    
                    // تبديل السؤال الحالي
                    item.classList.toggle('active');
                });
            });
        }

        // تهيئة زر العودة للأعلى
        function initScrollToTop() {
            const scrollButton = document.getElementById('scrollToTop');
            
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

        // فتح خرائط جوجل
        function openGoogleMaps() {
            const address = "المملكة العربية السعودية - الرياض - حي العليا";
            const encodedAddress = encodeURIComponent(address);
            window.open(`https://www.google.com/maps/search/?api=1&query=${encodedAddress}`, '_blank');
        }

        // فتح الخريطة (وظيفة بديلة)
        function openMap() {
            openGoogleMaps();
        }
    </script>
   <?php include 'footer.php'; ?>
