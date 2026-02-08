<template>

  <div
    class="fixed top-[64px] ml-84 w-[calc(100%-21rem)] z-10 px-4 py-3 bg-black/30 backdrop-blur-sm border-b border-gray-700 flex items-center justify-between gap-4"
  >

    <div class="flex justify-around items-center gap-6 flex-1">
      <div
        v-for="(label, index) in stepLabels"
        :key="index"
        class="flex gap-2 items-center select-none"
        :class="{
          'cursor-pointer': index <= currentStepIndex,           // được click
          'cursor-not-allowed opacity-40': index > currentStepIndex // bị khóa
        }"
        @click="goToStep(index)"
      >

        <div
          class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-semibold"
          :class="{
            'bg-green-500 text-white':
              currentStepIndex === index,                       
            'bg-gray-600 text-white':
              completedSteps.includes(index) &&
              currentStepIndex !== index,                       
            'border border-gray-400 text-white opacity-50':
              currentStepIndex !== index &&
              !completedSteps.includes(index)                   
          }"
        >
          <span v-if="completedSteps.includes(index)">✓</span>
          <span v-else>{{ index + 1 }}</span>
        </div>

        <div
          class="text-base"
          :class="{
            'text-white': currentStepIndex === index,
            'text-white opacity-60': currentStepIndex !== index
          }"
        >
          {{ label }}
        </div>
      </div>
    </div>

    <div class="flex gap-3">
      <button
        @click="handleSave"
        class="px-3 py-1.5 bg-white border border-gray-400 rounded text-dark hover:text-green-600 transition text-sm font-medium"
      >
        Lưu
      </button>
      <button
        @click="handleNext"
        class="px-3 py-1.5 bg-green-500 rounded text-white hover:bg-green-600 transition text-sm font-medium"
      >
        Tiếp tục
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'

// Props từ parent
const props = defineProps({
  currentStepComponentRef: Object,
  submitAll: Function
})
const route = useRoute()
const router = useRouter()

const steps = ['info', 'time', 'payment']
const stepLabels = ['Thông tin', 'Thời gian & Loại vé', 'Thanh toán']

// Bước hiện tại
const currentStepIndex = computed(() => {
  const step = route.query.step || 'info'
  return steps.indexOf(step)
})

// Bước hoàn thành (để hiện dấu ✓)
const completedSteps = ref([])

// Flag để chỉ submit 1 lần duy nhất
const submitted = ref(false)

// Click chuyển step
function goToStep(index) {
  if (index <= currentStepIndex.value) {
    router.push({ path: '/organizer/create-event', query: { step: steps[index] } })
  }
}

// Bấm tiếp tục → sang bước kế
async function handleNext() {
  // Nếu đã submit rồi → không cho tiếp tục
  if (submitted.value) return

  const nextStep = steps[currentStepIndex.value + 1]

  // Nếu là bước cuối → gửi toàn bộ dữ liệu
  if (currentStepIndex.value === steps.length - 1) {
    try {
      await props.submitAll()
      submitted.value = true // đánh dấu đã submit

      // Thông báo thành công
      Swal.fire({
        icon: 'success',
        title: 'Đã tạo sự kiện thành công!',
        timer: 2000,
        showConfirmButton: false,
        willClose: () => {
          // Khi thông báo đóng → chuyển về màn index
          router.push('/organizer/events')
        }
      })
    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Có lỗi khi tạo sự kiện',
        text: err.message || '',
      })
    }
  } else if (nextStep) {
    router.push({ path: '/organizer/create-event', query: { step: nextStep } })
  }
}


// Lưu và đánh dấu bước hoàn thành
async function handleSave() {
  // Gọi submit của step hiện tại (validate)
  await props.currentStepComponentRef?.value?.submitEvent?.()

  // Đánh dấu hoàn thành step
  if (!completedSteps.value.includes(currentStepIndex.value)) {
    completedSteps.value.push(currentStepIndex.value)
  }
}
</script>

