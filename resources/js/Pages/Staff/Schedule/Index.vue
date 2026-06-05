<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    schedule:  Object,  // { 'YYYY-MM-DD': [ ...events ] }
    weekStart: String,
});

// ─── Navigation ───────────────────────────────────────────────────────────────
function navigate(offset) {
    const d = new Date(props.weekStart);
    d.setDate(d.getDate() + offset);
    router.get(route('staff.schedule.index'), { week: d.toISOString().slice(0, 10) }, { preserveState: false });
}

const weekLabel = computed(() => {
    const start = new Date(props.weekStart);
    const end   = new Date(props.weekStart);
    end.setDate(end.getDate() + 6);
    const fmt = (d) => d.toLocaleDateString('pt-PT', { day: 'numeric', month: 'short' });
    return `${fmt(start)} – ${fmt(end)}`;
});

const isCurrentWeek = computed(() => {
    const today = new Date().toISOString().slice(0, 10);
    const start = new Date(props.weekStart);
    const end   = new Date(props.weekStart); end.setDate(end.getDate() + 6);
    return today >= start.toISOString().slice(0, 10) && today <= end.toISOString().slice(0, 10);
});

// ─── Day labels ───────────────────────────────────────────────────────────────
const days = computed(() => {
    const today = new Date().toISOString().slice(0, 10);
    return Object.keys(props.schedule).map(date => {
        const d = new Date(date);
        return {
            date,
            label:   d.toLocaleDateString('pt-PT', { weekday: 'short' }).replace('.', ''),
            dayNum:  d.getDate(),
            isToday: date === today,
        };
    });
});

// ─── Event processing per day ─────────────────────────────────────────────────
function processDay(events) {
    const atl         = events.filter(e => e.type === 'atl');
    const hotelIn     = events.filter(e => e.type === 'hotel' && e.event_type === 'checkin');
    const hotelStay   = events.filter(e => e.type === 'hotel' && e.event_type === 'stay');
    const hotelOut    = events.filter(e => e.type === 'hotel' && e.event_type === 'checkout');
    const sessions    = events.filter(e => ['aula', 'integracao', 'pack_creche'].includes(e.type));
    const outros      = events.filter(e => ['pet_sitting', 'dog_walking', 'banho'].includes(e.type));

    return {
        atlTaxi:   atl.filter(e => e.pet_taxi),
        atlNormal: atl.filter(e => !e.pet_taxi),
        hotelIn,
        hotelStay,
        hotelOut,
        sessions,
        outros,
        isEmpty: events.length === 0,
    };
}

// ─── Expand toggles ──────────────────────────────────────────────────────────
const expanded = ref({});

function toggleGroup(key) {
    expanded.value[key] = !expanded.value[key];
}

function groupKey(date, group) {
    return `${date}-${group}`;
}

// ─── Labels & colours ────────────────────────────────────────────────────────
const subtypeLabel = {
    individual:               'Individual',
    domicilio:                'Domicílio',
    grupo:                    'Grupo',
    avaliacao_comportamental: 'Avaliação Comportamental',
};

const serviceColor = {
    atl_taxi:    'bg-sky-500 text-white',
    atl_normal:  'bg-sky-200 text-sky-900',
    hotel_in:    'bg-emerald-500 text-white',
    hotel_stay:  'bg-emerald-100 text-emerald-800',
    hotel_out:   'bg-orange-400 text-white',
    aula:        'bg-purple-500 text-white',
    integracao:  'bg-indigo-500 text-white',
    pack_creche: 'bg-cyan-500 text-white',
    pet_sitting: 'bg-yellow-400 text-yellow-900',
    dog_walking: 'bg-lime-500 text-white',
    banho:       'bg-pink-400 text-white',
};

function sessionColor(type) {
    return serviceColor[type] ?? 'bg-gray-400 text-white';
}

const typeLabel = {
    aula:        'Treino',
    integracao:  'Integração',
    pack_creche: 'Pack Creche',
    pet_sitting: 'Pet Sitting',
    dog_walking: 'Dog Walking',
    banho:       'Banho',
};
</script>

<template>
    <Head title="Horário" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Horário Semanal</h2>
                <div class="flex items-center gap-3">
                    <button
                        @click="navigate(-7)"
                        class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50"
                    >← Anterior</button>
                    <span class="text-sm font-medium text-gray-700">{{ weekLabel }}</span>
                    <button
                        @click="navigate(7)"
                        class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50"
                    >Seguinte →</button>
                    <button
                        v-if="!isCurrentWeek"
                        @click="router.get(route('staff.schedule.index'))"
                        class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700"
                    >Hoje</button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto px-4 sm:px-6 lg:px-8" style="max-width: 100%;">

                <!-- Week grid -->
                <div class="grid grid-cols-7 gap-2">

                    <!-- Day column -->
                    <div v-for="day in days" :key="day.date" class="min-h-[600px]">

                        <!-- Day header -->
                        <div :class="['rounded-t-lg px-2 py-2 text-center mb-2', day.isToday ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700']">
                            <p class="text-xs font-medium uppercase tracking-wide opacity-75">{{ day.label }}</p>
                            <p :class="['text-xl font-bold leading-none mt-0.5', day.isToday ? 'text-white' : 'text-gray-900']">{{ day.dayNum }}</p>
                        </div>

                        <!-- Day content -->
                        <div class="space-y-1.5">
                            <template v-if="!processDay(schedule[day.date]).isEmpty">

                                <!-- ATL: Pet Taxi group -->
                                <div v-if="processDay(schedule[day.date]).atlTaxi.length > 0">
                                    <button
                                        @click="toggleGroup(groupKey(day.date, 'taxi'))"
                                        :class="['w-full rounded-md px-2 py-2 text-left text-xs font-semibold transition', serviceColor.atl_taxi]"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span>🚗 PET TAXI</span>
                                            <span class="rounded-full bg-white/30 px-1.5 py-0.5 text-xs">
                                                {{ processDay(schedule[day.date]).atlTaxi.length }}
                                            </span>
                                        </div>
                                    </button>
                                    <!-- Expanded dog list -->
                                    <div v-if="expanded[groupKey(day.date, 'taxi')]" class="mt-0.5 rounded-md bg-sky-50 border border-sky-200 px-2 py-1.5 space-y-1">
                                        <div v-for="e in processDay(schedule[day.date]).atlTaxi" :key="e.id" class="text-xs text-sky-900">
                                            <span class="font-medium">{{ e.dog.name }}</span>
                                            <span class="text-sky-600"> ({{ e.owner.name }})</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- ATL: Normal group (vem deixar) -->
                                <div v-if="processDay(schedule[day.date]).atlNormal.length > 0">
                                    <button
                                        @click="toggleGroup(groupKey(day.date, 'normal'))"
                                        :class="['w-full rounded-md px-2 py-2 text-left text-xs font-semibold transition', serviceColor.atl_normal]"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span>🏠 Vem deixar</span>
                                            <span class="rounded-full bg-sky-400/30 px-1.5 py-0.5 text-xs">
                                                {{ processDay(schedule[day.date]).atlNormal.length }}
                                            </span>
                                        </div>
                                    </button>
                                    <div v-if="expanded[groupKey(day.date, 'normal')]" class="mt-0.5 rounded-md bg-sky-50 border border-sky-200 px-2 py-1.5 space-y-1">
                                        <div v-for="e in processDay(schedule[day.date]).atlNormal" :key="e.id" class="text-xs text-sky-900">
                                            <span class="font-medium">{{ e.dog.name }}</span>
                                            <span class="text-sky-600"> ({{ e.owner.name }})</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hotel check-ins -->
                                <div
                                    v-for="e in processDay(schedule[day.date]).hotelIn"
                                    :key="`in-${e.id}`"
                                    :class="['rounded-md px-2 py-2 text-xs font-medium', serviceColor.hotel_in]"
                                >
                                    <p class="text-xs opacity-75 mb-0.5">🏨 Entrada</p>
                                    <p class="font-semibold">{{ e.dog.name }}</p>
                                    <p class="opacity-80">{{ e.owner.name }}</p>
                                </div>

                                <!-- Hotel stay -->
                                <div
                                    v-for="e in processDay(schedule[day.date]).hotelStay"
                                    :key="`stay-${e.id}`"
                                    :class="['rounded-md px-2 py-2 text-xs font-medium', serviceColor.hotel_stay]"
                                >
                                    <p class="text-xs opacity-60 mb-0.5">🏨 Em estadia</p>
                                    <p class="font-semibold">{{ e.dog.name }}</p>
                                    <p class="opacity-70">{{ e.owner.name }}</p>
                                </div>

                                <!-- Sessions (aula, integracao, pack_creche) -->
                                <div
                                    v-for="e in processDay(schedule[day.date]).sessions"
                                    :key="`session-${e.id}`"
                                    :class="['rounded-md px-2 py-2 text-xs font-medium', sessionColor(e.type)]"
                                >
                                    <p class="opacity-80 mb-0.5 text-xs">{{ typeLabel[e.type] }}<template v-if="e.subtype && e.type === 'aula'"> · {{ subtypeLabel[e.subtype] ?? e.subtype }}</template><template v-if="e.type === 'pack_creche' && e.subtype"> · {{ e.subtype }} sess.</template></p>
                                    <p class="font-semibold">{{ e.dog.name }}</p>
                                    <p class="opacity-80">{{ e.owner.name }}</p>
                                </div>

                                <!-- Hotel check-outs -->
                                <div
                                    v-for="e in processDay(schedule[day.date]).hotelOut"
                                    :key="`out-${e.id}`"
                                    :class="['rounded-md px-2 py-2 text-xs font-medium', serviceColor.hotel_out]"
                                >
                                    <p class="text-xs opacity-75 mb-0.5">🏨 Saída</p>
                                    <p class="font-semibold">{{ e.dog.name }}</p>
                                    <p class="opacity-80">{{ e.owner.name }}</p>
                                </div>

                                <!-- Outros (dog_walking, banho, pet_sitting) -->
                                <div
                                    v-for="e in processDay(schedule[day.date]).outros"
                                    :key="`outro-${e.id}`"
                                    :class="['rounded-md px-2 py-2 text-xs font-medium', sessionColor(e.type)]"
                                >
                                    <p class="opacity-80 mb-0.5 text-xs">{{ typeLabel[e.type] }}</p>
                                    <p class="font-semibold">{{ e.dog.name }}</p>
                                    <p class="opacity-80">{{ e.owner.name }}</p>
                                </div>

                            </template>

                            <!-- Empty day -->
                            <div v-else class="rounded-md border border-dashed border-gray-200 py-6 text-center text-xs text-gray-300">
                                —
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Legend -->
                <div class="mt-6 flex flex-wrap gap-3 rounded-lg bg-white p-4 shadow-sm">
                    <span class="text-xs font-medium text-gray-500 mr-2">Legenda:</span>
                    <span v-for="[key, label] in [['atl_taxi','PET TAXI'], ['atl_normal','Creche (ATL)'], ['hotel_in','Entrada Hotel'], ['hotel_stay','Em Hotel'], ['hotel_out','Saída Hotel'], ['aula','Treino'], ['integracao','Integração'], ['pack_creche','Pack Creche'], ['pet_sitting','Pet Sitting'], ['dog_walking','Dog Walking'], ['banho','Banho']]"
                          :key="key"
                          :class="['rounded-full px-2.5 py-1 text-xs font-medium', serviceColor[key]]">
                        {{ label }}
                    </span>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
