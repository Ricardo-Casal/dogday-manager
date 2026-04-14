<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    availableUsers: Array,
});

const form = useForm({
    name: '',
    phone: '',
    email: '',
    notes: '',
    user_id: '',
    dogs: [],
});

function addDog() {
    form.dogs.push({ name: '', breed: '', birthdate: '', notes: '' });
}

function removeDog(index) {
    form.dogs.splice(index, 1);
}

function submit() {
    form.post(route('owners.store'));
}
</script>

<template>
    <Head title="Novo Dono" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Novo Dono</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Owner fields -->
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Dados do Dono</h3>
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
                        <p class="mb-3 text-sm text-gray-500">Opcional — liga este dono a uma conta de utilizador. Se o email coincidir, a ligação é feita automaticamente.</p>
                        <select v-model="form.user_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">— Sem conta associada —</option>
                            <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                        <p v-if="form.errors.user_id" class="mt-1 text-sm text-red-600">{{ form.errors.user_id }}</p>
                    </div>

                    <!-- Dogs -->
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Cães</h3>
                            <button type="button" @click="addDog" class="text-sm text-indigo-600 hover:underline">+ Adicionar Cão</button>
                        </div>

                        <div v-if="form.dogs.length === 0" class="py-4 text-center text-sm text-gray-400">
                            Nenhum cão adicionado ainda.
                        </div>

                        <div v-for="(dog, index) in form.dogs" :key="index" class="mb-4 rounded-md border border-gray-200 p-4">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Cão {{ index + 1 }}</span>
                                <button type="button" @click="removeDog(index)" class="text-xs text-red-500 hover:underline">Remover</button>
                            </div>
                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Nome *</label>
                                        <input v-model="dog.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                        <p v-if="form.errors[`dogs.${index}.name`]" class="mt-1 text-sm text-red-600">{{ form.errors[`dogs.${index}.name`] }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Raça</label>
                                        <input v-model="dog.breed" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data de Nascimento</label>
                                    <input v-model="dog.birthdate" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Notas</label>
                                    <textarea v-model="dog.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <Link :href="route('owners.index')" class="text-sm text-gray-600 hover:underline">Cancelar</Link>
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
