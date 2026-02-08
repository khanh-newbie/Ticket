<template>
  <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 h-full flex flex-col">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Biểu đồ doanh thu</h3>
            <div class="flex items-center gap-2 mt-1">
                 <h2 class="text-2xl font-bold text-green-600">
                    {{ new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(totalRevenue) }}
                </h2>
                <span v-if="loading" class="text-xs text-gray-400"><i class='bx bx-loader-alt bx-spin'></i></span>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 items-center justify-end">
            <select v-model="filter.type" @change="handleTypeChange" class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:ring-2 focus:ring-green-500 outline-none bg-gray-50 font-medium text-gray-700">
                <option value="default">7 ngày qua</option>
                <option value="date_range">Khoảng ngày</option>
                <option value="month_range">Khoảng tháng</option>
            </select>

            <div v-if="filter.type === 'date_range'" class="flex gap-1 items-center bg-gray-50 p-1 rounded border">
                <input type="date" v-model="filter.start_date" @change="fetchData" class="bg-transparent text-xs outline-none w-[110px]" />
                <span class="text-gray-400">-</span>
                <input type="date" v-model="filter.end_date" @change="fetchData" class="bg-transparent text-xs outline-none w-[110px]" />
            </div>

            <div v-if="filter.type === 'month_range'" class="flex gap-1 items-center bg-purple-50 p-1 rounded border border-purple-100">
                <input type="month" v-model="filter.start_month" @change="fetchData" class="bg-transparent text-xs outline-none text-purple-700 font-medium" />
                <span class="text-purple-300">-</span>
                <input type="month" v-model="filter.end_month" @change="fetchData" class="bg-transparent text-xs outline-none text-purple-700 font-medium" />
            </div>
            
            <button @click="fetchData" class="p-1.5 text-gray-500 hover:text-green-600 border rounded-lg hover:bg-gray-50 transition" title="Làm mới dữ liệu">
                <i class='bx bx-refresh text-lg'></i>
            </button>
        </div>
    </div>
    
    <div class="flex-1 w-full min-h-[300px] relative">
      <Line v-if="!loading" :data="chartData" :options="chartOptions" />
      
      <div v-else class="absolute inset-0 flex items-center justify-center bg-white/50 z-10">
          <div class="animate-spin w-8 h-8 border-4 border-green-500 rounded-full border-t-transparent"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue'
import axios from 'axios'
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler)

const loading = ref(false)
const apiData = ref({ dates: [], values: [] })

// Lấy tháng hiện tại và tháng 1 để làm mặc định
const currentMonth = new Date().toISOString().slice(0, 7); // 2025-12
const startOfYear = new Date().toISOString().slice(0, 4) + '-01'; // 2025-01

const filter = reactive({
    type: 'default',
    start_date: '',
    end_date: '',
    start_month: startOfYear, 
    end_month: currentMonth
})

const totalRevenue = computed(() => apiData.value.values.reduce((a, b) => a + Number(b), 0))

// Cấu hình hiển thị Chart
const chartData = computed(() => ({
  labels: apiData.value.dates,
  datasets: [
    {
      label: 'Doanh thu',
      // Đổi màu Tím nếu xem theo tháng, Xanh nếu xem theo ngày
      backgroundColor: filter.type === 'month_range' ? (ctx) => {
         const canvas = ctx.chart.ctx;
         const gradient = canvas.createLinearGradient(0, 0, 0, 300);
         gradient.addColorStop(0, 'rgba(147, 51, 234, 0.2)'); 
         gradient.addColorStop(1, 'rgba(147, 51, 234, 0)');
         return gradient;
      } : (ctx) => {
         const canvas = ctx.chart.ctx;
         const gradient = canvas.createLinearGradient(0, 0, 0, 300);
         gradient.addColorStop(0, 'rgba(34, 197, 94, 0.2)');
         gradient.addColorStop(1, 'rgba(34, 197, 94, 0)');
         return gradient;
      },
      borderColor: filter.type === 'month_range' ? '#9333ea' : '#22c55e',
      borderWidth: 2,
      pointBackgroundColor: '#fff',
      pointBorderColor: filter.type === 'month_range' ? '#9333ea' : '#22c55e',
      pointRadius: 4,
      fill: true,
      tension: 0.4,
      data: apiData.value.values
    }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
        backgroundColor: '#1f2937',
        padding: 12,
        callbacks: {
            label: (context) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.raw)
        }
    }
  },
  scales: {
    y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
    x: { grid: { display: false } }
  }
}

const handleTypeChange = () => {
    fetchData(); // Gọi lại API khi đổi loại lọc
}

// HÀM GỌI API CHÍNH
const fetchData = async () => {
    // Validate dữ liệu đầu vào
    if (filter.type === 'date_range' && (!filter.start_date || !filter.end_date)) return;
    if (filter.type === 'month_range' && (!filter.start_month || !filter.end_month)) return;

    loading.value = true;
    try {
        const token = sessionStorage.getItem('access_token');
        
        // [QUAN TRỌNG] Gọi đúng API dashboard-revenue
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

        if(res.data.code === 200) {
            // Lấy đúng key revenue_chart từ backend
            apiData.value = res.data.data.revenue_chart;
            
            // Note: API này trả về cả `order_chart` (biểu đồ tròn). 
            // Nếu bạn muốn cập nhật biểu đồ tròn theo bộ lọc này, 
            // bạn cần emit sự kiện ra ngoài Dashboard.
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