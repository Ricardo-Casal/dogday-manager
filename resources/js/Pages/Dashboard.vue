<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    schedule:  Object,
    weekStart: String,
});

// ─── Config ───────────────────────────────────────────────────────────────────
const HOUR_START = 7;   // 07:00
const HOUR_END   = 24;  // 24:00
const PX_PER_HOUR = 64;
const TOTAL_HOURS = HOUR_END - HOUR_START;

// ─── Navigation ───────────────────────────────────────────────────────────────
function navigate(offset) {
    const d = new Date(props.weekStart);
    d.setDate(d.getDate() + offset);
    router.get(route('dashboard'), { week: d.toISOString().slice(0, 10) }, { preserveState: false });
}

const weekLabel = computed(() => {
    const start = new Date(props.weekStart);
    const end   = new Date(props.weekStart); end.setDate(end.getDate() + 6);
    return `${start.toLocaleDateString('pt-PT', { day: 'numeric', month: 'long' })} – ${end.toLocaleDateString('pt-PT', { day: 'numeric', month: 'long', year: 'numeric' })}`;
});

const isCurrentWeek = computed(() => {
    const today = new Date().toISOString().slice(0, 10);
    const end   = new Date(props.weekStart); end.setDate(end.getDate() + 6);
    return today >= props.weekStart && today <= end.toISOString().slice(0, 10);
});

// ─── Days ─────────────────────────────────────────────────────────────────────
const days = computed(() => {
    const today = new Date().toISOString().slice(0, 10);
    return Object.keys(props.schedule).map(date => ({
        date,
        label:   new Date(date).toLocaleDateString('pt-PT', { weekday: 'short' }).replace('.', ''),
        dayNum:  new Date(date).getDate(),
        isToday: date === today,
    }));
});

const hours = Array.from({ length: TOTAL_HOURS }, (_, i) => HOUR_START + i);

// ─── Time helpers ─────────────────────────────────────────────────────────────
function timeToMinutes(t) {
    if (!t) return null;
    const [h, m] = t.split(':').map(Number);
    return h * 60 + m;
}

function timeToTop(t) {
    const mins = timeToMinutes(t);
    if (mins === null) return null;
    return ((mins - HOUR_START * 60) / 60) * PX_PER_HOUR;
}

function eventHeight(startTime, endTime) {
    if (!startTime) return PX_PER_HOUR;
    if (!endTime)   return PX_PER_HOUR * 0.9;
    const diff = timeToMinutes(endTime) - timeToMinutes(startTime);
    return Math.max((diff / 60) * PX_PER_HOUR, 30);
}

// ─── Event classification ─────────────────────────────────────────────────────
function processDay(events) {
    const timed   = events.filter(e => e.start_time);
    const untimed = events.filter(e => !e.start_time);

    // For untimed ATL: group by pet_taxi
    const atlTaxi   = untimed.filter(e => e.type === 'atl' && e.pet_taxi);
    const atlNormal = untimed.filter(e => e.type === 'atl' && !e.pet_taxi);
    const otherUntimed = untimed.filter(e => e.type !== 'atl');

    return { timed, atlTaxi, atlNormal, otherUntimed };
}

// ─── Expand toggles ──────────────────────────────────────────────────────────
const expanded = ref({});
function toggle(key) { expanded.value[key] = !expanded.value[key]; }

// ─── Labels & colours ────────────────────────────────────────────────────────
const subtypeLabel = {
    individual: 'Individual', domicilio: 'Domicílio',
    grupo: 'Grupo', avaliacao_comportamental: 'Avaliação',
};

const typeLabel = {
    aula: 'Treino', integracao: 'Integração', pack_creche: 'Pack',
    pet_sitting: 'Pet Sitting', dog_walking: 'Dog Walking', banho: 'Banho',
};

// Tailwind classes per event type
function eventClass(e) {
    if (e.type === 'atl')         return e.pet_taxi ? 'bg-sky-500 text-white' : 'bg-sky-200 text-sky-900';
    if (e.type === 'hotel') {
        if (e.event_type === 'checkin')  return 'bg-emerald-500 text-white';
        if (e.event_type === 'stay')     return 'bg-emerald-100 text-emerald-800';
        if (e.event_type === 'checkout') return 'bg-orange-400 text-white';
    }
    if (e.type === 'aula')        return 'bg-purple-500 text-white';
    if (e.type === 'integracao')  return 'bg-indigo-500 text-white';
    if (e.type === 'pack_creche') return 'bg-cyan-500 text-white';
    if (e.type === 'pet_sitting') return 'bg-yellow-400 text-yellow-900';
    if (e.type === 'dog_walking') return 'bg-lime-500 text-white';
    if (e.type === 'banho')       return 'bg-pink-400 text-white';
    return 'bg-gray-400 text-white';
}

function eventIcon(e) {
    if (e.type === 'atl' && e.pet_taxi)                 return '🚗';
    if (e.type === 'atl')                               return '🏠';
    if (e.type === 'hotel' && e.event_type === 'checkin')  return '🏨↓';
    if (e.type === 'hotel' && e.event_type === 'stay')     return '🏨';
    if (e.type === 'hotel' && e.event_type === 'checkout') return '🏨↑';
    if (e.type === 'aula')        return '🎓';
    if (e.type === 'integracao')  return '🐾';
    if (e.type === 'pack_creche') return '📦';
    if (e.type === 'pet_sitting') return '🏡';
    if (e.type === 'dog_walking') return '🦮';
    if (e.type === 'banho')       return '🛁';
    return '';
}

function eventLabel(e) {
    if (e.type === 'hotel') {
        if (e.event_type === 'checkin')  return 'Entrada';
        if (e.event_type === 'stay')     return 'Em estadia';
        if (e.event_type === 'checkout') return 'Saída';
    }
    if (e.type === 'atl') return e.pet_taxi ? 'Pet Taxi' : 'Creche';
    if (e.type === 'aula' && e.subtype) return subtypeLabel[e.subtype] ?? 'Treino';
    return typeLabel[e.type] ?? e.type;
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <h2 class="text-xl font-semibold text-gray-800">Horário Semanal</h2>
                <div class="flex items-center gap-2">
                    <button @click="navigate(-7)" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50">← Anterior</button>
                    <span class="text-sm font-medium text-gray-700 px-1">{{ weekLabel }}</span>
                    <button @click="navigate(7)"  class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50">Seguinte →</button>
                    <button v-if="!isCurrentWeek" @click="router.get(route('dashboard'))" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">Hoje</button>
                </div>
            </div>
        </template>

        <div class="py-4">
            <div class="px-2 sm:px-4 lg:px-6">

                <!-- Legend -->
                <div class="mb-4 flex flex-wrap gap-1.5 items-center">
                    <span class="text-xs text-gray-400 mr-1">Legenda:</span>
                    <span v-for="[cls, lbl] in [
                        ['bg-sky-500 text-white','🚗 Pet Taxi ATL'],
                        ['bg-sky-200 text-sky-900','🏠 Creche ATL'],
                        ['bg-emerald-500 text-white','🏨↓ Entrada Hotel'],
                        ['bg-emerald-100 text-emerald-800','🏨 Em Hotel'],
                        ['bg-orange-400 text-white','🏨↑ Saída Hotel'],
                        ['bg-purple-500 text-white','🎓 Treino'],
                        ['bg-indigo-500 text-white','🐾 Integração'],
                        ['bg-cyan-500 text-white','📦 Pack'],
                        ['bg-yellow-400 text-yellow-900','🏡 Pet Sitting'],
                        ['bg-lime-500 text-white','🦮 Dog Walking'],
                        ['bg-pink-400 text-white','🛁 Banho'],
                    ]" :key="lbl" :class="['rounded-full px-2 py-0.5 text-xs font-medium', cls]">{{ lbl }}</span>
                </div>

                <!-- Calendar grid -->
                <div class="overflow-x-auto">
                    <div class="flex" style="min-width: 700px;">

                        <!-- Time column -->
                        <div class="shrink-0 w-12 pt-11">
                            <div
                                v-for="h in hours" :key="h"
                                :style="{ height: PX_PER_HOUR + 'px' }"
                                class="flex items-start justify-end pr-2"
                            >
                                <span class="text-xs text-gray-400 leading-none -mt-2">{{ String(h).padStart(2,'0') }}:00</span>
                            </div>
                        </div>

                        <!-- Day columns -->
                        <div class="flex flex-1 gap-1">
                            <div v-for="day in days" :key="day.date" class="flex-1 min-w-0">

                                <!-- Day header -->
                                <div :class="['rounded-t-lg px-1 py-1.5 text-center mb-1 h-10 flex flex-col justify-center', day.isToday ? 'bg-indigo-600' : 'bg-white shadow-sm']">
                                    <p :class="['text-xs font-semibold uppercase tracking-wide', day.isToday ? 'text-indigo-200' : 'text-gray-400']">{{ day.label }}</p>
                                    <p :class="['text-base font-bold leading-none', day.isToday ? 'text-white' : 'text-gray-900']">{{ day.dayNum }}</p>
                                </div>

                                <!-- Untimed events: ATL groups -->
                                <div v-if="processDay(schedule[day.date]).atlTaxi.length || processDay(schedule[day.date]).atlNormal.length || processDay(schedule[day.date]).otherUntimed.length"
                                     class="mb-1 space-y-0.5">

                                    <!-- ATL Pet Taxi group -->
                                    <div v-if="processDay(schedule[day.date]).atlTaxi.length">
                                        <button @click="toggle(`${day.date}-taxi`)"
                                                class="w-full rounded-md px-1.5 py-1 text-left text-xs font-semibold bg-sky-500 text-white flex items-center justify-between">
                                            <span>🚗 PET TAXI</span>
                                            <span class="rounded-full bg-white/25 px-1 font-bold text-xs">{{ processDay(schedule[day.date]).atlTaxi.length }}</span>
                                        </button>
                                        <div v-if="expanded[`${day.date}-taxi`]" class="rounded-b-md bg-sky-50 border border-sky-200 px-2 py-1 space-y-0.5">
                                            <p v-for="e in processDay(schedule[day.date]).atlTaxi" :key="e.id" class="text-xs text-sky-900">
                                                <span class="font-semibold">{{ e.dog.name }}</span> <span class="text-sky-600 text-xs">({{ e.owner.name }})</span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- ATL Vem deixar group -->
                                    <div v-if="processDay(schedule[day.date]).atlNormal.length">
                                        <button @click="toggle(`${day.date}-normal`)"
                                                class="w-full rounded-md px-1.5 py-1 text-left text-xs font-semibold bg-sky-200 text-sky-900 flex items-center justify-between">
                                            <span>🏠 Vem deixar</span>
                                            <span class="rounded-full bg-sky-400/25 px-1 font-bold text-xs">{{ processDay(schedule[day.date]).atlNormal.length }}</span>
                                        </button>
                                        <div v-if="expanded[`${day.date}-normal`]" class="rounded-b-md bg-sky-50 border border-sky-200 px-2 py-1 space-y-0.5">
                                            <p v-for="e in processDay(schedule[day.date]).atlNormal" :key="e.id" class="text-xs text-sky-900">
                                                <span class="font-semibold">{{ e.dog.name }}</span> <span class="text-sky-600 text-xs">({{ e.owner.name }})</span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Other untimed -->
                                    <div v-for="e in processDay(schedule[day.date]).otherUntimed" :key="`u-${e.id}`"
                                         :class="['rounded-md px-1.5 py-1 text-xs', eventClass(e)]">
                                        <span class="font-semibold">{{ e.dog.name }}</span><span class="opacity-80"> ({{ e.owner.name }})</span>
                                        <span class="opacity-70 text-xs"> · {{ eventLabel(e) }}</span>
                                    </div>
                                </div>

                                <!-- Time grid -->
                                <div class="relative bg-white rounded-b-lg border border-gray-100"
                                     :style="{ height: (TOTAL_HOURS * PX_PER_HOUR) + 'px' }">

                                    <!-- Hour lines -->
                                    <div v-for="h in hours" :key="h"
                                         class="absolute w-full border-t border-gray-100"
                                         :style="{ top: ((h - HOUR_START) * PX_PER_HOUR) + 'px' }">
                                    </div>

                                    <!-- Half-hour lines -->
                                    <div v-for="h in hours" :key="`h-${h}`"
                                         class="absolute w-full border-t border-gray-50"
                                         :style="{ top: ((h - HOUR_START) * PX_PER_HOUR + PX_PER_HOUR / 2) + 'px' }">
                                    </div>

                                    <!-- Timed events -->
                                    <template v-for="e in processDay(schedule[day.date]).timed" :key="`t-${e.id}`">
                                        <div
                                            :class="['absolute inset-x-0.5 rounded-md px-1 py-0.5 overflow-hidden text-xs shadow-sm', eventClass(e)]"
                                            :style="{
                                                top:    timeToTop(e.start_time) + 'px',
                                                height: eventHeight(e.start_time, e.end_time) + 'px',
                                            }"
                                        >
                                            <p class="font-bold leading-tight truncate">{{ eventIcon(e) }} {{ e.dog.name }} <span class="font-normal opacity-80">({{ e.owner.name }})</span></p>
                                            <p class="opacity-70 text-xs leading-tight">
                                                {{ e.start_time }}<template v-if="e.end_time"> – {{ e.end_time }}</template>
                                                · {{ eventLabel(e) }}
                                            </p>
                                        </div>
                                    </template>

                                    <!-- Current time indicator (today only) -->
                                    <div v-if="day.isToday"
                                         class="absolute inset-x-0 flex items-center pointer-events-none z-10"
                                         :style="{ top: timeToTop(new Date().toTimeString().slice(0,5)) + 'px' }">
                                        <div class="h-2 w-2 rounded-full bg-red-500 -ml-1 shrink-0"></div>
                                        <div class="flex-1 border-t-2 border-red-500"></div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
