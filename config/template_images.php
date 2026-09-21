<?php

return [
    // Prompt per template slug. Aspek default landscape_4_3 (untuk thumbnail).
    // Varian lain (landscape_16_9 untuk hero, square untuk konten) menggunakan prompt spesifik dari Blade.
    'prompts' => [
        'hotel-01' => 'website screenshot of modern hotel homepage with hero banner, navigation bar, room cards, clean blue theme',
        'hotel-02' => 'website screenshot of luxury hotel homepage with elegant serif typography, dark hero image, gold accents',
        'hotel-03' => 'website screenshot of tropical resort homepage with emerald theme, beach villa hero, palm trees',
        'hotel-04' => 'website screenshot of boutique hotel homepage with artistic dark theme, creative layout',
        'hotel-05' => 'website screenshot of business hotel homepage with slate dark navbar, corporate professional design',
        'hotel-06' => 'website screenshot of budget hotel homepage with gradient hero, simple clean cards, friendly design',
        'hotel-07' => 'website screenshot of heritage hotel homepage with amber vintage theme, colonial architecture hero',
        'hotel-08' => 'website screenshot of spa hotel homepage with soft lighting, wellness theme, minimal elegant design',
        'hotel-09' => 'website screenshot of city hotel homepage with urban skyline hero, modern minimalist design',
        'hotel-10' => 'website screenshot of luxury boutique resort homepage with infinity pool hero, contemporary serif design',
        'school-01' => 'website screenshot of elementary school homepage with blue theme, students in uniform hero, navigation bar',
        'school-02' => 'website screenshot of modern school homepage with dark hero, clean cards, tech-focused design',
        'school-03' => 'website screenshot of Islamic school homepage with emerald green theme, mosque hero, Arabic calligraphy accent',
        'school-04' => 'website screenshot of achievement school homepage with amber gold theme, trophy icons, celebration design',
        'school-05' => 'website screenshot of kindergarten homepage with pink playful theme, colorful icons, happy children',
        'school-06' => 'website screenshot of boarding school homepage with indigo dark theme, campus dormitory hero',
        'school-07' => 'website screenshot of vocational school homepage with slate dark navbar, orange accents, workshop hero',
        'school-08' => 'website screenshot of Islamic boarding school homepage with teal green theme, traditional modern design',
        'school-09' => 'website screenshot of tutoring center homepage with purple theme, study icons, bright classroom',
        'school-10' => 'website screenshot of special education school homepage with cyan inclusive theme, welcoming supportive design',
        'sme-01' => 'website screenshot of creative small business homepage with gradient hero, product cards, rounded design',
        'sme-02' => 'website screenshot of modern business homepage with gray dark hero, professional team image, service cards',
        'sme-03' => 'website screenshot of restaurant homepage with red theme, food photography hero, menu cards',
        'sme-04' => 'website screenshot of fashion boutique homepage with elegant serif, dark theme, clothing display hero',
        'sme-05' => 'website screenshot of coffee shop homepage with amber brown theme, barista latte art hero, warm cozy design',
        'sme-06' => 'website screenshot of online store homepage with blue theme, product grid, ecommerce packaging hero',
        'sme-07' => 'website screenshot of service business homepage with slate dark navbar, professional handshake hero, service cards',
        'sme-08' => 'website screenshot of beauty spa homepage with pink rose theme, spa interior hero, elegant serif design',
        'sme-09' => 'website screenshot of wedding organizer homepage with rose elegant theme, floral decoration hero, serif typography',
        'sme-10' => 'website screenshot of handmade craft homepage with amber warm theme, artisan workshop hero, rustic design',
    ],

    // Prompt tambahan per aspek (untuk hero dan konten jika prompt thumbnail tidak sesuai).
    // Key: "{slug}:{aspect}" => prompt
    'extra_prompts' => [
        // Hotel 02 (Elegance) butuh beberapa prompt untuk konten section
        'hotel-02:content1' => 'hotel bedroom with luxurious bedding, soft lighting, elegant decor',
        'hotel-02:content2' => 'hotel restaurant fine dining, elegant table setting',
        // Hotel 03 (Resort)
        'hotel-03:content' => 'resort pool surrounded by tropical greenery',
        // Hotel 04 (Boutique)
        'hotel-04:content1' => 'artistic hotel room decor with unique furniture',
        'hotel-04:content2' => 'hotel lounge cozy artistic interior',
        // Hotel 05 (Business)
        'hotel-05:content' => 'modern business hotel room with workspace desk',
        // Hotel 06 (Budget)
        'hotel-06:content' => 'cozy budget hotel room clean simple',
        // Hotel 07 (Heritage)
        'hotel-07:content' => 'hotel grand staircase heritage interior classic',
        // Hotel 10 (Boutique Resort)
        'hotel-10:content' => 'villa interior contemporary luxury ocean view',
        // School
        'school-01:content' => 'happy students in classroom learning together',
        'school-03:content' => 'students reading quran together in classroom',
        'school-05:content' => 'kindergarten classroom with happy toddlers playing',
        'school-06:content' => 'students in boarding school studying in library',
        'school-07:content' => 'vocational training workshop with modern equipment',
        'school-08:content' => 'students learning islamic studies in traditional classroom',
        'school-09:content' => 'tutor helping student with homework 1on1',
        'school-10:content' => 'special education teacher assisting student with art activity',
        // SME
        'sme-02:content' => 'professional business team meeting in modern office',
        'sme-04:content' => 'fashion designer workshop with fabric and sketches',
        'sme-06:content' => 'warehouse shipping ecommerce orders packing',
        'sme-07:content' => 'professional consultant presenting to client in office',
        'sme-08:content' => 'beauty treatment facial spa relaxing woman',
        'sme-09:content' => 'event planner decorating wedding venue arrangement',
    ],

    // Aspek per varian: 'thumb' => landscape_4_3, 'hero' => landscape_16_9, 'full' => square
    'aspect_per_variant' => [
        'thumb' => 'landscape_4_3',
        'hero' => 'landscape_16_9',
        'full' => 'square',
    ],
];