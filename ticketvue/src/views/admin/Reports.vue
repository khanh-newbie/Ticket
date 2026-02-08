<template>
  <div style="margin-top: 20px" class="reports-container">

    <!-- Tabs -->
    <div class="reports-tabs">
      <button 
        v-for="tab in tabs" :key="tab.value" 
        @click="changeTab(tab.value)"
        :class="{ active: currentStatus === tab.value }">
        <i :class="tab.icon"></i> {{ tab.label }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="reports-loading">
      <div class="spinner"></div>
      <p>Đang tải dữ liệu...</p>
    </div>

    <!-- Empty -->
    <div v-else-if="reports.length === 0" class="reports-empty">
      <i class='bx bx-flag'></i>
      <h3>Không có báo cáo nào</h3>
      <p>Hệ thống hiện tại đang "sạch sẽ".</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto">
      <table class="reports-table">
        <thead>
          <tr>
            <th>Người báo cáo</th>
            <th>Loại</th>
            <th>Đối tượng bị báo cáo</th>
            <th>Lý do & Chi tiết</th>
            <th>Trạng thái</th>
            <th style="text-align: center">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="report in reports" :key="report.id">
            <!-- Người báo cáo -->
            <td>
              <div style="display:flex; align-items:center; gap:0.5rem;">
                <div style="width:2.5rem;height:2.5rem;border-radius:9999px;background:#e5e7eb;color:#4b5563;display:flex;align-items:center;justify-content:center;font-weight:700;">
                  {{ report.user?.name?.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <div style="font-weight:700; color:#111827;">{{ report.user?.name }}</div>
                  <div style="font-size:0.75rem;color:#6b7280;">ID: {{ report.user_id }}</div>
                </div>
              </div>
            </td>

            <!-- Loại -->
            <td>
              <span v-if="report.event_id" class="badge-event">Sự kiện</span>
              <span v-else-if="report.review_id" class="badge-review">Đánh giá</span>
            </td>

            <!-- Đối tượng -->
            <td>
              <div v-if="report.event">
                <router-link :to="`/event-detail/${report.event.id}`" target="_blank" style="font-weight:700; color:#111827; text-decoration:none;">
                  {{ report.event.event_name }} <i class='bx bx-link-external'></i>
                </router-link>
                <p style="font-size:0.75rem;color:#6b7280;margin-top:0.25rem;">
                  BTC: <span style="font-weight:500;">{{ report.event.organizer?.organization_name }}</span>
                </p>
              </div>
              <div v-else-if="report.review">
                <div style="background:#f9fafb;padding:0.75rem;border:1px solid #e5e7eb;border-radius:0.5rem;font-style:italic;color:#374151;font-size:0.875rem;position:relative;">
                  {{ report.review.comment }}
                </div>
                <div style="display:flex;gap:0.5rem;font-size:0.75rem;color:#6b7280;margin-top:0.25rem;font-weight:500;">
                  <span>Review ID: {{ report.review.id }}</span>
                  <span style="color:#facc15;font-weight:700;"><i class='bx bxs-star'></i> {{ report.review.rating }}</span>
                </div>
              </div>
              <div v-else style="color:#b91c1c;font-style:italic;padding:0.5rem;background:#fee2e2;border-radius:0.25rem;display:flex;align-items:center;gap:0.25rem;font-weight:500;">
                <i class='bx bx-error-circle'></i> Đối tượng không tồn tại hoặc đã bị xóa.
              </div>
            </td>

            <!-- Lý do & chi tiết -->
            <td>
              <p style="font-weight:700;color:#dc2626;margin-bottom:0.25rem;">{{ report.reason }}</p>
              <p style="color:#4b5563;font-size:0.875rem;">{{ report.description || 'Không có mô tả chi tiết.' }}</p>
              <div style="font-size:0.75rem;color:#9ca3af;margin-top:0.25rem;display:flex;align-items:center;gap:0.25rem;">
                <i class='bx bx-time'></i> {{ formatDate(report.created_at) }}
              </div>
            </td>

            <!-- Trạng thái -->
            <td>
              <span :class="{
                    'status-pending': report.status==='pending',
                    'status-resolved': report.status==='resolved',
                    'status-dismissed': report.status==='dismissed'
                  }" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border capitalize">
                <i :class="{
                    'bx bx-radio-circle-marked mr-1 text-yellow-600': report.status==='pending',
                    'bx bx-radio-circle-marked mr-1 text-green-600': report.status==='resolved',
                    'bx bx-radio-circle-marked mr-1 text-gray-600': report.status==='dismissed'
                  }"></i>
                {{ statusLabel(report.status) }}
              </span>
            </td>

            <!-- Hành động -->
            <td style="text-align:center;">
              <div v-if="report.status==='pending'" style="display:flex; gap:0.5rem; justify-content:flex-end;">
                <button @click="updateStatus(report.id,'resolved')" style="padding:0.5rem;border-radius:0.5rem;background:#dcfce7;color:#166534;border:1px solid #bbf7d0; cursor:pointer;" title="Đã xử lý (Vi phạm)">
                  <i class='bx bx-check-shield'></i>
                </button>
                <button @click="updateStatus(report.id,'dismissed')" style="padding:0.5rem;border-radius:0.5rem;background:#f3f4f6;color:#374151;border:1px solid #d1d5db; cursor:pointer;" title="Bỏ qua (Không vi phạm)">
                  <i class='bx bx-x-circle'></i>
                </button>
              </div>
              <span v-else style="font-size:0.75rem;color:#9ca3af;font-style:italic;">Đã hoàn tất</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page>1" class="reports-pagination">
      <button @click="changePage(pagination.current_page-1)" :disabled="pagination.current_page===1">Trước</button>
      <span>{{ pagination.current_page }} / {{ pagination.last_page }}</span>
      <button @click="changePage(pagination.current_page+1)" :disabled="pagination.current_page===pagination.last_page">Sau</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const reports = ref([]);
const loading = ref(false);
const currentStatus = ref('pending');
const pagination = ref({ current_page:1, total:0, last_page:1 });

const tabs = [
  { label:'Chờ xử lý', value:'pending', icon:'bx bx-time' },
  { label:'Đã xử lý (Vi phạm)', value:'resolved', icon:'bx bx-check-shield' },
  { label:'Đã bỏ qua', value:'dismissed', icon:'bx bx-x-circle' }
];

const statusLabel = s => ({ pending:'Chờ xử lý', resolved:'Đã xử lý', dismissed:'Đã bỏ qua'}[s] || s);
const formatDate = d => new Date(d).toLocaleDateString('vi-VN',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});

const fetchReports = async (page=1)=>{
  loading.value = true;
  try{
    const token = sessionStorage.getItem('access_token');
    const res = await axios.get('http://localhost:8000/api/admin/reports',{
      params:{status:currentStatus.value,page},
      headers:{Authorization:`Bearer ${token}`}
    });
    if(res.data.code===200){
      reports.value=res.data.data.data;
      pagination.value=res.data.data;
    }
  }catch(e){
    console.error(e);
    Swal.fire('Lỗi','Không thể tải danh sách báo cáo','error');
  }finally{
    loading.value=false;
  }
};

const updateStatus = async (id,status)=>{
  try{
    const token=sessionStorage.getItem('access_token');
    await axios.post(`http://localhost:8000/api/admin/reports/${id}/status`,
      {status},
      {headers:{Authorization:`Bearer ${token}`}}
    );
    Swal.fire({
      icon: status==='resolved'?'success':'info',
      title: status==='resolved'?'Đã xác nhận vi phạm':'Đã bỏ qua báo cáo',
      timer:1500,showConfirmButton:false
    });
    fetchReports(pagination.value.current_page);
  }catch(e){
    Swal.fire('Lỗi','Có lỗi xảy ra khi cập nhật','error');
  }
};

const changeTab = val=>{
  currentStatus.value=val;
  pagination.value.current_page=1;
  fetchReports();
};
const changePage = page=>fetchReports(page);

onMounted(()=>{ fetchReports(); });
</script>
