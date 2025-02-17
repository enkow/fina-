<template>
	<div class="no-settings">Brak ustawień</div>
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
	is_start_at_required: props.feature.data.is_start_at_required,
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
