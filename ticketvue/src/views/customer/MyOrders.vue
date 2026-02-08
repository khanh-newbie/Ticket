<template>
  <div>
    <HeaderComponent />
    <NavBarComponent />

    <main class="flex-1 container mx-auto p-4 pb-24 max-w-5xl">
      
      <div class="flex items-center gap-3 mb-6">
         <div class="bg-orange-100 p-2.5 rounded-xl text-orange-600 shadow-sm">
            <i class="fas fa-receipt text-xl"></i>
         </div>
         <h1 class="text-2xl font-bold text-gray-800">Quản lý đơn hàng</h1>
      </div>

      <div class="flex gap-3 mb-6 overflow-x-auto pb-2 scrollbar-hide">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          @click="activeTab = tab.value"
          class="px-5 py-2 rounded-full border text-sm font-medium transition-all whitespace-nowrap"
          :class="activeTab === tab.value
            ? 'bg-orange-600 text-white border-orange-600 shadow-md'
            : 'bg-white text-gray-600 border-gray-200 hover:border-orange-400 hover:text-orange-500'"
        >
          {{ tab.label }}
        </button>
      </div>

      <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
        <div class="animate-spin rounded-full h-10 w-10 border-4 border-gray-200 border-t-orange-500 mb-4"></div>
        <p class="text-gray-500 text-sm font-medium">Đang tải dữ liệu...</p>
      </div>

      <div v-else-if="filteredOrders.length === 0" class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="text-6xl mb-4 opacity-30">📂</div>
        <p class="text-gray-500 text-lg font-medium">Không tìm thấy đơn hàng nào.</p>
        <button @click="$router.push('/')" class="mt-4 text-orange-600 hover:underline font-medium">
           Đặt vé ngay
        </button>
      </div>

      <div v-else class="space-y-8">
        
        <div 
          v-for="order in filteredOrders" 
          :key="order.id"
          class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-300"
        >
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center px-6 py-4 border-b bg-gray-50/80 gap-3">
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4">
              <span class="font-bold text-gray-800 text-lg">#{{ order.code }}</span>
              <span class="text-xs text-gray-300 hidden sm:inline">|</span>
              <span class="text-sm text-gray-500">
                 Ngày đặt: <span class="font-medium text-gray-700">{{ formatDateOnly(order.createdAt) }}</span>
              </span>
            </div>

            <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
              <span class="font-bold text-orange-600 text-lg">
                {{ formatCurrency(order.totalPrice) }}
              </span>
              <span
                class="text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide border"
                :class="statusClass(order.status)"
              >
                {{ statusDisplay(order.status) }}
              </span>
            </div>
          </div>

          <div class="divide-y divide-gray-100">
            <div 
                v-for="event in order.events" 
                :key="event.eventId" 
                class="p-6 flex flex-col md:flex-row gap-6"
            >
                <div class="flex-shrink-0 md:w-3/12">
                    <div class="group">
                        <div class="relative overflow-hidden rounded-lg shadow-sm border border-gray-100 mb-3 w-24 h-24 md:w-full md:h-auto md:aspect-[4/3]">
                            <img 
                                :src="event.eventImage" 
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                                alt="Poster"
                            />
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-800 leading-tight mb-2 line-clamp-2">
                                {{ event.eventName }}
                            </h3>
                            <div class="flex items-start gap-2 text-sm text-gray-500">
                                <i class="fas fa-map-marker-alt w-4 text-center mt-1 text-red-500"></i>
                                <span class="line-clamp-2">{{ event.location }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-1 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div 
                            v-for="schedule in event.schedules" 
                            :key="schedule.scheduleId"
                            class="bg-gray-50 rounded-xl border border-gray-200/80 p-4 relative"
                        >
                            <div class="flex items-center gap-2 mb-3">
                                 <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-md text-xs font-bold shadow-sm flex items-center gap-2">
                                    <i class="far fa-clock"></i>
                                    {{ formatDateTime(schedule.startDate) }}
                                </span>
                                <span v-if="schedule.endDate" class="text-xs text-gray-400 flex items-center gap-1">
                                    <i class="fas fa-arrow-right"></i> {{ formatTimeOnly(schedule.endDate) }}
                                </span>
                            </div>

                            <div class="bg-white rounded-lg border border-gray-100 px-3 py-2 shadow-sm">
                                 <div 
                                    v-for="(ticket, idx) in schedule.tickets" 
                                    :key="idx" 
                                    class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0 border-dashed"
                                >
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs bg-gray-100 text-gray-600 font-bold px-2 py-1 rounded border border-gray-200">
                                            x{{ ticket.quantity }}
                                        </span>
                                        <span class="text-sm font-medium text-gray-700">{{ ticket.ticketName }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-600">
                                        {{ formatCurrency(ticket.total) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                        <div v-if="event.review" class="flex items-center gap-3 bg-yellow-50 px-4 py-2 rounded-lg border border-yellow-100 w-full md:w-auto">
                            <div class="flex flex-col">
                                <div class="flex text-yellow-400 text-xs mb-1">
                                    <i v-for="n in 5" :key="n" class="fas" :class="n <= event.review.rating ? 'fa-star' : 'fa-star-o'"></i>
                                </div>
                                <span class="text-xs font-bold text-yellow-600">
                                    {{ event.review.status === 'pending' ? 'Đang chờ duyệt' : 'Đánh giá của bạn' }}
                                </span>
                            </div>
                            <div class="h-8 w-[1px] bg-yellow-200 mx-2 hidden md:block"></div>
                            <span class="text-sm text-gray-600 italic truncate max-w-xs">"{{ event.review.comment }}"</span>
                        </div>

                        <button
                            v-else-if="order.status === 'paid'"
                            @click="openReview(order, event)"
                            class="w-full md:w-auto px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-lg hover:bg-orange-600 shadow-sm transition flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-pen-nib"></i> Viết đánh giá
                        </button>
                    </div>
                </div>
            </div>
          </div>

          <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex justify-end items-center">
             <button
              @click="openOrderDetail(order.id)"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm flex items-center gap-2"
            >
              <i class="fas fa-ticket-alt text-orange-500"></i> Chi tiết & Lấy vé
            </button>
          </div>

        </div>
      </div>
    </main>

    <div v-if="showReview" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 animate-fade-in-up">
        <div class="text-center mb-6">
          <h3 class="text-xl font-bold text-gray-800">Đánh giá Sự kiện</h3>
          <p class="text-sm text-orange-600 font-medium mt-1">{{ selectedEventName }}</p>
        </div>

        <div class="flex justify-center gap-3 text-3xl text-gray-200 mb-6 cursor-pointer select-none">
          <i
            v-for="i in 5"
            :key="i"
            class="fas fa-star transition-all duration-200 hover:scale-110"
            :class="{ 'text-yellow-400 drop-shadow-sm': i <= rating }"
            @click="rating = i"
          ></i>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">Trải nghiệm của bạn</label>
          <textarea
            v-model="comment"
            rows="4"
            placeholder="Chương trình thế nào? Âm thanh, ánh sáng ra sao?..."
            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none resize-none text-sm"
          ></textarea>
        </div>

        <div class="flex gap-3">
          <button @click="closeReview" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
            Đóng
          </button>
          <button 
            @click="submitReview" 
            class="flex-1 px-4 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-medium shadow-lg shadow-orange-200 transition disabled:opacity-70"
            :disabled="isSubmittingReview"
          >
            {{ isSubmittingReview ? 'Đang gửi...' : 'Gửi đánh giá' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showDetail" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity">
       <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-fade-in-up">
          
          <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
             <div>
                <h3 class="text-lg font-bold text-gray-800">Chi tiết vé đã mua</h3>
                <p class="text-sm text-gray-500" v-if="selectedOrderDetail">
                   Mã đơn: <span class="font-mono font-bold text-orange-600">#{{ selectedOrderDetail.code }}</span>
                </p>
             </div>
             <button @click="closeDetail" class="text-gray-400 hover:text-gray-600 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 transition">
                <i class="fas fa-times text-xl"></i>
             </button>
          </div>

          <div class="p-6 overflow-y-auto custom-scrollbar flex-1 bg-gray-50/50">
             
             <div v-if="isLoadingDetail" class="flex flex-col items-center justify-center py-10">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600 mb-2"></div>
                <span class="text-xs text-gray-500">Đang tải thông tin vé...</span>
             </div>

             <div v-else-if="selectedOrderDetail">
                
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6 grid grid-cols-2 gap-4">
                   <div>
                      <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Tổng tiền</p>
                      <p class="text-lg font-bold text-orange-600">{{ formatCurrency(selectedOrderDetail.totalPrice) }}</p>
                   </div>
                   <div class="text-right">
                      <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Trạng thái</p>
                      <span class="text-xs font-bold px-2 py-1 rounded uppercase border" :class="statusClass(selectedOrderDetail.status)">
                         {{ statusDisplay(selectedOrderDetail.status) }}
                      </span>
                   </div>
                </div>

                <div class="space-y-4">
                   <h4 class="font-bold text-gray-800 flex items-center gap-2">
                      <i class="fas fa-qrcode text-orange-500"></i> Danh sách mã vé
                   </h4>

                   <div v-for="(eventItem, idx) in selectedOrderDetail.tickets" :key="idx" class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                      <div class="bg-orange-50 px-4 py-2 border-b border-orange-100 flex justify-between items-center">
                         <span class="font-bold text-orange-800 text-sm">{{ eventItem.eventName }}</span>
                         <span class="text-xs bg-white text-orange-600 px-2 py-0.5 rounded border border-orange-200 font-medium">{{ eventItem.ticketTypeName }}</span>
                      </div>
                      
                      <div class="p-4 grid gap-3">
                         <div v-for="code in eventItem.ticketCodes" :key="code.code" class="flex items-center gap-4 bg-gray-50 p-3 rounded-lg border border-dashed border-gray-300 relative group hover:border-orange-300 transition-colors">
                            
                            <div class="bg-white p-1 rounded shadow-sm border shrink-0">
                               <img 
                                  :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${code.code}`" 
                                  class="w-16 h-16 object-contain" 
                                  alt="QR Code"
                               >
                            </div>

                            <div class="flex-1 min-w-0">
                               <p class="text-xs text-gray-500 mb-1 font-semibold uppercase">Mã vé</p>
                               <p class="text-lg font-mono font-bold text-gray-800 tracking-wide truncate">{{ code.code }}</p>
                               <p class="text-xs text-gray-500 mt-1" v-if="code.seat">
                                  <i class="fas fa-chair text-gray-400"></i> Ghế: <span class="font-semibold text-gray-700">{{ code.seat }}</span>
                               </p>
                            </div>

                            <div class="absolute top-3 right-3">
                               <span class="text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wider shadow-sm" 
                                  :class="code.isUsed ? 'bg-gray-200 text-gray-500' : 'bg-green-100 text-green-700'">
                                  {{ code.isUsed ? 'Đã dùng' : 'Chưa dùng' }}
                               </span>
                            </div>
                         </div>
                      </div>
                   </div>
                </div>

             </div>
          </div>

          <div class="bg-white p-4 border-t border-gray-200 flex justify-end gap-3">
             <button @click="closeDetail" class="px-5 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 font-medium transition text-gray-700">Đóng</button>
          </div>
       </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useRouter } from 'vue-router'

// Import Layout Components
import HeaderComponent from '@/components/customers/layouts/HeaderComponent.vue'
import NavBarComponent from '@/components/customers/layouts/NavBarComponent.vue'

const router = useRouter()

// --- STATE: ORDER LIST ---
const isLoading = ref(true)
const orders = ref([])
const activeTab = ref('all')

const tabs = [
  { label: 'Tất cả', value: 'all' },
  { label: 'Đã thanh toán', value: 'paid' },
  { label: 'Chờ thanh toán', value: 'pending' },
  { label: 'Đã hủy', value: 'cancelled' }
]

// --- STATE: REVIEW ---
const showReview = ref(false)
const selectedOrderId = ref(null)
const selectedEventId = ref(null)
const selectedEventName = ref('')
const rating = ref(5)
const comment = ref('')
const isSubmittingReview = ref(false)

// --- STATE: ORDER DETAIL ---
const showDetail = ref(false)
const isLoadingDetail = ref(false)
const selectedOrderDetail = ref(null)

// --- API FUNCTIONS ---
const fetchOrders = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('http://localhost:8000/api/my-orders', {
        headers: { Authorization: `Bearer ${sessionStorage.getItem('access_token')}` }
    })
    
    if (response.data && response.data.data) {
        orders.value = response.data.data
    }
  } catch (error) {
    console.error("Lỗi tải đơn hàng:", error)
    if (error.response && error.response.status === 401) {
        router.push('/login')
    }
  } finally {
    isLoading.value = false
  }
}

// --- COMPUTED ---
const filteredOrders = computed(() => {
  if (activeTab.value === 'all') return orders.value
  return orders.value.filter(o => {
    if (activeTab.value === 'cancelled') return o.status === 'cancelled' || o.status === 'failed'
    return o.status === activeTab.value
  })
})

// --- FORMATTERS ---
const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val)

const formatDateOnly = (d) => { 
    if(!d) return ''
    return new Date(d).toLocaleDateString('vi-VN', {day:'2-digit', month:'2-digit', year:'numeric'})
}

const formatDateTime = (d) => { 
    if(!d) return ''
    const date = new Date(d)
    return `${date.toLocaleTimeString('vi-VN', {hour:'2-digit', minute:'2-digit'})}, ${date.toLocaleDateString('vi-VN')}`
}

const formatTimeOnly = (d) => {
    if(!d) return ''
    return new Date(d).toLocaleTimeString('vi-VN', {hour:'2-digit', minute:'2-digit'})
}

const statusDisplay = (s) => {
    const map = { paid: 'Đã thanh toán', pending: 'Chờ thanh toán', cancelled: 'Đã hủy', failed: 'Thất bại' }
    return map[s] || 'Khác'
}

const statusClass = (s) => {
    const map = {
        paid: 'bg-green-100 text-green-700 border-green-200',
        pending: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        cancelled: 'bg-red-100 text-red-700 border-red-200',
        failed: 'bg-red-100 text-red-700 border-red-200'
    }
    return map[s] || 'bg-gray-100 text-gray-600 border-gray-200'
}

// --- ACTION: OPEN ORDER DETAIL ---
const openOrderDetail = async (orderId) => {
    showDetail.value = true
    isLoadingDetail.value = true
    selectedOrderDetail.value = null

    try {
        const response = await axios.get(`http://localhost:8000/api/orders/${orderId}`, {
            headers: { Authorization: `Bearer ${sessionStorage.getItem('access_token')}` }
        })
        
        if(response.data && response.data.data) {
            selectedOrderDetail.value = response.data.data
        }
    } catch (error) {
        console.error("Lỗi tải chi tiết:", error)
        Swal.fire('Lỗi', 'Không thể tải chi tiết vé lúc này.', 'error')
        showDetail.value = false
    } finally {
        isLoadingDetail.value = false
    }
}

const closeDetail = () => {
    showDetail.value = false
}

// --- REVIEW LOGIC ---
const openReview = (order, event) => {
  selectedOrderId.value = order.id
  selectedEventId.value = event.eventId
  selectedEventName.value = event.eventName
  
  rating.value = 5
  comment.value = ''
  showReview.value = true
}

const closeReview = () => {
  showReview.value = false
  selectedEventId.value = null
  selectedOrderId.value = null
}

const submitReview = async () => {
    if (!comment.value.trim()) {
        return Swal.fire('Lưu ý', 'Vui lòng nhập nội dung đánh giá', 'warning')
    }

    try {
        isSubmittingReview.value = true
        
        const response = await axios.post('http://localhost:8000/api/reviews', {
            order_id: selectedOrderId.value,
            event_id: selectedEventId.value,
            rating: rating.value,
            comment: comment.value
        }, {
            headers: { Authorization: `Bearer ${sessionStorage.getItem('access_token')}` }
        })

        // Kiểm tra kết quả trả về từ Backend
        const result = response.data.data; // Cấu trúc ResponseApi: { code, message, data }

        // Mặc định icon là success
        let icon = 'success';
        let title = 'Thành công!';
        
        // Nếu status không phải active (ví dụ pending), đổi icon thành warning
        if (result && result.status !== 'active') {
            icon = 'warning';
            title = 'Đang chờ duyệt';
        }

        Swal.fire({
            icon: icon,
            title: title,
            text: response.data.message, // Lấy message từ Backend
            timer: 3000,
            showConfirmButton: false
        })

        // Update UI locally
        const orderIdx = orders.value.findIndex(o => o.id === selectedOrderId.value)
        if (orderIdx !== -1) {
            const eventIdx = orders.value[orderIdx].events.findIndex(e => e.eventId === selectedEventId.value)
            if (eventIdx !== -1) {
                orders.value[orderIdx].events[eventIdx].review = {
                    rating: rating.value,
                    comment: comment.value,
                    status: result ? result.status : 'active' // Cập nhật status để UI biết hiển thị "Đang chờ duyệt" hay không
                }
            }
        }

        closeReview()

    } catch (e) {
        console.error(e);
        Swal.fire('Lỗi', 'Không thể gửi đánh giá lúc này, vui lòng thử lại.', 'error')
    } finally {
        isSubmittingReview.value = false
    }
}

// --- INIT ---
onMounted(() => {
  fetchOrders()
})
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

/* Custom scrollbar for modal */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in-up { animation: fadeInUp 0.3s ease-out forwards; }
</style>