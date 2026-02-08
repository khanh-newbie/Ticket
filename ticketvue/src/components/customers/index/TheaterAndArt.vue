<template>
  <div class="mt-8">
    <!-- Tiêu đề + nút xem thêm -->
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-bold text-white">Sân khấu & nghệ thuật</h2>
      <button class="text-sm text-green-400 hover:text-green-300 transition-colors flex items-center gap-1">
        Xem thêm
        <i class="fa-solid fa-arrow-right text-xs"></i>
      </button>
    </div>

    <!-- Danh sách sự kiện -->
    <div class="flex">
      <div v-for="i in state.arts" :key="i.id" class="w-1/4 flex-shrink-0 gap-4 px-2"  @click="goToEventDetail(i.id)">
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
              <!-- Dùng ngày min từ backend -->
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

      <!-- Nếu không có sự kiện nào -->
      <div v-if="state.arts.length == 0" class="text-gray-400 text-sm">
        Hiện chưa có sự kiện sân khấu & nghệ thuật sắp diễn ra.
      </div>
    </div>
  </div>
</template>


<script setup>
import { reactive, onMounted } from 'vue'
import axios from 'axios';
import { useRouter } from 'vue-router'  

const router = useRouter()
const state = reactive({
  arts: []
});

// Format ngày kiểu: "22 tháng 11, 2025"
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  });
};

const getArtEvents = async () => {
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/art-events-index');

    console.log('res', res);
    console.log('res.data', res.data);

    if (res.data.code === 200) {
      // Backend đã filter & limit 4 rồi, dùng trực tiếp
      state.arts = res.data.data;
      console.log('state.arts', state.arts);
    } else {
      console.error('API error:', res.data.message);
    }
  } catch (err) {
    console.error('API exception:', err);
  }
};

onMounted(() => {
  getArtEvents();
});

const goToEventDetail = (id) => {
  router.push({ path: '/event', query: { id } });
};
</script>