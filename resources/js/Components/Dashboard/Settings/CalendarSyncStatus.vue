<template>
	<Status
		:customRoute="customRoute"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		name="calendar_sync_status">
		<template #icon>
			<ClockIcon class="!h-5 !w-5" />
		</template>

		<template #title>Synchronizacja kalendarza</template>
	</Status>
</template>

<script lang="ts" setup>
import Status from '@/Components/Dashboard/Settings/Types/Status.vue';
import { Club } from '@/Types/models';
import ClockIcon from '@/Components/Dashboard/Icons/ClockIcon.vue';
import { SettingEntity } from '@/Types/responses';

const props = withDefaults(
	defineProps<{
		setting: SettingEntity;
		club: Club;
		customRoute?: string;
		settingIconColor?: string;
	}>(),
	{
		settingIconColor: 'info',
	},
);
</script>
