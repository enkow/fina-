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

		<div v-if="!hasPermission" class="relative">
			<ToggleInput v-model="value" disabled />
			<div
				class="absolute left-0 top-0 h-10 w-40"
				v-tippy="{ allowHTML: true }"
				:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'"></div>
		</div>
		<ToggleInput v-else v-model="value" />
		<div v-if="usePage().props.errors?.updateStatus?.['value']" class="error">
			{{ usePage().props.errors?.updateStatus['value'] }}
		</div>
	</AccordionTab>
</template>
<script lang="ts" setup>
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import { onMounted, Ref, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { SettingEntity } from '@/Types/responses';
import { usePanelStore } from '@/Stores/panel';
import ToggleInput from '@/Components/Dashboard/ToggleInput.vue';

const props = defineProps<{
	name: string;
	setting: SettingEntity;
	customRoute?: string;
	settingIconColor: string;
	withBorder?: boolean;
}>();

const panelStore = usePanelStore();
const hasPermission = panelStore.isUserRole(['admin', 'manager']);

const value: Ref<boolean | string | number | number[] | null> = ref(props.setting.value);
let watchBlocked: boolean = true;

watchDebounced(
	value,
	() => {
		if (!watchBlocked) {
			router.put(
				!props.customRoute ? route('club.settings.update', { key: props.name }) : props.customRoute,
				{ value: value.value, feature_id: props.setting.feature?.id },
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
