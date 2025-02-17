<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.games.pricelists.index', { game: game }),
				label: $t('pricelist.plural'),
			},
			{
				href: route('club.games.pricelists.edit', {
					game: game,
					pricelist: pricelist,
				}),
				label: $t('pricelist.edit-game-pricelist', {
					game: usePage().props.gameNames[game.id],
				}),
			},
		]">
		<ContentContainer>
			<div class="col-span-12 space-y-5 xl:col-span-9">
				<Card>
					<div class="space-y-5">
						<div class="w-full space-y-1">
							<InputLabel :value="capitalize($t('main.name'))" required />
							<TextInput
								v-model="pricelistName"
								:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
								:class="{ 'disabled-readable': !['admin', 'manager'].includes(usePage().props.user.type) }" />
							<div v-if="usePage().props.errors?.updatePricelist?.name" class="error">
								{{ usePage().props.errors.updatePricelist.name }}
							</div>
						</div>
						<div v-for="i in 7" v-if="features.fixed_reservation_duration.length === 0">
							<AccordionTab :danger="pricelistDayErrors[i]" class="bolder">
								<template #title>
									{{ capitalize($t(`main.week-day.${i}`)) }}
								</template>
								<div class="mb-4 flex justify-end">
									<SimpleSelect
										v-if="['admin', 'manager'].includes(usePage().props.user.type)"
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
											<div class="relative">
												<InputLabel :value="capitalize($t('main.price'))" />
												<div class="absolute right-3 top-11.5 text-sm">PLN</div>
											</div>
										</div>
									</div>
									<div v-for="(item, index) in pricelistDays[i]" class="grid w-full grid-cols-12 gap-x-3">
										<div class="col-span-4">
											<SimpleSelect
												v-if="index === 0"
												:searchable="true"
												:filterable="true"
												:options="fromOptions"
												v-model="item.from"
												:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
												:class="{
													'disabled-readable': !['admin', 'manager'].includes(usePage().props.user.type),
												}" />
											<TextInput
												v-else
												v-model="pricelistDays[i][index - 1].to"
												readonly
												class="h-12 bg-gray-1 text-base text-gray-4" />
											<div
												v-if="usePage().props.errors?.updatePricelist?.[`days.${i}.${index}.from`] ?? false"
												class="error">
												{{ usePage().props.errors?.updatePricelist[`days.${i}.${index}.from`] }}
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
												v-model="item.to"
												:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
												:class="{
													'disabled-readable': !['admin', 'manager'].includes(usePage().props.user.type),
												}" />
											<div
												v-if="usePage().props.errors?.updatePricelist?.[`days.${i}.${index}.to`] ?? false"
												class="error">
												{{ usePage().props.errors?.updatePricelist[`days.${i}.${index}.to`] }}
											</div>
										</div>
										<div class="col-span-4">
											<AmountInput
												v-model="item.price"
												:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
												:class="{
													'disabled-readable': !['admin', 'manager'].includes(usePage().props.user.type),
												}" />
											<div
												v-if="usePage().props.errors?.updatePricelist?.[`days.${i}.${index}.price`] ?? false"
												class="error">
												{{ usePage().props.errors?.updatePricelist[`days.${i}.${index}.price`] }}
											</div>
										</div>
									</div>

									<div v-if="usePage().props.errors?.updatePricelist?.[`days.${i}`]" class="error">
										{{ usePage().props.errors?.updatePricelist[`days.${i}`] }}
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
						<div v-else>
							<div class="flex w-full py-6 font-semibold">
								<div class="w-1/2 pl-2">
									{{ capitalize($t('main.week-day.singular')) }}
								</div>
								<div class="w-1/2">{{ $t('pricelist.price-per-person') }}</div>
							</div>
							<div v-for="i in 7" class="flex w-full border-t border-gray-9">
								<div class="flex w-1/2 items-center pl-2">
									{{ capitalize($t(`main.week-day.${i}`)) }}
								</div>
								<div class="w-1/2 py-3">
									<TextInput v-model="pricelistDays[i][0].price" />
									<div
										v-if="usePage().props.errors?.updatePricelist?.[`days.${i}.0.price`] ?? false"
										class="error">
										{{ usePage().props.errors?.updatePricelist[`days.${i}.0.price`] }}
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="mt-5 flex w-full justify-end">
						<Button
							v-if="!['admin', 'manager'].includes(usePage().props.user.type)"
							class="disabled !px-20"
							v-tippy="{ allowHTML: true }"
							:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'">
							{{ $t('pricelist.update-pricelist') }}
						</Button>
						<Button v-else class="!px-20" @click="updatePricelist">
							{{ $t('pricelist.update-pricelist') }}
						</Button>
					</div>
				</Card>
				<Card>
					<template #header>
						<h2>{{ $t('pricelist-exception.exceptions-in-pricelist') }}</h2>
					</template>
					<template #description>
						{{ $t('pricelist-exception.exceptions-in-pricelist-description') }}
					</template>
					<div v-if="pricelistExceptions.length">
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
								<RangeDatepicker
									:only-future-dates="true"
									class="text-base"
									v-model="pricelistExceptions[index].dateRange"
									:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
									:class="{
										'disabled-readable': !['admin', 'manager'].includes(usePage().props.user.type),
									}" />
								<div
									v-if="usePage().props.errors?.updatePricelistExceptions?.[`${index}.dateRange`] ?? false"
									class="error">
									{{ usePage().props.errors?.updatePricelistExceptions[`${index}.dateRange`] }}
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
									:options="timeOptionsRendered"
									:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
									:class="{
										'disabled-readable': !['admin', 'manager'].includes(usePage().props.user.type),
									}" />
								<div
									v-if="usePage().props.errors?.updatePricelistExceptions?.[`${index}.from`] ?? false"
									class="error">
									{{ usePage().props.errors?.updatePricelistExceptions[`${index}.from`] }}
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
									:options="timeOptionsRendered"
									:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
									:class="{
										'disabled-readable': !['admin', 'manager'].includes(usePage().props.user.type),
									}" />
								<div
									v-if="usePage().props.errors?.updatePricelistExceptions?.[`${index}.to`] ?? false"
									class="error">
									{{ usePage().props.errors?.updatePricelist[`${index}.to`] }}
								</div>
							</div>
							<div class="col-span-2 space-y-2 lg:space-y-0">
								<InputLabel :value="capitalize($t('main.price'))" class="lg:hidden" />
								<AmountInput
									class="text-base"
									v-model="pricelistExceptions[index].price"
									:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
									:class="{
										'disabled-readable': !['admin', 'manager'].includes(usePage().props.user.type),
									}" />
								<div
									v-if="
										usePage().props.errors?.updatePricelistExceptions?.[`entries.${index}.price`] ?? false
									"
									class="error">
									{{ usePage().props.errors?.updatePricelistExceptions[`entries.${index}.price`] }}
								</div>
							</div>
							<div
								class="col-span-1 space-y-2 lg:space-y-0"
								v-if="['admin', 'manager'].includes(usePage().props.user.type)">
								<TrashButton class="hidden !h-12 !w-12 lg:flex" @click="removePricelistException(index)" />
								<Button class="danger w-full lg:!hidden" @click="removePricelistException(index)">
									{{ capitalize($t('main.action.delete')) }}
								</Button>
							</div>
							<hr class="block pb-5 lg:hidden" />
						</div>

						<div v-if="usePage().props.errors?.updatePricelistExceptions?.['overlapping']" class="error">
							{{ usePage().props.errors?.updatePricelistExceptions['overlapping'] }}
						</div>
					</div>
					<div
						class="mt-5 flex justify-between"
						v-if="['admin', 'manager'].includes(usePage().props.user.type)">
						<Button class="brand-light space-x-3" @click="addPricelistException">
							<PlusIcon class="-mt-0.5" />
							<p>{{ $t('pricelist-exception.add-new-exception') }}</p>
						</Button>
						<Button v-if="pricelistExceptions.length" @click="updatePricelistExceptions">
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
						:href="usePage().props.generalTranslations['help-pricelists-link']"
						class="grey xl:!px-0"
						type="link">
						{{ $t('help.learn-more') }}
					</Button>
				</template>
				{{ usePage().props.generalTranslations['help-pricelists-content'] }}
			</ResponsiveHelper>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Card from '@/Components/Dashboard/Card.vue';
import { Feature, Game, Pricelist, PricelistException, PricelistItem, SelectOption } from '@/Types/models';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { router, usePage } from '@inertiajs/vue3';
import { capitalize, computed, Ref, ref, watch } from 'vue';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import TrashButton from '@/Components/Dashboard/Buttons/TrashButton.vue';
import PlusButton from '@/Components/Dashboard/Buttons/PlusButton.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import { trans, wTrans } from 'laravel-vue-i18n';
import { useString } from '@/Composables/useString';
import PlusIcon from '@/Components/Dashboard/Icons/PlusIcon.vue';
import RangeDatepicker from '@/Components/Dashboard/RangeDatepicker.vue';
import dayjs, { Dayjs } from 'dayjs';
import MinusButton from '@/Components/Dashboard/Buttons/MinusButton.vue';
import { ucFirst } from '@/Utils';
import AmountInput from '@/Components/Dashboard/AmountInput.vue';

const props = defineProps<{
	pricelist: Pricelist;
	game: Game;
}>();

// composables
const { timeOptions } = useSelectOptions();
const { capitalize } = useString();

const timeOptionsRendered = timeOptions(30);
const fromOptions = timeOptions(30);
const toOptions = timeOptions(30);

// features
const featureTypes = ['full_day_reservations', 'fixed_reservation_duration'];
type FeatureMap = {
	[featureType: string]: Feature[];
};
const features: FeatureMap = featureTypes.reduce<FeatureMap>((acc, type) => {
	acc[type] = props.game.features?.filter((feature) => feature.type === type) ?? [];
	return acc;
}, {});

// pricelist
const pricelistDays = ref<{
	[day: number]: {
		from: string;
		to: string;
		price: number;
	}[];
}>({});

props.pricelist.pricelistItems?.forEach((pricelistItem: PricelistItem) => {
	if (!pricelistDays.value[pricelistItem.day]) {
		pricelistDays.value[pricelistItem.day] = [];
	}
	pricelistDays.value[pricelistItem.day].push({
		from: pricelistItem.from,
		to: pricelistItem.to,
		price: pricelistItem.price / 100,
	});
});

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

				if (!previousItem) continue;

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

	for (let field in usePage().props.errors?.updatePricelist ?? {}) {
		days[+field.split('.')[1]] = true;
	}

	return days;
});

const pricelistName: Ref<string | undefined> = ref(props.pricelist.name);

function updatePricelist(): void {
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

	router.put(
		route('club.games.pricelists.update', {
			game: props.game,
			pricelist: props.pricelist,
		}),
		{
			name: pricelistName.value,
			days: pricelistDays.value,
		},
		{
			errorBag: 'updatePricelist',
			preserveScroll: true,
		},
	);
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
props.pricelist.pricelistExceptions?.forEach((pricelistException: PricelistException) => {
	pricelistExceptions.value.push({
		dateRange: [pricelistException.start_at, pricelistException.end_at],
		from: pricelistException.from,
		to: pricelistException.to,
		price: pricelistException.price / 100,
	});
});

function updatePricelistExceptions(): void {
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
		route('club.games.pricelists.pricelist-exceptions.store', {
			game: props.game,
			pricelist: props.pricelist,
		}),
		{ entries: pricelistExceptionsData },
		{
			errorBag: 'updatePricelistExceptions',
			preserveScroll: true,
		},
	);
}

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

function removePricelistException(index: number): void {
	pricelistExceptions.value.splice(index, 1);
	if (pricelistExceptions.value.length === 0) {
		updatePricelistExceptions();
	}
}

const autoReset = ref();
watch(autoReset, () => (autoReset.value = null));
</script>
