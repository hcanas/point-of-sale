<script setup lang="ts">
import BtnSecondary from '@/components/buttons/BtnSecondary.vue';
import FormInput from '@/components/forms/FormInput.vue';
import Modal from '@/components/ui/Modal.vue';
import { useFormatter } from '@/composables/useFormatter';
import { index as membersIndex } from '@/routes/api/members';
import type { Member } from '@/types/member';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { nextTick, ref, watch } from 'vue';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    close: [];
    select: [member: Member];
}>();

const { formatCurrency } = useFormatter();

const search = ref('');
const members = ref<Member[]>([]);
const isLoading = ref(false);
const focusedIndex = ref<number>(-1);
const rowRefs = ref<HTMLTableRowElement[]>([]);
const searchInputRef = ref<HTMLInputElement | null>(null);

const resetFocusState = () => {
    focusedIndex.value = -1;
    rowRefs.value = [];
};

const focusRow = (index: number) => {
    nextTick(() => {
        rowRefs.value[index]?.focus();
    });
};

const loadMembers = async (searchTerm = '') => {
    isLoading.value = true;
    try {
        const response = await axios.get(membersIndex.url(), {
            params: searchTerm ? { search: searchTerm } : {},
        });
        members.value = response.data;
    } catch (error) {
        console.error('Failed to load members:', error);
    } finally {
        isLoading.value = false;
    }
};

const selectMember = (member: Member) => {
    if (!member.is_active) {
        return;
    }
    emit('select', member);
    emit('close');
};

const close = () => emit('close');

const handleKeydown = (e: KeyboardEvent) => {
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        searchInputRef.value?.focus();
        return;
    }

    if (!members.value.length) return;

    const active = document.activeElement;
    const isInput = active instanceof HTMLInputElement || active instanceof HTMLTextAreaElement || active instanceof HTMLSelectElement;

    if (isInput && e.key !== 'ArrowDown') {
        return;
    }

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            if (isInput) {
                focusedIndex.value = 0;
            } else {
                focusedIndex.value = Math.min(focusedIndex.value + 1, members.value.length - 1);
            }
            focusRow(focusedIndex.value);
            break;
        case 'ArrowUp':
            e.preventDefault();
            focusedIndex.value = Math.max(focusedIndex.value - 1, 0);
            focusRow(focusedIndex.value);
            break;
        case 'Enter':
            if (focusedIndex.value >= 0 && members.value[focusedIndex.value]) {
                e.preventDefault();
                selectMember(members.value[focusedIndex.value]);
            }
            break;
        case 'Escape':
            close();
            break;
    }
};

watch(
    () => props.show,
    (show) => {
        if (show) {
            search.value = '';
            resetFocusState();
            loadMembers();
        } else {
            resetFocusState();
        }
    },
);

watch(
    members,
    () => {
        if (members.value.length === 0) {
            focusedIndex.value = -1;
            nextTick(() => {
                searchInputRef.value?.focus();
            });
        } else if (focusedIndex.value >= 0 && focusedIndex.value < members.value.length) {
            focusRow(focusedIndex.value);
        }
    },
    { deep: true },
);

const debouncedSearch = useDebounceFn((value: string) => {
    loadMembers(value);
}, 500);

watch(search, (value) => {
    debouncedSearch(value);
});
</script>

<template>
    <Modal :show="show" @close="close">
        <template #header>
            <div>
                <h3 class="text-lg font-semibold text-foreground">Member Lookup</h3>
                <p class="text-sm text-foreground-soft">Search for a member for this sale</p>
            </div>
        </template>

        <div class="space-y-4">
            <FormInput ref="searchInputRef" v-model="search" placeholder="Search members... (Ctrl+F)" variant="canvas" @keydown="handleKeydown" />

            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-foreground-soft">
                <span><kbd>↑/↓</kbd> Navigate Rows</span>
                <span><kbd>Enter</kbd> Select Member</span>
            </div>

            <div v-if="isLoading" class="text-center text-foreground-soft">Loading...</div>

            <div v-else-if="members.length === 0" class="text-center text-foreground-soft">No members found</div>

            <div v-else class="max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-divider text-left text-foreground-soft">
                        <tr>
                            <th class="pb-2">Member</th>
                            <th class="pb-2 text-right">Outstanding Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(member, index) in members"
                            :key="member.id"
                            :ref="
                                (el) => {
                                    if (el) rowRefs[index] = el as HTMLTableRowElement;
                                }
                            "
                            tabindex="0"
                            class="cursor-pointer border-b border-divider transition-colors outline-none"
                            :class="{
                                'bg-primary-50/60 ring-1 ring-primary-600/30 ring-inset dark:bg-primary-900/20':
                                    focusedIndex === index && member.is_active,
                                'cursor-not-allowed opacity-50': !member.is_active,
                            }"
                            @click="selectMember(member)"
                            @focus="focusedIndex = index"
                            @mouseenter="focusedIndex = index"
                            @keydown="handleKeydown"
                        >
                            <td class="py-2 font-medium text-foreground" :class="{ 'text-foreground-muted': !member.is_active }">
                                {{ member.formal_name }}
                            </td>
                            <td
                                class="py-2 text-right"
                                :class="{ 'text-foreground-muted': !member.is_active, 'text-danger': member.outstanding_balance > 0 }"
                            >
                                {{ formatCurrency(member.outstanding_balance) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <template #footer>
            <BtnSecondary type="button" @click="close">Cancel</BtnSecondary>
        </template>
    </Modal>
</template>
