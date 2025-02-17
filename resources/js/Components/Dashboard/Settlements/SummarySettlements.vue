<template>
	<div class="overflow-hidden bg-white shadow sm:rounded-lg lg:sticky lg:top-5">
		<div class="flex items-center justify-between px-6 py-5 text-base font-semibold">
			<p>{{ $t('settlement.summary') }}</p>
			<Button
				class="brand sm"
				type="a"
				:href="route('club.invoices.export', { invoice: invoice.id })"
				v-if="invoice.fakturownia_token"
				target="_blank">
				{{ $t('settlement.download-vat-invoice') }}
			</Button>
		</div>
		<hr />

		<div class="space-y-4 px-6 py-5 text-sm">
			<div v-for="(row, i) in summaryTable" class="flex justify-between" v-if="row !== null">
				<div class="font-light">{{ row.label }}</div>
				<div
					:class="{
						'text-danger-base': row.cost,
						'text-brand-base': !row.cost,
					}"
					class="font-bold">
					{{ formattedFloat(row.value / 100, 2) }} {{ currencySymbol }}
				</div>
			</div>
		</div>

		<hr />
		<div class="flex items-center justify-between px-6 py-5 text-base font-semibold">
			<p>{{ $t('settlement.sum') }}</p>
			<p
				class="font-bold"
				:class="{
					'text-danger-base': summary < 0,
					'text-brand-base': summary > 0,
				}">
				{{ formattedFloat(summary / 100, 2) }} {{ currencySymbol }}
			</p>
		</div>
	</div>
</template>

<script lang="ts" setup>
import { Club, Invoice, InvoiceItem, User } from '@/Types/models';
import { wTrans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import { useNumber } from '@/Composables/useNumber';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
const { formattedFloat } = useNumber();

const props = defineProps<{
	club: Club;
	invoice: Invoice;
	currencySymbol: string;
}>();

const onlinePaymentSettings = props.invoice?.items?.filter((item) => !item.settings.period)[0]?.settings
	.online_payment_method;

const summaryTable = computed(() => [
	{
		label: wTrans('settlement.reservation-turnover').value,
		value: summaryTurnover(),
	},
	{
		label: wTrans('settlement.reservation-fee', { status: 'online' }).value,
		value: summaryCommission() * (-1),
		cost: true,
	},
	...(onlinePaymentSettings
		? [
				{
					label: wTrans('settlement.provider-commission', { paymentProvider: onlinePaymentSettings.name })
						.value,
					value: summaryCommissionProvider(),
					cost: true,
				},
		  ]
		: []),

	...(props.invoice.items
		.filter((item) => item.settings.period)
		.map((item) => ({
			label: wTrans('settlement.fee-per-product', {
				product: item.model[`name_${props.club.invoice_lang || 'en'}`],
			}).value,
			value: -item.total,
			cost: true,
		})) || []),
]);

const turnoverSumItem = (item: InvoiceItem) => {
	const details = item.details;
	let amount = 0;
	amount += parseInt(`${details?.online?.club?.price || 0}`);
	amount += parseInt(`${details?.online?.online?.price || 0}`);
	amount += parseInt(`${details?.online?.expired?.price || 0}`);

	return amount;
};

const summaryTurnover = () => {
	let amount = 0;
	props.invoice.items.forEach((item) => {
		amount += turnoverSumItem(item);
	});

	return amount;
};

const summaryCommissionProvider = () => {
	let amount = 0;
	props.invoice.items
		.filter((item) => !item.settings.period)
		.forEach((item) => {
			amount -= parseInt(`${item.details?.online?.providerCommission || 0}`);
		});

	return amount;
};

const summaryCommission = () => {
	let amount = 0;

	props.invoice.items
		.filter((item) => !item.settings.period)
		.forEach(({ details }) => {
			amount += parseInt(`${details?.online?.club?.commission || 0}`);
			amount += parseInt(`${details?.online?.online?.commission || 0}`);
			amount += parseInt(`${details?.online?.expired?.commission || 0}`);
		});

	return amount;
};

const summary = computed(() => {
	let amount = 0;
	props.invoice.items.forEach((item) => {
		amount += turnoverSumItem(item);

		amount -= parseInt(`${item.total || 0}`);
	});

	return amount;
});
</script>
