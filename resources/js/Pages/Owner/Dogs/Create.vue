<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    breed: '',
    birthdate: '',
    notes: '',
});

function submit() {
    form.post(route('owner.dogs.store'));
}
</script>

<template>
    <Head title="Adicionar Cão" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Adicionar o meu cão</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow-sm space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nome *</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Nome do cão"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Raça</label>
                            <input
                                v-model="form.breed"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Ex: Labrador, Beagle..."
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data de nascimento</label>
                            <input
                                v-model="form.birthdate"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notas</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Alergias, comportamento, necessidades especiais..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <Link :href="route('owner.dashboard')" class="text-sm text-gray-600 hover:underline">Cancelar</Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
