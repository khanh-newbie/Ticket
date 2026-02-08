<template>
    <!-- ================== ADMIN THEME ================== -->
    <div v-if="useAdminTheme" class="admin-theme">
        <SidebarComponent />

        <div class="content">
            <!-- Navbar -->
            <NavBarComponent />

            <main>
                <!-- Header -->
                <HeaderComponent />

                <!-- Các màn admin -->
                <RouterView />
            </main>
        </div>
    </div>

    <div v-else class="no-admin-layout">
        <RouterView />
    </div>
</template>

<script setup>
import HeaderComponent from '@/components/admin/layouts/HeaderComponent.vue'
import NavBarComponent from '@/components/admin/layouts/NavBarComponent.vue'
import SidebarComponent from '@/components/admin/layouts/SidebarComponent.vue'

import { onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const useAdminTheme = computed(() => {
    return route.name !== 'AdminEventDetail'
})

onMounted(() => {
    const menuBar = document.querySelector('.content nav .bx.bx-menu')
    const sideBar = document.querySelector('.sidebar')

    if (menuBar && sideBar) {
        menuBar.addEventListener('click', () => {
            sideBar.classList.toggle('close')
        })
    }

    const searchBtn = document.querySelector('.content nav form .form-input button')
    const searchBtnIcon = document.querySelector('.content nav form .form-input button .bx')
    const searchForm = document.querySelector('.content nav form')

    if (searchBtn && searchBtnIcon && searchForm) {
        searchBtn.addEventListener('click', function (e) {
            if (window.innerWidth < 576) {
                e.preventDefault()
                searchForm.classList.toggle('show')
                searchBtnIcon.classList.toggle('bx-x')
                searchBtnIcon.classList.toggle('bx-search')
            }
        })
    }
})
</script>

<style>
@import "@/assets/admin.css";
@import "boxicons/css/boxicons.min.css";

/* Layout cho EventsDetail */
.no-admin-layout {
    min-height: 100vh;
    background: #f5f5f5;
    padding: 24px;
}
</style>
