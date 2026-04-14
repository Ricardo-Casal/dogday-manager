<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ user: Object });

const ownerForm = useForm({
    phone: props.user.owner?.phone ?? '',
    notes: props.user.owner?.notes ?? '',
});

function saveOwner() {
    ownerForm.patch(route('staff.users.update', props.user.id));
}

// Add dog
const dogForm = useForm({ name: '', breed: '', birthdate: '', notes: '' });
const showDogForm = ref(false);

function saveDog() {
    dogForm.post(route('staff.users.dogs.store', props.user.id), {
        onSuccess: () => {
            dogForm.reset();
            showDogForm.value = false;
        },
    });
}

function removeDog(dog) {
    if (confirm(`Remover o cão "${dog.name}"?`)) {
        router.delete(route('staff.users.dogs.destroy', { user: props.user.id, dog: dog.id }));
    }
}

const roleLabel = props.user.role === 'staff' ? 'Staff' : 'Owner';
const roleBadge = props.user.role === 'staff' ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700';
</script>

<template>
    <Head :title="user.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('staff.users.index')" class="text-sm text-gray-500 hover:underline">Utilizadores</Link>
                <span class="text-gray-300">/</span>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ user.name }}</h2>
                <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', roleBadge]">{{ roleLabel }}</span>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8 space-y-6">

                <!-- User info -->
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Conta</h3>
                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex gap-2"><span class="font-medium w-20">Nome</span><span>{{ user.name }}</span></div>
                        <div class="flex gap-2"><span class="font-medium w-20">Email</span><span>{{ user.email }}</span></div>
                        <div class="flex gap-2"><span class="font-medium w-20">Criado</span><span>{{ new Date(user.created_at).toLocaleDateString('pt-PT') }}</span></div>
                    </div>
                </div>

                <!-- Owner profile (only for owners) -->
                <div v-if="user.role === 'owner'" class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Perfil de Dono</h3>
                    <form @submit.prevent="saveOwner" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Telefone</label>
                            <input v-model="ownerForm.phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notas internas</label>
                            <textarea v-model="ownerForm.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="ownerForm.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Dogs (only for owners) -->
                <div v-if="user.role === 'owner'" class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Cães</h3>
                        <button @click="showDogForm = !showDogForm" class="text-sm text-indigo-600 hover:underline">
                            {{ showDogForm ? 'Cancelar' : '+ Adicionar cão' }}
                        </button>
                    </div>

                    <!-- Add dog form -->
                    <form v-if="showDogForm" @submit.prevent="saveDog" class="mb-6 rounded-lg border border-indigo-100 bg-indigo-50 p-4 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome *</label>
                                <input v-model="dogForm.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <p v-if="dogForm.errors.name" class="mt-1 text-xs text-red-600">{{ dogForm.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Raça</label>
                                <input v-model="dogForm.breed" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data de nascimento</label>
                            <input v-model="dogForm.birthdate" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notas</label>
                            <textarea v-model="dogForm.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="dogForm.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                                Guardar cão
                            </button>
                        </div>
                    </form>

                    <!-- Dogs list -->
                    <div v-if="user.owner?.dogs?.length" class="space-y-2">
                        <div v-for="dog in user.owner.dogs" :key="dog.id" class="flex items-center justify-between rounded-lg border border-gray-100 px-4 py-3">
                            <div>
                                <span class="font-medium text-gray-900 text-sm">{{ dog.name }}</span>
                                <span v-if="dog.breed" class="ml-2 text-xs text-gray-400">{{ dog.breed }}</span>
                                <span v-if="dog.birthdate" class="ml-2 text-xs text-gray-400">· {{ new Date(dog.birthdate).toLocaleDateString('pt-PT') }}</span>
                            </div>
                            <button @click="removeDog(dog)" class="text-xs text-red-500 hover:underline">Remover</button>
                        </div>
                    </div>
                    <div v-else-if="!showDogForm" class="text-sm text-gray-400">Nenhum cão registado.</div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
