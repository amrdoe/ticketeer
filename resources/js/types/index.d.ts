interface CartItem {
    name: string;
    code: string;
    ticket_type_id: string;
    unit_price: number;
    quantity: number;
    event_id: string;
}

interface Event {
    id: string;
    title: string;
    description: string;
    start_date: string;
    end_date: string;
    sale_start: string;
    sale_end: string;
    location?: string;
    image_url?: string;
    ticket_types?: TicketType[];
    user: User;
}

interface Order {
    id: string;
    order_number: string;
    status: 'pending' | 'completed' | 'canceled';
    items: OrderItem[];
    total_amount: string;
    created_at: string;
}

interface OrderItem {
    id: string;
    ticketType: TicketType;
    quantity: number;
    subtotal: string;
}

interface Ticket {
    id: string;
    unique_code: string;
    status: 'active' | 'pending' | 'redeemed' | 'canceled' | 'expired';
    order: Order;
    ticket_type: TicketType;
    redeemed_at?: string;
    orderItem?: OrderItem;
    created_at: string;
    updated_at: string;
}

interface TicketType {
    id: string;
    name: string;
    code: string;
    description?: string;
    price: string;
    available_quantity: number;
    event_id: string;
    event?: Event;
}

interface User {
    id: string;
    name: string;
    email: string;
    is_organizer: boolean;
}
