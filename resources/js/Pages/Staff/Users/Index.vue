<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    users: Object,
    filters: Object,
});

const currentUserId = usePage().props.auth.user.id;

const search = ref(props.filters.search ?? '');
const perPage = ref(props.filters.per_page ?? 15);

let searchTimeout = null;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => reload(), 400);
});

watch(perPage, () => reload());

function reload() {
    router.get(route('staff.users.index'), { search: search.value, per_page: perPage.value }, { preserveState: true, replace: true });
}

// Delete modal
const deleteModal = ref(false);
const deleteTarget = ref(null);
const deleteNotes = ref('');
const deleteError = ref('');

function openDelete(user) {
    deleteTarget.value = user;
    deleteNotes.value = '';
    deleteError.value = '';
    deleteModal.value = true;
}

function confirmDelete() {
    if (deleteNotes.value.trim().length < 5) {
        deleteError.value = 'Indica o motivo (mínimo 5 caracteres).';
        return;
    }
    router.delete(route('staff.users.destroy', deleteTarget.value.id), {
        data: { notes: deleteNotes.value },
        onSuccess: () => { deleteModal.value = false; },
    });
}

function promote(user) {
    const action = user.role === 'staff' ? 'remover de staff' : 'promover a staff';
    if (confirm(`Tens a certeza que queres ${action} ${user.name}?`)) {
        router.patch(route('staff.users.promote', user.id));
    }
}

function roleLabel(user) {
    if (user.deleted_at) return 'Eliminado';
    return user.role === 'staff' ? 'Staff' : 'Owner';
}

function roleBadge(user) {
    if (user.deleted_at) return 'bg-red-100 text-red-700';
    if (user.role === 'staff') return 'bg-purple-100 text-purple-700';
    return 'bg-green-100 text-green-700';
}
</script>

<template>
    <Head title="Utilizadores" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Utilizadores</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-4">

                <!-- Search + per page -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <div class="relative flex-1 w-full">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Pesquisar por nome, email, telefone ou cão..."
                            class="pl-9 w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600 shrink-0">
                        <span>Por página:</span>
                        <select v-model="perPage" class="rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option :value="5">5</option>
                            <option :value="10">10</option>
                            <option :value="15">15</option>
                            <option :value="20">20</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nome</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hidden sm:table-cell">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hidden md:table-cell">Telefone</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hidden md:table-cell">Cães</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Perfil</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="user in users.data" :key="user.id" :class="user.deleted_at ? 'bg-red-50 opacity-70' : ''">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 text-sm">
                                        <Link :href="route('staff.users.show', user.id)" class="hover:underline hover:text-indigo-600">{{ user.name }}</Link>
                                        <span v-if="user.id === currentUserId" class="ml-1 text-xs text-gray-400">(tu)</span>
                                    </div>
                                    <div class="text-xs text-gray-400 sm:hidden">{{ user.email }}</div>
                                    <!-- Deleted reason -->
                                    <div v-if="user.deleted_at && user.deleted_notes" class="mt-1 text-xs text-red-500 italic">
                                        Motivo: {{ user.deleted_notes }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 hidden sm:table-cell">{{ user.email }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 hidden md:table-cell">{{ user.owner?.phone ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 hidden md:table-cell">
                                    <span v-if="user.owner?.dogs?.length">
                                        {{ user.owner.dogs.map(d => d.name).join(', ') }}
                                    </span>
                                    <span v-else class="text-gray-300">—</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', roleBadge(user)]">
                                        {{ roleLabel(user) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div v-if="!user.deleted_at && user.id !== currentUserId" class="flex items-center justify-end gap-3">
                                        <button @click="promote(user)" class="text-xs text-indigo-600 hover:underline">
                                            {{ user.role === 'staff' ? 'Remover staff' : 'Promover a staff' }}
                                        </button>
                                        <button @click="openDelete(user)" class="text-xs text-red-500 hover:underline">
                                            Apagar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!users.data.length">
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">
                                    Nenhum utilizador encontrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between text-sm text-gray-500">
                    <span>{{ users.total }} utilizador{{ users.total !== 1 ? 'es' : '' }}</span>
                    <div class="flex gap-1">
                        <template v-for="link in users.links" :key="link.label">
                            <button
                                v-if="link.url"
                                @click="router.get(link.url, { per_page: perPage, search }, { preserveState: true })"
                                :class="['px-3 py-1 rounded border text-xs', link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white border-gray-300 hover:bg-gray-50']"
                                v-html="link.label"
                            />
                            <span v-else class="px-3 py-1 rounded border border-gray-200 text-xs text-gray-300 bg-white" v-html="link.label" />
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Delete Modal -->
    <div v-if="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Apagar utilizador</h3>
            <p class="text-sm text-gray-500 mb-4">
                Tens a certeza que queres apagar <strong>{{ deleteTarget?.name }}</strong>?
                Esta ação pode ser consultada posteriormente.
            </p>
            <label class="block text-sm font-medium text-gray-700 mb-1">Motivo *</label>
            <textarea
                v-model="deleteNotes"
                rows="3"
                placeholder="Indica o motivo pelo qual estás a apagar este utilizador..."
                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
            ></textarea>
            <p v-if="deleteError" class="mt-1 text-xs text-red-500">{{ deleteError }}</p>
            <div class="mt-4 flex justify-end gap-3">
                <button @click="deleteModal = false" class="text-sm text-gray-600 hover:underline">Cancelar</button>
                <button @click="confirmDelete" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Apagar
                </button>
            </div>
        </div>
    </div>
</template>
