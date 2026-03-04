<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';
import MotorcycleCard from '../components/catalog/MotorcycleCard.vue';
import AlertMessage from '../components/ui/AlertMessage.vue';
import PageHero from '../components/ui/PageHero.vue';
import { sessionState } from '../session';
import type { Motorcycle } from '../types';

interface CatalogResponse {
  data: Motorcycle[];
  current_page: number;
  last_page: number;
  total: number;
}

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const toggling = ref(false);
const errorText = ref('');
const successText = ref('');

const motorcycles = ref<Motorcycle[]>([]);
const total = ref(0);
const currentPage = ref(1);
const lastPage = ref(1);

const type = ref('');
const search = ref('');
const sort = ref('newest');

const compareIds = ref<number[]>([]);
const favoriteIds = ref<number[]>([]);

const sortOptions = [
  { value: 'newest', label: 'Новинки' },
  { value: 'price_asc', label: 'Цена ↑' },
  { value: 'price_desc', label: 'Цена ↓' },
  { value: 'popular', label: 'Популярные' },
  { value: 'oldest', label: 'Сначала старые' },
];

function syncFromQuery() {
  type.value = typeof route.query.type === 'string' ? route.query.type : '';
  search.value = typeof route.query.search === 'string' ? route.query.search : '';
  sort.value = typeof route.query.sort === 'string' ? route.query.sort : 'newest';
  currentPage.value = Math.max(1, Number(route.query.page ?? 1) || 1);
}

function buildQuery(page = 1) {
  return {
    ...(type.value ? { type: type.value } : {}),
    ...(search.value ? { search: search.value } : {}),
    ...(sort.value && sort.value !== 'newest' ? { sort: sort.value } : {}),
    ...(page > 1 ? { page } : {}),
  };
}

async function loadCompareAndFavorites() {
  try {
    const { data } = await api.get('/compare');
    compareIds.value = data.ids ?? [];
  } catch {
    compareIds.value = [];
  }

  if (sessionState.user) {
    try {
      const { data } = await api.get('/favorites');
      favoriteIds.value = (data.favorites ?? []).map((m: Motorcycle) => m.id);
    } catch {
      favoriteIds.value = [];
    }
  } else {
    favoriteIds.value = [];
  }
}

async function loadCatalog() {
  loading.value = true;
  errorText.value = '';

  try {
    const { data } = await api.get<CatalogResponse>('/catalog', {
      params: {
        page: currentPage.value,
        type: type.value || undefined,
        search: search.value || undefined,
        sort: sort.value,
      },
    });

    motorcycles.value = data.data ?? [];
    currentPage.value = data.current_page ?? 1;
    lastPage.value = data.last_page ?? 1;
    total.value = data.total ?? motorcycles.value.length;
    await loadCompareAndFavorites();
  } catch {
    errorText.value = 'Не удалось загрузить каталог.';
  } finally {
    loading.value = false;
  }
}

async function applyFilters(page = 1) {
  await router.push({ path: '/catalog', query: buildQuery(page) });
}

async function toggleCompare(id: number) {
  if (toggling.value) {
    return;
  }

  toggling.value = true;
  errorText.value = '';
  successText.value = '';

  try {
    const { data } = await api.post(`/compare/${id}`);
    compareIds.value = data.ids ?? compareIds.value;
    successText.value = data.message ?? 'Список сравнения обновлен.';
  } catch (error: any) {
    errorText.value = error?.response?.data?.message ?? 'Не удалось изменить список сравнения.';
  } finally {
    toggling.value = false;
  }
}

async function toggleFavorite(id: number) {
  if (!sessionState.user) {
    await router.push('/login');
    return;
  }

  if (toggling.value) {
    return;
  }

  toggling.value = true;
  errorText.value = '';
  successText.value = '';

  try {
    const { data } = await api.post(`/favorites/${id}`);
    if (data.is_favorite) {
      favoriteIds.value = [...new Set([...favoriteIds.value, id])];
    } else {
      favoriteIds.value = favoriteIds.value.filter((itemId) => itemId !== id);
    }
    successText.value = data.message ?? 'Список избранного обновлён.';
  } catch {
    errorText.value = 'Не удалось изменить избранное.';
  } finally {
    toggling.value = false;
  }
}

const noResultsForSearch = computed(() => !loading.value && Boolean(search.value.trim()) && motorcycles.value.length === 0);

watch(
  () => route.query,
  async () => {
    syncFromQuery();
    await loadCatalog();
  },
  { deep: true },
);

onMounted(async () => {
  syncFromQuery();
  await loadCatalog();
});
</script>

<template>
  <div>
    <PageHero
      background-image="https://commons.wikimedia.org/wiki/Special:FilePath/Motocross-action.jpg"
      image-alt="Catalog Background"
      image-class="opacity-30 scale-110"
      overlay-class="bg-gradient-to-b from-dark via-dark/80 to-dark"
      :compact="false"
      center
    >
      <p class="text-primary font-bold text-sm md:text-base tracking-[0.3em] uppercase mb-4 pl-1">Наш Гараж</p>
      <h1 class="text-5xl md:text-7xl font-bold font-display italic text-white leading-none mb-6 tracking-tighter shadow-orange-glow">
        <span class="text-gradient">ТОВАРЫ</span> <span class="text-transparent text-stroke">AVANTIS</span>
      </h1>
      <p class="max-w-2xl mx-auto text-gray-400 text-lg md:text-xl font-light leading-relaxed">
        Только проверенные бойцы. Эндуро, квадроциклы и комплектующие, готовые к самым жестким испытаниям.
      </p>
    </PageHero>

    <div class="bg-dark min-h-screen pb-24 relative">
      <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-white/10 to-transparent" />

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-20">
        <div class="mb-10">
          <form class="flex gap-3 max-w-2xl mx-auto" @submit.prevent="applyFilters(1)">
            <div class="relative flex-1">
              <input
                v-model="search"
                type="text"
                class="w-full bg-dark-lighter border border-white/10 text-white px-5 py-3 pl-12 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors placeholder-gray-600 font-medium"
                placeholder="Поиск по названию, модели..."
              />
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" /></svg>
            </div>
            <button type="submit" class="btn btn-primary px-6">
              <span>Найти</span>
            </button>
          </form>
        </div>

        <div class="flex flex-wrap justify-center gap-4 mb-8">
          <button
            class="px-8 py-3 font-display font-bold uppercase tracking-wider text-sm transition-all duration-300 border border-white/10 transform -skew-x-12"
            :class="!type ? 'bg-primary border-primary text-white hover:bg-primary/90' : 'bg-dark-lighter text-gray-400 hover:border-primary hover:text-primary hover:bg-white/5'"
            @click="type = ''; applyFilters(1)"
          >
            <span class="inline-block skew-x-12">Все</span>
          </button>
          <button
            class="px-8 py-3 font-display font-bold uppercase tracking-wider text-sm transition-all duration-300 border border-white/10 transform -skew-x-12"
            :class="type === 'Enduro' ? 'bg-primary border-primary text-white hover:bg-primary/90' : 'bg-dark-lighter text-gray-400 hover:border-primary hover:text-primary hover:bg-white/5'"
            @click="type = 'Enduro'; applyFilters(1)"
          >
            <span class="inline-block skew-x-12">Эндуро</span>
          </button>
          <button
            class="px-8 py-3 font-display font-bold uppercase tracking-wider text-sm transition-all duration-300 border border-white/10 transform -skew-x-12"
            :class="type === 'ATV' ? 'bg-primary border-primary text-white hover:bg-primary/90' : 'bg-dark-lighter text-gray-400 hover:border-primary hover:text-primary hover:bg-white/5'"
            @click="type = 'ATV'; applyFilters(1)"
          >
            <span class="inline-block skew-x-12">Квадроциклы</span>
          </button>
          <button
            class="px-8 py-3 font-display font-bold uppercase tracking-wider text-sm transition-all duration-300 border border-white/10 transform -skew-x-12"
            :class="type === 'Parts' ? 'bg-primary border-primary text-white hover:bg-primary/90' : 'bg-dark-lighter text-gray-400 hover:border-primary hover:text-primary hover:bg-white/5'"
            @click="type = 'Parts'; applyFilters(1)"
          >
            <span class="inline-block skew-x-12">Запчасти</span>
          </button>
        </div>

        <div class="flex flex-wrap justify-center gap-3 mb-16">
          <button
            v-for="option in sortOptions"
            :key="option.value"
            class="px-5 py-2 text-xs font-bold uppercase tracking-wider transition-all duration-300 border"
            :class="sort === option.value ? 'border-primary text-primary bg-primary/10' : 'border-white/5 text-gray-500 hover:border-white/20 hover:text-gray-300'"
            @click="sort = option.value; applyFilters(1)"
          >
            {{ option.label }}
          </button>
        </div>

        <AlertMessage v-if="successText" tone="success">{{ successText }}</AlertMessage>
        <AlertMessage v-if="errorText">{{ errorText }}</AlertMessage>
        <div v-if="loading" class="text-center text-gray-500 py-16">Загрузка каталога...</div>

        <div v-if="noResultsForSearch" class="text-center py-16">
          <p class="text-2xl text-gray-500 font-display uppercase">По запросу «{{ search }}» ничего не найдено</p>
          <button class="mt-4 inline-block text-primary hover:text-white transition-colors font-bold uppercase tracking-wider text-sm" @click="search = ''; applyFilters(1)">
            Сбросить поиск
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <MotorcycleCard
            v-for="motorcycle in motorcycles"
            :key="motorcycle.id"
            :motorcycle="motorcycle"
            :compare-active="compareIds.includes(motorcycle.id)"
            :favorite-active="favoriteIds.includes(motorcycle.id)"
            :show-favorite="Boolean(sessionState.user)"
            @toggle-compare="toggleCompare"
            @toggle-favorite="toggleFavorite"
          />
        </div>

        <div class="mt-20 flex flex-wrap justify-center items-center gap-4" v-if="lastPage > 1">
          <button class="px-5 py-2 text-xs font-bold uppercase tracking-wider transition-all duration-300 border border-white/10 text-gray-400 hover:border-primary hover:text-primary disabled:opacity-40 disabled:cursor-not-allowed" :disabled="currentPage <= 1" @click="applyFilters(currentPage - 1)">
            Назад
          </button>

          <span class="text-sm text-gray-500">Страница {{ currentPage }} из {{ lastPage }} · {{ total }} товаров</span>

          <button class="px-5 py-2 text-xs font-bold uppercase tracking-wider transition-all duration-300 border border-white/10 text-gray-400 hover:border-primary hover:text-primary disabled:opacity-40 disabled:cursor-not-allowed" :disabled="currentPage >= lastPage" @click="applyFilters(currentPage + 1)">
            Вперёд
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
