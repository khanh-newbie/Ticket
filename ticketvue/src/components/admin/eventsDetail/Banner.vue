<template>
  <div class="py-10 px-4" style="background: linear-gradient(rgb(39, 39, 42) 48.04%, rgb(0, 0, 0) 100%)">
    <div id="banner"
      class="flex flex-col md:flex-row bg-white shadow-lg rounded-xl overflow-hidden max-w-6xl mx-auto my-10">

      <!-- BÊN TRÁI 30% -->
      <div class="flex-1  min-h-[350px] p-6 relative flex flex-col md:w-[30%] justify-between">
        <div id="circle1" class="w-3 h-3 bg-green-500 rounded-full absolute -left-3 top-3"></div>
        <div id="circle2" class="w-3 h-3 bg-green-500 rounded-full absolute -left-3 top-16"></div>

        <div class="text-gray-900 font-bold text-2xl mb-4">{{ event?.event_name }}</div>

        <div id="date" class="flex items-center gap-2 mb-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-800" fill="currentColor" viewBox="0 0 21 20">
            <g clip-path="url(#clip0)">
              <path
                d="M6.25 0a1 1 0 011 1v1h6V1a1 1 0 112 0v1h1a4 4 0 014 4v2h-20V6a4 4 0 014-4h1V1a1 1 0 011-1zM20.25 10h-20v8a2 2 0 002 2h16a2 2 0 002-2v-8z">
              </path>
            </g>
            <defs>
              <clipPath id="clip0">
                <path fill="#fff" d="M0 0h20v20H0z" />
              </clipPath>
            </defs>
          </svg>
          <span class="text-gray-700 text-sm">
            {{ event?.schedules?.length ? formatSchedule(event.schedules[0]) : 'Chưa có lịch' }}
          </span>


        </div>

        <div id="venue" class="flex items-center gap-2 mb-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-800" fill="currentColor" viewBox="0 0 22 28">
            <path fill-rule="evenodd" clip-rule="evenodd"
              d="M4.031 3.307a10.514 10.514 0 0113.937 0c4.485 3.945 4.955 10.854 1.058 15.392l-7.015 8.17a1.333 1.333 0 01-2.023 0l-7.015-8.17C-.923 14.161-.454 7.252 4.031 3.307zM11 14.667A3.333 3.333 0 1011 8a3.333 3.333 0 000 6.666z" />
          </svg>
          <span class="text-gray-700 text-sm">{{ event?.venue?.name }}</span>
        </div>

        <div id="address" class="text-gray-500 text-xs mb-4">
          {{ event?.venue?.address }}
        </div>

        <div class="flex items-baseline gap-2 mt-2">
          <span class="text-sm text-gray-500 font-medium">Giá từ</span>
          <span class="text-xl md:text-2xl font-bold text-green-500">
            {{ event?.ticket_types_min_base_price }}₫
          </span>
        </div>

        <div>
          <a href="#">
            <button class="bg-green-500 hover:bg-green-600 text-white w-full px-4 py-2 rounded-lg font-semibold">
              Mua vé ngay
            </button>
          </a>
        </div>

      </div>

      <div class="md:w-[70%]">
        <img :src="event?.background_image_url" alt="Event Image"
          class="w-full h-full object-cover" />


      </div>
    </div>
  </div>
</template>


<script setup>
import { reactive, watch, ref, onMounted } from 'vue';
import axios from 'axios';
import { BASE_URL } from '@/config/const';
const props = defineProps({
  event: { type: Object, default: () => ({}) }
})
// Reactive localData

watch(
  () => props.event,
  (newVal) => {
    console.log('Banner received event:', newVal)
  },
  { immediate: true, deep: true }
)
// console.log("prop event", props.event)

function formatSchedule(schedule) {
  if (!schedule) return ''

  const start = new Date(schedule.start_datetime)
  const end = new Date(schedule.end_datetime)

  const startTime = start.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
  const endTime = end.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
  const startDate = start.toLocaleDateString('vi-VN', { day: '2-digit', month: 'long', year: 'numeric' })

  return `${startTime} - ${endTime}, ${startDate}`
}


</script>
