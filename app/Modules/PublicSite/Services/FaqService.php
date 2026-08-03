<?php

namespace App\Modules\PublicSite\Services;

use App\Models\Page;
use DOMDocument;
use DOMElement;
use DOMXPath;

class FaqService
{
    /**
     * @return array{
     *     title: string,
     *     meta_title: string|null,
     *     meta_description: string|null,
     *     items: list<array{question: string, answer: string}>,
     * }
     */
    public function payload(): array
    {
        $page = Page::query()
            ->where('slug', 'faq')
            ->where('status', 'published')
            ->first();

        $items = $page ? $this->parseItems((string) $page->body) : [];
        if ($items === []) {
            $items = $this->defaultItems();
        }

        return [
            'title' => $page?->title ?: 'FAQ',
            'meta_title' => $page?->meta_title,
            'meta_description' => $page?->meta_description,
            'items' => $items,
        ];
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public function defaultItems(): array
    {
        return [
            [
                'question' => 'What areas do you deliver to?',
                'answer' => 'We deliver across Islamabad and nearby sectors. Delivery fees follow the zone you select at checkout (or the zone agreed on your corporate contract).',
            ],
            [
                'question' => 'How do corporate packages work?',
                'answer' => 'Packages are priced per person for office and event catering. Browse packages on the site, then WhatsApp us for a quote. Private contract rates for subscribers are shown only in your subscriber portal.',
            ],
            [
                'question' => 'Can I order single servings online?',
                'answer' => 'Yes. Cartable single-serving dishes can be ordered on the website with cash on delivery. Choose your delivery zone at checkout to apply the correct fee.',
            ],
            [
                'question' => 'How do bulk / kg orders work?',
                'answer' => 'Bulk offerings (deghs and large trays) are ordered via WhatsApp. Our team confirms quantity, timing, and pricing, then enters the deal in admin.',
            ],
            [
                'question' => 'What payment methods do you accept?',
                'answer' => 'Website checkout is cash on delivery (COD). Corporate and bulk deals may be invoiced as agreed with our team.',
            ],
            [
                'question' => 'How do I track my order?',
                'answer' => 'Use Track Order with your order number. Customers can also see order history and reorder from the customer portal after login.',
            ],
            [
                'question' => 'Can I change or cancel an order?',
                'answer' => 'Contact us as soon as possible by WhatsApp or phone. Changes depend on kitchen prep status—see our Cancellation & Refund policy for details.',
            ],
            [
                'question' => 'Do you offer daily office lunch contracts?',
                'answer' => 'Yes. Subscriber companies get scheduled meals on contracted days. After signup, you can view schedules, history, bills, and private contract pricing in the subscriber portal.',
            ],
        ];
    }

    /**
     * Parse CMS body into Q&A pairs from h2/h3 headings followed by paragraph blocks.
     *
     * @return list<array{question: string, answer: string}>
     */
    private function parseItems(string $body): array
    {
        $body = trim($body);
        if ($body === '') {
            return [];
        }

        $wrapped = '<div id="faq-root">'.$body.'</div>';
        $dom = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$wrapped);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($dom);
        $headings = $xpath->query('//*[@id="faq-root"]//h2|//*[@id="faq-root"]//h3');
        if ($headings === false || $headings->length === 0) {
            return [];
        }

        $items = [];
        foreach ($headings as $heading) {
            if (! $heading instanceof DOMElement) {
                continue;
            }

            $question = trim(preg_replace('/\s+/', ' ', $heading->textContent ?? '') ?? '');
            if ($question === '') {
                continue;
            }

            $answerParts = [];
            for ($node = $heading->nextSibling; $node !== null; $node = $node->nextSibling) {
                if ($node instanceof DOMElement && in_array(strtolower($node->tagName), ['h2', 'h3'], true)) {
                    break;
                }
                if ($node instanceof DOMElement) {
                    $text = trim(preg_replace('/\s+/', ' ', $node->textContent ?? '') ?? '');
                    if ($text !== '') {
                        $answerParts[] = $text;
                    }
                }
            }

            $answer = trim(implode("\n\n", $answerParts));
            if ($answer === '') {
                continue;
            }

            $items[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        return $items;
    }
}
