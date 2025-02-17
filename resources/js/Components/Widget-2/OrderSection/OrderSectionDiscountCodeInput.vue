<template>
	<Widget2InputWrapper
		v-bind="{
			...(widgetStore.discountCodeStatus === false && { error: $t('widget-2.incorrect-code') }),
			...(widgetStore.discountCodeStatus === true && { success: $t('widget-2.correct-code') }),
		}"
		v-slot="{ error }"
		fill>
		<Widget2Input
			v-model="widgetStore.form.discount_code"
			:placeholder="$t('widget-2.coupon-code')"
			:error="error" />
	</Widget2InputWrapper>
</template>

<script lang="ts" setup>
import Widget2InputWrapper from '../Widget2InputWrapper.vue';
import Widget2Input from '../Widget2Input.vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>
