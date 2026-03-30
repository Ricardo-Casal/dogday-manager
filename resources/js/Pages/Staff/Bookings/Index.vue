<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    bookings: Array,
});

const statusColor = {
    pendente: 'bg-yellow-100 text-yellow-800',
    aprovado: 'bg-green-100 text-green-800',
    rejeitado: 'bg-red-100 text-red-800',
};

const typeLabel = { atl: 'ATL', hotel: 'Hotel', aula: 'Aula' };
const freqLabel = { semanal: 'Semanal', quinzenal: 'Quinzenal', mensal: 'Mensal' };

const activeForm = ref(null);

function openForm(bookingId) {
    activeForm.value = bookingId;
}

function getForm(booking) {
    return useForm({
        status: booking.status === 'pendente' ? 'aprovado' : booking.status,
        staff_notes: booking.staff_notes ?? '',
    });
}

const forms = {};

function resolveForm(booking) {
    if (!forms[booking.id]) {
        forms[booking.id] = useForm({
            status: 'aprovado',
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
                <div v-if="bookings.length === 0" class="rounded-lg bg-white p-12 text-center shadow-sm text-gray-400">
                    Não há pedidos de reserva.
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="booking in bookings"
                        :key="booking.id"
                        class="rounded-lg bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="font-semibold text-gray-900">{{ booking.dog.name }}</span>
                                    <span class="text-sm text-gray-500">de {{ booking.owner.name }}</span>
                                    <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ typeLabel[booking.type] }}</span>
                                </div>
                                <p class="text-sm text-gray-500">
                                    <template v-if="booking.type === 'hotel'">
                                        {{ booking.start_date }} → {{ booking.end_date }}
                                    </template>
                                    <template v-else>
                                        A partir de {{ booking.start_date }} · {{ freqLabel[booking.frequency] }}
                                    </template>
                                </p>
                                <p v-if="booking.pet_taxi" class="mt-1 text-xs text-indigo-600">Inclui Pet Taxi</p>
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
                                    @click="openForm(booking.id)"
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
