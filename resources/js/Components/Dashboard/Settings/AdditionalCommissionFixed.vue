<template>
	<FloatValue
		:label="capitalize($t('main.current-value')) + ' (' + usePage().props.user.club.country.currency + ')'"
		:setting="setting"
		:setting-icon-color="settingIconColor"
		name="additional_commission_fixed"
		:with-border="withBorder">
		<template #icon>
			<HashIcon />
		</template>

		<template #title>{{ $t('settings.additional-commission-fixed') }}</template>

		{{ $t('settings.additional-commission-fixed-description') }}
	</FloatValue>
</template>

<script lang="ts" setup>
import { useString } from '@/Composables/useString';
import { SettingEntity } from '@/Types/responses';
import HashIcon from '@/Components/Dashboard/Icons/HashIcon.vue';
import FloatValue from '@/Components/Dashboard/Settings/Types/AmountValue.vue';
import { usePage } from '@inertiajs/vue3';

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
