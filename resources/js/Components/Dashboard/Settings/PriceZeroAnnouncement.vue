<template>
	<AnnouncementContent
		:class="`icon-${settingIconColor}`"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		:with-border="withBorder"
		name="price_zero_announcement">
		<template #icon>
			<MessageIcon />
		</template>

		<template #title>
			{{ setting.feature.translations['price-zero-setting-title'] }}
		</template>

		{{ setting.feature.translations['price-zero-setting-description'] }}
		<Link
			:href="route('club.help-sections.index')"
			class="mt-1 block w-full font-bold text-brand-base underline">
			{{ $t('help.learn-more') }}
		</Link>
	</AnnouncementContent>
</template>

<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import MessageIcon from '@/Components/Dashboard/Icons/MessageIcon.vue';
import AnnouncementContent from '@/Components/Dashboard/Settings/Types/AnnouncementContent.vue';
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
</script>
