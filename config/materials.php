<?php

return [
    'upload' => [
        'allowed_mimes' => array_map(
            'trim',
            explode(',', env('MATERIAL_ALLOWED_MIMES', 'pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png')),
        ),
        'max_size_kilobytes' => (int) env('MATERIAL_MAX_UPLOAD_KILOBYTES', 10240),
    ],
];
