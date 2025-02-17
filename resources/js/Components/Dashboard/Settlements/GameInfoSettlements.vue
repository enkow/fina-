<template>
	<div class="overflow-hidden bg-white font-light shadow sm:rounded-lg">
		<div class="flex items-center justify-between px-6 py-5 text-base font-semibold">
			<p>{{ usePage().props.gameTranslations[game.model.id]['game-name'] }}</p>
			<div class="flex space-x-2">
				<Button class="info sm" type="link" :href="urlReservations">
					{{ $t('settlement.show-reservations') }}
				</Button>
				<Button class="info sm" type="button" @click="() => (exportType = 'csv')">
					{{ $t('main.action.download-xls') }}
				</Button>
			</div>
		</div>
		<hr />
		<SettlementTab v-for="info in gameInfos">
			<template #title>
				<p>{{ info.label }}</p>
			</template>
			<template #amount>
				<p class="font-bold">
					{{ formattedFloat(info.items.reduce((acc, item) => acc + parseInt(`${item.amount}`), 0) / 100, 2) }}
					{{ currencySymbol }}
				</p>
			</template>
			<div v-for="row in info.items" class="flex justify-between py-2 text-sm">
				<div class="font-light">{{ row.label }}</div>
				<div class="font-bold">
					{{ formattedFloat(parseInt(`${row.amount}`) / 100, 2) }} {{ currencySymbol }}
				</div>
			</div>
		</SettlementTab>
	</div>
</template>

<script lang="ts" setup>
import SettlementTab from '@/Components/Dashboard/SettlementTab.vue';
import { wTrans } from 'laravel-vue-i18n';
import { InvoiceItem, Invoice } from '@/Types/models';
import { usePage } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { computed } from 'vue';
import { useNumber } from '@/Composables/useNumber';
import { useExport } from '@/Composables/useExport';
const { formattedFloat } = useNumber();

const props = defineProps<{
	invoice: Invoice;
	currencySymbol: string;
	game: InvoiceItem;
	urlReservations: string;
	routeExport: {
		name: string;
		options: {
			settlement: number;
			game?: number;
			club?: number;
		};
	};
}>();

const exportType = useExport(props.routeExport.name, props.routeExport.options);

const gameInfos = computed(() => [
	{
		label: wTrans('settlement.reservation-turnover').value,
		items: [
			{
				label: wTrans('settlement.reservation-turnover-paid', {
					paymentType: wTrans('settlement.paymentType.online').value,
				}).value,
				amount: props.game.details.online.online.price,
			},
			{
				label: wTrans('settlement.reservation-turnover-paid', {
					paymentType: wTrans('settlement.paymentType.club').value,
				}).value,
				amount: props.game.details.online.club.price,
			},
			{
				label: wTrans('settlement.reservation-turnover-unpaid').value,
				amount: props.game.details.online.expired.price,
			},
		],
	},
	{
		label: wTrans('settlement.reservation-fee', { status: 'online' }).value,
		items: [
			{
				label: wTrans('settlement.reservation-fee-paid', {
					paymentType: wTrans('settlement.paymentType.online').value,
				}).value,
				amount: props.game.details.online.online.commission,
			},
			{
				label: wTrans('settlement.reservation-fee-paid', {
					paymentType: wTrans('settlement.paymentType.club').value,
				}).value,
				amount: props.game.details.online.club.commission,
			},
			{
				label: wTrans('settlement.reservation-fee-unpaid').value,
				amount: props.game.details.online.expired.commission,
			},
		],
	},
]);
</script>
