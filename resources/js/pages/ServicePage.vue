<script setup lang="ts">
import PageHero from '../components/ui/PageHero.vue';
</script>

<template>
  <div>
    <PageHero
      background-image="/images/service_tuning.png"
      image-alt="Service Background"
      image-class="opacity-30"
      overlay-class="bg-gradient-to-r from-dark via-dark/80 to-transparent"
      :compact="false"
    >
      <h1 class="text-5xl md:text-7xl font-bold font-display italic text-white leading-none mb-6 tracking-tighter shadow-orange-glow">
        <span class="text-gradient">СЕРВИС</span> <span class="text-transparent text-stroke">& ТЮНИНГ</span>
      </h1>
      <p class="mt-4 text-xl text-gray-300 max-w-2xl font-light">
        Профессиональное обслуживание вашей техники. От планового ТО до подготовки к соревнованиям мирового уровня.
      </p>
    </PageHero>

    <div class="bg-dark py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div class="bg-dark-lighter border border-white/5 p-8 rounded-sm group hover:border-primary/50 transition-colors duration-300">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-6 text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0" /></svg>
            </div>
            <h3 class="text-xl font-bold text-white font-display uppercase mb-4">Плановое ТО</h3>
            <p class="text-gray-400 mb-4">Замена масел, фильтров, проверка узлов и агрегатов. Регламентные работы согласно мануалу производителя.</p>
            <span class="text-primary font-bold">от 3 500 ₽</span>
          </div>

          <div class="bg-dark-lighter border border-white/5 p-8 rounded-sm group hover:border-primary/50 transition-colors duration-300">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-6 text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7" /></svg>
            </div>
            <h3 class="text-xl font-bold text-white font-display uppercase mb-4">Ремонт Двигателя</h3>
            <p class="text-gray-400 mb-4">Капитальный ремонт, замена поршневой группы, регулировка клапанов, ремонт КПП любой сложности.</p>
            <span class="text-primary font-bold">от 8 000 ₽</span>
          </div>

          <div class="bg-dark-lighter border border-white/5 p-8 rounded-sm group hover:border-primary/50 transition-colors duration-300">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-6 text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
            </div>
            <h3 class="text-xl font-bold text-white font-display uppercase mb-4">Обслуживание подвески</h3>
            <p class="text-gray-400 mb-4">Переборка вилок и амортизаторов, замена сальников, пыльников, масла и настройка под вес райдера.</p>
            <span class="text-primary font-bold">от 4 500 ₽</span>
          </div>

          <div class="bg-dark-lighter border border-white/5 p-8 rounded-sm group hover:border-primary/50 transition-colors duration-300">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-6 text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0" /></svg>
            </div>
            <h3 class="text-xl font-bold text-white font-display uppercase mb-4">Предпродажная подготовка</h3>
            <p class="text-gray-400 mb-4">Комплексная диагностика, устранение мелких недочетов, мойка и полировка. Повысим стоимость вашей техники.</p>
            <span class="text-primary font-bold">от 5 000 ₽</span>
          </div>

          <div class="bg-dark-lighter border border-white/5 p-8 rounded-sm group hover:border-primary/50 transition-colors duration-300">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-6 text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
            </div>
            <h3 class="text-xl font-bold text-white font-display uppercase mb-4">Тюнинг и доработка</h3>
            <p class="text-gray-400 mb-4">Установка защиты, прямоточных выхлопов, карбюраторов, big bore китов. Сделаем ваш байк уникальным.</p>
            <span class="text-primary font-bold">Индивидуально</span>
          </div>

          <div class="bg-dark-lighter border border-white/5 p-8 rounded-sm group hover:border-primary/50 transition-colors duration-300">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-6 text-primary group-hover:bg-primary group-hover:text-white transition-all duration-300">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
            </div>
            <h3 class="text-xl font-bold text-white font-display uppercase mb-4">Шиномонтаж</h3>
            <p class="text-gray-400 mb-4">Замена резины, балансировка, ремонт камер, установка муссов и буксаторов.</p>
            <span class="text-primary font-bold">от 500 ₽</span>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-primary py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold font-display uppercase italic text-white mb-6">Запишись на сервис прямо сейчас</h2>
        <p class="text-white/80 text-lg mb-8 max-w-2xl mx-auto">Не жди поломки! Своевременное обслуживание — залог долгой жизни твоего мотоцикла.</p>
        <RouterLink to="/contacts" class="btn text-xl bg-white text-dark hover:bg-dark hover:text-white shadow-lg">
          <span>Оставить заявку</span>
        </RouterLink>
      </div>
    </div>
  </div>
</template>
