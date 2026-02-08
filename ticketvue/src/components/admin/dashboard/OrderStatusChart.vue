<template>
  <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 h-full flex flex-col">
    <div class="flex justify-between items-center mb-4">
         <h3 class="text-lg font-bold text-gray-800">Trạng thái đơn hàng</h3>
         <button @click="fetchData" class="text-gray-400 hover:text-green-600 transition" title="Cập nhật">
            <i class='bx bx-refresh text-xl'></i>
         </button>
    </div>

    <div class="flex flex-wrap gap-2 items-center mb-4 justify-end">
        <select v-model="filter.type" @change="fetchData" class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-2 focus:ring-green-500 outline-none bg-gray-50 font-medium text-gray-700 w-full sm:w-auto">
            <option value="default">7 ngày qua</option>
            <option value="date_range">Khoảng ngày</option>
            <option value="month_range">Khoảng tháng</option>
        </select>

        <div v-if="filter.type === 'date_range'" class="flex gap-1 items-center bg-gray-50 p-1 rounded border w-full sm:w-auto justify-between">
            <input type="date" v-model="filter.start_date" @change="fetchData" class="bg-transparent text-xs outline-none w-[95px]" />
            <span class="text-gray-400">-</span>
            <input type="date" v-model="filter.end_date" @change="fetchData" class="bg-transparent text-xs outline-none w-[95px]" />
        </div>

        <div v-if="filter.type === 'month_range'" class="flex gap-1 items-center bg-purple-50 p-1 rounded border border-purple-100 w-full sm:w-auto justify-between">
            <input type="month" v-model="filter.start_month" @change="fetchData" class="bg-transparent text-xs outline-none text-purple-700 font-medium" />
            <span class="text-purple-300">-</span>
            <input type="month" v-model="filter.end_month" @change="fetchData" class="bg-transparent text-xs outline-none text-purple-700 font-medium" />
        </div>
    </div>

    <p class="text-xs text-gray-500 mb-4 text-center italic">
        {{ filterSubtitle }}
    </p>
    
    <div class="flex-1 relative min-h-[250px] flex justify-center items-center">
      <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/50 z-10">
          <div class="animate-spin w-8 h-8 border-4 border-green-500 rounded-full border-t-transparent"></div>
      </div>

      <Doughnut v-if="loaded" :data="chartData" :options="chartOptions" />
      
      <div v-if="loaded" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
          <span class="text-3xl font-bold text-gray-800">{{ totalOrders }}</span>
          <span class="text-xs text-gray-400 font-medium uppercase">Đơn hàng</span>
      </div>
      
      <div v-if="loaded && totalOrders === 0" class="absolute inset-0 flex flex-col items-center justify-center text-gray-400">
          <i class='bx bx-doughnut-chart text-4xl mb-2 opacity-50'></i>
          <span class="text-xs">Chưa có dữ liệu</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue'
import axios from 'axios'
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend)

const loading = ref(false)
const loaded = ref(false)
const apiData = ref([0, 0, 0]) // [Paid, Pending, Canceled]

// --- STATE BỘ LỌC ---
const currentMonth = new Date().toISOString().slice(0, 7);
const startOfYear = new Date().toISOString().slice(0, 4) + '-01';

const filter = reactive({
    type: 'default',
    start_date: '',
    end_date: '',
    start_month: startOfYear, 
    end_month: currentMonth
})

// Tính text hiển thị subtitle
const filterSubtitle = computed(() => {
    if (filter.type === 'default') return 'Thống kê 7 ngày gần nhất';
    if (filter.type === 'month_range') return 'Thống kê theo xu hướng tháng';
    return 'Thống kê theo khoảng thời gian';
});

// Tính tổng đơn
const totalOrders = computed(() => apiData.value.reduce((a, b) => a + b, 0))

// Cấu hình Data Chart
const chartData = computed(() => ({
  labels: ['Hoàn thành', 'Đang chờ', 'Đã hủy'],
  datasets: [
    {
      backgroundColor: ['#22c55e', '#eab308', '#ef4444'], // Green, Yellow, Red
      borderWidth: 0,
      hoverOffset: 4,
      data: apiData.value
    }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '75%', 
  plugins: {
    legend: {
        position: 'bottom',
        labels: { usePointStyle: true, padding: 20, font: { size: 12 } }
    },
    tooltip: {
        callbacks: {
            label: (context) => {
                const value = context.raw;
                const total = context.chart._metasets[context.datasetIndex].total;
                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                return ` ${context.label}: ${value} đơn (${percentage})`;
            }
        }
    }
  }
}

const fetchData = async () => {
    // Validate bộ lọc trước khi gọi
    if (filter.type === 'date_range' && (!filter.start_date || !filter.end_date)) return;
    if (filter.type === 'month_range' && (!filter.start_month || !filter.end_month)) return;

    loading.value = true;
    try {
        const token = sessionStorage.getItem('access_token');
        
        // Gọi API với đầy đủ params bộ lọc
        const res = await axios.get('http://localhost:8000/api/admin/dashboard-revenue', {
            params: {
                filter_type: filter.type,
                start_date: filter.start_date,
                end_date: filter.end_date,
                start_month: filter.start_month,
                end_month: filter.end_month
            },
            headers: { Authorization: `Bearer ${token}` }
        });

        if (res.data.code === 200) {
            apiData.value = res.data.data.order_chart;
            loaded.value = true;
        }
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchData();
})
</script>