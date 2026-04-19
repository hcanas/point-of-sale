<script setup lang="ts">
import BtnDanger from '@/components/buttons/BtnDanger.vue';
import BtnDangerOutline from '@/components/buttons/BtnDangerOutline.vue';
import BtnSecondary from '@/components/buttons/BtnSecondary.vue';
import ReturnLink from '@/components/links/ReturnLink.vue';
import UserNameLink from '@/components/links/UserNameLink.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import Toast from '@/components/ui/Toast.vue';
import { useFormatter } from '@/composables/useFormatter';
import { useKeybinds } from '@/composables/useKeybinds';
import { useQueryStrings } from '@/composables/useQueryStrings';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import MemberFormModal from '@/pages/Members/Partials/MemberFormModal.vue';
import MemberLedgers from '@/pages/Members/Partials/MemberLedgers.vue';
import { destroy, index } from '@/routes/members';
import type { Member, MemberLedger } from '@/types/member';
import type { PaginatedData } from '@/types/pagination';
import { router, usePage } from '@inertiajs/vue3';
import { Pencil, Trash2, User } from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';

const props = defineProps<{
    member: Member;
    ledgers: PaginatedData<MemberLedger>;
}>();

const page = usePage();
const currentUrl = computed(() => page.url);
const showToast = ref(false);
const toastType = ref<'success' | 'error' | 'info'>('info');
const toastMessage = ref('');

const closeToast = () => {
    showToast.value = false;
};

const { getReturnUrl } = useQueryStrings();
const { formatCurrency, formatDateTime } = useFormatter();

const showModal = ref(false);
const showDeletePanel = ref(false);
const deleteConfirmation = ref('');
const deleteInputRef = ref<HTMLInputElement | null>(null);
const memberLedgersRef = ref<any>(null);

const openEditModal = () => {
    showModal.value = true;
};

const closeModal = (member?: Member) => {
    showModal.value = false;

    if (member) {
        router.reload({ only: ['member'] });
        showToast.value = true;
        toastType.value = 'success';
        toastMessage.value = 'Member updated successfully';
    }
};

const openDeletePanel = () => {
    showDeletePanel.value = true;
    deleteConfirmation.value = '';
    nextTick(() => deleteInputRef.value?.focus());
};

const closeDeletePanel = () => {
    showDeletePanel.value = false;
    deleteConfirmation.value = '';
};

const memberName = computed(() => props.member.full_name || `${props.member.first_name} ${props.member.last_name}`);

const focusLedgerSearch = () => {
    memberLedgersRef.value?.focusSearch();
};

const deleteMember = () => {
    router.delete(destroy.url(props.member.id));
};

useKeybinds([
    { key: 'e', ctrl: true, handler: openEditModal },
    { key: 'f', ctrl: true, handler: focusLedgerSearch },
    { key: 'ArrowLeft', alt: true, handler: () => router.visit(getReturnUrl('return_to', index.url())) },
]);
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <ReturnLink fallback="/members" />
            </div>

            <PageHeader :title="memberName">
                <BtnSecondary keybind="Ctrl+E" @click="openEditModal">
                    <Pencil class="h-4 w-4" />
                    Edit
                </BtnSecondary>
            </PageHeader>

            <div class="rounded-xl border border-divider bg-surface p-6 shadow-sm">
                <div class="flex flex-wrap items-center gap-8">
                    <div class="flex min-w-[240px] flex-1 items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-canvas">
                            <User class="h-6 w-6 text-foreground-soft" />
                        </div>
                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-semibold text-foreground">{{ memberName }}</h2>
                            <div class="mt-1.5 flex items-center gap-2">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium',
                                        member.is_active ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger',
                                    ]"
                                >
                                    <span :class="['h-1.5 w-1.5 rounded-full', member.is_active ? 'bg-success' : 'bg-danger']"></span>
                                    {{ member.is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium',
                                        member.balance > 0 ? 'bg-warning/10 text-warning' : 'bg-success/10 text-success',
                                    ]"
                                >
                                    <span :class="['h-1.5 w-1.5 rounded-full', member.balance > 0 ? 'bg-warning' : 'bg-success']"></span>
                                    {{ member.balance > 0 ? 'Has Balance' : 'No Balance' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="hidden h-14 w-px bg-divider sm:block"></div>

                    <div class="min-w-[140px]">
                        <p class="text-sm font-medium text-foreground-soft">Balance</p>
                        <p :class="['mt-1 text-xl font-bold tabular-nums', member.balance > 0 ? 'text-danger' : 'text-foreground']">
                            {{ formatCurrency(member.balance) }}
                        </p>
                    </div>

                    <div class="hidden h-14 w-px bg-divider sm:block"></div>

                    <div class="min-w-[140px]">
                        <p class="text-sm font-medium text-foreground-soft">Share Capital</p>
                        <p class="mt-1 text-xl font-bold text-foreground tabular-nums">
                            {{ formatCurrency(member.share_capital) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-divider bg-surface p-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <span class="text-xs font-medium text-foreground-soft">Member ID</span>
                        <p class="mt-1 text-lg font-semibold text-foreground">{{ member.id }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-foreground-soft">Phone</span>
                        <p class="mt-1 text-lg font-semibold text-foreground">{{ member.phone ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-foreground-soft">TIN</span>
                        <p class="mt-1 text-lg font-semibold text-foreground">{{ member.tin_number ?? '—' }}</p>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <span class="text-xs font-medium text-foreground-soft">Address</span>
                        <p class="mt-1 text-lg font-semibold text-foreground">{{ member.address ?? '—' }}</p>
                    </div>
                    <div v-if="member.creator">
                        <span class="text-xs font-medium text-foreground-soft">Created</span>
                        <p class="mt-1 text-sm font-medium text-foreground">{{ formatDateTime(member.created_at) }}</p>
                        <p class="text-xs text-foreground-soft">by <UserNameLink :user="member.creator" :return-to="currentUrl" /></p>
                    </div>
                    <div v-if="member.updater">
                        <span class="text-xs font-medium text-foreground-soft">Updated</span>
                        <p class="mt-1 text-sm font-medium text-foreground">{{ formatDateTime(member.updated_at) }}</p>
                        <p class="text-xs text-foreground-soft">by <UserNameLink :user="member.updater" :return-to="currentUrl" /></p>
                    </div>
                </div>
            </div>

            <MemberLedgers ref="memberLedgersRef" :ledgers="ledgers" :current-url="currentUrl" />

            <div class="rounded-lg border bg-surface" :class="member.deletable ? 'border-danger' : 'border-divider'">
                <div class="p-6">
                    <div class="flex items-start gap-3">
                        <div class="rounded p-2" :class="member.deletable ? 'bg-danger/10' : 'bg-foreground-soft/10'">
                            <Trash2 class="h-5 w-5" :class="member.deletable ? 'text-danger' : 'text-foreground-soft'" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-foreground">Delete this member</h3>
                            <p class="mt-1 text-sm text-foreground-soft">
                                <template v-if="!member.deletable"> This member has related records and cannot be deleted. </template>
                                <template v-else>
                                    Deletion is only allowed for members with no related records (transactions, ledgers). Members with history cannot
                                    be deleted.
                                </template>
                            </p>
                            <BtnDangerOutline v-if="!showDeletePanel && member.deletable" class="mt-4" @click="openDeletePanel">
                                Delete this member
                            </BtnDangerOutline>
                        </div>
                    </div>

                    <div v-if="showDeletePanel" class="border-border mt-6 border-t pt-6">
                        <div class="rounded-lg border border-warning bg-warning/10 p-4">
                            <p class="text-sm">
                                <span class="font-medium text-warning">Warning:</span>
                                <span class="text-foreground-soft">
                                    This will permanently delete
                                    <strong class="text-foreground">"{{ memberName }}"</strong>. If any related records exist, deletion will be
                                    blocked.
                                </span>
                            </p>
                        </div>

                        <form @submit.prevent="deleteMember" class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground">
                                    To confirm, type
                                    <strong class="text-danger">"{{ memberName }}"</strong>
                                    below:
                                </label>
                                <input
                                    ref="deleteInputRef"
                                    v-model="deleteConfirmation"
                                    type="text"
                                    class="mt-2 block w-full rounded-md border border-divider bg-canvas px-3 py-2 text-sm text-foreground placeholder:text-foreground-muted focus:ring-danger"
                                    placeholder="Type member name to confirm"
                                />
                            </div>

                            <div class="flex items-center gap-3">
                                <BtnSecondary type="button" @click="closeDeletePanel">Cancel</BtnSecondary>
                                <BtnDanger type="submit" :disabled="deleteConfirmation !== memberName"> I understand, delete this member </BtnDanger>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <MemberFormModal :show="showModal" :member="member" @close="closeModal" />

            <Toast v-if="showToast" :message="toastMessage" :type="toastType" @close="closeToast" />
        </div>
    </AuthenticatedLayout>
</template>
