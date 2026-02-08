<template>
  <div class="bottom-data">
    <div class="orders">
      <div class="header">
        <div class="left">
          <h3>Danh sách sự kiện</h3>
        </div>

        <div class="tools">
          <div class="table-search">
            <input
              v-model="keyword"
              type="text"
              @keyup.enter="searchEvent"
              placeholder="Tìm theo tên sự kiện..."
            />
            <i class="bx bx-search"></i>
            <button @click="searchEvent"></button>
          </div>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Mã sự kiện</th>
            <th>Tên sự kiện</th>
            <th>Nhà tổ chức</th>
            <th>Địa điểm</th>
            <th>Ngày tạo</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="event in filteredEvents" :key="event.id">
            <td>#{{ event.id }}</td>
            <td>{{ event.event_name }}</td>
            <td>{{ event.organizer?.organization_name || "N/A" }}</td>
            <td>{{ event.venue?.city || "N/A" }}</td>
            <td>{{ formatDate(event.created_at) }}</td>

            <td class="actions" style="display: flex; align-items: center; gap: 0.5rem">
              <button
                class="action-btn view"
                @click="goDetail(event.id)"
                style="
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  width: 32px;
                  height: 32px;
                  border: none;
                  border-radius: 6px;
                  background-color: #e0f7fa;
                  color: #006064;
                  cursor: pointer;
                  transition: all 0.2s;
                "
              >
                <i class="bx bx-show"></i>
              </button>

              <button
                class="action-btn approve"
                @click="approveEvent(event.id)"
                v-if="Number(event.status) === STATUS_PENDING_REVIEW"
                style="
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  width: 32px;
                  height: 32px;
                  border: none;
                  border-radius: 6px;
                  background-color: #e8f5e9;
                  color: #2e7d32;
                  cursor: pointer;
                  transition: all 0.2s;
                "
              >
                <i class="bx bx-check"></i>
              </button>

              <button
                class="action-btn reject"
                @click="rejectEvent(event.id)"
                v-if="Number(event.status) === STATUS_PENDING_REVIEW"
                style="
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  width: 32px;
                  height: 32px;
                  border: none;
                  border-radius: 6px;
                  background-color: #ffebee;
                  color: #c62828;
                  cursor: pointer;
                  transition: all 0.2s;
                "
              >
                <i class="bx bx-x"></i>
              </button>
            </td>
          </tr>

          <tr v-if="filteredEvents.length === 0">
            <td colspan="6" style="text-align: center">Không có sự kiện</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();
const events = ref([]);
const filteredEvents = ref([]);
const keyword = ref("");
const STATUS_PENDING_REVIEW = 2;

const fetchEvents = async () => {
  try {
    const res = await axios.get("http://127.0.0.1:8000/api/admin/events/pending");
    events.value = res.data.data;
    filteredEvents.value = events.value; // mặc định hiển thị tất cả
  } catch (err) {
    console.error(err);
  }
};

const approveEvent = async (id) => {
  const confirmed = window.confirm("Bạn có chắc muốn duyệt sự kiện này?");
  if (!confirmed) return;

  try {
    await axios.post(`http://127.0.0.1:8000/api/admin/events/${id}/approve`);
    alert("Duyệt sự kiện thành công!");
    fetchEvents();
  } catch (error) {
    alert("Có lỗi xảy ra khi duyệt sự kiện.");
  }
};

const rejectEvent = async (id) => {
  const confirmed = window.confirm("Bạn có chắc muốn từ chối sự kiện này?");
  if (!confirmed) return;

  try {
    await axios.post(`http://127.0.0.1:8000/api/admin/events/${id}/reject`);
    alert("Từ chối sự kiện thành công!");
    fetchEvents();
  } catch (error) {
    alert("Có lỗi xảy ra khi từ chối sự kiện.");
  }
};

const goDetail = (id) => {
  router.push({ name: "AdminEventDetail", params: { id } });
};

const searchEvent = () => {
  if (!keyword.value) {
    filteredEvents.value = events.value;
  } else {
    filteredEvents.value = events.value.filter((e) =>
      e.event_name.toLowerCase().includes(keyword.value.toLowerCase())
    );
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleString("vi-VN");
};

onMounted(fetchEvents);
</script>
