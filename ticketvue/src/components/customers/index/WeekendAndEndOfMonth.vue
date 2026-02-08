<template>
    <div class="mt-8">
        <div class="relative">
            <div class="flex items-center justify-between border-b border-gray-600 pb-2">
                <div class="flex gap-8 text-lg font-medium">
                    <button
                        @click="setActive('weekend')"
                        :class="active === 'weekend' ? 'text-white' : 'text-gray-500'"
                        class="relative transition-colors"
                    >
                        Cuối tuần này
                        <span
                            v-if="active === 'weekend'"
                            class="absolute left-0 -bottom-[2px] w-full h-[3px] bg-green-500 rounded-full"
                        ></span>
                    </button>

                    <button
                        @click="setActive('month')"
                        :class="active === 'month' ? 'text-white' : 'text-gray-500'"
                        class="relative transition-colors"
                    >
                        Tháng này
                        <span
                            v-if="active === 'month'"
                            class="absolute left-0 -bottom-[2px] w-full h-[3px] bg-green-500 rounded-full"
                        ></span>
                    </button>
                </div>

                <button 
                    @click="handleViewMore"
                    class="text-sm text-green-400 hover:text-green-300 transition-colors flex items-center gap-1">
                    Xem thêm
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </div>

            <div class="relative mt-8">
                <div v-if="displayedEvents.length === 0" class="text-gray-400 text-sm">
                    Hiện chưa có sự kiện nào trong khoảng thời gian này.
                </div>

                <div v-else class="relative group/slider">
                    <button
                        v-if="currentIndex > 0"
                        @click="prevSlide"
                        class="absolute -left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10
                               bg-black/60 hover:bg-black/90 text-white rounded-full
                               flex items-center justify-center transition-all backdrop-blur-sm opacity-0 group-hover/slider:opacity-100"
                    >
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <button
                        v-if="currentIndex < maxIndex"
                        @click="nextSlide"
                        class="absolute -right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10
                               bg-black/60 hover:bg-black/90 text-white rounded-full
                               flex items-center justify-center transition-all backdrop-blur-sm opacity-0 group-hover/slider:opacity-100"
                    >
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>

                    <div class="overflow-hidden rounded-2xl">
                        <div
                            class="flex transition-transform duration-500 ease-out"
                            :style="{ transform: `translateX(-${currentIndex * 100}%)` }"
                        >
                            <div
                                v-for="(event, index) in displayedEvents"
                                :key="event.id ?? index"
                                class="w-1/4 flex-shrink-0 gap-4 px-2 cursor-pointer group"
                                @click="goToEventDetail(event.id)"
                            >
                                <div>
                                    <div class="relative overflow-hidden rounded-xl aspect-[2/3]">
                                        <img
                                            :src="event.poster_image_url || fallbackImage"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                            alt="poster"
                                        />
                                    </div>

                                    <div class="pt-3 min-h-[50px]">
                                        <span class="text-white text-base font-bold line-clamp-2 leading-tight group-hover:text-green-400 transition-colors">
                                            {{ event.event_name }}
                                        </span>
                                    </div>

                                    <div class="flex flex-col mt-1">
                                        <p class="text-base text-green-400 font-semibold">
                                            Từ
                                            {{
                                                formatPrice(
                                                    event.ticket_types_min_base_price ??
                                                    event.ticketTypes_min_base_price ??
                                                    0
                                                )
                                            }}đ
                                        </p>
                                        <p
                                            class="text-gray-400 text-xs flex items-center gap-1 mt-1"
                                            v-if="event.schedules_min_start_datetime"
                                        >
                                            <i class="fa-regular fa-calendar text-xs"></i>
                                            {{ formatDate(event.schedules_min_start_datetime) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="w-1/4 flex-shrink-0 gap-4 px-2 cursor-pointer group" @click="handleViewMore">
                                <div
                                    class="bg-[#1a1a1a]/90 backdrop-blur-md rounded-2xl w-full h-full aspect-[2/3]
                                           flex flex-col items-center justify-center text-white
                                           border border-gray-700/50 group-hover:border-green-500 transition-all"
                                >
                                    <p class="text-lg font-medium mb-3 text-gray-300 group-hover:text-green-400 transition-colors">Khám phá thêm</p>
                                    <div class="bg-gray-700 w-12 h-12 rounded-full flex items-center justify-center group-hover:bg-green-600 transition-colors shadow-lg">
                                        <i class="fa-solid fa-arrow-right text-xl"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'  

const router = useRouter()

const active = ref('weekend')

const state = reactive({
    weekendEvents: [],
    endOfMonthEvents: [],
})

const currentIndex = ref(0)

// Fallback image nếu event không có poster_image_url
const fallbackImage = 'http://127.0.0.1:8000/images/sk1.png'

// Events hiển thị theo tab
const displayedEvents = computed(() => {
    return active.value === 'weekend'
        ? state.weekendEvents
        : state.endOfMonthEvents
})

// Tổng số item + 1 (ô khám phá thêm)
const maxIndex = computed(() => {
    const totalItems = displayedEvents.value.length + 1 // +1 là ô "Khám phá thêm"
    if (totalItems <= 4) return 0
    return Math.ceil(totalItems / 4) - 1
})

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    })
}

const formatPrice = (price) => {
    return Number(price || 0).toLocaleString('vi-VN')
}

const getWeekendEvents = async () => {
    try {
        const res = await axios.get('http://127.0.0.1:8000/api/weekend-events-index')
        if (res.data.code === 200) {
            state.weekendEvents = res.data.data
        } else {
            console.error('API weekend error:', res.data.message)
        }
    } catch (error) {
        console.error('Weekend request failed:', error)
    }
}

const getEndOfMonthEvents = async () => {
    try {
        const res = await axios.get('http://127.0.0.1:8000/api/end-of-month-events-index')
        if (res.data.code === 200) {
            state.endOfMonthEvents = res.data.data
        } else {
            console.error('API endOfMonth error:', res.data.message)
        }
    } catch (error) {
        console.error('EndOfMonth request failed:', error)
    }
}

const setActive = (tab) => {
    active.value = tab
    currentIndex.value = 0
}

const nextSlide = () => {
    if (currentIndex.value < maxIndex.value) {
        currentIndex.value++
    }
}

const prevSlide = () => {
    if (currentIndex.value > 0) {
        currentIndex.value--
    }
}

// Xử lý nút Xem thêm / Khám phá thêm
const handleViewMore = () => {
    const today = new Date();
    let fromDate = '';
    let toDate = '';

    if (active.value === 'weekend') {
        const dayOfWeek = today.getDay(); // 0(CN) -> 6(T7)
        // Tìm thứ 7 gần nhất
        const daysToSat = (6 - dayOfWeek + 7) % 7; 
        
        const saturday = new Date(today);
        saturday.setDate(today.getDate() + daysToSat);
        
        const sunday = new Date(saturday);
        sunday.setDate(saturday.getDate() + 1);

        fromDate = saturday.toISOString().split('T')[0];
        toDate = sunday.toISOString().split('T')[0];
    } else {
        // Từ hôm nay đến cuối tháng
        fromDate = today.toISOString().split('T')[0];
        const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        toDate = lastDayOfMonth.toISOString().split('T')[0];
    }

    // Chuyển sang màn hình tìm kiếm với query
    router.push({ 
        path: '/search', 
        query: { from: fromDate, to: toDate } 
    });
}

const goToEventDetail = (id) => {
  router.push({ path: '/event', query: { id } });
};

onMounted(() => {
    getWeekendEvents()
    getEndOfMonthEvents()
})
</script>