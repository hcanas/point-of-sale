<script setup lang="ts">
interface Option {
    value: string;
    label: string;
}

interface Props {
    id?: string;
    modelValue: string;
    options: Option[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const handleChange = (e: Event) => {
    emit('update:modelValue', (e.target as HTMLSelectElement).value);
};
</script>

<template>
    <select
        :id="id"
        :value="modelValue"
        @change="handleChange"
        class="w-full rounded-md border border-divider bg-surface px-3 py-2 text-sm text-foreground"
    >
        <option v-for="option in options" :key="option.value" :value="option.value">
            {{ option.label }}
        </option>
    </select>
</template>
