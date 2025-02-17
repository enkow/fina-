<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.games.pricelists.index', { game: game }),
				label: $t('pricelist.plural'),
			},
			{
				href: route('club.games.pricelists.create', { game: game }),
				label: $t('pricelist.add-game-pricelist', {
					game: $page.props.gameNames[game.id],
				}),
			},
		]">
		<ContentContainer>
			<div class="col-span-12 space-y-5 xl:col-span-9">
				<Card>
					<div class="space-y-5">
						<div class="w-full space-y-1">
							<InputLabel :value="capitalize($t('main.name'))" required />
							<TextInput v-model="pricelistName" />
							<div v-if="$page.props.errors?.createPricelist?.name" class="error">
								{{ $page.props.errors.createPricelist.name }}
							</div>
						</div>
						<div v-for="i in 7">
							<AccordionTab :danger="pricelistDayErrors[i]" class="bolder">
								<template #title>
									{{ capitalize($t(`main.week-day.${i}`)) }}
								</template>
								<div class="mb-4 flex justify-end">
									<SimpleSelect
										v-model="autoReset"
										@update:modelValue="
											(code) => (pricelistDays[i] = JSON.parse(JSON.stringify(pricelistDays[code])))
										"
										:header="$t('pricelist.copy-pricelist')"
										:options="
											[...Array(7).keys()]
												.filter((day) => day + 1 !== i)
												.map((day) => ({
													code: `${day + 1}`,
													label: ucFirst(trans(`main.week-day.${day + 1}`)),
												}))
										"
										:placeholder="capitalize($t('main.action.copy'))"
										class="margins-lg toggle-center toggle-bold toggle-info-base dropdown-separate open-indicator-none w-60"></SimpleSelect>
								</div>
								<div class="space-y-3">
									<div class="grid w-full grid-cols-12 gap-x-3">
										<div class="col-span-4">
											<InputLabel :value="capitalize($t('main.from'))" />
										</div>
										<div class="col-span-4">
											<InputLabel :value="capitalize($t('main.to'))" />
										</div>
										<div class="col-span-4">
											<InputLabel :value="capitalize($t('main.price'))" />
										</div>
									</div>
									<div v-for="(item, index) in pricelistDays[i]" class="grid w-full grid-cols-12 gap-x-3">
										<div class="col-span-4">
											<SimpleSelect
												v-if="index === 0"
												:searchable="true"
												:filterable="true"
												:options="fromOptions"
												v-model="item.from" />
											<TextInput
												v-else
												v-model="pricelistDays[i][index - 1].to"
												readonly
												class="h-12 bg-gray-1 text-base text-gray-4" />
											<div
												v-if="$page.props.errors?.createPricelist?.[`days.${i}.${index}.from`] ?? false"
												class="error">
												{{ $page.props.errors?.createPricelist[`days.${i}.${index}.from`] }}
											</div>
										</div>
										<div class="col-span-4">
											<SimpleSelect
												:searchable="true"
												:filterable="true"
												:options="
													toOptions.filter((option) => {
														return option.code >= (item.from ?? pricelistDays[i][index - 1]?.to);
													})
												"
												v-model="item.to" />
											<div
												v-if="$page.props.errors?.createPricelist?.[`days.${i}.${index}.to`] ?? false"
												class="error">
												{{ $page.props.errors?.createPricelist[`days.${i}.${index}.to`] }}
											</div>
										</div>
										<div class="col-span-4">
											<AmountInput v-model="item.price" class="h-12 text-base" />
											<div
												v-if="$page.props.errors?.createPricelist?.[`days.${i}.${index}.price`] ?? false"
												class="error">
												{{ $page.props.errors?.createPricelist[`days.${i}.${index}.price`] }}
											</div>
										</div>
									</div>
									<div v-if="usePage().props.errors?.createPricelist?.[`days.${i}`]" class="error">
										{{ usePage().props.errors?.createPricelist[`days.${i}`] }}
									</div>
									<div class="flex w-full justify-center space-x-2">
										<MinusButton
											class="!h-12 !w-12"
											v-if="pricelistDays[i].length > 1"
											@click="pricelistDays[i].splice(-1, 1)" />
										<PlusButton
											v-if="pricelistDays[i][pricelistDays[i].length - 1].to !== '24:00'"
											class="!h-12 !w-12"
											@click="
												pricelistDays[i].push({
													from: null,
													to: null,
													price: null,
												})
											" />
									</div>
								</div>
							</AccordionTab>
						</div>
					</div>
					<div v-if="pricelistExceptions.length" class="mt-10">
						<h2 class="mb-5 text-2xl">{{ $t('pricelist-exception.exceptions-in-pricelist') }}</h2>
						<div class="mb-2 hidden grid-cols-12 space-x-3 lg:grid">
							<div
								:class="{
									'col-span-9':
										features.fixed_reservation_duration.length &&
										parseInt(features.fixed_reservation_duration[0].value) === 24,
								}"
								class="col-span-5 space-y-2">
								<InputLabel
									:value="capitalize($t('main.date-from')) + ' - ' + capitalize($t('main.date-to'))" />
							</div>
							<div
								v-if="
									!(
										features.fixed_reservation_duration.length &&
										parseInt(features.fixed_reservation_duration[0].value) === 24
									)
								"
								class="col-span-2 space-y-2">
								<InputLabel :value="capitalize($t('main.from'))" />
							</div>
							<div
								v-if="
									!(
										features.fixed_reservation_duration.length &&
										parseInt(features.fixed_reservation_duration[0].value) === 24
									)
								"
								class="col-span-2 space-y-2">
								<InputLabel :value="capitalize($t('main.to'))" />
							</div>
							<div class="col-span-2 space-y-2">
								<InputLabel :value="capitalize($t('main.price'))" />
							</div>
						</div>
						<div
							v-for="(item, index) in pricelistExceptions"
							class="grid-cols-12 space-y-3 lg:mb-3 lg:grid lg:space-x-3 lg:space-y-0">
							<div
								:class="{
									'col-span-9':
										features.fixed_reservation_duration.length &&
										parseInt(features.fixed_reservation_duration[0].value) === 24,
								}"
								class="col-span-5 space-y-2 lg:space-y-0">
								<InputLabel
									:value="capitalize($t('main.date-from')) + ' - ' + capitalize($t('main.date-to'))"
									class="lg:hidden" />
								<RangeDatepicker :only-future-dates="true" v-model="pricelistExceptions[index].dateRange" />
								<div
									v-if="usePage().props.errors?.createPricelist?.[`exceptions.${index}.dateRange`] ?? false"
									class="error">
									{{ usePage().props.errors?.createPricelist[`exceptions.${index}.dateRange`] }}
								</div>
							</div>
							<div
								v-if="
									!(
										features.fixed_reservation_duration.length &&
										parseInt(features.fixed_reservation_duration[0].value) === 24
									)
								"
								class="col-span-2 space-y-2 lg:space-y-0">
								<InputLabel :value="capitalize($t('main.from'))" class="lg:hidden" />
								<SimpleSelect
									:searchable="true"
									:filterable="true"
									v-model="pricelistExceptions[index].from"
									:options="timeOptionsRendered" />
								<div
									v-if="usePage().props.errors?.createPricelist?.[`exceptions.${index}.from`] ?? false"
									class="error">
									{{ usePage().props.errors?.createPricelist[`exceptions.${index}.from`] }}
								</div>
							</div>
							<div
								v-if="
									!(
										features.fixed_reservation_duration.length &&
										parseInt(features.fixed_reservation_duration[0].value) === 24
									)
								"
								class="col-span-2 space-y-2 lg:space-y-0">
								<InputLabel :value="capitalize($t('main.to'))" class="lg:hidden" />
								<SimpleSelect
									:searchable="true"
									:filterable="true"
									v-model="pricelistExceptions[index].to"
									:options="timeOptionsRendered" />
								<div
									v-if="usePage().props.errors?.createPricelist?.[`exceptions.${index}.to`] ?? false"
									class="error">
									{{ usePage().props.errors?.updatePricelist[`exceptions.${index}.to`] }}
								</div>
							</div>
							<div class="col-span-2 space-y-2 lg:space-y-0">
								<InputLabel :value="capitalize($t('main.price'))" class="lg:hidden" />
								<AmountInput v-model="pricelistExceptions[index].price" />
								<div
									v-if="usePage().props.errors?.createPricelist?.[`exceptions.${index}.price`] ?? false"
									class="error">
									{{ usePage().props.errors?.createPricelist[`exceptions.${index}.price`] }}
								</div>
							</div>
							<div class="col-span-1 space-y-2 lg:space-y-0">
								<TrashButton
									class="hidden !h-12 !w-12 lg:flex"
									@click="pricelistExceptions.splice(index, 1)" />
								<Button class="danger w-full lg:!hidden" @click="pricelistExceptions.splice(index, 1)">
									{{ capitalize($t('main.action.delete')) }}
								</Button>
							</div>
							<hr class="block pb-5 lg:hidden" />
						</div>

						<div v-if="usePage().props.errors?.createPricelist?.['exceptions-overlapping']" class="error">
							{{ usePage().props.errors?.createPricelist['exceptions-overlapping'] }}
						</div>
					</div>
					<div class="mt-5 flex justify-between">
						<Button class="brand-light space-x-3" @click="addPricelistException">
							<PlusIcon class="-mt-0.5" />
							<p>{{ $t('pricelist-exception.add-new-exception') }}</p>
						</Button>
						<Button class="!px-20" @click="storePricelist">
							{{ capitalize($t('main.action.save')) }}
						</Button>
					</div>
				</Card>
			</div>
			<ResponsiveHelper width="3">
				<template #mascot>
					<Mascot :type="16" />
				</template>
				<template #button>
					<Button
						:href="$page.props.generalTranslations['help-pricelists-link']"
						class="grey xl:!px-0"
						type="link">
						{{ $t('help.learn-more') }}
					</Button>
				</template>
				{{ $page.props.generalTranslations['help-pricelists-content'] }}
			</ResponsiveHelper>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Card from '@/Components/Dashboard/Card.vue';
import { Feature, Game } from '@/Types/models';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { router, usePage } from '@inertiajs/vue3';
import { capitalize, computed, Ref, ref, watch } from 'vue';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import PlusButton from '@/Components/Dashboard/Buttons/PlusButton.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import MinusButton from '@/Components/Dashboard/Buttons/MinusButton.vue';
import dayjs from 'dayjs';
import RangeDatepicker from '@/Components/Dashboard/RangeDatepicker.vue';
import TrashButton from '@/Components/Dashboard/Buttons/TrashButton.vue';
import PlusIcon from '@/Components/Dashboard/Icons/PlusIcon.vue';
import { trans } from 'laravel-vue-i18n';
import { ucFirst } from '@/Utils';
import AmountInput from '@/Components/Dashboard/AmountInput.vue';

const props = defineProps<{
	game: Game;
}>();

const { timeOptions } = useSelectOptions();

// features
const featureTypes = ['full_day_reservations', 'fixed_reservation_duration'];
type FeatureMap = {
	[featureType: string]: Feature[];
};
const features: FeatureMap = featureTypes.reduce<FeatureMap>((acc, type) => {
	acc[type] = props.game.features?.filter((feature) => feature.type === type) ?? [];
	return acc;
}, {});

const fromOptions = timeOptions(30);
const toOptions = timeOptions(30);

const timeOptionsRendered = timeOptions(30);

const pricelistName: Ref<string> = ref('');
const pricelistDays = ref<{
	[day: number]: {
		from: string | null;
		to: string | null;
		price: number | null;
	}[];
}>({});
for (let i = 1; i <= 7; i++) {
	pricelistDays.value[i] = [
		{
			from: null,
			to: null,
			price: null,
		},
	];
}

// pricelist exception
const pricelistExceptions: Ref<
	Array<{
		dateRange: Array<string>;
		from: string;
		to: string;
		price: number | string | null;
	}>
> = ref([]);

function addPricelistException(): void {
	pricelistExceptions.value.push({
		dateRange: [dayjs().format('YYYY-MM-DD'), dayjs().format('YYYY-MM-DD')],
		price: null,
		from:
			features.fixed_reservation_duration.length &&
			parseInt(features.fixed_reservation_duration[0].value) === 24
				? '00:00'
				: '10:00',
		to:
			features.fixed_reservation_duration.length &&
			parseInt(features.fixed_reservation_duration[0].value) === 24
				? '24:00'
				: '16:00',
	});
}

watch(
	pricelistDays,
	(newValue) => {
		for (const day in newValue) {
			const dayItem = newValue[day];

			dayItem.sort((a, b) => {
				return a.from && b.from ? a.from.localeCompare(b.from) : 0;
			});

			for (let i = 1; i < newValue[day].length; i++) {
				const item = newValue[day][i];
				const previousItem = newValue[day][i - 1];

				if (item.to && previousItem.to && item.to.localeCompare(previousItem.to) < 0) {
					item.to = previousItem.to;
				}
			}
		}
	},
	{ deep: true },
);

const pricelistDayErrors = computed(() => {
	const days: {
		[day: number]: boolean;
	} = {};

	for (let field in usePage().props.errors?.createPricelist ?? {}) {
		days[+field.split('.')[1]] = true;
	}

	return days;
});

function storePricelist() {
	for (let day in pricelistDays.value) {
		const dayItem = pricelistDays.value[day];

		dayItem.sort((a, b) => {
			return a.from && b.from ? a.from.localeCompare(b.from) : 0;
		});

		for (let i = 1; i < dayItem.length; i++) {
			let item = pricelistDays.value[day][i];
			let previousItem = pricelistDays.value[day][i - 1];

			item.from = previousItem && previousItem.to ? previousItem.to : item.from;
		}
	}

	let pricelistExceptionsData: Array<{
		start_at: string;
		end_at: string;
		from: string;
		to: string;
		price: number;
	}> = [];
	pricelistExceptions.value.forEach((pricelistException) => {
		pricelistExceptionsData.push({
			start_at: pricelistException.dateRange[0],
			end_at: pricelistException.dateRange[1],
			from: pricelistException.from,
			to: pricelistException.to,
			price: pricelistException.price as number,
		});
	});

	router.post(
		route('club.games.pricelists.store', { game: props.game }),
		{
			name: pricelistName.value,
			days: pricelistDays.value,
			exceptions: pricelistExceptionsData,
		},
		{
			errorBag: 'createPricelist',
			preserveScroll: true,
		},
	);
}

const autoReset = ref();
watch(autoReset, () => (autoReset.value = null));
</script>
