<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    attendances: Array,
    date: String,
});

const selectedDate = ref(props.date);

function changeDate() {
    router.get(route('dashboard'), { date: selectedDate.value }, { preserveState: false });
}

function removeAttendance(id) {
    if (confirm('Remover este cão do dia?')) {
        router.delete(route('attendances.destroy', id));
    }
}

const typeLabel = { atl: 'ATL', hotel: 'Hotel', aula: 'Aula' };
const typeColor = {
    atl: 'bg-blue-100 text-blue-800',
    hotel: 'bg-purple-100 text-purple-800',
    aula: 'bg-green-100 text-green-800',
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard do Dia
                </h2>
                <Link
                    :href="route('attendances.create', { date: selectedDate })"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    + Adicionar Cão
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <!-- Date picker -->
                <div class="mb-6 flex items-center gap-3">
                    <input
                        type="date"
                        v-model="selectedDate"
                        @change="changeDate"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <span class="text-sm text-gray-500">
                        {{ attendances.length }} cão(es) hoje
                    </span>
                </div>

                <!-- Empty state -->
                <div v-if="attendances.length === 0" class="rounded-lg bg-white p-12 text-center shadow-sm">
                    <p class="text-gray-500">Nenhum cão agendado para este dia.</p>
                    <Link
                        :href="route('attendances.create', { date: selectedDate })"
                        class="mt-4 inline-block text-indigo-600 hover:underline"
                    >
                        Adicionar um cão
                    </Link>
                </div>

                <!-- Attendance list -->
                <div v-else class="space-y-3">
                    <div
                        v-for="attendance in attendances"
                        :key="attendance.id"
                        class="flex items-center justify-between rounded-lg bg-white px-6 py-4 shadow-sm"
                    >
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-lg font-bold text-indigo-700">
                                {{ attendance.dog.name[0].toUpperCase() }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ attendance.dog.name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ attendance.dog.breed ?? 'Raça desconhecida' }} &middot;
                                    Dono: {{ attendance.dog.owner.name }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span :class="['rounded-full px-3 py-1 text-xs font-medium', typeColor[attendance.type]]">
                                {{ typeLabel[attendance.type] }}
                            </span>
                            <button
                                @click="removeAttendance(attendance.id)"
                                class="text-sm text-red-500 hover:text-red-700"
                            >
                                Remover
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
