import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { api } from '../utils/api';

export const useCartStore = defineStore('cart', () => {
    const items = ref<CartItem[]>(
        JSON.parse(localStorage.getItem('cart_items') || '[]'),
    );
    const loading = ref(false);
    const error = ref('');

    const totalItems = computed(() =>
        items.value.reduce((sum, item) => sum + item.quantity, 0),
    );

    const totalPrice = computed(() => {
        return items.value.reduce(
            (sum, item) => sum + item.unit_price * item.quantity,
            0,
        );
    });

    const addItem = (ticketType: TicketType) => {
        const existingItem = items.value.find(
            (item) => item.ticket_type_id === ticketType.id,
        );

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            items.value.push({
                ticket_type_id: ticketType.id,
                name: ticketType.name,
                code: ticketType.code,
                unit_price: parseFloat(ticketType.price),
                quantity: 1,
                event_id: ticketType.event_id,
            });
        }

        saveCart();
    };

    const removeItem = (ticketTypeId: string) => {
        items.value = items.value.filter(
            (item) => item.ticket_type_id !== ticketTypeId,
        );
        saveCart();
    };

    const updateQuantity = (ticketTypeId: string, quantity: number) => {
        const item = items.value.find(
            (item) => item.ticket_type_id === ticketTypeId,
        );

        if (item) {
            if (quantity <= 0) {
                removeItem(ticketTypeId);
            } else {
                item.quantity = quantity;
                saveCart();
            }
        }
    };

    const clear = () => {
        items.value = [];
        localStorage.removeItem('cart_items');
    };

    const validateCart = async () => {
        loading.value = true;
        error.value = '';

        try {
            const cartItems = items.value.map((item) => ({
                ticket_type_id: item.ticket_type_id,
                quantity: item.quantity,
            }));

            const response = await api.validateCart(cartItems);
            return response;
        } catch (err) {
            error.value = (err as Error).message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const saveCart = () => {
        localStorage.setItem('cart_items', JSON.stringify(items.value));
    };

    return {
        items,
        loading,
        error,
        totalItems,
        totalPrice,
        addItem,
        removeItem,
        updateQuantity,
        clear,
        validateCart,
    };
});
