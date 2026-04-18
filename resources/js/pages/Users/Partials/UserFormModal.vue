<script setup lang="ts">
import BtnPrimary from '@/components/buttons/BtnPrimary.vue';
import FormError from '@/components/forms/FormError.vue';
import FormInput from '@/components/forms/FormInput.vue';
import FormLabel from '@/components/forms/FormLabel.vue';
import Modal from '@/components/ui/Modal.vue';
import { store, update } from '@/routes/users';
import { router } from '@inertiajs/vue3';
import { Save, X } from 'lucide-vue-next';
import { reactive, watch } from 'vue';

interface User {
    id?: number;
    first_name: string;
    last_name: string;
    username: string;
    role: string;
    is_active: boolean;
}

const props = defineProps<{
    show: boolean;
    user?: User | null;
}>();

const emit = defineEmits<{
    close: [];
}>();

const form = reactive({
    first_name: '',
    last_name: '',
    username: '',
    password: '',
    password_confirmation: '',
    role: 'cashier',
    is_active: true,
});

const errors = reactive<Record<string, string>>({});

const isEditing = () => props.user?.id !== undefined;

const resetForm = () => {
    form.first_name = '';
    form.last_name = '';
    form.username = '';
    form.password = '';
    form.password_confirmation = '';
    form.role = 'cashier';
    form.is_active = true;
    Object.keys(errors).forEach((key) => delete errors[key]);
};

watch(
    () => props.show,
    (show) => {
        if (show) {
            if (props.user) {
                form.first_name = props.user.first_name;
                form.last_name = props.user.last_name;
                form.username = props.user.username;
                form.role = props.user.role;
                form.is_active = props.user.is_active;
                form.password = '';
                form.password_confirmation = '';
            } else {
                resetForm();
            }
        }
    },
    { immediate: true },
);

const handleSubmit = () => {
    Object.keys(errors).forEach((key) => delete errors[key]);

    const onError = (err: Record<string, string>) => {
        Object.assign(errors, err);
    };

    const onSuccess = () => {
        emit('close');
        resetForm();
    };

    if (isEditing() && props.user?.id) {
        router.put(update.url(props.user.id), form, {
            onError,
            onSuccess,
        });
    } else {
        router.post(store.url(), form, {
            onError,
            onSuccess,
        });
    }
};

const handleClose = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" :title="isEditing() ? 'Edit User' : 'Add User'" @close="handleClose">
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <FormLabel for="first_name" required>First Name</FormLabel>
                    <FormInput id="first_name" v-model="form.first_name" type="text" variant="canvas" required />
                    <FormError v-if="errors.first_name">{{ errors.first_name }}</FormError>
                </div>
                <div>
                    <FormLabel for="last_name" required>Last Name</FormLabel>
                    <FormInput id="last_name" v-model="form.last_name" type="text" variant="canvas" required />
                    <FormError v-if="errors.last_name">{{ errors.last_name }}</FormError>
                </div>
            </div>

            <div>
                <FormLabel for="username" required>Username</FormLabel>
                <FormInput id="username" v-model="form.username" type="text" variant="canvas" required />
                <FormError v-if="errors.username">{{ errors.username }}</FormError>
            </div>

            <div>
                <FormLabel for="role">Role</FormLabel>
                <select
                    id="role"
                    v-model="form.role"
                    class="block h-9 w-full rounded-md border border-divider bg-canvas px-3 text-sm text-foreground outline-none focus-visible:ring-primary-600"
                >
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="inventory">Inventory</option>
                    <option value="auditor">Auditor</option>
                    <option value="cashier">Cashier</option>
                </select>
                <FormError v-if="errors.role">{{ errors.role }}</FormError>
            </div>

            <div>
                <FormLabel for="password">
                    {{ isEditing() ? 'New Password (leave blank to keep current)' : 'Password' }}
                </FormLabel>
                <FormInput id="password" v-model="form.password" type="password" variant="canvas" />
                <FormError v-if="errors.password">{{ errors.password }}</FormError>
            </div>

            <div v-if="!isEditing() || form.password">
                <FormLabel for="password_confirmation" :required="!isEditing()">Confirm Password</FormLabel>
                <FormInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    variant="canvas"
                    :required="!isEditing()"
                />
                <FormError v-if="errors.password_confirmation">{{ errors.password_confirmation }}</FormError>
            </div>

            <div class="flex items-center gap-2">
                <input
                    id="is_active"
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 rounded border-divider text-primary-600 focus:ring-primary-600"
                />
                <FormLabel for="is_active" class="!mt-0">Active</FormLabel>
            </div>
        </form>

        <template #footer>
            <button
                type="button"
                class="inline-flex h-9 items-center gap-2 rounded-md border border-divider bg-surface px-4 text-sm font-medium text-foreground hover:bg-hover"
                @click="handleClose"
            >
                <X class="h-4 w-4" />
                Cancel
            </button>
            <BtnPrimary type="button" @click="handleSubmit">
                <Save class="h-4 w-4" />
                {{ isEditing() ? 'Update' : 'Create' }}
            </BtnPrimary>
        </template>
    </Modal>
</template>
