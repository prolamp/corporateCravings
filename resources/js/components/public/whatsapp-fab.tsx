import { MessageCircle } from 'lucide-react';
import { usePage } from '@inertiajs/react';

export function WhatsappFab() {
    const { site } = usePage().props;

    if (!site?.whatsapp_url) {
        return null;
    }

    return (
        <a
            href={site.whatsapp_url}
            target="_blank"
            rel="noreferrer"
            data-wa-placement="fab"
            aria-label="Chat on WhatsApp"
            className="fixed right-5 bottom-5 z-50 flex size-14 items-center justify-center rounded-full bg-linear-to-br from-[#25D366] to-[#128C7E] text-white shadow-lg transition-transform active:scale-95 md:hidden"
        >
            <MessageCircle className="size-6 fill-current" />
        </a>
    );
}
