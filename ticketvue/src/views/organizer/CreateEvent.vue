<template>
  <div class="min-h-screen flex bg-gray-100">
    <OrganizerSidebar />
    <OrganizerHeader />

    <!-- Slider -->
    <OrganizerSlider :currentStepComponentRef="currentStepComponentRef" :stepComponents="stepComponents"
      :submitAll="submitAll" />

    <div class="flex-1 ml-84 p-4 space-y-4">
      <component :is="currentStepComponent" ref="currentStepComponentRef" :key="step.value" v-model="currentStepData" />

      <!-- Debug button
      <button @click="printCurrent" class="px-4 py-2 bg-blue-500 text-white rounded">
        Log current step data
      </button> -->
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";

import OrganizerSidebar from "@/components/organizers/OrganizerSidebar.vue";
import OrganizerHeader from "@/components/organizers/OrganizerHeader.vue";
import OrganizerSlider from "@/components/organizers/OrganizerSlider.vue";

import StepInfo from "./StepInfo.vue";
import StepTime from "./StepTime.vue";
import StepPayment from "./StepPayment.vue";
import { BASE_URL } from "@/config/const";

const route = useRoute();
const step = ref(route.query.step || "info");

// Dữ liệu tổng
const eventData = reactive({
  info: {},
  time: {
    startDate: "",
    startTime: "",
    endDate: "",
    endTime: "",
  },
  payment: {},
});
const state = reactive({
  user_info: sessionStorage.getItem('user_info') ? JSON.parse(sessionStorage.getItem('user_info')) : null,
  access_token: sessionStorage.getItem('access_token') ? sessionStorage.getItem('access_token') : null,

})


// Step mapping
const currentStepComponentRef = ref(null);
const stepComponents = { info: StepInfo, time: StepTime, payment: StepPayment };

const currentStepComponent = computed(
  () => stepComponents[step.value] || StepInfo
);

// v-model cho từng step
const currentStepData = computed({
  get: () => eventData[step.value],
  set: (val) => {
    eventData[step.value] = val;
  },
});

// log mỗi khi eventData thay đổi
watch(
  eventData,
  () => {
    console.log("🔥 EVENT DATA CHANGED:", JSON.parse(JSON.stringify(eventData)));
  },
  { deep: true }
);

// chuyển step
watch(
  () => route.query.step,
  (newStep) => {
    step.value = newStep || "info";
  }
);

// debug
function printCurrent() {
  console.log("📌 CURRENT STEP:", step.value);
  console.log("📌 CURRENT STEP DATA:", JSON.parse(JSON.stringify(eventData[step.value])));
  console.log("📦 FULL eventData:", JSON.parse(JSON.stringify(eventData)));
}


// Hàm convert Blob URL → File

async function submitAll() {
  try {
    const form = new FormData();

    // -------------------------
    // 1) INFO
    // -------------------------
    for (const key in eventData.info) {
      const val = eventData.info[key];
      // Nếu key là address
      if (key === "address" && val) {
        form.append(`info[${key}]`, JSON.stringify(val));
        continue;
      }

      // -------------------------
      // bgFile[] (array blob URLs)
      // -------------------------
      if (key === "bgFile") {
        if (Array.isArray(val) && val.length > 0) {
          for (let i = 0; i < val.length; i++) {
            const file = await blobUrlToFile(val[i], `bgFile-${i}.png`);
            form.append("bgFile[]", file);
          }
        } else if (eventData.info.bgPreview) {
          // Nếu chỉ có bgPreview mà bgFile rỗng
          const file = await blobUrlToFile(eventData.info.bgPreview, "bgFile-0.png");
          form.append("bgFile[]", file);
        }
        continue;
      }

      // -------------------------
      // eventFile[] (array blob URLs)
      // -------------------------
      if (key === "eventFile") {
        if (Array.isArray(val) && val.length > 0) {
          for (let i = 0; i < val.length; i++) {
            const file = await blobUrlToFile(val[i], `eventFile-${i}.png`);
            form.append("eventFile[]", file);
          }
        } else if (eventData.info.eventPreview) {
          // Nếu chỉ có eventPreview mà eventFile rỗng
          const file = await blobUrlToFile(eventData.info.eventPreview, "eventFile-0.png");
          form.append("eventFile[]", file);
        }
        continue;
      }


      if (key === "orgLogoFile") {
        if (Array.isArray(val) && val.length > 0) {
          for (let i = 0; i < val.length; i++) {
            const file = await blobUrlToFile(val[i], `orgLogoFile-${i}.png`);
            form.append("orgLogoFile[]", file);
          }
        } else if (eventData.info.orgLogoPreview) {
          // Nếu chỉ có orgLogoPreview mà eventFile rỗng
          const file = await blobUrlToFile(eventData.info.orgLogoPreview, "orgLogoFile-0.png");
          form.append("orgLogoFile[]", file);
        }
        continue;
      }

      // -------------------------
      // eventPreview (nếu muốn gửi riêng)
      // -------------------------
      if (key === "eventPreview" && val) {
        const file = await blobUrlToFile(val, "eventPreview.png");
        form.append("eventPreview", file);
        continue;
      }


      // -------------------------
      // các trường text khác
      // -------------------------
      form.append(`info[${key}]`, val);
    }

    // -------------------------
    // 2) TIME
    // -------------------------
    form.append("time", JSON.stringify(eventData.time));

    // -------------------------
    // 3) PAYMENT
    // -------------------------
    form.append("payment", JSON.stringify(eventData.payment));
    form.append("user_id", state.user_info.id);

    // -------------------------
    // 4) Gửi POST
    // -------------------------
    const res = await axios.post(
      "http://127.0.0.1:8000/api/create-event",
      form,
      { headers: { "Content-Type": "multipart/form-data" } }
    );

    console.log("📥 RESPONSE:", res.data);
  } catch (err) {
    console.error("❌ API ERROR:", err);
  }
}


/**
 * Chuyển blob URL thành File
 * @param {string} blobUrl
 * @param {string} fileName
 * @returns {Promise<File>}
 */
async function blobUrlToFile(blobUrl, filename) {
  if (!blobUrl) return null;

  // Nếu là URL server (có BASE_URL), bỏ BASE_URL để chỉ gửi filename
  if (blobUrl.startsWith(BASE_URL)) {
    // Chỉ gửi path phía server
    return new File([], blobUrl.replace(BASE_URL, ""));
  }

  // Blob URL từ preview
  const res = await fetch(blobUrl);
  const blob = await res.blob();
  return new File([blob], filename, { type: blob.type });
}
</script>
