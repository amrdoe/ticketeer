export interface CartItem {
    name: string;
    code: string;
    ticket_type_id: string;
    unit_price: number;
    quantity: number;
    event_id: string;
}

export interface Event {
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

export interface TicketTypeInput {
    id?: string; // Optional for updates
    __id?: string; // Frontend temp ID
    name: string;
    code: string;
    price: number;
    total_quantity: number;
    description?: string | null;
}

export interface EventInput {
    title?: string;
    description?: string;
    start_date?: string | null;
    end_date?: string | null;
    sale_start?: string | null;
    sale_end?: string | null;
    location?: string | null;
    image_url?: string | null;
    ticket_types?: TicketTypeInput[];
}

export interface Order {
    id: string;
    order_number: string;
    status: 'pending' | 'completed' | 'canceled';
    items: OrderItem[];
    total_amount: string;
    created_at: string;
}

export interface OrderItem {
    id: string;
    ticketType: TicketType;
    quantity: number;
    subtotal: string;
}

export interface Ticket {
    id: string;
    unique_code: string;
    status: 'active' | 'pending' | 'redeemed' | 'canceled' | 'expired';
    order: Order;
    ticketType: TicketType;
    redeemed_at?: string;
    orderItem?: OrderItem;
    created_at: string;
    updated_at: string;
}

export interface TicketType {
    id: string;
    name: string;
    code: string;
    description?: string;
    price: number; 
    total_quantity: number;
    available_quantity: number;
    event_id: string;
    event?: Event;
}

export interface User {
    id: string;
    name: string;
    email: string;
    is_organizer: boolean;
}
