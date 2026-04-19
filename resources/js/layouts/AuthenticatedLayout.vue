<script setup lang="ts">
import NavLink from '@/components/links/NavLink.vue';
import ThemeToggle from '@/components/ui/ThemeToggle.vue';
import { useKeybinds } from '@/composables/useKeybinds';
import { dashboard, logout } from '@/routes';
import { index as productsIndex } from '@/routes/products';
import { index as usersIndex } from '@/routes/users';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Building2, LayoutDashboard, LogOut, Package, Receipt, Settings, ShoppingCart, Truck, UserCog, Users } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const user = page.props.auth?.user;

const isAdmin = computed(() => user?.role === 'admin');
const isManager = computed(() => ['admin', 'manager'].includes(user?.role || ''));
const isInventory = computed(() => ['admin', 'manager', 'inventory'].includes(user?.role || ''));
const isAuditor = computed(() => user?.role === 'auditor');
const isCashier = computed(() => user?.role === 'cashier');

const iconMap = {
    layoutDashboard: LayoutDashboard,
    shoppingCart: ShoppingCart,
    package: Package,
    truck: Truck,
    users: Users,
    receipt: Receipt,
    userCog: UserCog,
};

const menuItems = computed(() => [
    { label: 'Dashboard', href: dashboard.url(), icon: 'layoutDashboard', keybind: 'Alt+1', active: page.url.startsWith(dashboard.url()) },
    { label: 'Point of Sale', href: '/pos', icon: 'shoppingCart', keybind: 'Alt+2', active: page.url.startsWith('/pos') },
    { label: 'Transactions', href: '/transactions', icon: 'receipt', keybind: 'Alt+3', active: page.url.startsWith('/transactions') },
    { label: 'Products', href: productsIndex.url(), icon: 'package', keybind: 'Alt+4', active: page.url.startsWith(productsIndex.url()) },
    { label: 'Purchases', href: '/purchases', icon: 'truck', keybind: 'Alt+5', active: page.url.startsWith('/purchases') },
    { label: 'Members', href: '/members', icon: 'users', keybind: 'Alt+6', active: page.url.startsWith('/members') },
    { label: 'Users', href: usersIndex.url(), icon: 'userCog', keybind: 'Alt+7', active: page.url.startsWith(usersIndex.url()) },
]);

const settingsItem = computed(() => ({
    label: 'Settings',
    href: '/settings',
    show: isAdmin.value,
    active: page.url.startsWith('/settings'),
}));

useKeybinds([
    { key: '1', alt: true, handler: () => router.visit(dashboard.url()) },
    { key: '2', alt: true, handler: () => router.visit('/pos') },
    { key: '3', alt: true, handler: () => router.visit('/transactions') },
    { key: '4', alt: true, handler: () => router.visit(productsIndex.url()) },
    { key: '5', alt: true, handler: () => router.visit('/purchases') },
    { key: '6', alt: true, handler: () => router.visit('/members') },
    { key: '7', alt: true, handler: () => router.visit(usersIndex.url()) },
]);
</script>

<template>
    <div class="flex h-screen bg-canvas">
        <aside class="flex w-72 flex-col border-r border-divider bg-surface">
            <div class="flex h-16 items-center justify-center gap-2 border-b border-divider">
                <Building2 class="h-6 w-6 text-primary-600" />
                <span class="text-xl font-semibold text-foreground">BMPC</span>
            </div>

            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1 px-2">
                    <li v-for="item in menuItems" :key="item.href">
                        <NavLink :href="item.href" :icon="iconMap[item.icon as keyof typeof iconMap]" :keybind="item.keybind" :active="item.active">
                            {{ item.label }}
                        </NavLink>
                    </li>
                </ul>
            </nav>

            <div class="border-t border-divider p-3">
                <div class="mb-3 flex items-center px-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-600 text-sm font-semibold text-white">
                        {{ user?.first_name?.[0] || 'U' }}
                    </div>
                    <div class="ml-3 min-w-0">
                        <p class="truncate text-sm font-medium text-foreground">{{ user?.first_name }}</p>
                        <p class="truncate text-xs text-foreground-soft">{{ user?.role }}</p>
                    </div>
                </div>

                <div class="mb-2 px-2">
                    <ThemeToggle />
                </div>

                <NavLink v-if="settingsItem.show" :href="settingsItem.href" :icon="Settings" :active="settingsItem.active" class="mb-2 py-2">
                    Settings
                </NavLink>

                <Link
                    :href="logout.url()"
                    method="post"
                    as="button"
                    class="flex w-full items-center rounded-md px-3 py-2 text-sm font-medium text-foreground-soft outline-none hover:bg-hover hover:text-foreground focus-visible:ring-primary-600"
                >
                    <LogOut class="h-5 w-5 shrink-0" />
                    <span class="ml-3">Log out</span>
                </Link>
            </div>
        </aside>

        <main class="flex-1 overflow-auto p-6">
            <slot />
        </main>
    </div>
</template>
