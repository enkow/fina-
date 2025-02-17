<template>
	<Status :setting="setting" :setting-icon-color="settingIconColor" name="sms_notifications_status">
		<template #icon>
			<MessageIcon class="!h-5 !w-5" />
		</template>

		<template #title>
			{{ $t('settings.sms-notifications') }}
		</template>

		{{ $t('settings.sms-notifications-description') }}
	</Status>
</template>

<script lang="ts" setup>
import MessageIcon from '@/Components/Dashboard/Icons/MessageIcon.vue';
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
