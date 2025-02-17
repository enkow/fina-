import { Feature, Reservation } from '@/Types/models';

export interface PaginationLink {
	url: string;
	label: string;
	active: boolean;
}

export interface Paginated<T> {
	current_page: number;
	data: T[];
	first_page_url: string;
	from: number;
	last_page: number;
	last_page_url: string;
	next_page_url: string;
	path: string;
	per_page: number;
	prev_page_url: string;
	to: number;
	total: number;
	links: PaginationLink[];
}

export interface PaginatedResource<T> {
	data: T[];
	links: {
		first?: string;
		last?: string;
		prev?: string;
		next?: string;
	};
	meta: {
		current_page: number;
		from: number;
		last_page: number;
		links: PaginationLink[];
		path: string;
		per_page: number;
		to: number;
		total: number;
	};
}

export interface SettingEntity {
	value: string | Array<number> | null | boolean | number;
	translations: Record<string, string> | null;
	feature: Feature | null;
}

interface ReservationSearchResults {
	confirmed: Reservation[];
	canceled: Reservation[];
}

export interface ClubSetting {
	feature: Feature;
	translations: Record<string, string>;
	value: any;
}

export interface DateOpeningHours {
	club_start: string;
	club_end: string;
	club_closed: boolean;
	open_to_last_customer: boolean;
	reservation_start: string;
	reservation_end: string;
	reservation_closed: boolean;
}

export interface ReservationStoredNotification {
	reservationData: {
		creator_id: number;
		game_name: string;
		number: string;
		start_at: string;
		customer_name: string | null;
		source: number;
	};
}
