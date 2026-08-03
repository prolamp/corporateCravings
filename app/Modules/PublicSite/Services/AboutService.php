<?php

namespace App\Modules\PublicSite\Services;

class AboutService
{
    /**
     * @return array{
     *     hero_image_url: string,
     *     story_image_url: string,
     *     operations_image_url: string,
     *     partner_image_url: string,
     *     story_stats: list<array{value: string, label: string}>,
     *     impact_stats: list<array{value: string, label: string}>,
     *     values: list<array{title: string, body: string}>,
     * }
     */
    public function payload(): array
    {
        return [
            'hero_image_url' => $this->designImageUrl('hero'),
            'story_image_url' => $this->designImageUrl('journey'),
            'operations_image_url' => $this->designImageUrl('operations'),
            'partner_image_url' => $this->designImageUrl('partner'),
            'story_stats' => [
                ['value' => '15+', 'label' => 'Culinary awards'],
                ['value' => '300+', 'label' => 'Handcrafted recipes'],
            ],
            'impact_stats' => [
                ['value' => '50+', 'label' => 'Partners'],
                ['value' => '10k+', 'label' => 'Monthly meals'],
                ['value' => '98%', 'label' => 'Satisfaction'],
            ],
            'values' => [
                [
                    'title' => 'Authenticity',
                    'body' => 'We respect the heritage of our ingredients and recipes, delivering flavors that are unmistakably authentic yet contemporary in presentation.',
                ],
                [
                    'title' => 'Precision',
                    'body' => 'From temperature control to table settings, we operate with a level of precision that matches the professional standards of our clients.',
                ],
                [
                    'title' => 'Reliability',
                    'body' => 'Consistency is our promise. Our clients count on us for flawless execution every single day, without fail or compromise.',
                ],
            ],
        ];
    }

    private function designImageUrl(string $name): string
    {
        foreach (['webp', 'png', 'jpg'] as $ext) {
            $relative = "images/about/{$name}.{$ext}";
            if (is_file(public_path($relative))) {
                return asset($relative);
            }
        }

        return asset("images/about/{$name}.webp");
    }
}
