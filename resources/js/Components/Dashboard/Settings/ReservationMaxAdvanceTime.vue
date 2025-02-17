<template>
	<TextValue
		:label="capitalize($t('main.current-value')) + ' (' + $t('main.days') + ')'"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		name="reservation_max_advance_time"
		:with-border="withBorder">
		<template #icon>
			<OvenIcon />
		</template>

		<template #title>
			{{ $t('settings.reservation-max-advance-time') }}
		</template>

		<template #info>
			{{ $t('settings.reservation-max-advance-time-info') }}
		</template>

		{{ $t('settings.reservation-max-advance-time-description') }}
	</TextValue>
</template>

<script lang="ts" setup>
import OvenIcon from '@/Components/Dashboard/Icons/OvenIcon.vue';
import { useString } from '@/Composables/useString';
import TextValue from '@/Components/Dashboard/Settings/Types/TextValue.vue';
import { SettingEntity } from '@/Types/responses';

const { capitalize } = useString();

withDefaults(
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
