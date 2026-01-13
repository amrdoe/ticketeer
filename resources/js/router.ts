import { createRouter, createWebHistory } from 'vue-router';
import Cart from './pages/Cart.vue';
import Checkout from './pages/Checkout.vue';
import EventDetail from './pages/EventDetail.vue';
import Events from './pages/Events.vue';
import Home from './pages/Home.vue';
import Login from './pages/Login.vue';
import MyTickets from './pages/MyTickets.vue';
import OrderConfirmation from './pages/OrderConfirmation.vue';
import Register from './pages/Register.vue';
import { useAuthStore } from './stores/useAuthStore';

const routes = [
    {
        path: '/',
        name: 'Home',
        component: Home,
    },
    {
        path: '/events',
        name: 'Events',
        component: Events,
    },
    {
        path: '/events/:id',
        name: 'EventDetail',
        component: EventDetail,
    },
    {
        path: '/cart',
        name: 'Cart',
        component: Cart,
    },
    {
        path: '/checkout',
        name: 'Checkout',
        component: Checkout,
        meta: { requiresAuth: true },
    },
    {
        path: '/order-confirmation/:orderId',
        name: 'OrderConfirmation',
        component: OrderConfirmation,
        meta: { requiresAuth: true },
    },
    {
        path: '/my-tickets',
        name: 'MyTickets',
        component: MyTickets,
        meta: { requiresAuth: true },
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
    },
    {
        path: '/register',
        name: 'Register',
        component: Register,
    },
    {
        path: '/tickets/scan/:code',
        name: 'ScanTicket',
        component: () => import('./pages/Scan.vue'),
        meta: { requiresAuth: true, organizerOnly: true },
        props: true,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    const user = await authStore.getUser();

    if (to.meta.requiresAuth && !user) {
        next('/login');
        return;
    }

    // Organizer-only route guard
    if (to.meta.organizerOnly && !user?.is_organizer) {
        next('/');
        return;
    }

    next();
});

export default router;
