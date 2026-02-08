<template>
  <div class="bg-gray-900 mt-32 min-h-screen p-8">
    <!-- Header -->
    <p class="text-3xl font-bold text-white mb-6">Ngày sự kiện</p>

    <!-- Suất diễn -->
    <div v-for="(show, showIndex) in evenData.shows" :key="showIndex" class="bg-gray-800 p-6 rounded-xl mb-6">
      <div class="flex justify-between items-center mb-4">
        <p class="font-semibold text-white">
          Suất diễn {{ showIndex + 1 }}: {{ show.start_time || 'Chưa đặt' }} → {{ show.end_time || 'Chưa đặt' }}
        </p>
        <button @click="removeShow(showIndex)"
          class="bg-red-600 hover:bg-red-700 text-white font-medium py-1 px-3 rounded transition">
          Xóa suất
        </button>
      </div>

      <!-- Thời gian -->
      <div class="flex gap-4 mb-4">
        <div class="flex flex-col w-1/2">
          <label class="text-white font-medium mb-1">Thời gian bắt đầu</label>
          <input type="datetime-local" v-model="show.start_time"
            class="rounded-lg p-2 border border-gray-600 bg-gray-700 text-white">
        </div>
        <div class="flex flex-col w-1/2">
          <label class="text-white font-medium mb-1">Thời gian kết thúc</label>
          <input type="datetime-local" v-model="show.end_time"
            class="rounded-lg p-2 border border-gray-600 bg-gray-700 text-white">
        </div>
      </div>

      <!-- Danh sách vé -->
      <div v-for="(ticket, ticketIndex) in show.tickets" :key="ticketIndex"
        class="flex flex-wrap gap-3 items-center mb-2">
        <p class="text-white">{{ ticket.ticket_name || 'Chưa có tên vé' }}</p>
        <button @click="editTicket(showIndex, ticketIndex)"
          class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1 px-3 rounded transition">
          Sửa
        </button>
        <button @click="removeTicket(showIndex, ticketIndex)"
          class="bg-red-600 hover:bg-red-700 text-white font-medium py-1 px-3 rounded transition">
          Xóa
        </button>
        <button @click="copyTicket(showIndex, ticketIndex)"
          class="bg-yellow-500 hover:bg-yellow-600 text-black font-medium py-1 px-3 rounded transition">
          Copy vé
        </button>
      </div>

      <button @click="addTicket(showIndex)"
        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded transition mt-3">
        Tạo vé mới
      </button>
    </div>

    <!-- Copy vé khi tạo suất diễn mới -->
    <div class="flex flex-wrap gap-4 items-center mb-6">
      <label class="text-white font-medium">Copy vé từ suất diễn:</label>
      <select v-model="copyFromShowIndex"
        class="p-2 rounded-lg bg-gray-700 text-white border border-gray-600">
        <option :value="null">Chọn suất diễn</option>
        <option v-for="(show, idx) in evenData.shows" :key="idx" :value="idx">
          Suất {{ idx + 1 }}
        </option>
      </select>
      <button @click="addShowWithCopy"
        class="bg-yellow-500 hover:bg-yellow-600 text-black font-medium py-2 px-4 rounded transition">
        Tạo suất diễn mới + copy vé
      </button>
      <button @click="addShow"
        class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded transition">
        Tạo suất diễn mới
      </button>
    </div>

    <!-- Popup tạo/sửa vé -->
    <div v-if="showPopup" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-4xl text-white shadow-2xl">
        <div class="flex justify-between items-center mb-4">
          <p class="text-2xl font-bold">
            {{ editingTicket.showIndex !== null && editingTicket.ticketIndex !== null ? 'Sửa vé' : 'Tạo loại vé mới' }}
          </p>
          <button @click="closePopup" class="text-white text-3xl font-bold hover:text-red-400 transition">×</button>
        </div>

        <div class="flex flex-col gap-4">
          <div class="flex flex-col">
            <label class="mb-1 font-medium">Tên vé</label>
            <input type="text" v-model="ticketForm.ticket_name"
              class="p-2 rounded-lg border border-gray-600 bg-gray-700 text-white placeholder-gray-400"
              placeholder="Nhập tên vé">
          </div>

          <div class="flex gap-4">
            <div class="flex flex-col w-1/2">
              <label class="mb-1 font-medium">Giá</label>
              <input type="number" v-model="ticketForm.price"
                class="p-2 rounded-lg border border-gray-600 bg-gray-700 text-white">
            </div>
            <div class="flex flex-col w-1/2">
              <label class="mb-1 font-medium">Số lượng</label>
              <input type="number" v-model="ticketForm.quantity"
                class="p-2 rounded-lg border border-gray-600 bg-gray-700 text-white">
            </div>
          </div>

          <div class="flex gap-4">
            <div class="flex flex-col w-1/2">
              <label class="mb-1 font-medium">Thời gian bắt đầu</label>
              <input type="datetime-local" v-model="ticketForm.start_time_ticket"
                class="p-2 rounded-lg border border-gray-600 bg-gray-700 text-white">
            </div>
            <div class="flex flex-col w-1/2">
              <label class="mb-1 font-medium">Thời gian kết thúc</label>
              <input type="datetime-local" v-model="ticketForm.end_time_ticket"
                class="p-2 rounded-lg border border-gray-600 bg-gray-700 text-white">
            </div>
          </div>

          <div class="flex gap-4">
            <div class="flex flex-col w-3/4">
              <label class="mb-1 font-medium">Mô tả</label>
              <textarea v-model="ticketForm.description_ticket"
                class="p-2 rounded-lg border border-gray-600 bg-gray-700 text-white placeholder-gray-400 h-32"></textarea>
            </div>
            <div class="flex flex-col w-1/4">
              <label class="mb-1 font-medium">Hình ảnh</label>
              <input type="file" @change="onFileChange"
                class="p-2 rounded-lg border border-gray-600 bg-gray-700 text-white h-32">
            </div>
          </div>

          <button @click="saveTicket"
            class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded transition w-full mt-3">
            Lưu
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, ref, defineExpose } from "vue";

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(["update:modelValue"]);

// ⭐ Dữ liệu nội bộ step
const evenData = reactive({
  shows: [
    {
      start_time: "",
      end_time: "",
      tickets: []
    }
  ]
});

// ⭐ Nhận dữ liệu từ cha (CreateEvent)
watch(
  () => props.modelValue,
  (v) => {
    if (!v) return;

    // overwrite
    if (v.shows) {
      evenData.shows = v.shows.map((s) => ({
        ...s,
        tickets: s.tickets.map((t) => ({ ...t }))
      }));
    }
  },
  { immediate: true, deep: true }
);

// ⭐ Emit ngược ra CHA mỗi khi thay đổi
watch(
  evenData,
  (v) => {
    emit("update:modelValue", JSON.parse(JSON.stringify(v)));
  },
  { deep: true }
);

/* ---------- REST CODE UI GIỮ NGUYÊN HOÀN TOÀN ---------- */

const showPopup = ref(false);
const editingTicket = reactive({ showIndex: null, ticketIndex: null });
const copyFromShowIndex = ref(null);

const ticketForm = reactive({
  ticket_name: "",
  price: "",
  quantity: "",
  start_time_ticket: "",
  end_time_ticket: "",
  description_ticket: "",
  image_ticket: null
});

const addShow = () => {
  evenData.shows.push({ start_time: "", end_time: "", tickets: [] });
};

const addShowWithCopy = () => {
  if (copyFromShowIndex.value === null) return;

  const sourceTickets = evenData.shows[copyFromShowIndex.value].tickets;
  evenData.shows.push({
    start_time: "",
    end_time: "",
    tickets: sourceTickets.map((t) => ({ ...t }))
  });

  copyFromShowIndex.value = null;
};

const removeShow = (index) => {
  if (evenData.shows.length <= 1) return;
  evenData.shows.splice(index, 1);
};

const addTicket = (showIndex) => {
  editingTicket.showIndex = showIndex;
  editingTicket.ticketIndex = null;

  Object.assign(ticketForm, {
    ticket_name: "",
    price: "",
    quantity: "",
    start_time_ticket: "",
    end_time_ticket: "",
    description_ticket: "",
    image_ticket: null
  });

  showPopup.value = true;
};

const editTicket = (showIndex, ticketIndex) => {
  editingTicket.showIndex = showIndex;
  editingTicket.ticketIndex = ticketIndex;

  Object.assign(ticketForm, evenData.shows[showIndex].tickets[ticketIndex]);

  showPopup.value = true;
};

const saveTicket = () => {
  const { showIndex, ticketIndex } = editingTicket;

  if (ticketIndex !== null) {
    Object.assign(evenData.shows[showIndex].tickets[ticketIndex], ticketForm);
  } else {
    evenData.shows[showIndex].tickets.push({ ...ticketForm });
  }

  closePopup();
};

const removeTicket = (s, t) => {
  evenData.shows[s].tickets.splice(t, 1);
};

const copyTicket = (s, t) => {
  const list = evenData.shows[s].tickets;
  list.push({ ...list[t] });
};

const closePopup = () => {
  showPopup.value = false;
  editingTicket.showIndex = null;
  editingTicket.ticketIndex = null;
};

const onFileChange = (e) => {
  ticketForm.image_ticket = e.target.files[0] || null;
};

// STEP VALIDATION + SUBMIT
function validateData() {
  for (let i = 0; i < evenData.shows.length; i++) {
    const s = evenData.shows[i];
    if (!s.start_time || !s.end_time) return false;
    for (let t of s.tickets) {
      if (!t.ticket_name || !t.price || !t.quantity) return false;
    }
  }
  return true;
}

async function submitEvent() {
  if (!validateData()) return false;

  console.log("📤 Submit StepTime:", JSON.parse(JSON.stringify(evenData)));
  return true;
}

defineExpose({ submitEvent });
</script>
