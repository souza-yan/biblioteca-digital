<?php

return [
    'upload' => [
        'allowed_mimes' => array_map(
            'trim',
            explode(',', env('MATERIAL_ALLOWED_MIMES', 'pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png')),
        ),
        'max_size_kilobytes' => (int) env('MATERIAL_MAX_UPLOAD_KILOBYTES', 10240),
    ],

    'pdf_cover' => [
        'disk' => 'local',
        'binary' => env('PDFTOPPM_BINARY', 'pdftoppm'),
        'max_dimension' => 720,
        'jpeg_quality' => 82,
        'max_size_kilobytes' => 2048,
        'timeout_seconds' => 20,
        'idle_timeout_seconds' => 10,
        'lock_seconds' => 90,
    ],

    'standard_preview' => [
        'width' => 2480,
        'height' => 3508,
    ],
];
