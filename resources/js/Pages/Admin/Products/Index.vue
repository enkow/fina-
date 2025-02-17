<template>
	<PanelLayout :breadcrumbs="[{ href: route('admin.products.index'), label: 'Produkty' }]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<div class="flex w-full justify-between">
							<h2 class="mb-10">Produkty</h2>
							<div class="flex space-x-2">
								<Button :href="route('admin.products.create')" class="w-50" type="link">Dodaj produkt</Button>
							</div>
						</div>
					</template>
					<Table
						:header="{
							id: 'ID',
							name_pl: 'Nazwa polska',
							name_en: 'Nazwa angielska',
						}"
						:items="products"
						:narrow="['actions']"
						table-name="admin_products">
						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									:href="route('admin.products.edit', { product: props.item.id })"
									class="warning-light xs uppercase"
									type="link">
									<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									as="button"
									class="danger-light xs uppercase"
									preserve-scroll
									type="button"
									v-if="props.item.system_label === null"
									@click="
										showDialog(capitalize($t('main.are-you-sure')), () => {
											router.visit(
												route('admin.products.destroy', {
													product: props.item,
												}),
												{
													method: 'delete',
													preserveState: true,
													preserveScroll: true,
												},
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
		{{ products }}
	</PanelLayout>
	<DecisionModal
		:showing="confirmationDialogShowing"
		@confirm="confirmDialog()"
		@decline="cancelDialog()"
		@close="confirmationDialogShowing = false">
		{{ dialogContent }}
	</DecisionModal>
</template>

<script lang="ts" setup>
import { Product } from '@/Types/models';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { PaginatedResource } from '@/Types/responses';
import Card from '@/Components/Dashboard/Card.vue';
import Table from '@/Components/Dashboard/Table.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { router } from '@inertiajs/vue3';
import { useString } from '@/Composables/useString';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();

const { capitalize } = useString();

const props = defineProps<{
	products: PaginatedResource<Product>;
}>();
</script>
