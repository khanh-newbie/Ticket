<template>
  <div class="bottom-data">
    <div class="orders">
      <div class="header">
        <div class="left">
          <h3>Danh sách nhà tổ chức</h3>
        </div>

        <div class="tools">
          <div class="table-search">
            <input
              v-model="keyword"
              @keyup.enter="fetchOrganizers"
              type="text"
              placeholder="Tìm theo tên nhà tổ chức..."
            />
            <i class="bx bx-search" @click="fetchOrganizers"></i>
          </div>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Mà nhà tổ chức</th>
            <th>Tên tổ chức</th>
            <th>Người đại diện</th>
            <th>Email</th>
            <th>Số điện thoại</th>
            <th>Ngày đăng ký</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="loading">
            <td colspan="6">Đang tải dữ liệu...</td>
          </tr>

          <tr v-for="item in organizers" :key="item.id">
            <td>#{{ item.id }}</td>
            <td>{{ item.organization_name }}</td>
            <td>{{ item.user?.name }}</td>
            <td>{{ item.user?.email }}</td>
            <td>{{ item.user?.phone }}</td>

            <td>{{ formatDate(item.created_at) }}</td>
          </tr>

          <tr v-if="!loading && organizers.length === 0">
            <td colspan="6">Không có dữ liệu</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "OrganizerList",

  data() {
    return {
      organizers: [],
      keyword: "",
      loading: false,
    };
  },

  mounted() {
    this.fetchOrganizers();
  },

  methods: {
    async fetchOrganizers() {
      this.loading = true;
      try {
        const res = await axios.get("http://127.0.0.1:8000/api/admin/organizers", {
          params: {
            keyword: this.keyword,
          },
        });

        if (res.data.code === 200) {
          this.organizers = res.data.data;
        } else {
          // code = 204 → không có dữ liệu
          this.organizers = [];
        }
      } catch (error) {
        console.error(error);
        alert("Không tải được danh sách nhà tổ chức");
      } finally {
        this.loading = false;
      }
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString("vi-VN");
    },
  },
};
</script>
