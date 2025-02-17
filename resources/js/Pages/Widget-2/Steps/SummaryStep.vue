<template>
	<OrderSummarySection />
	<div
		class="widget-2-scrollbar flex h-full flex-1 flex-col overflow-auto md:h-fit md:justify-center md:overflow-visible md:!pr-0"
		:class="{ 'md:mt-7': selectedSets.length }">
		<OrderSection
			v-if="widgetStore.club.customer_registration_required ? isAuth : isNotRequiredAuthSubmitted" />
		<template v-if="widgetStore.club.customer_registration_required && !isAuth">
			<template v-if="widgetStore.customer?.verified === false">
				<SmsVerification v-if="widgetStore.club.customer_verification_type === 1" />
				<PendingEmailVerification v-else />
			</template>
			<AuthSection v-else />
		</template>
		<NotRequiredAuthSection
			v-if="!widgetStore.club.customer_registration_required && !isNotRequiredAuthSubmitted"
			@submit="isNotRequiredAuthSubmitted = true" />
	</div>
</template>

<script lang="ts" setup>
import OrderSummarySection from '@/Components/Widget-2/OrderSummarySection/OrderSummarySection.vue';
import AuthSection from '@/Components/Widget-2/AuthSection/AuthSection.vue';
import NotRequiredAuthSection from '@/Components/Widget-2/NotRequiredAuthSection.vue';
import OrderSection from '@/Components/Widget-2/OrderSection/OrderSection.vue';
import { useAuth } from '@/Composables/useAuth';
import { useWidgetStore } from '@/Stores/widget';
import { useWidgetSelectedSets } from '@/Composables/useWidgetSelectedSets';
import { ref } from 'vue';
import SmsVerification from '@/Components/Widget-2/SmsVerification.vue';
import PendingEmailVerification from '@/Components/Widget-2/PendingEmailVerification.vue';

const { isAuth } = useAuth();
const { selectedSets } = useWidgetSelectedSets();
const widgetStore = useWidgetStore();
const isNotRequiredAuthSubmitted = ref(false);
</script>
