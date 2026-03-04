<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import api from '../api';
import type { Motorcycle, Order, User } from '../types';

interface DashboardStats {
  usersCount: number;
  ordersCount: number;
  totalRevenue: number;
  unavailableCount: number;
}

type AdminTab = 'products' | 'orders' | 'users';

const loading = ref(true);
const saving = ref(false);
const errorText = ref('');
const successText = ref('');
const activeTab = ref<AdminTab>('products');

const searchQuery = ref('');
const filterStatus = ref<'all' | 'available' | 'unavailable'>('all');

const motorcycles = ref<Motorcycle[]>([]);
const orders = ref<Order[]>([]);
const users = ref<User[]>([]);
const stats = ref<DashboardStats>({ usersCount: 0, ordersCount: 0, totalRevenue: 0, unavailableCount: 0 });

const editingId = ref<number | null>(null);

const form = reactive({
  brand: '',
  model: '',
  type: 'Enduro',
  year: new Date().getFullYear(),
  engine_capacity: 250,
  power: 20,
  price: 100000,
  description: '',
  image_url: '/images/product_enduro_1.png',
  is_available: true,
  transmission: '',
  cooling: '',
  fuel_system: '',
  weight: null as number | null,
  tank_capacity: null as number | null,
});

function resetForm() {
  editingId.value = null;
  form.brand = '';
  form.model = '';
  form.type = 'Enduro';
  form.year = new Date().getFullYear();
  form.engine_capacity = 250;
  form.power = 20;
  form.price = 100000;
  form.description = '';
  form.image_url = '/images/product_enduro_1.png';
  form.is_available = true;
  form.transmission = '';
  form.cooling = '';
  form.fuel_system = '';
  form.weight = null;
  form.tank_capacity = null;
}

function editMotorcycle(moto: Motorcycle) {
  editingId.value = moto.id;
  form.brand = moto.brand;
  form.model = moto.model;
  form.type = moto.type;
  form.year = moto.year;
  form.engine_capacity = moto.engine_capacity;
  form.power = moto.power;
  form.price = moto.price;
  form.description = moto.description;
  form.image_url = moto.image_url;
  form.is_available = moto.is_available;
  form.transmission = moto.transmission ?? '';
  form.cooling = moto.cooling ?? '';
  form.fuel_system = moto.fuel_system ?? '';
  form.weight = moto.weight ?? null;
  form.tank_capacity = moto.tank_capacity ?? null;
  activeTab.value = 'products';
}

const filteredMotorcycles = computed(() => {
  const query = searchQuery.value.toLowerCase().trim();

  return motorcycles.value.filter((moto) => {
    const searchable = `${moto.brand} ${moto.model} ${moto.type}`.toLowerCase();
    const matchesSearch = !query || searchable.includes(query);

    const matchesStatus =
      filterStatus.value === 'all' ||
      (filterStatus.value === 'available' && moto.is_available) ||
      (filterStatus.value === 'unavailable' && !moto.is_available);

    return matchesSearch && matchesStatus;
  });
});

async function loadDashboard() {
  loading.value = true;
  errorText.value = '';

  try {
    const { data } = await api.get('/admin/dashboard');
    motorcycles.value = data.motorcycles ?? [];
    orders.value = data.orders ?? [];
    users.value = data.users ?? [];
    stats.value = data.stats;
  } catch {
    errorText.value = 'Не удалось загрузить данные админ-панели.';
  } finally {
    loading.value = false;
  }
}

async function saveMotorcycle() {
  saving.value = true;
  errorText.value = '';
  successText.value = '';

  try {
    if (editingId.value) {
      const { data } = await api.put(`/admin/motorcycles/${editingId.value}`, form);
      successText.value = data.message;
    } else {
      const { data } = await api.post('/admin/motorcycles', form);
      successText.value = data.message;
    }

    resetForm();
    await loadDashboard();
  } catch (error: any) {
    errorText.value = error?.response?.data?.message ?? 'Не удалось сохранить товар.';
  } finally {
    saving.value = false;
  }
}

async function deleteMotorcycle(id: number) {
  if (!confirm('Удалить товар?')) {
    return;
  }

  try {
    const { data } = await api.delete(`/admin/motorcycles/${id}`);
    successText.value = data.message;
    await loadDashboard();
  } catch {
    errorText.value = 'Не удалось удалить товар.';
  }
}

async function updateOrderStatus(orderId: number, status: Order['status']) {
  try {
    const { data } = await api.patch(`/admin/orders/${orderId}/status`, { status });
    successText.value = data.message;
    await loadDashboard();
  } catch {
    errorText.value = 'Не удалось обновить статус заказа.';
  }
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

onMounted(loadDashboard);
</script>

<template>
  <div>
    <div class="relative bg-dark overflow-hidden py-16 md:py-20">
      <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-b from-primary/5 via-dark to-dark" />
        <div class="absolute top-0 right-0 w-96 h-96 bg-primary/5 rounded-full blur-3xl" />
      </div>
      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <div class="w-10 h-10 bg-primary/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016" /></svg>
              </div>
              <span class="text-primary text-sm font-bold uppercase tracking-widest">Администратор</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold font-display text-white uppercase tracking-wide mb-2">Панель управления</h1>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-dark min-h-screen pb-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p v-if="errorText" class="mb-6 p-4 bg-red-500/10 border border-red-500/30 text-red-400 text-sm">{{ errorText }}</p>
        <p v-if="successText" class="mb-6 p-4 bg-green-500/10 border border-green-500/30 text-green-400 text-sm">{{ successText }}</p>

        <p v-if="loading" class="text-gray-500 py-8">Загрузка...</p>

        <div v-else>
          <div class="grid grid-cols-2 md:grid-cols-5 gap-4 -mt-8 mb-10">
            <div class="bg-dark-lighter border border-white/5 p-5 relative overflow-hidden group hover:border-primary/30 transition-colors cursor-pointer" @click="activeTab = 'products'">
              <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-primary to-transparent" />
              <p class="text-xs text-gray-600 uppercase tracking-wider font-bold mb-1">Товаров</p>
              <p class="text-3xl font-display font-bold text-white">{{ motorcycles.length }}</p>
              <p class="text-xs text-gray-600 mt-1">{{ stats.unavailableCount }} нет в наличии</p>
            </div>
            <div class="bg-dark-lighter border border-white/5 p-5 relative overflow-hidden group hover:border-blue-500/30 transition-colors cursor-pointer" @click="activeTab = 'orders'">
              <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-blue-500 to-transparent" />
              <p class="text-xs text-gray-600 uppercase tracking-wider font-bold mb-1">Заказов</p>
              <p class="text-3xl font-display font-bold text-white">{{ stats.ordersCount }}</p>
              <p class="text-xs text-blue-400 mt-1">{{ orders.filter((order) => order.status === 'new').length }} новых</p>
            </div>
            <div class="bg-dark-lighter border border-white/5 p-5 relative overflow-hidden group hover:border-green-500/30 transition-colors">
              <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-green-500 to-transparent" />
              <p class="text-xs text-gray-600 uppercase tracking-wider font-bold mb-1">Выручка</p>
              <p class="text-2xl font-display font-bold text-white">{{ stats.totalRevenue.toLocaleString('ru-RU') }} ₽</p>
            </div>
            <div class="bg-dark-lighter border border-white/5 p-5 relative overflow-hidden group hover:border-purple-500/30 transition-colors cursor-pointer" @click="activeTab = 'users'">
              <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-purple-500 to-transparent" />
              <p class="text-xs text-gray-600 uppercase tracking-wider font-bold mb-1">Пользователей</p>
              <p class="text-3xl font-display font-bold text-white">{{ stats.usersCount }}</p>
            </div>
            <div class="bg-dark-lighter border border-white/5 p-5 relative overflow-hidden group hover:border-yellow-500/30 transition-colors">
              <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-yellow-500 to-transparent" />
              <p class="text-xs text-gray-600 uppercase tracking-wider font-bold mb-1">Ср. чек</p>
              <p class="text-2xl font-display font-bold text-white">
                {{ stats.ordersCount > 0 ? Math.round(stats.totalRevenue / stats.ordersCount).toLocaleString('ru-RU') : '0' }} ₽
              </p>
            </div>
          </div>

          <div class="flex items-center gap-1 mb-8 border-b border-white/5 overflow-x-auto">
            <button
              class="px-5 py-3 text-sm font-bold font-display uppercase tracking-wider border-b-2 transition-all whitespace-nowrap"
              :class="activeTab === 'products' ? 'text-primary border-primary' : 'text-gray-500 border-transparent hover:text-gray-300'"
              @click="activeTab = 'products'"
            >
              Товары
            </button>
            <button
              class="px-5 py-3 text-sm font-bold font-display uppercase tracking-wider border-b-2 transition-all whitespace-nowrap"
              :class="activeTab === 'orders' ? 'text-primary border-primary' : 'text-gray-500 border-transparent hover:text-gray-300'"
              @click="activeTab = 'orders'"
            >
              Заказы
            </button>
            <button
              class="px-5 py-3 text-sm font-bold font-display uppercase tracking-wider border-b-2 transition-all whitespace-nowrap"
              :class="activeTab === 'users' ? 'text-primary border-primary' : 'text-gray-500 border-transparent hover:text-gray-300'"
              @click="activeTab = 'users'"
            >
              Пользователи
            </button>
          </div>

          <div v-show="activeTab === 'products'">
            <form class="bg-dark-lighter border border-white/5 mb-8" @submit.prevent="saveMotorcycle">
              <div class="px-6 py-4 border-b border-white/5 flex items-center gap-3">
                <div class="w-8 h-8 bg-primary/10 flex items-center justify-center text-primary text-sm font-bold">{{ editingId ? 'E' : '+' }}</div>
                <h2 class="text-lg font-bold font-display text-white uppercase">{{ editingId ? 'Редактирование товара' : 'Новый товар' }}</h2>
              </div>

              <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <input v-model="form.brand" class="bg-dark border border-white/10 text-white px-4 py-3 text-sm focus:border-primary/50 focus:outline-none" placeholder="Бренд" required />
                <input v-model="form.model" class="bg-dark border border-white/10 text-white px-4 py-3 text-sm focus:border-primary/50 focus:outline-none" placeholder="Модель" required />
                <input v-model="form.type" class="bg-dark border border-white/10 text-white px-4 py-3 text-sm focus:border-primary/50 focus:outline-none" placeholder="Тип" required />
                <input v-model.number="form.year" type="number" class="bg-dark border border-white/10 text-white px-4 py-3 text-sm focus:border-primary/50 focus:outline-none" placeholder="Год" required />
                <input v-model.number="form.engine_capacity" type="number" class="bg-dark border border-white/10 text-white px-4 py-3 text-sm focus:border-primary/50 focus:outline-none" placeholder="Объём" required />
                <input v-model.number="form.power" type="number" class="bg-dark border border-white/10 text-white px-4 py-3 text-sm focus:border-primary/50 focus:outline-none" placeholder="Мощность" required />
                <input v-model.number="form.price" type="number" class="bg-dark border border-white/10 text-white px-4 py-3 text-sm focus:border-primary/50 focus:outline-none" placeholder="Цена" required />
                <input v-model="form.image_url" class="bg-dark border border-white/10 text-white px-4 py-3 text-sm focus:border-primary/50 focus:outline-none" placeholder="URL картинки" required />
                <input v-model="form.transmission" class="bg-dark border border-white/10 text-white px-4 py-3 text-sm focus:border-primary/50 focus:outline-none" placeholder="КПП" />
                <input v-model="form.cooling" class="bg-dark border border-white/10 text-white px-4 py-3 text-sm focus:border-primary/50 focus:outline-none" placeholder="Охлаждение" />
                <input v-model="form.fuel_system" class="bg-dark border border-white/10 text-white px-4 py-3 text-sm focus:border-primary/50 focus:outline-none" placeholder="Подача топлива" />
                <input v-model.number="form.weight" type="number" class="bg-dark border border-white/10 text-white px-4 py-3 text-sm focus:border-primary/50 focus:outline-none" placeholder="Вес" />
                <input v-model.number="form.tank_capacity" type="number" step="0.1" class="bg-dark border border-white/10 text-white px-4 py-3 text-sm focus:border-primary/50 focus:outline-none" placeholder="Бак" />
                <label class="flex items-center gap-3 text-sm text-gray-300">
                  <input v-model="form.is_available" type="checkbox" class="w-5 h-5 border-2 border-white/20 bg-dark text-primary" />
                  В наличии
                </label>
                <textarea v-model="form.description" class="bg-dark border border-white/10 text-white px-4 py-3 text-sm focus:border-primary/50 focus:outline-none md:col-span-2 lg:col-span-2" rows="3" placeholder="Описание" required />
              </div>

              <div class="px-6 pb-6 flex items-center gap-3">
                <button type="submit" class="btn btn-primary" :disabled="saving"><span>{{ saving ? 'Сохранение...' : (editingId ? 'Сохранить изменения' : 'Добавить товар') }}</span></button>
                <button type="button" class="px-6 py-3 border border-white/10 text-gray-400 text-sm font-bold font-display uppercase tracking-wider hover:border-white/30 hover:text-white transition-all" @click="resetForm">Сброс</button>
              </div>
            </form>

            <div class="mb-6 flex flex-col md:flex-row gap-4 items-stretch md:items-center">
              <div class="relative flex-1">
                <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" /></svg>
                <input v-model="searchQuery" type="text" placeholder="Поиск по названию, бренду или типу..." class="w-full bg-dark-lighter border border-white/10 text-white pl-12 pr-4 py-3 text-sm focus:border-primary/50 focus:outline-none transition-colors placeholder:text-gray-600" />
              </div>
              <div class="flex gap-2">
                <button class="px-4 py-3 text-xs font-bold uppercase tracking-wider transition-colors" :class="filterStatus === 'all' ? 'bg-primary text-white' : 'bg-dark-lighter text-gray-400 border border-white/10'" @click="filterStatus = 'all'">Все</button>
                <button class="px-4 py-3 text-xs font-bold uppercase tracking-wider transition-colors" :class="filterStatus === 'available' ? 'bg-green-500 text-white' : 'bg-dark-lighter text-gray-400 border border-white/10'" @click="filterStatus = 'available'">В наличии</button>
                <button class="px-4 py-3 text-xs font-bold uppercase tracking-wider transition-colors" :class="filterStatus === 'unavailable' ? 'bg-red-500 text-white' : 'bg-dark-lighter text-gray-400 border border-white/10'" @click="filterStatus = 'unavailable'">Нет</button>
              </div>
            </div>

            <div class="flex items-center justify-between mb-4">
              <p class="text-gray-600 text-sm">Найдено: <span class="text-white font-bold">{{ filteredMotorcycles.length }}</span> товаров</p>
            </div>

            <div class="bg-dark-lighter border border-white/5 overflow-hidden">
              <div class="overflow-x-auto">
                <table class="min-w-full">
                  <thead>
                    <tr class="border-b border-white/5">
                      <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Товар</th>
                      <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">Тип</th>
                      <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Цена</th>
                      <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">Статус</th>
                      <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-white/5">
                    <tr v-for="motorcycle in filteredMotorcycles" :key="motorcycle.id" class="hover:bg-white/[0.02] transition-colors">
                      <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                          <div class="w-14 h-14 bg-dark border border-white/5 overflow-hidden flex-shrink-0"><img :src="motorcycle.image_url" alt="" class="w-full h-full object-cover" /></div>
                          <div>
                            <p class="text-white font-bold text-sm font-display uppercase">{{ motorcycle.brand }} {{ motorcycle.model }}</p>
                            <p class="text-gray-600 text-xs">{{ motorcycle.year }} г. • {{ motorcycle.engine_capacity }}cc • {{ motorcycle.power }} л.с.</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 hidden md:table-cell"><span class="px-2 py-1 bg-white/5 text-gray-400 text-xs font-bold uppercase">{{ motorcycle.type }}</span></td>
                      <td class="px-6 py-4"><span class="text-primary font-bold font-display text-lg">{{ motorcycle.price.toLocaleString('ru-RU') }} ₽</span></td>
                      <td class="px-6 py-4 hidden md:table-cell">
                        <span v-if="motorcycle.is_available" class="inline-flex items-center gap-1.5 text-xs font-bold text-green-400"><span class="w-2 h-2 rounded-full bg-green-400 animate-pulse" />В наличии</span>
                        <span v-else class="inline-flex items-center gap-1.5 text-xs font-bold text-red-400"><span class="w-2 h-2 rounded-full bg-red-400" />Нет в наличии</span>
                      </td>
                      <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                          <button type="button" class="w-9 h-9 flex items-center justify-center border border-white/10 text-gray-500 hover:text-primary hover:border-primary/50 transition-all" title="Редактировать" @click="editMotorcycle(motorcycle)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                          </button>
                          <button type="button" class="w-9 h-9 flex items-center justify-center border border-white/10 text-gray-500 hover:text-red-400 hover:border-red-500/50 transition-all" title="Удалить" @click="deleteMotorcycle(motorcycle.id)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div v-show="activeTab === 'orders'" class="space-y-4">
            <div v-for="order in orders" :key="order.id" class="bg-dark-lighter border border-white/5 overflow-hidden hover:border-white/10 transition-colors">
              <div class="p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 flex items-center justify-center text-sm font-display font-bold" :class="{
                    'bg-blue-500/10 text-blue-400 border border-blue-500/20': order.status === 'new',
                    'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20': order.status === 'processing',
                    'bg-green-500/10 text-green-400 border border-green-500/20': order.status === 'completed',
                    'bg-red-500/10 text-red-400 border border-red-500/20': order.status === 'cancelled',
                  }">#{{ order.id }}</div>
                  <div>
                    <p class="text-white font-bold text-sm">{{ order.name || 'Гость' }}</p>
                    <p class="text-gray-600 text-xs">{{ new Date(order.created_at).toLocaleString('ru-RU') }} • {{ order.phone || 'Без телефона' }}</p>
                  </div>
                </div>

                <div class="flex items-center gap-4">
                  <span class="text-xl font-bold text-primary font-display">{{ order.total.toLocaleString('ru-RU') }} ₽</span>
                  <select class="bg-dark border border-white/10 text-white text-sm px-3 py-2 focus:border-primary/50 focus:outline-none" :value="order.status" @change="updateOrderStatus(order.id, ($event.target as HTMLSelectElement).value as Order['status'])">
                    <option value="new">Новый</option>
                    <option value="processing">В обработке</option>
                    <option value="completed">Завершён</option>
                    <option value="cancelled">Отменён</option>
                  </select>
                </div>
              </div>
              <div class="p-5 bg-dark/30 border-t border-white/5">
                <p class="text-xs text-gray-600 uppercase tracking-wider font-bold mb-3">Состав заказа ({{ statusLabel(order.status) }})</p>
                <div class="space-y-2">
                  <div v-for="item in order.items" :key="item.id" class="flex items-center justify-between text-sm">
                    <span class="text-gray-300">{{ item.name }} <span class="text-gray-600">× {{ item.quantity }}</span></span>
                    <span class="text-white font-bold font-display">{{ (item.price * item.quantity).toLocaleString('ru-RU') }} ₽</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-show="activeTab === 'users'" class="bg-dark-lighter border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
              <table class="min-w-full">
                <thead>
                  <tr class="border-b border-white/5">
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Пользователь</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">Email</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">Роль</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                  <tr v-for="user in users" :key="user.id" class="hover:bg-white/[0.02] transition-colors">
                    <td class="px-6 py-4"><span class="text-gray-600 text-sm font-display font-bold">#{{ user.id }}</span></td>
                    <td class="px-6 py-4">
                      <div class="flex items-center gap-3">
                        <div class="w-9 h-9 flex items-center justify-center text-sm font-display font-bold" :class="user.is_admin ? 'bg-primary/20 text-primary border border-primary/30' : 'bg-white/5 text-gray-400 border border-white/10'">
                          {{ user.name.trim().charAt(0).toUpperCase() }}
                        </div>
                        <span class="text-white font-medium text-sm">{{ user.name }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 hidden md:table-cell"><span class="text-gray-400 text-sm">{{ user.email }}</span></td>
                    <td class="px-6 py-4 hidden md:table-cell">
                      <span v-if="user.is_admin" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary">Админ</span>
                      <span v-else class="text-gray-600 text-xs font-bold">Пользователь</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
