<template>
  <div class="bottom-data">
    <div class="orders">
      <div class="header">
        <div class="left">
          <i class="bx bx-receipt"></i>
          <h3>Danh sách đơn hàng đã thanh toán</h3>
        </div>

        <div class="tools">
          <div class="table-search">
            <input v-model="keyword" type="text" placeholder="Tìm theo tên sự kiện..." />
            <i class="bx bx-search"></i>
          </div>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Mã đơn hàng</th>
            <th>Người mua</th>
            <th>Sự kiện</th>
            <th>Số vé</th>
            <th>Thành tiền</th>
            <th>Trạng thái</th>
            <th>Thanh toán</th>
            <th>Ngày đặt</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="order in filteredOrders" :key="order.id">
            <td>#{{ order.id }}</td>
            <td>{{ order.buyer_name }}</td>
            <td>{{ order.event_name }}</td>
            <td>{{ order.ticket_quantity }} vé</td>
            <td>{{ formatMoney(order.final_amount) }}</td>
            <td>
              <span
                class="status"
                :class="{
                  completed: order.payment_status === 'paid',
                  process: order.payment_status === 'pending',
                  cancell: order.payment_status === 'cancelled',
                }"
              >
                {{
                  order.payment_status === "paid"
                    ? "Đã thanh toán"
                    : order.payment_status === "pending"
                    ? "Chờ thanh toán"
                    : order.payment_status === "cancelled"
                    ? "Đã hủy"
                    : order.payment_status
                }}
              </span>
            </td>

            <td>{{ order.payment_method }}</td>
            <td>{{ formatDate(order.created_at) }}</td>
          </tr>

          <tr v-if="filteredOrders.length === 0">
            <td colspan="8" style="text-align: center">Không có đơn hàng</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";

const orders = ref([]);
const keyword = ref("");

const fetchOrders = async () => {
  const res = await axios.get("http://127.0.0.1:8000/api/admin/orders/paid");
  orders.value = res.data.data;
};

const filteredOrders = computed(() => {
  if (!keyword.value) return orders.value;
  return orders.value.filter((o) =>
    o.event_name.toLowerCase().includes(keyword.value.toLowerCase())
  );
});

const formatMoney = (money) => {
  return Number(money).toLocaleString("vi-VN") + "đ";
};

const formatDate = (date) => {
  return new Date(date).toLocaleString("vi-VN");
};

onMounted(fetchOrders);
</script>
