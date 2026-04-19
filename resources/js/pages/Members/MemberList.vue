<script setup lang="ts">
import BtnPrimary from '@/components/buttons/BtnPrimary.vue';
import FormInput from '@/components/forms/FormInput.vue';
import DetailLink from '@/components/links/DetailLink.vue';
import DataTable from '@/components/tables/DataTable.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Toast from '@/components/ui/Toast.vue';
import { useFormatter } from '@/composables/useFormatter';
import { useKeybinds } from '@/composables/useKeybinds';
import { useQueryStrings } from '@/composables/useQueryStrings';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { show } from '@/routes/members';
import type { Member } from '@/types/member';
import type { PaginationData } from '@/types/pagination';
import { usePage } from '@inertiajs/vue3';
import { Plus, Search, Upload } from 'lucide-vue-next';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import MemberCsvImportModal from './Partials/MemberCsvImportModal.vue';
import MemberFormModal from './Partials/MemberFormModal.vue';

const props = defineProps<{
    members: PaginationData;
}>();

const page = usePage();
const { buildDetailUrl, get, set } = useQueryStrings();
const { formatCurrency } = useFormatter();

const search = computed({
    get: () => get('search') ?? '',
    set: (val) => set('search', val || undefined),
});

const showToast = ref(false);
const toastMessage = ref('');

const hideToast = () => {
    showToast.value = false;
};

const searchInputRef = ref<InstanceType<typeof FormInput> | null>(null);
const showModal = ref(false);
const showImportModal = ref(false);

const isNewMember = (member: Member) => {
    if (!member.created_at) return false;
    const createdAt = new Date(member.created_at);
    const now = new Date();
    const hoursDiff = (now.getTime() - createdAt.getTime()) / (1000 * 60 * 60);
    return hoursDiff < 24;
};

onMounted(() => {
    nextTick(() => searchInputRef.value?.focus());
});

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

useKeybinds([
    { key: 'f', ctrl: true, handler: () => searchInputRef.value?.focus() },
    { key: 'n', ctrl: true, handler: () => openModal() },
]);

const openModal = () => {
    (document.activeElement as HTMLElement)?.blur();
    showModal.value = true;
};

const closeModal = (member?: Member) => {
    showModal.value = false;

    if (member) {
        props.members.data.unshift(member);
        props.members.total++;
        if (props.members.from !== null) props.members.from = Math.min(props.members.from, 1);
        if (props.members.to !== null) props.members.to++;

        toastMessage.value = 'Member created successfully.';
        showToast.value = true;
    }

    nextTick(() => searchInputRef.value?.focus());
};

const openImportModal = () => {
    showImportModal.value = true;
};

const closeImportModal = () => {
    showImportModal.value = false;
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <PageHeader title="Members">
                <div class="relative w-64">
                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-foreground-soft" />
                    <FormInput
                        ref="searchInputRef"
                        v-model="search"
                        type="text"
                        placeholder="Search members..."
                        variant="surface"
                        keybind="Ctrl+F"
                        class="pl-10"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <BtnPrimary keybind="Ctrl+N" @click="openModal()">
                        <Plus class="h-4 w-4" />
                        Add Member
                    </BtnPrimary>
                    <button
                        class="rounded-md border border-divider bg-surface px-3 py-2 text-sm font-medium text-foreground hover:bg-hover"
                        @click="openImportModal"
                    >
                        <Upload class="mr-1 inline h-4 w-4" />
                        Import from CSV
                    </button>
                </div>
            </PageHeader>

            <div class="flex items-center gap-4 text-xs text-foreground-soft">
                <span
                    ><kbd class="rounded border border-divider bg-canvas px-1.5 py-0.5 font-mono text-foreground">↑</kbd
                    ><kbd class="rounded border border-divider bg-canvas px-1.5 py-0.5 font-mono text-foreground">↓</kbd>: Navigate rows</span
                >
                <span
                    ><kbd class="rounded border border-divider bg-canvas px-1.5 py-0.5 font-mono text-foreground">Ctrl</kbd>+<kbd
                        class="rounded border border-divider bg-canvas px-1.5 py-0.5 font-mono text-foreground"
                        >←</kbd
                    >/<kbd class="rounded border border-divider bg-canvas px-1.5 py-0.5 font-mono text-foreground">→</kbd>: Navigate pages</span
                >
            </div>

            <div
                class="rounded-lg bg-surface p-6 shadow-[inset_0px_0px_0px_1px_rgba(30,41,59,0.16)] dark:shadow-[inset_0px_0px_0px_1px_rgba(148,163,184,0.2)]"
            >
                <DataTable
                    :columns="[
                        { key: 'id', label: 'ID' },
                        { key: 'last_name', label: 'Name', sortable: true, width: 'fill' },
                        { key: 'balance', label: 'Balance', align: 'right', sortable: true },
                        { key: 'is_active', label: 'Status' },
                    ]"
                    :data="members"
                >
                    <template #cell-id="{ row }">
                        <span class="text-foreground-soft">{{ row.id }}</span>
                    </template>
                    <template #cell-last_name="{ row }">
                        <div class="flex items-center gap-2">
                            <DetailLink :href="buildDetailUrl(show.url(row.id))" tabindex="-1">{{ row.formal_name }}</DetailLink>
                            <span
                                v-if="isNewMember(row)"
                                class="inline-flex rounded-full bg-primary-100 px-1.5 py-0.5 text-[10px] font-semibold tracking-wider text-primary-700 uppercase dark:bg-primary-900/30 dark:text-primary-400"
                                >New</span
                            >
                        </div>
                    </template>
                    <template #cell-balance="{ value }">
                        <span
                            :class="[
                                'text-foreground',
                                value > 0 ? 'text-red-600 dark:text-red-400' : value < 0 ? 'text-amber-600 dark:text-amber-400' : '',
                            ]"
                        >
                            {{ formatCurrency(value) }}
                        </span>
                    </template>
                    <template #cell-is_active="{ value }">
                        <span
                            :class="[
                                'inline-flex rounded-full px-2 py-1 text-xs font-medium',
                                value
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                    : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            ]"
                        >
                            {{ value ? 'Active' : 'Inactive' }}
                        </span>
                    </template>
                </DataTable>
            </div>

            <Toast v-if="showToast" :message="toastMessage" type="success" @close="hideToast" />
            <MemberCsvImportModal :show="showImportModal" @close="closeImportModal" @imported="closeImportModal" />
            <MemberFormModal :show="showModal" @close="closeModal" />
        </div>
    </AuthenticatedLayout>
</template>
