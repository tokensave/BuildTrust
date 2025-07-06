<?php

declare(strict_types=1);
declare(ticks=1000);

return [
    'fallbacks' => [
        'ad_image' => '/images/default-ad.png',
        'user_avatar' => '/images/default-avatar.png',
        'deal_doc' => '/documents/fallback.pdf',
    ],
    'mime_types' => [
        'ad_images' => ['image/jpeg', 'image/png', 'image/webp'],
        'user_avatars' => ['image/jpeg', 'image/png'],
        'deal_documents' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ]
];
