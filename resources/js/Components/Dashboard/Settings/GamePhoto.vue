<template>
	<File
		:pathPrepend="pathPrepend"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		:placeholder="setting.feature.translations['drop-game-photo-there']"
		:with-border="withBorder"
		class="icon-brand"
		name="game_photo">
		<template #icon>
			<PhotoIcon />
		</template>

		<template #title>
			{{ setting.feature.translations['game-photo'] }}
		</template>

		<template #noFileAlert>
			{{ setting.feature.translations['game-photo-no-file-alert'] }}
		</template>

		<p class="block w-full">
			{{ setting.feature.translations['game-photo-description'] }}
		</p>
	</File>
</template>
<script lang="ts" setup>
import File from '@/Components/Dashboard/Settings/Types/File.vue';
import { SettingEntity } from '@/Types/responses';
import PhotoIcon from '@/Components/Dashboard/Icons/PhotoIcon.vue';

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
