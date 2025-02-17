<template>
	<TextValue
		:customRoute="customRoute"
		:scope="scope"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		:label="capitalize($t('main.current-value')) + ' (' + $t('main.hours') + ')'"
		name="refund_time_limit"
		:with-border="withBorder">
		<template #icon>
			<OvenIcon />
		</template>

		<template #title>{{ $t('settings.refund-time-limit') }}</template>

		{{ $t('settings.refund-time-limit-description') }}
	</TextValue>
</template>

<script lang="ts" setup>
import OvenIcon from '@/Components/Dashboard/Icons/OvenIcon.vue';
import { useString } from '@/Composables/useString';
import TextValue from '@/Components/Dashboard/Settings/Types/TextValue.vue';
import { SettingEntity } from '@/Types/responses';

const { capitalize } = useString();

defineProps<{
	setting: SettingEntity;
	scope?: string;
	customRoute?: string;
	settingIconColor?: string;
	withBorder?: boolean;
}>();
</script>
