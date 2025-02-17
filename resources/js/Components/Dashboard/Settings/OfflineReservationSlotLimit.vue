<template>
	<WeekDayNumbers
		:custom-route="customRoute"
		:scope="scope"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		name="offline_reservation_slot_limit"
		:with-border="withBorder">
		<template #icon>
			<HashIcon />
		</template>

		<template #title>
			<div v-if="customTitle">
				{{ customTitle }}
			</div>
			<div v-else>
				{{ setting.feature.translations['offline-reservation-slot-limit-setting-title'] }}
			</div>
		</template>
		{{ setting.feature.translations['offline-reservation-slot-limit-setting-description'] }}
	</WeekDayNumbers>
</template>
<script lang="ts" setup>
import WeekDayNumbers from '@/Components/Dashboard/Settings/Types/WeekDayNumbers.vue';
import { SettingEntity } from '@/Types/responses';
import HashIcon from '@/Components/Dashboard/Icons/HashIcon.vue';

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
