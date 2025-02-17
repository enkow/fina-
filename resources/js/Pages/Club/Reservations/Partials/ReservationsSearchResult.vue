<template>
	<div>
		<div
			v-for="reservation in reservations"
			v-if="reservations.length"
			class="flex w-full cursor-pointer px-2 py-1.5 text-xs hover:bg-brand-base hover:text-white"
			@click="$emit('open', reservation.reservation_number_id)">
			<div class="min-w-10 pr-1">
				{{ reservation.number }}
			</div>
			<div v-if="reservation.customer_name && reservation.customer_name.length > 1">
				- {{ reservation.customer_name }}
			</div>
		</div>
		<div v-else class="px-2 py-1 text-xs">
			{{ $t('reservation.no-results') }}
		</div>
	</div>
</template>

<script lang="ts" setup>
import { Reservation } from '@/Types/models';

const props = defineProps<{
	reservations: Reservation[];
}>();
const emit = defineEmits<{
	(e: 'open', reservation: Reservation): void;
}>();
</script>
