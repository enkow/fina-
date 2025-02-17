import { PaginatedResource } from '@/Types/responses';
import { ComputedRef } from 'vue';

export interface Agreement {
    id: number;
    type: number;
    club_id: number;
    club: Club;
    content_type: number;
    text: string | null;
    file: string | null;
    active: boolean;
    required: boolean;
    created_at: string;
}

export interface Game {
    id: number;
    name: string;
    description: string;
    icon: string;
    setting_icon_color: string;
    clubs_count?: number;
    created_at: string;
    photo?: string;

    pivot: GamePivot;
    features?: Feature[];
    slots?: Slot[];
    discountCodes?: DiscountCode[];
    specialOffers?: SpecialOffer[];
    translations?: Translation[];
}

export interface GamePivot {
    enabled_on_widget: number;
    include_on_invoice?: boolean;
    include_on_invoice_status: string;
    fee_percent: number;
    fee_fixed: number;
}

export interface Feature {
    id: number;
    type: string;
    code: string;
    data: any;
    game?: Game;
    reservations?: Reservation[];
    translations: {
        [key: string]: string;
    };
    pivot?: FeatureSlotPivot;
    created_at?: string;
}

export interface Slot {
    id: number;
    active: boolean;
    name: string;
    created_at?: string;
    pricelist_id: number;
    pricelist?: Pricelist;
    features: Feature[];
    pivot?: FeatureSlotPivot;
    reservations?: Reservation[];
    slot_id?: number | null;
    childrenSlots?: Slot[];
    parentSlot?: Slot;
    bulb_status?: number;
    expanded?: boolean;
}

export type SlotWithParent = { parent_slot_id: number | undefined } & Slot;

export interface FeatureSlotPivot {
    data?: string;
}

export interface Pricelist {
    id?: number;
    name?: string;
    created_at?: string;
    game?: Game;
    slots?: Slot[];
    pricelistItems?: PricelistItem[];
    pricelistExceptions?: PricelistException[];
}

export interface PricelistItem {
    id?: number;
    day: number;
    from: string;
    to: string;
    price: number;
    created_at?: string;
    pricelist?: Pricelist;
}

export interface PricelistException {
    id?: number;
    day: number;
    start_at: string;
    end_at: string;
    from: string;
    to: string;
    price: number;
    created_at?: string;
    pricelist?: Pricelist;
}

export interface DiscountCode {
    id: number;
    game_id: number;
    active: boolean;
    code: string;
    type: number;
    value: number;
    code_quantity?: number;
    code_quantity_per_customer?: number;
    created_at?: string;
    start_at?: string;
    end_at?: string;
    reservations_count: number;
    is_available?: boolean;
    club?: Club;
    game?: Game;
}

export interface Club {
    id?: number;
    name: string;
    slug: string;
    description: string;
    widget_enabled: boolean;
    widget_countries: Country[];
    calendar_enabled: boolean;
    aggregator_enabled: boolean;
    customer_registration_required: boolean;
    sets_enabled: boolean;
    created_at: string;
    email?: string;
    address?: string;
    postal_code?: string;
    city?: string;

    billing_name?: string;
    billing_address?: string;
    billing_nip?: string;
    billing_postal_code?: string;
    billing_city?: string;

    sms_notifications_online: boolean;
    sms_notifications_offline: boolean;

    subscription_active: boolean;

    phone_number?: string;
    vat_number?: string;
    invoice_emails?: string[];
    panel_enabled?: boolean;
    timer_enabled?: boolean;
    first_login_message?: string[];
    first_login_message_showed?: boolean;
    online_payments_enabled?: 'external' | 'internal' | 'disabled';
    openingHours?: OpeningHours[];
    openingHoursExceptions?: OpeningHoursException[];
    settings?: Setting[];
    country?: Country;
    slots?: Slot[];
    sets?: Set[];
    discountCodes?: DiscountCode[];
    discount_codes?: DiscountCode[];
    specialOffers?: SpecialOffer[];
    special_offers?: SpecialOffer[];
    announcements?: Announcement[];
    pricelists?: Pricelist[];
    games?: Game[];
    settlements?: Settlement[];
    users?: User[];
    customers?: Customer[];
    tags?: Tag[];
    reservationTypes?: ReservationType[];
    reservation_types?: ReservationType[];
    payment_methods?: PaymentMethod[];
    payment_method?: PaymentMethod;
    paymentMethod?: PaymentMethod;
    offline_payments_enabled?: boolean;
    reservations?: Reservation[];
    products: Product[];
    invoice_autosend: Boolean;
    invoice_advance_payment: Boolean;
    invoice_autopay: Boolean;
    invoice_last: Boolean;
    invoice_lang: string;
    invoice_next_year: String;
    invoice_payment_time: number;
    invoice_next_month: String;
    preview_mode: Boolean;
    invoices?: Invoice[];
    lastInvoice?: Invoice;
    vat: number;
    customer_verification_type: number;

    sms_count_offline_monthly: number;
    sms_count_online_monthly: number;
    sms_price_online: number;
    sms_price_offline: number;
}

export interface Invoice {
    id: number;
    club_id: number;
    items: InvoiceItem[];
    total: number;
    vat: number;
    currency: string;
    paid_at?: string;
    created_at: string;
    title: string;
    lang: string;
    from: Date;
    to: Date;
    stripe_payment_intent_id: number;
    fakturownia_id: number;
    fakturownia_token: string;
}

export interface InvoiceItem {
    id: number;
    invoice?: Invoice;
    model?: Game | Product;
    settings: any;
    details?: {
        offline?: InvoiceItemDetails;
        online?: {
            expired?: InvoiceItemDetails;
            online?: InvoiceItemDetails;
            club?: InvoiceItemDetails;
            providerCommission?: number;
        };
    };
    total: number;
    created_at: string;
}

interface InvoiceItemDetails {
    price: number | string;
    commission: number;
}

export interface ReservationType {
    id?: number;
    name?: string;
    color?: string;
    created_at?: string;
    club?: Club;
    reservations?: Reservation[];
}

export interface ClubProduct {
    id: number;
    name: string;
    period: string;
    cost: number;
    pivot: Product;
}

export interface Tag {
    id?: number;
    name?: string;
    customers?: Customer[];
}

export interface Customer {
    id?: number;
    encryptedId?: string;
    email?: string;
    online?: boolean;
    first_name?: string;
    last_name?: string;
    full_name?: string;
    phone?: string;
    verified?: boolean;
    created_at?: string;
    locale: string;
    club?: Club;
    reservations?: Reservation[];
    tags?: Tag[];
    offline_today_reservations_count: number;
    online_active_reservations_count: number;
    agreements_to_consent: Agreement[];
    sms_limit_reached?: boolean;
}

export interface User {
    id?: number;
    type?: string;
    first_name?: string;
    last_name?: string;
    email?: string;
    sidebar_reduced?: boolean;
    country_id?: number;
    created_at?: string;
    club?: Club;
}

export interface Settlement {
    id?: number;
    club?: Club;
    status?: number;
    created_at?: string;
}

export interface Announcement {
    type: number;
    content: string;
    content_top: string;
    content_bottom: string;
    game_id?: number;
    id?: number;
    club?: Club;
    start_at?: string;
    end_at?: string;
}

export interface Set {
    id: number;
    name: string;
    photo: string;
    mobile_photo: string;
    description: string;
    price: number;
    quantity: Array<number>;
    club?: Club;
    reservations?: Reservation[];
    selected?: number;
    available?: number;
    created_at?: string;
}

export interface SetModel {
    id: number;
    name: string;
    photo: string;
    description: string;
    price: number;
    quantity: Array<number>;
    club?: Club;
    reservations?: Reservation[];
    pivot?: {
        price: number;
    };
    created_at?: string;
}

export interface Country {
    id?: number;
    active?: boolean;
    code: string;
    currency: string;
    locale: string;
    timezone: string;
    clubs_count?: number;
    paymentMethod?: PaymentMethod;
    translations?: Translation[];
    helpSections?: HelpSection[];
    clubs?: Club[];
    dialing_code?: string;
}

export interface PaymentMethod {
    id: number;
    code: string;
    external_id: string;
    type: string;
    club?: Club;
    activated?: boolean;
    online: boolean;
    country?: Country;
    credentials?: Record<string, string>;
    reservations?: Reservation[];
    adminTab?: string;
    fee_percentage: number;
    fee_fixed: number;
}

export interface OpeningHours {
    id?: number;
    day: number;
    club_start: string;
    club_end: string;
    club_close: string;
    club_closed: boolean;
    open_to_last_customer: boolean;
    reservation_start: string;
    reservation_end: string;
    reservation_closed: boolean;
    created_at?: string;
    club?: Club;
}

export interface OpeningHoursException {
    id?: number;
    start_at: string;
    end_at: string;
    club_start: string;
    club_end: string;
    club_close: string;
    club_closed: boolean;
    open_to_last_customer: boolean;
    reservation_start: string;
    reservation_end: string;
    reservation_closed: boolean;
    created_at?: string;
    club?: Club;
}

export interface Reservation {
    additionalReservation: boolean;
    calendar_color: string;
    club_commission_partial: number;
    club_note: string;
    show_club_note_on_calendar: boolean;
    club_reservation: boolean;
    created_at: string;
    customer_name: string;
    customer_note: string;
    show_customer_note_on_calendar: boolean;
    customer_phone: string;
    display_name: string;
    end_datetime: string | null;
    end_datetime_raw: string | null;
    final_price: number;
    reservation_number_id: number | string;
    number: number | string;
    occupied_status: boolean;
    parent_slot_name: string;
    payment_method_online: boolean;
    reservation_time_range?: string;
    reservation_type_color: string;
    sets: Set[];
    slot_id: string;
    slot_name: string;
    slots_count: number;
    start_datetime: string;
    status: number;
    status_color: string;
    extended?: {
        cancelation_type: number | null;
        customer: Customer;
        customer_email: string;
        customer_presence: number;
        customer_reservations_count: number;
        customer_reservations_hours: number;
        customer_reservations_turnover: number;
        discountCode: DiscountCode | null;
        duration: number;
        features: Feature[];
        game: Game;
        online_status: boolean;
        price: number;
        final_price: number;
        relatedReservations: Reservation | null;
        reservationType?: ReservationType;
        reservation_slots_count?: number;
        slot: Slot;
        occupied_status: boolean;
        slotFeatures: Feature[];
        specialOffer: SpecialOffer | null;
        status: number;
        timer_enabled: boolean;
        total_paid: number;
        total_price: number;
    };
}

export interface Refund {
    id: number;
    club: Club;
    status: number;
    approver_id: number;
    approver: User;
    price: number;
    reservation_number: number;
    created_at: number;
}

export interface SpecialOffer {
    id: number;
    number: string;
    active: boolean;
    value: number;
    active_by_default: boolean;
    name: string;
    description: string;
    active_week_days: number[];
    duration: string;
    photo: string | null;
    time_range: {
        [time_range_type: string]: Array<{
            from: string;
            to: string;
        }>;
    };
    time_range_type: string;
    slots: number;
    when_applies: Array<{
        from: string;
        to: string;
    }>;
    applies_default: boolean;
    when_not_applies: Array<{
        from: string;
        to: string;
    }>;
    game?: Game;
    game_id?: number;
    game_name?: string;
    reservations?: Reservation[];
    club?: Club;
    creator?: User;
    created_at?: string;
}

export interface Translation {
    key: string;
    value: string;
    country?: Country;
    feature?: Feature;
    Game?: Game;
}

export interface DataChange {
    reservation?: Reservation;
    data?: Object;
    created_at?: string;
}

export interface HelpItem {
    id?: number;
    thumbnail: string;
    title: string;
    description: string;
    content: string;
    video_url: string;
    help_section_id: number;
    helpSection: HelpSection;
    helpItemImages: HelpItemImage[];
    created_at?: string;
}

export interface HelpSection {
    id?: number;
    title: string;
    description: string;
    weight?: number;
    created_at?: string;
    country?: Country;
    helpItems?: HelpItem[] | PaginatedResource<HelpItem>;
}

export interface HelpItemImage {
    id: number;
    path: string;
}

export interface ManagerEmail {
    id: number;
    game_id?: number;
    email: string;
}

//HELPERS

export interface SelectOption {
    code: string | number | boolean | null;
    label: string | ComputedRef<any>;
}

export interface Setting {
    translations: Record<string, string>;
    feature_id: number | null;
    feature_name?: string | null;
    value: string | boolean | number | Array<number> | null;
}

export interface Product {
    id: number;
    name_en: String;
    name_pl: String;
    system_label?: string;
    pivot: ClubProduct;
}
