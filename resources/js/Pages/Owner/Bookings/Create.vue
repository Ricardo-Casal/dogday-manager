<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    owner: Object,
    prices: Object,
});

const form = useForm({
    dog_id: '',
    type: 'atl',
    start_date: '',
    end_date: '',
    frequency: 'semanal',
    pet_taxi: false,
    notes: '',
});

const isHotel = computed(() => form.type === 'hotel');
const isRecurring = computed(() => form.type === 'atl' || form.type === 'aula');

function submit() {
    form.post(route('owner.bookings.store'));
}
</script>

<template>
    <Head title="Novo Pedido" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Novo Pedido de Reserva</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow-sm space-y-5">

                        <!-- Dog -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cão *</label>
                            <select v-model="form.dog_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecionar cão...</option>
                                <option v-for="dog in owner.dogs" :key="dog.id" :value="dog.id">
                                    {{ dog.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.dog_id" class="mt-1 text-sm text-red-600">{{ form.errors.dog_id }}</p>
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de serviço *</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label
                                    v-for="opt in [{ value: 'atl', label: 'ATL', price: prices.atl }, { value: 'hotel', label: 'Hotel', price: prices.hotel_noite + '/noite' }, { value: 'aula', label: 'Aula', price: prices.aula }]"
                                    :key="opt.value"
                                    :class="['flex flex-col items-center rounded-lg border-2 p-3 cursor-pointer transition', form.type === opt.value ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300']"
                                >
                                    <input type="radio" v-model="form.type" :value="opt.value" class="sr-only" />
                                    <span class="font-medium text-gray-900">{{ opt.label }}</span>
                                    <span class="text-xs text-gray-500 mt-1">{{ opt.price }}€</span>
                                </label>
                            </div>
                        </div>

                        <!-- Hotel dates -->
                        <template v-if="isHotel">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data de entrada *</label>
                                    <input v-model="form.start_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data de saída *</label>
                                    <input v-model="form.end_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">{{ form.errors.end_date }}</p>
                                </div>
                            </div>
                        </template>

                        <!-- ATL / Aula -->
                        <template v-if="isRecurring">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Data de início *</label>
                                <input v-model="form.start_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Frequência *</label>
                                <div class="flex gap-4">
                                    <label v-for="opt in [{ value: 'semanal', label: 'Semanal' }, { value: 'quinzenal', label: 'Quinzenal' }, { value: 'mensal', label: 'Mensal' }]" :key="opt.value" class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" v-model="form.frequency" :value="opt.value" class="text-indigo-600" />
                                        <span class="text-sm">{{ opt.label }}</span>
                                    </label>
                                </div>
                                <p v-if="form.errors.frequency" class="mt-1 text-sm text-red-600">{{ form.errors.frequency }}</p>
                            </div>
                        </template>

                        <!-- Pet taxi -->
                        <div class="flex items-start gap-3 rounded-lg bg-gray-50 p-4">
                            <input id="pet_taxi" v-model="form.pet_taxi" type="checkbox" class="mt-0.5 h-4 w-4 rounded text-indigo-600" />
                            <div>
                                <label for="pet_taxi" class="text-sm font-medium text-gray-700 cursor-pointer">
                                    Pet Taxi (recolha em casa)
                                </label>
                                <p class="text-xs text-gray-500 mt-0.5">Custo adicional: {{ prices.pet_taxi }}€</p>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notas (opcional)</label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="Informações adicionais, horário preferido..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                            Enviar Pedido
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
