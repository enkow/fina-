<template>
	<PanelLayout :breadcrumbs="[{ href: route('admin.games.index'), label: 'Gry' }]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<div class="mb-3 flex justify-between">
							<div>
								<h2>Gry</h2>
							</div>
							<div>
								<Button :href="route('admin.games.create')" type="link">Dodaj grę</Button>
							</div>
						</div>
					</template>

					<Table
						:columns-max-widths="{
							id: 8,
						}"
						:header="{
							id: 'Identyfikator',
							name: 'Nazwa',
							icon: 'Ikonka',
							clubs_count: 'Liczba klubów',
						}"
						:items="games"
						:narrow="['actions', 'icon']"
						table-name="admin_games">
						<template #cell_icon="props">
							<GameSquare class="!bg-brand-base">
								<div v-html="props.item.icon" />
							</GameSquare>
						</template>

						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									:href="route('admin.games.edit', { game: props.item.id })"
									class="warning-light xs uppercase"
									type="link">
									<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									as="button"
									class="danger-light xs uppercase"
									preserve-scroll
									type="button"
									@click="destroy(props.item)">
									<TrashIcon class="-mx-0.5 -mt-0.5" />
								</Button>
							</div>
						</template>
					</Table>
				</Card>
			</div>
		</ContentContainer>
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
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { Game } from '@/Types/models';
import { PaginatedResource } from '@/Types/responses';
import Card from '@/Components/Dashboard/Card.vue';
import Table from '@/Components/Dashboard/Table.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { router } from '@inertiajs/vue3';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { POSITION, useToast } from 'vue-toastification';
import GameSquare from '@/Components/Dashboard/GameSquare.vue';

const props = defineProps<{
	games: PaginatedResource<Game>;
}>();

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const toast = useToast();

function destroy(game: Game) {
	if (game.clubs_count) {
		toast.error('Nie możesz usunąć gry, która jest przypisana do klubów', {
			timeout: 4000,
			position: POSITION.BOTTOM_RIGHT,
		});
		return;
	}
	showDialog('Jesteś pewny?', () => {
		router.visit(
			route('admin.games.destroy', {
				game: game,
			}),
			{
				method: 'delete',
				preserveState: true,
				preserveScroll: true,
			},
		);
	});
}
</script>
