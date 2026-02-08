<template>
  <div
    class="py-10 px-4"
    style="background: linear-gradient(rgb(39, 39, 42) 48.04%, rgb(0, 0, 0) 100%)"
  >
    <div
      id="banner"
      class="flex flex-col md:flex-row bg-white shadow-lg rounded-xl overflow-hidden max-w-6xl mx-auto my-10"
    >
      <div
        class="flex-1 min-h-[350px] p-6 relative flex flex-col md:w-[30%] justify-between"
      >
        <div class="w-3 h-3 bg-green-500 rounded-full absolute -left-3 top-3"></div>
        <div class="w-3 h-3 bg-green-500 rounded-full absolute -left-3 top-16"></div>

        <div>
            <div class="text-gray-900 font-bold text-2xl mb-4">{{ event?.event_name }}</div>

            <div id="date" class="flex items-center gap-2 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-800" fill="currentColor" viewBox="0 0 21 20">
                <path d="M6.25 0a1 1 0 011 1v1h6V1a1 1 0 112 0v1h1a4 4 0 014 4v2h-20V6a4 4 0 014-4h1V1a1 1 0 011-1zM20.25 10h-20v8a2 2 0 002 2h16a2 2 0 002-2v-8z"></path>
            </svg>
            <span class="text-gray-900 text-sm font-medium"> {{ event?.schedules?.length ? formatSchedule(event.schedules[0]) : "Chưa có lịch" }}
            </span>
            </div>

            <div id="venue" class="flex items-center gap-2 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-800" fill="currentColor" viewBox="0 0 22 28">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M4.031 3.307a10.514 10.514 0 0113.937 0c4.485 3.945 4.955 10.854 1.058 15.392l-7.015 8.17a1.333 1.333 0 01-2.023 0l-7.015-8.17C-.923 14.161-.454 7.252 4.031 3.307zM11 14.667A3.333 3.333 0 1011 8a3.333 3.333 0 000 6.666z" />
            </svg>
            <span class="text-gray-900 text-sm font-medium">{{ event?.venue?.name }}</span> </div>

            <div id="address" class="text-gray-600 text-xs mb-4 ml-8 font-medium"> {{ event?.venue?.address }}
            </div>
        </div>

        <div>
            <div class="flex items-baseline gap-2 mt-2 mb-4">
                <span class="text-sm text-gray-600 font-bold">Giá từ</span> <span class="text-xl md:text-2xl font-bold text-green-600">
                    {{ new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(event?.ticket_types_min_base_price || 0) }}
                </span>
            </div>

            <button
                @click="scrollToTickets"
                class="bg-green-500 hover:bg-green-600 text-white w-full px-4 py-3 rounded-lg font-bold shadow-md transition-transform active:scale-95"
            >
                Mua vé ngay
            </button>

            <button 
                @click="openReportModal"
                class="mt-3 w-full text-xs font-bold text-gray-900 hover:text-red-600 flex items-center justify-center gap-1 transition-colors py-2 underline-offset-2 hover:underline"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-8a2 2 0 012-2h10a2 2 0 012 2v8m2-2h2a2 2 0 012 2v2a2 2 0 01-2 2H3v2m2-2h14a2 2 0 002-2v-2a2 2 0 00-2-2h-2m-4-6V5a2 2 0 012-2h6a2 2 0 012 2v2m-6 16h6m-6-12h6" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01" /> 
                </svg>
                Báo cáo sự kiện vi phạm
            </button>
        </div>
      </div>

      <div class="md:w-[70%] bg-gray-100">
        <img
          :src="event?.background_image_url || 'https://via.placeholder.com/800x400'"
          alt="Event Image"
          class="w-full h-full object-cover"
        />
      </div>
    </div>

    <div v-if="showReport" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 animate-fade-in-up">
            <h3 class="text-xl font-extrabold text-gray-900 mb-1">Báo cáo sự kiện</h3>
            <p class="text-sm text-gray-600 mb-5 font-medium">Hãy cho chúng tôi biết vấn đề của sự kiện này.</p>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-1.5">Lý do báo cáo <span class="text-red-600">*</span></label>
                    <select v-model="reportForm.reason" class="w-full border border-gray-300 bg-gray-50 rounded-lg p-2.5 text-gray-900 font-medium focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                        <option value="" disabled class="text-gray-400">Chọn lý do...</option>
                        <option value="Lừa đảo">Lừa đảo / Thông tin sai sự thật</option>
                        <option value="Nội dung cấm">Nội dung đồi trụy / Cấm</option>
                        <option value="Spam">Spam / Quảng cáo rác</option>
                        <option value="Khác">Lý do khác</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-1.5">Chi tiết <span class="text-gray-500 font-normal text-xs">(Tùy chọn)</span></label>
                    <textarea 
                        v-model="reportForm.description" 
                        rows="3" 
                        placeholder="Mô tả thêm về vi phạm..." 
                        class="w-full border border-gray-300 bg-gray-50 rounded-lg p-3 text-gray-900 font-medium focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none resize-none transition-all placeholder:text-gray-400"
                    ></textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-8">
                <button @click="showReport = false" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-100 font-bold text-gray-700 transition">
                    Hủy bỏ
                </button>
                <button 
                    @click="submitReport" 
                    :disabled="isSubmitting"
                    class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold shadow-md disabled:opacity-70 flex justify-center items-center gap-2 transition"
                >
                    <svg v-if="isSubmitting" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ isSubmitting ? 'Đang gửi...' : 'Gửi báo cáo' }}
                </button>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, ref } from "vue";
import axios from "axios";
import Swal from "sweetalert2"; 
// import { BASE_URL } from "@/config/const"; 

const props = defineProps({
  event: { type: Object, default: () => ({}) },
});

// --- STATE BÁO CÁO ---
const showReport = ref(false);
const isSubmitting = ref(false);
const reportForm = reactive({
    reason: "",
    description: ""
});

// --- HELPER FUNCTIONS ---
function formatSchedule(schedule) {
  if (!schedule) return "";
  const start = new Date(schedule.start_datetime);
  const end = new Date(schedule.end_datetime);

  const startTime = start.toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" });
  const endTime = end.toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" });
  const startDate = start.toLocaleDateString("vi-VN", { day: "2-digit", month: "long", year: "numeric" });

  return `${startTime} - ${endTime}, ${startDate}`;
}

function scrollToTickets() {
  const el = document.getElementById('ticket-section')
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

// --- LOGIC BÁO CÁO ---
const openReportModal = () => {
    reportForm.reason = "";
    reportForm.description = "";
    showReport.value = true;
};

const submitReport = async () => {
    if (!reportForm.reason) {
        Swal.fire({ icon: 'warning', title: 'Thiếu thông tin', text: 'Vui lòng chọn lý do báo cáo.' });
        return;
    }

    const token = sessionStorage.getItem('access_token');
    if (!token) {
        showReport.value = false;
        Swal.fire({
            icon: 'info',
            title: 'Yêu cầu đăng nhập',
            text: 'Bạn cần đăng nhập để thực hiện chức năng này.',
            showCancelButton: true,
            confirmButtonText: 'Đăng nhập ngay'
        }).then((result) => {
            if(result.isConfirmed) {
                // Redirect tới trang login
                window.location.href = '/login'; 
            }
        });
        return;
    }

    try {
        isSubmitting.value = true;
        
        const response = await axios.post('http://localhost:8000/api/reports', {
            type: 'event', 
            id: props.event.id, 
            reason: reportForm.reason,
            description: reportForm.description
        }, {
            headers: { Authorization: `Bearer ${token}` }
        });

        showReport.value = false;
        Swal.fire({
            icon: 'success',
            title: 'Đã gửi báo cáo',
            text: 'Cảm ơn bạn đã phản hồi. Chúng tôi sẽ xem xét sự kiện này.',
            timer: 2000,
            showConfirmButton: false
        });

    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: error.response?.data?.message || 'Không thể gửi báo cáo lúc này.'
        });
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<style scoped>
@keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in-up { animation: fadeInUp 0.3s ease-out forwards; }
</style>