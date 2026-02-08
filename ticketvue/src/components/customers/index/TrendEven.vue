<template>
    <div class="mt-8">
        <h2 class="text-xl font-bold mb-4 text-white">🔥 Sự kiện xu hướng</h2>

        <div class="relative">
            <!-- Nút trái -->
            <button
                v-if="trendIndex > 0"
                @click="trendIndex--"
                class="absolute left-0 top-1/2 -translate-y-1/2 z-20 w-10 h-10
                       bg-black/50 hover:bg-black/70 text-white rounded-full
                       flex items-center justify-center"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Nút phải -->
            <button
                v-if="trendIndex < trendMaxIndex"
                @click="trendIndex++"
                class="absolute right-0 top-1/2 -translate-y-1/2 z-20 w-10 h-10
                       bg-black/50 hover:bg-black/70 text-white rounded-full
                       flex items-center justify-center"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Track -->
            <div class="overflow-hidden">
                <div
                    class="flex transition-transform duration-500 ease-out"
                    :style="{ transform: `translateX(-${trendIndex * 100}%)` }"
                >
                    <div
                        v-for="(item, index) in trendEvents"
                        :key="item.id ?? index"
                        class="w-1/5 flex-shrink-0 px-1 relative ml-1"
                        @click="goToEventDetail(item.id)"
                    >
                        <!-- Số thứ hạng -->
                        <span class="absolute left-2 bottom-2 text-4xl font-bold text-green-400 drop-shadow-md z-10">
                            {{ index + 1 }}
                        </span>

                        <!-- Ảnh -->
                        <img
                            :src="item.background_image_url || item.image || fallbackImage"
                            class="w-full h-auto object-cover rounded-xl"
                            alt="poster"
                        >

                        <!-- Info -->
                        <div class="mt-2">
                            <!-- <p class="text-white text-sm font-semibold line-clamp-2">
                                {{ item.event_name || item.title }}
                            </p>
                            <p class="text-green-400 text-sm">
                                Từ {{ formatPrice(item.ticket_types_min_base_price || item.price_raw || 0) }}đ
                            </p> -->
                            <p v-if="item.schedules_min_start_datetime || item.date"
                               class="text-gray-400 text-xs flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h.01a1 1 0 100-2H6zm2 0a1 1 0 000 2h.01a1 1 0 100-2H8zm2 0a1 1 0 000 2h.01a1 1 0 100-2H10zm2 0a1 1 0 000 2h.01a1 1 0 100-2H12zm-8 4a1 1 0 000 2h.01a1 1 0 100-2H6zm2 0a1 1 0 000 2h.01a1 1 0 100-2H8zm2 0a1 1 0 000 2h.01a1 1 0 100-2H10zm2 0a1 1 0 000 2h.01a1 1 0 100-2H12z"
                                        clip-rule="evenodd" />
                                </svg>
                                <!-- {{ formatDate(item.schedules_min_start_datetime || item.date) }} -->
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'  

const router = useRouter()

const trendIndex = ref(0)
const trendEvents = ref([])

// fallback image nếu backend không trả ảnh
const fallbackImage = 'http://127.0.0.1:8000/images/sk1.png'

// số slide tối đa: mỗi slide 5 item (w-1/5)
const trendMaxIndex = computed(() => {
    const total = trendEvents.value.length
    if (total <= 5) return 0
    return Math.ceil(total / 5) - 1
})

const formatPrice = (price) => {
    return Number(price || 0).toLocaleString('vi-VN')
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    })
}

const getTrendingEvents = async () => {
    try {
        const res = await axios.get('http://127.0.0.1:8000/api/trending-events-index')
        console.log('trending res', res.data)

        if (res.data.code === 200) {
            trendEvents.value = res.data.data || []
            trendIndex.value = 0
        } else {
            console.error('API trending error:', res.data.message)
        }
    } catch (err) {
        console.error('Trending request failed:', err)
    }
}

onMounted(() => {
    getTrendingEvents()
})

const goToEventDetail = (id) => {
  router.push({ path: '/event', query: { id } });
};
</script>
