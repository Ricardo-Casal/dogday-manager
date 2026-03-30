<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    owner: Object,
});
</script>

<template>
    <Head :title="owner.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ owner.name }}</h2>
                <Link :href="route('owners.edit', owner.id)" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Editar
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8 space-y-6">
                <!-- Owner info -->
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Dados do Dono</h3>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Telefone</dt>
                            <dd class="font-medium text-gray-900">{{ owner.phone ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Email</dt>
                            <dd class="font-medium text-gray-900">{{ owner.email ?? '—' }}</dd>
                        </div>
                        <div v-if="owner.notes" class="col-span-2">
                            <dt class="text-gray-500">Notas</dt>
                            <dd class="font-medium text-gray-900">{{ owner.notes }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Dogs -->
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Cães ({{ owner.dogs.length }})</h3>
                    <div v-if="owner.dogs.length === 0" class="text-sm text-gray-400">Nenhum cão registado.</div>
                    <div v-else class="space-y-4">
                        <div v-for="dog in owner.dogs" :key="dog.id" class="rounded-md border border-gray-200 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ dog.name }}</p>
                                    <p class="text-sm text-gray-500">{{ dog.breed ?? 'Raça desconhecida' }}</p>
                                </div>
                                <Link
                                    :href="route('attendances.create', { dog_id: dog.id })"
                                    class="text-sm text-indigo-600 hover:underline"
                                >
                                    Agendar dia
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex">
                    <Link :href="route('owners.index')" class="text-sm text-gray-500 hover:underline">← Voltar à lista</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
