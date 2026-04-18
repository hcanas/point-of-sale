<script setup lang="ts">
import { useQueryStrings } from '@/composables/useQueryStrings';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

interface Props {
    href?: string;
    fallback: string;
    queryParam?: string;
}

const props = withDefaults(defineProps<Props>(), {
    queryParam: 'return_to',
});

const { getReturnUrl } = useQueryStrings();

const destination = getReturnUrl(props.queryParam, props.fallback);
</script>

<template>
    <Link
        :href="destination"
        class="inline-flex items-center gap-2 rounded text-sm text-foreground-soft outline-none hover:text-foreground"
        :title="`Alt+Left to go back`"
    >
        <ArrowLeft class="h-4 w-4" />
        <slot>Back</slot>
    </Link>
</template>
