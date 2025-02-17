<template>
	<div class="flex w-full space-x-5">
		<div class="w-6/12">
			<TextInput v-model="form.icon" />
		</div>
		<div class="w-2/12">
			<p class="font-bold">Obecna ikonka</p>
			<br />
			<div v-if="feature.data.icon">
				<div v-html="feature.data.icon" class="icon-wrapper" />
			</div>
			<div v-else>Brak ikonki</div>
		</div>
	</div>
	<div class="flex justify-end">
		<Button type="button" @click="update">Zapisz</Button>
	</div>
</template>

<style scoped>
.icon-wrapper:deep(svg) {
	@apply h-10 w-10;
	fill: black !important;
}
</style>

<script lang="ts" setup>
import { Feature } from '@/Types/models';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import TextInput from '@/Components/Dashboard/TextInput.vue';

const props = defineProps<{
	feature: Feature;
}>();

const form = useForm({
	icon: props.feature.data.icon,
});

function update() {
	sendUpdateRequest();
}

function sendUpdateRequest() {
	form.post(
		route('admin.games.features.update', {
			game: usePage().props.game,
			feature: props.feature,
		}),
		{ preserveState: true, preserveScroll: true, forceFormData: true },
	);
}
</script>
