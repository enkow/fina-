<template>
	<div class="space-y-3">
		<div class="flex w-full">
			<div class="w-1/2">Status komunikatu (cena = 0)</div>
			<div class="w-1/2">
				<Checkbox
					v-model="form.price_zero_announcement_status"
					:checked="form.price_zero_announcement_status" />
			</div>
		</div>
		<div class="flex w-full">
			<div class="w-1/2">Status komunikatu (cena > 0)</div>
			<div class="w-1/2">
				<Checkbox
					v-model="form.price_non_zero_announcement_status"
					:checked="form.price_non_zero_announcement_status" />
			</div>
		</div>
		<div class="flex w-full justify-end">
			<Button type="button" @click="update">Zapisz</Button>
		</div>
	</div>
</template>

<script lang="ts" setup>
import { Feature } from '@/Types/models';
import { useForm, usePage } from '@inertiajs/vue3';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

const props = defineProps<{
	feature: Feature;
}>();

const form = useForm({
	price_non_zero_announcement_status: props.feature.data.price_non_zero_announcement_status,
	price_zero_announcement_status: props.feature.data.price_zero_announcement_status,
});

function update() {
	form.post(
		route('admin.games.features.update', {
			game: usePage().props.game,
			feature: props.feature,
		}),
		{ preserveState: true, preserveScroll: true },
	);
}
</script>
