<template>
	<PanelLayout :breadcrumbs="[{ href: route('club.sets.index'), label: $t('set.plural') }]">
		<ContentContainer>
			<div class="col-span-12 xl:col-span-9">
				<Card>
					<template #header>
						<div class="flex flex-col space-y-6 md:flex-row-reverse md:items-center md:space-y-0">
							<Button
								v-if="['admin', 'manager'].includes(usePage().props.user.type)"
								class="!px-16"
								:href="route('club.sets.create')"
								type="link">
								{{ $t('set.add-set') }}
							</Button>
							<h2 class="my-0 grow">
								{{ sets.meta.total ? $t('set.current-sets-statuses') : $t('set.no-sets-created') }}
							</h2>
						</div>
					</template>
					<Table
						v-if="sets.meta.total"
						class="mt-5"
						:header="{
							active: capitalize($t('main.active')),
							name: capitalize($t('main.name')),
							today_all: capitalize($t('set.all')),
							today_sold: capitalize($t('set.sold')),
							today_available: capitalize($t('set.available')),
							creator: capitalize($t('main.created-by')),
							created_at: capitalize($t('main.created')),
						}"
						:items="sets"
						table-name="sets">
						<template #cell_active="props">
							<Link
								:href="
									['admin', 'manager'].includes(usePage().props.user.type)
										? route('club.sets.toggle-active', {
												set: props?.item,
										  })
										: null
								"
								as="link"
								class="flex w-full cursor-default items-center justify-center"
								:class="{ 'cursor-pointer': panelStore.isUserRole(['admin', 'manager']) }"
								:method="['admin', 'manager'].includes(usePage().props.user.type) ? 'post' : 'get'"
								preserve-scroll>
								<SquareBadge v-if="props?.item.active" class="brand capitalize">
									{{ $t('main.yes') }}
								</SquareBadge>
								<SquareBadge v-else class="danger capitalize">{{ $t('main.no') }}</SquareBadge>
							</Link>
						</template>

						<template #cell_today_all="props">
							{{ props?.item.quantity[currentWeekDay - 1] }}
						</template>

						<template #cell_today_sold="props">
							{{ props?.item.reservation_slots_count }}
						</template>

						<template #cell_today_available="props">
							{{
								Math.max(0, props?.item.quantity[currentWeekDay - 1] - props?.item.reservation_slots_count)
							}}
						</template>

						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									v-if="['admin', 'manager'].includes(usePage().props.user.type)"
									:href="route('club.sets.edit', { set: props.item.id })"
									class="warning-light xs uppercase"
									type="link">
									<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									v-else
									:href="route('club.sets.edit', { set: props.item.id })"
									class="info info-light xs"
									type="link">
									<InfoIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									v-if="['admin', 'manager'].includes(usePage().props.user.type)"
									:href="route('club.sets.destroy', { set: props.item.id })"
									class="danger-light xs uppercase"
									@click="
										showDialog(capitalize($t('main.are-you-sure')), () => {
											router.visit(
												route('club.sets.destroy', {
													set: props.item,
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

						<template #cell_creator="props">
							{{ props?.item.creator?.email ?? '' }}
						</template>
					</Table>
				</Card>
			</div>
			<ResponsiveHelper width="3">
				<template #mascot>
					<Mascot :type="3" />
				</template>
				<template #button>
					<Button
						:href="usePage().props.generalTranslations['help-sets-link']"
						class="grey xl:!px-0"
						type="link">
						{{ $t('help.learn-more') }}
					</Button>
				</template>
				{{ usePage().props.generalTranslations['help-sets-content'] }}
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
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import Card from '@/Components/Dashboard/Card.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import { Set } from '@/Types/models';
import { PaginatedResource } from '@/Types/responses';
import Table from '@/Components/Dashboard/Table.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import { useString } from '@/Composables/useString';
import { Link, router, usePage } from '@inertiajs/vue3';
import SquareBadge from '@/Components/Dashboard/Icons/SquareBadge.vue';
import dayjs from 'dayjs';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import InfoIcon from '@/Components/Dashboard/Icons/InfoIcon.vue';
import { usePanelStore } from '@/Stores/panel';

const { capitalize } = useString();
const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const page = usePage();
const panelStore = usePanelStore();

const props = defineProps<{
	sets: PaginatedResource<Set>;
}>();

let currentWeekDay = dayjs().day();
currentWeekDay = currentWeekDay === 0 ? 7 : currentWeekDay;
</script>
