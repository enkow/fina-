<template>
	<Status :setting="setting" :setting-icon-color="settingIconColor" name="email_notifications_status">
		<template #icon>
			<EnvelopeIcon class="!h-5 !w-5" />
		</template>

		<template #title>
			{{ $t('settings.email-notifications') }}
		</template>

		{{ $t('settings.email-notifications-description') }}
	</Status>
</template>

<script lang="ts" setup>
import EnvelopeIcon from '@/Components/Dashboard/Icons/EnvelopeIcon.vue';
import Status from '@/Components/Dashboard/Settings/Types/Status.vue';
import { SettingEntity } from '@/Types/responses';

const props = withDefaults(
	defineProps<{
		setting: SettingEntity;
		settingIconColor: string;
	}>(),
	{
		settingIconColor: 'info',
	},
);
</script>
