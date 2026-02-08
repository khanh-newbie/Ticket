<template>
    <div class="header">
        <div class="left">
            <h1>{{ title }}</h1>

            <ul class="breadcrumb">
                <li><a>{{ parent }}</a></li>
                /
                <li><a class="active">{{ title }}</a></li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { useRoute } from 'vue-router';
import { computed } from "vue";

const route = useRoute();

const titleMap = {
    AdminDashboard: "Tổng quan",
    AdminApproveEvents: "Duyệt sự kiện",
    AdminEventDetail: "Chi tiết sự kiện",
    AdminOrders: "Quản lý đơn hàng",
    AdminReviews: "Quản lý báo cáo phạm vi",
    AdminReports: "Quản lý báo cáo vi phạm",
    AdminOrganizers: "Quản lý nhà tổ chức",
};

const parentMap = {
    AdminDashboard: "Admin",
    AdminApproveEvents: "Admin",
    AdminEventDetail: "Duyệt sự kiện",  
    AdminOrders: "Admin",
    AdminReviews: "Admin",
    AdminReports: "Admin",
    AdminOrganizers: "Admin",
};

const title = computed(() => titleMap[route.name] ?? "Dashboard");
const parent = computed(() => parentMap[route.name] ?? "Admin");
</script>

