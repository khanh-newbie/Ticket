<template>
    <div class="sidebar">
        <router-link to="/admin/dashboard" class="logo">
            <i class='bx bx-code-alt'></i>
            <div class="logo-name"><span>Ticket</span>Admin</div>
        </router-link>

        <ul class="side-menu">
            <li :class="{ active: route.name === 'AdminDashboard' }">
                <router-link to="/admin/dashboard"><i class='bx bxs-dashboard'></i>Tổng quan</router-link>
            </li>
            <li :class="{ active: route.name === 'AdminApproveEvents' }">
                <router-link to="/admin/approve-events"><i class='bx bx-calendar-check'></i>Duyệt sự kiện</router-link>
            </li>
            <li :class="{ active: route.name === 'AdminOrders' }">
                <router-link to="/admin/orders"><i class='bx bx-cart-alt'></i>Quản lý đơn hàng</router-link>
            </li>
            <li :class="{ active: route.name === 'AdminReviews' }">
                <router-link to="/admin/reviews"><i class='bx bx-message-square-detail'></i>Quản lý đánh
                    giá</router-link>
            </li>

            <li :class="{ active: route.name === 'AdminReports' }">
                <router-link to="/admin/reports">
                    <i class='bx bx-flag'></i> Quản lý báo cáo
                </router-link>
            </li>

            <li :class="{ active: route.name === 'AdminOrganizers' }">
                <router-link to="/admin/organizers"><i class='bx bx-user-voice'></i>Quản lý nhà tổ chức</router-link>
            </li>
        </ul>

        <ul class="side-menu">
            <li style="color: var(--danger)" @click="logout">
                <!-- <li>
                    <router-link to="/" class="logout"><i class='bx bx-log-out-circle'></i>Đăng xuất</router-link>
                </li> -->
                <i class='bx bx-log-out-circle'></i>Đăng xuất
            </li>
        </ul>
    </div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router';

const router = useRouter();
const route = useRoute();
const logout = () => {
  sessionStorage.clear();
  router.push("/");
};
</script>