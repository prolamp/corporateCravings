export type PublicNavItem = {
    title: string;
    href: string;
};

/** Primary marketing nav — destinations land in later public phases. */
export const publicPrimaryNav: PublicNavItem[] = [
    { title: 'Home', href: '/' },
    { title: 'About', href: '/about' },
    { title: 'Menu', href: '/menu' },
    { title: 'Packages', href: '/packages' },
    { title: 'Bulk Orders', href: '/bulk-orders' },
    { title: 'Track Order', href: '/track-order' },
    { title: 'Contact', href: '/contact' },
];

export const publicFooterServices: PublicNavItem[] = [
    { title: 'Daily Menu', href: '/menu' },
    { title: 'Corporate Packages', href: '/packages' },
    { title: 'Bulk Orders', href: '/bulk-orders' },
];

export const publicFooterCompany: PublicNavItem[] = [
    { title: 'About Us', href: '/about' },
    { title: 'FAQ', href: '/faq' },
    { title: 'Privacy Policy', href: '/privacy-policy' },
    { title: 'Terms of Service', href: '/terms' },
    { title: 'Delivery Policy', href: '/delivery-policy' },
    { title: 'Cancellation & Refund', href: '/cancellation-refund' },
];
