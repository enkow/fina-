<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('admin.help-sections.index'), label: 'Sekcje pomocy' },
			{
				href: route('admin.help-sections.edit', { help_section: helpSection }),
				label: helpSection.title,
			},
			{
				href: route('admin.help-sections.help-items.index', {
					help_section: helpSection,
				}),
				label: 'Wpisy',
			},
			{
				href: route('admin.help-sections.help-items.edit', {
					help_section: helpSection,
					help_item: helpItem,
				}),
				label: helpItem.title,
			},
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<h2>Edytuj wpis: {{ helpItem.title }}</h2>
					</template>

					<form
						class="mt-4 grid grid-cols-4 gap-x-4 gap-y-5"
						@submit.prevent="
							helpItemForm.post(
								route('admin.help-sections.help-items.update', {
									help_section: helpSection,
									help_item: helpItem,
								}),
								{ preserveState: true, preserveScroll: true },
							)
						">
						<div class="input-group col-span-4">
							<InputLabel value="Tytuł" />
							<TextInput v-model="helpItemForm.title" />
							<div v-if="helpItemForm.errors.title" class="error">
								{{ helpItemForm.errors.title }}
							</div>
						</div>

						<div class="input-group col-span-4">
							<InputLabel value="Link do filmu" />
							<TextInput v-model="helpItemForm.video_url" />
							<div v-if="helpItemForm.errors.video_url" class="error">
								{{ helpItemForm.errors.video_url }}
							</div>
						</div>

						<div class="input-group col-span-2">
							<InputLabel value="Miniaturka" />
							<input type="file" @input="helpItemForm.thumbnail = $event.target.files[0]" />
							<div v-if="helpItemForm.errors.thumbnail" class="error">
								{{ helpItemForm.errors.thumbnail }}
							</div>
						</div>

						<div class="input-group col-span-2">
							<InputLabel value="Obecna miniaturka" />
							<img
								v-if="helpItem.thumbnail !== null"
								:src="'/images/help-item-thumbnails/' + helpItem.thumbnail"
								class="w-full" />
						</div>

						<div class="input-group col-span-4">
							<InputLabel value="Opis" />
							<TextInput v-model="helpItemForm.description" />
							<div v-if="helpItemForm.errors.description" class="error">
								{{ helpItemForm.errors.description }}
							</div>
						</div>

						<div class="input-group col-span-4">
							<InputLabel value="Treść" />
							<TextareaInput v-model="helpItemForm.content" :value="helpItemForm.content" rows="6" />
							<div v-if="helpItemForm.errors.content" class="error">
								{{ helpItemForm.errors.content }}
							</div>
						</div>

						<div class="input-group col-span-4">
							<InputLabel value="Waga wyświetlania" />
							<TextInput v-model="helpItemForm.weight" />
							<div v-if="helpItemForm.errors.weight" class="error">
								{{ helpItemForm.errors.weight }}
							</div>
						</div>

						<Button class="col-span-4 md:col-span-2 md:col-start-3" type="submit">
							{{ capitalize($t('main.action.update')) }}
						</Button>
					</form>
				</Card>
				<Card class="mt-3">
					<template #header>
						<h2>Obrazki</h2>
					</template>

					<Table
						v-if="helpItem.helpItemImages.length"
						:header="{
							id: 'Kod umieszczenia',
							path: 'Obrazek',
						}"
						:items="helpItemImages"
						:narrow="['path', 'actions']"
						class="mb-5"
						table-name="admin_help_item_images">
						<template #cell_id="props">[image:{{ props?.item.id }}]</template>

						<template #cell_path="props">
							<img :src="'/images/help-item-images/' + props?.item.path" class="mx-auto max-w-53" />
						</template>

						<template #cell_actions="props">
							<Button
								class="danger-light xs uppercase"
								type="button"
								@click="
									showDialog(capitalize($t('main.are-you-sure')), () => {
										router.visit(
											route('admin.help-sections.help-items.help-item-images.destroy', {
												help_section: helpSection,
												help_item: helpItem,
												help_item_image: props.item.id,
											}),
											{
												method: 'delete',
												preserveScroll: true,
												preserveState: true,
											},
										);
									})
								">
								<TrashIcon class="-mx-0.5 -mt-0.5" />
							</Button>
						</template>
					</Table>

					<form
						class="space-y-2"
						@submit.prevent="
							imageForm.post(
								route('admin.help-sections.help-items.help-item-images.store', {
									help_section: helpSection,
									help_item: helpItem,
								}),
								{ preserveScroll: true, preserveState: true },
							)
						">
						<p class="font-semibold">Dodaj nowy</p>
						<input type="file" @input="imageForm.file = $event.target.files[0]" />
						<div v-if="imageForm.errors.file" class="error">
							{{ imageForm.errors.file }}
						</div>

						<Button class="col-span-4 md:col-span-2 md:col-start-3" type="submit">
							{{ capitalize($t('main.action.add')) }}
						</Button>
					</form>
				</Card>
			</div>
		</ContentContainer>

		<DecisionModal
			:showing="confirmationDialogShowing"
			@confirm="confirmDialog()"
			@decline="cancelDialog()"
			@close="confirmationDialogShowing = false">
			{{ dialogContent }}
		</DecisionModal>
	</PanelLayout>
</template>

<script lang="ts" setup>
import { HelpItem, HelpItemImage, HelpSection } from '@/Types/models';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Card from '@/Components/Dashboard/Card.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { router, useForm } from '@inertiajs/vue3';
import { useString } from '@/Composables/useString';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { PaginatedResource } from '@/Types/responses';

const props = defineProps<{
	helpSection: HelpSection;
	helpItem: HelpItem;
	helpItemImages: PaginatedResource<HelpItemImage>;
}>();

const { capitalize } = useString();

const helpItemForm = useForm({
	video_url: props.helpItem.video_url,
	thumbnail: null,
	title: props.helpItem.title,
	description: props.helpItem.description,
	content: props.helpItem.content,
	weight: props.helpItem.weight,
});

const imageForm = useForm({
	file: null,
});

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
</script>
