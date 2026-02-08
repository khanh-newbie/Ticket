<template>
    <HeaderComponent />
    <div class="min-h-screen bg-black text-white px-10 py-6">
        <div class="container mx-auto relative">
            <h2 class="mb-4 font-semibold text-lg">Kết quả tìm kiếm: <span v-if="searchText" class="text-green-500">"{{
                    searchText }}"</span></h2>

            <div class="flex items-center justify-end mb-6 gap-4">
                <DateFilter v-model="dateRange" />

                <div class="relative flex items-center gap-2">

                    <button
                        class="flex items-center gap-2 rounded-full bg-green-600 px-4 h-10 text-sm font-medium text-white hover:bg-green-700 transition"
                        @click="showFilter = !showFilter">
                        <span>🧪</span> <span>Bộ lọc</span> <span>▼</span>
                    </button>

                    <div class="flex items-center gap-2">
                        <button v-if="filters.location && filters.location !== 'all'"
                            class="flex items-center gap-1 rounded-full bg-green-600 px-4 h-10 text-sm font-medium text-white hover:bg-green-700 transition"
                            @click="filters.location = 'all'">
                            <span>✕</span> <span>{{ locationLabelMap[filters.location] || filters.location }}</span>
                        </button>

                        <button v-if="filters.freeOnly"
                            class="flex items-center gap-1 rounded-full bg-green-600 px-4 h-10 text-sm font-medium text-white hover:bg-green-700 transition"
                            @click="filters.freeOnly = false">
                            <span>✕</span> <span>Miễn phí</span>
                        </button>

                        <button v-for="catId in filters.categories" :key="catId"
                            class="flex items-center gap-1 rounded-full bg-green-600 px-4 h-10 text-sm font-medium text-white hover:bg-green-700 transition"
                            @click="removeCategory(catId)">
                            <span>✕</span> <span>{{ getCategoryName(catId) }}</span>
                        </button>
                    </div>

                    <div v-if="showFilter" class="absolute right-0 top-full mt-2 z-50">
                        <FilterPanel v-model="filters" :categories="categoriesList" @close="showFilter = false"
                            @reset="resetFilters" />
                    </div>
                </div>
            </div>

            <EventList :events="filteredEvents" />

            <div v-if="isLoadingMore" class="mt-8 flex justify-center py-4">
                <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-green-500"></div>
            </div>

            <div v-if="!hasMore && events.length > 0" class="mt-8 text-center text-gray-600 text-sm pb-10">
                Đã hiển thị tất cả kết quả.
            </div>

            <div v-if="!isInitializing && events.length === 0" class="text-center text-gray-400 mt-10">
                Không tìm thấy sự kiện nào phù hợp.
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from "vue"; // Thêm onUnmounted
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

import HeaderComponent from "@/components/customers/layouts/HeaderComponent.vue";
import DateFilter from "@/components/customers/search/DateFilter.vue";
import FilterPanel from "@/components/customers/search/FilterPanel.vue";
import EventList from "@/components/customers/search/EventList.vue";

const route = useRoute();
const router = useRouter();

/* ===== CONFIG ===== */
const PAGE_SIZE = 12;

/* ===== STATE ===== */
const events = ref([]);
const categoriesList = ref([]);
const showFilter = ref(false);
const searchText = ref("");
const isInitializing = ref(true);
const isLoadingMore = ref(false);
const currentPage = ref(1);
const hasMore = ref(true);

const filters = ref({
    location: "all",
    categories: [],
    freeOnly: false,
});
const dateRange = ref({ from: null, to: null });

const locationLabelMap = {
    'all': 'Toàn quốc', '79': 'TP. Hồ Chí Minh', '01': 'Hà Nội', '48': 'Đà Nẵng', '42': 'Lâm Đồng (Đà Lạt)', 'other': 'Khác'
};

/* ===== API CALLS ===== */
const fetchCategories = async () => {
    try {
        const res = await axios.get("http://127.0.0.1:8000/api/categories");
        if (res.data.code === 200) categoriesList.value = res.data.data || [];
    } catch (e) { console.error(e); }
};

const getCategoryName = (id) => {
    const cat = categoriesList.value.find(c => c.id == id);
    return cat ? cat.name : `Danh mục ${id}`;
};

const removeCategory = (id) => filters.value.categories = filters.value.categories.filter(c => c != id);

const buildApiParamsFromState = () => {
    const params = { limit: PAGE_SIZE, page: currentPage.value };
    if (searchText.value.trim()) params.q = searchText.value.trim();
    if (filters.value.location !== "all") params.local = filters.value.location;
    if (dateRange.value.from) params.from = dateRange.value.from;
    if (dateRange.value.to) params.to = dateRange.value.to;
    if (filters.value.freeOnly) params.price = "free";
    if (filters.value.categories.length > 0) params.cate = filters.value.categories.join(",");
    return params;
};

const getSearchEvents = async (append = false) => {
    if (append) isLoadingMore.value = true;

    try {
        const params = buildApiParamsFromState();
        const res = await axios.get("http://127.0.0.1:8000/api/search", { params });
        const newEvents = res.data.code === 200 ? (res.data.data || []) : [];

        if (newEvents.length < PAGE_SIZE) hasMore.value = false;
        else hasMore.value = true;

        if (append) events.value = [...events.value, ...newEvents];
        else events.value = newEvents;
    } catch (err) {
        console.error(err);
        if (!append) events.value = [];
    } finally {
        isLoadingMore.value = false;
    }
};

/* ===== INFINITE SCROLL LOGIC ===== */
const handleScroll = () => {
    // 1. Nếu đang tải hoặc đã hết dữ liệu thì dừng
    if (isLoadingMore.value || !hasMore.value) return;

    // 2. Tính toán vị trí
    // window.scrollY: Vị trí cuộn hiện tại
    // window.innerHeight: Chiều cao màn hình hiển thị
    // document.documentElement.offsetHeight: Chiều cao toàn bộ trang web

    // Logic: Nếu (Vị trí hiện tại + Chiều cao màn hình) >= (Chiều cao trang - 200px buffer)
    if ((window.innerHeight + window.scrollY) >= document.documentElement.offsetHeight - 200) {
        loadMore();
    }
};

const loadMore = async () => {
    currentPage.value++;
    await getSearchEvents(true);
};

/* ===== LIFECYCLE ===== */
onMounted(async () => {
    await Promise.all([fetchCategories(), initFromQuery()]);

    // Lắng nghe sự kiện scroll
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    // Xóa sự kiện khi rời trang để tránh lỗi memory leak
    window.removeEventListener('scroll', handleScroll);
});

/* ===== INIT & WATCHERS ===== */
const initFromQuery = async () => {
    const q = route.query;
    searchText.value = (q.q || "").toString();
    filters.value.location = locationLabelMap[q.local] ? q.local : (q.local || "all");
    dateRange.value = { from: q.from || null, to: q.to || null };
    filters.value.freeOnly = q.price === "free";
    filters.value.categories = q.cate ? q.cate.toString().split(",") : [];

    currentPage.value = 1;
    hasMore.value = true;
    await getSearchEvents(false);
    isInitializing.value = false;
};

const buildQueryFromState = () => {
    // ... (Giống code cũ)
    const query = {};
    if (searchText.value.trim()) query.q = searchText.value.trim();
    if (filters.value.location !== "all") query.local = filters.value.location;
    if (dateRange.value.from) query.from = dateRange.value.from;
    if (dateRange.value.to) query.to = dateRange.value.to;
    if (filters.value.freeOnly) query.price = "free";
    if (filters.value.categories.length > 0) query.cate = filters.value.categories.join(",");
    return query;
};

const resetFilters = () => {
    filters.value = { location: "all", categories: [], freeOnly: false };
    dateRange.value = { from: null, to: null };
};

watch([filters, dateRange], async () => {
    if (isInitializing.value) return;
    currentPage.value = 1;
    hasMore.value = true;
    router.replace({ query: buildQueryFromState() });
    await getSearchEvents(false);
}, { deep: true });

watch(() => route.query.q, async (newQ) => {
    searchText.value = (newQ || "").toString();
    currentPage.value = 1;
    hasMore.value = true;
    await getSearchEvents(false);
});

const filteredEvents = computed(() => events.value);
</script>