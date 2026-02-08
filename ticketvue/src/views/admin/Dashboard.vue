<template>
  <div class="flex flex-col gap-8">
    <!-- Insight -->
    <Insight />

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <RevenueChart />
      </div>

      <div>
        <OrderStatusChart />
      </div>
    </div>
  </div>
</template>

<script setup>
import Insight from '@/components/admin/dashboard/Insight.vue'
import RevenueChart from '@/components/admin/dashboard/RevenueChart.vue'
import OrderStatusChart from '@/components/admin/dashboard/OrderStatusChart.vue'
</script>
