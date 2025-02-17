<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.games.slots.index', { game: game }),
				label: usePage().props.gameTranslations[game.id]['slot-plural'],
			},
			{
				href: route('club.games.slots.create', { game: game }),
				label: usePage().props.gameTranslations[game.id]['slot-add'],
			},
		]">
		<ContentContainer>
			<div class="col-span-12 xl:col-span-9">
				<Card>
					<template #header>
						<div class="flex justify-between">
							<h2>
								{{ usePage().props.gameTranslations[game.id]['slot-add'] }}
							</h2>
						</div>
					</template>
					<form class="slot-form mt-5 grid grid-cols-4 gap-x-11 gap-y-5" @submit.prevent="submit">
						<div
							:class="{
								'col-span-4': features.parent_slot_has_online_status.length === 0,
								'col-span-4 md:col-span-3': features.parent_slot_has_online_status.length !== 0,
							}"
							class="space-y-2">
							<InputLabel :value="features.slot_has_parent[0].translations['parent-slot-name']" required />
							<TextInput v-model="form.name" />

							<div class="error">
								{{ form.errors?.name ?? '&nbsp;' }}
							</div>
						</div>
						<div
							v-if="features.parent_slot_has_online_status.length !== 0"
							class="col-span-4 mt-0 flex items-start space-x-3 md:col-span-1 md:mt-10">
							<Checkbox
								id="parentSlotHasOnlineStatus"
								v-model="form.features[features.parent_slot_has_online_status[0].id].status"
								:checked="form.features[features.parent_slot_has_online_status[0].id].status" />
							<InputLabel
								:value="features.parent_slot_has_online_status[0].translations['active-online']"
								for="parentSlotHasOnlineStatus" />
						</div>
						<hr class="col-span-4" />
						<div class="col-span-4 block w-full justify-between space-y-3 md:flex md:space-y-0">
							<Button class="brand-light w-full space-x-2 md:w-80" @click="addSubslot">
								<p>{{ capitalize($t('slot.add')) }}</p>
							</Button>
							<Button class="w-full md:w-80" type="submit">
								{{ capitalize($t('main.action.save')) }}
							</Button>
						</div>
						<AccordionTab
							v-for="(item, index) in form.features[features.slot_has_parent[0].id]['items']"
							:is-expanded="true"
							:key="item['uid']"
							class="bolder col-span-4">
							<template #title>{{ getSubslotFullName(item) }}</template>
							<div
								class="flex w-full flex-wrap items-start space-x-0 space-y-3 md:flex-nowrap md:space-x-5 md:space-y-0">
								<div class="w-full md:w-5/12">
									<InputLabel :value="getSubslotFullName(item)" required />
									<TextInput v-model="item.name" />
									<div class="error">
										{{
											form.errors?.[
												'features.' + features.slot_has_parent[0].id + '.items.' + index + '.name'
											] ?? '&nbsp;'
										}}
									</div>
								</div>
								<div class="w-full md:w-5/12">
									<InputLabel
										:value="
											features.book_singular_slot_by_capacity[0].translations['slot-create-capacity-label']
										"
										required />
									<TextInput
										v-model="item.features[features.book_singular_slot_by_capacity[0].id].capacity" />
									<div class="error">
										{{
											form.errors?.[
												'features.' +
													features.slot_has_parent[0].id +
													'.items.' +
													index +
													'.features.' +
													features.book_singular_slot_by_capacity[0].id +
													'.capacity'
											] ?? '&nbsp;'
										}}
									</div>
								</div>
								<div class="w-full md:w-2/12">
									<InputLabel value="&nbsp;" />
									<Button class="danger-light flex w-full space-x-2 !px-0" @click="removeSubslot(item.uid)">
										<TrashIcon />
										<p>{{ capitalize($t('main.action.delete')) }}</p>
									</Button>
								</div>
							</div>
							<div
								class="mb-4 mt-6 grid w-full grid-cols-1 justify-between gap-x-2 gap-y-3 sm:grid-cols-4 md:grid-cols-5 2xl:grid-cols-7">
								<div
									v-for="(weekDayStatus, i) in item.features[
										features.slot_has_active_status_per_weekday[0].id
									]['active']"
									class="flex w-full">
									<Checkbox
										id="setAsActive"
										v-model="item.features[features.slot_has_active_status_per_weekday[0].id]['active'][i]"
										:checked="weekDayStatus" />
									<InputLabel :value="$t(`main.week-day.${i + 1}`)" class="ml-3" for="setAsActive" />
								</div>
							</div>
						</AccordionTab>
					</form>
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
	</PanelLayout>
</template>

<script lang="ts" setup>
import Card from '@/Components/Dashboard/Card.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { Feature, Game, Pricelist } from '@/Types/models';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import PlusIcon from '@/Components/Dashboard/Icons/PlusIcon.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import { useString } from '@/Composables/useString';

const props = defineProps<{
	game: Game;
	pricelists: Pricelist[];
}>();

interface FeaturesData {
	[featureId: number]: {
		capacity?: number;
		active?: Array<Boolean>;
		items?: Array<Subslot>;
		status?: boolean;
	};
}

interface Subslot {
	uid: number;
	name: string;
	features: FeaturesData;
}

const { capitalize } = useString();

const featureTypes = [
	'slot_has_parent',
	'slot_has_active_status_per_weekday',
	'book_singular_slot_by_capacity',
	'parent_slot_has_online_status',
];
type FeatureMap = {
	[featureType: string]: Feature[];
};
const features: FeatureMap = featureTypes.reduce<FeatureMap>((acc, type) => {
	acc[type] = props.game.features?.filter((feature) => feature.type === type) ?? [];
	return acc;
}, {});

const formDictionary: {
	name: string | null;
	pricelist_id: number | null;
	features: FeaturesData;
} = {
	name: null,
	pricelist_id: props.pricelists[0].id ?? null,
	features: {
		[features.book_singular_slot_by_capacity[0].id]: {
			capacity: 5,
		},
		[features.slot_has_active_status_per_weekday[0].id]: {
			active: Array(7).fill(true),
		},
		[features.slot_has_parent[0].id]: {
			items: [],
		},
		[features.parent_slot_has_online_status[0].id]: {
			status: true,
		},
	},
	isExpanded: false,
};
const form = useForm(formDictionary);

let iterator: number = -1;
addSubslot();

function addSubslot(): void {
	form['features'][features.slot_has_parent[0].id]['items']?.unshift({
		uid: iterator,
		name: '',
		features: {
			[features.slot_has_active_status_per_weekday[0].id]: {
				active: Array(7).fill(true),
			},
			[features.book_singular_slot_by_capacity[0].id]: {
				capacity: null,
			},
		},
		isExpanded: true,
	});
	iterator--;
}

function removeSubslot(uid: number): void {
	const items: Subslot[] | undefined = form['features']?.[features.slot_has_parent[0].id]?.['items'];
	if (items) {
		const index = items.findIndex((item) => item.uid === uid);
		if (index !== -1) {
			items.splice(index, 1);
		}
		form['features'][features.slot_has_parent[0].id]['items'] = items;
	}
}

function getSubslotFullName(slot: Subslot): string {
	return features.book_singular_slot_by_capacity[0].translations['slot-create-name-label'] + ' ' + slot.name;
}

function submit() {
	form.post(
		route('club.games.slots.store', {
			game: props.game,
		}),
		{
			preserveScroll: true,
			preserveState: true,
		},
	);
}
</script>
