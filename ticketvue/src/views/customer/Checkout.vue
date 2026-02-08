<template>
  <div class="min-w-[400px] min-h-screen bg-gray-100 font-sans">
    <HeaderComponent />
    <NavBarComponent />

    <div class="container mx-auto py-12 px-4">
      <h1 class="text-4xl font-bold text-center text-gray-900 tracking-tight">
        Thanh toán đơn hàng
      </h1>

      <div class="min-h-screen bg-gray-100 flex items-center justify-center p-6">
        <div class="w-full max-w-2xl">
          <div class="bg-white border border-gray-200 rounded-2xl shadow-lg p-8">
            <h5 class="text-2xl font-semibold mb-6 text-gray-900 flex items-center gap-2">
              🛒 Đơn hàng
            </h5>

            <div v-for="item in checkoutItems" :key="item.ticket_type_id"
              class="flex items-center mb-5 bg-gray-50 rounded-xl p-4 border border-gray-200">
              <div class="relative">
                <img :src="item.poster_image_url || defaultThumbnail"
                  class="w-16 h-16 object-cover rounded-xl shadow-sm border" />
                <span
                  class="absolute -top-2 -right-2 bg-green-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center shadow">
                  {{ item.quantity }}
                </span>
              </div>

              <div class="flex-1 ml-4">
                <p class="text-gray-900 font-semibold text-sm">{{ item.ticket_type_name }}</p>
                <p class="text-gray-700 text-sm">{{ formatPrice(item.base_price) }}</p>
                <p class="text-gray-500 text-xs">{{ item.event_name }}</p>
              </div>

              <div class="text-right text-gray-900 font-bold text-sm w-24">
                {{ formatPrice(item.base_price * item.quantity) }}
              </div>
            </div>

            <hr class="my-6">

            <div class="flex justify-between items-center mb-6">
              <strong class="text-xl text-gray-900">Tổng cộng</strong>
              <strong class="text-2xl text-green-600 font-bold">{{ formatPrice(totalValue) }}</strong>
            </div>

            <div class="flex flex-col sm:flex-row justify-center items-center gap-6 mt-6">
              <a href="/cart" class="text-green-700 font-semibold mr-35 hover:underline text-lg">
                ← Trở về giỏ hàng
              </a>

              <button @click="placeOrder" class="px-10 py-4 bg-gradient-to-r from-green-500 to-green-600 
                      hover:from-green-600 hover:to-green-700 text-white rounded-xl 
                      shadow-md font-bold transition transform hover:-translate-y-0.5 text-lg">
                Đặt hàng ngay
              </button>
            </div>

          </div>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import HeaderComponent from '@/components/customers/layouts/HeaderComponent.vue'
import NavBarComponent from '@/components/customers/layouts/NavBarComponent.vue'
import { useCartStore } from '@/stores/cart'
import axios from 'axios'
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
const router = useRouter()

const cartStore = useCartStore()
import { useRoute } from 'vue-router'

const route = useRoute()
const returnPath = route.fullPath
// Lấy items từ store
const checkoutItems = computed(() => cartStore.checkoutItems)


const user_info = ref(sessionStorage.getItem('user_info'))
const access_token = ref(sessionStorage.getItem('access_token'))

// Phương thức thanh toán
const paymentMethod = ref(null)

// Tổng tiền
const totalValue = computed(() =>
  checkoutItems.value.reduce((sum, i) => sum + i.base_price * i.quantity, 0)
)

// Format giá tiền
function formatPrice(price) {
  return price.toLocaleString('vi-VN') + '₫'
}

// Đặt hàng
async function placeOrder() {

  const user = JSON.parse(sessionStorage.getItem('user') || '{}')

  // Chuẩn hóa items để gửi backend
  const orderItems = checkoutItems.value.map(item => ({
    ticket_type_id: item.ticket_type_id, // phải khớp với DB TicketType
    quantity: item.quantity
  }))

  const payload = {
    customer_email: user_info.value.email,
    customer_phone: user_info.value.phone,
    customer_address: user_info.value.address ?? '',
    user_id: user_info.value.id,

    payment_method: paymentMethod.value,
    items: orderItems,
    return_path: returnPath // đường dẫn để trả về sau khi thanh toán online
  }

  try {
    console.log("return path:", returnPath)
    const res = await axios.post('http://127.0.0.1:8000/api/create-order', payload,

      {
        headers: {
          Authorization: `Bearer ${access_token.value}`,
        }
      })

    if (res.data.code === 200) {
      const checkoutUrl = res.data.data.checkout_url

      if (checkoutUrl) {
        // Luôn redirect sang link thanh toán nếu backend trả về
        window.location.href = checkoutUrl
        return
      }

      // Nếu không có checkout_url, tức là thanh toán offline
      alert('Đặt hàng thành công! Cảm ơn bạn đã mua hàng.')
      window.location.href = '/home'
    } else {
      alert(res.data.message || 'Có lỗi xảy ra khi tạo đơn hàng')
    }


  } catch (err) {
    console.error(err)
    alert('Lỗi kết nối đến server. Vui lòng thử lại.')
  }
}
</script>
