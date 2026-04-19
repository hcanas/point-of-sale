<script setup lang="ts">
import BtnPrimary from '@/components/buttons/BtnPrimary.vue';
import BtnSecondary from '@/components/buttons/BtnSecondary.vue';
import FormError from '@/components/forms/FormError.vue';
import FormInput from '@/components/forms/FormInput.vue';
import FormLabel from '@/components/forms/FormLabel.vue';
import FormNumberInput from '@/components/forms/FormNumberInput.vue';
import Modal from '@/components/ui/Modal.vue';
import { store, update } from '@/routes/api/members';
import type { Member } from '@/types/member';
import axios from 'axios';
import { reactive, ref, watch } from 'vue';

type MemberFormData = Pick<
    Member,
    'first_name' | 'middle_name' | 'last_name' | 'name_extension' | 'phone' | 'address' | 'tin_number' | 'share_capital' | 'is_active'
> & {
    id?: number;
};

const props = defineProps<{
    show: boolean;
    member?: MemberFormData;
}>();

const emit = defineEmits<{
    close: [member?: Member];
}>();

const defaultFormValues = (): MemberFormData => ({
    first_name: props.member?.first_name ?? '',
    middle_name: props.member?.middle_name ?? null,
    last_name: props.member?.last_name ?? '',
    name_extension: props.member?.name_extension ?? null,
    phone: props.member?.phone ?? null,
    address: props.member?.address ?? null,
    share_capital: props.member?.share_capital ?? 0,
    tin_number: props.member?.tin_number ?? null,
    is_active: props.member?.is_active ?? true,
});

const form = reactive<MemberFormData>(defaultFormValues());
const errors = ref<Record<string, string>>({});

const isEditing = () => props.member?.id !== undefined;

const resetForm = () => {
    Object.assign(form, defaultFormValues());
    errors.value = {};
};

watch(
    () => props.show,
    (isShown, wasShown) => {
        if (!isShown && wasShown) {
            resetForm();
        }
    },
);

const isProcessing = ref(false);

const handleSubmit = async () => {
    errors.value = {};
    isProcessing.value = true;

    try {
        if (isEditing() && props.member?.id) {
            const response = await axios.put(update.url(props.member.id), form);
            emit('close', response.data as Member);
        } else {
            const response = await axios.post(store.url(), form);
            emit('close', response.data as Member);
            resetForm();
        }
    } catch (error: any) {
        if (error.response?.status === 422) {
            const errorData = error.response.data.errors;
            for (const [field, messages] of Object.entries(errorData)) {
                errors.value[field] = (messages as string[])[0];
            }
        }
    } finally {
        isProcessing.value = false;
    }
};

const close = () => emit('close');
</script>

<template>
    <Modal :show="show" max-width="lg" @close="close">
        <template #header>
            <div>
                <h3 class="text-lg font-semibold text-foreground">{{ isEditing() ? 'Edit Member' : 'Add Member' }}</h3>
                <p class="text-sm text-foreground-soft">
                    {{ isEditing() ? 'Update member details below' : 'Enter member information to add a new member' }}
                </p>
            </div>
        </template>

        <form @submit.prevent="handleSubmit" class="space-y-4">
            <FormError :message="errors.full_name" />

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <FormLabel for="first_name" required>First Name</FormLabel>
                    <FormInput id="first_name" v-model="form.first_name" type="text" variant="canvas" required />
                    <FormError :message="errors.first_name" />
                </div>

                <div>
                    <FormLabel for="last_name" required>Last Name</FormLabel>
                    <FormInput id="last_name" v-model="form.last_name" type="text" variant="canvas" required />
                    <FormError :message="errors.last_name" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <FormLabel for="middle_name">Middle Name</FormLabel>
                    <FormInput id="middle_name" v-model="form.middle_name" type="text" variant="canvas" />
                    <FormError :message="errors.middle_name" />
                </div>

                <div>
                    <FormLabel for="name_extension">Name Extension</FormLabel>
                    <FormInput id="name_extension" v-model="form.name_extension" type="text" variant="canvas" placeholder="Jr., Sr., III, etc." />
                    <FormError :message="errors.name_extension" />
                </div>
            </div>

            <div>
                <FormLabel for="address">Address</FormLabel>
                <FormInput id="address" v-model="form.address" type="text" variant="canvas" />
                <FormError :message="errors.address" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <FormLabel for="phone">Phone Number</FormLabel>
                    <FormInput id="phone" v-model="form.phone" type="text" variant="canvas" />
                    <FormError :message="errors.phone" />
                </div>
                <div>
                    <FormLabel for="tin_number">TIN Number</FormLabel>
                    <FormInput id="tin_number" v-model="form.tin_number" type="text" variant="canvas" />
                    <FormError :message="errors.tin_number" />
                </div>
            </div>

            <div>
                <FormLabel for="share_capital" required>Share Capital</FormLabel>
                <FormNumberInput id="share_capital" v-model="form.share_capital" :min="0" :step="0.01" variant="canvas" required />
                <FormError :message="errors.share_capital" />
            </div>

            <div class="flex items-start gap-2">
                <input id="is_active" v-model="form.is_active" type="checkbox" class="mt-0.5 rounded border-gray-300 dark:border-gray-600" />
                <div class="flex flex-col">
                    <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</label>
                    <span class="text-xs text-gray-500">Inactive members won't be available for transactions</span>
                </div>
            </div>
        </form>

        <template #footer>
            <BtnSecondary type="button" @click="close" :disabled="isProcessing">Cancel</BtnSecondary>
            <BtnPrimary type="submit" @click="handleSubmit" :disabled="isProcessing">
                {{ isProcessing ? 'Saving...' : isEditing() ? 'Update' : 'Create' }}
            </BtnPrimary>
        </template>
    </Modal>
</template>
