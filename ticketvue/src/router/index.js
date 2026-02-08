import { createRouter, createWebHistory } from 'vue-router'
import IndexComponent from '@/views/customer/IndexComponent.vue';
import CreateEvent from '@/views/organizer/CreateEvent.vue';
import ExportFile from '@/views/organizer/ExportFile.vue';
import LegalDocument from '@/views/organizer/LegalDocument.vue';
import Pending from '@/views/organizer/Pending.vue';
import EventSearchPage from '@/views/customer/EventSearchPage.vue';
import EventDetail from '@/views/customer/EventDetail.vue';
import { authGuard } from "./guards/authGuard";
import Dashboard from '@/views/admin/Dashboard.vue';
import AdminLayout from '@/components/admin/layouts/AdminLayout.vue';
import ApproveEvent from '@/views/admin/ApproveEvent.vue';
import Order from '@/views/admin/Order.vue';
// import Ticket from '@/views/admin/Ticket.vue';
import Organizer from '@/views/admin/Organizer.vue';
import EventsDetail from '@/views/admin/EventsDetail.vue';
import Cart from '@/views/customer/Cart.vue';
import Login from '@/components/auth/Login.vue';
import Register from '@/components/auth/Register.vue';
import ForgotPassword from '@/components/auth/ForgotPassword.vue';
import Checkout from '@/views/customer/Checkout.vue';
import MyOrders from '@/views/customer/MyOrders.vue';
import Events from '@/views/organizer/Events.vue';
import MyProfile from '@/views/customer/MyProfile.vue';
import MyTicket from '@/views/customer/MyTicket.vue';
import Reviews from '@/views/admin/Reviews.vue';
import Reports from '@/views/admin/Reports.vue';
const routes = [
    // --- PUBLIC ROUTES (Ai cũng vào được) ---
    { path: '/', name: 'home', component: IndexComponent },
    { path: '/index', name: 'Index', component: IndexComponent },
    { path: '/search', name: 'EventSearchPage', component: EventSearchPage },
    { path: '/event', name: 'EventDetail', component: EventDetail },
    
    // --- GUEST ONLY (Đã đăng nhập thì không vào lại được) ---
    { 
        path: '/login', 
        name: 'Login', 
        component: Login,
        meta: { guestOnly: true } 
    },
    { 
        path: '/register', 
        name: 'Register', 
        component: Register,
        meta: { guestOnly: true }
    },
    { 
        path: '/forgot-password', 
        name: 'ForgotPassword', 
        component: ForgotPassword,
        meta: { guestOnly: true }
    },

    // --- CUSTOMER ROUTES (Cần đăng nhập) ---
    { 
        path: '/cart', name: 'Cart', component: Cart,
        meta: { requiresAuth: true }
    },
    { 
        path: '/checkout', name: 'CheckOut', component: Checkout,
        meta: { requiresAuth: true }
    },
    { 
        path: '/my-order', name: 'MyOrder', component: MyOrders,
        meta: { requiresAuth: true }
    },
    { 
        path: '/my-profile', name: 'MyProfile', component: MyProfile,
        meta: { requiresAuth: true }
    },
    { 
        path: '/my-tickets', name: 'MyTicket', component: MyTicket,
        meta: { requiresAuth: true }
    },

    // --- ORGANIZER ROUTES (Cần Role: organizer) ---
    {
        path: '/organizer/create-event', name: 'CreateEvent', component: CreateEvent,
        meta: { requiresAuth: true }
    },
    {
        path: '/organizer/export-file', name: 'ExportFile', component: ExportFile,
        meta: { requiresAuth: true }
    },
    {
        path: '/organizer/legal-document', name: 'LegalDocument', component: LegalDocument,
        meta: { requiresAuth: true }
    },
    {
        path: "/organizer/pending", name: "EventPending", component: Pending,
        meta: { requiresAuth: true }
    },
    {
        path: '/organizer/events', name: 'Events', component: Events,
        meta: { requiresAuth: true }
    },

    // --- ADMIN ROUTES (Cần Role: admin) ---
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiresAuth: true, role: 'admin' }, // Áp dụng cho cả con
        children: [
            { path: 'dashboard', name: "AdminDashboard" , component: Dashboard },
            { path: 'approve-events', name: "AdminApproveEvents" , component: ApproveEvent},
            { path: 'approve-events/:id', name: "AdminEventDetail", component: EventsDetail},
            { path: 'orders', name: "AdminOrders" , component: Order},
            { path: 'organizers', name: "AdminOrganizers" , component: Organizer},
            {path: 'reviews', name: "AdminReviews" , component: Reviews},
            {path: 'reports', name: "AdminReports" , component: Reports},
        ]
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach(authGuard);
export default router;