<template>
	<Status
		:setting="setting"
		:setting-icon-color="settingIconColor"
		name="full_hour_start_reservations_status"
		:with-border="withBorder">
		<template #icon>
			<StopwatchIcon class="!h-5 !w-5" />
		</template>

		<template #title>
			{{ $t('settings.full-hour-start-reservations-status') }}
		</template>

		<div v-html="$t('settings.full-hour-start-reservations-status-description')" />
	</Status>
</template>

<script lang="ts" setup>
import StopwatchIcon from '@/Components/Dashboard/Icons/StopwatchIcon.vue';
import Status from '@/Components/Dashboard/Settings/Types/Status.vue';
import { SettingEntity } from '@/Types/responses';

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
