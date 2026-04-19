<script setup lang="ts">
import BtnPrimary from '@/components/buttons/BtnPrimary.vue';
import BtnSecondary from '@/components/buttons/BtnSecondary.vue';
import FormError from '@/components/forms/FormError.vue';
import Modal from '@/components/ui/Modal.vue';
import { importCsv } from '@/routes/members';
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
interface MemberPreview {
    last_name: string;
    first_name: string;
    middle_name?: string;
    name_extension?: string;
    address: string;
    tin_number?: string;
    balance: number;
    share_capital: number;
}

const preview = ref<MemberPreview[]>([]);

const hasFile = computed(() => selectedFile.value !== null);

const EXPECTED_HEADERS = ['last_name', 'first_name', 'middle_name', 'name_extension', 'address', 'tin_number', 'balance', 'share_capital'];

const validateHeader = (header: string[]): boolean => {
    if (header.length < 8) {
        return false;
    }

    const normalized = header.map((h) => h.trim().toLowerCase());

    for (let i = 0; i < EXPECTED_HEADERS.length; i++) {
        if (normalized[i] !== EXPECTED_HEADERS[i]) {
            return false;
        }
    }

    return true;
};

const parseCsvLine = (line: string): string[] => {
    const result: string[] = [];
    let current = '';
    let inQuotes = false;

    for (let i = 0; i < line.length; i++) {
        const char = line[i];
        const nextChar = line[i + 1];

        if (char === '"') {
            if (inQuotes && nextChar === '"') {
                current += '"';
                i++;
            } else {
                inQuotes = !inQuotes;
            }
        } else if (char === ',' && !inQuotes) {
            result.push(current.trim());
            current = '';
        } else {
            current += char;
        }
    }
    result.push(current.trim());

    return result.map((field) => {
        if (field.startsWith('"') && field.endsWith('"')) {
            return field.slice(1, -1).replace(/""/g, '"');
        }
        return field;
    });
};

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

        if (lines.length === 0) {
            errors.value.file = 'CSV file is empty';
            selectedFile.value = null;
            return;
        }

        const header = parseCsvLine(lines[0]);

        if (!validateHeader(header)) {
            errors.value.file = `Invalid CSV header. Expected: ${EXPECTED_HEADERS.join(', ')}`;
            selectedFile.value = null;
            preview.value = [];
            return;
        }

        const dataLines = lines.slice(1);

        preview.value = dataLines.slice(0, 5).map((line) => {
            const cols = parseCsvLine(line);
            return {
                last_name: cols[0] || '',
                first_name: cols[1] || '',
                middle_name: cols[2] || undefined,
                name_extension: cols[3] || undefined,
                address: cols[4] || '',
                tin_number: cols[5] || undefined,
                balance: parseFloat(cols[6]) || 0,
                share_capital: parseFloat(cols[7]) || 0,
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
    const csv =
        'last_name,first_name,middle_name,name_extension,address,tin_number,balance,share_capital\nDoe,John,Michael,Jr,123 Main St,123-456-789,0.00,1000.00';
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'members_template.csv';
    a.click();
    URL.revokeObjectURL(url);
};
</script>

<template>
    <Modal :show="show" max-width="xl" @close="close">
        <template #header>
            <div>
                <h3 class="text-lg font-semibold text-foreground">Bulk Import Members</h3>
                <p class="text-sm text-foreground-soft">Add multiple members at once using a spreadsheet</p>
            </div>
        </template>

        <div class="space-y-4">
            <div class="rounded-md bg-canvas p-3 text-sm text-foreground-soft">
                <p class="font-medium text-foreground">Required CSV Format:</p>
                <p class="font-mono text-xs">last_name, first_name, middle_name, name_extension, address, tin_number, balance, share_capital</p>
                <p class="mt-1 text-xs">
                    <strong>Required:</strong> last_name, first_name, address. <strong>Optional:</strong> middle_name, name_extension, tin_number,
                    balance, share_capital.
                </p>
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
                <p class="mb-2 text-sm font-medium text-foreground">Preview (showing first 5 members):</p>
                <div class="overflow-x-auto rounded-md border border-divider">
                    <table class="w-full text-sm">
                        <thead class="bg-surface">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium whitespace-nowrap">Last Name</th>
                                <th class="px-3 py-2 text-left font-medium whitespace-nowrap">First Name</th>
                                <th class="px-3 py-2 text-left font-medium whitespace-nowrap">Middle Name</th>
                                <th class="px-3 py-2 text-left font-medium whitespace-nowrap">Name Extension</th>
                                <th class="px-3 py-2 text-left font-medium whitespace-nowrap">Address</th>
                                <th class="px-3 py-2 text-right font-medium whitespace-nowrap">TIN Number</th>
                                <th class="px-3 py-2 text-right font-medium whitespace-nowrap">Balance</th>
                                <th class="px-3 py-2 text-right font-medium whitespace-nowrap">Share Capital</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in preview" :key="i" class="border-t border-divider">
                                <td class="px-3 py-2 whitespace-nowrap">{{ row.last_name }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ row.first_name }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-foreground-soft">{{ row.middle_name || '-' }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-foreground-soft">{{ row.name_extension || '-' }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ row.address }}</td>
                                <td class="px-3 py-2 text-right whitespace-nowrap text-foreground-soft">{{ row.tin_number || '-' }}</td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">{{ row.balance.toFixed(2) }}</td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">{{ row.share_capital.toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <template #footer>
            <BtnSecondary @click="close">Cancel</BtnSecondary>
            <BtnPrimary :disabled="!hasFile || isProcessing" @click="handleSubmit">
                {{ isProcessing ? 'Importing Members...' : 'Import Members' }}
            </BtnPrimary>
        </template>
    </Modal>
</template>
