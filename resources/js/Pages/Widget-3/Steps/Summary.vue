<template>
	<Widget3Layout :with-reservation-details="true">
		<div v-if="widgetStore.reservationData">
			<!-- Reservation has been made -->
			<Status></Status>
		</div>
		<div
			v-else-if="
				askedNonRegisteredCustomerForDataStatus === false &&
				widgetStore.club.customer_registration_required === false
			">
			<!-- Reservation is not required, we ask the customer for basic booking details -->
			<NotRegisteredData @submit="askedNonRegisteredCustomerForDataStatus = true" />
		</div>
		<div v-else-if="widgetStore.club.customer_registration_required === true && forgotPasswordPanelShowing">
			<ForgotPassword @close="forgotPasswordPanelShowing = false" />
		</div>
		<div
			v-else-if="
				widgetStore.club.customer_registration_required === true &&
				widgetStore.customer !== null &&
				(widgetStore.customer.agreements_to_consent?.length ?? 0) > 0
			">
			<Consents @reload-customer="reloadCustomerDataTriggerer++" />
		</div>
		<div
			v-else-if="
				widgetStore.club.customer_registration_required === true &&
				widgetStore.customer !== null &&
				widgetStore.customer.verified === false
			">
			<SmsVerification
				@reload-customer="reloadCustomerDataTriggerer++"
				v-if="widgetStore.club.customer_verification_type == 1" />
			<PendingVerification :channel="channel" @reload-customer="reloadCustomerDataTriggerer++" v-else />
		</div>
		<div
			v-else-if="
				(widgetStore.customer !== null && widgetStore.customer.verified === true) ||
				widgetStore.club.customer_registration_required === false
			">
			<div class="flex-between mb-0 mt-7 flex flex-wrap sm:mb-8">
				<div class="h-8 w-6/12 pr-3 sm:w-[30%] sm:pr-4">
					<TextInput
						class="hidden xs:block"
						v-model="widgetStore.form.discount_code"
						:placeholder="$t('widget.enter-the-discount-code')" />
					<TextInput
						class="block xs:hidden"
						v-model="widgetStore.form.discount_code"
						:placeholder="$t('widget.enter-the-discount-code-short')" />
					<div class="mt-1 hidden text-xs sm:block">
						<template v-if="widgetStore.discountCodeStatus === true">
							<p v-if="widgetStore.discountCode?.is_available" class="font-semibold text-brand-dark">
								{{ $t('widget.discount-code-valid') }}
							</p>
							<p v-else class="font-semibold text-danger-dark">
								{{ $t('widget.discount-code-invalid') }}
							</p>
						</template>
						<p v-else-if="widgetStore.discountCodeStatus === false" class="font-semibold text-danger-dark">
							{{ $t('widget.discount-code-invalid') }}
						</p>
					</div>
				</div>
				<div v-if="widgetStore.showingStatuses['slotHasLounge']" class="block w-6/12 sm:hidden">
					<SlotHasLoungeFilter />
				</div>
				<div v-else-if="widgetStore.showingStatuses['slotSelection']" class="block w-6/12 sm:hidden">
					<SlotIdsFilter />
				</div>
				<div
					:class="{
						'w-full sm:w-[38%] sm:pr-4':
							widgetStore.showingStatuses['slotHasLounge'] || widgetStore.showingStatuses['slotSelection'],
						'w-6/12 sm:w-[70%]':
							!widgetStore.showingStatuses['slotHasLounge'] && !widgetStore.showingStatuses['slotSelection'],
					}">
					<TextInput
						v-model="widgetStore.form.customer_note"
						:placeholder="$t('widget.write-a-message-to-the-club')"
						maxlength="160" />
				</div>
				<div v-if="widgetStore.showingStatuses['slotHasLounge']" class="hidden w-full sm:block sm:w-[32%]">
					<SlotHasLoungeFilter />
				</div>
				<div
					v-else-if="widgetStore.showingStatuses['slotSelection']"
					class="hidden w-full sm:block sm:w-[32%]">
					<SlotIdsFilter />
				</div>
			</div>
			<div class="mb-3 mt-1 block text-xs sm:hidden">
				<p v-if="widgetStore.discountCodeStatus === true" class="font-semibold text-brand-dark">
					{{ $t('widget.discount-code-valid') }}
				</p>
				<p v-else-if="widgetStore.discountCodeStatus === false" class="font-semibold text-danger-dark">
					{{ $t('widget.discount-code-invalid') }}
				</p>
				<p v-else class="font-semibold text-danger-dark">
					{{ '⠀' }}
				</p>
			</div>
			<div v-if="widgetStore.hasSetMap()" class="flex w-full space-x-7.5">
				<div class="mb-3 flex w-2/3 cursor-pointer items-center space-x-1 md:w-1/2">
					<p class="text-widget block flex text-xs font-bold uppercase" @click="widgetStore.showMap()">
						{{ $t('widget.see-club-map') }}
					</p>
				</div>
			</div>
			<div class="flex w-full justify-end gap-x-3 md:gap-x-7.5">
				<div class="w-1/2">
					<div v-if="widgetStore.club.offline_payments_enabled || widgetStore.finalPrice === 0">
						<div v-if="offlinePaymentAvailable">
							<Button class="pay-button w-full !text-center" @click="widgetStore.store('offline')">
								<div v-if="widgetStore.finalPrice === 0">
									<span class="block w-full text-center">{{ $t('widget.book-long') }}</span>
								</div>
								<div v-else>
									<span class="block hidden w-full text-center md:block">
										{{ $t('widget.book-and-pay-offline') }}
									</span>
									<span class="block block w-full text-center md:hidden">{{ $t('widget.pay-offline') }}</span>
								</div>
							</Button>
						</div>
						<div v-else>
							<Button
								class="disabled pay-button w-full !text-center"
								v-tippy="{ allowHTML: true }"
								:content="`<p style=\'font-size:11px\'>${offlineDisabledMessage}</p>`">
								<div v-if="widgetStore.finalPrice === 0">
									<span class="block w-full text-center">{{ $t('widget.book-long') }}</span>
								</div>
								<div v-else>
									<span class="block hidden w-full text-center md:block">
										{{ $t('widget.book-and-pay-offline') }}
									</span>
									<span class="block block w-full text-center md:hidden">{{ $t('widget.pay-offline') }}</span>
								</div>
							</Button>
						</div>
					</div>
				</div>
				<div
					v-if="
						(widgetStore.club.online_payments_enabled === 'internal' ||
							(widgetStore.club.online_payments_enabled === 'external' &&
								widgetStore.club.paymentMethod !== null &&
								widgetStore.club.paymentMethod.activated === true)) &&
						widgetStore.finalPrice > 0
					"
					class="w-1/2">
					<div v-if="!isOnlinePaymentsDisabled">
						<Button class="pay-button w-full !text-center text-sm" @click="widgetStore.store('online')">
							<span class="hidden w-full text-center md:block">{{ $t('widget.book-and-pay-online') }}</span>
							<span class="block w-full text-center md:hidden">{{ $t('widget.pay-online') }}</span>
						</Button>
					</div>
					<div v-else>
						<Button
							class="disabled pay-button w-full !text-center text-sm"
							v-tippy="{ allowHTML: true }"
							:content="
								'<p style=\'font-size:11px\'>' +
								onlinePaymentsDisabledMessage +
								'</p>'
							"
							@click="widgetStore.store('online')">
							<span class="hidden w-full text-center md:block">{{ $t('widget.book-and-pay-online') }}</span>
							<span class="block w-full text-center md:hidden">{{ $t('widget.pay-online') }}</span>
						</Button>
					</div>
				</div>
			</div>
			<div
				v-if="
					(widgetStore.club.online_payments_enabled === 'internal' ||
						(widgetStore.club.online_payments_enabled === 'external' &&
							widgetStore.club.paymentMethod !== null &&
							widgetStore.club.paymentMethod.activated === true)) &&
					!isOnlinePaymentsDisabled &&
					widgetStore.finalPrice > 0
				"
				class="text-widget mt-2 w-full space-y-1 text-right text-xxs sm:mt-3 sm:space-y-4 sm:text-xs">
				<p v-html="$t('widget.complete-time-limit', { minutes: '5' })"></p>
				<p
					v-if="
						!isOnlinePaymentsDisabled &&
						(widgetStore.settings['additional_commission_fixed']['value'] > 0 ||
							widgetStore.settings['additional_commission_percent']['value'] > 0)
					"
					class="uppercase">
					{{
						$t('widget.handling-fee-info-fixed', {
							fixed: commisionPrice,
						})
					}}
				</p>
			</div>
		</div>
		<div v-else>
			<div
				class="login-container h-[315px] w-full overflow-y-hidden rounded-md border border-0 md:h-[300px] md:overflow-x-hidden md:border-[3px]">
				<div class="block w-full sm:grid sm:min-w-[500px] sm:grid-cols-2">
					<Register
						@show-login="mobileAuthView = 'login'"
						class="h-[300px]"
						:class="{ 'hidden sm:flex': mobileAuthView === 'login' }" />
					<Login
						@show-register="mobileAuthView = 'register'"
						@forgot-password="forgotPasswordPanelShowing = true"
						:class="{ 'hidden sm:flex': mobileAuthView === 'register' }" />
				</div>
			</div>
		</div>
	</Widget3Layout>
</template>

<style scoped>
.login-container {
	border-color: v-bind(widgetColor);
}

.pay-button {
	@apply text-xs xxs:text-sm sm:text-base;
}
</style>

<script lang="ts" setup>
import Widget3Layout from '@/Layouts/Widget3Layout.vue';
import TextInput from '@/Components/Widget-3/TextInput.vue';
import Button from '@/Components/Widget-3/Button.vue';
import { useWidgetStore } from '@/Stores/widget';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';
import { Customer } from '@/Types/models';
import Pusher from 'pusher-js';
import ForgotPassword from '@/Pages/Widget-3/Partials/ForgotPassword.vue';
import SmsVerification from '@/Pages/Widget-3/Partials/SmsVerification.vue';
import PendingVerification from '@/Pages/Widget-3/Partials/PendingEmailVerification.vue';
import Register from '@/Pages/Widget-3/Partials/Register.vue';
import Login from '@/Pages/Widget-3/Partials/Login.vue';
import { useNumber } from '@/Composables/useNumber';
import Consents from '@/Pages/Widget-3/Partials/Consents.vue';
import SlotHasLoungeFilter from '@/Pages/Widget-3/Filters/SlotHasLoungeFilter.vue';
import SlotIdsFilter from '@/Pages/Widget-3/Filters/SlotIdsFilter.vue';
import NotRegisteredData from '@/Pages/Widget-3/Partials/NotRegisteredData.vue';
import Status from '@/Pages/Widget-3/Partials/Status.vue';
import {refDebounced, watchDebounced} from "@vueuse/core";
import {wTrans} from "laravel-vue-i18n";
import { useWidgetAdditionalComission } from '@/Composables/widget/useWidgetAdditionalComission';
import { useWidgetOfflinePaymentAvaliable } from '@/Composables/widget/useWidgetOfflinePaymentAvaliable';

const widgetStore = useWidgetStore();
const widgetColor: string = widgetStore.widgetColor;
const { commisionPrice } = useWidgetAdditionalComission();
const { offlinePaymentAvailable, offlineDisabledMessage } = useWidgetOfflinePaymentAvaliable();

const mobileAuthView = ref<string>('login');
const isOnlinePaymentsDisabled = computed<boolean>(() => {
  // stripe cannot process payments under 2 {currency}
  if(widgetStore.club.paymentMethod?.type === 'stripe' && widgetStore.finalPrice < 200) {
    return true;
  }

	return widgetStore.customerOnlineActiveReservationsCount >= 3;
});
const onlinePaymentsDisabledMessage = computed<boolean>(() => {
  // stripe cannot process payments under 2 {currency}
  console.log(widgetStore.club.paymentMethod?.type, widgetStore.finalPrice, widgetStore.customerOnlineActiveReservationsCount);
  if(widgetStore.club.paymentMethod?.type === 'stripe' && widgetStore.finalPrice < 200) {
    return wTrans('widget.stripe-amount-too-low').value;
  }

  return wTrans('widget.online-reservation-limit-exceeded', {
    count: widgetStore.customerOnlineActiveReservationsCount.toString(),
  }).value;
});
const askedNonRegisteredCustomerForDataStatus = ref<boolean>(false);
const forgotPasswordPanelShowing = ref<boolean>(false);

const reloadCustomerDataTriggerer = ref<number>(1);
const reloadCustomerDataTriggererDebounced = refDebounced<number>(reloadCustomerDataTriggerer, 2000);
const encryptedId = ref<string | null>(null);
watchDebounced(
    reloadCustomerDataTriggerer,
    () => { reloadCustomerData(encryptedId.value) },
    { debounce: 2000, maxWait: 5000 },
)


function reloadCustomerData(encryptedId: string | null = null): void {
	let encryptedCustomerId = encryptedId ?? widgetStore.customer.encryptedId;
	if (encryptedCustomerId) {
		axios
			.get(
				route('widget.customers.show', {
					club: widgetStore.club,
					encryptedCustomerId: encryptedCustomerId,
				}),
			)
			.then((response: { data: { customer: Customer } }) => {
				widgetStore.customer = response.data.customer;
				forgotPasswordPanelShowing.value = false;
			});
	}
}
let intervalId: number | undefined;
onMounted(() => {
	widgetStore.channel.bind('customer-logged', function (data: { customer: Customer; channel: string }) {
    encryptedId.value = data.customer.encryptedId ?? null;
    reloadCustomerDataTriggerer.value++;
	});
	if (widgetStore.customer) {
    reloadCustomerDataTriggerer.value++;
	}
	intervalId = setInterval(() => {
		if (widgetStore.customer) {
      reloadCustomerDataTriggerer.value++;
		}
	}, 60000) as unknown as number;
});

onUnmounted(() => {
	widgetStore.channel.unbind('customer-logged');
	widgetStore.channel.unbind('customer-verified');
	if (intervalId !== undefined) {
		clearInterval(intervalId);
	}
});
</script>
