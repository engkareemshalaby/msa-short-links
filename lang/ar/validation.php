<?php

return [
    'required' => 'حقل :attribute مطلوب.',
    'email' => 'يجب أن يكون :attribute بريدًا إلكترونيًا صحيحًا.',
    'url' => 'يجب أن يكون :attribute رابطًا صحيحًا يبدأ بـ http أو https.',
    'unique' => 'قيمة :attribute مستخدمة بالفعل.',
    'confirmed' => 'تأكيد :attribute غير مطابق.',
    'min' => ['string' => 'يجب ألا يقل :attribute عن :min أحرف.'],
    'max' => ['string' => 'يجب ألا يزيد :attribute عن :max حرفًا.'],
    'after' => 'يجب أن يكون :attribute تاريخًا لاحقًا.',
    'regex' => 'صيغة :attribute غير صحيحة.',
    'attributes' => [
        'title' => 'العنوان', 'destination_url' => 'رابط الوجهة', 'custom_code' => 'الاسم المختصر',
        'code' => 'الكود', 'expires_at' => 'تاريخ الانتهاء', 'name' => 'الاسم', 'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور', 'role' => 'الدور',
    ],
];
