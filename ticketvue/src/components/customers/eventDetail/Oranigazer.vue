<template>
  <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow space-y-4">
    <h4 class="text-2xl text-black font-semibold">Ban tổ chức</h4>

    <div class="flex flex-col md:flex-row items-start md:items-center gap-4">

      <!-- Organizer Logo -->
      <img 
 
        :src="event?.organizer?.logo  || '/images/logo.png'" 
        alt="Organizer Logo" 
        class="w-32 h-32 object-cover rounded"
      />

      <div>
        <p class="text-lg font-medium text-gray-800">{{ event?.organizer?.organization_name }}</p>
        <p class="text-gray-500 mt-1">{{ event?.organizer?.description }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { BASE_URL } from "@/config/const"; // import từ file const

const props = defineProps({
  event: { type: Object, default: () => ({}) }
})
</script>
