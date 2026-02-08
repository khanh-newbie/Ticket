<!-- components/DateFilter.vue -->
<template>
  <div class="relative">
    <!-- Nút pill hiển thị range -->
    <button
      class="flex items-center gap-2 rounded-full bg-green-600 px-4 py-2 text-sm font-medium"
      @click="isOpen = !isOpen"
    >
      <span>📅</span>
      <span>{{ label }}</span>

      <!-- nút clear nhỏ bên phải giống dấu X -->
      <span
        v-if="modelValue.from || modelValue.to"
        class="ml-1 px-1 rounded-full bg-green-700 hover:bg-green-800 text-xs"
        @click.stop="clearRange"
      >
        ✕
      </span>

      <span class="ml-1">▼</span>
    </button>

    <!-- Dropdown calendar -->
    <div
      v-if="isOpen"
      class="absolute z-50 mt-2 w-[600px] bg-white text-black rounded-xl shadow-lg"
    >
      <!-- Hàng preset -->
      <div class="flex border-b border-gray-200 text-xs">
        <button
          v-for="p in presets"
          :key="p.key"
          class="flex-1 px-4 py-2 text-center border-b-2"
          :class="
            activePreset === p.key
              ? 'border-green-500 text-green-600 bg-green-50'
              : 'border-transparent text-gray-600 hover:bg-gray-100'
          "
          @click="applyPreset(p.key)"
        >
          {{ p.label }}
        </button>
      </div>

      <!-- Calendar range inline -->
      <div class="p-4 border-b border-gray-200 flex justify-center">
        <VueDatePicker
          v-model="internalRange"
          range
          :multi-calendars="2"
          :enable-time-picker="false"
          inline
          auto-apply="false"
          :locale="viLocale"
          :day-names="dayLabels"
          @update:model-value="onInternalChange"
        >
          <!-- custom tiêu đề tháng -->
          <template #month-year="{ month, year }">
            <div class="w-full text-center font-semibold text-sm">
              Tháng {{ String(month + 1).padStart(2, '0') }}, {{ year }}
            </div>
          </template>
        </VueDatePicker>
      </div>

      <!-- Footer: reset + apply -->
      <div class="flex gap-3 p-4">
        <button
          class="flex-1 rounded-full border border-green-500 py-2 text-sm"
          @click="onReset"
        >
          Thiết lập lại
        </button>
        <button
          class="flex-1 rounded-full py-2 text-sm"
          :class="
            canApply
              ? 'bg-green-500 text-white hover:bg-green-600'
              : 'bg-gray-200 text-gray-500 cursor-not-allowed'
          "
          :disabled="!canApply"
          @click="onApply"
        >
          Áp dụng
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { VueDatePicker } from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { vi as viLocale } from "date-fns/locale";

const props = defineProps({
  // { from: 'YYYY-MM-DD' | null, to: 'YYYY-MM-DD' | null }
  modelValue: {
    type: Object,
    required: true,
  },
});
const emit = defineEmits(["update:modelValue"]);

const isOpen = ref(false);

/**
 * internalRange là [Date|null, Date|null] dùng cho datepicker
 * sync từ/ra modelValue
 */
const internalRange = ref([null, null]);

const activePreset = ref("all"); // 'all' | 'today' | 'tomorrow' | 'weekend' | 'month' | ''

// label thứ: T2 -> CN (sẽ truyền vào day-names)
const dayLabels = ["T2", "T3", "T4", "T5", "T6", "T7", "CN"];

watch(
  () => props.modelValue,
  (val) => {
    const from = val.from ? new Date(val.from) : null;
    const to = val.to ? new Date(val.to) : null;
    internalRange.value = [from, to];

    // nếu không có ngày => preset = all
    if (!from && !to) {
      activePreset.value = "all";
    } else {
      activePreset.value = "";
    }
  },
  { immediate: true, deep: true }
);

// format "03 tháng 01, 2026"
const formatViLabel = (date) => {
  if (!date) return "";
  const d = new Date(date);
  const day = d.getDate().toString().padStart(2, "0");
  const month = (d.getMonth() + 1).toString().padStart(2, "0");
  const year = d.getFullYear();
  return `${day} tháng ${month}, ${year}`;
};

const label = computed(() => {
  const { from, to } = props.modelValue;
  if (!from && !to) return "Tất cả các ngày";

  if (from && to) return `${formatViLabel(from)} - ${formatViLabel(to)}`;
  if (from) return `Từ ${formatViLabel(from)}`;
  return `Đến ${formatViLabel(to)}`;
});

const clearRange = () => {
  internalRange.value = [null, null];
  emit("update:modelValue", { from: null, to: null });
  activePreset.value = "all";
};

// có thể apply khi đã chọn ít nhất 1 ngày hoặc preset khác 'all'
const canApply = computed(() => {
  const [from] = internalRange.value || [];
  return !!from || activePreset.value !== "all";
});

const onReset = () => {
  internalRange.value = [null, null];
  emit("update:modelValue", { from: null, to: null });
  activePreset.value = "all";
};

// khi người dùng click ngày trên calendar
const onInternalChange = (val) => {
  internalRange.value = val || [null, null];
  activePreset.value = ""; // bỏ preset đang chọn
};

const onApply = () => {
  if (!canApply.value) return;

  let [from, to] = internalRange.value || [];

  // nếu chỉ chọn 1 ngày thì from = to = ngày đó
  if (from && !to) {
    to = from;
  } else if (!from && to) {
    from = to;
  }

  // nếu vẫn chưa có ngày (trường hợp preset "Tất cả các ngày")
  if (!from && !to) {
    emit("update:modelValue", { from: null, to: null });
  } else {
    emit("update:modelValue", {
      from: from ? from.toISOString().slice(0, 10) : null,
      to: to ? to.toISOString().slice(0, 10) : null,
    });
  }

  isOpen.value = false;
};

/** PRESETS */
const presets = [
  { key: "all", label: "Tất cả các ngày" },
  { key: "today", label: "Hôm nay" },
  { key: "tomorrow", label: "Ngày mai" },
  { key: "weekend", label: "Cuối tuần này" },
  { key: "month", label: "Tháng này" },
];

const applyPreset = (key) => {
  const today = new Date();
  const clone = (d) => new Date(d.getTime());
  const toStr = (d) => d.toISOString().slice(0, 10);

  activePreset.value = key;

  if (key === "all") {
    onReset();
    return;
  }

  if (key === "today") {
    const d = clone(today);
    internalRange.value = [d, d];
    emit("update:modelValue", { from: toStr(d), to: toStr(d) });
    return;
  }

  if (key === "tomorrow") {
    const d = clone(today);
    d.setDate(d.getDate() + 1);
    internalRange.value = [d, d];
    emit("update:modelValue", { from: toStr(d), to: toStr(d) });
    return;
  }

  if (key === "weekend") {
    const d = clone(today);
    const day = d.getDay(); // 0..6 (CN = 0)
    const saturday = clone(d);
    saturday.setDate(d.getDate() + ((6 - day + 7) % 7));
    const sunday = clone(saturday);
    sunday.setDate(saturday.getDate() + 1);

    internalRange.value = [saturday, sunday];
    emit("update:modelValue", { from: toStr(saturday), to: toStr(sunday) });
    return;
  }

  if (key === "month") {
    const first = new Date(today.getFullYear(), today.getMonth(), 1);
    const last = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    internalRange.value = [first, last];
    emit("update:modelValue", { from: toStr(first), to: toStr(last) });
    return;
  }
};
</script>

<style scoped>
/* 2 calendar nằm giữa & cách nhau 1 chút */
:deep(.dp__calendar_wrap) {
  display: flex !important;
  justify-content: center !important;
  gap: 24px !important;
}

/* header tháng căn giữa */
:deep(.dp__month_year_row) {
  justify-content: center !important;
  margin-bottom: 8px;
}

/* header thứ */
:deep(.dp__calendar_header) {
  display: flex !important;
  justify-content: space-between !important;
  padding: 0 8px;
}

:deep(.dp__calendar_header_item) {
  width: 32px;
  text-align: center;
  font-size: 12px;
  color: #555;
}
</style>
