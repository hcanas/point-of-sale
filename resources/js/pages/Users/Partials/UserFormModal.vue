<script setup lang="ts">
import BtnPrimary from '@/components/buttons/BtnPrimary.vue';
import BtnSecondary from '@/components/buttons/BtnSecondary.vue';
import FormError from '@/components/forms/FormError.vue';
import FormInput from '@/components/forms/FormInput.vue';
import FormLabel from '@/components/forms/FormLabel.vue';
import Modal from '@/components/ui/Modal.vue';
import { store, update } from '@/routes/api/users';
import type { User } from '@/types';
import axios from 'axios';
import { reactive, ref, watch } from 'vue';

type UserFormData = Pick<User, 'first_name' | 'middle_name' | 'last_name' | 'name_extension' | 'username' | 'role' | 'is_active'> & {
    id?: number;
    password?: string;
    password_confirmation?: string;
};

const props = defineProps<{
    show: boolean;
    user?: UserFormData;
}>();

const emit = defineEmits<{
    close: [user?: User];
}>();

const defaultFormValues = (): UserFormData => ({
    first_name: props.user?.first_name ?? '',
    middle_name: props.user?.middle_name ?? '',
    last_name: props.user?.last_name ?? '',
    name_extension: props.user?.name_extension ?? '',
    username: props.user?.username ?? '',
    password: '',
    password_confirmation: '',
    role: props.user?.role ?? 'cashier',
    is_active: props.user?.is_active ?? true,
});

const form = reactive<UserFormData>(defaultFormValues());
const errors = ref<Record<string, string>>({});

const isEditing = () => props.user?.id !== undefined;

const resetForm = () => {
    Object.assign(form, defaultFormValues());
    errors.value = {};
};

watch(
    () => props.show,
    (isShown, wasShown) => {
        if (isShown && !wasShown) {
            Object.assign(form, defaultFormValues());
        }
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
        if (isEditing() && props.user?.id) {
            const response = await axios.put(update.url(props.user.id), form);
            emit('close', response.data as User);
        } else {
            const response = await axios.post(store.url(), form);
            emit('close', response.data as User);
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
                <h3 class="text-lg font-semibold text-foreground">{{ isEditing() ? 'Edit User' : 'Add User' }}</h3>
                <p class="text-sm text-foreground-soft">
                    {{ isEditing() ? 'Update user details below' : 'Enter user information to add a new user' }}
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
                    <FormInput id="name_extension" v-model="form.name_extension" type="text" variant="canvas" />
                    <FormError :message="errors.name_extension" />
                </div>
            </div>

            <div>
                <FormLabel for="username" required>Username</FormLabel>
                <FormInput id="username" v-model="form.username" type="text" variant="canvas" required />
                <FormError :message="errors.username" />
            </div>

            <div>
                <FormLabel for="password" :required="!isEditing()">
                    {{ isEditing() ? 'New Password (leave blank to keep current)' : 'Password' }}
                </FormLabel>
                <FormInput id="password" v-model="form.password" type="password" variant="canvas" :required="!isEditing()" />
                <FormError :message="errors.password" />
            </div>

            <div>
                <FormLabel for="password_confirmation" :required="!isEditing()">Confirm Password</FormLabel>
                <FormInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    variant="canvas"
                    :required="!isEditing()"
                />
                <FormError :message="errors.password_confirmation" />
            </div>

            <div>
                <FormLabel for="role">Role</FormLabel>
                <select
                    id="role"
                    v-model="form.role"
                    class="block h-9 w-full rounded-md border border-divider bg-canvas px-3 text-sm text-foreground outline-none focus-visible:ring-2 focus-visible:ring-primary-600"
                >
                    <option value="manager">Manager</option>
                    <option value="inventory">Inventory</option>
                    <option value="auditor">Auditor</option>
                    <option value="cashier">Cashier</option>
                </select>
                <FormError :message="errors.role" />
            </div>

            <div class="flex items-start gap-2">
                <input
                    id="is_active"
                    v-model="form.is_active"
                    type="checkbox"
                    class="mt-0.5 h-4 w-4 rounded border-divider text-primary-600 focus-visible:ring-2 focus-visible:ring-primary-600"
                />
                <div class="flex flex-col">
                    <FormLabel for="is_active" class="!mt-0">Active</FormLabel>
                    <span class="text-xs text-foreground-soft">Inactive users won't be able to access the system</span>
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
