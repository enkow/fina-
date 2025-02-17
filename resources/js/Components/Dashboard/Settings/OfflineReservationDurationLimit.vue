<template>
	<WeekDayNumbers
		:custom-route="customRoute"
		:scope="scope"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		:with-border="withBorder"
		name="offline_reservation_duration_limit">
		<template #icon>
			<HourglassIcon />
		</template>

		<template #title>
			<div v-if="customTitle">
				{{ customTitle }}
			</div>
			<div v-else>
				{{ setting.feature.translations['offline-reservation-duration-limit-setting-title'] }}
			</div>
		</template>
		{{ setting.feature.translations['offline-reservation-duration-limit-setting-description'] }}
	</WeekDayNumbers>
</template>
<script lang="ts" setup>
import HourglassIcon from '@/Components/Dashboard/Icons/HourglassIcon.vue';
import WeekDayNumbers from '@/Components/Dashboard/Settings/Types/WeekDayNumbers.vue';
import { SettingEntity } from '@/Types/responses';

const props = withDefaults(
	defineProps<{
		setting: SettingEntity;
		scope?: string;
		customRoute?: string;
		customTitle?: string;
		settingIconColor?: string;
		withBorder?: boolean;
	}>(),
	{
		scope: 'club',
		settingIconColor: 'info',
	},
);
</script>
