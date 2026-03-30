<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    owners: Array,
});

function deleteOwner(id) {
    if (confirm('Tens a certeza? Isto irá apagar o dono e todos os cães associados.')) {
        router.delete(route('owners.destroy', id));
    }
}
</script>

<template>
    <Head title="Donos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Donos</h2>
                <Link
                    :href="route('owners.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    + Novo Dono
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div v-if="owners.length === 0" class="rounded-lg bg-white p-12 text-center shadow-sm">
                    <p class="text-gray-500">Nenhum dono registado.</p>
                </div>

                <div v-else class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nome</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Telefone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Cães</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="owner in owners" :key="owner.id">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    <Link :href="route('owners.show', owner.id)" class="hover:text-indigo-600">
                                        {{ owner.name }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ owner.phone ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ owner.email ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ owner.dogs_count }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <Link :href="route('owners.edit', owner.id)" class="mr-3 text-indigo-600 hover:underline">Editar</Link>
                                    <button @click="deleteOwner(owner.id)" class="text-red-500 hover:underline">Apagar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
