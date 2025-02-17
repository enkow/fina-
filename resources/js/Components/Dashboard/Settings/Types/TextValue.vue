<template>
	<AccordionTab :key="name" :class="`icon-${settingIconColor}`" :with-border="withBorder">
		<template #icon>
			<slot name="icon" />
		</template>

		<template #title>
			<slot name="title" />
		</template>

		<p class="mb-3 block w-full font-semibold">
			<slot name="info" />
		</p>

		<p class="block w-full">
			<slot />
		</p>

		<form
			class="accordion-footer flex-wrap sm:flex-nowrap"
			@submit.prevent="
				form.put(!customRoute ? route(scope + '.settings.update', { key: name }) : customRoute, {
					preserveScroll: true,
					preserveState: true,
				})
			">
			<div class="w-full space-y-2 sm:w-1/2">
				<InputLabel :value="label" class="text-xs" />
				<TextInput
					v-model="form.value"
					:disabled="!hasPermission"
					class="w-full"
					:class="{ 'disabled-readable': !hasPermission }" />
				<div v-if="form.errors.value" class="error w-full">
					{{ form.errors.value }}
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
	</AccordionTab>
</template>

<script lang="ts" setup>
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { SettingEntity } from '@/Types/responses';
import { User } from '@/Types/models';
import { usePanelStore } from '@/Stores/panel';

const props = withDefaults(
	defineProps<{
		label?: string;
		name: string;
		setting: SettingEntity;
		scope?: string;
		customRoute?: string;
		settingIconColor: string;
		withBorder?: boolean;
	}>(),
	{
		scope: 'club',
	},
);
const { page } = usePanelStore();
const hasPermission = ['admin', 'manager'].includes(page.props.user.type as string);

const form = useForm({
	value: props.setting.value,
	feature_id: props.setting.feature?.id,
});
</script>
