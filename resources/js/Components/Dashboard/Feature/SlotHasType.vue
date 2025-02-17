<template>
	<form class="mb-3 grid w-full grid-cols-4 gap-x-3" @submit.prevent="store">
		<div class="col-span-3">
			<TextInput v-model="createName" />
		</div>
		<div class="col-span-1">
			<Button class="w-full" type="submit">Dodaj nowy typ</Button>
		</div>
	</form>
	<div
		v-for="option in feature.data.options"
		class="flex w-full items-center justify-between border-t border-gray-2 px-2 py-3">
		<div>
			{{ option }}
		</div>
		<div class="flex items-center space-x-2">
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

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const { capitalize } = useString();

const props = defineProps<{
	feature: Feature;
}>();

const createName: Ref<string> = ref('');

function store(): void {
	let options: Array<string> = props.feature.data.options;
	options.push(createName.value);
	sendUpdateRequest([...new Set(options)]);
}

function move(type: String, entry: String): void {
	let options: Array<string> = props.feature.data.options;
	let index: number = options.findIndex((item) => item === entry);
	let replacementIndex: number = type === 'up' ? index + 1 : index - 1;
	[options[index], options[replacementIndex]] = [options[replacementIndex], options[index]];
	sendUpdateRequest(options);
}

function destroy(entry: String) {
	let options: Array<String> = props.feature.data.options;
	let index: number = options.findIndex((item) => item === entry);
	delete options[index];
	sendUpdateRequest(compactArray(options));
}

function compactArray(array: Array<String>): Array<String> {
	let result: Array<String> = [];
	array.forEach((item) => {
		result.push(item);
	});
	return result;
}

function sendUpdateRequest(options: Array<String>): void {
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
