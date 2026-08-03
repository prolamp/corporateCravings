<?php

namespace App\Modules\PublicSite\Services;

use App\Modules\Cms\Services\SiteSettingsService;
use App\Modules\PublicSite\Mail\ContactInquiryMail;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class ContactService
{
    public function __construct(
        private readonly SiteSettingsService $settings,
    ) {}

    /**
     * @return array{
     *     hero_image_url: string,
     *     subjects: list<array{value: string, label: string}>,
     * }
     */
    public function payload(): array
    {
        return [
            'hero_image_url' => $this->designImageUrl(),
            'subjects' => [
                ['value' => 'General inquiry', 'label' => 'General inquiry'],
                ['value' => 'Corporate packages', 'label' => 'Corporate packages'],
                ['value' => 'Daily lunch / schedule', 'label' => 'Daily lunch / schedule'],
                ['value' => 'Bulk / event order', 'label' => 'Bulk / event order'],
                ['value' => 'Other', 'label' => 'Other'],
            ],
        ];
    }

    /**
     * @param  array{
     *     name: string,
     *     email: string,
     *     phone?: string|null,
     *     subject: string,
     *     message: string,
     * }  $data
     */
    public function submit(array $data): void
    {
        $to = $this->settings->publicPayload()['brand']['email'] ?? null;
        if (! is_string($to) || trim($to) === '') {
            throw new InvalidArgumentException('Contact email is not configured.');
        }

        Mail::to($to)->send(new ContactInquiryMail([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]));
    }

    private function designImageUrl(): string
    {
        foreach (['webp', 'png', 'jpg'] as $ext) {
            $relative = "images/contact/hero.{$ext}";
            if (is_file(public_path($relative))) {
                return asset($relative);
            }
        }

        return asset('images/contact/hero.webp');
    }
}
