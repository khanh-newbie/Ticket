<template>
  <div class="bg-white mt-6 rounded-lg p-6 shadow-sm border border-gray-100">
    <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center gap-2">
      Đánh giá từ khách hàng
      <span class="text-gray-500 text-sm font-normal">
        ({{ reviews.length }} đánh giá)
      </span>
    </h2>

    <div class="flex items-center gap-4 mb-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
      <div class="text-4xl font-bold text-orange-500">
        {{ avgRating.toFixed(1) }}
      </div>

      <div>
        <div class="flex text-yellow-400 text-lg mb-1">
          <i v-for="i in 5" :key="i" class="fa" :class="i <= Math.round(avgRating) ? 'fa-star' : 'fa-star-o'" />
        </div>
        <p class="text-sm text-gray-500 font-medium">
          Trung bình cộng
        </p>
      </div>
    </div>

    <div v-if="reviews.length" class="space-y-6">
      <div v-for="review in reviews" :key="review.id" class="border-b border-gray-100 pb-6 last:border-none">
        <div class="flex gap-4">
          <div class="shrink-0">
             <img :src="review.user_avatar || '/assets/avatar-default.png'" 
                @error="$event.target.src = '/assets/avatar-default.png'"
                class="w-10 h-10 rounded-full object-cover border border-gray-200" alt="avatar" />
          </div>

          <div class="flex-1">
            <div class="flex items-center justify-between mb-1">
              <p class="font-bold text-gray-800 text-sm">
                {{ review.user_name }}
              </p>
              
              <div class="flex items-center gap-3">
                  <span class="text-xs text-gray-400">
                    {{ formatDate(review.created_at) }}
                  </span>
                  
                  <button 
                    @click="openReport(review)"
                    class="text-gray-300 hover:text-red-500 transition-colors tooltip relative group"
                    title="Báo cáo vi phạm"
                  >
                    <i class="fa fa-flag text-xs"></i>
                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:block bg-gray-900 text-white text-[10px] px-2 py-1 rounded whitespace-nowrap">
                        Báo cáo
                    </span>
                  </button>
              </div>
            </div>
          
            <div class="flex text-yellow-400 text-xs mb-2">
              <i v-for="i in 5" :key="i" class="fa" :class="i <= review.rating ? 'fa-star' : 'fa-star-o'" />
            </div>

            <p class="text-gray-700 text-sm leading-relaxed">
              {{ review.comment }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-gray-500 text-center py-12 flex flex-col items-center">
      <i class="fa fa-comment-o text-4xl text-gray-300 mb-2"></i>
      <p>Chưa có đánh giá nào cho sự kiện này</p>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 animate-fade-in-up">
            <h3 class="text-xl font-extrabold text-gray-900 mb-1">Báo cáo đánh giá</h3>
            <p class="text-sm text-gray-600 mb-5 font-medium">
                Bạn đang báo cáo bình luận của <span class="font-bold text-gray-900">{{ selectedReview?.user_name }}</span>
            </p>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-1.5">Lý do <span class="text-red-600">*</span></label>
                    <select v-model="form.reason" class="w-full border border-gray-300 bg-gray-50 rounded-lg p-2.5 text-sm text-gray-900 font-medium focus:ring-2 focus:ring-red-500 outline-none transition-all">
                        <option value="" disabled class="text-gray-400">Chọn lý do phù hợp...</option>
                        <option value="Spam">Spam / Quảng cáo</option>
                        <option value="Ngôn từ đả kích">Ngôn từ thù địch / Đả kích</option>
                        <option value="Thông tin sai lệch">Thông tin sai lệch</option>
                        <option value="Quấy rối">Quấy rối / Xúc phạm</option>
                        <option value="Khác">Lý do khác</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-1.5">Chi tiết <span class="text-gray-500 font-normal text-xs">(Tùy chọn)</span></label>
                    <textarea 
                        v-model="form.description" 
                        rows="3" 
                        class="w-full border border-gray-300 bg-gray-50 rounded-lg p-3 text-sm text-gray-900 font-medium focus:ring-2 focus:ring-red-500 outline-none resize-none placeholder:text-gray-400 transition-all"
                        placeholder="Mô tả thêm vấn đề..."
                    ></textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-8">
                <button @click="closeModal" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-100 font-bold text-gray-800 text-sm transition">
                    Hủy bỏ
                </button>
                <button 
                    @click="submitReport" 
                    :disabled="isSubmitting"
                    class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold shadow-md disabled:opacity-70 flex justify-center items-center gap-2 text-sm transition"
                >
                    <i v-if="isSubmitting" class="fa fa-spinner fa-spin"></i>
                    {{ isSubmitting ? 'Đang gửi...' : 'Gửi báo cáo' }}
                </button>
            </div>
        </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'

const reviews = ref([])
const route = useRoute()

// --- STATE REPORT ---
const showModal = ref(false)
const isSubmitting = ref(false)
const selectedReview = ref(null)
const form = reactive({
    reason: '',
    description: ''
})

// FORMAT DATE
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('vi-VN', { 
      day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute:'2-digit' 
  })
}

// CALL API FETCH REVIEWS
const fetchReviews = async () => {
  const id = route.query.id
  try {
    const res = await axios.get(
      'http://127.0.0.1:8000/api/event-review',
      { params: { event_id: id } }
    )

    if (res.data.code === 200) {
      reviews.value = res.data.data
    }
  } catch (err) {
    console.error(err)
  }
}

// AVG RATING
const avgRating = computed(() => {
  if (!reviews.value.length) return 0
  return (
    reviews.value.reduce((sum, r) => sum + r.rating, 0) /
    reviews.value.length
  )
})

// --- LOGIC BÁO CÁO ---
const openReport = (review) => {
    // Check login
    const token = sessionStorage.getItem('access_token');
    if (!token) {
        Swal.fire({
            icon: 'info',
            title: 'Yêu cầu đăng nhập',
            text: 'Bạn cần đăng nhập để báo cáo đánh giá này.',
            showCancelButton: true,
            confirmButtonText: 'Đăng nhập'
        }).then((res) => {
            if(res.isConfirmed) window.location.href = '/login';
        });
        return;
    }

    selectedReview.value = review;
    form.reason = '';
    form.description = '';
    showModal.value = true;
}

const closeModal = () => {
    showModal.value = false;
    selectedReview.value = null;
}

const submitReport = async () => {
    if (!form.reason) {
        Swal.fire('Lỗi', 'Vui lòng chọn lý do báo cáo', 'warning');
        return;
    }

    try {
        isSubmitting.value = true;
        const token = sessionStorage.getItem('access_token');
        
        await axios.post('http://127.0.0.1:8000/api/reports', {
            type: 'review',          
            id: selectedReview.value.id, 
            reason: form.reason,
            description: form.description
        }, {
            headers: { Authorization: `Bearer ${token}` }
        });

        closeModal();
        Swal.fire({
            icon: 'success',
            title: 'Đã gửi báo cáo',
            text: 'Cảm ơn bạn đã giúp chúng tôi xây dựng cộng đồng văn minh.',
            timer: 2000,
            showConfirmButton: false
        });

    } catch (error) {
        console.error(error);
        Swal.fire('Lỗi', error.response?.data?.message || 'Không thể gửi báo cáo', 'error');
    } finally {
        isSubmitting.value = false;
    }
}

onMounted(fetchReviews)
</script>

<style scoped>
@keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in-up { animation: fadeInUp 0.3s ease-out forwards; }
</style>