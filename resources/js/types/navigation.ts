import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
};

/**
 * Navigation item untuk sidebar variant Lucide (linear, profesional).
 *
 * Berbeda dari {@link NavItem} — `icon` di sini wajib diisi komponen
 * Lucide supaya rendering konsisten & bisa menerima stroke-width kustom.
 */
export interface NavItemLucide {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon: LucideIcon;
    /** Only shown for admin role (enforced at composer level, not here). */
    adminOnly?: boolean;
}
