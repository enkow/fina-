<template>
    <OrderSettingsListItem v-if="active">
        <SectionLabel :for="INPUT_ID">{{ label }}</SectionLabel>
        <WidgetNumberInput
            :id="INPUT_ID"
            :min="min"
            :max="max"
            v-model="model.person_count"
            compact
        />
    </OrderSettingsListItem>
</template>

<script lang="ts" setup>
import OrderSettingsListItem from './OrderSettingsListItem.vue';
import SectionLabel from '../SectionLabel.vue';
import WidgetNumberInput from '@/Components/Widget/Ui/WidgetNumberInput.vue';
import { useWidgetPricePerPersonSetting } from '@/Composables/widget/useWidgetPricePerPersonSetting';

const { active, label, min, max, model } = useWidgetPricePerPersonSetting();

const INPUT_ID = 'price-per-person-setting-input';
</script>
