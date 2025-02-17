<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.games.pricelists.index', { game: game }),
				label: $t('pricelist.plural'),
			},
		]">
		<ContentContainer>
			<div class="col-span-12 flex flex-wrap space-x-0 space-y-3 sm:flex-nowrap sm:space-x-3 sm:space-y-0">
				<GameFilter
					:searchable="false"
					:game="game"
					custom-route="club.games.pricelists.index"
					table-name="pricelists" />
				<Button
					v-if="['admin', 'manager'].includes(usePage().props.user.type)"
					:href="route('club.games.pricelists.create', { game: game })"
					class="shrink-0 !px-16"
					type="link">
					{{ $t('pricelist.add-pricelist') }}
				</Button>
			</div>
			<div class="col-span-12 grid grid-cols-12 gap-x-5 gap-y-5">
				<div class="col-span-12 space-y-5 xl:col-span-9">
					<Card>
						<Table
							v-if="pricelists.data.length"
							:header="{
								name: capitalize($t('main.name')),
							}"
							:items="pricelists"
							:narrow="['actions']"
							table-name="pricelists">
							<template #cell_actions="props">
								<div class="flex items-center space-x-1">
									<Button
										v-if="['admin', 'manager'].includes(usePage().props.user.type)"
										:href="
											route('club.games.pricelists.edit', {
												game: game,
												pricelist: props.item.id,
											})
										"
										class="warning-light xs uppercase"
										type="link">
										<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
									</Button>
									<Button
										v-else
										:href="
											route('club.games.pricelists.edit', {
												game: game,
												pricelist: props.item.id,
											})
										"
										class="info info-light xs"
										type="link">
										<InfoIcon class="-mx-0.5 -mt-0.5" />
									</Button>
									<Button
										v-if="['admin', 'manager'].includes(usePage().props.user.type)"
										as="button"
										class="danger-light xs uppercase"
										@click="
											showDialog(capitalize($t('main.are-you-sure')), () => {
												router.visit(
													route('club.games.pricelists.destroy', {
														game: game,
														pricelist: props.item.id,
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
						<p v-else>
							{{ $t('pricelist.no-pricelist-info') }}
						</p>
					</Card>
					<AccordionTab
						v-if="features['price_per_person'].length && pricePerPersonTypeSetting['value'] === 1"
						class="bolder">
						<template #title>
							{{ pricePerPersonSetting.translations['pricelist-title'] }}
						</template>
						{{ pricePerPersonSetting.translations['pricelist-description'] }}
						<form class="mt-5 flex w-full space-x-2" @submit.prevent="submitPricePerPersonSettingForm">
							<div class="w-1/2">
								<AmountInput
									v-model="pricePerPersonValue"
									:placeholder="capitalize($t('main.price'))"
									:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
									:class="{
										'disabled-readable': !['admin', 'manager'].includes(usePage().props.user.type),
									}" />
							</div>
							<div class="w-1/2">
								<Button
									class="w-full"
									v-if="['admin', 'manager'].includes(usePage().props.user.type)"
									type="submit">
									{{ capitalize($t('main.action.save')) }}
								</Button>
								<Button
									class="disabled w-full"
									v-tippy="{ allowHTML: true }"
									:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'"
									v-else>
									{{ capitalize($t('main.action.save')) }}
								</Button>
							</div>
						</form>
					</AccordionTab>
				</div>
				<ResponsiveHelper width="3">
					<template #mascot>
						<Mascot :type="16" />
					</template>
					<template #button>
						<Button
							:href="usePage().props.generalTranslations['help-pricelists-link']"
							class="grey xl:!px-0"
							type="link">
							{{ $t('help.learn-more') }}
						</Button>
					</template>
					{{ usePage().props.generalTranslations['help-pricelists-content'] }}
				</ResponsiveHelper>
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
import Card from '@/Components/Dashboard/Card.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import { router, usePage } from '@inertiajs/vue3';
import GameFilter from '@/Components/Dashboard/GameFilter.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import Table from '@/Components/Dashboard/Table.vue';
import { PaginatedResource } from '@/Types/responses';
import { Feature, Game, Pricelist } from '@/Types/models';
import { useString } from '@/Composables/useString';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import { Ref, ref } from 'vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { useNumber } from '@/Composables/useNumber';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import AmountInput from '@/Components/Dashboard/AmountInput.vue';
import InfoIcon from '@/Components/Dashboard/Icons/InfoIcon.vue';

const { capitalize } = useString();
const { formattedFloat } = useNumber();
const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();

const props = defineProps<{
	game: Game;
	pricelists: PaginatedResource<Pricelist>;
}>();

const featureTypes = ['price_per_person'];

type FeatureMap = {
	[featureType: string]: Feature[];
};

const features: FeatureMap = featureTypes.reduce<FeatureMap>((acc, type) => {
	acc[type] = props.game.features?.filter((feature) => feature.type === type) ?? [];
	return acc;
}, {});

const pricePerPersonFeature: Feature = features.price_per_person[0];

const pricePerPersonTypeSetting: Ref<Record<string, any>> =
	usePage().props.clubSettings[
		['price_per_person_type' ?? '', pricePerPersonFeature?.id.toString() ?? ''].join('_')
	];
const pricePerPersonSetting: Ref<Record<string, any>> =
	usePage().props.clubSettings[
		['price_per_person' ?? '', pricePerPersonFeature?.id.toString() ?? ''].join('_')
	];

const pricePerPersonValue: Ref<string> = ref(((pricePerPersonSetting?.value ?? 0) / 100).toString());

function submitPricePerPersonSettingForm() {
	router.post(
		route('club.settings.update', {
			key: 'price_per_person',
		}),
		{
			feature_id: pricePerPersonFeature.id,
			value: parseInt(
				formattedFloat(pricePerPersonValue.value.replace(',', '.'), 2)
					.toString()
					.replace('.', '')
					.replace(' ', ''),
			),
		},
		{
			preserveScroll: true,
		},
	);
}
</script>
