<template>
	<TextValue
		:label="setting.translations['slots-quantity-default-setting-label']"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		name="person_as_slot_quantity_default">
		<template #icon>
			<HashIcon />
		</template>

		<template #title>{{ setting.translations['slots-quantity-default-setting-title'] }}</template>

		{{ setting.translations['slots-quantity-default-setting-description'] }}
	</TextValue>
</template>

<script lang="ts" setup>
import { useString } from '@/Composables/useString';
import TextValue from '@/Components/Dashboard/Settings/Types/TextValue.vue';
import { SettingEntity } from '@/Types/responses';
import HashIcon from '@/Components/Dashboard/Icons/HashIcon.vue';

const { capitalize } = useString();

const props = withDefaults(
	defineProps<{
		setting: SettingEntity;
		settingIconColor?: string;
	}>(),
	{
		settingIconColor: 'info',
	},
);
</script>
