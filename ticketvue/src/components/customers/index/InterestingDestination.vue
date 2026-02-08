<template>
  <div class="rounded-lg mt-8">
    <h2 class="text-xl font-semibold text-white mb-4">Điểm đến thú vị</h2>

    <div class="grid grid-cols-4 gap-4">
      <div 
        v-for="(destination, index) in destinations" 
        :key="index"
        @click="goToSearch(destination.code)"
        class="relative overflow-hidden rounded-xl shadow-lg cursor-pointer group h-64"
      >
        <img 
            :src="destination.image" 
            :alt="destination.name"
            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
        > 
        
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>

        <div class="absolute bottom-4 left-4">
          <p class="text-2xl font-bold text-white shadow-text-sm group-hover:text-green-400 transition-colors">
            {{ destination.name }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// Dữ liệu mẫu với đường dẫn ảnh cũ của bạn và thêm mã code
const destinations = ref([
  {
    name: 'Tp. Hồ Chí Minh',
    image: 'images/url_image_hcm.jpg',
    code: '79', // Mã HCM
    isComposite: false,
  },
  {
    name: 'Hà Nội',
    image: 'images/url_image_hanoi.jpg',
    code: '01', // Mã Hà Nội
    isComposite: false,
  },
  {
    name: 'Đà Lạt',
    image: 'images/url_image_dalat.jpg',
    code: '42', // Mã Lâm Đồng
    isComposite: false,
  },
  {
    name: 'Vị trí khác',
    image: 'images/url_image_other.jpg',
    code: 'other', // Mã khác
    isComposite: false,
  },
]);

// Hàm chuyển trang sang màn Search
const goToSearch = (localCode) => {
  router.push({ 
    path: '/search', 
    query: { local: localCode } 
  });
};
</script>

<style scoped>
.shadow-text-sm {
  text-shadow: 0px 1px 3px rgba(0, 0, 0, 0.9);
}
</style>