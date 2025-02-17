<script setup lang="ts">
import Card from '@/Components/Dashboard/Card.vue';
import Table from '@/Components/Dashboard/Table.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { computed, onMounted, ref } from 'vue';
import { wTrans } from 'laravel-vue-i18n';
import { useNumber } from '@/Composables/useNumber';
import { Invoice } from '@/Types/models';

//	'invoices-billing' => ['created_at', 'total_gross', 'duration', 'invoice'],

const props = defineProps<{
	invoices: [Invoice];
}>();

const invoicesHeaders = computed(() => {
	return {
		invoice: wTrans('invoice.singular').value,
		total_gross: wTrans('invoice.total').value,
		duration: wTrans('invoice.duration').value,
		created_at: wTrans('invoice.settlement-date').value,
	};
});

const customTablePreference = computed(() => {
	return ['created_at', 'total_gross', 'duration', 'invoice'].map((i) => ({
		key: i,
		enabled: true,
	}));
});

const { formatAmount } = useNumber();
</script>

<template>
	<Card>
		<Table
			table-classes="xl:overflow-x-hidden"
			:header="invoicesHeaders"
			:items="invoices"
			:customTablePreference="customTablePreference"
			:narrow="['duraction', 'total_gross']"
			:disabled="['actions']">
			<template #cell_duration="props">{{ props.item.from }} - {{ props.item.to }}</template>
			<template #cell_total_gross="props">
				{{ formatAmount(props.item.total_gross) }}
			</template>
			<template #cell_invoice="props">
				<a :href="route('club.invoices.export', { invoice: props.item.id })">
					{{ props.item.title }}
					<svg
						xmlns="http://www.w3.org/2000/svg"
						fill="none"
						viewBox="0 0 24 24"
						stroke-width="2"
						stroke="currentColor"
						class="h-3s ml-1 inline w-3">
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
					</svg>
				</a>
			</template>
		</Table>
	</Card>
</template>
