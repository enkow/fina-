<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.games.slots.index', { game: game }),
				label: usePage().props.gameTranslations[game.id]['slot-plural'],
			},
		]">
		<ContentContainer>
			<div class="col-span-12 xl:col-span-9">
				<Card>
					<template #header>
						<div class="flex justify-between">
							<h2>
								{{ usePage().props.gameTranslations[game.id]['slot-plural'] }}
							</h2>
							<Button
								v-if="['admin', 'manager'].includes(usePage().props.user.type)"
								:href="route('club.games.slots.create', { game: game.id })"
								type="link">
								{{ usePage().props.gameTranslations[game.id]['slot-add'] }}
							</Button>
						</div>
					</template>
					<div class="relative overflow-x-auto" v-if="slots.data.length">
						<table class="mt-5 table w-full table-auto border-b border-gray-2">
							<thead>
								<tr>
									<th class="whitespace-nowrap px-3 py-4 pb-5 text-left !font-semibold">
										{{ features.slot_has_parent[0].translations['parent-slot-name'] }}
									</th>
									<th class="w-10 border-l border-r border-gray-2 px-3 pb-2 text-left !font-semibold">
										<div class="capitalize">
											<p class="text-xs font-normal">
												{{
													props.game.features
														?.find((feature: Feature) => feature.type === 'parent_slot_has_online_status')
														?.translations['active-online'].split(' ')[0]
												}}
											</p>
											<p>
												{{
													props.game.features
														?.find((feature: Feature) => feature.type === 'parent_slot_has_online_status')
														?.translations['active-online'].split(' ')[1]
												}}
											</p>
										</div>
									</th>
									<th
										v-for="i in 7"
										class="w-10 border-l border-r border-gray-2 px-3 pb-2 text-center !font-semibold">
										{{ capitalize($t(`main.week-day-short.${i}`)) }}
									</th>
									<th
										class="min-w-[60px] pb-2 pl-3 text-left !font-semibold"
										style="width: 1%; white-space: nowrap">
										{{ capitalize($t('main.action.plural')) }}
									</th>
								</tr>
							</thead>
							<tbody v-for="(slot, index) in slots.data">
								<tr class="h-[3.625rem] border-t border-gray-2">
									<td class="no-wrap border-r border-gray-2 px-3">
										<div class="flex items-center space-x-2">
											<p>{{ slot.name }}</p>
										</div>
									</td>
									<td class="no-wrap border-r border-gray-2 px-3">
										<div class="flex items-center space-x-2">
											<Link
												:href="
													panelStore.authorizeLinkByRole(
														route('club.games.slots.toggle-online-status', {
															game: game,
															slot: slot,
														}),
														['admin', 'manager'],
													)
												"
												:class="{
													'cursor-pointer': panelStore.isUserRole(['admin', 'manager']),
													'cursor-default': !panelStore.isUserRole(['admin', 'manager']),
												}"
												as="link"
												class="flex w-full items-center justify-center"
												method="post"
												preserve-scroll
												preserve-state>
												<SquareBadge
													v-if="
														JSON.parse(
															slot.features.find(
																(feature) => feature.type === 'parent_slot_has_online_status',
															)?.pivot?.data ?? '{\'status\': false}',
														)['status']
													"
													class="brand uppercase">
													{{ $t('main.yes') }}
												</SquareBadge>
												<SquareBadge v-else class="danger capitalize">{{ $t('main.no') }}</SquareBadge>
											</Link>
										</div>
									</td>
									<td v-for="i in 7" class="border-r border-gray-2 text-center">
										<div class="m-3 w-10 text-center">
											{{ getSlotCapacityByWeekday(slot, i) }}
										</div>
									</td>
									<td class="pr-5">
										<div class="ml-3 flex items-center space-x-1">
											<Button
												v-if="['admin', 'manager'].includes(usePage().props.user.type)"
												:href="
													route('club.games.slots.edit', {
														game: game,
														slot: slot,
													})
												"
												class="warning-light xs uppercase"
												type="link">
												<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
											</Button>
											<Button
												v-else
												:href="
													route('club.games.slots.edit', {
														game: game,
														slot: slot,
													})
												"
												class="info-light xs uppercase"
												type="link">
												<InfoIcon class="-mx-0.5 -mt-0.5" />
											</Button>
											<Button
												v-if="['admin', 'manager'].includes(usePage().props.user.type)"
												as="button"
												class="danger-light xs uppercase"
												preserve-scroll
												type="button"
												@click="
													showDialog(capitalize($t('main.are-you-sure')), () => {
														router.visit(
															route('club.games.slots.destroy', {
																game: game,
																slot: slot,
															}),
															{
																method: 'delete',
																preserveScroll: true,
															},
														);
													})
												">
												<TrashIcon class="-mx-0.5 -mt-0.5" />
											</Button>
											<Button
												as="button"
												class="xs !bg-gray-2/40 uppercase !text-gray-5 hover:!bg-gray-5 hover:!text-white"
												@click="slotsExpandedStatuses[slot.id].value = !slotsExpandedStatuses[slot.id].value">
												<ChevronDownIcon v-if="!slots.data[index].expanded" class="w-3" />
												<ChevronUpIcon v-else class="w-3" />
											</Button>
										</div>
									</td>
								</tr>
								<tr
									v-for="(capacityDictionary, index) in subslotQuantitiesGrouped[slot.id][1]"
									:class="{ hidden: slotsExpandedStatuses[slot.id].value === false }">
									<td class="border-r border-t border-gray-2 pl-4">
										{{
											features.book_singular_slot_by_capacity[0].translations['slot'] +
											' ' +
											index +
											features.book_singular_slot_by_capacity[0].translations['capacity-value-postfix-short']
										}}
									</td>
									<td class="border-r border-gray-2 text-center"></td>
									<td v-for="i in 7" class="border-b border-r border-t border-gray-2 py-2.5 text-center">
										{{ subslotQuantitiesGrouped[slot.id][i][index] ?? 0 }}
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<Pagination :meta="slots.meta ?? slots" :table-name="`slots_${game.id}`" class="pt-5" />
				</Card>
			</div>
			<ResponsiveHelper width="3">
				<template #mascot>
					<Mascot :type="parseInt(usePage().props.gameTranslations[game.id]['slot-mascot-type'])" />
				</template>
				<template #button>
					<Button
						:href="usePage().props.gameTranslations[game.id]['slot-help-link']"
						class="grey"
						type="link">
						{{ $t('help.learn-more') }}
					</Button>
				</template>
				{{ usePage().props.gameTranslations[game.id]['slot-help-content'] }}
			</ResponsiveHelper>
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
import Card from '@/Components/Dashboard/Card.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { Feature, Game, Slot } from '@/Types/models';
import { router, usePage, Link } from '@inertiajs/vue3';
import Table from '@/Components/Dashboard/Table.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useString } from '@/Composables/useString';
import ChevronDownIcon from '@/Components/Dashboard/Icons/ChevronDownIcon.vue';
import ChevronUpIcon from '@/Components/Dashboard/Icons/ChevronUpIcon.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';
import { PaginatedResource } from '@/Types/responses';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import InfoIcon from '@/Components/Dashboard/Icons/InfoIcon.vue';
import { usePanelStore } from '@/Stores/panel';
import SquareBadge from '@/Components/Dashboard/Icons/SquareBadge.vue';
import { Ref, ref } from 'vue';

const props = defineProps<{
	game: Game;
	slots: PaginatedResource<Slot>;
}>();

interface subslotQuantities {
	[slotId: number]: {
		[weekday: number]: {
			[capacity: number]: number;
		};
	};
}

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const { capitalize } = useString();
const panelStore = usePanelStore();

const featureTypes = ['slot_has_parent', 'book_singular_slot_by_capacity'];
type FeatureMap = {
	[featureType: string]: Feature[];
};
const features: FeatureMap = featureTypes.reduce<FeatureMap>((acc, type) => {
	acc[type] = props.game.features?.filter((feature) => feature.type === type) ?? [];
	return acc;
}, {});

let slotsExpandedStatuses: { [slotId: number]: Ref<boolean> } = {};

props.slots.data.forEach((slot, index) => {
	slotsExpandedStatuses[slot.id] = ref<boolean>(false);
});

function getSubslotCapacitiesForSlot(slot: Slot): number[] {
	return [
		...new Set(
			slot.childrenSlots
				?.map((item) => {
					const pivotData = item.features.find((f) => f.type === 'book_singular_slot_by_capacity')?.pivot
						?.data;
					return pivotData && parseInt(JSON.parse(String(pivotData)).capacity);
				})
				.filter(Boolean),
		),
	].sort();
}

function groupSubslotQuantities(): subslotQuantities {
	const result: subslotQuantities = {};

	const filterByCapacityAndWeekday = (subslotCapacity: number, weekDay: number) => (slot: any) => {
		const capacityData = parseInt(
			JSON.parse(
				slot.features.find((f: any) => f.type === 'book_singular_slot_by_capacity')?.pivot?.data || '{}',
			).capacity,
		);
		const activeData =
			JSON.parse(
				slot.features.find((f: any) => f.type === 'slot_has_active_status_per_weekday')?.pivot?.data || '{}',
			).active || [];
		return capacityData === subslotCapacity && activeData[weekDay] === true;
	};

	props.slots.data.forEach((slot) => {
		result[slot.id] = {};
		[...Array(7).keys()].forEach((weekDay) => {
			const day = weekDay + 1;
			result[slot.id][day] = {};
			getSubslotCapacitiesForSlot(slot).forEach((subslotCapacity) => {
				result[slot.id][day][subslotCapacity] = (slot.childrenSlots || []).filter(
					filterByCapacityAndWeekday(subslotCapacity, weekDay),
				).length;
			});
		});
	});

	return result;
}

let subslotQuantitiesGrouped: subslotQuantities = groupSubslotQuantities();

function getSlotCapacityByWeekday(slot: Slot, weekDay: number): number {
	return Object.keys(subslotQuantitiesGrouped[slot.id][weekDay]).reduce(
		(acc, key) => acc + parseInt(key) * subslotQuantitiesGrouped[slot.id][weekDay][parseInt(key)],
		0,
	);
}
</script>

<style>
.slot-form .vs__dropdown-option__content,
.slot-form .vs__selected {
	@apply !capitalize;
}
</style>
