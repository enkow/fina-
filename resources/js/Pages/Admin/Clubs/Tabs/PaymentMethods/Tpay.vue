<template>
	<div class="flex flex-col gap-5">
		<form
			v-if="club.online_payments_enabled === 'external'"
			class="grid grid-cols-6 gap-x-4 gap-y-5"
			@submit.prevent="
				dataForm.post(route('admin.clubs.payment-method-connect', { club: club, payment_method: 'tpay' }), {
					preserveScroll: true,
					preserveState: true,
				})
			">
			<div class="input-group col-span-9 md:col-span-3">
				<InputLabel value="Merchant secret" required />
				<TextInput v-model="dataForm.merchant_secret" />
				<div v-if="dataForm.errors.merchant_secret" class="error">
					{{ dataForm.errors.merchant_secret }}
				</div>
			</div>

			<div class="input-group col-span-9 md:col-span-3">
				<InputLabel value="Merchant ID" required />
				<TextInput v-model="dataForm.merchant_id" />
				<div v-if="dataForm.errors.merchant_id" class="error">
					{{ dataForm.errors.merchant_id }}
				</div>
			</div>

			<div class="input-group col-span-9 md:col-span-3">
				<InputLabel value="Confirmation code" required />
				<TextInput v-model="dataForm.confirmation_code" />
				<div v-if="dataForm.errors.confirmation_code" class="error">
					{{ dataForm.errors.confirmation_code }}
				</div>
			</div>

			<div class="input-group col-span-12 flex items-end justify-end md:col-span-3">
				<Button class="w-full" type="submit" v-if="!club.paymentMethod.activated">
					{{ capitalize($t('main.action.connect')) }}
				</Button>
				<Button
					class="danger w-full"
					type="button"
					@click="
						useForm({}).delete(
							route('admin.clubs.payment-method-disconnect', { club: club, payment_method: 'tpay' }),
						)
					"
					v-else>
					Rozłącz
				</Button>
			</div>
		</form>

		<PaymentMethodFee :club="club" :paymentMethod="paymentMethod" />
	</div>
</template>

<script lang="ts" setup>
import { useString } from '@/Composables/useString';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { Club, PaymentMethod } from '@/Types/models';
import PaymentMethodFee from '@/Components/Dashboard/Settings/PaymentMethodFee.vue';

const { capitalize } = useString();

const props = defineProps<{
	club: Club;
}>();

const paymentMethod: PaymentMethod = props.club.paymentMethod as PaymentMethod;

const dataForm = useForm({
	merchant_secret: paymentMethod.credentials?.merchant_secret,
	merchant_id: paymentMethod.credentials?.merchant_id,
	confirmation_code: paymentMethod.credentials?.confirmation_code,
});
</script>
