<template>
  <div class="h-screen bg-gray-50 flex overflow-hidden">
    
    <OrganizerSidebar class="flex-shrink-0" />

    <div class="flex-1 flex flex-col min-w-0">
      
      <OrganizerHeader class="flex-shrink-0" />

      <main class="flex-1 overflow-y-auto ml-84 w-[calc(100%-21rem)] p-6 bg-gray-50 scroll-smooth">
        <div class="max-w-7xl mx-auto pb-10">
          
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Quản lý sự kiện</h1>
             <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium shadow transition flex items-center gap-2">
              <span>+ Tạo sự kiện mới</span>
            </button> 
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 sticky top-15 z-10">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
              
              <div class="relative w-full md:w-96">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input 
                  v-model="keyword" 
                  @keyup.enter="handleSearch"
                  type="text" 
                  class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition" 
                  placeholder="Tìm tên sự kiện..." 
                />
              </div>

              <div class="flex flex-wrap gap-1 bg-gray-100 p-1 rounded-lg">
                <button
                  v-for="tab in statusTabs"
                  :key="tab.value"
                  @click="changeTab(tab.value)"
                  class="px-4 py-2 text-sm font-medium rounded-md transition-all duration-200"
                  :class="currentStatus === tab.value 
                    ? 'bg-white text-green-700 shadow-sm' 
                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200'"
                >
                  {{ tab.label }}
                </button>
              </div>
            </div>
          </div>

          <div v-if="loading" class="flex justify-center items-center h-64 bg-white rounded-xl shadow-sm">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
          </div>

          <div v-else-if="events.length === 0" class="flex flex-col items-center justify-center h-64 bg-white rounded-xl shadow-sm text-gray-500">
            <p class="text-lg font-medium">Không tìm thấy sự kiện nào</p>
          </div>

          <div v-else class="space-y-4">
            <div 
              v-for="event in events" 
              :key="event.id" 
              @click="goToDetail(event.id)"
              class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md hover:border-green-200 transition-all duration-200 flex flex-col md:flex-row gap-4 cursor-pointer group"
            >
              <div class="w-full md:w-32 h-32 flex-shrink-0">
                <img 
                  :src="event.poster_image_url || 'https://via.placeholder.com/300x400?text=No+Image'" 
                  class="w-full h-full object-cover rounded-lg bg-gray-200 group-hover:opacity-90 transition"
                  alt="Event Poster"
                />
              </div>

              <div class="flex-1 flex flex-col justify-between">
                <div>
                  <div class="flex justify-between items-start">
                    <h3 class="text-lg font-bold text-gray-800 line-clamp-1 group-hover:text-green-600 transition">
                      {{ event.event_name }}
                    </h3>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold border whitespace-nowrap ml-2" :class="getStatusColor(event.status)">
                      {{ getStatusLabel(event.status) }}
                    </span>
                  </div>

                  <div class="mt-2 space-y-1 text-sm text-gray-600">
                    <p class="flex items-center gap-2">
                      📅 <span>{{ formatDate(event.schedules_min_start_datetime) }}</span>
                    </p>
                    <p class="flex items-center gap-2">
                      📍 <span>{{ event.venue ? event.venue.name : 'Chưa cập nhật địa điểm' }}</span>
                    </p>
                  </div>
                </div>

                <div class="mt-3 flex justify-between items-end border-t pt-3 border-gray-50">
                   <div class="text-sm">
                      Giá từ: <span class="text-green-600 font-bold text-base">{{ formatCurrency(event.ticket_types_min_base_price) }}</span>
                   </div>
                   <div class="flex gap-2">
                      <!-- <button @click.stop="goToDetail(event.id)" class="text-gray-500 hover:text-green-600 text-sm font-medium px-3 py-1 bg-gray-50 rounded hover:bg-green-50 transition">
                          Chi tiết
                      </button>
                      <button @click.stop="" class="text-gray-500 hover:text-blue-600 text-sm font-medium px-3 py-1 bg-gray-50 rounded hover:bg-blue-50 transition">
                          Chỉnh sửa
                      </button> -->
                   </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="pagination.total > 0" class="mt-8 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <span class="text-sm text-gray-500">
              Trang {{ pagination.current_page }} / {{ Math.ceil(pagination.total / pagination.per_page) }}
            </span>
            <div class="flex gap-2">
              <button 
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-4 py-2 border rounded-lg text-sm font-medium disabled:opacity-50 hover:bg-gray-50"
              >
                Trước
              </button>
              <button 
                @click="changePage(pagination.current_page + 1)"
                :disabled="events.length < pagination.per_page || (pagination.current_page * pagination.per_page) >= pagination.total"
                class="px-4 py-2 border rounded-lg text-sm font-medium disabled:opacity-50 hover:bg-gray-50"
              >
                Sau
              </button>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router"; // 1. Import Router
import OrganizerSidebar from "@/components/organizers/OrganizerSidebar.vue";
import OrganizerHeader from "@/components/organizers/OrganizerHeader.vue";

const router = useRouter(); // 2. Khởi tạo Router

// --- STATE ---
const loading = ref(false);
const events = ref([]);
const pagination = ref({ total: 0, current_page: 1, per_page: 10 });
const keyword = ref("");
const currentStatus = ref("upcoming");
const accessToken = sessionStorage.getItem("access_token");

// --- CONFIG ---
const statusTabs = [
  { label: "Sắp tới", value: "upcoming" },
  { label: "Chờ duyệt", value: "pending" },
  { label: "Bản nháp", value: "draft" },
  { label: "Đã qua", value: "past" },
  { label: "Đã hủy", value: "cancelled" },
];

// --- NAVIGATION FUNCTION ---
// 3. Hàm chuyển trang
const goToDetail = (eventId) => {
    router.push(`/event?id=${eventId}`);
    // Hoặc nếu bạn dùng name route:
    // router.push({ name: 'OrganizerEventDetail', params: { id: eventId } });
};

// --- HELPERS & API (GIỮ NGUYÊN) ---
const getStatusLabel = (statusId) => {
    switch (statusId) {
        case 1: return "Bản nháp";
        case 2: return "Chờ duyệt";
        case 3: return "Đã công bố";
        case 4: return "Đã hủy";
        case 5: return "Đã kết thúc";
        default: return "Không xác định";
    }
};

const getStatusColor = (statusId) => {
    switch (statusId) {
        case 3: return "bg-green-100 text-green-700 border-green-200";
        case 5: return "bg-blue-100 text-blue-700 border-blue-200";
        case 2: return "bg-yellow-100 text-yellow-700 border-yellow-200";
        case 1: return "bg-gray-100 text-gray-600 border-gray-200";
        case 4: return "bg-red-100 text-red-600 border-red-200";
        default: return "bg-gray-50 text-gray-500 border-gray-200";
    }
};

const formatCurrency = (value) => {
    if (value === undefined || value === null) return "Liên hệ";
    if (Number(value) === 0) return "Miễn phí";
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return "Chưa có lịch diễn";
    const date = new Date(dateString);
    return date.toLocaleString("vi-VN", {
        hour: '2-digit', minute: '2-digit',
        day: '2-digit', month: '2-digit', year: 'numeric'
    });
};

async function fetchEvents() {
    loading.value = true;
    try {
        const response = await axios.get("http://localhost:8000/api/organizer/my-events", {
            params: {
                keyword: keyword.value,
                status: currentStatus.value,
                page: pagination.value.current_page,
                limit: pagination.value.per_page
            },
            headers: { Authorization: `Bearer ${accessToken}` }
        });

        if (response.data.code === 200) {
            events.value = response.data.data.events;
            pagination.value = response.data.data.pagination;
        }
    } catch (error) {
        console.error("Lỗi:", error);
    } finally {
        loading.value = false;
    }
}

const handleSearch = () => { pagination.value.current_page = 1; fetchEvents(); };
const changeTab = (status) => { currentStatus.value = status; pagination.value.current_page = 1; keyword.value = ""; fetchEvents(); };
const changePage = (page) => { pagination.value.current_page = page; fetchEvents(); };

onMounted(() => { fetchEvents(); });
</script>

<style scoped>
/* Scrollbar đẹp hơn */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: #f1f1f1; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>