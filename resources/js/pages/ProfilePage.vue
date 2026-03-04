<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import api from '../api';
import type { Order, User } from '../types';

const router = useRouter();

const loading = ref(true);
const errorText = ref('');
const user = ref<User | null>(null);
const orders = ref<Order[]>([]);
const favoritesCount = ref(0);
const expandedOrders = ref<number[]>([]);

function toggleOrder(id: number) {
  if (expandedOrders.value.includes(id)) {
    expandedOrders.value = expandedOrders.value.filter((orderId) => orderId !== id);
    return;
  }
  expandedOrders.value = [...expandedOrders.value, id];
}

function isExpanded(id: number): boolean {
  return expandedOrders.value.includes(id);
}

function statusLabel(status: Order['status']): string {
  switch (status) {
    case 'new':
      return 'Новый';
    case 'processing':
      return 'В обработке';
    case 'completed':
      return 'Завершён';
    case 'cancelled':
      return 'Отменён';
    default:
      return status;
  }
}

async function loadProfile() {
  loading.value = true;
  errorText.value = '';

  try {
    const { data } = await api.get('/profile');
    user.value = data.user;
    orders.value = data.orders ?? [];
    favoritesCount.value = Number(data.favorites_count ?? 0);
  } catch {
    await router.push('/login');
  } finally {
    loading.value = false;
  }
}

onMounted(loadProfile);
</script>

<template>
  <div>
    <div class="relative bg-dark overflow-hidden py-16 md:py-20">
      <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-b from-primary/5 via-dark to-dark" />
        <div class="absolute -top-40 right-0 w-96 h-96 bg-primary/10 rounded-full blur-[120px] pointer-events-none" />
      </div>

      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-6" v-if="user">
          <div class="w-20 h-20 bg-primary/20 border-2 border-primary/50 flex items-center justify-center text-primary text-3xl font-bold font-display">
            {{ user.name.trim().charAt(0).toUpperCase() }}
          </div>
          <div>
            <h1 class="text-4xl md:text-5xl font-bold font-display text-white uppercase tracking-wide mb-1">{{ user.name }}</h1>
            <p class="text-gray-500">{{ user.email }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-dark min-h-screen pb-24 relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <p v-if="loading" class="text-gray-500 py-8">Загрузка...</p>
        <p v-if="errorText" class="mb-6 p-4 bg-red-500/10 border border-red-500/30 text-red-400 text-sm">{{ errorText }}</p>

        <div v-if="!loading" class="grid grid-cols-1 md:grid-cols-3 gap-6 -mt-8 mb-12">
          <div class="bg-dark-lighter border border-white/5 p-6 relative overflow-hidden group hover:border-primary/30 transition-colors">
            <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-primary to-transparent" />
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-600 uppercase tracking-wider font-bold mb-1">Заказов</p>
                <p class="text-3xl font-display font-bold text-white">{{ orders.length }}</p>
              </div>
              <div class="w-12 h-12 bg-primary/10 flex items-center justify-center text-primary">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" /></svg>
              </div>
            </div>
          </div>

          <div class="bg-dark-lighter border border-white/5 p-6 relative overflow-hidden group hover:border-primary/30 transition-colors">
            <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-primary to-transparent" />
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-600 uppercase tracking-wider font-bold mb-1">В избранном</p>
                <p class="text-3xl font-display font-bold text-white">{{ favoritesCount }}</p>
              </div>
              <div class="w-12 h-12 bg-red-500/10 flex items-center justify-center text-red-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
              </div>
            </div>
          </div>

          <div class="bg-dark-lighter border border-white/5 p-6 relative overflow-hidden group hover:border-primary/30 transition-colors">
            <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-primary to-transparent" />
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-600 uppercase tracking-wider font-bold mb-1">Дата регистрации</p>
                <p class="text-xl font-display font-bold text-white">{{ user?.created_at ? new Date(user.created_at).toLocaleDateString('ru-RU') : '—' }}</p>
              </div>
              <div class="w-12 h-12 bg-blue-500/10 flex items-center justify-center text-blue-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2" /></svg>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-wrap gap-3 mb-12" v-if="!loading">
          <RouterLink to="/favorites" class="inline-flex items-center gap-2 px-5 py-2.5 bg-dark-lighter border border-white/5 text-gray-400 hover:text-white hover:border-primary/50 transition-all text-sm font-bold uppercase tracking-wider">
            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
            Избранное
          </RouterLink>
          <RouterLink to="/compare" class="inline-flex items-center gap-2 px-5 py-2.5 bg-dark-lighter border border-white/5 text-gray-400 hover:text-white hover:border-primary/50 transition-all text-sm font-bold uppercase tracking-wider">
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            Сравнение
          </RouterLink>
          <RouterLink to="/catalog" class="inline-flex items-center gap-2 px-5 py-2.5 bg-dark-lighter border border-white/5 text-gray-400 hover:text-white hover:border-primary/50 transition-all text-sm font-bold uppercase tracking-wider">
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
            Каталог
          </RouterLink>
          <RouterLink to="/cart" class="inline-flex items-center gap-2 px-5 py-2.5 bg-dark-lighter border border-white/5 text-gray-400 hover:text-white hover:border-primary/50 transition-all text-sm font-bold uppercase tracking-wider">
            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" /></svg>
            Корзина
          </RouterLink>
        </div>

        <div class="flex items-center justify-between mb-8" v-if="!loading">
          <h2 class="text-3xl font-bold font-display text-white uppercase">История заказов</h2>
          <div class="h-px flex-1 bg-white/5 ml-6" />
        </div>

        <div v-if="!loading && !orders.length" class="bg-dark-lighter border border-white/5 p-16 text-center">
          <div class="w-20 h-20 mx-auto mb-6 bg-white/5 flex items-center justify-center">
            <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
          </div>
          <h3 class="text-xl font-display font-bold text-white uppercase mb-2">Заказов пока нет</h3>
          <p class="text-gray-500 mb-8 max-w-md mx-auto">Перейдите в каталог и выберите технику, которая подойдёт именно вам</p>
          <RouterLink to="/catalog" class="btn btn-primary"><span>Перейти в каталог</span></RouterLink>
        </div>

        <div v-else-if="!loading" class="space-y-4">
          <div v-for="order in orders" :key="order.id" class="bg-dark-lighter border border-white/5 overflow-hidden hover:border-white/10 transition-colors">
            <div class="p-5 md:p-6 flex flex-col md:flex-row md:items-center md:justify-between cursor-pointer hover:bg-white/[0.02] transition-colors" @click="toggleOrder(order.id)">
              <div class="flex items-center gap-4 mb-3 md:mb-0">
                <div
                  class="w-10 h-10 flex items-center justify-center text-sm font-display font-bold"
                  :class="{
                    'bg-blue-500/10 text-blue-400 border border-blue-500/20': order.status === 'new',
                    'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20': order.status === 'processing',
                    'bg-green-500/10 text-green-400 border border-green-500/20': order.status === 'completed',
                    'bg-red-500/10 text-red-400 border border-red-500/20': order.status === 'cancelled',
                  }"
                >
                  #{{ order.id }}
                </div>
                <div>
                  <h3 class="text-base font-bold font-display text-white uppercase">{{ order.items.length }} товаров</h3>
                  <p class="text-sm text-gray-600">{{ new Date(order.created_at).toLocaleString('ru-RU') }}</p>
                </div>
              </div>

              <div class="flex items-center gap-4 md:gap-6">
                <span
                  class="px-3 py-1 text-xs font-bold uppercase tracking-wider"
                  :class="{
                    'bg-blue-500/10 text-blue-400 border border-blue-500/20': order.status === 'new',
                    'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20': order.status === 'processing',
                    'bg-green-500/10 text-green-400 border border-green-500/20': order.status === 'completed',
                    'bg-red-500/10 text-red-400 border border-red-500/20': order.status === 'cancelled',
                  }"
                >
                  {{ statusLabel(order.status) }}
                </span>
                <span class="text-xl font-bold text-primary font-display">{{ order.total.toLocaleString('ru-RU') }} ₽</span>
                <svg class="w-5 h-5 text-gray-600 transform transition-transform" :class="isExpanded(order.id) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
              </div>
            </div>

            <div v-if="isExpanded(order.id)" class="border-t border-white/5">
              <div class="p-5 md:p-6 space-y-3">
                <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center py-2 border-b border-white/5 last:border-0">
                  <div class="flex items-center gap-3">
                    <span class="text-white font-medium">{{ item.name }}</span>
                    <span class="text-xs px-2 py-0.5 bg-white/5 text-gray-500">x{{ item.quantity }}</span>
                  </div>
                  <span class="text-primary font-bold font-display">{{ (item.price * item.quantity).toLocaleString('ru-RU') }} ₽</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
