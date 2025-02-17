<template>
	<AmountValue
		:label="capitalize($t('main.current-value')) + ' (' + (customSymbol || club?.country?.currency) + ')'"
		:custom-symbol="customSymbol || club?.country?.currency"
		:custom-route="customRoute('fee_fixed')"
		:setting="pseudoSetting(paymentMethod.fee_fixed)"
		setting-icon-color="info"
		name="fee_fixed"
		with-border>
		<template #icon>
			<HashIcon />
		</template>

		<template #title>
			Prowizja kwotowa do rezerwacji opłacanych online przez {{ paymentMethod.type }}
		</template>

		{{ $t('settings.additional-commission-fixed-description') }}
	</AmountValue>
	<AmountValue
		:label="capitalize($t('main.current-value')) + ' (%)'"
		custom-symbol="%"
		:custom-route="customRoute('fee_percentage')"
		:setting="pseudoSetting(paymentMethod.fee_percentage)"
		setting-icon-color="info"
		name="fee_percentage"
		with-border>
		<template #icon>
			<HashIcon />
		</template>

		<template #title>
			Prowizja procentowa do rezerwacji opłacanych online przez {{ paymentMethod.type }}
		</template>

		{{ $t('settings.additional-commission-fixed-description') }}
	</AmountValue>
</template>

<script lang="ts" setup>
import { useString } from '@/Composables/useString';
import { SettingEntity } from '@/Types/responses';
import HashIcon from '@/Components/Dashboard/Icons/HashIcon.vue';
import AmountValue from '@/Components/Dashboard/Settings/Types/AmountValue.vue';
import { Club, PaymentMethod } from '@/Types/models';

const { capitalize } = useString();

const pseudoSetting = (value: number): SettingEntity => ({
	value: value,
	translations: null,
	feature: null,
});

const props = defineProps<{
	club?: Club;
	customSymbol?: string;
	paymentMethod: PaymentMethod;
}>();

const customRoute = (name: string) => {
	if (!props.club) {
		return route('admin.settings.payment-methods.update', {
			payment_method: props.paymentMethod.type,
			field: name,
		});
	} else {
		return route('admin.clubs.payment-method-update', {
			club: props.club.id,
			payment_method: props.paymentMethod.type,
			field: name,
		});
	}
};
</script>
