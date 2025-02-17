<template>
	<AccordionTab :key="name" :class="`icon-${settingIconColor}`" :with-border="withBorder">
		<template #icon>
			<HourglassIcon />
		</template>

		<template #title>
			{{ customTitle ?? defaultTitle }}
		</template>

		{{ valueSetting.feature.translations['status-setting-description'] }}

		<div v-if="!hasPermission" class="relative">
			<ToggleInput v-model="statusValue" disabled />
			<div
				class="absolute left-0 top-0 h-10 w-40"
				v-tippy="{ allowHTML: true }"
				:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'"></div>
		</div>
		<ToggleInput v-model="statusValue" />
		<div v-if="usePage().props.errors?.updateStatus?.['value']" class="error">
			{{ usePage().props.errors?.updateStatus['value'] }}
		</div>

		<div class="mt-10 w-full" v-show="statusValue">
			{{ valueSetting.feature.translations['value-setting-description'] }}

			<form
				class="accordion-footer flex-wrap sm:flex-nowrap"
				@submit.prevent="
					valueForm.put(
						!customRoute
							? route(scope + '.settings.update', { key: 'fixed_reservation_duration_value' })
							: customRoute,
						{
							preserveScroll: true,
							preserveState: true,
						},
					)
				">
				<div class="w-full space-y-2 sm:w-1/2">
					<InputLabel :value="valueSetting.feature.translations['value-setting-label']" class="text-xs" />
					<TextInput
						v-model="valueForm.value"
						:disabled="!hasPermission"
						class="w-full"
						:class="{ 'disabled-readable': !hasPermission }" />
					<div v-if="valueForm.errors.value" class="error w-full">
						{{ valueForm.errors.value }}
					</div>
				</div>
				<div class="w-full space-y-2 sm:w-1/2">
					<InputLabel :value="'&nbsp;'" class="text-xs" />
					<Button
						v-if="!hasPermission"
						v-tippy="{ allowHTML: true }"
						:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'"
						class="lg accordion-footer__submit disabled !h-11 2xl:!h-11.5">
						{{ $t('main.action.update') }}
					</Button>
					<Button v-else class="lg accordion-footer__submit !h-12" type="submit">
						{{ $t('main.action.update') }}
					</Button>
				</div>
			</form>
		</div>
	</AccordionTab>
</template>

<script lang="ts" setup>
import { useString } from '@/Composables/useString';
import { SettingEntity } from '@/Types/responses';
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import HourglassIcon from '@/Components/Dashboard/Icons/HourglassIcon.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { usePanelStore } from '@/Stores/panel';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { watchDebounced } from '@vueuse/core/index';
import { computed, onMounted, ref } from 'vue';
import ToggleInput from '@/Components/Dashboard/ToggleInput.vue';

const { capitalize } = useString();

const { page } = usePanelStore();
const hasPermission = ['admin', 'manager'].includes(page.props.user.type as string);

const props = withDefaults(
	defineProps<{
		statusSetting: SettingEntity;
		valueSetting: SettingEntity;
		scope?: string;
		customRoute?: string;
		settingIconColor?: string;
		customTitle?: string;
		withBorder?: boolean;
	}>(),
	{
		scope: 'club',
		settingIconColor: 'info',
		withBorder: true,
	},
);

const defaultTitle = computed<string>(() => {
	return `Stała długość rezerwacji - ${props.statusSetting.feature.game.name} - ${props.statusSetting.feature.code}`;
});

const valueForm = useForm({
	value: props.valueSetting.value,
	feature_id: props.valueSetting.feature?.id,
});

const statusValue = ref<boolean>(!!props.statusSetting.value);
let watchBlocked: boolean = true;
watchDebounced(
	statusValue,
	() => {
		if (!watchBlocked) {
			router.put(
				!props.customRoute
					? route('club.settings.update', { key: 'fixed_reservation_duration_status' })
					: props.customRoute,
				{ value: statusValue.value, feature_id: props.statusSetting.feature?.id },
				{ preserveState: true, preserveScroll: true, errorBag: 'updateStatus' },
			);
		}
	},
	{ debounce: 600, maxWait: 3000 },
);

onMounted(() => {
	setTimeout(() => {
		watchBlocked = false;
	}, 700);
});
</script>
