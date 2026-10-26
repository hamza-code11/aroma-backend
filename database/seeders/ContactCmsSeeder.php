<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContactCms;

class ContactCmsSeeder extends Seeder
{
    public function run(): void
    {
        ContactCms::updateOrCreate([], [
            'hero' => [
                'eyebrow' => 'Contact',
                'title' => 'Let’s find your',
                'titleHighlight' => 'signature scent',
                'lead' => 'Questions about a fragrance, an order, or a custom blend? We’d love to hear from you.',
            ],
            'banner_image' => null, // Aap initial image path daal sakte hain
            'info' => [
                ['id' => 'info-email', 'label' => 'Email', 'value' => 'hello@ishinearoma.com', 'icon' => 'mail'],
                ['id' => 'info-phone', 'label' => 'Phone', 'value' => '+92 300 000 0000', 'icon' => 'phone'],
                ['id' => 'info-address', 'label' => 'Studio', 'value' => 'Karachi, Pakistan', 'icon' => 'map'],
                ['id' => 'info-hours', 'label' => 'Hours', 'value' => 'Mon–Sat · 10am–6pm', 'icon' => 'clock'],
            ],
            'faq' => [
                'eyebrow' => 'FAQ',
                'title' => 'Common questions',
                'items' => [
                    [
                        'id' => 'faq-1',
                        'question' => 'Do you ship across Pakistan?',
                        'answer' => 'Yes, we ship nationwide via reliable courier partners.',
                    ],
                    [
                        'id' => 'faq-2',
                        'question' => 'Can I request a custom scent?',
                        'answer' => 'Absolutely. Get in touch and we’ll help you build a bespoke blend.',
                    ],
                ],
            ],
        ]);
    }
}
