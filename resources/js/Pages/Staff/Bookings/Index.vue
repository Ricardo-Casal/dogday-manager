<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    bookings: Array,
    tab: String,
    counts: Object,
});

const statusColor = {
    pendente:  'bg-yellow-100 text-yellow-800',
    aprovado:  'bg-green-100 text-green-800',
    rejeitado: 'bg-red-100 text-red-800',
};

const typeLabel = { atl: 'ATL', hotel: 'Hotel', aula: 'Treino', integracao: 'Integração', pack_creche: 'Pack Creche', pet_sitting: 'Pet Sitting', dog_walking: 'Dog Walking', banho: 'Banho' };

function fmtDate(d) {
    if (!d) return '';
    return new Date(d).toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
const subtypeLabel = {
    individual: 'Individual', domicilio: 'Domicílio', grupo: 'Grupo',
    avaliacao_comportamental: 'Avaliação Comp.',
};
const freqLabel = { semanal: 'Semanal', quinzenal: 'Quinzenal', mensal: 'Mensal' };

const tabs = [
    { key: 'pendente',  label: 'Pendentes' },
    { key: 'aprovado',  label: 'Aprovados' },
    { key: 'rejeitado', label: 'Recusados' },
];

function switchTab(key) {
    router.get(route('staff.bookings.index'), { tab: key }, { preserveState: false });
}

const activeForm = ref(null);
const forms = {};

function resolveForm(booking) {
    if (!forms[booking.id]) {
        forms[booking.id] = useForm({
            status:      'aprovado',
            staff_notes: booking.staff_notes ?? '',
        });
    }
    return forms[booking.id];
}

function submit(booking) {
    const form = resolveForm(booking);
    form.patch(route('staff.bookings.update', booking.id), {
        onSuccess: () => { activeForm.value = null; },
    });
}
</script>

<template>
    <Head title="Pedidos de Reserva" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Pedidos de Reserva</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">

                <!-- Tabs -->
                <div class="flex gap-1 mb-6 border-b border-gray-200">
                    <button
                        v-for="t in tabs"
                        :key="t.key"
                        @click="switchTab(t.key)"
                        :class="[
                            'px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors',
                            tab === t.key
                                ? 'border-indigo-600 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        {{ t.label }}
                        <span v-if="counts[t.key]" :class="[
                            'ml-1.5 rounded-full px-1.5 py-0.5 text-xs',
                            tab === t.key ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500'
                        ]">{{ counts[t.key] }}</span>
                    </button>
                </div>

                <div v-if="bookings.length === 0" class="rounded-lg bg-white p-12 text-center shadow-sm text-gray-400">
                    Não há pedidos {{ tab === 'pendente' ? 'pendentes' : tab === 'aprovado' ? 'aprovados' : 'recusados' }}.
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="booking in bookings"
                        :key="booking.id"
                        class="rounded-lg bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-1 flex-wrap">
                                    <span class="font-semibold text-gray-900">{{ booking.dog.name }}</span>
                                    <span class="text-sm text-gray-500">de {{ booking.owner.name }}</span>
                                    <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ typeLabel[booking.type] }}</span>
                                    <span v-if="booking.subtype && booking.type === 'aula'" class="rounded bg-purple-100 px-2 py-0.5 text-xs text-purple-700">{{ subtypeLabel[booking.subtype] ?? booking.subtype }}</span>
                                    <span v-if="booking.subtype && booking.type === 'pack_creche'" class="rounded bg-blue-100 px-2 py-0.5 text-xs text-blue-700">{{ booking.subtype }} sessões</span>
                                    <span v-if="['atl','hotel'].includes(booking.type) && booking.is_regular" class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">Regular</span>
                                    <span v-if="['atl','hotel'].includes(booking.type) && !booking.is_regular" class="rounded bg-orange-100 px-2 py-0.5 text-xs text-orange-700">Não Regular</span>
                                    <span v-if="booking.pet_taxi" class="rounded bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">Pet Taxi</span>
                                </div>
                                <p class="text-sm text-gray-500">
                                    <template v-if="booking.type === 'hotel'">
                                        {{ fmtDate(booking.start_date) }} → {{ fmtDate(booking.end_date) }}
                                    </template>
                                    <template v-else-if="booking.type === 'pack_creche'">
                                        A partir de {{ fmtDate(booking.start_date) }}
                                    </template>
                                    <template v-else>
                                        A partir de {{ fmtDate(booking.start_date) }} · {{ freqLabel[booking.frequency] }}
                                    </template>
                                </p>
                                <p v-if="booking.notes" class="mt-2 text-sm text-gray-500 italic">"{{ booking.notes }}"</p>
                                <p v-if="booking.staff_notes" class="mt-1 text-sm text-gray-600">
                                    <span class="font-medium">Nota:</span> {{ booking.staff_notes }}
                                </p>
                            </div>

                            <div class="flex flex-col items-end gap-2">
                                <span :class="['rounded-full px-3 py-1 text-xs font-medium capitalize', statusColor[booking.status]]">
                                    {{ booking.status }}
                                </span>
                                <button
                                    v-if="booking.status === 'pendente'"
                                    @click="activeForm = booking.id"
                                    class="text-sm text-indigo-600 hover:underline"
                                >
                                    Responder
                                </button>
                            </div>
                        </div>

                        <!-- Response form -->
                        <div v-if="activeForm === booking.id" class="mt-4 border-t pt-4">
                            <div class="flex gap-4 mb-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="resolveForm(booking).status" value="aprovado" class="text-green-600" />
                                    <span class="text-sm font-medium text-green-700">Aprovar</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="resolveForm(booking).status" value="rejeitado" class="text-red-600" />
                                    <span class="text-sm font-medium text-red-700">Rejeitar</span>
                                </label>
                            </div>
                            <textarea
                                v-model="resolveForm(booking).staff_notes"
                                rows="2"
                                placeholder="Nota para o dono (opcional)..."
                                class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 mb-3"
                            ></textarea>
                            <div class="flex gap-2">
                                <button
                                    @click="submit(booking)"
                                    :disabled="resolveForm(booking).processing"
                                    class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Confirmar
                                </button>
                                <button @click="activeForm = null" class="text-sm text-gray-500 hover:underline">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
