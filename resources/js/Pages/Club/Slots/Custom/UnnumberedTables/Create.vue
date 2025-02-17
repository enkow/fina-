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
					<form
						class="slot-form mt-5 grid grid-cols-3 gap-x-11 gap-y-10"
						@submit.prevent="
							form.post(
								route('club.games.slots.store', {
									game: game,
								}),
								{
									preserveScroll: true,
									preserveState: true,
								},
							)
						">
						<div class="col-span-3 space-y-2">
							<InputLabel :value="features.slot_has_parent[0].translations['parent-slot-name']" required />
							<TextInput v-model="form.name" />

							<div v-if="form.errors.name" class="error">
								{{ form.errors.name }}
							</div>
						</div>
						<div v-for="i in 7" class="col-span-3 space-y-2 md:col-span-1">
							<div class="flex w-full items-center justify-between">
								<p class="text-lg font-semibold capitalize text-brand-base">
									{{ $t(`main.week-day.${i}`) }}
								</p>
								<div class="flex items-start space-x-0.5">
									<p>
										{{ features.person_as_slot[0].translations['capacity'] }}
									</p>
									<p class="text-red-500">*</p>
								</div>
							</div>
							<TextInput v-model="form.features[features.person_as_slot[0].id]['capacity'][i]" />

							<div
								v-if="form.errors[`features.${features.person_as_slot[0].id}.capacity.${i}`]"
								class="error">
								{{ form.errors[`features.${features.person_as_slot[0].id}.capacity.${i}`] }}
							</div>
						</div>
						<div class="col-span-3 flex justify-end">
							<Button class="w-full md:w-1/3" type="submit">{{ capitalize($t('main.action.save')) }}</Button>
						</div>
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
import PanelLayout from '@/Layouts/PanelLayout.vue';
import Card from '@/Components/Dashboard/Card.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import { Feature, Game, Pricelist } from '@/Types/models';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useString } from '@/Composables/useString';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';

const props = defineProps<{
	game: Game;
	pricelists: Pricelist[];
}>();

const { capitalize } = useString();

const featureTypes = ['person_as_slot', 'slot_has_parent', 'parent_slot_has_online_status'];
type FeatureMap = {
	[featureType: string]: Feature[];
};
const features: FeatureMap = featureTypes.reduce<FeatureMap>((acc, type) => {
	acc[type] = props.game.features?.filter((feature) => feature.type === type) ?? [];
	return acc;
}, {});

let baseCapacityArray: Number[] = Array.from({ length: 8 }).fill(0) as Number[];

const formDictionary: {
	name: string | null;
	pricelist_id: number | undefined;
	features: {
		capacity?: {
			[weekDay: number]: number;
		};
	};
} = {
	name: null,
	pricelist_id: props.pricelists[0].id,
	features: {
		[features.person_as_slot[0].id]: {
			capacity: baseCapacityArray,
		},
		[features.parent_slot_has_online_status[0].id]: {
			status: true,
		},
	},
};

const form = useForm(formDictionary);
</script>
