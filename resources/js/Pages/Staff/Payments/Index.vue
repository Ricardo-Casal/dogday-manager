<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    owners: Array,
    tab: String,
    counts: Object,
    isMock: Boolean,
    isSandbox: Boolean,
});

function switchTab(key) {
    router.get(route('staff.payments.index'), { tab: key }, { preserveState: false });
}

const activeBooking = ref(null);
const forms = {};

function resolveForm(bookingId) {
    if (!forms[bookingId]) {
        forms[bookingId] = useForm({ method: 'mbway', mbway_phone: '' });
    }
    return forms[bookingId];
}

function generate(booking) {
    resolveForm(booking.id).post(route('staff.payments.generate', booking.id), {
        onSuccess: () => { activeBooking.value = null; },
    });
}

function resend(payment) {
    router.post(route('staff.payments.resend', payment.id));
}

const statusColor = {
    pendente: 'bg-yellow-100 text-yellow-800',
    pago:     'bg-green-100 text-green-800',
    expirado: 'bg-gray-100 text-gray-600',
    falhado:  'bg-red-100 text-red-800',
};

const typeLabel    = { atl: 'ATL', hotel: 'Hotel', aula: 'Treino', integracao: 'Integração', pack_creche: 'Pack Creche' };
const subtypeLabel = {
    individual: 'Individual', domicilio: 'Domicílio', grupo: 'Grupo',
    avaliacao_comportamental: 'Avaliação Comp.',
};
</script>

<template>
    <Head title="Pagamentos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Gestão de Pagamentos</h2>
                <span v-if="isMock" class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">
                    Modo de demonstração — sem credenciais Easypay
                </span>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">

                <!-- Tabs -->
                <div class="flex gap-1 border-b border-gray-200">
                    <button
                        v-for="t in [{ key: 'pendente', label: 'Por pagar' }, { key: 'pago', label: 'Pagos' }]"
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

                <div v-if="owners.length === 0" class="rounded-lg bg-white p-12 text-center shadow-sm text-gray-400">
                    {{ tab === 'pago' ? 'Não há pagamentos realizados.' : 'Não há reservas com pagamentos pendentes.' }}
                </div>

                <div v-for="owner in owners" :key="owner.id" class="rounded-lg bg-white shadow-sm overflow-hidden">
                    <!-- Owner header -->
                    <div class="bg-gray-50 px-6 py-3 flex items-center justify-between">
                        <div>
                            <span class="font-semibold text-gray-900">{{ owner.name }}</span>
                            <span v-if="owner.phone" class="ml-3 text-sm text-gray-500">{{ owner.phone }}</span>
                        </div>
                        <span class="text-sm text-gray-400">{{ owner.bookings.length }} reserva(s)</span>
                    </div>

                    <!-- Bookings -->
                    <div class="divide-y divide-gray-100">
                        <div v-for="booking in owner.bookings" :key="booking.id" class="px-6 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <span class="font-medium text-gray-900">{{ booking.dog.name }}</span>
                                        <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ typeLabel[booking.type] }}</span>
                                        <span v-if="booking.subtype && booking.type === 'aula'" class="rounded bg-purple-100 px-2 py-0.5 text-xs text-purple-700">{{ subtypeLabel[booking.subtype] ?? booking.subtype }}</span>
                                        <span v-if="booking.subtype && booking.type === 'pack_creche'" class="rounded bg-blue-100 px-2 py-0.5 text-xs text-blue-700">{{ booking.subtype }} sessões</span>
                                        <span v-if="['atl','hotel'].includes(booking.type) && !booking.is_regular" class="rounded bg-orange-100 px-2 py-0.5 text-xs text-orange-700">Não Regular</span>
                                        <span v-if="booking.pet_taxi" class="rounded bg-indigo-100 px-2 py-0.5 text-xs text-indigo-700">Pet Taxi</span>
                                    </div>
                                    <p class="text-sm text-gray-500">
                                        <template v-if="booking.type === 'hotel'">
                                            {{ booking.start_date }} → {{ booking.end_date }}
                                        </template>
                                        <template v-else-if="booking.type === 'pack_creche'">
                                            A partir de {{ booking.start_date }}
                                        </template>
                                        <template v-else>
                                            A partir de {{ booking.start_date }} · {{ booking.frequency }}
                                        </template>
                                    </p>

                                    <!-- Payment info -->
                                    <div v-if="booking.payment" class="mt-2">
                                        <div class="flex items-center gap-2">
                                            <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusColor[booking.payment.status]]">
                                                {{ booking.payment.status }}
                                            </span>
                                            <span class="text-sm font-semibold text-gray-900">{{ booking.payment.amount }}€</span>
                                            <span class="text-xs text-gray-400">via {{ booking.payment.method === 'mbway' ? 'MBWay' : 'Multibanco' }}</span>
                                        </div>

                                        <!-- Multibanco reference -->
                                        <div v-if="booking.payment.method === 'multibanco' && booking.payment.status === 'pendente'" class="mt-2 rounded bg-gray-50 px-3 py-2 text-sm flex items-center justify-between gap-4">
                                            <div>
                                                <span class="text-gray-500">Entidade:</span> <strong>{{ booking.payment.mb_entity }}</strong>
                                                &nbsp;·&nbsp;
                                                <span class="text-gray-500">Referência:</span> <strong>{{ booking.payment.mb_reference }}</strong>
                                            </div>
                                            <button
                                                @click="router.post(route('staff.payments.check', booking.payment.id))"
                                                class="shrink-0 text-xs text-indigo-600 hover:underline"
                                            >
                                                Verificar pagamento
                                            </button>
                                        </div>

                                        <!-- MBWay resend -->
                                        <button
                                            v-if="booking.payment.method === 'mbway' && booking.payment.status === 'pendente'"
                                            @click="resend(booking.payment)"
                                            class="mt-2 text-sm text-indigo-600 hover:underline"
                                        >
                                            Reenviar notificação MBWay
                                        </button>

                                        <!-- Sandbox simulate button -->
                                        <div v-if="isSandbox && booking.payment.status === 'pendente'" class="mt-2 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 flex items-center justify-between">
                                            <p class="text-xs text-amber-700">Simular pagamento (sandbox)</p>
                                            <button
                                                @click="router.post(route('staff.payments.simulate', booking.payment.id))"
                                                class="rounded-md bg-amber-500 px-3 py-1 text-xs font-medium text-white hover:bg-amber-600"
                                            >
                                                Pagar agora
                                            </button>
                                        </div>

                                        <p v-if="booking.payment.status === 'pago'" class="mt-1 text-xs text-green-600">
                                            Pago em {{ booking.payment.paid_at }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Generate payment button -->
                                <div class="shrink-0 flex flex-col items-end gap-1">
                                    <button
                                        v-if="!booking.payment || ['expirado', 'falhado'].includes(booking.payment.status)"
                                        @click="activeBooking = booking.id"
                                        class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700"
                                    >
                                        Gerar Pagamento
                                    </button>
                                    <button
                                        v-if="booking.payment?.status === 'pendente'"
                                        @click="activeBooking = booking.id"
                                        class="text-xs text-indigo-500 hover:text-indigo-700 hover:underline font-medium"
                                    >
                                        Alterar método
                                    </button>
                                </div>
                            </div>

                            <!-- Generate payment form -->
                            <div v-if="activeBooking === booking.id" class="mt-4 rounded-lg border border-gray-200 p-4 bg-gray-50">
                                <p class="text-sm font-medium text-gray-700 mb-3">Método de pagamento</p>
                                <div class="flex gap-4 mb-3">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" v-model="resolveForm(booking.id).method" value="mbway" class="text-indigo-600" />
                                        <span class="text-sm">MBWay</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" v-model="resolveForm(booking.id).method" value="multibanco" class="text-indigo-600" />
                                        <span class="text-sm">Multibanco</span>
                                    </label>
                                </div>
                                <div v-if="resolveForm(booking.id).method === 'mbway'" class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700">Nº de telemóvel</label>
                                    <input
                                        v-model="resolveForm(booking.id).mbway_phone"
                                        type="text"
                                        placeholder="9XXXXXXXX"
                                        class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        @click="generate(booking)"
                                        :disabled="resolveForm(booking.id).processing"
                                        class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                                    >
                                        Enviar Pedido
                                    </button>
                                    <button @click="activeBooking = null" class="text-sm text-gray-500 hover:underline">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
