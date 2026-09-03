<?php

return [

    'brand' => [
        'name'     => 'فرنِش',
        'strap'    => 'أثاث مصنوع باليد',
        'tagline'  => 'أثاثٌ يُصنع ليبقى بعد الغرفة التي اشتُري لها.',
        'colophon' => 'بخطّي أميري وIBM Plex Sans Arabic',
    ],

    'nav' => [
        'home'       => 'الرئيسية',
        'catalogue'  => 'الكتالوج',
        'lookbook'   => 'دفتر الإلهام',
        'story'      => 'حكايتنا',
        'reviews'    => 'الآراء',
        'contact'    => 'تواصل معنا',
        'menu'       => 'القائمة',
        'primary'    => 'التنقّل الرئيسي',
        'mobile'     => 'قائمة الجوال',
        'language'   => 'اللغة',
    ],

    'account' => [
        'signed_in_as' => 'الحساب',
        'dashboard'    => 'لوحة التحكم',
        'profile'      => 'الملف والطلبات',
        'sign_out'     => 'تسجيل الخروج',
        'sign_in'      => 'تسجيل الدخول',
        'menu_for'     => 'قائمة حساب :name',
    ],

    'util' => [
        'search'      => 'ابحث في الكتالوج',
        'saved'       => 'المحفوظات',
        'saved_items' => 'القطع المحفوظة',
        'bag'         => 'حقيبة التسوّق',
        'bag_count'   => 'حقيبة التسوّق، :count قطعة',
        'bag_empty'   => 'حقيبة التسوّق، فارغة',
    ],

    'search' => [
        'placeholder' => 'أريكة، طاولة بلوط، مصباح أرضي…',
        'label'       => 'البحث عن المنتجات',
        'searching'   => 'جارٍ البحث…',
        'none'        => 'لا شيء يطابق «:query». جرّب كلمة أوسع — «طاولة»، «بلوط»، «مصباح».',
        'error'       => 'البحث غير متاح الآن. حاول بعد لحظات.',
        'browse_all'  => 'تصفّح الكتالوج كاملًا ←',
    ],

    'wishlist' => [
        'empty'          => 'لم تحفظ شيئًا بعد.',
        'browse'         => 'تصفّح الكتالوج ←',
        'loading'        => 'جارٍ التحميل…',
        'move_to_bag'    => 'انقل إلى الحقيبة',
        'move_named'     => 'انقل :name إلى الحقيبة',
        'remove'         => 'إزالة',
        'remove_named'   => 'أزل :name من المحفوظات',
        'save_named'     => 'احفظ :name',
        'update_failed'  => 'تعذّر تحديث محفوظاتك.',
        'move_failed'    => 'تعذّر نقل القطعة إلى حقيبتك.',
    ],

    'newsletter' => [
        'title'       => 'رسائل من الورشة',
        'body'        => 'قطعٌ جديدة، وملاحظات عن الخامات، وما نصنعه تاليًا. تصلك حين يكون هناك ما يستحق الإرسال.',
        'email'       => 'البريد الإلكتروني',
        'placeholder' => 'you@example.com',
        'submit'      => 'اشترك ←',
    ],

    'home' => [
        'title'          => 'أثاث مصنوع باليد',
        'fold_one_label' => 'غرفة المعيشة',
        'fold_one_title' => 'اجلس. وابقَ قليلًا.',
        'fold_one_alt'   => 'أريكة منخفضة بوسائد كتّانية بجانب طاولة بسطح زجاجي.',
        'fold_one_cta'   => 'شاهد الكتالوج ←',
        'lede'           => 'يُصنع مرّة، وكما ينبغي.',
        'body_one'       => 'كل قطعة تُنهى باليد قبل أن تغادر الورشة — تُركّب المفاصل، وتُنعَّم الحواف، وتُزيَّت الأسطح. لا شيء يُستعجل للحاق بموسم.',
        'body_two'       => 'نفضّل أن نصنع أقلّ، وأن نقف خلف كل ما صنعناه.',
        'most_saved'     => 'الأكثر حفظًا',
        'most_saved_qualifier' => ':pieces · مرتّبة بحسب تكرار حفظها',
        'all_in_stock'   => 'كل المتوفّر ←',
        'fold_two_label' => 'من أرشيف العمل',
        'fold_two_title' => 'من أين تأتي القطع.',
        'fold_two_alt'   => 'طاولة الورشة أثناء العمل.',
        'fold_two_cta'   => 'حكايتنا ←',
        'by_room'        => 'حسب الغرفة',
        'by_room_body'   => 'الكتالوج، مرتّبًا كما يُرتَّب البيت.',
        'all_reviews'    => 'كل الآراء ←',
        'review_on'      => 'عن :product',
    ],

    // Arabic counts in six forms: zero, singular, dual, small plural (3–10),
    // large plural (11+), and back to singular agreement at 100+. Laravel's
    // range syntax covers the five that matter for a product count.
    'piece_count' => '{0} لا قطع|{1} قطعة واحدة|{2} قطعتان|[3,10] :count قطع|[11,*] :count قطعة',

];
