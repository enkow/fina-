<template>
	<WeekDayNumbers
		:label="capitalize($t('main.current-value')) + ' (' + $t('main.days') + ')'"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		name="reservation_min_advance_time"
		:with-border="withBorder">
		<template #icon>
			<ClockIcon />
		</template>

		<template #title>
			{{ $t('settings.reservation-min-advance-time') }}
		</template>

		<p class="block w-full" v-html="$t('settings.reservation-min-advance-description')"></p>
		<p class="mt-2 font-bold text-info-base">
			{{ $t('settings.reservation-min-advance-info') }}
		</p>
	</WeekDayNumbers>
</template>
<script lang="ts" setup>
import ClockIcon from '@/Components/Dashboard/Icons/ClockIcon.vue';
import WeekDayNumbers from '@/Components/Dashboard/Settings/Types/WeekDayNumbers.vue';
import { useString } from '@/Composables/useString';
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

const { capitalize } = useString();
</script>
