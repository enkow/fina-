<template>
	<PendingSmsVerification
		v-if="pendingSmsVerification"
		@reload-customer="reloadCustomerData()"
		@change-phone-number="changePhoneNumber(false)" />
	<ChangePhoneNumber
		v-else
		@reload-customer="reloadCustomerData()"
		@change-phone-number="changePhoneNumber(true)" />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import PendingSmsVerification from './PendingSmsVerification.vue';
import ChangePhoneNumber from './ChangePhoneNumber.vue';

const pendingSmsVerification = ref(true);
const emit = defineEmits<{
	(e: 'reloadCustomer'): void;
}>();

const reloadCustomerData = () => {
	emit('reloadCustomer');
};

const changePhoneNumber = (val: boolean) => {
	pendingSmsVerification.value = val;
};
</script>
