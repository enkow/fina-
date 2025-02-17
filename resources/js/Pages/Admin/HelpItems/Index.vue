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
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<div class="mb-3 flex w-full justify-between">
							<div>
								<h2>Sekcja pomocy: {{ helpSection.title }} - wpisy</h2>
							</div>
							<div>
								<Button
									:href="
										route('admin.help-sections.help-items.create', {
											help_section: helpSection,
										})
									"
									type="link">
									Dodaj
								</Button>
							</div>
						</div>
					</template>
					<Table
						:header="{
							video_url: 'Link do video',
							title: 'Tytuł',
							weight: 'Waga wyświetlania',
							active: 'Czy widoczny',
						}"
						:items="helpItems"
						:narrow="['active', 'actions']"
						table-name="admin_help_items">
						<template #cell_active="props">
							<div class="flex justify-center">
								<div
									class="cursor-pointer"
									@click="
										router.visit(
											route('admin.help.sections.help-items.toggle-active', {
												help_section: helpSection,
												help_item: props.item.id,
											}),
											{ method: 'post' },
										)
									">
									<SuccessSquareIcon v-if="props?.item.active" />
									<DangerSquareIcon v-else />
								</div>
							</div>
						</template>

						<template #cell_video_url="props">
							<a :href="props?.item.video_url" class="text-brand-base" target="_blank">Link</a>
						</template>

						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									:href="
										route('admin.help-sections.help-items.edit', {
											help_section: helpSection,
											help_item: props.item.id,
										})
									"
									class="warning-light xs uppercase"
									type="link">
									<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									class="danger-light xs uppercase"
									preserve-scroll
									type="button"
									@click="
										showDialog(capitalize($t('main.are-you-sure')), () => {
											router.visit(
												route('admin.help-sections.help-items.destroy', {
													help_section: helpSection,
													help_item: props.item.id,
												}),
												{ method: 'delete' },
											);
										})
									">
									<TrashIcon class="-mx-0.5 -mt-0.5" />
								</Button>
							</div>
						</template>
					</Table>
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
import { PaginatedResource } from '@/Types/responses';
import { router } from '@inertiajs/vue3';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Card from '@/Components/Dashboard/Card.vue';
import Table from '@/Components/Dashboard/Table.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { HelpItem, HelpSection } from '@/Types/models';
import SuccessSquareIcon from '@/Components/Dashboard/Icons/SuccessSquareIcon.vue';
import DangerSquareIcon from '@/Components/Dashboard/Icons/DangerSquareIcon.vue';
import { useString } from '@/Composables/useString';

const props = defineProps<{
	helpSection: HelpSection;
	helpItems: PaginatedResource<HelpItem>;
}>();

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const { capitalize } = useString();
</script>
