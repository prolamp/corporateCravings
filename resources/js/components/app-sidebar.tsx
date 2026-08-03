import { Link, usePage } from '@inertiajs/react';
import { LayoutGrid } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { filterAdminNav } from '@/lib/admin-nav';
import { isManagementUser, isSubscriber } from '@/lib/roles';
import { filterSubscriberNav } from '@/lib/subscriber-nav';
import { dashboard } from '@/routes';

export function AppSidebar() {
    const { auth } = usePage().props;
    const user = auth.user;
    const management = isManagementUser(user);
    const subscriber = isSubscriber(user) && !management;

    const portalHome = subscriber
        ? '/subscriber/dashboard'
        : '/customer/dashboard';

    const groups = management
        ? filterAdminNav(user)
        : subscriber
          ? filterSubscriberNav(user)
          : [
                {
                    label: 'Portal',
                    items: [
                        {
                            title: 'Dashboard',
                            href: portalHome,
                            icon: LayoutGrid,
                        },
                    ],
                },
            ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link
                                href={management ? dashboard() : portalHome}
                                prefetch
                            >
                                <AppLogo
                                    subtitle={
                                        management
                                            ? 'Admin Portal'
                                            : subscriber
                                              ? 'Subscriber Portal'
                                              : 'Customer Portal'
                                    }
                                />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain groups={groups} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
