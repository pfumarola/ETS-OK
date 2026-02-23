<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $types = config('email_templates.types', []);
        foreach ($types as $tipo => $config) {
            EmailTemplate::firstOrCreate(
                ['tipo' => $tipo],
                [
                    'subject' => $config['default_subject'] ?? '[{{appName}}]',
                    'body_html' => $config['default_body'] ?? '',
                ]
            );
        }
    }
}
