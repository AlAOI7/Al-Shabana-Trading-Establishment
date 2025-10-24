-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 24 أكتوبر 2025 الساعة 20:47
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alshabanat`
--

-- --------------------------------------------------------

--
-- بنية الجدول `about_page`
--

CREATE TABLE `about_page` (
  `id` int(11) NOT NULL,
  `section_type` varchar(50) NOT NULL COMMENT 'intro, story, official_details, mission, vision, values',
  `title_ar` varchar(255) DEFAULT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `content_ar` text DEFAULT NULL,
  `content_en` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `about_page`
--

INSERT INTO `about_page` (`id`, `section_type`, `title_ar`, `title_en`, `content_ar`, `content_en`, `image`, `icon`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'intro', 'نحن مؤسسة الشبانات: التزام بالجودة والموثوقية', 'We are Al Shabana: Commitment to Quality and Reliability', 'أكثر من عقد من الخبرة في توفير مستلزمات المنزل الأساسية', 'Over a decade of experience in providing essential household supplies', NULL, NULL, 1, '2025-10-23 15:38:17', '2025-10-23 15:38:17'),
(2, 'story', 'قصتنا', 'Our Story', 'تأسست مؤسسة عبدالرحمن محمد الشبانات التجارية لتكون رائدة في توفير مستلزمات المنزل الأساسية. نحن نجمع بذكاء بين الطاقة المنزلية الدافئة (الفحم وأخشاب الوقود) وبين حلول النقاء والتنظيف الحديثة (أجهزة تنقية المياه ومواد التنظيف). هدفنا هو تقديم منتجات عالية الجودة تلبي احتياجات عملائنا اليومية وتعزز من جودة حياتهم.', 'Al Rahman Mohammed Al Shabana Trading Establishment was founded to be a leader in providing essential household supplies. We intelligently combine warm home energy (coal and fuel wood) with modern purity and cleaning solutions (water purification devices and cleaning materials). Our goal is to provide high-quality products that meet our customers\' daily needs and enhance their quality of life.', NULL, NULL, 2, '2025-10-23 15:38:17', '2025-10-23 15:38:17'),
(3, 'official_details', 'بيانات السجل الرسمي والموقع', 'Official Registration and Location Details', 'مؤسسة عبدالرحمن محمد الشبانات للمقاولات', 'Abdulrahman Mohammed Al Shabana Contracting Establishment', NULL, NULL, 3, '2025-10-23 15:38:17', '2025-10-23 15:38:17'),
(4, 'mission', 'رسالتنا', 'Our Mission', 'توفير تشكيلة متكاملة وموثوقة من المنتجات بأسعار تنافسية تلبي احتياجات العملاء اليومية.', 'Providing a comprehensive and reliable range of products at competitive prices that meet customers\' daily needs.', NULL, NULL, 4, '2025-10-23 15:38:17', '2025-10-23 15:38:17'),
(5, 'vision', 'رؤيتنا', 'Our Vision', 'أن نكون الخيار الأول في قطاع البيع بالتجزئة للمستلزمات المنزلية في المنطقة.', 'To be the first choice in the retail sector for household supplies in the region.', NULL, NULL, 5, '2025-10-23 15:38:17', '2025-10-23 15:38:17'),
(6, 'values', 'قيمنا', 'Our Values', 'الجودة، الموثوقية، الابتكار، ورضا العملاء هي أساس كل ما نقوم به.', 'Quality, Reliability, Innovation, and Customer Satisfaction are the foundation of everything we do.', NULL, NULL, 6, '2025-10-23 15:38:17', '2025-10-23 15:38:17');

-- --------------------------------------------------------

--
-- بنية الجدول `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'تسجيل دخول الأدمن', '192.168.1.100', NULL, '2025-10-22 12:51:16'),
(2, 2, 'login', 'تسجيل دخول العميل', '192.168.1.101', NULL, '2025-10-22 12:51:16'),
(3, 1, 'product_add', 'إضافة منتج جديد', '192.168.1.100', NULL, '2025-10-22 12:51:16'),
(4, 2, 'order_create', 'إنشاء طلب جديد', '192.168.1.101', NULL, '2025-10-22 12:51:16');

-- --------------------------------------------------------

--
-- بنية الجدول `contact_info`
--

CREATE TABLE `contact_info` (
  `id` int(11) NOT NULL,
  `address` text DEFAULT NULL,
  `address_en` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `working_hours_ar` varchar(255) DEFAULT NULL,
  `working_hours_en` varchar(255) DEFAULT NULL,
  `social_facebook` varchar(255) DEFAULT NULL,
  `social_twitter` varchar(255) DEFAULT NULL,
  `social_instagram` varchar(255) DEFAULT NULL,
  `social_whatsapp` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `contact_info`
--

INSERT INTO `contact_info` (`id`, `address`, `address_en`, `phone`, `email`, `working_hours_ar`, `working_hours_en`, `social_facebook`, `social_twitter`, `social_instagram`, `social_whatsapp`, `created_at`, `updated_at`) VALUES
(1, 'المملكة العربية السعودية - الرياض - حي العليا\nشارع الملك فهد - مقابل مركز المملكة', 'Saudi Arabia - Riyadh - Al Olaya District\nKing Fahd Road - Opposite Kingdom Center', '+966555382875', 'amnt.est.sa@gmail.com', 'السبت - الخميس: 8:00 ص - 6:00 م\nالجمعة: إجازة', 'Saturday - Thursday: 8:00 AM - 6:00 PM\nFriday: Day Off', 'https://facebook.com/alshabana', 'https://twitter.com/alshabana', 'https://instagram.com/alshabana', 'https://wa.me/966555382875', '2025-10-23 16:40:38', '2025-10-23 16:40:38');

-- --------------------------------------------------------

--
-- بنية الجدول `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `question_ar` varchar(255) NOT NULL,
  `question_en` varchar(255) NOT NULL,
  `answer_ar` text NOT NULL,
  `answer_en` text NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `faqs`
--

INSERT INTO `faqs` (`id`, `question_ar`, `question_en`, `answer_ar`, `answer_en`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'ما هي أوقات استلام الطلبات؟', 'What are the order receiving times?', 'نستقبل الطلبات خلال أيام العمل من السبت إلى الخميس من الساعة 8:00 صباحاً حتى 6:00 مساءً.', 'We receive orders during working days from Saturday to Thursday from 8:00 AM to 6:00 PM.', 1, 1, '2025-10-23 15:55:03', '2025-10-23 15:55:03'),
(2, 'هل تقدمون خدمة التوصيل؟', 'Do you offer delivery service?', 'نعم، نقدم خدمة التوصيل لجميع أنحاء الرياض مع إمكانية التوصيل لمدن أخرى حسب الطلب.', 'Yes, we offer delivery service throughout Riyadh with the possibility of delivery to other cities upon request.', 2, 1, '2025-10-23 15:55:03', '2025-10-23 15:55:03'),
(3, 'كيف يمكنني متابعة طلبي؟', 'How can I track my order?', 'يمكنك متابعة طلبك عبر الاتصال بنا على الأرقام المخصصة للطلبات أو عبر البريد الإلكتروني.', 'You can track your order by calling us on the dedicated order numbers or via email.', 3, 1, '2025-10-23 15:55:03', '2025-10-23 15:55:03'),
(4, 'ما هي طرق الدفع المتاحة؟', 'What payment methods are available?', 'نقبل الدفع نقداً، التحويل البنكي، وبطاقات الائتمان حسب الاتفاق.', 'We accept cash, bank transfer, and credit cards as agreed.', 4, 1, '2025-10-23 15:55:03', '2025-10-23 15:55:03');

-- --------------------------------------------------------

--
-- بنية الجدول `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','shipped','delivered') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(4, 1, 1, 1, 3000.00),
(5, 1, 2, 1, 500.00),
(6, 2, 3, 1, 800.00),
(7, 3, 4, 1, 200.00),
(8, 3, 5, 1, 100.00);

-- --------------------------------------------------------

--
-- بنية الجدول `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `S_NO` int(11) DEFAULT NULL,
  `Item_Code` varchar(100) DEFAULT NULL,
  `Item_Name` text DEFAULT NULL,
  `Packing` varchar(100) DEFAULT NULL,
  `Item_Group` varchar(100) DEFAULT NULL,
  `Brand` varchar(100) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `products`
--

INSERT INTO `products` (`id`, `S_NO`, `Item_Code`, `Item_Name`, `Packing`, `Item_Group`, `Brand`, `featured`, `created_at`, `updated_at`) VALUES
(1, 324, '2342', 'سشبي', 'fd', 'sadf', 'sdf', 1, '2025-10-24 16:52:37', '2025-10-24 16:52:37'),
(2, 433, 're44', 'r34r', 'r', 'fds', 'sdfs', 0, '2025-10-24 17:57:19', '2025-10-24 17:57:19'),
(3, 343, 'er', 'ree', 'fdd', 'sdfs', 'sdfs', 0, '2025-10-24 17:57:55', '2025-10-24 17:57:55'),
(4, 2, 'PROD001', 'شوكولاتة حليب', '100 جرام', 'حلويات', 'شوكولاتا بارك', 1, '2025-10-24 18:41:21', '2025-10-24 18:41:21'),
(5, 3, 'PROD002', 'بسكويت شوكولاتة', '150 جرام', 'مخبوزات', 'بسكويتا', 0, '2025-10-24 18:41:21', '2025-10-24 18:41:21'),
(6, 4, 'PROD003', 'عصير برتقال', '1 لتر', 'مشروبات', 'عصائر طبيعية', 1, '2025-10-24 18:41:22', '2025-10-24 18:41:22'),
(7, 5, 'PROD004', 'معجون أسنان', '75 مل', 'العناية الشخصية', 'سنان', 0, '2025-10-24 18:41:22', '2025-10-24 18:41:22'),
(8, 6, 'PROD005', 'أرز بسمتي', '5 كجم', 'أطعمة', 'أرز الذهب', 1, '2025-10-24 18:41:22', '2025-10-24 18:41:22');

-- --------------------------------------------------------

--
-- بنية الجدول `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `image_name` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_name`, `is_primary`) VALUES
(1, 1, '68fbbd6193954_.jfif', 1),
(2, 2, '68fbbdffbaacc_2.jfif', 1),
(3, 3, '68fbbe2386a5a_1.jpg', 1);

-- --------------------------------------------------------

--
-- بنية الجدول `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `services`
--

INSERT INTO `services` (`id`, `title`, `title_en`, `description`, `description_en`, `icon`, `created_at`) VALUES
(1, 'توصيل سريع', 'Coal and Energy Supply', 'خدمة التوصيل السريع خلال 24 ساعة', 'We provide the finest types of coal and fuel wood to ensure warm and sustainable home heating throughout the year', 'fas fa-shipping-fast', '2025-10-22 12:30:34'),
(2, 'دعم فني', 'Water Purification Systems', 'فريق دعم فني متاح على مدار الساعة', 'Advanced water purification devices to ensure pure and healthy water for you and your family', 'fas fa-headset', '2025-10-22 12:30:34'),
(3, 'ضمان الجودة', 'Comprehensive Cleaning Materials', 'ضمان جودة المنتجات وخدمة ما بعد البيع', 'A comprehensive range of high-quality cleaning materials to maintain the cleanliness of your home', 'fas fa-award', '2025-10-22 12:30:34'),
(4, 'دفع آمن', 'Fast Home Delivery', 'أنظمة دفع آمنة ومشفرة', 'Fast and reliable delivery service for all products directly to your home at the specified time', 'fas fa-shield-alt', '2025-10-22 12:30:34'),
(5, 'توفير الفحم والطاقة', 'Home Consultations', 'نوفر أجود أنواع الفحم وأخشاب الوقود لضمان تدفئة منزلية دافئة ومستدامة طوال العام', 'Specialized consulting team to help you choose the best products and services for your home', 'fas fa-fire', '2025-10-23 15:15:46'),
(6, 'أنظمة تنقية المياه', 'Quality Guarantee', 'أجهزة تنقية المياه المتطورة لضمان مياه نقية وصحية لك ولعائلتك', 'We guarantee the quality of all our products and services with full commitment to safety and security standards', 'fas fa-tint', '2025-10-23 15:15:46'),
(7, 'مواد التنظيف المتكاملة', NULL, 'تشكيلة شاملة من مواد التنظيف عالية الجودة للمحافظة على نظافة منزلك', NULL, 'fas fa-spray-can', '2025-10-23 15:15:46'),
(8, 'توصيل سريع للمنازل', NULL, 'خدمة توصيل سريعة وموثوقة لجميع المنتجات مباشرة إلى منزلك في الوقت المحدد', NULL, 'fas fa-truck', '2025-10-23 15:15:46'),
(9, 'استشارات منزلية', NULL, 'فريق استشاري متخصص لمساعدتك في اختيار أفضل المنتجات والخدمات لمنزلك', NULL, 'fas fa-user-tie', '2025-10-23 15:15:46'),
(10, 'ضمان الجودة', NULL, 'نضمن لك جودة جميع منتجاتنا وخدماتنا مع التزام تام بمعايير السلامة والأمان', NULL, 'fas fa-award', '2025-10-23 15:15:46');

-- --------------------------------------------------------

--
-- بنية الجدول `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`) VALUES
(1, 'company_name_ar', 'مؤسسة عبدالرحمن محمد الشبانات التجارية', 'header'),
(2, 'company_name_en', 'Al Rahman Mohammed Al Shabana Trading Est.', 'header'),
(3, 'tagline_ar', 'شريكك الموثوق لتأمين مستلزمات المنزل الأساسية', 'header'),
(4, 'tagline_en', 'Your trusted partner for securing essential household supplies', 'header'),
(5, 'description_ar', 'نجمع بين تقاليد الطاقة المنزلية الدافئة وتقنيات النقاء والتنظيف الحديثة لتقديم أفضل الحلول لعملائنا الكرام', 'header'),
(6, 'description_en', 'We combine traditional home energy warmth with modern purity and cleaning technologies to provide the best solutions for our valued customers', 'header'),
(7, 'about_title_ar', 'من نحن', 'about'),
(8, 'about_title_en', 'About Us', 'about'),
(9, 'about_description_ar', 'تأسست مؤسسة عبدالرحمن محمد الشبانات التجارية لتكون رائدة في توفير مستلزمات المنزل الأساسية. نحن نجمع بين الطاقة المنزلية الدافئة (الفحم وأخشاب الوقود) وبين حلول النقاء والتنظيف الحديثة (أجهزة تنقية المياه ومواد التنظيف).', 'about'),
(10, 'about_description_en', 'Al Rahman Mohammed Al Shabana Trading Establishment was founded to be a leader in providing essential household supplies. We combine warm home energy (coal and fuel wood) with modern purity and cleaning solutions (water purification devices and cleaning materials).', 'about'),
(11, 'mission_title_ar', 'رسالتنا', 'about'),
(12, 'mission_title_en', 'Our Mission', 'about'),
(13, 'mission_description_ar', 'توفير تشكيلة متكاملة وموثوقة من المنتجات بأسعار تنافسية وخدمة متميزة.', 'about'),
(14, 'mission_description_en', 'Providing a comprehensive and reliable range of products at competitive prices with exceptional service.', 'about'),
(15, 'vision_title_ar', 'رؤيتنا', 'about'),
(16, 'vision_title_en', 'Our Vision', 'about'),
(17, 'vision_description_ar', 'أن نكون الخيار الأول في قطاع البيع بالتجزئة للمستلزمات المنزلية في المنطقة.', 'about'),
(18, 'vision_description_en', 'To be the first choice in the retail sector for household supplies in the region.', 'about'),
(19, 'values_title_ar', 'قيمنا', 'about'),
(20, 'values_title_en', 'Our Values', 'about'),
(21, 'values_description_ar', 'الجودة، المصداقية، الابتكار، والالتزام برضا العملاء.', 'about'),
(22, 'values_description_en', 'Quality, Credibility, Innovation, and Commitment to Customer Satisfaction.', 'about'),
(23, 'stats_title_ar', 'إحصائياتنا', 'stats'),
(24, 'stats_title_en', 'Our Statistics', 'stats'),
(25, 'products_count', '6532', 'stats'),
(26, 'products_label_ar', 'منتج متوفر', 'stats'),
(27, 'products_label_en', 'Available Products', 'stats'),
(28, 'customers_count', '2506', 'stats'),
(29, 'customers_label_ar', 'عميل راضي', 'stats'),
(30, 'customers_label_en', 'Satisfied Customers', 'stats'),
(31, 'experience_count', '15', 'stats'),
(32, 'experience_label_ar', 'عام من الخبرة', 'stats'),
(33, 'experience_label_en', 'Years of Experience', 'stats'),
(34, 'deliveries_count', '5000', 'stats'),
(35, 'deliveries_label_ar', 'توصيل شهري', 'stats'),
(36, 'deliveries_label_en', 'Monthly Deliveries', 'stats'),
(37, 'products_title_ar', 'منتجاتنا', 'products'),
(38, 'products_title_en', 'Our Products', 'products'),
(39, 'filter_all_ar', 'الكل', 'products'),
(40, 'filter_all_en', 'All', 'products'),
(41, 'filter_cleaning_ar', 'مواد تنظيف', 'products'),
(42, 'filter_cleaning_en', 'Cleaning', 'products'),
(43, 'filter_energy_ar', 'طاقة', 'products'),
(44, 'filter_energy_en', 'Energy', 'products'),
(45, 'brands_title_ar', 'علاماتنا التجارية', 'brands'),
(46, 'brands_title_en', 'Our Brands', 'brands'),
(47, 'brand_double_class', 'Double Class', 'brands'),
(48, 'brand_gator', 'Gator', 'brands'),
(49, 'brand_premium', 'Premium', 'brands'),
(50, 'brand_shield', 'Shield', 'brands'),
(51, 'contact_title_ar', 'اتصل بنا', 'contact'),
(52, 'contact_title_en', 'Contact Us', 'contact'),
(53, 'address_ar', 'المملكة العربية السعودية - الرياض', 'contact'),
(54, 'address_en', 'Saudi Arabia - Riyadh', 'contact'),
(55, 'phone', '+966555382875', 'contact'),
(56, 'email', 'amnt.est.sa@gmail.com', 'contact'),
(57, 'working_hours_ar', 'السبت - الخميس: 9:00 ص - 6:00 م', 'contact'),
(58, 'working_hours_en', 'Saturday - Thursday: 9:00 AM - 6:00 PM', 'contact'),
(59, 'form_name_ar', 'الاسم الكريم', 'contact'),
(60, 'form_name_en', 'Your Name', 'contact'),
(61, 'form_email_ar', 'البريد الإلكتروني', 'contact'),
(62, 'form_email_en', 'Email Address', 'contact'),
(63, 'form_phone_ar', 'رقم الهاتف', 'contact'),
(64, 'form_phone_en', 'Phone Number', 'contact'),
(65, 'form_message_ar', 'رسالتك', 'contact'),
(66, 'form_message_en', 'Your Message', 'contact'),
(67, 'submit_btn_ar', 'إرسال الرسالة', 'contact'),
(68, 'submit_btn_en', 'Send Message', 'contact'),
(69, 'footer_logo_text_ar', 'مؤسسة الشبانات التجارية', 'footer'),
(70, 'footer_logo_text_en', 'Al Shabana Trading Establishment', 'footer'),
(71, 'footer_description_ar', 'شريكك الموثوق في توفير مستلزمات المنزل الأساسية منذ عام 2010', 'footer'),
(72, 'footer_description_en', 'Your trusted partner in providing essential household supplies since 2010', 'footer'),
(73, 'copyright_ar', '© 2024 مؤسسة عبدالرحمن محمد الشبانات التجارية. جميع الحقوق محفوظة.', 'footer'),
(74, 'copyright_en', '© 2024 Al Rahman Mohammed Al Shabana Trading Establishment. All rights reserved.', 'footer'),
(75, 'header_logo_text_ar', 'مؤسسة الشبانات التجارية', 'header'),
(76, 'header_logo_text_en', 'Al Shabana Trading Establishment', 'header'),
(77, 'header_logo_subtext', 'Al Shabana Trading Establishment', 'header'),
(78, 'welcome_text_ar', 'مرحباً بكم في مؤسسة الشبانات التجارية', 'header'),
(79, 'welcome_text_en', 'Welcome to Al Shabana Trading Establishment', 'header'),
(80, 'header_phone', '+966555382875', 'header'),
(81, 'header_phone_display', '+966555382875', 'header'),
(82, 'contact_top_phone', '+966555382875', 'contact'),
(83, 'contact_top_phone_display', '+966555382875', 'contact'),
(84, 'hero_title_main_ar', 'مؤسسة عبدالرحمن محمد الشبانات التجارية', 'hero'),
(85, 'hero_title_sub_ar', 'Al Rahman Mohammed Al Shabana Trading Est.', 'hero'),
(86, 'hero_subtitle_ar', 'شريكك الموثوق لتأمين مستلزمات المنزل الأساسية', 'hero'),
(87, 'hero_description_ar', 'نجمع بين تقاليد الطاقة المنزلية الدافئة وتقنيات النقاء والتنظيف الحديثة لتقديم أفضل الحلول لعملائنا الكرام', 'hero'),
(88, 'hero_button_products_ar', 'تصفح المنتجات', 'hero'),
(89, 'hero_button_about_ar', 'تعرف علينا', 'hero'),
(90, 'hero_button_contact_ar', 'تواصل معنا', 'hero'),
(91, 'hero_category_energy_ar', 'الطاقة', 'hero'),
(92, 'hero_category_purification_ar', 'تنقية', 'hero'),
(93, 'hero_category_cleaning_ar', 'تنظيف', 'hero'),
(94, 'hero_category_supplies_ar', 'مستلزمات', 'hero'),
(95, 'hero_title_main_en', 'Al Rahman Mohammed Al Shabana Trading Est.', 'hero'),
(96, 'hero_title_sub_en', 'Al Rahman Mohammed Al Shabana Trading Est.', 'hero'),
(97, 'hero_subtitle_en', 'Your Trusted Partner for Essential Household Supplies', 'hero'),
(98, 'hero_description_en', 'We combine traditional home energy warmth with modern purification and cleaning technologies to provide the best solutions for our valued customers', 'hero'),
(99, 'hero_button_products_en', 'Browse Products', 'hero'),
(100, 'hero_button_about_en', 'About Us', 'hero'),
(101, 'hero_button_contact_en', 'Contact Us', 'hero'),
(102, 'hero_category_energy_en', 'Energy', 'hero'),
(103, 'hero_category_purification_en', 'Purification', 'hero'),
(104, 'hero_category_cleaning_en', 'Cleaning', 'hero'),
(105, 'hero_category_supplies_en', 'Supplies', 'hero');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('admin','client') DEFAULT 'client',
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `user_type`, `full_name`, `phone`, `created_at`, `last_login`) VALUES
(1, 'alaoi', 'alaoi@company.com', '$2y$10$3Ky49MBwgxqWsN2/1Hv5fO1a5CMh70Hg5mMGwL6QRrPU6IRpZ5mte', 'admin', 'Alaoi Administrator', NULL, '2025-10-22 20:29:55', '2025-10-24 15:45:21'),
(2, 'user1', 'user1@example.com', '$2y$10$qzGCfMTfn./pN3Hnzyw27.bvw8b4RMNch3ih9t.UR7AysrIur9yJa', 'client', 'أحمد محمد', NULL, '2025-10-22 20:29:55', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_page`
--
ALTER TABLE `about_page`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `contact_info`
--
ALTER TABLE `contact_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_order_id` (`order_id`),
  ADD KEY `fk_order_items_product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_page`
--
ALTER TABLE `about_page`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contact_info`
--
ALTER TABLE `contact_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
