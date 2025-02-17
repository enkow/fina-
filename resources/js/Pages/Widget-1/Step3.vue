<template>
	<CalendarStepper />
	<div class="flex flex-col gap-10 lg:flex-row">
		<CalendarBooking />
		<article :class="twMerge('w-full rounded-10 bg-ui-green-400 px-6 py-9 lg:p-10 space-y-6')">
			<template v-if="registrationRequired && !isAuth">
				<template v-if="widgetStore.customer?.verified === false">
					<CalendarSmsVerification v-if="widgetStore.club.customer_verification_type == 1" />
					<CalendarPendingVerification v-else />
				</template>
				<CalendarAuth v-else />
			</template>
			<CalendarCustomerNote ></CalendarCustomerNote>
			<CalendarNotRequiredRegistrationForm
				v-if="!registrationRequired && !notRequiredSubmitted"
				@submit="notRequiredSubmitted = true" />
			<CalendarPaymentMethod v-if="registrationRequired ? isAuth : notRequiredSubmitted" />
		</article>
	</div>
	<CalendarNavigation />
</template>

<script lang="ts" setup>
import CalendarStepper from '@/Components/Widget-1/Shared/CalendarStepper/CalendarStepper.vue';
import CalendarBooking from '@/Components/Widget-1/Step3/CalendarBooking/CalendarBooking.vue';
import CalendarAuth from '@/Components/Widget-1/Step3/CalendarAuth/CalendarAuth.vue';
import CalendarPaymentMethod from '@/Components/Widget-1/Step3/CalendarPaymentMethod.vue';
import CalendarNavigation from '@/Components/Widget-1/Shared/CalendarNavigation.vue';
import CalendarPendingVerification from '@/Components/Widget-1/Step3/CalendarPendingEmailVerification.vue';
import CalendarNotRequiredRegistrationForm from '@/Components/Widget-1/Step3/CalendarNotRequiredRegistrationForm.vue';
import { useAuth } from '@/Composables/useAuth';
import { useWidgetStore } from '@/Stores/widget';
import { ref, computed } from 'vue';
import { twMerge } from 'tailwind-merge';
import CalendarSmsVerification from '@/Components/Widget-1/Step3/CalendarSmsVerification.vue';
import CalendarCustomerNote from '@/Components/Widget-1/Step3/CalendarCustomerNote.vue';

const widgetStore = useWidgetStore();
const { isAuth } = useAuth();

const notRequiredSubmitted = ref(false);
const registrationRequired = computed(() => widgetStore.club.customer_registration_required);

widgetStore.logoutUnverifiedUser();
</script>
