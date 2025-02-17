<template>
	<File
		:pathPrepend="pathPrepend"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		:placeholder="$t('settings.drop-map-here')"
		:with-border="withBorder"
		class="icon-brand"
		name="club_map">
		<template #icon>
			<MapIcon />
		</template>

		<template #title>
			{{ $t('settings.club-map') }}
		</template>

		<template #noFileAlert>
			{{ $t('settings.club-map-no-file-alert') }}
		</template>

		<p class="block w-full">
			{{ $t('settings.club-map-description') }}
		</p>
	</File>
</template>
<script lang="ts" setup>
import File from '@/Components/Dashboard/Settings/Types/File.vue';
import MapIcon from '@/Components/Dashboard/Icons/MapIcon.vue';
import { SettingEntity } from '@/Types/responses';

const props = withDefaults(
	defineProps<{
		setting: SettingEntity;
		pathPrepend: string;
		settingIconColor?: string;
		withBorder?: boolean;
	}>(),
	{
		settingIconColor: 'info',
	},
);
</script>
