<template>
	<TextValue
		:customRoute="customRoute"
		:scope="scope"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		label="Host url żarowki"
		name="bulb_url">
		<template #icon>
			<OvenIcon />
		</template>

		<template #title>Host url żarowki</template>
	</TextValue>
</template>

<script lang="ts" setup>
import OvenIcon from '@/Components/Dashboard/Icons/OvenIcon.vue';
import { useString } from '@/Composables/useString';
import TextValue from '@/Components/Dashboard/Settings/Types/TextValue.vue';
import { SettingEntity } from '@/Types/responses';

const { capitalize } = useString();

const props = withDefaults(
	defineProps<{
		setting: SettingEntity;
		scope?: string;
		customRoute?: string;
		settingIconColor?: string;
	}>(),
	{
		scope: 'user',
		settingIconColor: 'info',
	},
);
</script>
