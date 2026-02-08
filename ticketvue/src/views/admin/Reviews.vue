<template>
  <div style="margin-top: 20px" class="reviews-container">
    <!-- Search -->
    <div class="reviews-search">
      <i class="bx bx-search"></i>
      <input v-model="keyword" type="text" placeholder="Tìm User ID, tên, nội dung..." />
    </div>

    <!-- Tabs -->
    <div class="reviews-tabs">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        @click="changeTab(tab.value)"
        :class="{ active: currentStatus === tab.value }"
      >
        <i :class="tab.icon"></i> {{ tab.label }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="reviews-loading">
      <div class="spinner"></div>
      <p>Đang tải dữ liệu...</p>
    </div>

    <!-- Empty -->
    <div v-else-if="reviews.length === 0" class="reviews-empty">
      <i class="bx bx-message-square-x"></i>
      <h3>Không tìm thấy đánh giá nào</h3>
      <p>Thử thay đổi trạng thái hoặc từ khóa tìm kiếm.</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto">
      <table class="reviews-table">
        <thead>
          <tr>
            <th>Khách hàng</th>
            <th>Sự kiện</th>
            <th>Đánh giá</th>
            <th>Nội dung</th>
            <th>Trạng thái</th>
            <th style="text-align: center">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="review in reviews" :key="review.id">
            <!-- Khách hàng -->
            <td>
              <div style="display: flex; align-items: center; gap: 0.5rem">
                <div
                  style="
                    width: 2.5rem;
                    height: 2.5rem;
                    border-radius: 9999px;
                    background: #bfdbfe;
                    color: #1d4ed8;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: 700;
                  "
                >
                  {{ review.user?.name?.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <div style="font-weight: 700; color: #111827">
                    {{ review.user?.name }}
                  </div>
                  <div
                    style="
                      font-size: 0.75rem;
                      color: #9ca3af;
                      background: #f3f4f6;
                      padding: 0 0.25rem;
                      border-radius: 0.25rem;
                    "
                  >
                    ID: {{ review.user_id }}
                  </div>
                </div>
              </div>
            </td>

            <!-- Sự kiện -->
            <td style="color: #4b5563">{{ review.event?.event_name }}</td>

            <!-- Đánh giá sao -->
            <td class="review-stars">
              <i
                v-for="n in 5"
                :key="n"
                class="bx"
                :class="n <= review.rating ? 'bxs-star' : 'bx-star'"
              ></i>
            </td>

            <!-- Nội dung -->
            <td>
              <p style="font-style: italic; color: #374151">"{{ review.comment }}"</p>
              <div v-if="review.status === 'pending'" class="ai-flag">
                <i class="bx bxs-error-circle"></i>
                <span>AI Flag: {{ review.admin_note || "Nội dung cần xem xét" }}</span>
              </div>
            </td>

            <!-- Trạng thái -->
            <td>
              <span
                :class="{
                  'status-active': review.status === 'active',
                  'status-pending': review.status === 'pending',
                  'status-hidden': review.status === 'hidden',
                }"
              >
                <span
                  class="status-dot"
                  :class="{
                    'bg-green-600': review.status === 'active',
                    'bg-yellow-600': review.status === 'pending',
                    'bg-gray-600': review.status === 'hidden',
                  }"
                ></span>
                {{
                  review.status === "active"
                    ? "Hiển thị"
                    : review.status === "pending"
                    ? "Cần duyệt"
                    : "Đã ẩn"
                }}
              </span>
              <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem">
                {{ formatDate(review.created_at) }}
              </div>
            </td>

            <!-- Hành động -->
            <td style="text-align: right; vertical-align: middle">
              <div
                style="
                  display: flex;
                  gap: 0.5rem;
                  justify-content: center;
                  align-items: center;
                  height: 100%;
                "
              >
                <button
                  v-if="review.status !== 'active'"
                  @click="updateStatus(review.id, 'active')"
                  class="action-btn approve"
                  title="Duyệt"
                >
                  <i class="bx bx-check"></i>
                </button>
                <button
                  v-if="review.status !== 'hidden'"
                  @click="updateStatus(review.id, 'hidden')"
                  class="action-btn hide"
                  title="Ẩn"
                >
                  <i class="bx bx-x"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="reviews-pagination">
      <button
        @click="changePage(pagination.current_page - 1)"
        :disabled="pagination.current_page === 1"
      >
        Trước
      </button>
      <span>{{ pagination.current_page }} / {{ pagination.last_page }}</span>
      <button
        @click="changePage(pagination.current_page + 1)"
        :disabled="pagination.current_page === pagination.last_page"
      >
        Sau
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import Swal from "sweetalert2";

const reviews = ref([]);
const loading = ref(false);
const currentStatus = ref("pending");
const keyword = ref("");
const pagination = ref({ current_page: 1, total: 0, last_page: 1 });
let debounceTimeout = null;

const tabs = [
  { label: "Cần duyệt", value: "pending", icon: "bx bx-time" },
  { label: "Đang hiển thị", value: "active", icon: "bx bx-show" },
  { label: "Đã ẩn", value: "hidden", icon: "bx bx-hide" },
];

const formatDate = (d) =>
  d
    ? new Date(d).toLocaleDateString("vi-VN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      })
    : "";

const fetchReviews = async (page = 1) => {
  loading.value = true;
  try {
    const token = sessionStorage.getItem("access_token");
    const res = await axios.get("http://localhost:8000/api/admin/reviews", {
      params: { status: currentStatus.value, keyword: keyword.value, page },
      headers: { Authorization: `Bearer ${token}` },
    });
    if (res.data.code === 200) {
      reviews.value = res.data.data.data;
      pagination.value = res.data.data;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const updateStatus = async (id, status) => {
  try {
    const token = sessionStorage.getItem("access_token");
    await axios.post(
      `http://localhost:8000/api/admin/reviews/${id}/status`,
      { status },
      { headers: { Authorization: `Bearer ${token}` } }
    );
    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 2000,
      timerProgressBar: true,
    });
    Toast.fire({ icon: "success", title: `Đã cập nhật: ${status}` });
    fetchReviews(pagination.value.current_page);
  } catch (e) {
    Swal.fire("Lỗi", "Có lỗi xảy ra", "error");
  }
};

const changeTab = (val) => {
  currentStatus.value = val;
  pagination.value.current_page = 1;
  keyword.value = "";
  fetchReviews();
};

const changePage = (page) => fetchReviews(page);

watch(keyword, () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    pagination.value.current_page = 1;
    fetchReviews();
  }, 500);
});

onMounted(() => fetchReviews());
</script>
