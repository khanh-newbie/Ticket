<template>
  <div class="w-[380px] rounded-xl bg-zinc-900 p-4 border border-zinc-700 shadow-xl text-white">
    <div class="flex justify-between items-center mb-4 border-b border-zinc-700 pb-2">
      <h3 class="text-sm font-semibold">Bộ lọc</h3>
      <button class="text-zinc-400 hover:text-white" @click="$emit('close')">
        ✕
      </button>
    </div>

    <section class="mb-5">
      <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">
        Vị trí
      </h4>
      <div class="grid grid-cols-2 gap-2">
        <label 
          v-for="loc in locationOptions" 
          :key="loc.value"
          class="flex items-center gap-2 cursor-pointer p-2 rounded border border-transparent hover:bg-zinc-800 transition-all"
          :class="modelValue.location == loc.value ? 'bg-zinc-800 border-green-600' : ''"
        >
            <input 
              type="radio" 
              name="location_filter"
              :value="loc.value" 
              :checked="modelValue.location == loc.value"
              @change="updateLocation(loc.value)"
              class="accent-green-500 w-4 h-4"
            />
            <span class="text-sm">{{ loc.label }}</span>
        </label>
      </div>
    </section>

    <section class="mb-5 border-t border-zinc-700 pt-3">
      <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">
        Danh mục sự kiện
      </h4>
      <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto custom-scrollbar">
        <button
          v-for="cat in categories"
          :key="cat.id"
          class="rounded-full border px-3 py-1 text-xs transition-all"
          :class="
            isCategorySelected(cat.id)
              ? 'border-green-500 bg-green-600 text-black font-semibold'
              : 'border-zinc-600 text-zinc-300 hover:border-zinc-400 hover:text-white'
          "
          @click="toggleCategory(cat.id)"
        >
          {{ cat.name }}
        </button>
      </div>
      <p v-if="categories.length === 0" class="text-xs text-zinc-500 italic mt-1">Đang tải danh mục...</p>
    </section>

    <section class="mb-4 border-t border-zinc-700 pt-3">
      <label class="flex items-center gap-2 text-sm cursor-pointer hover:text-green-400 transition">
        <input
          type="checkbox"
          class="accent-green-500 w-4 h-4 rounded"
          :checked="modelValue.freeOnly"
          @change="toggleFree"
        />
        <span>Chỉ hiện sự kiện miễn phí</span>
      </label>
    </section>

    <div class="flex justify-between gap-3 text-xs mt-4 pt-2 border-t border-zinc-700">
      <button
        class="flex-1 rounded-full border border-zinc-500 py-2 hover:bg-zinc-800 transition"
        @click="$emit('reset')"
      >
        Thiết lập lại
      </button>
      <button
        class="flex-1 rounded-full bg-green-600 text-black font-bold py-2 hover:bg-green-500 transition shadow-lg shadow-green-900/20"
        @click="$emit('close')"
      >
        Áp dụng
      </button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: { type: Object, required: true },
  categories: { type: Array, default: () => [] } // Nhận từ API cha truyền xuống
});

const emit = defineEmits(["update:modelValue", "close", "reset"]);

/* --- Dữ liệu Cứng (Location) --- */
const locationOptions = [
    { value: 'all', label: 'Toàn quốc' },
    { value: '79',  label: 'TP. Hồ Chí Minh' },
    { value: '01',  label: 'Hà Nội' },
    { value: '48',  label: 'Đà Nẵng' },
    { value: '42',  label: 'Đà Lạt' },
    { value: 'other', label: 'Khác' }
];

/* --- Logic xử lý --- */
const updateLocation = (val) => {
  emit("update:modelValue", { ...props.modelValue, location: val });
};

const toggleFree = () => {
  emit("update:modelValue", { ...props.modelValue, freeOnly: !props.modelValue.freeOnly });
};

// So sánh lỏng (==) để tránh lỗi string vs number (API trả về number, URL là string)
const isCategorySelected = (id) => {
    return props.modelValue.categories.some(c => c == id);
};

const toggleCategory = (id) => {
  // Tạo mảng mới
  const currentCats = [...props.modelValue.categories];
  const index = currentCats.findIndex(c => c == id);

  if (index > -1) {
    currentCats.splice(index, 1); // Xóa
  } else {
    currentCats.push(id); // Thêm
  }

  emit("update:modelValue", { ...props.modelValue, categories: currentCats });
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #52525b;
    border-radius: 4px;
}
</style>