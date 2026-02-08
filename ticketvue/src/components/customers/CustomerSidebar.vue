<template>
  <aside class="w-64 bg-white shadow-lg p-6 flex flex-col gap-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold text-lg">
        {{ user.name.charAt(0) }}
      </div>
      <h2 class="text-green-700 font-semibold text-lg">Chào, {{ user.name }}</h2>
    </div>

    <!-- Menu -->
    <nav class="flex flex-col gap-3">
        <router-link
            to="/my-profile"
            class="flex items-center gap-3 p-2 rounded hover:bg-green-50 transition"
        >
            <i class="fas fa-user text-green-600 w-5"></i>
            <span class="text-gray-700 font-medium">Thông tin tài khoản</span>
        </router-link>

        <router-link
            to="/my-ticket"
            class="flex items-center gap-3 p-2 rounded hover:bg-green-50 transition"
        >
            <i class="fas fa-ticket text-green-600 w-5"></i>
            <span class="text-gray-700 font-medium">Vé của tôi</span>
        </router-link>

        <router-link
            to="/my-events"
            class="flex items-center gap-3 p-2 rounded hover:bg-green-50 transition"
        >
            <i class="fas fa-calendar-alt text-green-600 w-5"></i>
            <span class="text-gray-700 font-medium">Sự kiện của tôi</span>
        </router-link>
    </nav>

  </aside>
</template>

<script setup>
import { reactive } from 'vue'

const user = reactive({
  name: 'User',
})





</script>
