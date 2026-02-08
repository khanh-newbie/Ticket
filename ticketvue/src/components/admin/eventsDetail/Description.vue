<template>
  <div class="p-4 bg-gray-900 text-white rounded-lg shadow max-w-4xl mx-auto">
    <!-- v-html để render nội dung HTML từ TinyMCE -->
    <div v-html="eventDescription" class="prose prose-white"></div>
  </div>
</template>

<script setup>
const props = defineProps({
  eventDescription: {
    type: String,
    default: '',
  },
})
</script>