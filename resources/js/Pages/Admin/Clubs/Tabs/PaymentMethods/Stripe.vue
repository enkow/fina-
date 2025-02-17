<template>
	<div class="flex flex-col gap-5">
		<PaymentMethodFee :club="club" :paymentMethod="paymentMethod" />
	</div>
</template>

<script lang="ts" setup>
import { useString } from '@/Composables/useString';
import { Club, PaymentMethod } from '@/Types/models';
import PaymentMethodFee from '@/Components/Dashboard/Settings/PaymentMethodFee.vue';

const { capitalize } = useString();

const props = defineProps<{
	club: Club;
}>();

const paymentMethod: PaymentMethod = props.club.paymentMethod as PaymentMethod;
</script>
