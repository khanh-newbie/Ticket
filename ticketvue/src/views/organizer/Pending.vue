<template>
  <div class="min-h-screen flex flex-col items-center justify-center text-center bg-gray-100 p-8">

    <img
      src="#"
      class="w-40 h-40 mb-6 opacity-90"
      alt="Pending"
    />

    <h2 class="text-3xl font-semibold mb-3 text-gray-800">
      Sự kiện của bạn đang chờ duyệt
    </h2>

    <p class="text-gray-600 max-w-xl mb-6 text-lg">
      Chúng tôi đã nhận được thông tin sự kiện của bạn.  
      Bộ phận kiểm duyệt sẽ xem xét trong vòng <strong>24–48 giờ</strong>.  
      Khi duyệt xong, bạn sẽ nhận được thông báo qua email.
    </p>

    <router-link
      to="/index"
      class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition"
    >
      Quay lại trang chủ
    </router-link>

  </div>
</template>

<script setup>
</script>
