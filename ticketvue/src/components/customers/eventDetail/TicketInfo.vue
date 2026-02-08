<template>
  <div id="ticket-section" class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow space-y-4">
    <h4 class="text-2xl text-black font-semibold">Thông tin vé</h4>

    <div
      v-for="schedule in event.schedules"
      :key="schedule.id"
      class="border rounded-lg overflow-hidden mb-4"
    >
      <div class="flex justify-between items-center bg-gray-100 p-4">
        <div class="flex flex-col">
          <span class="font-medium text-xs text-gray-800">
            {{ formatSchedule(schedule) }}
          </span>
        </div>
      </div>

      <div class="p-4 bg-gray-50 space-y-3">
        <div
          v-for="ticket in event.ticket_types.filter(
            (t) => t.schedule_id === schedule.id
          )"
          :key="ticket.id"
          class="flex justify-between items-center"
        >
          <div class="flex flex-col">
            <span class="font-medium text-gray-700">{{ ticket.name }}</span>
            <span class="text-green-600 font-semibold">{{
              formatPrice(ticket.base_price)
            }}</span>
            <span class="text-gray-500 text-xs">
              Số lượng còn lại: {{ ticket.available_quantity ?? 0 }}
            </span>
          </div>

          <div
            v-if="ticket.available_quantity > 0 && !scheduleExpired(schedule)"
            class="flex items-center space-x-2 text-green-800"
          >
            <button
              @click="decrease(ticket)"
              class="px-2 py-1 bg-gray-200 rounded font-bold"
            >
              -
            </button>

            <span class="w-6 text-center font-bold">{{ localCart[ticket.id] ?? 1 }}</span>

            <button
              @click="increase(ticket, ticket.available_quantity)"
              class="px-2 py-1 bg-gray-200 rounded font-bold"
            >
              +
            </button>

            <button
              @click="handleAddToCart(ticket)"
              class="flex items-center space-x-1 text-white bg-blue-500 hover:bg-blue-600 px-3 py-1 rounded text-sm transition transform active:scale-95"
            >
              <img :src="'/images/cart.png'" class="w-4 h-4" />
              <span>Thêm vào giỏ</span>
            </button>

            <button
              @click="buyNow(ticket)"
              class="text-white bg-green-500 hover:bg-green-600 px-3 py-1 rounded text-sm"
            >
              Mua ngay
            </button>
          </div>

          <span v-else class="text-red-600 text-xs font-semibold">
            {{ scheduleExpired(schedule) ? "Sự kiện đã kết thúc" : "Hết vé" }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from "vue";
import { useCartStore } from "@/stores/cart.js";
import { useRouter } from "vue-router";

const router = useRouter();
const cartStore = useCartStore();
const props = defineProps({
  event: { type: Object, default: () => ({ schedules: [], ticket_types: [] }) },
});

// localCart: dùng để lưu số lượng người dùng đang chọn (ticketId -> quantity)
const localCart = reactive({});

// Format ngày giờ
function formatSchedule(schedule) {
  const start = new Date(schedule.start_datetime);
  const end = new Date(schedule.end_datetime);
  const startTime = start.toLocaleTimeString("vi-VN", {
    hour: "2-digit",
    minute: "2-digit",
  });
  const endTime = end.toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" });
  const startDate = start.toLocaleDateString("vi-VN", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
  return `${startTime} - ${endTime}, ${startDate}`;
}

function scheduleExpired(schedule) {
  const now = new Date();
  const end = new Date(schedule.end_datetime);
  return now > end;
}

function formatPrice(price) {
  return new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(
    price
  );
}

// Nút tăng số lượng
function increase(ticket, max) {
  const current = localCart[ticket.id] ?? 1;
  if (current < max) localCart[ticket.id] = current + 1;
}

// Nút giảm số lượng
function decrease(ticket) {
  const current = localCart[ticket.id] ?? 1;
  if (current > 1) localCart[ticket.id] = current - 1;
}

// --- LOGIC THÊM VÀO GIỎ HÀNG ---
async function handleAddToCart(ticket) {
  // Lấy số lượng đang chọn, mặc định là 1
  const quantity = localCart[ticket.id] ?? 1;

  // Gọi Store và đợi kết quả
  const success = await cartStore.addToCart(ticket, quantity);

  // Nếu thành công -> Reset số lượng chọn về 1
  if (success) {
    localCart[ticket.id] = 1;
  }
}

async function buyNow(ticket) {
  const quantity = localCart[ticket.id] ?? 1;

  const success = await cartStore.buyNow(ticket, quantity);

  if (success) {
    localCart[ticket.id] = 1;
    router.push("/checkout");
  }
}
</script>
