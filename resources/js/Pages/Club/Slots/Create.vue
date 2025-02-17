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
						class="slot-form mt-9 grid grid-cols-4 gap-x-4 gap-y-5"
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
						<div class="col-span-4 space-y-2 md:col-span-2">
							<InputLabel :value="capitalize($t('main.name'))" required />
							<TextInput v-model="form.name" />
							<div v-if="form.errors.name" class="error">
								{{ form.errors.name }}
							</div>
						</div>

						<div v-for="feature in features.slot_has_type" class="col-span-4 space-y-2 md:col-span-2">
							<InputLabel :value="feature.translations['slot-create-label']" required />
							<SimpleSelect
								v-model="form.features[feature.id].name"
								:options="
									feature['data']['options'].map((option) => ({
										code: option,
										label: feature.translations['type-' + option],
									}))
								"
								@option:selected="reloadSubtypeValue" />
							<div v-if="form.errors[`features.${feature.id}.name`]" class="error">
								{{ form.errors[`features.${feature.id}.name`] }}
							</div>
						</div>

						<div v-for="feature in features.slot_has_subtype" class="col-span-4 space-y-2 md:col-span-2">
							<InputLabel :value="feature.translations['slot-create-label']" required />
							<SimpleSelect
								v-model="form.features[feature.id].name"
								:options="
									features.slot_has_subtype[0]['data']['options']
										.filter((option) => option.type === form.features[features.slot_has_type[0].id]['name'])
										.map((option) => ({
											code: option.name,
											label: feature.translations['type-' + option.name],
										}))
								" />
							<div v-if="form.errors[`features.${feature.id}.name`]" class="error">
								{{ form.errors[`features.${feature.id}.name`] }}
							</div>
						</div>

						<div
							v-for="feature in features.book_singular_slot_by_capacity"
							class="col-span-4 space-y-2 md:col-span-2">
							<InputLabel :value="feature.translations['slot-create-label']" />
							<TextInput v-model="form.features[feature.id].capacity" />
							<div v-if="form.errors[`features.${feature.id}.capacity`]" class="error">
								{{ form.errors[`features.${feature.id}.capacity`] }}
							</div>
						</div>

						<div v-for="feature in features.slot_has_convenience" class="col-span-4 space-y-2 md:col-span-2">
							<InputLabel :value="feature.translations['slot-create-label']" />
							<SimpleSelect v-model="form.features[feature.id].status" :options="yesNoOptions" />
							<div v-if="form.errors[`features.${feature.id}.status`]" class="error">
								{{ form.errors[`features.${feature.id}.status`] }}
							</div>
						</div>

						<div class="col-span-4 space-y-2 md:col-span-2">
							<InputLabel :value="capitalize($t('main.pricelist'))" required />
							<SimpleSelect
								v-model="form.pricelist_id"
								:options="
									pricelists.map((item) => ({
										code: item.id,
										label: item.name,
									}))
								" />
							<div v-if="form.errors.pricelist" class="error">
								{{ form.errors.pricelist }}
							</div>
						</div>
						<template v-for="feature in features.slot_has_lounge">
							<div
								class="col-span-4"
								v-if="usePage().props.clubSettings['lounges_status_' + feature.id]?.value === true">
								<div class="grid w-full grid-cols-3 gap-x-4 gap-y-5 md:col-span-4">
									<div class="col-span-3 space-y-2 md:col-span-1">
										<InputLabel :value="feature.translations['slot-create-status-label']" />
										<SimpleSelect v-model="form.features[feature.id]['status']" :options="yesNoOptions" />
										<div v-if="form.errors[`features.${feature.id}.status`]" class="error">
											{{ form.errors[`features.${feature.id}.status`] }}
										</div>
									</div>
									<div class="col-span-3 space-y-2 md:col-span-1">
										<InputLabel :value="feature.translations['slot-create-min-label']" />
										<TextInput v-model="form.features[feature.id]['min']" placeholder="-" />
										<div v-if="form.errors[`features.${feature.id}.min`]" class="error">
											{{ form.errors[`features.${feature.id}.min`] }}
										</div>
									</div>
									<div class="col-span-3 space-y-2 md:col-span-1">
										<InputLabel :value="feature.translations['slot-create-max-label']" />
										<TextInput v-model="form.features[feature.id]['max']" placeholder="-" />
										<div v-if="form.errors[`features.${feature.id}.max`]" class="error">
											{{ form.errors[`features.${feature.id}.max`] }}
										</div>
									</div>
								</div>
							</div>
						</template>

						<template v-for="feature in features.slot_has_bulb">
							<div
								class="col-span-4 space-y-2 md:col-span-2"
								v-if="usePage().props.clubSettings['bulb_status_' + feature.id]?.value === true">
								<InputLabel :value="feature.translations['bulb-name']" />
								<TextInput v-model="form.features[feature.id]['name']" />
								<div v-if="form.errors[`features.${feature.id}.name`]" class="error">
									{{ form.errors[`features.${feature.id}.name`] }}
								</div>
							</div>
						</template>

						<div class="col-span-4 flex justify-end">
							<Button class="w-full md:w-1/4" type="submit">{{ capitalize($t('main.action.save')) }}</Button>
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
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import { useSelectOptions } from '@/Composables/useSelectOptions';

const props = defineProps<{
	game: Game;
	pricelists: Pricelist[];
}>();

const { capitalize } = useString();
const { yesNoOptions } = useSelectOptions();

const formDictionary: Record<string, any> = {
	name: '',
	pricelist_id: props.pricelists[0].id,
	features: [],
};

const featureTypes = [
	'slot_has_type',
	'slot_has_subtype',
	'book_singular_slot_by_capacity',
	'slot_has_convenience',
	'slot_has_lounge',
	'slot_has_bulb',
];
type FeatureMap = {
	[featureType: string]: Feature[];
};
const features: FeatureMap = featureTypes.reduce<FeatureMap>((acc, type) => {
	acc[type] = props.game.features?.filter((feature) => feature.type === type) ?? [];
	return acc;
}, {});

features.slot_has_bulb.forEach((feature) => {
	formDictionary['features'][feature.id] = {
		name: '',
	};
});
features.slot_has_type.forEach((feature) => {
	formDictionary['features'][feature.id] = {
		name: feature.data.options[0],
	};
});
features.slot_has_subtype.forEach((feature) => {
	formDictionary['features'][feature.id] = {
		name: '',
	};
});
features.book_singular_slot_by_capacity.forEach((feature) => {
	formDictionary['features'][feature.id] = {
		capacity: 5,
	};
});
features.slot_has_convenience.forEach((feature) => {
	formDictionary['features'][feature.id] = {
		status: false,
	};
});
features.slot_has_lounge.forEach((feature) => {
	formDictionary['features'][feature.id] = {
		status: false,
		min: null,
		max: null,
	};
});

const form = useForm(formDictionary);

function reloadSubtypeValue() {
	form.features[features.slot_has_subtype[0].id].name = features.slot_has_subtype[0]['data'][
		'options'
	].filter((option) => option.type === form.features[features.slot_has_type[0].id].name)[0].name;
}

features.slot_has_subtype.forEach((feature) => {
	reloadSubtypeValue();
});
</script>

<style>
.slot-form .vs__dropdown-option__content,
.slot-form .vs__selected {
	@apply !capitalize;
}
</style>
