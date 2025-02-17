<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.discount-codes.index'),
				label: $t('discount-code.plural'),
			},
		]">
		<ContentContainer>
			<div class="col-span-12 space-y-3 lg:col-span-6">
				<Card class="flex h-full flex-col justify-between" v-if="usePage().props.user.type === 'manager'">
					<template #header>
						<h2 class="text-2xl">{{ $t('discount-code.index-title') }}</h2>
					</template>
					<Button :href="route('club.discount-codes.create')" class="md mt-10 !w-64 !p-0" type="link">
						{{ $t('discount-code.add-discount-code') }}
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
							:href="usePage().props.generalTranslations['help-discount-codes-link']"
							class="grey w-full sm:w-fit"
							type="link">
							{{ $t('help.learn-more') }}
						</Button>
					</template>
					{{ usePage().props.generalTranslations['help-discount-codes-content'] }}
				</WideHelper>
			</div>
			<div class="col-span-12" v-if="discountCodes.data.length > 0">
				<Card class="!px-0 !pt-0">
					<Table
						:centered="['active', 'game']"
						:disabled="disabledColumns"
						:header="{
							id: capitalize($t('main.number-short')),
							type: $t('main.type'),
							code: $t('discount-code.code'),
							code_quantity: $t('discount-code.used-limit'),
							code_quantity_per_customer: $t('discount-code.limit-user'),
							game: capitalize($t('main.type')),
							value: capitalize($t('main.discount')),
							active: capitalize($t('main.active')),
							start_at: capitalize($t('main.start')),
							end_at: $t('discount-code.expires'),
							creator: $t('discount-code.by'),
							actions: $t('actions'),
						}"
						:items="discountCodes"
						:narrow="['active', 'game', 'actions']"
						:nowrap="['code_quantity', 'code_quantity_per_customer']"
						:sortable="['code', 'active', 'start_at', 'end_at']"
						:with-column-edit="true"
						table-name="discount_codes">
						<template #header_game="props">
							<div class="mx-auto text-center">
								{{ capitalize($t('main.type')) }}
							</div>
						</template>

						<template #cell_game="props">
							<GameSquare class="!text-brand-dark">
								<div v-html="props?.item.game.icon" />
							</GameSquare>
						</template>

						<template #cell_start_at="props">
							{{ props.item.start_at ?? dayjs().format('YYYY-MM-DD') + ' 00:00' }}
						</template>

						<template #cell_end_at="props">
							{{ props.item.end_at ?? '∞' }}
						</template>

						<template #cell_id="props">
							{{ props?.item.id }}
						</template>

						<template #cell_code_quantity="props">
							{{
								props?.item.reservations_count +
								'/' +
								(props?.item.code_quantity ? props?.item.code_quantity : '∞')
							}}
						</template>

						<template #cell_code_quantity_per_customer="props">
							{{ props?.item.code_quantity_per_customer ?? '∞' }}
						</template>

						<template #cell_active="props">
							<Link
								:href="
									panelStore.authorizeLinkByRole(
										route('club.discount-codes.toggle-active', {
											discount_code: props?.item,
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

						<template #cell_value="props">
							{{ props?.item.value
							}}{{ props?.item.type === 0 ? '%' : ' ' + usePage().props.user.club.country.currency }}
						</template>

						<template #cell_creator="props">
							{{ props?.item.creator?.email ?? '' }}
						</template>

						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									:href="
										route('club.discount-codes.edit', {
											discount_code: props.item.id,
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
										route('club.discount-codes.clone', {
											discount_code: props.item.id,
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
												route('club.discount-codes.destroy', {
													discount_code: props.item,
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
import Table from '@/Components/Dashboard/Table.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import DocumentDuplicateIcon from '@/Components/Dashboard/Icons/DocumentDuplicateIcon.vue';
import SquareBadge from '@/Components/Dashboard/Icons/SquareBadge.vue';
import { useString } from '@/Composables/useString';
import { Link, router, usePage } from '@inertiajs/vue3';
import { PaginatedResource } from '@/Types/responses';
import { DiscountCode } from '@/Types/models';
import { useToast } from 'vue-toastification';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import dayjs from 'dayjs';
import GameSquare from '@/Components/Dashboard/GameSquare.vue';
import WideHelper from '@/Components/Dashboard/Help/WideHelper.vue';
import { usePanelStore } from '@/Stores/panel';

const props = defineProps<{
	discountCodes: PaginatedResource<DiscountCode>;
}>();
const panelStore = usePanelStore();
const page = usePage();

const disabledColumns = ['club', 'type'];
if (!['admin', 'manager'].includes(usePage().props.user.type)) {
	disabledColumns.push('actions');
}

const { capitalize } = useString();
const toast = useToast();
const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
</script>
