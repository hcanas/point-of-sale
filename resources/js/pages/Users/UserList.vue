<script setup lang="ts">
import BtnPrimary from '@/components/buttons/BtnPrimary.vue';
import FormInput from '@/components/forms/FormInput.vue';
import DetailLink from '@/components/links/DetailLink.vue';
import DataTable from '@/components/tables/DataTable.vue';
import Badge from '@/components/ui/Badge.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Toast from '@/components/ui/Toast.vue';
import { useKeybinds } from '@/composables/useKeybinds';
import { useQueryStrings } from '@/composables/useQueryStrings';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { show } from '@/routes/users';
import type { User } from '@/types';
import type { PaginationData } from '@/types/pagination';
import { usePage } from '@inertiajs/vue3';
import { Plus, Search } from 'lucide-vue-next';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import UserFormModal from './Partials/UserFormModal.vue';

const props = defineProps<{
    users: PaginationData;
}>();

const { buildDetailUrl, get, set } = useQueryStrings();

const search = computed({
    get: () => get('search') ?? '',
    set: (val) => set('search', val || undefined),
});

const searchInputRef = ref<InstanceType<typeof FormInput> | null>(null);
const showModal = ref(false);
const selectedUser = ref<User | null>(null);

const page = usePage();
const showToast = ref(false);
const toastMessage = ref('');

const hideToast = () => {
    showToast.value = false;
};

const isNewUser = (user: User) => {
    if (!user.created_at) return false;
    const createdAt = new Date(user.created_at);
    const now = new Date();
    const hoursDiff = (now.getTime() - createdAt.getTime()) / (1000 * 60 * 60);
    return hoursDiff < 24;
};

watch(
    () => page.props.flash?.success,
    (message) => {
        if (message) {
            toastMessage.value = message;
            showToast.value = true;
        }
    },
    { immediate: true },
);

onMounted(() => {
    nextTick(() => searchInputRef.value?.focus());
});

useKeybinds([
    { key: 'f', ctrl: true, handler: () => searchInputRef.value?.focus() },
    { key: 'n', ctrl: true, handler: () => openModal() },
]);

const openModal = (user: User | null = null) => {
    (document.activeElement as HTMLElement)?.blur();
    selectedUser.value = user;
    showModal.value = true;
};

const closeModal = (user?: User) => {
    const wasEditing = selectedUser.value?.id !== undefined;
    showModal.value = false;
    selectedUser.value = null;

    if (user && !wasEditing) {
        props.users.data.unshift(user);
        props.users.total++;
        if (props.users.from !== null) props.users.from = Math.min(props.users.from, 1);
        if (props.users.to !== null) props.users.to++;

        toastMessage.value = 'User created successfully.';
        showToast.value = true;
    }

    nextTick(() => searchInputRef.value?.focus());
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <PageHeader title="Users">
                <div class="relative w-64">
                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-foreground-soft" />
                    <FormInput
                        ref="searchInputRef"
                        v-model="search"
                        type="text"
                        placeholder="Search users..."
                        variant="surface"
                        keybind="Ctrl+F"
                        class="pl-10"
                    />
                </div>
                <BtnPrimary keybind="Ctrl+N" @click="openModal()">
                    <Plus class="h-4 w-4" />
                    Add User
                </BtnPrimary>
            </PageHeader>

            <div
                class="rounded-lg bg-surface p-6 shadow-[inset_0px_0px_0px_1px_rgba(30,41,59,0.16)] dark:shadow-[inset_0px_0px_0px_1px_rgba(148,163,184,0.2)]"
            >
                <DataTable
                    :columns="[
                        { key: 'last_name', label: 'Name', sortable: true, width: 'fill' },
                        { key: 'username', label: 'Username', sortable: true },
                        { key: 'role', label: 'Role', sortable: true },
                        { key: 'is_active', label: 'Status' },
                    ]"
                    :data="users"
                >
                    <template #cell-last_name="{ row }">
                        <div class="flex items-center gap-2">
                            <DetailLink :href="buildDetailUrl(show.url(row.id))" tabindex="-1">{{ row.formal_name }}</DetailLink>
                            <Badge v-if="isNewUser(row)" variant="primary" size="sm">New</Badge>
                        </div>
                    </template>
                    <template #cell-role="{ value }">
                        <Badge
                            :variant="
                                value === 'admin'
                                    ? 'red'
                                    : value === 'manager'
                                      ? 'blue'
                                      : value === 'inventory'
                                        ? 'emerald'
                                        : value === 'auditor'
                                          ? 'purple'
                                          : 'amber'
                            "
                        >
                            {{ value }}
                        </Badge>
                    </template>
                    <template #cell-is_active="{ value }">
                        <Badge :variant="value ? 'emerald' : 'red'">
                            {{ value ? 'Active' : 'Inactive' }}
                        </Badge>
                    </template>
                </DataTable>
            </div>
        </div>

        <UserFormModal :show="showModal" :user="selectedUser ?? undefined" @close="closeModal" />

        <Toast v-if="showToast" :message="toastMessage" type="success" @close="hideToast" />
    </AuthenticatedLayout>
</template>
