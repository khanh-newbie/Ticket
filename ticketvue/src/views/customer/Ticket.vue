<template>
  <div class="min-h-screen flex bg-gray-100">
    <CustomerSidebar />
    <main class="flex-1 p-8 flex flex-col gap-6">
        <div class="flex gap-4">
            <button
                @click="setFilter('all')"
                :class="active === 'all' ? activeClass : inactiveClass"
            >
                Tất cả
            </button>

            <button
                @click="setFilter('success')"
                :class="active === 'success' ? activeClass : inactiveClass"
            >
                Thành công
            </button>

            <button
                @click="setFilter('processing')"
                :class="active === 'processing' ? activeClass : inactiveClass"
            >
                Đang xử lý
            </button>

            <button
                @click="setFilter('cancelled')"
                :class="active === 'cancelled' ? activeClass : inactiveClass"
            >
                Đã hủy
            </button>
        </div>


      <!-- Empty state -->
      <div class="flex-1 flex flex-col items-center justify-center mt-10">
        <div class="w-48 h-48 rounded-lg overflow-hidden bg-gray-200 flex items-center justify-center">
          <img src="" alt="Chưa có vé" class="w-full h-full object-cover"/>
        </div>
        <p class="mt-4 text-gray-500">Bạn chưa có vé nào</p>
      </div>
    </main>
  </div>
</template>

<script setup>
import CustomerSidebar from '@/components/customers/CustomerSidebar.vue'
import { ref } from 'vue'

const active = ref('all')

const activeClass = 'px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700 transition flex-1 text-center'
const inactiveClass = 'px-4 py-2 rounded bg-gray-200 text-gray-700 hover:bg-gray-300 transition flex-1 text-center'

function setFilter(status) {
  active.value = status
  // Gọi backend filter dữ liệu theo status
}
</script>
