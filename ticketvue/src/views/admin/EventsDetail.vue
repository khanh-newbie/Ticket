<template>
  <main class="container mx-auto p-4">

    <!-- LOADING -->
    <div
      v-if="loading"
      class="text-center py-10 text-gray-600"
    >
      Đang tải sự kiện...
    </div>

    <!-- DATA OK -->
    <div
      v-else-if="state.event"
      class="space-y-6"
    >

      <!-- STATUS + ACTIONS -->
      <div
        class="bg-white p-4 rounded shadow flex items-center justify-between"
      >
        <!-- LEFT: BACK + STATUS -->
        <div class="flex items-center gap-4">

          <!-- BACK BUTTON -->
          <button
            @click="goBack"
            class="px-3 py-2 rounded bg-gray-200 text-gray-700 text-sm hover:bg-gray-300"
          >
            ← Quay lại
          </button>

          <!-- STATUS -->
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">Trạng thái:</span>

            <span
              class="px-3 py-1 text-sm font-semibold rounded-full"
              :class="statusClass"
            >
              {{ statusText }}
            </span>
          </div>
        </div>

        <!-- RIGHT: ACTIONS (CHỈ KHI CHỜ DUYỆT) -->
        <div
          v-if="state.event.status === STATUS_PENDING_REVIEW"
          class="flex gap-3"
        >
          <button
            @click="approveEvent"
            class="px-4 py-2 rounded bg-green-600 text-white text-sm hover:bg-green-700"
          >
            ✔ Duyệt
          </button>

          <button
            @click="rejectEvent"
            class="px-4 py-2 rounded bg-red-600 text-white text-sm hover:bg-red-700"
          >
            ✖ Từ chối
          </button>
        </div>
      </div>

      <!-- EVENT CONTENT -->
      <div class="bg-white space-y-6 pt-6 rounded shadow">
        <Banner 
          :event="state.event" 
        />

        <Description
          :eventDescription="state.event.description"
        />

        <TicketInfo
          :event="state.event"
        />

        <Oranigazer
          :event="state.event"
        />
      </div>
    </div>

    <!-- NOT FOUND -->
    <div
      v-else
      class="text-center text-red-500 py-10"
    >
      Không tìm thấy sự kiện
    </div>

  </main>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

import Banner from '@/components/admin/eventsDetail/Banner.vue'
import Description from '@/components/admin/eventsDetail/Description.vue'
import TicketInfo from '@/components/admin/eventsDetail/TicketInfo.vue'
import Oranigazer from '@/components/admin/eventsDetail/Oranigazer.vue'

/* ================= ROUTER ================= */
const route = useRoute()
const router = useRouter()

/* ================= STATE ================= */
const loading = ref(true)

const state = reactive({
  event: null
})

/* ================= STATUS CONST ================= */
const STATUS_DRAFT = 1
const STATUS_PENDING_REVIEW = 2
const STATUS_PUBLISHED = 3
const STATUS_CANCELLED = 4
const STATUS_COMPLETED = 5

/* ================= API ================= */
const API_BASE = 'http://127.0.0.1:8000/api/admin/events'

/* ================= FETCH EVENT ================= */
onMounted(async () => {
  try {
    const res = await axios.get(`${API_BASE}/${route.params.id}`)
    const data = res.data.data

    data.status = Number(data.status)
    state.event = data
  } catch (err) {
    console.error('Load event error:', err)
  } finally {
    loading.value = false
  }
})

/* ================= NAV ================= */
const goBack = () => {
  router.back()
}

/* ================= ACTIONS ================= */
const approveEvent = async () => {
  if (!confirm('Bạn chắc chắn muốn DUYỆT sự kiện này?')) return

  try {
    await axios.post(`${API_BASE}/${route.params.id}/approve`)
    alert('Duyệt sự kiện thành công')

    router.push({ name: 'AdminApproveEvents' })
  } catch (err) {
    console.error(err)
    alert('Duyệt sự kiện thất bại')
  }
}

const rejectEvent = async () => {
  if (!confirm('Bạn chắc chắn muốn TỪ CHỐI sự kiện này?')) return

  try {
    await axios.post(`${API_BASE}/${route.params.id}/reject`)
    alert('Đã từ chối sự kiện')

    router.push({ name: 'AdminApproveEvents' })
  } catch (err) {
    console.error(err)
    alert('Từ chối sự kiện thất bại')
  }
}

/* ================= COMPUTED ================= */
const statusText = computed(() => {
  if (!state.event) return ''

  switch (state.event.status) {
    case STATUS_DRAFT:
      return 'Bản nháp'
    case STATUS_PENDING_REVIEW:
      return 'Chờ duyệt'
    case STATUS_PUBLISHED:
      return 'Đã xuất bản'
    case STATUS_CANCELLED:
      return 'Đã huỷ'
    case STATUS_COMPLETED:
      return 'Đã kết thúc'
    default:
      return 'Không xác định'
  }
})

const statusClass = computed(() => {
  if (!state.event) return ''

  switch (state.event.status) {
    case STATUS_PENDING_REVIEW:
      return 'bg-yellow-100 text-yellow-700'
    case STATUS_PUBLISHED:
      return 'bg-green-100 text-green-700'
    case STATUS_CANCELLED:
      return 'bg-red-100 text-red-700'
    case STATUS_COMPLETED:
      return 'bg-gray-200 text-gray-700'
    default:
      return 'bg-gray-100 text-gray-600'
  }
})
</script>
