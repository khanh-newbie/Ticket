import { defineStore } from "pinia";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: JSON.parse(sessionStorage.getItem("user_info")) || null,
        showLoginPopup: false,
        redirectAfterLogin: null
    }),
    actions: {
        openLogin() {
            this.showLoginPopup = true;
        },
        closeLogin() {
            this.showLoginPopup = false;
        },
        setUser(user) {
            this.user = user;
            sessionStorage.setItem("user_info", JSON.stringify(user));
        },
        loadUser() {
            const u = sessionStorage.getItem("user_info");
            if (u) this.user = JSON.parse(u);
        },
        logout() {
            this.user = null;
            sessionStorage.removeItem("user_info");
        }
    }
});
