<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.games.reservations.calendar', {
					game: panelStore.lastVisitedCalendarGame.name,
					'filters[reservations][game]': panelStore.lastVisitedCalendarGame.id,
					'filters[calendar_reservations][startRange][to]': panelStore.lastVisitedCalendarDate,
					'filters[calendar_reservations][startRange][from]': panelStore.lastVisitedCalendarDate,
					'filters[reservations][startRange][to]': panelStore.lastVisitedCalendarDate,
					'filters[reservations][startRange][from]': panelStore.lastVisitedCalendarDate,
				}),
				label: $t('reservation.club-calendar'),
			},
			{
				href: route('club.announcements.index'),
				label: $t('announcement.announcements-for-employees'),
			},
		]">
		<ContentContainer>
			<div class="col-span-12 md:col-span-4">
				<Card v-if="['admin', 'manager'].includes(usePage().props.user.type)">
					<h2>{{ $t('announcement.add-announcement') }}</h2>

					<form class="space-y-4" @submit.prevent="submit">
						<div class="input-group">
							<InputLabel :value="capitalize($t('main.date'))" required />
							<SimpleDatepicker v-model="form.start_at" :inputWithIcon="true" />
							<div v-if="form.errors.start_at" class="error">
								{{ form.errors.start_at }}
							</div>
						</div>

						<div class="input-group">
							<InputLabel :value="capitalize($t('announcement.announcement-content'))" required />
							<TextareaInput
								v-model="form.content"
								:placeholder="$t('main.type-here')"
								:value="form.content" />
							<div v-if="form.errors.content" class="error">
								{{ form.errors.content }}
							</div>
						</div>

						<Button class="lg !mt-7 !w-full" type="submit">{{ capitalize($t('main.action.save')) }}</Button>
					</form>
				</Card>
			</div>
			<div class="col-span-12 md:col-span-8">
				<Card class="!px-0 !pb-0">
					<div class="mb-6 px-7">
						<h2>{{ $t('announcement.current-announcements') }}</h2>
						<p>{{ $t('announcement.index-description') }}</p>
					</div>

					<Table
						v-if="announcements.data.length"
						:header="{
							id: capitalize($t('main.number-short')),
							start_at: capitalize($t('main.date')),
							content: capitalize($t('announcement.content')),
						}"
						:items="announcements"
						:narrow="['actions']"
						class="max-w-full"
						table-name="announcements">
						<template #cell_content="props">
							<div class="w-72 break-words sm:w-96 md:w-64 lg:w-72 xl:w-96 2xl:w-136 3xl:w-179">
								{{ props.item.content }}
							</div>
						</template>
						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									:href="
										route('club.announcements.edit', {
											announcement: props.item.id,
										})
									"
									class="warning-light xs uppercase"
									:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
									:type="['admin', 'manager'].includes(usePage().props.user.type) ? 'link' : 'button'">
									<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									as="button"
									class="danger-light xs uppercase"
									preserve-scroll
									type="button"
									:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
									@click="
										showDialog($t('main.are-you-sure'), () => {
											router.visit(
												route('club.announcements.destroy', {
													announcement: props.item,
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
					<div v-else>
						<div class="mt-6 border-t border-t-gray-2 px-7 pb-6 pt-5">
							{{ $t('announcement.there-will-appear-added-announcements') }}
						</div>
					</div>
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
import { Announcement } from '@/Types/models';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Card from '@/Components/Dashboard/Card.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import SimpleDatepicker from '@/Components/Dashboard/SimpleDatepicker.vue';
import dayjs from 'dayjs';
import { PaginatedResource } from '@/Types/responses';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import { useString } from '@/Composables/useString';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import { usePanelStore } from '@/Stores/panel';

const { capitalize } = useString();
const panelStore = usePanelStore();

const props = defineProps<{
	announcements: PaginatedResource<Announcement>;
}>();

const form = useForm({
	type: 0,
	start_at: dayjs().format('YYYY-MM-DD'),
	content: null,
});

function submit() {
	form.post(route('club.announcements.store'), {
		onSuccess: () => {
			form.reset();
		},
	});
}

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
</script>
