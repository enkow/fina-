<template>
	<div class="space-y-3">
		<div class="flex w-full">
			<div class="w-1/2">Czy limit slotów rezerwacji ma być uruchomiony?</div>
			<div class="w-1/2">
				<Checkbox v-model="form.slot_limit_status" :checked="form.slot_limit_status" />
			</div>
		</div>
		<div class="flex w-full">
			<div class="w-1/2">Czy limit czasowy rezerwacji ma być uruchomiony?</div>
			<div class="w-1/2">
				<Checkbox v-model="form.duration_limit_status" :checked="form.duration_limit_status" />
			</div>
		</div>
		<div class="flex w-full">
			<div class="w-1/2">Czy limit dzienny rezerwacji ma być uruchomiony?</div>
			<div class="w-1/2">
				<Checkbox
					v-model="form.daily_reservation_limit_status"
					:checked="form.daily_reservation_limit_status" />
			</div>
		</div>
		<div class="flex w-full justify-end">
			<Button type="button" @click="update">Zapisz</Button>
		</div>
	</div>
</template>

<script lang="ts" setup>
import { Feature } from '@/Types/models';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

const props = defineProps<{
	feature: Feature;
}>();

const form = useForm({
	slot_limit_status: props.feature.data.slot_limit_status,
	duration_limit_status: props.feature.data.duration_limit_status,
	daily_reservation_limit_status: props.feature.data.daily_reservation_limit_status,
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
