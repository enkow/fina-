<template>
	<TableColumnEditor
		:disabled="[
			'parent_slot_id',
			'reservation_number_id',
			'reservation_id',
			'reservation_slot_id',
			'status',
			'end_datetime',
			'slot_id',
			'created_at',
			'club_reservation',
			'club_note',
			'customer_note',
			'cancelation_type',
			'cancelation_status',
			'status',
			'display_name',
			'custom_display_name',
			'created_at',
			'payment_method_id',
			'payment_method_online',
			'calendar_color',
			'calendar_name',
			'customer_email',
			'show_customer_note_on_calendar',
			'show_club_note_on_calendar',
			'timer_status',
			disabledActions ? 'occupied_status' : ''
		]"
		:header="reservationTableHeadings"
		:items="reservations"
		:table-name-preference-postfix="game.id.toString()"
		class="!hidden md:!block"
		table-name="reservations" />
</template>

<script lang="ts" setup>
import TableColumnEditor from '@/Components/Dashboard/TableColumnEditor.vue';
import { Game, Reservation } from '@/Types/models';
import { PaginatedResource } from '@/Types/responses';

const props = withDefaults(
    defineProps<{
      reservations: PaginatedResource<Reservation>;
      reservationTableHeadings: Record<string, string>;
      game: Game;
      disabledActions?: boolean
    }>(),
    {
      disabledActions: false
    }
);

interface ColumnType {
	key: string;
	name?: string;
	enabled: boolean;
}
</script>
