<template>
	<CalendarPendingSmsVerification
		v-if="pendingSmsVerification"
		@change-phone-number="changePhoneNumber(false)" />
	<CalendarChangePhoneNumber v-else @change-phone-number="changePhoneNumber(true)" />
</template>

<script setup lang="ts">
import { ref } from 'vue';
import CalendarPendingSmsVerification from './CalendarPendingSmsVerification.vue';
import CalendarChangePhoneNumber from './CalendarChangePhoneNumber.vue';

const pendingSmsVerification = ref(true);

const changePhoneNumber = (val: boolean) => {
	pendingSmsVerification.value = val;
};
</script>
