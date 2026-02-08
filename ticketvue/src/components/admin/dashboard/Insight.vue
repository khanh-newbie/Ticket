<template>
    <ul class="insights">
        <li>
            <i class='bx bx-calendar-check'></i>
            <span class="info">
                <h3>{{ stats.events }}</h3>
                <p>Sự kiện</p>
            </span>
        </li>

        <li>
            <i class='bx bx-cart'></i>
            <span class="info">
                <h3>{{ stats.orders }}</h3>
                <p>Đơn hàng</p>
            </span>
        </li>

        <li>
            <i class='bx bx-line-chart'></i>
            <span class="info">
                <h3>{{ stats.organizers }}</h3>
                <p>Nhà tổ chức</p>
            </span>
        </li>

        <li>
            <i class='bx bx-atom'></i>
            <span class="info">
                <h3>{{ stats.tickets }}</h3>
                <p>Vé</p>
            </span>
        </li>
    </ul>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const stats = ref({
    events: 0,
    orders: 0,
    organizers: 0,
    tickets: 0
})

async function fetchStats() {
    try {
        const res = await axios.get('http://127.0.0.1:8000/api/admin/dashboard-stats')
        stats.value = res.data
    } catch (err) {
        console.error("Lỗi lấy thống kê:", err)
    }
}

onMounted(() => {
    fetchStats()
})
</script>
