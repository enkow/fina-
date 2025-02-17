<template>
	<AmountValue
		:label="capitalize($t('main.current-value')) + ' (%)'"
		custom-symbol="%"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		name="additional_commission_percent"
		:with-border="withBorder">
		<template #icon>
			<HashIcon />
		</template>

		<template #title>{{ $t('settings.additional-commission-percent') }}</template>

		{{ $t('settings.additional-commission-percent-description') }}
	</AmountValue>
</template>

<script lang="ts" setup>
import { useString } from '@/Composables/useString';
import { SettingEntity } from '@/Types/responses';
import HashIcon from '@/Components/Dashboard/Icons/HashIcon.vue';
import AmountValue from '@/Components/Dashboard/Settings/Types/AmountValue.vue';

const { capitalize } = useString();

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
