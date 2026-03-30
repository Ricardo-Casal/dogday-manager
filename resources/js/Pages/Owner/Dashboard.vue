<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    owner: Object,
});

const statusColor = {
    pendente: 'bg-yellow-100 text-yellow-800',
    aprovado: 'bg-green-100 text-green-800',
    rejeitado: 'bg-red-100 text-red-800',
};

const typeLabel = { atl: 'ATL', hotel: 'Hotel', aula: 'Aula' };
const freqLabel = { semanal: 'Semanal', quinzenal: 'Quinzenal', mensal: 'Mensal' };
</script>

<template>
    <Head title="A minha área" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Olá, {{ owner?.name ?? 'Dono' }}
                </h2>
                <Link
                    :href="route('owner.bookings.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    + Novo Pedido
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">

                <!-- Dogs -->
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-3 text-lg font-medium text-gray-900">Os meus cães</h3>
                    <div v-if="!owner?.dogs?.length" class="text-sm text-gray-400">
                        Ainda não tem cães registados. Contacte-nos para os adicionar.
                    </div>
                    <div v-else class="flex flex-wrap gap-3">
                        <div
                            v-for="dog in owner.dogs"
                            :key="dog.id"
                            class="flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2"
                        >
                            <span class="font-medium text-indigo-800">{{ dog.name }}</span>
                            <span v-if="dog.breed" class="text-sm text-indigo-500">· {{ dog.breed }}</span>
                        </div>
                    </div>
                </div>

                <!-- Bookings -->
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Os meus pedidos</h3>

                    <div v-if="!owner?.bookings?.length" class="text-center py-8 text-sm text-gray-400">
                        Ainda não fez nenhum pedido.
                        <Link :href="route('owner.bookings.create')" class="ml-1 text-indigo-600 hover:underline">
                            Fazer primeiro pedido
                        </Link>
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="booking in owner.bookings"
                            :key="booking.id"
                            class="rounded-lg border border-gray-100 p-4"
                        >
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-medium text-gray-900">{{ booking.dog.name }}</span>
                                        <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                                            {{ typeLabel[booking.type] }}
                                        </span>
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
                                    <p v-if="booking.notes" class="mt-1 text-sm text-gray-400 italic">"{{ booking.notes }}"</p>
                                    <p v-if="booking.staff_notes" class="mt-1 text-sm text-gray-600">
                                        <span class="font-medium">Nota da equipa:</span> {{ booking.staff_notes }}
                                    </p>
                                </div>
                                <span :class="['rounded-full px-3 py-1 text-xs font-medium capitalize', statusColor[booking.status]]">
                                    {{ booking.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
