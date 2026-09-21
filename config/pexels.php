<?php

return [
    // API Key Pexels
    'api_key' => env('PEXELS_API_KEY', ''),

    // Query pencarian per template slug.
    // Setiap template punya query utama (hero) dan query tambahan untuk konten/galeri.
    'queries' => [
        // ===== HOTEL =====
        'hotel-01' => [
            'hero' => 'modern hotel building exterior luxury',
            'content' => ['hotel room interior modern', 'hotel swimming pool resort', 'hotel lobby elegant', 'hotel restaurant dining'],
        ],
        'hotel-02' => [
            'hero' => 'luxury hotel lobby elegant interior',
            'content' => ['luxury hotel bedroom suite', 'fine dining restaurant elegant', 'hotel spa wellness', 'hotel pool infinity'],
        ],
        'hotel-03' => [
            'hero' => 'tropical resort beach villa palm trees',
            'content' => ['resort pool tropical', 'beach villa ocean view', 'tropical resort bungalow', 'resort garden tropical'],
        ],
        'hotel-04' => [
            'hero' => 'boutique hotel artistic interior design',
            'content' => ['boutique hotel room decor', 'hotel lounge cozy interior', 'artistic hotel architecture', 'hotel rooftop bar'],
        ],
        'hotel-05' => [
            'hero' => 'modern business hotel building corporate',
            'content' => ['business hotel room workspace', 'hotel conference room', 'corporate hotel lobby', 'hotel executive suite'],
        ],
        'hotel-06' => [
            'hero' => 'cozy budget hotel room simple',
            'content' => ['budget hotel bedroom', 'hotel reception desk', 'hotel breakfast buffet', 'cozy hotel lounge'],
        ],
        'hotel-07' => [
            'hero' => 'heritage hotel colonial architecture classic',
            'content' => ['heritage hotel grand staircase', 'classic hotel interior vintage', 'colonial building architecture', 'heritage hotel courtyard'],
        ],
        'hotel-08' => [
            'hero' => 'spa hotel wellness relaxation',
            'content' => ['spa treatment massage', 'hotel wellness pool', 'spa interior candles', 'hotel relaxation lounge'],
        ],
        'hotel-09' => [
            'hero' => 'city hotel urban skyline modern',
            'content' => ['city hotel room view', 'modern hotel architecture', 'city skyline night', 'hotel rooftop city view'],
        ],
        'hotel-10' => [
            'hero' => 'luxury boutique resort infinity pool',
            'content' => ['villa interior luxury ocean view', 'resort infinity pool', 'luxury resort suite', 'resort private beach'],
        ],

        // ===== SCHOOL =====
        'school-01' => [
            'hero' => 'elementary school students classroom learning',
            'content' => ['happy students classroom', 'school building campus', 'children reading books', 'school playground children'],
        ],
        'school-02' => [
            'hero' => 'modern school building architecture',
            'content' => ['students studying technology', 'modern classroom computers', 'school library students', 'science laboratory students'],
        ],
        'school-03' => [
            'hero' => 'islamic school mosque students',
            'content' => ['students reading quran', 'mosque architecture islamic', 'islamic school classroom', 'students praying'],
        ],
        'school-04' => [
            'hero' => 'achievement school students success',
            'content' => ['students graduation celebration', 'student trophy award', 'school achievement ceremony', 'students studying hard'],
        ],
        'school-05' => [
            'hero' => 'kindergarten children playing colorful',
            'content' => ['kindergarten classroom toddlers', 'children playing toys', 'happy kids learning', 'kindergarten art activity'],
        ],
        'school-06' => [
            'hero' => 'boarding school campus dormitory',
            'content' => ['students studying library', 'boarding school dormitory', 'students campus life', 'school sports field'],
        ],
        'school-07' => [
            'hero' => 'vocational school workshop training',
            'content' => ['vocational training workshop', 'students engineering workshop', 'technical school students', 'students welding workshop'],
        ],
        'school-08' => [
            'hero' => 'islamic boarding school traditional',
            'content' => ['students islamic studies', 'traditional islamic classroom', 'students learning religion', 'pesantren students'],
        ],
        'school-09' => [
            'hero' => 'tutoring center students studying',
            'content' => ['tutor helping student', 'students homework study', 'bright classroom learning', 'students exam preparation'],
        ],
        'school-10' => [
            'hero' => 'special education teacher students',
            'content' => ['special education teacher', 'students art activity', 'inclusive classroom students', 'teacher assisting student'],
        ],

        // ===== SME =====
        'sme-01' => [
            'hero' => 'creative small business workspace',
            'content' => ['creative entrepreneur workspace', 'small business products', 'creative team working', 'startup office creative'],
        ],
        'sme-02' => [
            'hero' => 'professional business team meeting office',
            'content' => ['business team meeting', 'professional office workers', 'business consultant presentation', 'corporate team collaboration'],
        ],
        'sme-03' => [
            'hero' => 'delicious food restaurant dish',
            'content' => ['indonesian food dish', 'restaurant food photography', 'chef cooking kitchen', 'restaurant interior cozy'],
        ],
        'sme-04' => [
            'hero' => 'fashion boutique clothing elegant',
            'content' => ['fashion designer workshop', 'clothing boutique display', 'fashion model elegant', 'tailor sewing fabric', 'fashion store interior'],
        ],
        'sme-05' => [
            'hero' => 'coffee shop barista latte art',
            'content' => ['coffee shop interior', 'barista making coffee', 'latte art cup', 'coffee beans roasting'],
        ],
        'sme-06' => [
            'hero' => 'online store ecommerce products',
            'content' => ['ecommerce warehouse shipping', 'online shopping products', 'packaging orders boxes', 'product photography ecommerce'],
        ],
        'sme-07' => [
            'hero' => 'professional service business handshake',
            'content' => ['business consultant client', 'professional service team', 'office meeting handshake', 'business presentation'],
        ],
        'sme-08' => [
            'hero' => 'beauty spa salon treatment',
            'content' => ['spa facial treatment', 'beauty salon interior', 'woman spa relaxing', 'skincare treatment'],
        ],
        'sme-09' => [
            'hero' => 'wedding organizer event decoration',
            'content' => ['wedding venue decoration', 'event planner flowers', 'wedding table setting', 'wedding ceremony elegant'],
        ],
        'sme-10' => [
            'hero' => 'handmade craft artisan workshop',
            'content' => ['artisan pottery workshop', 'handmade craft products', 'craftsman working wood', 'handicraft artisan hands'],
        ],
    ],
];
