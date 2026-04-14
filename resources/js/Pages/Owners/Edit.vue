<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    owner: Object,
    availableUsers: Array,
});

const form = useForm({
    name: props.owner.name,
    phone: props.owner.phone ?? '',
    email: props.owner.email ?? '',
    notes: props.owner.notes ?? '',
    user_id: props.owner.user_id ?? '',
});

function submit() {
    form.patch(route('owners.update', props.owner.id));
}
</script>

<template>
    <Head title="Editar Dono" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Editar Dono</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome *</label>
                                <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Telefone</label>
                                    <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notas</label>
                                <textarea v-model="form.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Conta de acesso -->
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="mb-3 text-lg font-medium text-gray-900">Conta de Acesso</h3>
                        <p class="mb-3 text-sm text-gray-500">Liga este dono a uma conta de utilizador para que possa aceder ao portal.</p>

                        <div v-if="owner.user && !form.user_id" class="mb-3 flex items-center gap-2 rounded-md bg-green-50 px-3 py-2 text-sm text-green-700">
                            <span>Ligado a <strong>{{ owner.user.name }}</strong> ({{ owner.user.email }})</span>
                        </div>

                        <select v-model="form.user_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">— Sem conta associada —</option>
                            <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                        <p v-if="form.errors.user_id" class="mt-1 text-sm text-red-600">{{ form.errors.user_id }}</p>
                    </div>

                    <!-- Cães associados -->
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="mb-3 text-lg font-medium text-gray-900">Cães associados</h3>
                        <div v-if="owner.dogs.length === 0" class="text-sm text-gray-400">Nenhum cão.</div>
                        <ul v-else class="space-y-1 text-sm text-gray-700">
                            <li v-for="dog in owner.dogs" :key="dog.id" class="flex items-center gap-2">
                                <span class="font-medium">{{ dog.name }}</span>
                                <span class="text-gray-400">{{ dog.breed ?? '' }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <Link :href="route('owners.show', owner.id)" class="text-sm text-gray-600 hover:underline">Cancelar</Link>
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
