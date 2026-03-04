<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import api from '../api';
import { loadSession, sessionState } from '../session';
import type { Motorcycle } from '../types';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const errorText = ref('');
const successText = ref('');

const motorcycle = ref<Motorcycle | null>(null);
const isFavorite = ref(false);
const isInCompare = ref(false);

async function loadMotorcycle() {
  loading.value = true;
  errorText.value = '';

  try {
    const { data } = await api.get(`/motorcycles/${route.params.id}`);
    motorcycle.value = data.motorcycle;
    isFavorite.value = Boolean(data.is_favorite);
    isInCompare.value = Boolean(data.is_in_compare);
  } catch {
    errorText.value = 'Товар не найден.';
  } finally {
    loading.value = false;
  }
}

async function addToCart(buyNow = false) {
  if (!motorcycle.value) {
    return;
  }

  errorText.value = '';
  successText.value = '';

  try {
    await api.post(`/cart/${motorcycle.value.id}`, { buy_now: buyNow });
    await loadSession();

    if (buyNow) {
      await router.push('/checkout');
      return;
    }

    successText.value = 'Товар добавлен в корзину!';
  } catch {
    errorText.value = 'Не удалось добавить товар в корзину.';
  }
}

async function toggleCompare() {
  if (!motorcycle.value) {
    return;
  }

  errorText.value = '';
  successText.value = '';

  try {
    const { data } = await api.post(`/compare/${motorcycle.value.id}`);
    isInCompare.value = !isInCompare.value;
    successText.value = data.message ?? 'Сравнение обновлено.';
    await loadSession();
  } catch (error: any) {
    errorText.value = error?.response?.data?.message ?? 'Ошибка при сравнении.';
  }
}

async function toggleFavorite() {
  if (!motorcycle.value) {
    return;
  }

  if (!sessionState.user) {
    await router.push('/login');
    return;
  }

  errorText.value = '';
  successText.value = '';

  try {
    const { data } = await api.post(`/favorites/${motorcycle.value.id}`);
    isFavorite.value = Boolean(data.is_favorite);
    successText.value = data.message ?? 'Избранное обновлено.';
  } catch {
    errorText.value = 'Ошибка при изменении избранного.';
  }
}

onMounted(loadMotorcycle);
</script>

<template>
  <div>
    <div class="bg-dark-lighter py-6 border-b border-white/5 relative z-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <RouterLink to="/catalog" class="inline-flex items-center text-sm font-bold text-gray-500 hover:text-primary transition-colors uppercase tracking-wider group">
          <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Назад в каталог
        </RouterLink>
      </div>
    </div>

    <div class="min-h-screen bg-dark py-12 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-1/3 h-full bg-gradient-to-l from-primary/5 to-transparent skew-x-[-12deg] pointer-events-none" />

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <p v-if="loading" class="text-gray-500">Загрузка...</p>
        <p v-if="errorText" class="mb-5 bg-red-500/10 border border-red-500/30 text-red-400 p-4 text-sm">{{ errorText }}</p>
        <p v-if="successText" class="mb-5 bg-green-500/10 border border-green-500/30 text-green-400 p-4 text-sm">{{ successText }}</p>

        <div v-if="motorcycle" class="lg:grid lg:grid-cols-2 lg:gap-x-16 lg:items-start">
          <div class="flex flex-col">
            <div class="w-full aspect-[4/3] rounded-sm overflow-hidden bg-dark-lighter border border-white/5 relative group shadow-2xl">
              <img :src="motorcycle.image_url" :alt="motorcycle.model" class="w-full h-full object-center object-cover transform group-hover:scale-110 transition-transform duration-700" />
              <div class="absolute inset-0 bg-gradient-to-t from-dark/80 via-transparent to-transparent opacity-60" />

              <div class="absolute top-4 left-4">
                <span class="bg-primary/90 backdrop-blur-sm text-white text-xs font-bold font-display uppercase px-4 py-2 tracking-wider transform -skew-x-12 shadow-lg">
                  <span class="inline-block skew-x-12">{{ motorcycle.type }}</span>
                </span>
              </div>

              <div class="absolute top-4 right-4 flex gap-2">
                <button
                  v-if="sessionState.user"
                  type="button"
                  class="w-10 h-10 flex items-center justify-center rounded-full transition-all shadow-lg"
                  :class="isFavorite ? 'bg-red-500 text-white' : 'bg-dark/80 text-gray-300 hover:bg-red-500 hover:text-white'"
                  @click="toggleFavorite"
                >
                  <svg class="w-5 h-5" :fill="isFavorite ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                </button>

                <button
                  type="button"
                  class="w-10 h-10 flex items-center justify-center rounded-full transition-all shadow-lg"
                  :class="isInCompare ? 'bg-blue-500 text-white' : 'bg-dark/80 text-gray-300 hover:bg-blue-500 hover:text-white'"
                  @click="toggleCompare"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                </button>
              </div>
            </div>
          </div>

          <div class="mt-10 px-4 sm:px-0 sm:mt-16 lg:mt-0">
            <h1 class="text-5xl md:text-6xl font-bold font-display uppercase italic text-white leading-none mb-2 tracking-wide">
              {{ motorcycle.brand }}
              <span class="text-transparent text-stroke">{{ motorcycle.model }}</span>
            </h1>

            <div class="mb-8 flex items-end gap-4 border-b border-white/10 pb-8">
              <p class="text-4xl font-bold text-primary font-display tracking-wider">{{ motorcycle.price.toLocaleString('ru-RU') }} ₽</p>
              <p class="text-sm text-gray-500 mb-2 uppercase tracking-widest hidden sm:block">Цена актуальна</p>
            </div>

            <div class="prose prose-invert prose-lg text-gray-400 mb-10 leading-relaxed font-light">
              <p>{{ motorcycle.description }}</p>
            </div>

            <div class="bg-dark-lighter border border-white/5 p-6 mb-10 relative overflow-hidden group">
              <div class="absolute top-0 right-0 w-2 h-full bg-primary transform scale-y-0 group-hover:scale-y-100 transition-transform duration-300 origin-bottom" />

              <h3 class="text-xl font-bold uppercase font-display text-white mb-6 italic">Характеристики</h3>
              <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                <div class="flex justify-between border-b border-white/5 pb-2">
                  <dt class="text-sm text-gray-500 font-bold uppercase">Тип</dt>
                  <dd class="text-sm font-bold text-white text-right">{{ motorcycle.type }}</dd>
                </div>
                <div class="flex justify-between border-b border-white/5 pb-2">
                  <dt class="text-sm text-gray-500 font-bold uppercase">Год выпуска</dt>
                  <dd class="text-sm font-bold text-white text-right">{{ motorcycle.year }}</dd>
                </div>
                <div class="flex justify-between border-b border-white/5 pb-2">
                  <dt class="text-sm text-gray-500 font-bold uppercase">Объем двигателя</dt>
                  <dd class="text-sm font-bold text-white text-right">{{ motorcycle.engine_capacity }} см³</dd>
                </div>
                <div class="flex justify-between border-b border-white/5 pb-2">
                  <dt class="text-sm text-gray-500 font-bold uppercase">Мощность</dt>
                  <dd class="text-sm font-bold text-white text-right">{{ motorcycle.power }} л.с.</dd>
                </div>
                <div v-if="motorcycle.transmission" class="flex justify-between border-b border-white/5 pb-2">
                  <dt class="text-sm text-gray-500 font-bold uppercase">КПП</dt>
                  <dd class="text-sm font-bold text-white text-right">{{ motorcycle.transmission }}</dd>
                </div>
                <div v-if="motorcycle.cooling" class="flex justify-between border-b border-white/5 pb-2">
                  <dt class="text-sm text-gray-500 font-bold uppercase">Охлаждение</dt>
                  <dd class="text-sm font-bold text-white text-right">{{ motorcycle.cooling }}</dd>
                </div>
                <div v-if="motorcycle.fuel_system" class="flex justify-between border-b border-white/5 pb-2">
                  <dt class="text-sm text-gray-500 font-bold uppercase">Подача топлива</dt>
                  <dd class="text-sm font-bold text-white text-right">{{ motorcycle.fuel_system }}</dd>
                </div>
                <div v-if="motorcycle.weight" class="flex justify-between border-b border-white/5 pb-2">
                  <dt class="text-sm text-gray-500 font-bold uppercase">Вес</dt>
                  <dd class="text-sm font-bold text-white text-right">{{ motorcycle.weight }} кг</dd>
                </div>
                <div v-if="motorcycle.tank_capacity" class="flex justify-between border-b border-white/5 pb-2">
                  <dt class="text-sm text-gray-500 font-bold uppercase">Объем бака</dt>
                  <dd class="text-sm font-bold text-white text-right">{{ motorcycle.tank_capacity }} л</dd>
                </div>
              </dl>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
              <button type="button" class="btn btn-primary flex-1 text-xl shadow-lg shadow-primary/20" @click="addToCart(false)">
                <span>В корзину</span>
              </button>
              <button type="button" class="btn btn-outline flex-1 text-xl text-center" @click="addToCart(true)">
                <span>Купить сейчас</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
