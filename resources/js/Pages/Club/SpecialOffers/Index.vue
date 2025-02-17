<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.special-offers.index'),
				label: $t('special-offer.plural'),
			},
		]">
		<ContentContainer>
			<div class="col-span-12 space-y-3 lg:col-span-6">
				<Card class="flex h-full flex-col justify-between" v-if="usePage().props.user.type === 'manager'">
					<template #header>
						<h2 class="text-2xl">{{ $t('special-offer.index-heading') }}</h2>
					</template>
					<Button :href="route('club.special-offers.create')" class="md mt-10 !w-64 !p-0" type="link">
						{{ $t('special-offer.add-special-offer') }}
					</Button>
				</Card>
			</div>
			<div class="col-span-12 space-y-6 lg:col-span-6">
				<WideHelper float="left" class="h-full">
					<template #mascot>
						<Mascot :type="19" class="-ml-2" />
					</template>
					<template #button>
						<Button
							:href="usePage().props.generalTranslations['help-special-offers-link']"
							class="grey w-full sm:w-fit"
							type="link">
							{{ $t('help.learn-more') }}
						</Button>
					</template>
					{{ usePage().props.generalTranslations['help-special-offers-content'] }}
				</WideHelper>
			</div>

			<div class="col-span-12" v-if="specialOffers.data.length > 0">
				<Card class="!px-0 !pt-0">
					<Table
						:centered="['slots', 'duration', 'time_range', 'when_applies', 'when_not_applies']"
						:header="{
							id: capitalize($t('main.number-short')),
							active: capitalize($t('main.active')),
							name: capitalize($t('main.name')),
							game_id: capitalize($t('special-offer.game')),
							value: capitalize($t('main.discount')),
							active_week_days: $t('special-offer.week-day-range'),
							duration: capitalize($t('main.duration')),
							slots: capitalize($t('main.quantity')),
							time_range: capitalize($t('special-offer.time-range')),
							when_applies: $t('special-offer.when-applies'),
							when_not_applies: $t('special-offer.when-not-applies'),
							created_at: capitalize($t('main.created_at')),
							creator: capitalize($t('main.created-by')),
						}"
						:disabled="disabledColumns"
						:items="specialOffers"
						:narrow="['actions']"
						:sortable="['name', 'active']"
						table-name="special_offers">
						<template #cell_active="props">
							<Link
								:href="
									panelStore.authorizeLinkByRole(
										route('club.special-offers.toggle-active', {
											special_offer: props?.item,
										}),
										['admin', 'manager'],
									)
								"
								as="link"
								class="flex w-full cursor-default items-center justify-center"
								:class="{ 'cursor-pointer': panelStore.isUserRole(['admin', 'manager']) }"
								:method="panelStore.isUserRole(['admin', 'manager']) ? 'post' : 'get'"
								preserve-scroll
								preserve-state>
								<SquareBadge v-if="props?.item.active" class="brand capitalize">
									{{ $t('main.yes') }}
								</SquareBadge>
								<SquareBadge v-else class="danger capitalize">{{ $t('main.no') }}</SquareBadge>
							</Link>
						</template>

						<template #header_game="props">
							<div class="mx-auto text-center">
								{{ capitalize($t('special-offer.game')) }}
							</div>
						</template>

						<template #cell_active_week_days="props">
							<div class="flex flex-wrap">
								<div v-for="i in 7" class="px-0.5 pb-1">
									<SquareBadge
										:class="{
											brand: props?.item.active_week_days.includes(i),
											danger: !props?.item.active_week_days.includes(i),
										}">
										{{ $t('main.week-day-short.' + i) }}
									</SquareBadge>
								</div>
							</div>
						</template>

						<template #cell_game="props">
							<GameSquare class="!text-brand-dark">
								<div v-html="props?.item.game.icon" />
							</GameSquare>
						</template>

						<template #cell_value="props">{{ props?.item.value }}%</template>

						<template #cell_slots="props">
							{{ props?.item.slots ?? '-' }}
						</template>

						<template #cell_time_range="props">
							<div v-if="props.item.time_range[props?.item.time_range_type].length">
								<p
									v-for="item in props?.item.time_range[props?.item.time_range_type]"
									class="whitespace-nowrap">
									{{ item.from + ' - ' + item.to }}
								</p>
							</div>
							<div v-else>-</div>
						</template>

						<template #cell_when_applies="props">
							<div v-if="props.item.applies_default">∞</div>
							<div v-else-if="props.item.when_applies.length">
								<p v-for="item in props?.item.when_applies" class="whitespace-nowrap">
									{{ formatDate(item.from) + ' - ' + formatDate(item.to) }}
								</p>
							</div>
							<div v-else>-</div>
						</template>

						<template #cell_when_not_applies="props">
							<div v-if="props.item.when_not_applies.length">
								<p v-for="item in props?.item.when_not_applies" class="whitespace-nowrap">
									{{ formatDate(item.from) + ' - ' + formatDate(item.to) }}
								</p>
							</div>
							<div v-else>-</div>
						</template>

						<template #cell_creator="props">
							{{
								props?.item.creator
									? props?.item.creator.first_name + ' ' + props?.item.creator.last_name
									: ''
							}}
						</template>

						<template #cell_duration="props">
							{{ props?.item.duration ?? '-' }}
						</template>

						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									:href="
										route('club.special-offers.edit', {
											special_offer: props.item.id,
										})
									"
									class="warning-light xs uppercase"
									type="link">
									<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									class="info-light xs uppercase"
									type="link"
									:href="
										route('club.special-offers.clone', {
											special_offer: props.item.id,
										})
									">
									<DocumentDuplicateIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									as="button"
									class="danger-light xs uppercase"
									preserve-scroll
									type="button"
									@click="
										showDialog(capitalize($t('main.are-you-sure')), () => {
											router.visit(
												route('club.special-offers.destroy', {
													special_offer: props.item,
												}),
												{ method: 'delete', preserveScroll: true },
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
import PanelLayout from '@/Layouts/PanelLayout.vue';
import Card from '@/Components/Dashboard/Card.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import { SpecialOffer } from '@/Types/models';
import { Link, router, usePage } from '@inertiajs/vue3';
import Table from '@/Components/Dashboard/Table.vue';
import { useString } from '@/Composables/useString';
import { PaginatedResource } from '@/Types/responses';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import SquareBadge from '@/Components/Dashboard/Icons/SquareBadge.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import GameSquare from '@/Components/Dashboard/GameSquare.vue';
import DocumentDuplicateIcon from '@/Components/Dashboard/Icons/DocumentDuplicateIcon.vue';
import WideHelper from '@/Components/Dashboard/Help/WideHelper.vue';
import { usePanelStore } from '@/Stores/panel';

const props = defineProps<{
	specialOffers: PaginatedResource<SpecialOffer>;
}>();

const { capitalize, pad } = useString();
const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const page = usePage();
const panelStore = usePanelStore();

const disabledColumns = [];
if (!['admin', 'manager'].includes(usePage().props.user.type)) {
	disabledColumns.push('actions');
}

function formatDate(inputDate: string): string {
	const date = new Date(inputDate);
	const day = date.getDate().toString().padStart(2, '0');
	const month = (date.getMonth() + 1).toString().padStart(2, '0');

	return `${day}.${month}`;
}
</script>
