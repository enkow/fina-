<script setup lang="ts">
import PanelLayout from '@/Layouts/PanelLayout.vue';
import Card from '@/Components/Dashboard/Card.vue';
import Table from '@/Components/Dashboard/Table.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { Invoice } from '@/Types/models';
import { computed, onMounted, ref } from 'vue';
import { PaginatedResource } from '@/Types/responses';
import dayjs from 'dayjs';
import { wTrans } from 'laravel-vue-i18n';
import { useNumber } from '@/Composables/useNumber';

const invoicesHeaders = computed(() => {
	return {
		title: wTrans('invoice.singular').value,
		total_gross: wTrans('invoice.total').value,
		created_at: wTrans('invoice.settlement-date').value,
	};
});
const { formatAmount } = useNumber();
const props = defineProps<{
	invoices: PaginatedResource<Invoice>;
}>();

const fakturowniaUrl = ref<string>('');
onMounted(() => {
	fakturowniaUrl.value = import.meta.env.VITE_FAKTUROWNIA_URL;
});
</script>

<template>
	<PanelLayout :breadcrumbs="[{ href: '#', label: $t('invoice.plural') }]">
		<div class="flex w-full flex-wrap space-y-5 px-10 py-8">
			<div class="flex w-full items-center justify-between">
				<Card>
					<Table
						:header="invoicesHeaders"
						:items="invoices"
						table-name="invoices"
						:narrow="['actions', 'total_net', 'total_gross']">
						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									target="_blank"
									:href="route('club.invoices.export', { invoice: props.item.id })"
									class="info xs uppercase"
									type="a">
									<svg
										xmlns="http://www.w3.org/2000/svg"
										fill="none"
										viewBox="0 0 24 24"
										stroke-width="2"
										stroke="currentColor"
										class="h-5 w-5">
										<path
											stroke-linecap="round"
											stroke-linejoin="round"
											d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
									</svg>
								</Button>
							</div>
						</template>
						<template #cell_created_at="props">
							{{ props.item.created_at }}
						</template>
						<template #cell_total_net="props">
							{{ formatAmount(props.item.total_net) }}
						</template>
						<template #cell_total_gross="props">
							{{ formatAmount(props.item.total_gross) }}
						</template>
					</Table>
				</Card>
			</div>
		</div>
	</PanelLayout>
</template>
