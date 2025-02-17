import { Game, Reservation, SlotWithParent } from '@/Types/models';
import dayjs from 'dayjs';
import objectSupport from 'dayjs/plugin/objectSupport';
import { PaginationOptions } from 'swiper/types';
import customParseFormat from 'dayjs/plugin/customParseFormat';

dayjs.extend(objectSupport);
dayjs.extend(customParseFormat);

export function ucFirst(string: string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

export const emptyGame: Game = {
    id: 0,
    name: '',
    icon: '',
    setting_icon_color: '',
    created_at: '',
};

export const emptyReservation: Reservation = {
    calendar_color: '#000',
    club_note: '',
    created_at: dayjs().format('YYYY-MM-DD HH:mm'),
    customer_name: '',
    customer_note: '',
    customer_phone: '',
    end_datetime: dayjs().format('YYYY-MM-DD HH:mm'),
    number: 0,
    parent_slot_name: '',
    reservation_time_range: '',
    reservation_type_color: '',
    sets: [],
    slot_name: '',
    slots_count: 0,
    start_datetime: dayjs().format('YYYY-MM-DD HH:mm'),
    status_color: '',
    extended: {
        cancelation_type: null,
        customer: null,
        customer_email: '',
        customer_presence: 0,
        customer_reservations_count: 0,
        customer_reservations_hours: 0,
        customer_reservations_turnover: 0,
        discountCode: null,
        duration: 120,
        features: [],
        final_price: 0,
        game: emptyGame,
        online_status: false,
        price: 0,
        relatedReservations: null,
        reservation_slots_count: 0,
        reservationType: undefined,
        slot: {
            active: false,
            features: [],
            id: 0,
            name: '',
        },
        slotFeatures: [],
        specialOffer: null,
        status: 0,
        timer_enabled: false,
        total_paid: 0,
        total_price: 0,
    },
};

export const generateTimePickerDates = (
    { date, start, end }: { date: string; start: string; end: string },
    duration: number,
) => {
    const TIME_TEMPLATE = 'HH:mm:ss';

    const days = [dayjs(`${date} ${start}`)];

    while (
        (end !== '23:59:00' ||
            !['23:00:00', '23:30:00'].includes(
                days.at(-1)?.format(TIME_TEMPLATE) || '',
            )) &&
        days.at(-1)?.format(TIME_TEMPLATE) !== end
    ) {
        const day = days.at(-1);

        if (day) {
            days.push(day.add(duration, 'm'));
        }
    }

    return (end === '23:59:00' ? days : days.slice(0, -1)).map((day) =>
        day.toDate(),
    );
};

export const createSwiperPagination = (): PaginationOptions => ({
    clickable: true,
    renderBullet: (_index, className) => `<button class=${className}></button>`,
});

export const createStepUrl = (step: number) => {
    const params = route().params as unknown;

    if (
        !(
            !!params &&
            typeof params === 'object' &&
            'club' in params &&
            typeof params.club === 'string'
        )
    ) {
        return '#';
    }

    return route('widget.calendar', { step, club: params.club });
};

export const isInputElement = (element: unknown): element is HTMLInputElement =>
    element instanceof HTMLInputElement;

export const formatPrice = (price: number) => {
    const finalPrice = price / 100;

    if (finalPrice % 1 === 0) {
        return finalPrice.toString();
    } else {
        return finalPrice.toFixed(2);
    }
};

export const formatCalendarDate = (date: Date | string) =>
    dayjs(date).format('YYYY-MM-DD');

export const formatCalendarDateWithTime = (date: Date | string) =>
    dayjs(date).format('YYYY-MM-DD HH:mm:ss');

export const formatCalendarOrderDate = (date: string, locale: string) =>
    dayjs(date).toDate().toLocaleDateString(locale, {
        month: 'short',
        weekday: 'short',
        day: 'numeric',
    });

export const formatCalendarOrderTime = (start: string, duration: number) => {
    const TEMPLATE = 'HH:mm';

    const startDay = dayjs(start);
    const endDay = startDay.clone().add(duration, 'm');

    return { from: startDay.format(TEMPLATE), to: endDay.format(TEMPLATE) };
};

export const hasTime = (value: string) => /\b\d{2}:\d{2}:\d{2}\b/.test(value);
export const removeTime = (value: string) => value.replace(/\b\d{2}:\d{2}:\d{2}\b/, '');

export const formatSelectedSlots = (
    slots: SlotWithParent[],
    locale: string,
) => {
    return slots
        .map((slot) => {
            const convenienceSlot = slot.features.find(
                (feature) => feature.code === 'slot_has_convenience',
            );

            if (
                convenienceSlot &&
                JSON.parse(convenienceSlot?.pivot?.data as string)?.status
            ) {
                return `#${slot.name} (${convenienceSlot.translations[locale]['slot-with-convenience']})`;
            }

            if (slot.parent_slot_id) {
            }

            return `#${slot.name}`;
        })
        .join(', ');
};

export const generateCarouselDates = ({
    startAt,
    limit,
}: {
    startAt: string;
    limit: number;
}) => {
    const dates: Date[] = [];

    const start = dayjs(startAt);
    const stop = start.clone().add(limit, 'day');

    const startDate = start.toDate();
    const stopDate = stop.toDate();

    while (startDate <= stopDate) {
        dates.push(new Date(startDate));
        startDate.setDate(startDate.getDate() + 1);
    }

    return dates;
};

export const createSignOutButtonLabel = ({
    color,
    email,
    text,
}: {
    color: string;
    email: string;
    text: string;
}) =>
    [
        email,
        `<hr style='border-color: ${color}; margin-top: 3px; margin-bottom: 3px;'>`,
        "<p style='text-align: center'>",
        text,
        '</p>',
    ].join('');
