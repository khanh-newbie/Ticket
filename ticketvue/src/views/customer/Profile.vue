<template>
  <div class="min-h-screen flex bg-gray-100">
    <CustomerSidebar />
    <!-- Main content -->
    <main class="flex-1 flex justify-center items-start pt-16">
      <div class="bg-white p-8 rounded-lg shadow w-full max-w-md">
        <!-- Avatar -->
        <div class="flex justify-center mb-6">
          <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
            avt
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="saveInfo" class="space-y-4">
          <div>
            <label class="block text-gray-700">Họ tên</label>
            <input v-model="form.name" type="text" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-gray-700">Số điện thoại</label>
            <input v-model="form.phone" type="text" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-gray-700">Email</label>
            <input v-model="form.email" type="email" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-gray-700">Ngày tháng năm sinh</label>
            <input v-model="form.birth" type="date" class="w-full border rounded px-3 py-2" />
          </div>
          <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition">
            Lưu thông tin
          </button>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup>
import CustomerSidebar from '@/components/customers/CustomerSidebar.vue'
import { reactive } from 'vue'

const form = reactive({
  name: '',
  phone: '',
  email: '',
  birth: '',
})

function saveInfo() {
  console.log('Thông tin được lưu:', form)
  alert('Thông tin đã được lưu!')
}
</script>
