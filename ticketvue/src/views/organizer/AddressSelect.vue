<template>
  <div class="space-y-4">
    <div class="flex flex-col md:flex-row gap-4">
      <div class="flex-1">
        <label class="block text-white font-medium mb-1">Tỉnh/Thành</label>
        <select
          v-model="form.cityId"
          class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600
                 focus:outline-none focus:border-green-500"
        >
          <option value="">Chọn Tỉnh/Thành</option>
          <option v-for="p in provinces" :key="p.code" :value="p.code">
            {{ p.name }}
          </option>
        </select>
      </div>

      <div class="flex-1">
        <label class="block text-white font-medium mb-1">Quận/Huyện</label>
        <select
          v-model="form.districtId"
          :disabled="!form.cityId"
          class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600
                 focus:outline-none focus:border-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <option value="">Chọn Quận/Huyện</option>
          <option v-for="d in districts" :key="d.code" :value="d.code">
            {{ d.name }}
          </option>
        </select>
      </div>
    </div>

    <div class="flex flex-col md:flex-row gap-4">
      <div class="flex-1">
        <label class="block text-white font-medium mb-1">Phường/Xã</label>
        <select
          v-model="form.wardId"
          :disabled="!form.districtId"
          class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600
                 focus:outline-none focus:border-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <option value="">Chọn Phường/Xã</option>
          <option v-for="w in wards" :key="w.code" :value="w.code">
            {{ w.name }}
          </option>
        </select>
      </div>

      <div class="flex-1">
        <label class="block text-white font-medium mb-1">Số nhà, đường</label>
        <input
          v-model="form.street"
          type="text"
          maxlength="80"
          placeholder="Số nhà, đường"
          class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600
                 focus:outline-none focus:border-green-500"
        />
        <div class="text-gray-400 text-xs mt-1">
          {{ form.street.length }} / 80
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'
import {
  getProvinces,
  getDistrictsByProvinceCode,
  getWardsByDistrictCode
} from 'sub-vn'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      cityId: "",
      districtId: "",
      wardId: "",
      street: ""
    })
  }
})

const emit = defineEmits(["update:modelValue"])

// form nội bộ
const form = reactive({
  cityId: props.modelValue.cityId,
  districtId: props.modelValue.districtId,
  wardId: props.modelValue.wardId,
  street: props.modelValue.street,
})

// emit về cha
watch(
  form,
  (v) => emit("update:modelValue", { ...v }),
  { deep: true }
)

// update khi parent thay đổi
watch(
  () => props.modelValue,
  (v) => {
    Object.assign(form, v)
  },
  { deep: true }
)

// load vùng tỉnh/huyện/xã
const provinces = getProvinces()
const districts = reactive([])
const wards = reactive([])

watch(
  () => form.cityId,
  (city) => {
    form.districtId = ""
    form.wardId = ""
    districts.splice(0)
    wards.splice(0)

    if (city) districts.push(...getDistrictsByProvinceCode(city))
  },
  { immediate: true }
)

watch(
  () => form.districtId,
  (district) => {
    form.wardId = ""
    wards.splice(0)

    if (district) wards.push(...getWardsByDistrictCode(district))
  },
  { immediate: true }
)
</script>
