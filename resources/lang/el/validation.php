<?php


// Code generates using ChatGPT
return [
    'required' => 'Το πεδίο είναι υποχρεωτικό.',
    'min' => [
        'numeric' => 'Το πεδίο πρέπει να είναι τουλάχιστον :min.',
    ],
    'regex' => 'Το πεδίο δεν έχει σωστή μορφή.',
    'date' => 'Το πεδίο πρέπει να είναι έγκυρη ημερομηνία.',
    'business_id.required' => 'Το Id της εταιρείας είναι υποχρεωτικό.',
    'business_id.exists' => 'Η εταιρεία δεν υπάρχει.',
    'vat_num.regex' => "Μη έγκυρο ΑΦΜ",
    // Add other custom messages here
];
