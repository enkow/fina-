<template>
	<form class="mb-3 grid w-full grid-cols-4 gap-x-3" @submit.prevent="store">
		<div class="col-span-1">
			<select v-model="type" class="w-full">
				<option v-for="availableSlotType in availableSlotTypes">
					{{ availableSlotType }}
				</option>
			</select>
		</div>
		<div class="col-span-2">
			<TextInput v-model="name" />
		</div>
		<div class="col-span-1">
			<Button class="w-full" type="submit">Dodaj nowy podtyp</Button>
		</div>
	</form>
	<div
		v-for="option in feature.data.options"
		class="flex w-full items-center justify-between border-t border-gray-2 px-2 py-3">
		<div class="w-5/12">
			{{ option.type }}
		</div>
		<div class="w-5/12">
			{{ option.name }}
		</div>
		<div class="flex w-2/12 items-center justify-end space-x-2">
			<div v-if="feature.data.options[0] !== option" class="cursor-pointer" @click="move('down', option)">
				<svg
					class="h-4 w-4"
					fill="none"
					stroke="currentColor"
					stroke-width="1.5"
					viewBox="0 0 24 24"
					xmlns="http://www.w3.org/2000/svg">
					<path d="M8.25 6.75L12 3m0 0l3.75 3.75M12 3v18" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
			</div>

			<p
				v-if="feature.data.options.slice(-1)[0] !== option"
				class="cursor-pointer"
				@click="move('up', option)">
				<svg
					class="h-4 w-4"
					fill="none"
					stroke="currentColor"
					stroke-width="1.5"
					viewBox="0 0 24 24"
					xmlns="http://www.w3.org/2000/svg">
					<path
						d="M15.75 17.25L12 21m0 0l-3.75-3.75M12 21V3"
						stroke-linecap="round"
						stroke-linejoin="round" />
				</svg>
			</p>

			<Button
				as="button"
				class="danger-light xs uppercase"
				preserve-scroll
				type="button"
				@click="
					showDialog(capitalize($t('main.are-you-sure')), () => {
						destroy(option);
					})
				">
				<TrashIcon class="-mx-0.5 -mt-0.5" />
			</Button>
		</div>
	</div>

	<DecisionModal
		:showing="confirmationDialogShowing"
		@confirm="confirmDialog()"
		@decline="cancelDialog()"
		@close="confirmationDialogShowing = false">
		{{ dialogContent }}
	</DecisionModal>
</template>

<script lang="ts" setup>
import { router, usePage } from '@inertiajs/vue3';
import { Ref, ref } from 'vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import { Feature } from '@/Types/models';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { useString } from '@/Composables/useString';

interface Subtype {
	type: string;
	name: string;
}

const availableSlotTypes: Ref<Array<string>> = ref([]);
usePage().props.features.forEach((feature) => {
	if (feature.type === 'slot_has_type') {
		availableSlotTypes.value = feature.data.options;
	}
});

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const { capitalize } = useString();

const props = defineProps<{
	feature: Feature;
}>();

const type: Ref<string> = ref(availableSlotTypes.value[0]);
const name: Ref<string> = ref('');

function store(): void {
	name.value = name.value.trim();
	if (name.value === '') {
		return;
	}
	let options: Array<Subtype> = props.feature.data.options;
	let canStore: Boolean =
		options.filter((option) => option.type === type.value && option.name === name.value).length === 0;
	if (!canStore) {
		return;
	}
	options.push({
		type: type.value,
		name: name.value,
	});
	sendUpdateRequest(options);
}

function move(type: String, entry: Subtype): void {
	let options: Array<Subtype> = props.feature.data.options;
	let index: number = options.findIndex((option) => option === entry);
	let replacementIndex: number = type === 'up' ? index + 1 : index - 1;
	[options[index], options[replacementIndex]] = [options[replacementIndex], options[index]];
	sendUpdateRequest(options);
}

function destroy(entry: Subtype) {
	let options: Array<Subtype> = props.feature.data.options;
	let index: number = options.findIndex((item) => item === entry);
	delete options[index];
	sendUpdateRequest(compactArray(options));
}

function compactArray(array: Array<Subtype>): Array<Subtype> {
	let result: Array<Subtype> = [];
	array.forEach((item) => {
		result.push(item);
	});
	return result;
}

function sendUpdateRequest(options: Array<Subtype>): void {
	router.patch(
		route('admin.games.features.update', {
			game: usePage().props.game,
			feature: props.feature,
		}),
		{ options: options },
		{ preserveState: true, preserveScroll: true },
	);
}
</script>
