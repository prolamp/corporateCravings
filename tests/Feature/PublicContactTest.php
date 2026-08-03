<?php

use App\Modules\PublicSite\Mail\ContactInquiryMail;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('contact page is public', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/contact')
            ->has('subjects')
            ->where('hero_image_url', fn ($url) => str_contains((string) $url, '/images/contact/hero.'))
            ->has('site.brand.email')
            ->has('site.whatsapp_url'));
});

test('contact form sends inquiry email', function () {
    Mail::fake();

    $this->post(route('contact.store'), [
        'name' => 'Ayesha Khan',
        'email' => 'ayesha@example.com',
        'phone' => '03001112233',
        'subject' => 'Corporate packages',
        'message' => 'We need lunch for 40 people starting next month.',
    ])
        ->assertRedirect(route('contact'))
        ->assertSessionHas('code', 'contact_sent');

    Mail::assertSent(ContactInquiryMail::class, function (ContactInquiryMail $mail) {
        return $mail->inquiry['email'] === 'ayesha@example.com'
            && $mail->inquiry['subject'] === 'Corporate packages';
    });
});

test('contact form validates required fields', function () {
    Mail::fake();

    $this->from(route('contact'))
        ->post(route('contact.store'), [])
        ->assertRedirect(route('contact'))
        ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

    Mail::assertNothingSent();
});
