<script setup lang="ts">
import { show } from '@/routes/users';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface UserLike {
    id: number;
    first_name: string;
    last_name: string;
}

const props = defineProps<{
    user: UserLike | null;
    returnTo?: string;
}>();

const page = usePage();
const canLink = computed(() => {
    const role = page.props.auth?.user?.role;
    return role === 'admin' || role === 'manager';
});
</script>

<template>
    <Link
        v-if="user && canLink"
        :href="show.url(user.id, props.returnTo ? { query: { return_to: props.returnTo } } : undefined)"
        class="text-primary-600 hover:text-primary-700 hover:underline"
    >
        {{ user.first_name }} {{ user.last_name }}
    </Link>
    <span v-else-if="user" class="text-foreground-soft"> {{ user.first_name }} {{ user.last_name }} </span>
</template>
