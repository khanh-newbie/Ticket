<template>
  <!-- StepInfo Template -->
  <div class="space-y-6">

    <!-- Ảnh sự kiện nhỏ -->
    <div class="bg-[#23252C] rounded-lg p-4 space-y-4">
      <div class="flex flex-col md:flex-row gap-4">

        <!-- Upload Image -->
        <div class="w-full md:w-1/5">
          <div class="min-h-[40px]">
            <label class="flex flex-col text-white text-sm font-medium">
              Upload hình ảnh
              <span class="text-[#2DC275] text-xs cursor-pointer mt-1">
                Xem vị trí hiển thị các ảnh
              </span>
            </label>
          </div>

          <div
            class="mt-2 aspect-[720/958] w-full flex items-center justify-center
            border-2 border-dashed border-gray-500 rounded-lg cursor-pointer
            bg-[#5B5B62] hover:border-green-500"
            @click="eventInput?.click()"
          >
            <input
              type="file"
              ref="eventInput"
              accept="image/*"
              class="hidden"
              @change="handleEventImage"
            />

            <div
              v-if="!localData.eventPreview"
              class="flex flex-col items-center justify-center text-center text-white"
            >
              <p class="text-sm mb-1">Thêm ảnh sự kiện (720x958)</p>
            </div>

            <img
              v-else
              :src="localData.eventPreview"
              class="max-h-full max-w-full rounded-lg object-contain"
            />
          </div>
        </div>

        <!-- Ảnh nền -->
        <div class="w-full md:w-3/4">
          <div class="min-h-[40px]"></div>

          <div
            class="mt-2 aspect-[1280/720] w-full flex items-center justify-center
            border-2 border-dashed border-gray-500 rounded-lg cursor-pointer
            bg-[#5B5B62] hover:border-green-500"
            @click="bgInput?.click()"
          >
            <input
              type="file"
              ref="bgInput"
              accept="image/*"
              class="hidden"
              @change="handleBackgroundImage"
            />

            <div
              v-if="!localData.bgPreview"
              class="flex flex-col items-center justify-center text-center text-white"
            >
              <p class="text-sm mb-1">Thêm ảnh nền sự kiện (1280x720)</p>
            </div>

            <img
              v-else
              :src="localData.bgPreview"
              class="w-full h-full object-cover rounded-lg"
            />
          </div>
        </div>

      </div>

      <!-- Tên sự kiện -->
      <div>
        <label class="block text-white font-medium mb-1">Tên sự kiện</label>
        <input
          v-model="localData.eventName"
          maxlength="100"
          placeholder="Tên sự kiện"
          class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600 focus:outline-none focus:border-green-500"
        />
        <div class="text-gray-400 text-xs mt-1">
          {{ localData.eventName.length }} / 100
        </div>
      </div>
    </div>

    <!-- Địa điểm -->
    <div class="bg-[#23252C] rounded-lg p-4 space-y-4">
      <div>
        <label class="block text-white font-medium mb-1">Tên địa điểm</label>
        <input
          v-model="localData.venueName"
          maxlength="80"
          class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600 focus:outline-none focus:border-green-500"
          placeholder="Tên địa điểm"
        />
      </div>

      <AddressSelect v-model="localData.address" />
    </div>

    <!-- Thể loại -->
    <div class="bg-[#23252C] rounded-lg p-4 space-y-4">
      <label class="block text-white font-medium mb-1">Thể loại sự kiện</label>

      <select
        v-model="localData.category"
        class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600"
      >
        <option value="">Chọn thể loại</option>

        <option
          v-for="cat in categories"
          :key="cat.id"
          :value="cat.id"
        >
          {{ cat.name }}
        </option>
      </select>
    </div>

    <!-- Editor -->
    <EventDescriptionEditor v-model="localData.eventDescription" />

    <!-- Tổ chức -->
    <div class="p-4 bg-[#23252C] rounded-lg w-full mb-2">
      <div class="flex flex-col-reverse md:flex-row gap-3">

        <!-- Logo -->
        <div class="w-full mt-5 md:w-[15%]">
          <div
            class="aspect-[275/275] w-full flex items-center justify-center
            rounded-lg border-2 border-dashed border-gray-600 cursor-pointer"
            @click="orgLogoInput?.click()"
          >
            <input
              type="file"
              ref="orgLogoInput"
              class="hidden"
              accept="image/*"
              @change="handleOrgLogo"
            />

            <div
              v-if="!localData.orgLogoPreview"
              class="text-white text-center font-bold text-sm"
            >
              Thêm logo ban tổ chức (275x275)
            </div>

            <img
              v-else
              :src="localData.orgLogoPreview"
              class="max-h-full max-w-full object-contain rounded-lg"
            />
          </div>
        </div>

        <!-- Fields -->
        <div class="w-full md:w-[85%] flex flex-col gap-3">
          <div>
            <label class="block text-white font-medium mb-1">Tên tổ chức</label>
            <input
              v-model="localData.orgName"
              placeholder="Tên ban tổ chức"
              maxlength="80"
              class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600"
            />
          </div>

          <div>
            <label class="block text-white font-medium mb-1">Thông tin ban tổ chức</label>
            <textarea
              v-model="localData.orgDescription"
              maxlength="500"
              rows="3"
              class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600"
            ></textarea>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { reactive, watch, ref, onMounted } from "vue"
import axios from "axios"
import AddressSelect from "./AddressSelect.vue"
import EventDescriptionEditor from "./EventDescriptionEditor.vue"

/* ================= PROPS & EMIT ================= */
const props = defineProps({ modelValue: Object })
const emit = defineEmits(["update:modelValue"])

/* ================= STATE ================= */
const categories = ref([])
const loadingCategories = ref(false)

const state = reactive({
  user_info: sessionStorage.getItem("user_info")
    ? JSON.parse(sessionStorage.getItem("user_info"))
    : null,
  access_token: sessionStorage.getItem("access_token") || null,
})

/* ================= LOCAL DATA ================= */
const localData = reactive({
  eventName: "",
  venueName: "",
  address: { cityId: "", districtId: "", wardId: "", street: "" },
  category: "",
  eventDescription: "",
  orgName: "",
  orgDescription: "",
  eventPreview: null,
  bgPreview: null,
  orgLogoPreview: null,
  eventFile: null,
  bgFile: null,
  orgLogoFile: null,
})

/* ================= SYNC ================= */
watch(
  () => props.modelValue,
  val => val && Object.assign(localData, val),
  { deep: true, immediate: true }
)

watch(
  localData,
  val => emit("update:modelValue", { ...val }),
  { deep: true }
)

/* ================= REFS ================= */
const eventInput = ref(null)
const bgInput = ref(null)
const orgLogoInput = ref(null)

/* ================= IMAGE HANDLERS ================= */
const validateImageSize = (file, w, h) =>
  new Promise(resolve => {
    const img = new Image()
    img.src = URL.createObjectURL(file)
    img.onload = () => resolve(img.width === w && img.height === h)
  })

async function handleEventImage(e) {
  const file = e.target.files[0]
  if (!file) return
  if (!(await validateImageSize(file, 720, 958))) {
    alert("Ảnh sự kiện phải đúng 720x958")
    return
  }
  localData.eventPreview = URL.createObjectURL(file)
  localData.eventFile = file
}

async function handleBackgroundImage(e) {
  const file = e.target.files[0]
  if (!file) return
  if (!(await validateImageSize(file, 1280, 720))) {
    alert("Ảnh nền phải đúng 1280x720")
    return
  }
  localData.bgPreview = URL.createObjectURL(file)
  localData.bgFile = file
}

async function handleOrgLogo(e) {
  const file = e.target.files[0]
  if (!file) return
  if (!(await validateImageSize(file, 275, 275))) {
    alert("Logo phải đúng 275x275")
    return
  }
  localData.orgLogoPreview = URL.createObjectURL(file)
  localData.orgLogoFile = file
}

/* ================= API ================= */
async function getDataOrganizer() {
  const res = await axios.get(
    `http://127.0.0.1:8000/api/organizer/getInfo/${state.user_info.id}`
  )
  if (res.data.code === 200) {
    localData.orgName = res.data.data.organization_name
    localData.orgDescription = res.data.data.description
    localData.orgLogoPreview = res.data.data.logo
  }
}

async function fetchCategories() {
  loadingCategories.value = true
  const res = await axios.get("http://127.0.0.1:8000/api/organizer/event-categories")
  if (res.data.code === 200) {
    categories.value = res.data.data
  }
  loadingCategories.value = false
}

/* ================= MOUNT ================= */
onMounted(() => {
  if (state.user_info && state.access_token) {
    getDataOrganizer()
    fetchCategories()
  }
})
</script>
