<template>
	<AccordionTab :key="name" :class="`icon-${settingIconColor}`">
		<template #icon>
			<slot name="icon" />
		</template>

		<template #title>
			<slot name="title" />
		</template>

		<!--    <div v-if="!setting.value" class="mb-5 flex w-full flex-wrap">-->
		<!--      <Alert :close-button="false" class="small" type="warning">-->
		<!--        <slot name="noFileAlert" />-->
		<!--      </Alert>-->
		<!--    </div>-->

		<slot />

		<div v-if="setting.value">
			<div class="mb-7 mt-4 flex w-full justify-between rounded-md border border-gray-3 px-4 py-2">
				<a :href="'/' + pathPrepend + '/' + setting.value" target="_blank">{{ setting.value }}</a>
				<div class="flex cursor-pointer items-center space-x-2" @click="destroy">
					<p class="flex items-center text-danger-base">
						{{ capitalize($t('main.action.delete')) }}
					</p>
					<TrashIcon class="cursor-pointer text-danger-base" />
				</div>
			</div>
		</div>

		<FilePicker
			v-if="!setting.value && !hasPermission"
			v-tippy="{ allowHTML: true }"
			:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'"
			disabled
			class="mt-5">
			{{ placeholder ?? $t('settings.drop-terms-here') }}
		</FilePicker>
		<FilePicker v-else-if="!setting.value" class="mt-5" @update="upload">
			{{ placeholder ?? $t('settings.drop-terms-here') }}
		</FilePicker>
		<div v-if="form.errors.value" class="error w-full">
			{{ form.errors.value }}
		</div>
	</AccordionTab>
</template>

<script lang="ts" setup>
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import Alert from '@/Components/Dashboard/Alerts/Alert.vue';
import FilePicker from '@/Components/Dashboard/FilePicker.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import { useString } from '@/Composables/useString';
import { SettingEntity } from '@/Types/responses';
import { usePanelStore } from '@/Stores/panel';

const props = defineProps<{
	name: string;
	setting: SettingEntity;
	pathPrepend: string;
	settingIconColor: string;
	placeholder?: string;
}>();

const panelStore = usePanelStore();
const hasPermission = panelStore.isUserRole(['admin', 'manager']);

const { capitalize } = useString();

const form = useForm({
	value: null,
	feature_id: props.setting.feature?.id,
});

function upload(file: HTMLInputElement): void {
	form.value = file;
	form.post(route('club.settings.update', { key: props.name }), {
		preserveScroll: true,
	});
}

function destroy(): void {
	form.value = null;
	form.post(route('club.settings.update', { key: props.name }), {
		preserveScroll: true,
	});
}
</script>
