<template>
	<AnnouncementContent
		:setting="setting"
		:setting-icon-color="settingIconColor"
		name="widget_message"
		:with-border="withBorder">
		<template #icon>
			<MessageIcon />
		</template>

		<template #title>
			{{ $t('settings.widget-message') }}
		</template>

		{{ $t('settings.widget-message-description') }}
	</AnnouncementContent>
</template>

<script lang="ts" setup>
import MessageIcon from '@/Components/Dashboard/Icons/MessageIcon.vue';
import AnnouncementContent from '@/Components/Dashboard/Settings/Types/AnnouncementContent.vue';
import { SettingEntity } from '@/Types/responses';

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
