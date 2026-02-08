<template>
  <header
    class="fixed top-0 ml-84 w-[calc(100%-21rem)] flex justify-between items-center bg-gray-800 bg-opacity-50 px-4 py-2 z-10"
  >
    <div class="flex items-center w-[450px]">
      <div class="font-bold text-2xl text-white truncate">
        {{ pageTitle }}
      </div>
    </div>

    <div class="flex items-center gap-4">
      <router-link
        to="/organizer/create-event?step=info"
        class="hidden lg:inline-flex px-3 py-1.5 bg-green-500 rounded-full gap-2 items-center justify-center cursor-pointer hover:bg-green-600"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none">
          <path
            fill="#fff"
            d="M10.833 5a.833.833 0 0 0-1.666 0v4.167H5a.833.833 0 1 0 0 1.667h4.167V15a.833.833 0 1 0 1.666 0v-4.166H15a.833.833 0 0 0 0-1.667h-4.167z"
          />
        </svg>
        <span class="text-white text-sm font-medium">Tạo sự kiện</span>
      </router-link>

      <div class="relative inline-block text-left">
        <button
          @click="open = !open"
          class="flex items-center p-2 rounded-full cursor-pointer bg-transparent hover:bg-gray-700"
        >
          <img
            :src="user.avatar || defaultAvatar"
            class="w-8 h-8 rounded-full border border-gray-300 p-0.5"
          />
          <span class="ml-2 text-sm font-medium text-white hidden md:block">
            {{ user.name }}
          </span>
          <svg
            class="w-4 h-4 ml-2 text-white hidden md:block"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path d="M5 6l5 5 5-5H5z"></path>
          </svg>
        </button>

        <!-- DROPDOWN -->
        <div
          v-show="open"
          class="absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg z-50"
        >
          <router-link
            to="/my-tickets"
            class="flex items-center px-4 py-2 hover:bg-gray-100"
          >
            <i class="fas fa-ticket-alt mr-2"></i> Vé của tôi
          </router-link>
          <router-link
            to="/my-profile"
            class="flex items-center px-4 py-2 hover:bg-gray-100"
          >
            <i class="fas fa-user mr-2"></i> Tài khoản của tôi
          </router-link>
          <router-link
            to="/organizer/events"
            class="flex items-center px-4 py-2 hover:bg-gray-100"
          >
            <i class="fas fa-calendar-alt mr-2"></i> Sự kiện của tôi
          </router-link>
          <button
            @click="logout"
            class="w-full text-left flex items-center px-4 py-2 hover:bg-gray-100 text-red-500"
          >
            <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const open = ref(false);

const user = JSON.parse(sessionStorage.getItem("user_info"));
const defaultAvatar = "/images/default-avatar.png";

const logout = () => {
  sessionStorage.clear();
  router.push("/");
};

const pageTitle = computed(() => {
  if (route.path.includes("create-event")) return "Sự kiện của tôi";
  if (route.path.includes("legal-document")) return "Điều khoản ban tổ chức";
  return "Dashboard";
});
</script>
