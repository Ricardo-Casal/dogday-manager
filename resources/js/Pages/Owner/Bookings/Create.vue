<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    owner: Object,
    prices: Object,
});

const form = useForm({
    dog_id:     '',
    type:       'atl',
    subtype:    '',
    is_regular: true,
    start_date: '',
    end_date:   '',
    frequency:  'semanal',
    pet_taxi:   false,
    notes:      '',
});

const isHotel      = computed(() => form.type === 'hotel');
const isRecurring  = computed(() => ['atl', 'aula', 'integracao'].includes(form.type));
const isPack       = computed(() => form.type === 'pack_creche');
const isAula       = computed(() => form.type === 'aula');
const needsRegular = computed(() => ['atl', 'hotel'].includes(form.type));

const serviceTypes = [
    { value: 'atl',         label: 'Creche (ATL)',  icon: '🏠' },
    { value: 'hotel',       label: 'Hotel',         icon: '🌙' },
    { value: 'integracao',  label: 'Integração',    icon: '🐾' },
    { value: 'aula',        label: 'Treino',        icon: '🎓' },
    { value: 'pack_creche', label: 'Pack Creche',   icon: '📦' },
];

const aulaSubtypes = [
    { value: 'individual',               label: 'Individual (nas instalações)', price: () => props.prices.aula },
    { value: 'domicilio',                label: 'A Domicílio',                  price: () => props.prices.aula_domicilio },
    { value: 'grupo',                    label: 'Em Grupo',                     price: () => props.prices.aula_grupo },
    { value: 'avaliacao_comportamental', label: 'Avaliação Comportamental',    price: () => props.prices.avaliacao_comportamental },
];

const packOptions = [
    { value: '4',  sessions: 4,  price: () => props.prices.pack_4 },
    { value: '5',  sessions: 5,  price: () => props.prices.pack_5 },
    { value: '6',  sessions: 6,  price: () => props.prices.pack_6 },
    { value: '8',  sessions: 8,  price: () => props.prices.pack_8 },
    { value: '10', sessions: 10, price: () => props.prices.pack_10 },
    { value: '12', sessions: 12, price: () => props.prices.pack_12 },
    { value: '15', sessions: 15, price: () => props.prices.pack_15 },
];

const displayPrice = computed(() => {
    switch (form.type) {
        case 'atl':
            return (form.is_regular ? props.prices.atl : props.prices.atl_nao_regular) + '€/dia';
        case 'hotel':
            return (form.is_regular ? props.prices.hotel_noite : props.prices.hotel_noite_nao_regular) + '€/noite';
        case 'integracao':
            return props.prices.integracao + '€/sessão';
        case 'aula': {
            const sub = aulaSubtypes.find(s => s.value === form.subtype) ?? aulaSubtypes[0];
            return sub.price() + '€';
        }
        case 'pack_creche': {
            if (!form.subtype) return '—';
            const key = 'pack_' + form.subtype;
            return props.prices[key] + '€';
        }
        default: return '—';
    }
});

function onTypeChange() {
    form.subtype = '';
    if (form.type === 'aula') form.subtype = 'individual';
}

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

                <!-- No dogs warning -->
                <div v-if="!owner?.dogs?.length" class="rounded-lg bg-yellow-50 border border-yellow-200 p-6 text-center">
                    <p class="text-2xl mb-3">🐾</p>
                    <p class="text-gray-700 font-medium mb-1">Ainda não tens nenhum cão registado.</p>
                    <p class="text-sm text-gray-500 mb-4">Para fazer um pedido de reserva precisas de registar o teu patudo primeiro.</p>
                    <Link
                        :href="route('owner.dogs.create')"
                        class="inline-block rounded-md bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        Registar o meu cão
                    </Link>
                </div>

                <form v-else @submit.prevent="submit" class="space-y-6">
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

                        <!-- Service type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de serviço *</label>
                            <div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                                <label
                                    v-for="opt in serviceTypes"
                                    :key="opt.value"
                                    :class="['flex flex-col items-center rounded-lg border-2 p-3 cursor-pointer transition', form.type === opt.value ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300']"
                                >
                                    <input type="radio" v-model="form.type" :value="opt.value" class="sr-only" @change="onTypeChange" />
                                    <span class="text-xl mb-1">{{ opt.icon }}</span>
                                    <span class="text-xs font-medium text-gray-900 text-center">{{ opt.label }}</span>
                                </label>
                            </div>
                            <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                        </div>

                        <!-- Aula subtype -->
                        <div v-if="isAula">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de treino *</label>
                            <div class="space-y-2">
                                <label
                                    v-for="sub in aulaSubtypes"
                                    :key="sub.value"
                                    :class="['flex items-center justify-between rounded-lg border-2 p-3 cursor-pointer transition', form.subtype === sub.value ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300']"
                                >
                                    <div class="flex items-center gap-3">
                                        <input type="radio" v-model="form.subtype" :value="sub.value" class="text-indigo-600" />
                                        <span class="text-sm font-medium text-gray-900">{{ sub.label }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-indigo-700">{{ sub.price() }}€</span>
                                </label>
                            </div>
                            <p v-if="form.errors.subtype" class="mt-1 text-sm text-red-600">{{ form.errors.subtype }}</p>
                        </div>

                        <!-- Pack sessions selector -->
                        <div v-if="isPack">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Número de sessões *</label>
                            <div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
                                <label
                                    v-for="pack in packOptions"
                                    :key="pack.value"
                                    :class="['flex flex-col items-center rounded-lg border-2 p-2 cursor-pointer transition', form.subtype === pack.value ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300']"
                                >
                                    <input type="radio" v-model="form.subtype" :value="pack.value" class="sr-only" />
                                    <span class="text-lg font-bold text-gray-900">{{ pack.sessions }}</span>
                                    <span class="text-xs text-gray-500">{{ pack.price() }}€</span>
                                </label>
                            </div>
                            <p v-if="form.errors.subtype" class="mt-1 text-sm text-red-600">{{ form.errors.subtype }}</p>
                        </div>

                        <!-- Regular / Não regular toggle (ATL and Hotel) -->
                        <div v-if="needsRegular" class="rounded-lg bg-gray-50 p-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Frequência do cliente</p>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" :value="true" v-model="form.is_regular" class="text-indigo-600" />
                                    <div>
                                        <span class="text-sm font-medium text-gray-800">Regular</span>
                                        <span class="ml-2 text-xs text-indigo-700 font-semibold">
                                            {{ form.type === 'atl' ? prices.atl : prices.hotel_noite }}€
                                        </span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" :value="false" v-model="form.is_regular" class="text-indigo-600" />
                                    <div>
                                        <span class="text-sm font-medium text-gray-800">Não Regular</span>
                                        <span class="ml-2 text-xs text-indigo-700 font-semibold">
                                            {{ form.type === 'atl' ? prices.atl_nao_regular : prices.hotel_noite_nao_regular }}€
                                        </span>
                                    </div>
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

                        <!-- Recurring (ATL / Aula / Integração) -->
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

                        <!-- Pack start date -->
                        <template v-if="isPack">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Data de início *</label>
                                <input v-model="form.start_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
                            </div>
                        </template>

                        <!-- Price summary -->
                        <div class="rounded-lg bg-indigo-50 px-4 py-3 flex items-center justify-between">
                            <span class="text-sm text-indigo-700">Preço estimado</span>
                            <span class="text-base font-bold text-indigo-900">{{ displayPrice }}</span>
                        </div>

                        <!-- Pet taxi -->
                        <div class="flex items-start gap-3 rounded-lg bg-gray-50 p-4">
                            <input id="pet_taxi" v-model="form.pet_taxi" type="checkbox" class="mt-0.5 h-4 w-4 rounded text-indigo-600" />
                            <div>
                                <label for="pet_taxi" class="text-sm font-medium text-gray-700 cursor-pointer">
                                    Pet Taxi (ida e volta)
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
