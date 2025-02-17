<template>
	<div class="space-y-3">
		<!--    <div class="flex w-full">-->
		<!--      <div class="w-1/2">Ustawienie - maksymalna ilość osób na rezerwacje stolika</div>-->
		<!--      <div class="w-1/2">-->
		<!--        <Checkbox v-model="form.slots_limit_setting" :checked="form.slots_limit_setting" />-->
		<!--      </div>-->
		<!--    </div>-->
		<!--    <div class="flex w-full">-->
		<!--      <div class="w-1/2">Ustawienie - domyślna ilość osób na rezerwacje stolika</div>-->
		<!--      <div class="w-1/2">-->
		<!--        <Checkbox v-model="form.slots_default_setting" :checked="form.slots_default_setting" />-->
		<!--      </div>-->
		<!--    </div>-->
		<!--    <div class="flex w-full">-->
		<!--      <div class="w-1/2">Slot nadrzędny - pojemność ustawiana względem dnia tygodnia</div>-->
		<!--      <div class="w-1/2">-->
		<!--        <Checkbox v-model="form.parent_has_capacity_by_week_day" :checked="form.parent_has_capacity_by_week_day" />-->
		<!--      </div>-->
		<!--    </div>-->

		<div class="flex w-full justify-end">
			<Button type="button" @click="update">Zapisz</Button>
		</div>
	</div>
</template>

<script lang="ts" setup>
import { Feature } from '@/Types/models';
import { useForm, usePage } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';

const props = defineProps<{
	feature: Feature;
}>();

const form = useForm({
	slots_default_setting: props.feature.data.slots_default_setting,
	slots_limit_setting: props.feature.data.slots_limit_setting,
	parent_has_capacity_by_week_day: props.feature.data.parent_has_capacity_by_week_day,
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
