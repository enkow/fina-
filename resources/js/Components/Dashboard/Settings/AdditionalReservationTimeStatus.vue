<template>
	<Status :setting="setting" :setting-icon-color="settingIconColor" name="additional_club_reservation_status">
		<template #icon>
			<svg fill="none" height="18" viewBox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M9 16.5C13.1421 16.5 16.5 13.1421 16.5 9C16.5 4.85786 13.1421 1.5 9 1.5C4.85786 1.5 1.5 4.85786 1.5 9C1.5 13.1421 4.85786 16.5 9 16.5Z"
					stroke="#1BC5BD"
					stroke-width="1.6" />
				<path d="M3.75 14.25L14.25 3.75" stroke="#1BC5BD" stroke-width="1.6" />
			</svg>
		</template>

		<template #title>
			{{ $t('settings.additional-reservation-time-status') }}
		</template>

		{{ $t('settings.additional-reservation-time-status-description') }}
	</Status>
</template>
<script lang="ts" setup>
import Status from '@/Components/Dashboard/Settings/Types/Status.vue';
import { SettingEntity } from '@/Types/responses';

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
