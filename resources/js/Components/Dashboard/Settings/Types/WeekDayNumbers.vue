<template>
	<AccordionTab :key="name" :class="`icon-${settingIconColor}`" :with-border="withBorder">
		<template #icon>
			<slot name="icon" />
		</template>

		<template #title>
			<slot name="title" />
		</template>

		<p class="block w-full">
			<slot />
		</p>

		<form
			class="w-full"
			@submit.prevent="
				form.put(
					!props.customRoute
						? route(scope + '.settings.update', {
								key: name,
						  })
						: customRoute,
					{
						preserveScroll: true,
						preserveState: true,
					},
				)
			">
			<div class="my-5 grid w-full grid-cols-4 gap-x-6 gap-y-6">
				<div v-for="i in 7" class="col-span-4 sm:col-span-1">
					<InputLabel :value="$t('main.week-day.' + i)" class="mb-1 font-normal capitalize" />
					<ToggleInput v-if="isToggle" v-model="form.value[i - 1]" />

					<TextInput
					v-else
						:class="{ 'disabled-readable': !hasPermission }"
						v-model="form.value[i - 1]"
						:placeholder="placeholder ?? capitalize($t('main.none'))"
						:disabled="!hasPermission" />
					<div v-if="form.errors['value.' + (i - 1)]" class="error">
						{{ form.errors['value.' + (i - 1)] }}
					</div>
				</div>
			</div>

			<div class="accordion-footer">
				<div class="hidden w-1/2 space-y-2 sm:block"></div>
				<div class="w-full sm:w-1/2">
					<Button
						v-if="!hasPermission"
						v-tippy="{ allowHTML: true }"
						:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'"
						class="lg accordion-footer__submit disabled !h-11.5">
						{{ $t('main.action.update') }}
					</Button>
					<Button v-else class="lg accordion-footer__submit !h-12" type="submit">
						{{ $t('main.action.update') }}
					</Button>
				</div>
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
import { capitalize } from 'vue';
import { usePanelStore } from '@/Stores/panel';
import ToggleInput from '@/Components/Dashboard/ToggleInput.vue';

const props = withDefaults(
	defineProps<{
		name: string;
		setting: SettingEntity;
		scope?: string;
		customRoute?: string;
		settingIconColor: string;
		placeholder?: string;
		withBorder?: boolean;
		isToggle?: boolean;
	}>(),
	{
		scope: 'club',
		withBorder: false,
		isToggle: false,
	},
);

const panelStore = usePanelStore();
const hasPermission = panelStore.isUserRole(['admin', 'manager']);

const form = useForm({
	value: props.setting.value,
	feature_id: props.setting.feature?.id,
});
console.log(form.value)
</script>
