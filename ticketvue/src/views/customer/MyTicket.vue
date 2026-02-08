<template>
  <div class="min-w-[400px]">
    <div class="min-h-screen text-gray-800 font-sans bg-gray-50">
      <HeaderComponent @search="onSearch" @reset="resetSearch" />

      <main class="flex-1 p-6">
        <div class="text-sm text-gray-700 mb-6 flex items-center gap-2">
          <span>Trang chủ</span>
          <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 14 14">
            <path d="M4.53 2.53l4 4-4 4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span class="text-green-600 font-semibold">Vé của tôi</span>
        </div>

        <div class="bg-white shadow-lg rounded-xl p-8 max-w-xl mx-auto">
          <div class="flex flex-wrap justify-between mb-6 gap-2">
            <div class="flex gap-2 flex-wrap">
              <button
                v-for="status in statuses"
                :key="status.value"
                @click="filterStatus(status.value)"
                :class="[
                  'px-4 py-2 rounded-full border transition-all duration-200',
                  activeStatus === status.value
                    ? 'bg-green-500 text-white border-green-500'
                    : 'bg-white text-gray-700 border-gray-300'
                ]"
              >
                {{ status.label }}
              </button>
            </div>

            <div class="flex gap-2 flex-wrap">
              <button
                v-for="time in times"
                :key="time.value"
                @click="filterTime(time.value)"
                :class="[
                  'px-4 py-2 rounded-full border transition-all duration-200',
                  activeTime === time.value
                    ? 'bg-green-500 text-white border-green-500'
                    : 'bg-white text-gray-700 border-gray-300'
                ]"
              >
                {{ time.label }}
              </button>
            </div>
          </div>

          <div v-if="tickets.length > 0" class="grid gap-4">
            <div
              v-for="ticket in filteredTickets"
              :key="ticket.id"
              class="bg-gray-50 p-4 rounded-xl shadow flex justify-between items-center"
            >
              <div>
                <h3 class="font-semibold text-gray-800">{{ ticket.event_name }}</h3>
                <p class="text-gray-500 text-sm">{{ ticket.date }}</p>
              </div>
              <span
                :class="[
                  'px-3 py-1 rounded-full text-sm font-medium',
                  ticket.status === 'success' ? 'bg-green-100 text-green-600' :
                  ticket.status === 'pending' ? 'bg-yellow-100 text-yellow-600' :
                  'bg-red-100 text-red-600'
                ]"
              >
                {{ ticket.statusLabel }}
              </span>
            </div>
          </div>

          <div v-else class="flex flex-col items-center justify-center text-center mt-8">
            <svg
              class="w-48 h-48 mb-4"
              viewBox="0 0 260 260"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <circle cx="130" cy="130" r="130" fill="#F59D5D" />
            </svg>
            <p class="text-gray-500 mb-4">Bạn chưa có vé nào</p>
            <a href="/">
              <button class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                Mua vé ngay
              </button>
            </a>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import HeaderComponent from "@/components/customers/layouts/HeaderComponent.vue";

const statuses = ref([
  { label: "Tất cả", value: "all" },
  { label: "Thành công", value: "success" },
  { label: "Đang xử lý", value: "pending" },
  { label: "Đã hủy", value: "cancel" },
]);
const times = ref([
  { label: "Sắp diễn ra", value: "upcoming" },
  { label: "Đã kết thúc", value: "ended" },
]);

const activeStatus = ref("all");
const activeTime = ref("upcoming");

const tickets = ref([
  { id: 1, event_name: "Concert A", date: "2025-12-25", status: "success", statusLabel: "Thành công", time: "upcoming" },
  { id: 2, event_name: "Exhibition B", date: "2025-11-20", status: "pending", statusLabel: "Đang xử lý", time: "upcoming" },
  { id: 3, event_name: "Sport Event C", date: "2025-10-10", status: "cancel", statusLabel: "Đã hủy", time: "ended" },
]);

const filterStatus = (status) => {
  activeStatus.value = status;
};

const filterTime = (time) => {
  activeTime.value = time;
};

const filteredTickets = computed(() => {
  return tickets.value.filter(t =>
    (activeStatus.value === "all" || t.status === activeStatus.value) &&
    (activeTime.value === "all" || t.time === activeTime.value)
  );
});
</script>
