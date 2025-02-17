<template>
	<PanelLayout :breadcrumbs="[{ href: route('club.agreements.index'), label: $t('agreement.plural') }]">
		<ContentContainer>
			<div class="col-span-12 xl:col-span-9">
				<Card class="!px-0 !pb-0">
					<template #header>
						<div class="flex items-center justify-between px-7">
							<div class="text-2xl font-medium">
								{{ $t('agreement.plural') }}
							</div>
						</div>
					</template>

					<Table
						:centered="['active', 'required', 'actions']"
						:header="{
							type: capitalize($t('main.type')),
							name: capitalize($t('main.name')),
							active: capitalize($t('main.active')),
							required: capitalize($t('main.required')),
							is_filled: capitalize($t('agreement.is-filled')),
						}"
						:items="agreements"
						:narrow="['actions', 'active', 'required']"
						class="mt-8"
						table-name="agreements">
						<template #cell_is_filled="props">
							{{ $t('main.' + (props.item.is_filled ? 'yes' : 'no')) }}
						</template>
						<template #cell_type="props">
							{{ $t(`agreement.types.${props.item.type}`) }}
						</template>
						<template #cell_active="props">
							<div class="flex justify-center">
								<div
									:class="{ 'cursor-pointer': panelStore.isUserRole(['admin', 'manager']) }"
									@click="
										panelStore.isUserRole(['admin', 'manager'])
											? router.visit(
													route('club.agreements.toggleActive', {
														agreement: props.item.id,
													}),
													{ method: 'post' },
											  )
											: null
									">
									<SuccessSquareIcon v-if="props?.item.active" />
									<DangerSquareIcon v-else />
								</div>
							</div>
						</template>
						<template #cell_file="props">
							<a :href="`/club-assets/agreements/${props.item.file}`" target="_blank">
								{{ $t('agreement.link') }}
							</a>
						</template>
						<template #cell_required="props">
							<div class="flex justify-center">
								<div
									:class="{ 'cursor-pointer': panelStore.isUserRole(['admin', 'manager']) }"
									@click="
										panelStore.isUserRole(['admin', 'manager'])
											? router.visit(
													route('club.agreements.toggleRequired', {
														agreement: props.item.id,
													}),
													{ method: 'post' },
											  )
											: null
									">
									<SuccessSquareIcon v-if="props?.item.required" />
									<DangerSquareIcon v-else />
								</div>
							</div>
						</template>
						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									v-if="panelStore.isUserRole(['admin', 'manager'])"
									:href="route('club.agreements.edit', { agreement: props.item.id })"
									class="warning-light xs uppercase"
									type="link">
									<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									v-else
									:href="route('club.agreements.edit', { agreement: props.item.id })"
									class="info info-light xs"
									type="link">
									<InfoIcon class="-mx-0.5 -mt-0.5" />
								</Button>
							</div>
						</template>
					</Table>
				</Card>
			</div>
			<ResponsiveHelper width="3">
				<template #mascot>
					<Mascot :type="15" />
				</template>
				<template #button>
					<Button
						:href="usePage().props.generalTranslations['help-agreements-link']"
						class="grey xl:!px-0"
						type="link">
						{{ $t('help.learn-more') }}
					</Button>
				</template>
				{{ usePage().props.generalTranslations['help-agreements-content'] }}
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
import { capitalize } from 'vue';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Table from '@/Components/Dashboard/Table.vue';
import { Agreement } from '@/Types/models';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import Card from '@/Components/Dashboard/Card.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { router, usePage } from '@inertiajs/vue3';
import SuccessSquareIcon from '@/Components/Dashboard/Icons/SuccessSquareIcon.vue';
import DangerSquareIcon from '@/Components/Dashboard/Icons/DangerSquareIcon.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import InfoIcon from '@/Components/Dashboard/Icons/InfoIcon.vue';
import { usePanelStore } from '@/Stores/panel';

const panelStore = usePanelStore();
const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const page = usePage();

const props = defineProps<{
	agreements: Agreement;
}>();
</script>
