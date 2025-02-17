<script setup lang="ts">
import Card from '@/Components/Dashboard/Card.vue';
import CheckmarkIcon from '@/Components/Widget-3/Icons/CheckmarkIcon.vue';
import { ref } from 'vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { Club } from '@/Types/models';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import Radio from '@/Components/Dashboard/Radio.vue';
import XIcon from '@/Components/Dashboard/Icons/XIcon.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import { useSelectOptions } from '@/Composables/useSelectOptions';

const { countryOptions } = useSelectOptions();

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
}>();

const { billing_name, billing_address, billing_nip, billing_postal_code, billing_city, invoice_lang, country } =
	props.user.club;

const detailsForm = useForm({
	billing_name,
	billing_address,
	billing_nip,
	billing_postal_code,
	billing_city,
	invoice_lang,
	country_id: country?.id || 1,
});
const saveDetails = () => {
	detailsForm.post(route('club.billing.update-details'), {
		onSuccess: () => {
			editBillingDetails.value = false;
		},
	});
};
const editBillingDetails = ref(false);

const selectMethodForm = useForm({});
const selectMethod = (id: string) => {
	selectMethodForm.transform((data: any) => ({ id })).post(route('club.billing.select-method'));
};

const removeMethodForm = useForm({});
const removeMethod = (id: string) => {
	removeMethodForm.transform((data: any) => ({ id })).delete(route('club.billing.remove-method'));
};
</script>

<template>
	<Card>
		<template #header>
			<div class="flex items-center space-x-6">
				<div
					:class="{
						'border-2 border-brand-base font-semibold text-brand-base': $props.hasBillingDetails,
						'border border-gray-400 text-gray-400': !$props.hasBillingDetails,
					}"
					class="grid h-8 w-12 place-items-center rounded-full text-xl">
					1
				</div>
				<h3 class="my-0 grow text-2xl">
					{{
						$t(
							hasBillingDetails && !editBillingDetails
								? 'billing.your-billing-details'
								: 'billing.enter-billing-details',
						)
					}}
				</h3>
				<div v-if="$props.hasBillingDetails">
					<CheckmarkIcon class="h-6 w-6 text-brand-base" />
				</div>
			</div>
		</template>
		<form
			v-if="!$props.hasBillingDetails || editBillingDetails"
			class="mt-8 flex flex-col"
			@submit.prevent="saveDetails">
			<div class="space-y-3">
				<div class="flex items-start space-x-2">
					<div class="w-1/2 space-y-1">
						<InputLabel class="capitalize">{{ $t('validation.attributes.billing_name') }}</InputLabel>
						<TextInput v-model="detailsForm.billing_name" />
						<p v-if="detailsForm.errors.billing_name" class="error">
							{{ detailsForm.errors.billing_name }}
						</p>
					</div>
					<div class="w-1/2 space-y-1">
						<InputLabel class="capitalize">{{ $t('validation.attributes.vat_number') }}</InputLabel>
            <TextInput v-model="detailsForm.billing_nip" />
            <p v-if="detailsForm.errors.billing_nip" class="error">
              {{ detailsForm.errors.billing_nip }}
            </p>
					</div>
				</div>
				<div class="space-y-1">
					<InputLabel class="capitalize">{{ $t('validation.attributes.billing_address') }}</InputLabel>
					<TextInput v-model="detailsForm.billing_address" />
					<p v-if="detailsForm.errors.billing_address" class="error">
						{{ detailsForm.errors.billing_address }}
					</p>
				</div>
				<div class="flex items-start space-x-2">
					<div class="w-1/2 space-y-1">
						<InputLabel class="capitalize">{{ $t('validation.attributes.postal_code') }}</InputLabel>
						<TextInput v-model="detailsForm.billing_postal_code" />
						<p v-if="detailsForm.errors.billing_postal_code" class="error">
							{{ detailsForm.errors.billing_postal_code }}
						</p>
					</div>
					<div class="w-1/2 space-y-1">
						<InputLabel class="capitalize">{{ $t('validation.attributes.billing_city') }}</InputLabel>
						<TextInput v-model="detailsForm.billing_city" />
						<p v-if="detailsForm.errors.billing_city" class="error">
							{{ detailsForm.errors.billing_city }}
						</p>
					</div>
				</div>
				<div class="flex items-start space-x-2">
					<div class="w-1/2 space-y-1">
						<InputLabel class="capitalize">{{ $t('validation.attributes.country') }}</InputLabel>
						<SimpleSelect v-model="detailsForm.country_id" :options="countryOptions" />
						<p v-if="detailsForm.errors.country_id" class="error">
							{{ detailsForm.errors.country_id }}
						</p>
					</div>
					<div class="w-1/2 space-y-1">
						<InputLabel class="capitalize">{{ $t('validation.attributes.billing_lang') }}</InputLabel>
						<SimpleSelect
							v-model="detailsForm.invoice_lang"
							:options="[
								{ code: 'pl', label: 'Polski' },
								{ code: 'en', label: 'English' },
							]"
							class="self-stretch" />
						<p v-if="detailsForm.errors.invoice_lang" class="error">
							{{ detailsForm.errors.invoice_lang }}
						</p>
					</div>
				</div>
			</div>
			<Button type="submit" :disabled="detailsForm.processing" class="ml-auto mt-6 capitalize">
				{{ $t('main.action.save') }}
			</Button>
		</form>
		<div v-else class="mt-6 text-base leading-tight">
      <p>{{ $props.user.club.billing_name }}</p>
			<p>{{ $props.user.club.billing_address }}</p>
			<p>{{ $props.user.club.billing_postal_code }} {{ $props.user.club.billing_city }}</p>
			<p>{{ $t('country.' + $props.user.club.country.code) }}</p>
      <p v-if="$props.user.club.billing_nip?.length">VAT: {{ $props.user.club.billing_nip }}</p>
			<p>
				{{ $t('validation.attributes.billing_lang') }}:
				{{ $props.user.club.invoice_lang === 'pl' ? 'Polski' : 'English' }}
			</p>
			<button
				class="mt-1 text-sm capitalize text-brand-base transition-colors duration-150 ease-in-out hover:text-opacity-60"
				@click="editBillingDetails = !!1">
				{{ $t('main.action.edit') }}
			</button>
		</div>
	</Card>
	<Card>
		<template #header>
			<div class="flex items-center space-x-6">
				<div
					:class="{
						'border-2 border-brand-base font-semibold text-brand-base': $props.activePaymentMethod,
						'border border-gray-400 text-gray-400': !$props.activePaymentMethod,
					}"
					class="grid h-8 w-12 place-items-center rounded-full text-xl">
					2
				</div>
				<h3 class="my-0 grow text-2xl" :class="{ 'text-gray-4': !$props.hasBillingDetails }">
					{{ $props.activePaymentMethod ? $t('billing.payment-methods') : $t('billing.add-payment-method') }}
				</h3>
				<div v-if="$props.hasBillingDetails">
					<Button
						type="a"
						v-if="$props.paymentMethods.length < 2"
						:href="route('club.billing.add-method')"
						class="!h-auto !px-5 !py-2.5">
						{{ $t('billing.add-method') }}
					</Button>
				</div>
			</div>
		</template>
		<div v-if="$props.hasBillingDetails && $props.paymentMethods.length" class="mt-6 flex flex-col space-y-4">
			<div
				v-for="method in $props.paymentMethods"
				class="flex w-full items-center rounded-lg border border-gray-2/50 bg-gray-1/50 px-5 py-2 shadow-sm">
				<Radio
					name="activePaymentMethod"
					:value="method.id"
					@click="selectMethod(method.id)"
					:disabled="selectMethodForm.processing"
					:checked="method.id === $props.activePaymentMethod" />

				<div class="mx-6 flex w-16">
					<img v-if="method.card?.brand === 'amex'" src="@as/images/cards/amex.png" alt="" />
					<img v-else-if="method.card?.brand === 'diners'" src="@as/images/cards/diners.png" alt="" />
					<img v-else-if="method.card?.brand === 'discover'" src="@as/images/cards/discover.png" alt="" />
					<img v-else-if="method.card?.brand === 'jcb'" src="@as/images/cards/jcb.png" alt="" />
					<img v-else-if="method.card?.brand === 'mastercard'" src="@as/images/cards/mastercard.png" alt="" />
					<img v-else-if="method.card?.brand === 'visa'" src="@as/images/cards/visa.png" alt="" />
					<img v-else-if="method.type === 'paypal'" src="@as/images/cards/paypal.png" alt="" />
					<img v-else src="@as/images/cards/unknown.png" alt="" />
				</div>

				<div class="flex font-mono font-medium text-gray-5" v-if="method.type === 'card'">
					<div v-for="i in 3" class="mb-0.5 mr-2 flex items-center text-gray-4">
						<svg
							v-for="j in 4"
							xmlns="http://www.w3.org/2000/svg"
							viewBox="0 0 20 20"
							fill="currentColor"
							class="h-2 w-2">
							<path
								fill-rule="evenodd"
								d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
								clip-rule="evenodd" />
						</svg>
					</div>
					<span>{{ method.card?.last4 }}</span>
					<span class="ml-6">{{ method.card?.exp_month }}/{{ method.card?.exp_year }}</span>
				</div>
				<div v-else-if="method.type === 'paypal'" class="text-gray-5">
					{{ method.paypal.email }}
				</div>

				<button
					@click="removeMethod(method.id)"
					:disabled="removeMethodForm.processing"
					class="transition-color ml-auto text-gray-3 duration-150 hover:text-gray-4 active:text-gray-2 disabled:text-gray-2">
					<XIcon class="h-4 w-4" />
				</button>
			</div>
		</div>
	</Card>
</template>

<style scoped></style>
