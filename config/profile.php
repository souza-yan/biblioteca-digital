<?php

return [
    'photo' => [
        'allowed_mimes' => ['jpg', 'jpeg', 'png'],
        'max_size_kilobytes' => (int) env('PROFILE_PHOTO_MAX_UPLOAD_KILOBYTES', 5120),
    ],
];
