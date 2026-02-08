<template>
  <div class="bg-[#23252C] p-6 rounded-xl space-y-6">

    <h2 class="text-xl font-semibold text-white">Thông tin thanh toán</h2>
    <p class="text-gray-300 text-sm">
      Ticketbox sẽ chuyển tiền bán vé đến tài khoản của bạn.
    </p>
    <p class="text-gray-300 text-sm">
      Tiền bán vé (sau khi trừ phí dịch vụ) sẽ vào tài khoản của bạn sau khi xác nhận sale report từ 7 - 10 ngày.
      Nếu bạn muốn nhận tiền sớm hơn, vui lòng liên hệ 1900.6408 hoặc info@ticketbox.vn
    </p>

    <div class="space-y-4">
      <!-- Chủ tài khoản -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <label class="sm:w-1/4 text-white font-medium">Chủ tài khoản</label>
        <div class="sm:w-3/4 relative">
          <input v-model="localData.bankAccountName" type="text" maxlength="100" placeholder="Nhập chủ tài khoản"
            class="w-full bg-gray-800 border border-green-400 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500"/>
          <span class="absolute right-3 top-2 text-sm text-gray-400">{{ localData.bankAccountName.length }}/100</span>
        </div>
      </div>

      <!-- Số tài khoản -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <label class="sm:w-1/4 text-white font-medium">Số tài khoản</label>
        <div class="sm:w-3/4">
          <input v-model.number="localData.bankAccountNumber" type="number" placeholder="Nhập số tài khoản"
            class="w-full bg-gray-800 border border-green-400 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500"/>
        </div>
      </div>

      <!-- Tên ngân hàng -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <label class="sm:w-1/4 text-white font-medium">Tên ngân hàng</label>
        <div class="sm:w-3/4 relative">
          <select v-model="localData.bankName"
            class="w-full bg-gray-800 border border-green-400 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
            <option disabled value="">Chọn ngân hàng</option>
            <option v-for="bank in banks" :key="bank" :value="bank">{{ bank }}</option>
          </select>
        </div>
      </div>

      <!-- Chi nhánh -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <label class="sm:w-1/4 text-white font-medium">Chi nhánh</label>
        <div class="sm:w-3/4 relative">
          <input v-model="localData.bankBranch" type="text" maxlength="100" placeholder="Chi nhánh"
            class="w-full bg-gray-800 border border-green-400 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500"/>
          <span class="absolute right-3 top-2 text-sm text-gray-400">{{ localData.bankBranch.length }}/100</span>
        </div>
      </div>

      <!-- Loại hình kinh doanh -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <label class="sm:w-1/4 text-white font-medium">Loại hình kinh doanh</label>
        <div class="sm:w-3/4">
          <select v-model="localData.businessType"
            class="w-full bg-gray-800 border border-green-400 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="individual">Cá nhân</option>
            <option value="company">Công ty</option>
          </select>
        </div>
      </div>

      <!-- Họ tên -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <label class="sm:w-1/4 text-white font-medium">Họ tên</label>
        <div class="sm:w-3/4 relative">
          <input v-model="localData.personalFullName" type="text" maxlength="100" placeholder="Họ tên"
            class="w-full bg-gray-800 border border-green-400 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500"/>
          <span class="absolute right-3 top-2 text-sm text-gray-400">{{ localData.personalFullName.length }}/100</span>
        </div>
      </div>

      <!-- Địa chỉ -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <label class="sm:w-1/4 text-white font-medium">Địa chỉ</label>
        <div class="sm:w-3/4 relative">
          <input v-model="localData.personalAddress" type="text" maxlength="100" placeholder="Địa chỉ"
            class="w-full bg-gray-800 border border-green-400 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500"/>
          <span class="absolute right-3 top-2 text-sm text-gray-400">{{ localData.personalAddress.length }}/100</span>
        </div>
      </div>

      <!-- Mã số thuế -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <label class="sm:w-1/4 text-white font-medium">Mã số thuế</label>
        <div class="sm:w-3/4">
          <input v-model.number="localData.taxNumber" type="number" placeholder="Mã số thuế"
            class="w-full bg-gray-800 border border-green-400 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500"/>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, defineExpose } from 'vue'

const props = defineProps({ modelValue: Object })
const emit = defineEmits(["update:modelValue"])

// Danh sách ngân hàng
const banks = [
  'Vietcombank',
  'Techcombank',
  'VPBank',
  'BIDV',
  'ACB',
  'MB Bank',
  'Sacombank'
]

// Dữ liệu local
const localData = reactive({
  bankAccountName: '',
  bankAccountNumber: 0,
  bankName: '',
  bankBranch: '',
  businessType: 'individual',
  personalFullName: '',
  personalAddress: '',
  taxNumber: 0
})

// Watch để emit về parent (v-model hai chiều)
watch(localData, (newVal) => {
  emit('update:modelValue', { ...newVal })
}, { deep: true })

// Expose data cho parent nếu cần
defineExpose({ getData: () => localData })
</script>
