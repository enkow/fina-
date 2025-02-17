<template>
	<AccordionTab :key="name" :class="`icon-${settingIconColor}`" :with-border="withBorder">
		<template #icon>
			<slot name="icon" />
		</template>

		<template #title>
			<slot name="title" />
		</template>

		<slot />

		<form
			class="w-full"
			@submit.prevent="
				form.put(route('club.settings.update', { key: name }), {
					preserveState: true,
					preserveScroll: true,
				})
			">
			<div class="mt-5 space-y-2">
				<InputLabel class="text-xs capitalize">{{ $t('announcement.announcement-content') }}</InputLabel>
				<TextareaInput
					v-model="form.value"
					:value="form.value"
					:disabled="!hasPermission"
					:class="{ 'disabled-readable': !hasPermission }" />
				<div v-if="form.errors.value" class="error">
					{{ form.errors.value }}
				</div>
			</div>

			<div class="accordion-footer">
				<div class="hidden w-1/2 space-y-2 sm:block"></div>
				<div class="w-full sm:w-1/2">
					<Button
						v-if="!hasPermission"
						class="lg accordion-footer__submit disabled !h-12"
						v-tippy="{ allowHTML: true }"
						:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'">
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
import { useForm, usePage } from '@inertiajs/vue3';
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { SettingEntity } from '@/Types/responses';
import { usePanelStore } from '@/Stores/panel';

const props = defineProps<{
	name: string;
	setting: SettingEntity;
	settingIconColor: string;
	withBorder?: boolean;
}>();

const { page } = usePanelStore();
const hasPermission = ['admin', 'manager'].includes(page.props.user.type as string);

const form = useForm({
	value: props.setting.value,
	feature_id: props.setting.feature?.id,
});
</script>
