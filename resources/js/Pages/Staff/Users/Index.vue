<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

defineProps({
    users: Array,
});

const currentUserId = usePage().props.auth.user.id;

function deleteUser(id) {
    if (confirm('Tens a certeza que queres remover este utilizador de staff?')) {
        router.delete(route('staff.users.destroy', id));
    }
}
</script>

<template>
    <Head title="Utilizadores Staff" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Utilizadores Staff</h2>
                <Link
                    :href="route('staff.users.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    + Novo Staff
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nome</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="user in users" :key="user.id">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ user.name }}
                                    <span v-if="user.id === currentUserId" class="ml-2 text-xs text-gray-400">(tu)</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ user.email }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        v-if="user.id !== currentUserId"
                                        @click="deleteUser(user.id)"
                                        class="text-sm text-red-500 hover:underline"
                                    >
                                        Remover
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
