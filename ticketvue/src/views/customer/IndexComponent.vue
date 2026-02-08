<template>
    <div class="min-w-[400px]">
        <div class="min-h-screen bg-gray-700 text-white font-sans">
            <HeaderComponent @search="onSearch" @reset="resetSearch" />
            <NavBarComponent />

            <main class="container mx-auto p-4">
                <div v-if="isSearching" class="mt-8">
                    <h2 class="text-xl font-bold text-white mb-4">Kết quả tìm kiếm</h2>
                    <div class="flex flex-wrap">
                        <div v-for="i in searchResults" :key="i.id" class="w-1/4 px-2 mb-6 cursor-pointer"
                            @click="goToEventDetail(i.id)">
                            <img :src="i.background_image_url" class="w-full h-[180px] object-cover rounded-xl" />
                            <div class="pt-2">
                                <span class="text-white font-bold">{{ i.event_name }}</span>
                                <p class="text-green-400">Từ {{ i.ticket_types_min_base_price }}đ</p>
                            </div>
                        </div>
                    </div>
                </div>

                <template v-else>
                    <TrendEven />
                    <WeekendAndEndOfMonth />

                    <div v-if="state.categories.length > 0">
                        <CategorySection v-for="cat in state.categories" :key="cat.id" :categoryId="cat.id"
                            :categoryName="cat.name" />
                    </div>

                    <div v-else class="text-center mt-10 text-gray-400">
                        Đang tải danh mục sự kiện...
                    </div>

                    <InterestingDestination />
                </template>

            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

// Import Components
import HeaderComponent from '@/components/customers/layouts/HeaderComponent.vue';
import NavBarComponent from '@/components/customers/layouts/NavBarComponent.vue';
import TrendEven from '@/components/customers/index/TrendEven.vue';
import WeekendAndEndOfMonth from '@/components/customers/index/WeekendAndEndOfMonth.vue';
import InterestingDestination from '@/components/customers/index/InterestingDestination.vue';

// Import Component Mới
import CategorySection from '@/components/customers/index/CategorySection.vue';

const router = useRouter()
const isSearching = ref(false)
const searchResults = ref([])

const state = reactive({
    user_info: sessionStorage.getItem('user_info'),
    access_token: sessionStorage.getItem('access_token'),
    // Mảng chứa danh sách Category lấy từ DB
    categories: []
})

// --- CÁC HÀM XỬ LÝ SEARCH (Giữ nguyên) ---
const onSearch = async (keyword) => {
    // ... logic search cũ của bạn
    try {
        isSearching.value = true
        const res = await axios.get('http://127.0.0.1:8000/api/search', { params: { q: keyword } })
        searchResults.value = res.data.data ?? []
    } catch (err) {
        console.error(err)
    }
}

const resetSearch = () => {
    isSearching.value = false
    searchResults.value = []
}

const goToEventDetail = (id) => {
    router.push({ path: '/event', query: { id } });
};

// --- HÀM LẤY DANH MỤC TỪ DB ---
const fetchCategories = async () => {
    try {
        // Gọi API lấy tất cả danh mục
        // Đảm bảo Laravel có route: Route::get('/categories', ...)
        const response = await axios.get('http://127.0.0.1:8000/api/categories');

        if (response.data && response.data.code === 200) {
            state.categories = response.data.data;
        }
    } catch (error) {
        console.error("Lỗi lấy danh mục:", error);
    }
}

onMounted(() => {
    fetchCategories();
});
</script>