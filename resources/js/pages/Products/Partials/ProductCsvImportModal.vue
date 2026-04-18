<script setup lang="ts">
import BtnPrimary from '@/components/buttons/BtnPrimary.vue';
import BtnSecondary from '@/components/buttons/BtnSecondary.vue';
import FormError from '@/components/forms/FormError.vue';
import Modal from '@/components/ui/Modal.vue';
import { importCsv } from '@/routes/products';
import { router } from '@inertiajs/vue3';
import { Upload } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    close: [];
    imported: [];
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const isProcessing = ref(false);
const errors = ref<Record<string, string>>({});
const preview = ref<Array<{ barcode?: string; name: string; price: number; stock: number }>>([]);

const hasFile = computed(() => selectedFile.value !== null);

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        selectedFile.value = file;
        errors.value = {};
        parsePreview(file);
    }
};

const parsePreview = (file: File) => {
    const reader = new FileReader();
    reader.onload = (e) => {
        const text = e.target?.result as string;
        const lines = text.split('\n').filter((line) => line.trim());

        const dataLines = lines.slice(1);

        preview.value = dataLines.slice(0, 5).map((line) => {
            const cols = line.split(',').map((c) => c.trim());
            return {
                barcode: cols[0] || undefined,
                name: cols[1] || '',
                price: parseFloat(cols[2]) || 0,
                stock: parseInt(cols[3]) || 0,
            };
        });
    };
    reader.readAsText(file);
};

const clearFile = () => {
    selectedFile.value = null;
    preview.value = [];
    errors.value = {};
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const close = () => {
    clearFile();
    emit('close');
};

const handleSubmit = () => {
    if (!selectedFile.value) {
        errors.value.file = 'Please select a CSV file';
        return;
    }

    isProcessing.value = true;
    errors.value = {};

    const formData = new FormData();
    formData.append('csv_file', selectedFile.value);

    router.post(importCsv.url(), formData, {
        onSuccess: () => {
            isProcessing.value = false;
            emit('imported');
            close();
        },
        onError: (err) => {
            isProcessing.value = false;
            errors.value = err as Record<string, string>;
        },
    });
};

const downloadTemplate = () => {
    const csv = 'barcode,name,price,stock\nABC123,Sample Product,19.99,100';
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'products_template.csv';
    a.click();
    URL.revokeObjectURL(url);
};
</script>

<template>
    <Modal :show="show" @close="close">
        <template #header>
            <div>
                <h3 class="text-lg font-semibold text-foreground">Bulk Import Products</h3>
                <p class="text-sm text-foreground-soft">Add multiple products at once using a spreadsheet</p>
            </div>
        </template>

        <div class="space-y-4">
            <div class="rounded-md bg-canvas p-3 text-sm text-foreground-soft">
                <p class="font-medium text-foreground">Required CSV Format:</p>
                <p class="font-mono text-xs">barcode, name, price, stock</p>
                <p class="mt-1 text-xs"><strong>Required:</strong> name, price. <strong>Optional:</strong> barcode, stock.</p>
            </div>

            <div>
                <button type="button" class="text-xs text-primary-600 hover:text-primary-700" @click="downloadTemplate">
                    Download sample CSV template
                </button>
            </div>

            <div class="cursor-pointer rounded-lg border-2 border-dashed border-divider p-6 text-center hover:bg-hover" @click="fileInput?.click()">
                <input ref="fileInput" type="file" accept=".csv" class="hidden" @change="handleFileSelect" />
                <Upload class="mx-auto h-8 w-8 text-foreground-soft" />
                <p class="mt-2 text-sm text-foreground">
                    {{ hasFile ? selectedFile?.name : 'Choose a CSV file' }}
                </p>
                <p v-if="!hasFile" class="text-xs text-foreground-soft">or drag and drop here</p>
            </div>

            <FormError v-if="errors.file">{{ errors.file }}</FormError>
            <FormError v-if="errors.csv_file">{{ errors.csv_file }}</FormError>

            <div v-if="preview.length > 0">
                <p class="mb-2 text-sm font-medium text-foreground">Preview (showing first 5 products):</p>
                <div class="overflow-hidden rounded-md border border-divider">
                    <table class="w-full text-sm">
                        <thead class="bg-surface">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium">Barcode</th>
                                <th class="px-3 py-2 text-left font-medium">Product Name</th>
                                <th class="px-3 py-2 text-right font-medium">Price</th>
                                <th class="px-3 py-2 text-right font-medium">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in preview" :key="i" class="border-t border-divider">
                                <td class="px-3 py-2 text-foreground-soft">{{ row.barcode || '-' }}</td>
                                <td class="px-3 py-2">{{ row.name }}</td>
                                <td class="px-3 py-2 text-right">{{ row.price.toFixed(2) }}</td>
                                <td class="px-3 py-2 text-right">{{ row.stock }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <template #footer>
            <BtnSecondary @click="close">Cancel</BtnSecondary>
            <BtnPrimary :disabled="!hasFile || isProcessing" @click="handleSubmit">
                {{ isProcessing ? 'Importing Products...' : 'Import Products' }}
            </BtnPrimary>
        </template>
    </Modal>
</template>
