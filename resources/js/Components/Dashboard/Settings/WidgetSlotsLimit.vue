<template>
	<WeekDayNumbers
		:label="setting.translations['setting-label']"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		name="widget_slots_limit"
		:placeholder="$t('slot.all')"
		:with-border="withBorder">
		<template #icon>
			<HashIcon />
		</template>

		<template #title>{{ setting.feature.translations['setting-title'] }}</template>

		{{ setting.feature.translations['setting-description'] }}
	</WeekDayNumbers>
</template>
<script lang="ts" setup>
import HashIcon from '@/Components/Dashboard/Icons/HashIcon.vue';
import WeekDayNumbers from '@/Components/Dashboard/Settings/Types/WeekDayNumbers.vue';
import { SettingEntity } from '@/Types/responses';
import { useString } from '@/Composables/useString';

const props = withDefaults(
	defineProps<{
		setting: SettingEntity;
		scope?: string;
		customRoute?: string;
		settingIconColor?: string;
		withBorder?: boolean;
	}>(),
	{
		scope: 'club',
		settingIconColor: 'info',
	},
);
const { capitalize } = useString();
</script>
