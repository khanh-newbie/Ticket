<template>
  <div class="container mx-auto p-4 bg-white text-black">
    <!-- Slider -->
    <section class="mb-8">
        <div class="h-60 rounded-lg overflow-hidden">
            <img
                src="/images/slider1.jpg"
                alt="Slider"
                class="w-full h-full object-cover"
            />
        </div>
    </section>

    <!-- Xu hướng -->
    <section class="mb-10">
      <h2 class="text-xl font-bold mb-4 flex items-center">
        Xu hướng <span class="ml-2 text-orange-500">🔥</span>
      </h2>
      <div class="grid grid-cols-4 gap-6">
        <div
            v-for="n in 4"
            :key="n"
            class="relative border border-gray-300 rounded-lg flex items-center justify-center text-4xl font-bold text-red-600 h-40 overflow-hidden group hover:shadow-lg transition"
            >
           <img
                :src="`/images/thumb${n}.jpg`"
                alt="Trend"
                class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:opacity-70 transition"
            />
            <span class="relative z-10">{{ n }}</span>
            </div>
      </div>
    </section>

    <!-- Sự kiện trong tuần -->
    <section class="mb-12">
      <h2 class="text-xl font-bold mb-4">Sự kiện trong tuần</h2>
      <div class="flex space-x-4 overflow-x-auto pb-2">
        <div
            v-for="n in 4"
            :key="'event-' + n"
            class="relative rounded-xl min-w-[250px] h-60 overflow-hidden flex-shrink-0 group"
            >
            <!-- Ảnh nền -->
            <img
                :src="`/images/event${n}.jpg`"
                alt="Event"
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
            />

            <!-- Lớp phủ mờ -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>

            <!-- Nội dung chữ -->
            <div class="absolute bottom-0 left-0 right-0 text-white p-3">
                <h3 class="font-semibold text-sm mb-1">Tên sự kiện ví dụ {{ n }}</h3>
                <p class="text-green-400 text-xs">Từ 100.000đ</p>
                <p class="text-xs mt-1">
                <i class="fa-solid fa-calendar-days mr-1"></i> 08 Tháng 11, 2025
                </p>
            </div>
        </div>
      </div>
    </section>

    <!-- Footer
    <footer class="bg-gray-100 p-6 rounded-lg mt-10">
      <div class="grid grid-cols-3 gap-6">
        <div>
          <h3 class="font-bold text-lg mb-2">Về chúng tôi</h3>
          <p class="text-sm text-gray-700">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
          </p>
        </div>
        <div>
          <h3 class="font-bold text-lg mb-2">Liên lạc</h3>
          <p>Hotline: ......</p>
          <p>Địa chỉ: ......</p>
        </div>
        <div class="flex flex-col items-end justify-between">
          <img
            src="https://upload.wikimedia.org/wikipedia/commons/0/0b/VN_registered.png"
            alt="Đã đăng ký"
            class="w-24"
          />
        </div>
      </div>
      <div class="text-center text-gray-600 text-sm mt-4 border-t pt-2">
        © Công ty TNHH TicketZone
      </div>
    </footer> -->
  </div>
</template>
