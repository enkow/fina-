<template>
    <CalendarSectionTitle>
		{{ $t('calendar.message-title') }}
        <span class=" font-normal">({{ $t('calendar.optional') }})</span>
	</CalendarSectionTitle>

    <WidgetInputWrapper>
		<WidgetTextarea
            v-model="widgetStore.form.customer_note" 
            color="white"
			type="email"
			:placeholder="$t('calendar.message-placeholder')"
            maxlength="160"
        />
	</WidgetInputWrapper>
</template>

<script lang="ts" setup>
import CalendarSectionTitle from './CalendarSectionTitle.vue';
import WidgetTextarea from '@/Components/Widget/Ui/WidgetTextarea.vue';
import WidgetInputWrapper from '@/Components/Widget/Ui/WidgetInputWrapper.vue';
import { useWidgetStore } from '@/Stores/widget';

const widgetStore = useWidgetStore();
</script>