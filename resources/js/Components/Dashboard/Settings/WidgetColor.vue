<template>
	<AccordionTab key="widget_color" :class="`icon-${settingIconColor}`" :with-border="withBorder">
		<template #icon>
			<ClubIcon />
		</template>

		<template #title>
			{{ $t('announcement.widget-color') }}
		</template>

		<form class="accordion-footer" @submit.prevent="submitForm">
			<div class="w-1/2 space-y-2">
				<InputLabel class="text-xs" value="" />
				<SquareColorPicker
					:color="String(form.value)"
					:preview-classes="{
						'!w-12 !h-12 -mt-3': true,
					}"
					@updated="(hex) => (form.value = hex)"
					:disabled="!hasPermission" />
				<div v-if="form.errors.value" class="error w-full">
					{{ form.errors.value }}
				</div>
			</div>
			<div class="w-1/2">
				<Button
					v-if="!hasPermission"
					class="lg accordion-footer__submit disabled !h-11.5"
					v-tippy="{ allowHTML: true }"
					:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'">
					{{ $t('main.action.update') }}
				</Button>
				<Button v-else class="lg accordion-footer__submit !h-12" type="submit">
					{{ $t('main.action.update') }}
				</Button>
			</div>
		</form>
	</AccordionTab>
</template>

<script lang="ts" setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { useString } from '@/Composables/useString';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import ClubIcon from '@/Components/Dashboard/Icons/ClubIcon.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { Setting } from '@/Types/models';
import SquareColorPicker from '@/Components/SquareColorPicker.vue';
import { usePanelStore } from '@/Stores/panel';

const props = withDefaults(
	defineProps<{
		name: string;
		setting: Setting;
		settingIconColor?: string;
		withBorder?: boolean;
	}>(),
	{
		settingIconColor: 'info',
	},
);

const { page } = usePanelStore();
const hasPermission = ['admin', 'manager'].includes(page.props.user.type as string);

const { capitalize } = useString();

const form = useForm({
	value: props.setting.value,
	feature_id: props.setting.feature_id,
});

function submitForm() {
	form.put(route('club.settings.update', { key: props.name }), {
		preserveScroll: true,
		preserveState: true,
	});
}
</script>
