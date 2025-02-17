<template>
	<form class="space-y-3">
		<div v-for="routeName in Object.entries(feature.data.custom_views)" class="flex w-full">
			<div class="w-1/2">{{ routeName[0] }}</div>
			<div class="w-1/2">
				<select v-model="form.custom_views[routeName[0]]" class="w-full">
					<option value="-1">Domyślny</option>
					<option
						v-for="gameCustomView in usePage().props.gameCustomViews[routeName[0]]"
						:value="gameCustomView">
						{{ gameCustomView }}
					</option>
				</select>
			</div>
		</div>
		<div class="flex w-full justify-end pt-4">
			<Button type="button" @click="save">Zapisz</Button>
		</div>
	</form>
</template>

<script lang="ts" setup>
import { Feature } from '@/Types/models';
import { useForm, usePage } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

const props = defineProps<{
	feature: Feature;
}>();

const customViews: Record<string, string> = {};
for (const [key, value] of Object.entries(props.feature.data.custom_views)) {
	customViews[key] = value ?? '-1';
}

const form = useForm({
	custom_views: customViews,
});

function save() {
	form.post(
		route('admin.games.features.update', {
			game: usePage().props.game,
			feature: props.feature,
		}),
		{ preserveState: true, preserveScroll: true },
	);
}
</script>
