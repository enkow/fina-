<script setup lang="ts">
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import TabHistory from '@/Pages/Club/Billing/Tabs/History.vue';
import TabData from '@/Pages/Club/Billing/Tabs/Data.vue';
import { computed, ref, Ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Club, Invoice } from '@/Types/models';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import Card from '@/Components/Dashboard/Card.vue';
import { useReservations } from '@/Composables/useReservations';
const { currencySymbols } = useReservations();

const props = defineProps<{
	user: {
		club: Club;
	};
	activePaymentMethod: string;
	paymentMethods: {
		id: string;
		type: 'card' | 'paypal';
		card?: {
			brand: string;
			last4: string;
			exp_month: number;
			exp_year: number;
		};
		paypal?: {
			email: string;
		};
	}[];
	hasBillingDetails: boolean;
	subscriptionDetails: {
		price: {
			year: number;
			month: number;
		};
		active: boolean;
		currency: string;
		nextInvoice: {
			date: string;
			from: string;
			price: number;
			type: string;
		};
		currentInvoice: {
			date: string;
			from: string;
			price: number;
			type: string;
		};
	};
	invoices: [Invoice];
	lastPaymentStatus: boolean;
}>();

const subscriptionForm = useForm({});
const cancelSubscription = () => {
	subscriptionForm.delete(route('club.billing.subscription.cancel'));
};

const activateSubscription = () => {
	subscriptionForm.post(route('club.billing.subscription.activate'));
};
const currentTab: Ref<number> = ref(1);

const commission = computed(() => {
	return !!props.user?.club?.games?.filter(
		(game) =>
			game.pivot.include_on_invoice &&
			game.pivot.include_on_invoice_status !== null &&
			JSON.parse(game.pivot.include_on_invoice_status).length,
	).length;
});

const infoBox = computed(() => {
	if ((!props.subscriptionDetails.active || props.activePaymentMethod === null) && props.user.club.billing_name === null) {
		return 'billing.error.data';
	} else if(!props.user.club.subscription_active && props.activePaymentMethod === null && props.user.club.billing_name !== null) {
    return 'billing.error.payment-method-activate';
  } else if(props.user.club.subscription_active && props.activePaymentMethod === null && props.user.club.billing_name !== null) {
    return 'billing.error.payment-method';
  }
  else if (!props.user.club.subscription_active) {
		return 'billing.error.active';
	} else if (!props.lastPaymentStatus) {
		return 'billing.error.payment';
	}
});
</script>

<template>
	<PanelLayout :breadcrumbs="[{ href: '#', label: $t('billing.singular') }]">
		<ContentContainer>
			<div class="col-span-12 space-y-4 xl:col-span-12">
				<div class="flex justify-between">
					<Card class="!w-1/2">
						<template #header>
							<h3 class="my-0 grow text-lg font-bold">
								{{ $t('billing.current') }}
							</h3>
						</template>
						<p class="my-2 font-medium">
							{{ $t('billing.subscription-name') }}

							<span
								v-if="$props.user.club.subscription_active && lastPaymentStatus"
								v-text="$t('billing.subscription-active')"
								class="ml-2 rounded-md bg-emerald-100 px-2 py-1 text-xs font-normal" />
							<span
								v-else
								v-text="$t('billing.subscription-deactive')"
								class="ml-2 rounded-md bg-rose-300 px-2 py-1 text-xs font-normal" />
						</p>

						<p
							class="mt-3"
							v-if="(subscriptionDetails.active && lastPaymentStatus) || ($props.user.club.subscription_active && lastPaymentStatus) || !lastPaymentStatus">
							{{ $t('billing.period') }}:
							{{ subscriptionDetails.currentInvoice.from }}
							-
							{{ subscriptionDetails.currentInvoice.date }}
						</p>

						<p class="m-0 mt-1 font-medium">
							{{ $t('billing.amount') }}:
							{{ (subscriptionDetails.nextInvoice?.price / 100).toString() ?? '0' }}
							{{ currencySymbols[subscriptionDetails.currency] }}
							{{
								subscriptionDetails.nextInvoice?.type === 'month' && commission
									? $t('billing.next-invoice-commission')
									: ''
							}}
						</p>
					</Card>
					<div>
						<button
							class="block !h-min rounded-md border-2 border-brand-base !px-3 !py-2 text-sm text-brand-base hover:bg-brand-base hover:text-white"
							v-if="!lastPaymentStatus"
							@click="activateSubscription">
							{{ $t('billing.button.subscription-renew') }}
						</button>

						<button
							class="block !h-min rounded-md border-2 border-red-600 !px-3 !py-2 text-sm text-red-600 hover:bg-red-600 hover:text-white"
							v-else-if="$props.user.club.subscription_active"
							@click="cancelSubscription">
							{{ $t('billing.button.subscription-cancel') }}
						</button>

						<button
							class="block !h-min rounded-md border-2 border-brand-base !px-3 !py-2 text-sm text-brand-base hover:bg-brand-base hover:text-white"
							v-else-if="subscriptionDetails.active && activePaymentMethod !== null"
							@click="activateSubscription">
							{{ $t('billing.button.subscription-activate') }}
						</button>
					</div>
				</div>
				<Card v-if="infoBox" class="bg-red-200 !py-3 text-center font-medium">
					<span class="text-lg text-red-700" v-text="$t(infoBox)" />
				</Card>
				<div class="text-center text-sm font-medium text-gray-500 dark:border-gray-700 dark:text-gray-400">
					<ul
						class="flex flex-wrap border-b border-gray-200 text-center text-sm font-medium text-gray-500 dark:text-gray-400">
						<li
							v-for="(item, index) in {
								1: $t('billing.billing-data'),
								2: $t('billing.invoice-history'),
							}"
							class="w-1/2">
							<p
								:class="{
									active_tab: currentTab === parseInt(index),
								}"
								class="inline-block w-full cursor-pointer rounded-t-lg border-b border-transparent p-4 hover:border-gray-300 hover:text-gray-600 dark:hover:text-gray-500"
								@click="currentTab = parseInt(index)">
								{{ item }}
							</p>
						</li>
					</ul>
				</div>

				<TabData v-bind="props" v-if="currentTab === 1" />
				<TabHistory v-bind="props" v-if="currentTab === 2" />
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<style scoped>
.active_tab {
	color: rgb(27 197 189 / var(--tw-bg-opacity));
	border-color: rgb(27 197 189 / var(--tw-bg-opacity));
}
</style>
