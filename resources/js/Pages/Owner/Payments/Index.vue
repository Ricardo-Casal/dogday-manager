<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    payments: Array,
});

const flash = usePage().props.flash;

function resend(payment) {
    router.post(route('owner.payments.resend', payment.id));
}

const changingPayment = ref(null);
const changeForm = useForm({ method: 'multibanco', mbway_phone: '' });

function openChange(payment) {
    changingPayment.value = payment.id;
    changeForm.method = 'multibanco';
    changeForm.mbway_phone = '';
}

function submitChange(payment) {
    changeForm.post(route('owner.payments.change-method', payment.id), {
        onSuccess: () => { changingPayment.value = null; },
    });
}

const statusColor = {
    pendente: 'bg-yellow-100 text-yellow-800',
    pago:     'bg-green-100 text-green-800',
    expirado: 'bg-gray-100 text-gray-600',
    falhado:  'bg-red-100 text-red-800',
};

const typeLabel = { atl: 'ATL', hotel: 'Hotel', aula: 'Aula' };
</script>

<template>
    <Head title="Os meus pagamentos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Os meus pagamentos</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div v-if="payments.length === 0" class="rounded-lg bg-white p-12 text-center shadow-sm text-gray-400">
                    Não tem pagamentos registados.
                </div>

                <div v-else class="space-y-4">
                    <div v-for="payment in payments" :key="payment.id" class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-medium text-gray-900">{{ payment.booking.dog.name }}</span>
                                    <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ typeLabel[payment.booking.type] }}</span>
                                </div>
                                <p class="text-sm font-semibold text-gray-800">{{ payment.amount }}€</p>
                                <p class="text-xs text-gray-400 mt-0.5">via {{ payment.method === 'mbway' ? 'MBWay' : 'Multibanco' }}</p>
                            </div>
                            <span :class="['rounded-full px-3 py-1 text-xs font-medium', statusColor[payment.status]]">
                                {{ payment.status }}
                            </span>
                        </div>

                        <!-- Multibanco details -->
                        <div v-if="payment.method === 'multibanco' && payment.status === 'pendente'" class="mt-3 rounded-lg bg-blue-50 p-4">
                            <p class="text-sm font-medium text-blue-900 mb-2">Dados para pagamento no ATM ou homebanking:</p>
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="rounded bg-white p-2">
                                    <p class="text-xs text-gray-500">Entidade</p>
                                    <p class="text-lg font-bold text-gray-900">{{ payment.mb_entity }}</p>
                                </div>
                                <div class="rounded bg-white p-2 col-span-2">
                                    <p class="text-xs text-gray-500">Referência</p>
                                    <p class="text-lg font-bold text-gray-900 tracking-wider">{{ payment.mb_reference }}</p>
                                </div>
                            </div>
                            <p class="text-xs text-blue-700 mt-2">Valor: <strong>{{ payment.amount }}€</strong></p>
                        </div>

                        <!-- MBWay -->
                        <div v-if="payment.method === 'mbway' && payment.status === 'pendente'" class="mt-3 rounded-lg bg-green-50 p-4">
                            <p class="text-sm text-green-800">
                                Foi enviado um pedido de pagamento para o seu telemóvel.
                                Se não recebeu ou expirou, pode reenviar:
                            </p>
                            <button
                                @click="resend(payment)"
                                class="mt-2 rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700"
                            >
                                Reenviar pedido MBWay
                            </button>
                        </div>

                        <!-- Change method -->
                        <div v-if="['pendente', 'falhado'].includes(payment.status)" class="mt-3">
                            <button
                                v-if="changingPayment !== payment.id"
                                @click="openChange(payment)"
                                class="text-sm text-indigo-600 hover:underline"
                            >
                                Alterar método de pagamento
                            </button>

                            <div v-else class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-3">
                                <p class="text-sm font-medium text-gray-700">Escolha o novo método:</p>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" v-model="changeForm.method" value="multibanco" class="text-indigo-600" />
                                        <span class="text-sm">Multibanco</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" v-model="changeForm.method" value="mbway" class="text-indigo-600" />
                                        <span class="text-sm">MBWay</span>
                                    </label>
                                </div>
                                <div v-if="changeForm.method === 'mbway'">
                                    <label class="block text-sm font-medium text-gray-700">Nº de telemóvel</label>
                                    <input
                                        v-model="changeForm.mbway_phone"
                                        type="text"
                                        placeholder="9XXXXXXXX"
                                        class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>
                                <p v-if="changeForm.errors.payment" class="text-xs text-red-600">{{ changeForm.errors.payment }}</p>
                                <div class="flex gap-2">
                                    <button
                                        @click="submitChange(payment)"
                                        :disabled="changeForm.processing"
                                        class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                                    >
                                        Confirmar
                                    </button>
                                    <button @click="changingPayment = null" class="text-sm text-gray-500 hover:underline">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <p v-if="payment.status === 'pago'" class="mt-2 text-xs text-green-600">
                            Pago em {{ new Date(payment.paid_at).toLocaleDateString('pt-PT') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
