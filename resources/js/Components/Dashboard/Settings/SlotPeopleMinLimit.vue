<template>
	<TextValue
		:label="setting.translations['min-setting-label']"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		:with-border="withBorder"
		name="slot_people_min_limit">
		<template #icon>
			<PeopleUnfilledIcon />
		</template>

		<template #title>{{ setting.feature.translations['min-setting-title'] }}</template>

		{{ setting.feature.translations['min-setting-description'] }}
	</TextValue>
</template>

<script lang="ts" setup>
import { useString } from '@/Composables/useString';
import TextValue from '@/Components/Dashboard/Settings/Types/TextValue.vue';
import { SettingEntity } from '@/Types/responses';
import PeopleUnfilledIcon from '@/Components/Dashboard/Icons/PeopleUnfilledIcon.vue';

const { capitalize } = useString();

const props = withDefaults(
	defineProps<{
		setting: SettingEntity;
		settingIconColor?: string;
		withBorder?: boolean;
	}>(),
	{
		settingIconColor: 'info',
	},
);
</script>
