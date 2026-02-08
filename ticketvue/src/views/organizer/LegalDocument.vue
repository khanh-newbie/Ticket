<template>
  <div class="min-h-screen bg-gray-100">
    <OrganizerSidebar />
    <OrganizerHeader />

    <main class="ml-84 pt-20 p-6">
      <div class="bg-white rounded-xl shadow p-6">

        <!-- LIST ĐIỀU KHOẢN -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <button
            v-for="(doc, index) in documents"
            :key="index"
            @click="currentPdf = doc.pdf"
            class="p-4 border rounded-lg text-left hover:bg-gray-50 transition"
            :class="currentPdf === doc.pdf ? 'border-green-500 bg-green-50' : ''"
          >
            <p class="font-semibold text-gray-800">
              {{ index + 1 }}. {{ doc.title }}
            </p>
          </button>
        </div>

        <!-- PDF VIEWER -->
        <div v-if="currentPdf" class="h-[75vh] border rounded-lg overflow-hidden">
          <iframe
            :src="currentPdf"
            class="w-full h-full"
            frameborder="0"
          />
        </div>

        <div v-else class="text-center text-gray-500 py-20">
          Chọn một điều khoản để xem nội dung PDF
        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import OrganizerSidebar from '@/components/organizers/OrganizerSidebar.vue'
import OrganizerHeader from '@/components/organizers/OrganizerHeader.vue'

const documents = [
  {
    title: 'Danh mục hàng hoá, dịch vụ cấm kinh doanh',
    pdf: '/pdf/legal/business.pdf'
  },
  {
    title: 'Danh mục hàng hóa, dịch vụ cấm quảng cáo',
    pdf: '/pdf/legal/advertising.pdf'
  },
  {
    title: 'Quy định kiểm duyệt nội dung & hình ảnh',
    pdf: '/pdf/legal/content-image.pdf'
  }
]

const currentPdf = ref(documents[0].pdf)
</script>
