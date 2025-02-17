<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.games.slots.index', { game: game }),
				label: usePage().props.gameTranslations[game.id]['slot-plural'],
			},
		]">
		<ContentContainer>
			<div class="col-span-12 flex flex-wrap space-x-0 space-y-3 sm:flex-nowrap sm:space-x-3 sm:space-y-0">
				<GameFilter
					:game="game"
					class="hidden md:block"
					custom-route="club.games.pricelists.index"
					table-name="pricelists" />
				<GameFilter
					:game="game"
					class="block !w-full md:hidden"
					custom-route="club.games.pricelists.index"
					table-name="pricelists" />
			</div>
			<div class="col-span-12 grid grid-cols-12 gap-x-5 gap-y-5">
				<div class="col-span-12 space-y-5 xl:col-span-9">
					<Card class="!px-0">
						<template #header>
							<div class="flex justify-between pl-8">
								<h2>
									{{ usePage().props.gameTranslations[game.id]['slot-plural'] }}
								</h2>
							</div>
						</template>
						<div class="w-full">
							<div class="flex w-full px-10 py-6 font-semibold">
								<div class="w-1/2">
									{{ capitalize($t('main.week-day.singular')) }}
								</div>
								<div class="w-1/2">{{ $t('pricelist.price-per-person') }}</div>
							</div>
							<div v-for="i in 7" class="flex w-full border-t border-gray-9 px-10">
								<div class="flex w-1/2 items-center">
									{{ capitalize($t(`main.week-day.${i}`)) }}
								</div>
								<div class="w-1/2 py-3">
									<AmountInput
										v-model="pricelistDays[i][0].price"
										:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
										:class="{
											'disabled-readable': !['admin', 'manager'].includes(usePage().props.user.type),
										}" />
									<div
										v-if="usePage().props.errors?.updatePricelist?.[`days.${i}.0.price`] ?? false"
										class="error">
										{{ usePage().props.errors?.updatePricelist[`days.${i}.0.price`] }}
									</div>
								</div>
							</div>
						</div>
						<div class="mt-5 flex w-full justify-end pr-10">
							<Button
								@click="updatePricelist"
								v-if="['admin', 'manager'].includes(usePage().props.user.type)">
								{{ capitalize($t('main.action.save')) }}
							</Button>
							<Button
								v-else
								class="disabled"
								v-tippy="{ allowHTML: true }"
								:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'">
								{{ capitalize($t('main.action.save')) }}
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
							<div class="mb-3 grid grid-cols-12 space-x-3">
								<div class="col-span-5 space-y-2">
									<InputLabel
										:value="capitalize($t('main.date-from')) + ' - ' + capitalize($t('main.date-to'))" />
								</div>
								<div class="col-span-2 space-y-2">
									<InputLabel :value="capitalize($t('main.from'))" />
								</div>
								<div class="col-span-2 space-y-2">
									<InputLabel :value="capitalize($t('main.to'))" />
								</div>
								<div class="col-span-2 space-y-2">
									<InputLabel :value="capitalize($t('main.price'))" />
								</div>
							</div>
							<div v-for="(item, index) in pricelistExceptions" class="grid grid-cols-12 space-x-3">
								<div class="col-span-5 space-y-2">
									<RangeDatepicker :only-future-dates="true" v-model="pricelistExceptions[index].dateRange" />
									<div
										v-if="usePage().props.errors?.updatePricelistExceptions?.[`${index}.dateRange`] ?? false"
										class="error">
										{{ usePage().props.errors?.updatePricelistExceptions[`${index}.dateRange`] }}
									</div>
									<div
										v-if="usePage().props.errors?.updatePricelistExceptions?.[`overlapping`] && index === pricelistExceptions.length - 1"
										class="error">
										{{ usePage().props.errors?.updatePricelistExceptions[`overlapping`] }}
									</div>
								</div>
								<div class="col-span-2 space-y-2">
									<SimpleSelect
										:searchable="true"
										:filterable="true"
										v-model="pricelistExceptions[index].from"
										:options="fromOptions" />
									<div
										v-if="usePage().props.errors?.updatePricelistExceptions?.[`${index}.from`] ?? false"
										class="error">
										{{ usePage().props.errors?.updatePricelistExceptions[`${index}.from`] }}
									</div>
								</div>
								<div class="col-span-2 space-y-2">
									<SimpleSelect
										:searchable="true"
										:filterable="true"
										v-model="pricelistExceptions[index].to"
										:options="toOptions" />
									<div
										v-if="usePage().props.errors?.updatePricelistExceptions?.[`${index}.to`] ?? false"
										class="error">
										{{ usePage().props.errors?.updatePricelist[`${index}.to`] }}
									</div>
								</div>
								<div class="col-span-2 space-y-2">
									<AmountInput v-model="pricelistExceptions[index].price" />
									<div
										v-if="usePage().props.errors?.updatePricelistExceptions?.[`${index}.price`] ?? false"
										class="error">
										{{ usePage().props.errors?.updatePricelistExceptions[`${index}.price`] }}
									</div>
								</div>
								<div class="col-span-1">
									<TrashButton class="hidden !h-12 !w-12 lg:flex" @click="removePricelistException(index)" />
								</div>
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
						<Mascot :type="parseInt(usePage().props.gameTranslations[game.id]['slot-mascot-type'])" />
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
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import Card from '@/Components/Dashboard/Card.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import { Game, Pricelist } from '@/Types/models';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { router, usePage } from '@inertiajs/vue3';
import { useString } from '@/Composables/useString';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import PlusIcon from '@/Components/Dashboard/Icons/PlusIcon.vue';
import TrashButton from '@/Components/Dashboard/Buttons/TrashButton.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import { ref, Ref } from 'vue';
import dayjs, { Dayjs } from 'dayjs';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import RangeDatepicker from '@/Components/Dashboard/RangeDatepicker.vue';
import { useNumber } from '@/Composables/useNumber';
import GameFilter from '@/Components/Dashboard/GameFilter.vue';
import AmountInput from '@/Components/Dashboard/AmountInput.vue';

const props = defineProps<{
	pricelist: Pricelist;
	game: Game;
}>();

const { formattedFloat } = useNumber();

interface PricelistDayData {
	from: string;
	to: string;
	price: string;
}

interface pricelistException {
	dateRange: Array<Date>;
	from: string;
	to: string;
	price: number | string;
}

// composables
const { capitalize } = useString();
const { fromOptions, toOptions } = useSelectOptions();

// pricelist
const pricelistDays: Ref<Record<number, Array<PricelistDayData>>> = ref({});

function updatePricelist() {
	router.put(
		route('club.games.pricelists.update', {
			game: props.game,
			pricelist: props.pricelist,
		}),
		{
			name: 'unnumbered_tables',
			days: pricelistDays.value,
		},
		{
			errorBag: 'updatePricelist',
			preserveScroll: true,
			onSuccess: reloadPricelistDays,
		},
	);
}

function reloadPricelistDays() {
	pricelistDays.value = {};
	props.pricelist.pricelistItems?.forEach((pricelistItem) => {
		if (!pricelistDays.value[pricelistItem.day]) {
			pricelistDays.value[pricelistItem.day] = [];
		}
		pricelistDays.value[pricelistItem.day].push({
			from: '00:00',
			to: '24:00',
			price: formattedFloat(pricelistItem.price / 100, 2),
		});
	});
}
reloadPricelistDays();

const pricelistExceptions: Ref<Array<pricelistException>> = ref([]);
props.pricelist.pricelistExceptions?.forEach((pricelistException) => {
	pricelistExceptions.value.push({
		dateRange: [new Date(pricelistException.start_at), new Date(pricelistException.start_at)],
		from: pricelistException.from,
		to: pricelistException.to,
		price: pricelistException.price / 100,
	});
});

//pricelist exception
function updatePricelistExceptions(): void {
	let pricelistExceptionsData: Array<{
		start_at: string;
		end_at: string;
		from: string;
		to: string;
		price: number;
	}> = [];
	pricelistExceptions.value.forEach((pricelistException) => {

    let startAt = pricelistException.dateRange[0];
    let startAtYear = startAt.getFullYear();
    let startAtMonth = startAt.getMonth() + 1;
    startAtMonth = startAtMonth < 10 ? '0' + startAtMonth : startAtMonth;
    let startAtDay = startAt.getDate();

    let endAt = pricelistException.dateRange[1];
    let endAtYear = endAt.getFullYear();
    let endAtMonth = endAt.getMonth() + 1;
    endAtMonth = endAtMonth < 10 ? '0' + endAtMonth : endAtMonth;
    let endAtDay = endAt.getDate();

		pricelistExceptionsData.push({
			start_at: startAtYear + '-' + startAtMonth + '-' + startAtDay,
      end_at: endAtYear + '-' + endAtMonth + '-' + endAtDay,
			from: pricelistException.from,
			to: pricelistException.to,
			price: pricelistException.price,
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

function addPricelistException() {
	pricelistExceptions.value.push({
		dateRange: [dayjs(), dayjs()],
		price: '10',
		from: '10:00',
		to: '16:00',
	});
  usePage().props.errors = {};
}

function removePricelistException(index: number): void {
	pricelistExceptions.value.splice(index, 1);
	if (pricelistExceptions.value.length === 0) {
		updatePricelistExceptions();
	}
  usePage().props.errors = {};
}
</script>
