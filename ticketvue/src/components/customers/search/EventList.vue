<!-- components/EventList.vue -->
<template>
  <div class="mt-8">
    <!-- Danh sách sự kiện -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <div v-for="i in events" :key="i.id" class="bg-transparent" @click="goToEventDetail(i.id)">
        <div>
          <img :src="i.background_image_url" class="w-full h-auto object-cover rounded-xl" alt="" />

          <div class="pt-2 min-h-[50px]">
            <span class="text-white text-base font-bold line-clamp-2 leading-tight">
              {{ i.event_name }}
            </span>
          </div>

          <div class="flex flex-col">
            <p class="text-base text-green-400">
              Từ {{ i.ticket_types_min_base_price }}đ
            </p>

            <p class="text-gray-400 text-xs flex items-center gap-1">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h.01a1 1 0 100-2H6zm2 0a1 1 0 000 2h.01a1 1 0 100-2H8zm2 0a1 1 0 000 2h.01a1 1 0 100-2H10zm2 0a1 1 0 000 2h.01a1 1 0 100-2H12zm-8 4a1 1 0 000 2h.01a1 1 0 100-2H6zm2 0a1 1 0 000 2h.01a1 1 0 100-2H8zm2 0a1 1 0 000 2h.01a1 1 0 100-2H10zm2 0a1 1 0 000 2h.01a1 1 0 100-2H12z"
                  clip-rule="evenodd" />
              </svg>

              <span v-if="i.schedules_min_start_datetime">
                {{ formatDate(i.schedules_min_start_datetime) }}
              </span>
              <span v-else>
                Đang cập nhật
              </span>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Nếu không có sự kiện nào -->
    <div v-if="!events || events.length === 0" class="text-gray-400 text-sm mt-4">
      Hiện chưa có sự kiện nào.
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'; 

const router = useRouter();
const props = defineProps({
  title: {
    type: String,
    default: "Sự kiện",
  },
  events: {
    type: Array,
    default: () => [],
  },
});

const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString("vi-VN", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
};
const goToEventDetail = (id) => {
  router.push({ path: '/event', query: { id } });
};
</script>
