<template>
	<div class="space-y-2">
		<div
			v-for="customer in customers"
			v-if="customers?.length ?? false"
			class="flex w-full cursor-pointer flex-wrap items-center justify-between px-2 py-1.5 text-xs hover:bg-brand-base hover:text-white"
			@click="$emit('fill', customer)">
			<div>{{ customer.phone ?? '' }}</div>
			<div class="min-w-10 pr-1">
				{{ `${customer.first_name ?? ''} ${customer.last_name ?? ''}` }}
			</div>
			<div class="mt-1 w-full">{{ customer.email ?? '' }}</div>
		</div>
		<div v-else class="px-2 py-1 text-xs">
			{{ $t('customer.no-results') }}
		</div>
	</div>
</template>

<script lang="ts" setup>
import { Customer } from '@/Types/models';

const props = defineProps<{
	customers: Customer[];
}>();
const emit = defineEmits<{
	(e: 'fill', reservation: Customer): void;
}>();
</script>
