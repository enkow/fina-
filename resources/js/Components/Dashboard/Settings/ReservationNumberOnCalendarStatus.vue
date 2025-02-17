<template>
	<Status
		:setting="setting"
		:setting-icon-color="settingIconColor"
		name="reservation_number_on_calendar_status">
		<template #icon>
			<HashIcon class="!h-5 !w-5" />
		</template>

		<template #title>
			{{ $t('settings.reservation-number-on-calendar-status') }}
		</template>

		{{ $t('settings.reservation-number-on-calendar-status-description') }}
	</Status>
</template>

<script lang="ts" setup>
import MessageIcon from '@/Components/Dashboard/Icons/MessageIcon.vue';
import Status from '@/Components/Dashboard/Settings/Types/Status.vue';
import { SettingEntity } from '@/Types/responses';
import PuzzleIcon from '@/Components/Dashboard/Icons/PuzzleIcon.vue';
import HashIcon from '@/Components/Dashboard/Icons/HashIcon.vue';

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
