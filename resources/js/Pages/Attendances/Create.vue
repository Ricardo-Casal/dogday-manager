<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    dogs: Array,
    date: String,
});

const form = useForm({
    dog_id: '',
    date: props.date,
    type: 'atl',
    notes: '',
});

function submit() {
    form.post(route('attendances.store'));
}
</script>

<template>
    <Head title="Agendar Cão" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Agendar Cão</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow-sm space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cão *</label>
                            <select v-model="form.dog_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecionar cão...</option>
                                <option v-for="dog in dogs" :key="dog.id" :value="dog.id">
                                    {{ dog.name }} ({{ dog.owner.name }})
                                </option>
                            </select>
                            <p v-if="form.errors.dog_id" class="mt-1 text-sm text-red-600">{{ form.errors.dog_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data *</label>
                            <input v-model="form.date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <p v-if="form.errors.date" class="mt-1 text-sm text-red-600">{{ form.errors.date }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo *</label>
                            <div class="mt-2 flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="form.type" value="atl" class="text-indigo-600" />
                                    <span class="text-sm">ATL</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="form.type" value="hotel" class="text-indigo-600" />
                                    <span class="text-sm">Hotel</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="form.type" value="aula" class="text-indigo-600" />
                                    <span class="text-sm">Aula</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notas</label>
                            <textarea v-model="form.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <Link :href="route('dashboard')" class="text-sm text-gray-600 hover:underline">Cancelar</Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            Agendar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
