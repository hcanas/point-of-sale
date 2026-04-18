<script setup lang="ts">
import FormError from '@/components/forms/FormError.vue';
import FormInput from '@/components/forms/FormInput.vue';
import FormLabel from '@/components/forms/FormLabel.vue';
import FlashMessage from '@/components/ui/FlashMessage.vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { UserKey } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const page = usePage();
const usernameRef = ref<InstanceType<typeof FormInput> | null>(null);

const form = useForm({
    username: '',
    password: '',
});

const focusUsername = () => {
    usernameRef.value?.focus();
};

const submit = () => {
    form.post('/login', {
        onFinish: () => {
            form.reset('password');
            focusUsername();
        },
    });
};

onMounted(() => {
    focusUsername();
});
</script>

<template>
    <GuestLayout>
        <div class="p-8">
            <div class="mb-8 text-center">
                <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-primary-100 dark:bg-primary-900/30">
                    <UserKey class="h-8 w-8 text-primary-600 dark:text-primary-400" />
                </div>
                <h1 class="text-2xl font-semibold text-foreground">Welcome back</h1>
                <p class="mt-1 text-sm text-foreground-soft">Sign in to your POS account</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <FlashMessage :message="page.props.flash?.error" />

                <div>
                    <FormLabel for="username">Username</FormLabel>
                    <FormInput ref="usernameRef" id="username" v-model="form.username" type="text" :error="!!form.errors.username" />
                    <FormError :message="form.errors.username" />
                </div>

                <div>
                    <FormLabel for="password">Password</FormLabel>
                    <FormInput id="password" v-model="form.password" type="password" :error="!!form.errors.password" />
                    <FormError :message="form.errors.password" />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm outline-none hover:bg-primary-700 focus-visible:ring-primary-600 disabled:opacity-50"
                >
                    {{ form.processing ? 'Signing in...' : 'Sign in' }}
                </button>
            </form>
        </div>
    </GuestLayout>
</template>
