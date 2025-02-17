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

					<Table
						v-if="slots.data.length"
						:disabled="disabledColumns"
						:header="tableHeadings"
						:items="slots"
						:narrow="[
							'id',
							'active',
							'actions',
							'online_status',
							'capacity_1',
							'capacity_2',
							'capacity_3',
							'capacity_4',
							'capacity_5',
							'capacity_6',
							'capacity_7',
						]"
						:table-name="`slots_${game.id}`"
						class="mt-4">
						<template #cell_pricelist="props">
							<div class="flex items-center space-x-2">
								<p>{{ props.item.pricelist.name }}</p>
								<Link
									:href="
										route('club.games.pricelists.edit', {
											game: game.id,
											pricelist: props.item.pricelist.id,
										})
									">
									<NewTabIcon class="text-info-base" />
								</Link>
							</div>
						</template>
						<template
							v-for="weekday in [...Array(7).keys()].map((item) => item + 1)"
							v-slot:[`header_capacity_${weekday}`]="props">
							<div class="w-10 text-center capitalize">
								<p>
									{{ $t(`main.week-day-short.${weekday}`) }}
								</p>
							</div>
						</template>
						<template v-for="weekday in [...Array(7).keys()]" v-slot:[`cell_capacity_${weekday+1}`]="props">
							<div class="text-center">
								{{ getSlotCapacityByWeekday(props.item, weekday + 1) }}
							</div>
						</template>
						<template #cell_active="props">
							<Link
								:href="
									panelStore.authorizeLinkByRole(
										route('club.games.slots.toggle-active', {
											game: game,
											slot: props.item,
										}),
										['admin', 'manager'],
									)
								"
								as="link"
								class="flex w-full cursor-pointer items-center justify-center"
								method="post"
								preserve-scroll>
								<SquareBadge v-if="props?.item.active" class="brand capitalize">
									{{ $t('main.yes') }}
								</SquareBadge>
								<SquareBadge v-else class="danger capitalize">{{ $t('main.no') }}</SquareBadge>
							</Link>
						</template>
						<template #header_online_status="props">
							<div class="capitalize">
								<p class="text-xs font-normal">
									{{ tableHeadings.online_status.split(' ')[0] }}
								</p>
								<p>
									{{ tableHeadings.online_status.split(' ')[1] }}
								</p>
							</div>
						</template>
						<template #cell_online_status="props">
							<Link
								:href="
									panelStore.authorizeLinkByRole(
										route('club.games.slots.toggle-online-status', {
											game: game,
											slot: props.item,
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
								preserve-scroll>
								<SquareBadge
									v-if="
										JSON.parse(
											props.item.features.find((feature) => feature.type === 'parent_slot_has_online_status')
												?.pivot?.data ?? '{\'status\': false}',
										)['status']
									"
									class="brand uppercase">
									{{ $t('main.yes') }}
								</SquareBadge>
								<SquareBadge v-else class="danger capitalize">{{ $t('main.no') }}</SquareBadge>
							</Link>
						</template>
						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									:href="
										route('club.games.slots.edit', {
											game: game,
											slot: props.item.id,
										})
									"
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
											router.visit(
												route('club.games.slots.destroy', {
													game: game,
													slot: props.item,
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
							</div>
						</template>
					</Table>
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
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import { Feature, Game, Slot } from '@/Types/models';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { PaginatedResource } from '@/Types/responses';
import Table from '@/Components/Dashboard/Table.vue';
import { useString } from '@/Composables/useString';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import SquareBadge from '@/Components/Dashboard/Icons/SquareBadge.vue';
import NewTabIcon from '@/Components/Dashboard/Icons/NewTabIcon.vue';
import { wTrans } from 'laravel-vue-i18n';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import { usePanelStore } from '@/Stores/panel';

const props = defineProps<{
	game: Game;
	slots: PaginatedResource<Slot>;
}>();
const page = usePage();
const panelStore = usePanelStore();

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const { capitalize } = useString();

const featureTypes = ['person_as_slot'];
type FeatureMap = {
	[featureType: string]: Feature[];
};
const features: FeatureMap = featureTypes.reduce<FeatureMap>((acc, type) => {
	acc[type] = props.game.features?.filter((feature) => feature.type === type) ?? [];
	return acc;
}, {});

const tableHeadings = {
	id: wTrans('main.number'),
	name: wTrans('main.name'),
	pricelist: wTrans('main.pricelist'),
	active: wTrans('main.active'),
	online_status: props.game.features?.find(
		(feature: Feature) => feature.type === 'parent_slot_has_online_status',
	)?.translations['active-online'],
};

const disabledColumns = ['active'];
if (!['admin', 'manager'].includes(usePage().props.user.type)) {
	disabledColumns.push('actions');
}

function getSlotCapacityByWeekday(slot: Slot, weekDay: number) {
	return slot.childrenSlots?.reduce(
		(acc, item) =>
			acc +
			(JSON.parse(
				String(item.features.filter((feature) => feature.type === 'person_as_slot')[0]?.pivot?.data),
			)['active'][weekDay - 1]
				? 1
				: 0),
		0,
	);
}
</script>
