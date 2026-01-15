const API_BASE_URL = '/api';

type Method = 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH';

export const api = {
    async request(
        method: Method,
        endpoint: string,
        data: any = null,
        token = '',
    ) {
        const headers = new Headers({
            'Content-Type': 'application/json',
            Accept: 'application/json',
        });

        if (token) headers.set('Authorization', `Bearer ${token}`);

        const options: RequestInit = {
            method,
            headers,
        };

        if (
            data &&
            (method === 'POST' || method === 'PUT' || method === 'PATCH')
        ) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, options);
            const responseData = await response.json();

            if (!response.ok) {
                throw new Error(responseData.message || 'API Error');
            }

            return responseData;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    // Auth endpoints
    register(data: any) {
        return this.request('POST', '/auth/register', data);
    },

    login(data: any) {
        return this.request('POST', '/auth/login', data);
    },

    logout(token: string) {
        return this.request('POST', '/auth/logout', null, token);
    },

    getMe(token: string) {
        return this.request('GET', '/auth/me', null, token);
    },

    updateProfile(data: any, token: string) {
        return this.request('PUT', '/auth/profile', data, token);
    },

    // Events endpoints
    getEvents(page = 1) {
        return this.request('GET', `/events?page=${page}`);
    },

    getFeaturedEvents() {
        return this.request('GET', '/events/featured');
    },

    getEvent(id: string) {
        return this.request('GET', `/events/${id}`);
    },

    // Events belonging to the authenticated user
    getMyEvents(page = 1, token = '') {
        return this.request('GET', `/user/events?page=${page}`, null, token);
    },

    createEvent(data: any, token: string) {
        return this.request('POST', '/events', data, token);
    },

    updateEvent(id: string, data: any, token: string) {
        return this.request('PUT', `/events/${id}`, data, token);
    },

    deleteEvent(id: string, token: string) {
        return this.request('DELETE', `/events/${id}`, null, token);
    },

    // Ticket types endpoints
    getTicketTypes(eventId: string) {
        return this.request('GET', `/events/${eventId}/tickets`);
    },

    createTicketType(eventId: string, data: any, token: string) {
        return this.request('POST', `/events/${eventId}/tickets`, data, token);
    },

    updateTicketType(id: string, data: any, token: string) {
        return this.request('PUT', `/tickets/${id}`, data, token);
    },

    deleteTicketType(id: string, token: string) {
        return this.request('DELETE', `/tickets/${id}`, null, token);
    },

    // Cart endpoints
    validateCart(items: any[]) {
        return this.request('POST', '/cart/validate', { items });
    },

    // Orders endpoints
    getOrders(token: string) {
        return this.request('GET', '/orders', null, token);
    },

    createOrder(items: any[], token: string) {
        return this.request('POST', '/orders', { items }, token);
    },

    getOrder(id: string, token: string) {
        return this.request('GET', `/orders/${id}`, null, token);
    },

    cancelOrder(id: string, token: string) {
        return this.request('POST', `/orders/${id}/cancel`, null, token);
    },

    // Payment endpoints
    createPaymentIntent(orderId: string, token: string) {
        return this.request(
            'POST',
            `/orders/${orderId}/payment-intent`,
            null,
            token,
        );
    },

    confirmPayment(orderId: string, paymentIntentId: string, token: string) {
        return this.request(
            'POST',
            `/orders/${orderId}/confirm-payment`,
            { payment_intent_id: paymentIntentId },
            token,
        );
    },

    getPaymentStatus(orderId: string, token: string) {
        return this.request(
            'GET',
            `/orders/${orderId}/payment-status`,
            null,
            token,
        );
    },

    getTickets(token: string) {
        return this.request('GET', '/tickets', null, token);
    },

    scanTicket(code: string, token: string) {
        return this.request('POST', '/tickets/scan', { code }, token);
    },

    redeemTicket(code: string, token: string) {
        return this.request('POST', '/tickets/redeem', { code }, token);
    },
};
