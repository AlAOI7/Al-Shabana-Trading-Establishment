// تهيئة صفحة الاتصال
document.addEventListener('DOMContentLoaded', function() {
    initializeContactPage();
});

function initializeContactPage() {
    initializeContactForm();
    initializeFAQ();
    initializeSocialLinks();
    initializeFormValidation();
}

// تهيئة نموذج الاتصال
function initializeContactForm() {
    const contactForm = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    
    contactForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            await submitForm();
        }
    });
    
    // التحقق أثناء الكتابة
    const inputs = contactForm.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearError(this.id);
        });
        
        input.addEventListener('blur', function() {
            validateField(this.id);
        });
    });
}

// التحقق من صحة النموذج
function validateForm() {
    let isValid = true;
    const fields = ['name', 'email', 'subject', 'message'];
    
    fields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

// التحقق من حقل معين
function validateField(fieldId) {
    const field = document.getElementById(fieldId);
    const errorElement = document.getElementById(fieldId + 'Error');
    let isValid = true;
    
    switch(fieldId) {
        case 'name':
            if (field.value.trim().length < 2) {
                showError(fieldId, getTranslatedText('الاسم يجب أن يكون至少 حرفين', 'Name must be at least 2 characters'));
                isValid = false;
            }
            break;
            
        case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value.trim())) {
                showError(fieldId, getTranslatedText('البريد الإلكتروني غير صحيح', 'Invalid email address'));
                isValid = false;
            }
            break;
            
        case 'subject':
            if (field.value === '') {
                showError(fieldId, getTranslatedText('يرجى اختيار الموضوع', 'Please select a subject'));
                isValid = false;
            }
            break;
            
        case 'message':
            if (field.value.trim().length < 10) {
                showError(fieldId, getTranslatedText('الرسالة يجب أن تكون至少 10 أحرف', 'Message must be at least 10 characters'));
                isValid = false;
            }
            break;
    }
    
    if (isValid) {
        clearError(fieldId);
        field.style.borderColor = 'var(--success-color)';
    }
    
    return isValid;
}

// عرض خطأ
function showError(fieldId, message) {
    const errorElement = document.getElementById(fieldId + 'Error');
    const field = document.getElementById(fieldId);
    
    errorElement.textContent = message;
    errorElement.classList.add('show');
    field.style.borderColor = '#e74c3c';
}

// مسح الخطأ
function clearError(fieldId) {
    const errorElement = document.getElementById(fieldId + 'Error');
    const field = document.getElementById(fieldId);
    
    errorElement.classList.remove('show');
    field.style.borderColor = 'var(--border-color)';
}

// إرسال النموذج
async function submitForm() {
    const submitBtn = document.getElementById('submitBtn');
    const formData = new FormData(document.getElementById('contactForm'));
    const data = Object.fromEntries(formData);
    
    // عرض حالة التحميل
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
    
    try {
        // محاكاة إرسال النموذج (يمكن استبدالها بـ fetch حقيقي)
        await simulateFormSubmission(data);
        
        showNotification(
            getTranslatedText('شكراً لك! تم استلام رسالتك وسنقوم بالرد في أقرب وقت.', 'Thank you! Your message has been received and we will respond as soon as possible.'),
            'success'
        );
        
        // إعادة تعيين النموذج
        document.getElementById('contactForm').reset();
        
    } catch (error) {
        showNotification(
            getTranslatedText('حدث خطأ في الإرسال. يرجى المحاولة مرة أخرى.', 'An error occurred while sending. Please try again.'),
            'error'
        );
    } finally {
        // إخفاء حالة التحميل
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
    }
}

// محاكاة إرسال النموذج
function simulateFormSubmission(data) {
    return new Promise((resolve, reject) => {
        setTimeout(() => {
            // 90% نجاح لمحاكاة الإرسال الحقيقي
            if (Math.random() > 0.1) {
                resolve(data);
            } else {
                reject(new Error('Network error'));
            }
        }, 2000);
    });
}

// تهيئة الأسئلة الشائعة
function initializeFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', function() {
            // إغلاق جميع العناصر الأخرى
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            
            // تبديل العنصر الحالي
            item.classList.toggle('active');
        });
    });
}

// تهيئة روابط وسائل التواصل
function initializeSocialLinks() {
    // يمكن إضافة تتبع للنقرات هنا
    const socialLinks = document.querySelectorAll('.social-link');
    
    socialLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // تسجيل الحدث للتتبع
            console.log('Social link clicked:', this.href);
        });
    });
}

// فتح خرائط جوجل
function openGoogleMaps() {
    const address = "المملكة العربية السعودية - الرياض - حي العليا";
    const encodedAddress = encodeURIComponent(address);
    const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodedAddress}`;
    
    window.open(mapsUrl, '_blank');
}

// فتح الخريطة
function openMap() {
    openGoogleMaps();
}

// الحصول على النص المترجم
function getTranslatedText(arText, enText) {
    const currentLang = document.documentElement.lang || 'ar';
    return currentLang === 'ar' ? arText : enText;
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
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
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
        z-index: 10000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        max-width: 400px;
        display: flex;
        align-items: center;
        gap: 10px;
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

// إضافة أنماط الإشعارات إذا لم تكن موجودة
const notificationStyles = `
.notification-close {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 0;
    margin-right: 10px;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}
`;

// حقن الأنماط إذا لزم الأمر
if (!document.querySelector('#notification-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'notification-styles';
    styleSheet.textContent = notificationStyles;
    document.head.appendChild(styleSheet);
}