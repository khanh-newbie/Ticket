<template>
  <aside class="w-84 fixed h-full bg-white shadow-xl p-6 flex flex-col gap-6">
    <div class="flex justify-center mb-4">
      <h3 class="text-2xl font-semibold text-gray-800">Organizer</h3>
    </div>

    <div class="border-b border-gray-200 mb-4"></div>

    <nav class="flex flex-col gap-2">
      <router-link
        to="/organizer/events"
        class="flex items-center gap-3 p-3 rounded-lg text-gray-700 font-medium transition-all duration-200 hover:bg-green-50 hover:text-green-700 hover:shadow-sm"
        :class="{ 'bg-green-100 text-green-700 font-semibold': $route.path === '/organizer/events' }"
      >
        <i class="fas fa-user text-green-600 w-5"></i>
        <span>Sự kiện của tôi</span>
      </router-link>

      <router-link
        to="/organizer/legal-document"
        class="flex items-center gap-3 p-3 rounded-lg text-gray-700 font-medium transition-all duration-200 hover:bg-green-50 hover:text-green-700 hover:shadow-sm"
        :class="{ 'bg-green-100 text-green-700 font-semibold': $route.path === '/organizer/legal-document' }"
      >
        <i class="fas fa-calendar-alt text-green-600 w-5"></i>
        <span>Điều khoản cho Ban tổ chức</span>
      </router-link>
    </nav>
  </aside>
</template>

<script setup></script>

<style scoped>
nav a:hover {
  box-shadow: 0 2px 6px rgba(72, 187, 120, 0.15);
}
</style>
