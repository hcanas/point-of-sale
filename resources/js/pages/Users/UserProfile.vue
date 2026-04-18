<script setup lang="ts">
import BtnDanger from '@/components/buttons/BtnDanger.vue';
import BtnDangerOutline from '@/components/buttons/BtnDangerOutline.vue';
import BtnSecondary from '@/components/buttons/BtnSecondary.vue';
import ReturnLink from '@/components/links/ReturnLink.vue';
import UserNameLink from '@/components/links/UserNameLink.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { useFormatter } from '@/composables/useFormatter';
import { useKeybinds } from '@/composables/useKeybinds';
import { useQueryStrings } from '@/composables/useQueryStrings';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import UserFormModal from '@/pages/Users/Partials/UserFormModal.vue';
import { destroy, index } from '@/routes/users';
import type { User } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { Pencil, Trash2, User as UserIcon } from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';

const props = defineProps<{
    user: User;
}>();

const showModal = ref(false);
const showDeletePanel = ref(false);
const deleteConfirmation = ref('');
const deleteInputRef = ref<HTMLInputElement | null>(null);

const { formatDateTime } = useFormatter();
const { getReturnUrl } = useQueryStrings();
const page = usePage();
const currentUrl = computed(() => page.url);

const openEditModal = () => {
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
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

const deleteUser = () => {
    router.delete(destroy.url(props.user.id));
};

useKeybinds([
    { key: 'e', ctrl: true, handler: openEditModal },
    { key: 'ArrowLeft', alt: true, handler: () => router.visit(getReturnUrl('return_to', index.url())) },
]);
</script>

<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <ReturnLink :fallback="index.url()">Back</ReturnLink>
            </div>

            <PageHeader :title="`${user.first_name} ${user.last_name}`">
                <BtnSecondary keybind="Ctrl+E" @click="openEditModal">
                    <Pencil class="h-4 w-4" />
                    Edit User
                </BtnSecondary>
            </PageHeader>

            <div class="rounded-xl border border-divider bg-surface p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-canvas">
                        <UserIcon class="h-6 w-6 text-foreground-soft" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="truncate text-lg font-semibold text-foreground">{{ user.first_name }} {{ user.last_name }}</h2>
                        <div class="mt-1.5 flex items-center gap-2">
                            <span
                                :class="[
                                    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium',
                                    user.is_active ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger',
                                ]"
                            >
                                <span :class="['h-1.5 w-1.5 rounded-full', user.is_active ? 'bg-success' : 'bg-danger']"></span>
                                {{ user.is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-canvas px-2.5 py-1 text-xs font-medium text-foreground-soft"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-primary-500"></span>
                                {{ user.role }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-divider bg-surface p-6">
                <h3 class="text-base font-semibold text-foreground">User Details</h3>
                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-foreground-soft">User ID</dt>
                        <dd class="mt-1 text-sm font-semibold text-foreground">{{ user.id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-foreground-soft">Username</dt>
                        <dd class="mt-1 font-mono text-sm font-semibold text-foreground">{{ user.username }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-foreground-soft">First Name</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ user.first_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-foreground-soft">Last Name</dt>
                        <dd class="mt-1 text-sm text-foreground">{{ user.last_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-foreground-soft">Middle Name</dt>
                        <dd class="mt-1 text-sm text-foreground">
                            <span v-if="user.middle_name">{{ user.middle_name }}</span>
                            <span v-else class="text-foreground-muted italic">—</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-foreground-soft">Name Extension</dt>
                        <dd class="mt-1 text-sm text-foreground">
                            <span v-if="user.name_extension">{{ user.name_extension }}</span>
                            <span v-else class="text-foreground-muted italic">—</span>
                        </dd>
                    </div>
                    <div v-if="user.creator">
                        <dt class="text-sm font-medium text-foreground-soft">Created</dt>
                        <dd class="mt-1 text-sm font-semibold text-foreground">{{ formatDateTime(user.created_at) }}</dd>
                        <dd class="text-xs text-foreground-soft">by <UserNameLink :user="user.creator" :return-to="currentUrl" /></dd>
                    </div>
                    <div v-if="user.updater">
                        <dt class="text-sm font-medium text-foreground-soft">Updated</dt>
                        <dd class="mt-1 text-sm font-semibold text-foreground">{{ formatDateTime(user.updated_at) }}</dd>
                        <dd class="text-xs text-foreground-soft">by <UserNameLink :user="user.updater" :return-to="currentUrl" /></dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border bg-surface" :class="user.deletable ? 'border-danger' : 'border-divider'">
                <div class="p-6">
                    <div class="flex items-start gap-3">
                        <div class="rounded p-2" :class="user.deletable ? 'bg-danger/10' : 'bg-foreground-soft/10'">
                            <Trash2 class="h-5 w-5" :class="user.deletable ? 'text-danger' : 'text-foreground-soft'" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-foreground">Delete this user</h3>
                            <p class="mt-1 text-sm text-foreground-soft">
                                <template v-if="user.deletable">
                                    This will permanently remove {{ user.first_name }} {{ user.last_name }} from the system.
                                </template>
                                <template v-else> You cannot delete your own account. </template>
                            </p>
                            <BtnDangerOutline v-if="!showDeletePanel && user.deletable" class="mt-4" @click="openDeletePanel">
                                Delete User
                            </BtnDangerOutline>
                        </div>
                    </div>

                    <div v-if="showDeletePanel" class="mt-6 border-t border-divider pt-6">
                        <div class="rounded-lg border border-warning bg-warning/10 p-4">
                            <p class="text-sm">
                                <span class="font-medium text-warning">Warning:</span>
                                <span class="text-foreground-soft">
                                    This will permanently remove
                                    <strong class="text-foreground">{{ user.first_name }} {{ user.last_name }}</strong> from the system.
                                </span>
                            </p>
                        </div>

                        <form @submit.prevent="deleteUser" class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground">
                                    To confirm, type
                                    <strong class="text-danger">"{{ user.username }}"</strong>
                                    below:
                                </label>
                                <input
                                    ref="deleteInputRef"
                                    v-model="deleteConfirmation"
                                    type="text"
                                    class="mt-2 block w-full rounded-md border border-divider bg-canvas px-3 py-2 text-sm text-foreground placeholder:text-foreground-muted focus:ring-danger"
                                    placeholder="Type username to confirm"
                                />
                            </div>

                            <div class="flex items-center gap-3">
                                <BtnSecondary type="button" @click="closeDeletePanel">Cancel</BtnSecondary>
                                <BtnDanger type="submit" :disabled="deleteConfirmation !== user.username"> I understand, delete this user </BtnDanger>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <UserFormModal :show="showModal" :user="user" @close="closeModal" />
    </AuthenticatedLayout>
</template>
