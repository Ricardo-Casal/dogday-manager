<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: Array,
});

const forms = {};
const saved = ref({});

function resolveForm(setting) {
    if (!forms[setting.id]) {
        forms[setting.id] = useForm({ value: setting.value });
    }
    return forms[setting.id];
}

function save(setting) {
    resolveForm(setting).patch(route('staff.settings.update', setting.id), {
        onSuccess: () => {
            saved.value[setting.id] = true;
            setTimeout(() => { saved.value[setting.id] = false; }, 2000);
        },
    });
}
</script>

<template>
    <Head title="Preços" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Gestão de Preços</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white shadow-sm divide-y">
                    <div
                        v-for="setting in settings"
                        :key="setting.id"
                        class="flex items-center justify-between px-6 py-4"
                    >
                        <div>
                            <p class="font-medium text-gray-900">{{ setting.label }}</p>
                            <p class="text-xs text-gray-400">{{ setting.key }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">€</span>
                                <input
                                    v-model="resolveForm(setting).value"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-28 rounded-md border-gray-300 pl-7 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                />
                            </div>
                            <button
                                @click="save(setting)"
                                :disabled="resolveForm(setting).processing"
                                class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                            >
                                <span v-if="saved[setting.id]">✓ Guardado</span>
                                <span v-else>Guardar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
