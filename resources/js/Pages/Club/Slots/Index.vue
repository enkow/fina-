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
								v-if="pricelists.length && panelStore.isUserRole(['admin', 'manager'])"
								:href="route('club.games.slots.create', { game: game.id })"
								type="link">
								{{ usePage().props.gameTranslations[game.id]['slot-add'] }}
							</Button>
						</div>
					</template>
					<Table
						v-if="pricelists.length && slots.data.length"
						:disabled="disabledColumns"
						:header="tableHeadings"
						:items="slots"
						:narrow="['active', 'actions']"
						:table-name="`slots_${game.id}`"
						class="mt-6">
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
						<template #cell_subtype="props">
							{{ getSubtypeString(props.item) }}
							({{ getTypeString(props.item) }})
						</template>
						<template #cell_lounge="props">
							<div class="capitalize">
								{{ getLoungeString(props.item) }}
							</div>
						</template>
						<template #cell_type="props">
							{{ getTypeString(props.item) }}
						</template>
						<template
							v-for="feature in game.features.filter(
								(item) => item.id === features.slot_has_convenience?.[0]?.id,
							)"
							v-slot:[`cell_convenience_${feature.id}`]="props">
							<div class="capitalize">
								{{ getConvenienceString(props.item) }}
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
								class="flex w-full cursor-default items-center justify-center"
								:class="{ 'cursor-pointer': panelStore.isUserRole(['admin', 'manager']) }"
								method="post"
								preserve-scroll>
								<SquareBadge v-if="props?.item.active" class="brand capitalize">
									{{ $t('main.yes') }}
								</SquareBadge>
								<SquareBadge v-else class="danger capitalize">{{ $t('main.no') }}</SquareBadge>
							</Link>
						</template>
						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									v-if="panelStore.isUserRole(['admin', 'manager'])"
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
									v-else
									:href="
										route('club.games.slots.edit', {
											game: game,
											slot: props.item.id,
										})
									"
									class="info-light xs uppercase"
									type="link">
									<InfoIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									as="button"
									class="danger-light xs uppercase"
									v-if="panelStore.isUserRole(['admin', 'manager'])"
									@click="
										showDialog(capitalize($t('main.are-you-sure')), () => {
											router.visit(
												route('club.games.slots.destroy', {
													game: game,
													slot: props.item.id,
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
							</div>
						</template>
					</Table>
					<div v-else-if="pricelists.length === 0" class="pt-4">
						{{ usePage().props.gameTranslations[game.id]['slots-no-pricelist-error'] }}
					</div>
				</Card>
			</div>
			<ResponsiveHelper width="3">
				<template #mascot>
					<Mascot :type="parseInt(usePage().props.gameTranslations[game.id]['slot-mascot-type'])" />
				</template>
				<template #button>
					<Button
						:href="usePage().props.gameTranslations[game.id]['slot-help-link']"
						class="grey xl:!px-0"
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
import { Feature, Game, Pricelist, Slot } from '@/Types/models';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { PaginatedResource } from '@/Types/responses';
import Table from '@/Components/Dashboard/Table.vue';
import { useString } from '@/Composables/useString';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import SquareBadge from '@/Components/Dashboard/Icons/SquareBadge.vue';
import NewTabIcon from '@/Components/Dashboard/Icons/NewTabIcon.vue';
import { trans, wTrans } from 'laravel-vue-i18n';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import InfoIcon from '@/Components/Dashboard/Icons/InfoIcon.vue';
import { usePanelStore } from '@/Stores/panel';

const props = defineProps<{
	game: Game;
	pricelists: Pricelist[];
	slots: PaginatedResource<Slot>;
}>();
const panelStore = usePanelStore();

const page = usePage();
const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const { capitalize } = useString();

const featureTypes = ['slot_has_type', 'slot_has_subtype', 'slot_has_lounge', 'slot_has_convenience'];
type FeatureMap = {
	[featureType: string]: Feature[];
};
const features: FeatureMap = featureTypes.reduce<FeatureMap>((acc, type) => {
	acc[type] = props.game.features?.filter((feature) => feature.type === type) ?? [];
	return acc;
}, {});

const disabledColumns = [];

const tableHeadings = {
	name: wTrans('main.name'),
	pricelist: wTrans('main.pricelist'),
	active: wTrans('main.active'),
	subtype: features.slot_has_subtype[0]?.translations['slots-table-heading'] ?? '',
	type: features.slot_has_type[0]?.translations['slots-table-heading'] ?? '',
};
if (
	features.slot_has_lounge.length > 0 &&
	usePage().props.clubSettings['lounges_status_' + features.slot_has_lounge[0].id]?.value === true
) {
	tableHeadings['lounge'] = features.slot_has_lounge[0]?.translations['slots-table-heading'] ?? '';
}
props.game.features
	.filter((item) => item.id === features.slot_has_convenience?.[0]?.id)
	.forEach((convenience) => {
		tableHeadings[`convenience_${convenience.id}`] =
			features.slot_has_convenience[0].translations['slots-table-heading'];
	});

function getSlotFeatureById(slot: Slot, id: number) {
	return slot.features.filter((item) => item.id === id)[0];
}

function getTypeString(slot: Slot) {
	let feature = getSlotFeatureById(slot, features.slot_has_type[0].id);
	let featureString = JSON.parse(feature?.pivot.data ?? JSON.stringify({ name: '---' })).name;
	let translationString = features.slot_has_type[0].translations['type-' + featureString];
	return translationString ?? featureString;
}

function getSubtypeString(slot: Slot) {
	let feature = getSlotFeatureById(slot, features.slot_has_subtype[0].id);
	let featureString = JSON.parse(feature?.pivot.data ?? JSON.stringify( { name: '---' })).name;
	let translationString = features.slot_has_subtype[0].translations['type-' + featureString];
	return translationString ?? featureString;
}

function getLoungeString(slot: Slot) {
	let feature = getSlotFeatureById(slot, features.slot_has_lounge[0].id);
	let data = JSON.parse(feature?.pivot.data ?? JSON.stringify({ status: false }));
	if (!data['status']) {
		return trans('main.no');
	}
	return (
		`${data['min']} - ${data['max']} ` + features.slot_has_lounge[0].translations['lounge-capacity-items']
	);
}

function getConvenienceString(slot: Slot) {
	let feature = getSlotFeatureById(slot, features.slot_has_convenience[0].id);
	let data = JSON.parse(feature?.pivot.data ?? JSON.stringify({ status: false }));
	return trans(`main.${data['status'] ? 'yes' : 'no'}`);
}
</script>
