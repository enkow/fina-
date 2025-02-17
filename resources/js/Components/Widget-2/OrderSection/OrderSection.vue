<template>
	<section>
		<div class="space-y-2.5 md:space-y-5 md:rounded-2xl md:bg-ui-green-100 md:p-8">
			<div :class="WRAPPER_STYLES">
				<OrderSectionDiscountCodeInput />
				<OrderSectionSlotSelect />
				<OrderSectionSlotHasLoungeSelect />
			</div>
			<div :class="WRAPPER_STYLES">
				<Widget2Input :placeholder="$t('widget-2.message')" :max-length="200" fill />
			</div>
		</div>
		<OrderSectionPaymentMethods />
	</section>
</template>

<script lang="ts" setup>
import Widget2Input from '../Widget2Input.vue';
import OrderSectionDiscountCodeInput from './OrderSectionDiscountCodeInput.vue';
import OrderSectionPaymentMethods from './OrderSectionPaymentMethods.vue';
import OrderSectionSlotSelect from './OrderSectionSlotSelect.vue';
import OrderSectionSlotHasLoungeSelect from './OrderSectionSlotHasLoungeSelect.vue';

const WRAPPER_STYLES = 'flex flex-col gap-y-2.5 md:flex-row md:gap-x-4.5';
</script>
