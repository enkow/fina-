<template>
	<PanelLayout :breadcrumbs="[{ href: route('admin.clubs.index'), label: 'Kluby' }]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<div class="flex w-full justify-between">
							<h2 class="mb-10">Kluby</h2>
							<div class="flex space-x-2">
								<TableSearch class="h-12 max-w-104" table-name="admin_clubs" />
								<Button :href="route('admin.clubs.create')" class="w-50" type="link">Dodaj klub</Button>
							</div>
						</div>
					</template>

					<Table
						:centered="['panel_enabled', 'aggregator_enabled', 'widget_enabled', 'calendar_enabled']"
						:header="{
							id: 'Identyfikator',
							name: 'Nazwa',
							email: 'Email',
							city: 'Miasto',
							panel_enabled: 'Panel',
							aggregator_enabled: 'Agregator',
							widget_enabled: 'Widget',
							calendar_enabled: 'Kalendarz',
							created_at: 'Utworzono',
						}"
						:items="clubs"
						:narrow="['panel_enabled', 'aggregator_enabled', 'widget_enabled', 'calendar_enabled']"
						table-name="admin_clubs">
						<template #cell_calendar_enabled="props">
							<div class="flex justify-center">
								<div
									class="cursor-pointer"
									@click="
										router.visit(
											route('admin.clubs.toggle-field', {
												club: props?.item.id,
												field: 'calendar_enabled',
											}),
											{ method: 'post' },
										)
									">
									<SuccessSquareIcon v-if="props?.item.calendar_enabled" />
									<DangerSquareIcon v-else />
								</div>
							</div>
						</template>

						<template #cell_widget_enabled="props">
							<div class="flex justify-center">
								<div
									class="cursor-pointer"
									@click="
										router.visit(
											route('admin.clubs.toggle-field', {
												club: props?.item.id,
												field: 'widget_enabled',
											}),
											{ method: 'post' },
										)
									">
									<SuccessSquareIcon v-if="props?.item.widget_enabled" />
									<DangerSquareIcon v-else />
								</div>
							</div>
						</template>

						<template #cell_aggregator_enabled="props">
							<div class="flex justify-center">
								<div
									class="cursor-pointer"
									@click="
										router.visit(
											route('admin.clubs.toggle-field', {
												club: props?.item.id,
												field: 'aggregator_enabled',
											}),
											{ method: 'post' },
										)
									">
									<SuccessSquareIcon v-if="props?.item.aggregator_enabled" />
									<DangerSquareIcon v-else />
								</div>
							</div>
						</template>

						<template #cell_panel_enabled="props">
							<div class="flex justify-center">
								<div
									class="cursor-pointer"
									@click="
										router.visit(
											route('admin.clubs.toggle-field', {
												club: props?.item.id,
												field: 'panel_enabled',
											}),
											{ method: 'post' },
										)
									">
									<SuccessSquareIcon v-if="props?.item.panel_enabled" />
									<DangerSquareIcon v-else />
								</div>
							</div>
						</template>

						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									:href="route('admin.clubs.login', { club: props.item.id })"
									as="button"
									class="brand-light xs uppercase"
									method="post"
									type="link">
									<ServiceIcon class="-mx-0.5 h-4 w-4" />
								</Button>
								<Button
									:href="route('admin.clubs.edit', { club: props.item.id })"
									class="warning-light xs uppercase"
									type="link">
									<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									as="button"
									class="danger-light xs uppercase"
									preserve-scroll
									type="button"
									@click="
										showDialog(capitalize($t('main.are-you-sure')), () => {
											router.visit(route('admin.clubs.destroy', { club: props.item }), { method: 'delete' });
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
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';

import { Club } from '@/Types/models';
import Card from '@/Components/Dashboard/Card.vue';
import Table from '@/Components/Dashboard/Table.vue';
import { PaginatedResource } from '@/Types/responses';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import SuccessSquareIcon from '@/Components/Dashboard/Icons/SuccessSquareIcon.vue';
import DangerSquareIcon from '@/Components/Dashboard/Icons/DangerSquareIcon.vue';
import ServiceIcon from '@/Components/Dashboard/Icons/ServiceIcon.vue';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { router } from '@inertiajs/vue3';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import { useString } from '@/Composables/useString';
import TableSearch from '@/Components/Dashboard/TableSearch.vue';
import GameSquare from '@/Components/Dashboard/GameSquare.vue';

const props = defineProps<{
	clubs: PaginatedResource<Club>;
}>();

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const { capitalize } = useString();
</script>
