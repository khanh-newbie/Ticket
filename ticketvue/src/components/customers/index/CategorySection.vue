<template>
    <div class="mt-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-white">{{ categoryName }}</h2>
            <button @click="viewMore"
                class="text-sm text-green-400 hover:text-green-300 transition-colors flex items-center gap-1">
                Xem thêm
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </div>

        <div v-if="loading" class="text-gray-400 text-sm">Đang tải...</div>

        <div v-else class="flex flex-wrap -mx-2">
            <div v-for="i in events" :key="i.id" class="w-1/4 flex-shrink-0 px-2 mb-4 cursor-pointer"
                @click="goToEventDetail(i.id)">
                <div class="bg-gray-800 rounded-xl h-full pb-2 hover:bg-gray-750 transition duration-200">
                    <img :src="i.background_image_url" class="w-full h-[180px] object-cover rounded-t-xl" alt="" />
                    <div class="px-2">
                        <div class="pt-2 min-h-[50px]">
                            <span class="text-white text-base font-bold line-clamp-2 leading-tight">
                                {{ i.event_name }}
                            </span>
                        </div>

                        <div class="flex flex-col mt-1">
                            <p class="text-base text-green-400 font-semibold">
                                Từ {{ new Intl.NumberFormat('vi-VN').format(i.ticket_types_min_base_price) }}đ
                            </p>

                            <p class="text-gray-400 text-xs flex items-center gap-1 mt-1">
                                <i class="fa-regular fa-calendar text-xs"></i>
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

            <div v-if="events.length === 0" class="text-gray-400 text-sm px-2 w-full">
                Hiện chưa có sự kiện nào thuộc danh mục này.
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

// Nhận ID và Tên danh mục từ Component Cha
const props = defineProps({
    categoryId: {
        type: [Number, String],
        required: true
    },
    categoryName: {
        type: String,
        required: true
    }
})

const router = useRouter()
const events = ref([])
const loading = ref(false)

// Format ngày hiển thị
const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

// Hàm lấy ngày hiện tại định dạng YYYY-MM-DD theo giờ địa phương
const getTodayDate = () => {
    const d = new Date();
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Gọi API lấy sự kiện
const getEventsByCategory = async () => {
    loading.value = true;
    try {
        const today = getTodayDate();

        const res = await axios.get('http://127.0.0.1:8000/api/search', {
            params: {
                cate: props.categoryId,
                limit: 10, // Lấy dư ra một chút để FE lọc lại cho chuẩn
                from: today 
            }
        });

        if (res.data.code === 200) {
            let rawData = res.data.data ? res.data.data : [];
            
            // --- LOGIC XỬ LÝ DỮ LIỆU ---
            const now = new Date();

            events.value = rawData
                .filter(item => {
                    // 1. Kiểm tra phải có ngày giờ
                    if (!item.schedules_min_start_datetime) return false;
                    
                    // 2. Lọc: Chỉ lấy sự kiện chưa diễn ra (Ngày bắt đầu >= Hiện tại)
                    // Lưu ý: Dùng new Date để so sánh chính xác cả giờ phút
                    const eventDate = new Date(item.schedules_min_start_datetime);
                    return eventDate >= now;
                })
                .sort((a, b) => {
                    // 3. Sắp xếp: Ngày gần nhất lên đầu (Tăng dần)
                    const dateA = new Date(a.schedules_min_start_datetime);
                    const dateB = new Date(b.schedules_min_start_datetime);
                    return dateA - dateB;
                })
                .slice(0, 4); // 4. Cắt lấy 4 sự kiện đầu tiên
        }
    } catch (err) {
        console.error(`Lỗi lấy danh mục ${props.categoryName}:`, err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    getEventsByCategory();
});

// Chuyển trang chi tiết
const goToEventDetail = (id) => {
    router.push({ path: '/event', query: { id } });
};

// Chuyển sang trang search xem tất cả (kèm filter from để đồng bộ)
const viewMore = () => {
    const today = getTodayDate();
    router.push({ 
        path: '/search', 
        query: { 
            cate: props.categoryId,
            from: today // Khi bấm xem thêm cũng lọc luôn các cái cũ
        } 
    });
}
</script>