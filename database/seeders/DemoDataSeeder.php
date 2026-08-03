<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Modules\Addons\Models\Addon;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Delivery\Models\DeliveryZone;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Modules\Packages\Models\Package;
use App\Modules\Packages\Models\PackageItem;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberPackageHistory;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use App\Support\Enums\UserRole;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedAddons();
        $this->seedDeliveryZones();
        $this->seedExpenseCategories();
        $this->seedGroceryItems();
        $this->seedPackageAndSubscriber();
        $this->seedCms();
    }

    private function seedUsers(): void
    {
        $users = [
            ['name' => 'Admin User', 'email' => 'admin.user@corporatecraving.com', 'phone' => '03001110001', 'role' => UserRole::Admin],
            ['name' => 'Manager User', 'email' => 'manager@corporatecraving.com', 'phone' => '03001110002', 'role' => UserRole::Manager],
            ['name' => 'Assistant User', 'email' => 'assistant@corporatecraving.com', 'phone' => '03001110003', 'role' => UserRole::Assistant],
            ['name' => 'Demo Customer', 'email' => 'customer@corporatecraving.com', 'phone' => '03001110004', 'role' => UserRole::Customer],
            ['name' => 'Factory Foods Co', 'email' => 'subscriber@corporatecraving.com', 'phone' => '03001110005', 'role' => UserRole::Subscriber, 'company_name' => 'Factory Foods Co', 'address' => 'I-9 Islamabad'],
        ];

        foreach ($users as $row) {
            $user = User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'company_name' => $row['company_name'] ?? null,
                    'address' => $row['address'] ?? null,
                    'password' => Hash::make($row['phone']),
                    'status' => 'active',
                ],
            );
            $user->syncRoles([$row['role']->value]);
        }
    }

    private function seedAddons(): void
    {
        $addons = [
            ['title' => 'Raita', 'type' => 'sauce', 'price' => 80],
            ['title' => 'Mint Chutney', 'type' => 'sauce', 'price' => 60],
            ['title' => 'Soft Drink', 'type' => 'drink', 'price' => 100],
            ['title' => 'Mineral Water', 'type' => 'drink', 'price' => 50],
            ['title' => 'Naan', 'type' => 'extra', 'price' => 40],
        ];

        foreach ($addons as $index => $row) {
            Addon::query()->updateOrCreate(
                ['slug' => Str::slug($row['title'])],
                [
                    'title' => $row['title'],
                    'short_description' => $row['title'].' addon',
                    'type' => $row['type'],
                    'price' => $row['price'],
                    'is_global' => true,
                    'status' => 'active',
                    'sort_order' => $index + 1,
                ],
            );
        }
    }

    private function seedDeliveryZones(): void
    {
        $zones = [
            ['name' => 'I-8', 'fee' => 150, 'sort' => 1],
            ['name' => 'I-9', 'fee' => 150, 'sort' => 2],
            ['name' => 'I-10', 'fee' => 200, 'sort' => 3],
            ['name' => 'I-11', 'fee' => 200, 'sort' => 4],
            ['name' => 'G-8', 'fee' => 200, 'sort' => 5],
            ['name' => 'G-9', 'fee' => 200, 'sort' => 6],
            ['name' => 'G-10', 'fee' => 250, 'sort' => 7],
            ['name' => 'G-11', 'fee' => 250, 'sort' => 8],
            ['name' => 'G-13', 'fee' => 250, 'sort' => 9],
            ['name' => 'H-8', 'fee' => 200, 'sort' => 10],
            ['name' => 'H-9', 'fee' => 200, 'sort' => 11],
            ['name' => 'H-13', 'fee' => 250, 'sort' => 12],
        ];

        foreach ($zones as $zone) {
            DeliveryZone::query()->updateOrCreate(
                ['name' => $zone['name']],
                [
                    'fee' => $zone['fee'],
                    'is_active' => true,
                    'sort_order' => $zone['sort'],
                ],
            );
        }
    }

    private function seedExpenseCategories(): void
    {
        $items = [
            'Groceries', 'Maintenance', 'Employee Payments', 'Utilities', 'Rent',
            'Marketing', 'Transportation', 'Insurance', 'Taxes', 'Other',
        ];

        foreach ($items as $name) {
            ExpenseCategory::query()->updateOrCreate(
                ['name' => $name],
                ['description' => $name, 'is_active' => true],
            );
        }
    }

    /**
     * Grocery *items* (Chicken, Rice, Oil…) under the Groceries category.
     * These feed the dish cost calculator unit prices.
     */
    private function seedGroceryItems(): void
    {
        $category = ExpenseCategory::query()->where('name', 'Groceries')->first();
        if (! $category) {
            return;
        }

        $items = [
            ['title' => 'Chicken', 'unit' => 'kg', 'unit_price' => 800, 'quantity' => 10],
            ['title' => 'Beef', 'unit' => 'kg', 'unit_price' => 1200, 'quantity' => 5],
            ['title' => 'Mutton', 'unit' => 'kg', 'unit_price' => 2200, 'quantity' => 3],
            ['title' => 'Rice', 'unit' => 'kg', 'unit_price' => 300, 'quantity' => 25],
            ['title' => 'Cooking Oil', 'unit' => 'litre', 'unit_price' => 500, 'quantity' => 10],
            ['title' => 'Onion', 'unit' => 'kg', 'unit_price' => 120, 'quantity' => 15],
            ['title' => 'Tomato', 'unit' => 'kg', 'unit_price' => 150, 'quantity' => 10],
            ['title' => 'Yogurt', 'unit' => 'kg', 'unit_price' => 250, 'quantity' => 8],
            ['title' => 'Garlic Ginger Paste', 'unit' => 'kg', 'unit_price' => 400, 'quantity' => 2],
            ['title' => 'Spices Mix', 'unit' => 'kg', 'unit_price' => 1800, 'quantity' => 1],
        ];

        foreach ($items as $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];

            Expense::query()->updateOrCreate(
                [
                    'expense_category_id' => $category->id,
                    'title' => $item['title'],
                ],
                [
                    'description' => 'Demo grocery stock for dish cost calculator',
                    'quantity' => Money::of($qty),
                    'unit' => $item['unit'],
                    'unit_price' => Money::of($price),
                    'total' => Money::of($qty * $price),
                    'date' => now()->toDateString(),
                    'status' => 'approved',
                ],
            );
        }
    }

    private function seedPackageAndSubscriber(): void
    {
        $dishTitles = ['Chicken Biryani', 'Chicken Karahi', 'Daal Chawal'];
        $dishes = Dish::query()->whereIn('title', $dishTitles)->with('singleVariations')->get();

        if ($dishes->count() < 2) {
            return;
        }

        $sum = 0.0;
        $items = [];
        foreach ($dishes as $dish) {
            $variation = $dish->singleVariations->first();
            $unit = (float) ($variation->price ?? 0);
            $sum += $unit;
            $items[] = compact('dish', 'variation', 'unit');
        }

        $base = count($items) > 0 ? $sum / count($items) : 0.0;
        $discount = 100.0;
        $total = max(0, $base - $discount);

        $package = Package::query()->updateOrCreate(
            ['slug' => 'office-lunch-combo'],
            [
                'title' => 'Office Lunch Combo',
                'short_description' => 'Corporate daily lunch package with rotating dishes.',
                'base_amount_per_person' => Money::of($base),
                'discount_amount' => Money::of($discount),
                'discount_percent' => 0,
                'total_amount_per_person' => Money::of($total),
                'status' => 'active',
                'sort_order' => 1,
            ],
        );

        foreach ($items as $index => $item) {
            PackageItem::query()->updateOrCreate(
                [
                    'package_id' => $package->id,
                    'dish_id' => $item['dish']->id,
                ],
                [
                    'dish_variation_id' => $item['variation']?->id,
                    'unit_price' => Money::of($item['unit']),
                    'sort_order' => $index + 1,
                ],
            );
        }

        $subscriberUser = User::query()->where('email', 'subscriber@corporatecraving.com')->first();
        if (! $subscriberUser) {
            return;
        }

        $packageAmount = (float) $package->total_amount_per_person;
        $contractDiscount = 50.0;
        $agreedTotal = max(0, $packageAmount - $contractDiscount);

        $contract = SubscribeContract::query()->updateOrCreate(
            ['user_id' => $subscriberUser->id],
            [
                'package_id' => $package->id,
                'meal_type' => 'lunch',
                'days_of_week' => ['mon', 'tue', 'wed', 'thu', 'fri'],
                'persons' => 50,
                'delivery_time' => '13:00:00',
                'package_amount' => Money::of($packageAmount),
                'discount' => Money::of($contractDiscount),
                'agreed_total' => Money::of($agreedTotal),
                'status' => 'active',
                'starts_on' => now()->startOfMonth()->toDateString(),
            ],
        );

        SubscriberPackageHistory::query()->updateOrCreate(
            [
                'subscribe_contract_id' => $contract->id,
                'effective_from' => now()->startOfMonth(),
            ],
            [
                'package_id' => $package->id,
                'package_title' => $package->title,
                'package_amount' => $contract->package_amount,
                'discount' => $contract->discount,
                'agreed_total' => $contract->agreed_total,
                'persons' => $contract->persons,
                'effective_to' => null,
            ],
        );

        $rotation = $dishes->values();
        $start = Carbon::now()->startOfWeek();
        for ($i = 0; $i < 5; $i++) {
            $date = $start->copy()->addDays($i);
            $dish = $rotation[$i % $rotation->count()];
            $persons = 50 - ($i * 2);
            $unit = (float) $contract->agreed_total;

            SubscriberSchedule::query()->updateOrCreate(
                [
                    'subscribe_contract_id' => $contract->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'package_id' => $package->id,
                    'dish_id' => $dish->id,
                    'week_of_month' => (int) ceil($date->day / 7),
                    'dish_title' => $dish->title,
                    'package_title' => $package->title,
                    'persons' => $persons,
                    'unit_price' => Money::of($unit),
                    'total' => Money::of($unit * $persons),
                    'status' => 'scheduled',
                ],
            );
        }
    }

    private function seedCms(): void
    {
        $pages = [
            ['title' => 'Privacy Policy', 'slug' => 'privacy-policy'],
            ['title' => 'Terms & Conditions', 'slug' => 'terms'],
            ['title' => 'Delivery Policy', 'slug' => 'delivery-policy'],
            ['title' => 'Cancellation & Refund Policy', 'slug' => 'cancellation-refund'],
            ['title' => 'FAQ', 'slug' => 'faq'],
        ];

        foreach ($pages as $page) {
            $body = match ($page['slug']) {
                'faq' => $this->faqBodyHtml(),
                'privacy-policy' => $this->legalBodyHtml(
                    'This Privacy Policy describes how Corporate Cravings (“we”, “us”, “our”) collects, uses, and protects personal information when you use our website, place orders, enquire about corporate packages, or manage a subscriber account. By using our services you agree to the practices described here.',
                    [
                        'Who we are' => 'Corporate Cravings provides prepared meals and corporate catering primarily in Islamabad and nearby delivery zones. You can reach us using the phone, WhatsApp, email, and address shown on our Contact page and site footer.',
                        'Information we collect' => [
                            'intro' => 'Depending on how you interact with us, we may collect:',
                            'items' => [
                                'Identity and contact details — name, phone number, email address, and delivery or office address.',
                                'Order and billing details — items ordered, quantities, delivery zone, amounts, payment status (including cash on delivery), and order notes.',
                                'Subscriber and contract details — company or subscriber profile, package selections, schedules, bill slips, and agreed commercial terms.',
                                'Enquiry messages — information you send through the contact form, WhatsApp, or email.',
                                'Technical data — basic device and browser information, cookies needed for the site to function (for example session and cart), and approximate usage data if analytics are enabled after consent.',
                            ],
                        ],
                        'How we use your information' => [
                            'intro' => 'We use personal information to:',
                            'items' => [
                                'Accept, prepare, deliver, and support orders.',
                                'Manage corporate packages, bulk/event orders, subscriber schedules, and invoices or bill slips.',
                                'Respond to enquiries and provide customer service.',
                                'Improve our menu, website, and operations.',
                                'Meet legal, accounting, and fraud-prevention requirements.',
                            ],
                            'outro' => 'We do not sell your personal information to third parties.',
                        ],
                        'Sharing of information' => 'We may share information with staff and trusted service providers who help us operate (for example hosting, messaging, or payment-related tools), only as needed to provide the service. We may also disclose information when required by law or to protect our rights, customers, or safety.',
                        'Retention' => 'We keep order, subscriber, and enquiry records for as long as needed for operations, accounting, dispute resolution, and legal obligations, then delete or anonymise them when they are no longer required.',
                        'Your choices' => 'You may request access to, correction of, or deletion of personal information we hold about you, subject to legal and operational requirements (for example completed order records). Contact us using the details on our Contact page. You can also manage cookie or analytics preferences where a consent banner is shown.',
                        'Security' => 'We take reasonable technical and organisational measures to protect personal data. No method of transmission or storage is completely secure; please use strong passwords for any account and do not share login details.',
                        'Children' => 'Our services are intended for businesses and adults placing food orders. We do not knowingly collect personal information from children.',
                        'Updates to this policy' => 'We may update this Privacy Policy from time to time. The “Last updated” date on this page reflects the latest version. Continued use of the site after changes means you accept the updated policy.',
                        'Contact' => 'For privacy questions or requests, email or WhatsApp Corporate Cravings using the contact details published on this website.',
                    ],
                ),
                'terms' => $this->legalBodyHtml(
                    'These Terms & Conditions govern your use of the Corporate Cravings website and related ordering and catering services. By browsing the site, creating an account, or placing an order, you agree to these terms.',
                    [
                        'About our services' => 'Corporate Cravings offers à-la-carte and package meals, corporate lunch programmes, and bulk or event catering. Website features may include browsing the menu, adding items to a cart, cash-on-delivery checkout, order tracking, a customer account area, and a subscriber portal for contracted schedules.',
                        'Eligibility and accounts' => 'You must provide accurate contact and delivery information. You are responsible for activity under your account and for keeping login credentials confidential. We may suspend accounts that misuse the service or submit false information.',
                        'Website orders (cash on delivery)' => [
                            'intro' => 'Orders placed through website checkout are typically paid cash on delivery unless we agree otherwise in writing:',
                            'items' => [
                                'Prices shown are in Pakistani Rupees (Rs.) and may change without notice until an order is confirmed.',
                                'Delivery fees depend on the zone selected at checkout.',
                                'An order is an offer to purchase; we may decline or adjust orders due to availability, coverage, or operational limits.',
                                'You agree to receive the order at the stated address and to pay the amount due on delivery.',
                            ],
                        ],
                        'Corporate packages, bulk and event orders' => 'Corporate packages, subscriber contracts, and bulk/event orders are confirmed through WhatsApp, phone, or a written contract. Public package prices on the website are indicative marketing rates; contracted amounts, discounts, person counts, and delivery arrangements may differ and take precedence once agreed.',
                        'Menu, allergens and availability' => 'Menu descriptions are provided in good faith. Ingredients and availability can vary. If you have allergies or dietary restrictions, tell us before ordering. We cannot guarantee a completely allergen-free kitchen environment.',
                        'Acceptable use' => [
                            'intro' => 'You agree not to:',
                            'items' => [
                                'Misuse the website, attempt unauthorised access, or disrupt our systems.',
                                'Submit false, harassing, or fraudulent orders or enquiries.',
                                'Scrape, copy, or reuse our content or branding without permission for commercial purposes.',
                            ],
                        ],
                        'Intellectual property' => 'Website content, branding, logos, and materials belong to Corporate Cravings or our licensors. You may not use them without prior written consent except for personal, non-commercial viewing of the site.',
                        'Limitation of liability' => 'To the fullest extent permitted by law, Corporate Cravings is not liable for indirect, incidental, or consequential losses arising from use of the site or services. Our liability related to a specific order is limited to the amount paid for that order, except where liability cannot be limited under applicable law.',
                        'Governing law' => 'These terms are governed by the laws of Pakistan. Disputes will first be addressed amicably; failing that, courts in Islamabad shall have jurisdiction.',
                        'Changes' => 'We may update these terms periodically. The version published on this page applies from the date shown as last updated.',
                        'Contact' => 'Questions about these terms can be sent via our Contact page, WhatsApp, or the phone number listed on the site.',
                    ],
                ),
                'delivery-policy' => $this->legalBodyHtml(
                    'This Delivery Policy explains how Corporate Cravings delivers meals for website orders, corporate packages, and subscriber schedules in our service areas.',
                    [
                        'Service area' => 'We deliver primarily within Islamabad and selected nearby zones. Coverage is shown during checkout through delivery-zone selection. Addresses outside active zones may not be available for website checkout; contact us on WhatsApp for special arrangements.',
                        'Delivery fees' => [
                            'intro' => 'Fees depend on order type:',
                            'items' => [
                                'Website cart orders — the fee for the zone you select is calculated at checkout and shown before you confirm.',
                                'Corporate and subscriber contracts — delivery may be included, waived, or charged as agreed in the contract.',
                                'Bulk or event orders — delivery and setup fees are confirmed with our team for that booking.',
                            ],
                        ],
                        'Delivery windows' => 'We aim to deliver within the time window communicated when the order or schedule is confirmed. For recurring subscriber meals, deliveries follow the agreed schedule. Exact arrival times can vary with kitchen load, traffic, weather, building access, and security checks.',
                        'Someone must receive the order' => 'Please ensure someone is available at the delivery address to receive and, for COD orders, pay for the meal. If we cannot complete delivery because the recipient is unavailable, we may attempt to contact you; redelivery is not always possible and kitchen-prepared food may still be charged according to our cancellation policy.',
                        'Access and instructions' => 'Provide clear address details, landmarks, gate or office instructions, and a reachable phone number. Delays caused by incomplete directions, restricted entry, or unanswered calls are outside our control.',
                        'Packaging and food safety' => 'Meals are packed for transport and intended to be consumed within a reasonable time after delivery. Refrigerate leftovers promptly. Reheating instructions, where relevant, should be followed for food safety.',
                        'Same-day changes' => 'For changes to address, time, or quantity on the delivery day, WhatsApp or call us as early as possible. We will help where kitchen and rider capacity allow; late changes may not be possible once cooking or dispatch has started.',
                        'Delays and disruptions' => 'Events beyond our reasonable control (severe weather, road closures, strikes, emergencies) may delay or prevent delivery. We will notify you when practical and discuss options such as a revised window or cancellation where food has not yet been prepared.',
                        'Questions' => 'For delivery coverage or timing questions before you order, use Contact or WhatsApp. For an active order, share your order number so we can assist quickly.',
                    ],
                ),
                'cancellation-refund' => $this->legalBodyHtml(
                    'This Cancellation & Refund Policy explains how Corporate Cravings handles changes, cancellations, and complaints for website orders, corporate deals, and subscriber schedules. Fresh food is prepared to order, so timing matters.',
                    [
                        'Website cash-on-delivery orders' => [
                            'intro' => 'Please contact us by phone or WhatsApp as soon as you need to change or cancel:',
                            'items' => [
                                'Before kitchen preparation — we will usually cancel at no charge.',
                                'After preparation has started — cancellation may no longer be possible, and the order may still be payable.',
                                'After dispatch — cancellations are generally not accepted; refusal on delivery without a valid quality or error issue may still leave the order payable.',
                            ],
                        ],
                        'Corporate packages and bulk or event orders' => 'Cancellations, date changes, headcount changes, and refunds follow the terms confirmed with our team for that booking or written contract. Large orders often require advance notice so we can adjust purchasing and staffing. Unless otherwise agreed, late cancellations may incur a partial or full charge.',
                        'Subscriber schedules' => 'Recurring subscriber meals follow the schedule and contract rules (including any notice period for pauses, skips, or ending the service). Day-level changes should be requested as early as your contract allows—ideally before the kitchen cutoff for that delivery day.',
                        'Payment and refunds' => [
                            'intro' => 'Most website orders are paid cash on delivery, so refunds usually apply only when money has already been collected or a credit is agreed:',
                            'items' => [
                                'If we cancel an order before delivery for our own operational reasons, you will not be charged for that order.',
                                'If a paid amount must be returned, we will agree the method (for example cash adjustment on a future order or another mutually accepted method).',
                                'Promotional discounts and complimentary items are handled case by case and are not always refundable as cash.',
                            ],
                        ],
                        'Quality or missing-item issues' => 'If something is wrong with your order (missing items, significant quality concern, wrong dish), tell us the same day with your order number and, where helpful, a photo. We may offer a remake, partial credit, replacement item, or other fair remedy after we review the case. Complaints raised long after delivery are harder to verify and may not qualify for a remedy.',
                        'How to request a cancellation or refund' => 'Contact Corporate Cravings using the phone, WhatsApp, or email on our Contact page. Include your name, order number or company name, delivery date, and a short description of the request. We aim to respond during business hours listed on the site.',
                        'Fair use' => 'We reserve the right to refuse repeated abusive cancellation patterns or fraudulent claims. This policy does not limit any rights you may have under applicable Pakistani consumer law that cannot be waived by contract.',
                    ],
                ),
                default => '<p>'.$page['title'].' content will be managed from admin.</p>',
            };

            Page::query()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'body' => $body,
                    'meta_title' => $page['title'].' | Corporate Cravings',
                    'meta_description' => $page['slug'] === 'faq'
                        ? 'Answers about delivery, packages, orders, and office lunch contracts.'
                        : $page['title'].' for Corporate Cravings.',
                    'status' => 'published',
                ],
            );
        }

        $settings = [
            'brand.name' => 'Corporate Cravings',
            'brand.phone' => '03041160070',
            'brand.whatsapp' => '923041160070',
            'brand.email' => 'info@corporatecraving.com',
            'brand.address' => 'I-9 Islamabad, Pakistan',
            'brand.hours' => 'Mon–Sat 10:00–22:00',
            'analytics.ga_measurement_id' => '',
        ];

        foreach ($settings as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'group' => explode('.', $key)[0],
                    'value' => $value,
                    'type' => 'string',
                ],
            );
        }
    }

    private function faqBodyHtml(): string
    {
        $items = app(\App\Modules\PublicSite\Services\FaqService::class)->defaultItems();
        $html = '';
        foreach ($items as $item) {
            $html .= '<h3>'.e($item['question']).'</h3><p>'.e($item['answer']).'</p>';
        }

        return $html;
    }

    /**
     * @param  array<string, string|array{intro?: string, items?: list<string>, outro?: string}>  $sections
     */
    private function legalBodyHtml(string $intro, array $sections): string
    {
        $html = '<p>'.e($intro).'</p>';

        foreach ($sections as $heading => $content) {
            $html .= '<h2>'.e($heading).'</h2>';

            if (is_string($content)) {
                $html .= '<p>'.e($content).'</p>';

                continue;
            }

            if (! empty($content['intro'])) {
                $html .= '<p>'.e($content['intro']).'</p>';
            }

            if (! empty($content['items'])) {
                $html .= '<ul>';
                foreach ($content['items'] as $item) {
                    $html .= '<li>'.e($item).'</li>';
                }
                $html .= '</ul>';
            }

            if (! empty($content['outro'])) {
                $html .= '<p>'.e($content['outro']).'</p>';
            }
        }

        return $html;
    }
}
