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
						class="slot-form mt-9 grid grid-cols-12 gap-x-4 gap-y-5"
						@submit.prevent="
							form.post(route('club.games.slots.store', { game: game }), {
								preserveScroll: true,
								preserveState: true,
							})
						">
						<div class="col-span-12 space-y-2 md:col-span-5">
							<InputLabel :value="capitalize($t('main.name'))" required />
							<TextInput v-model="form.name" />
							<div v-if="form.errors.name" class="error">
								{{ form.errors.name }}
							</div>
						</div>

						<div class="col-span-12 space-y-2 md:col-span-4">
							<InputLabel :value="capitalize($t('main.pricelist'))" required />
							<SimpleSelect
								v-model="form.pricelist_id"
								:options="
									props.pricelists.map((pricelist) => ({
										code: pricelist.id,
										label: pricelist.name,
									}))
								" />
							<div v-if="form.errors.pricelist" class="error">
								{{ form.errors.pricelist }}
							</div>
						</div>

						<div v-for="feature in features.slot_has_convenience" class="col-span-12 space-y-2 md:col-span-3">
							<InputLabel :value="feature.translations['slot-create-label']" />
							<SimpleSelect v-model="form.features[feature.id].status" :options="yesNoOptions" />
							<div v-if="form.errors[`features.${feature.id}.status`]" class="error">
								{{ form.errors[`features.${feature.id}.status`] }}
							</div>
						</div>

						<div class="col-span-12 flex justify-end">
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

const featureTypes = ['slot_has_convenience'];
type FeatureMap = {
	[featureType: string]: Feature[];
};
const features: FeatureMap = featureTypes.reduce<FeatureMap>((acc, type) => {
	acc[type] = props.game.features?.filter((feature) => feature.type === type) ?? [];
	return acc;
}, {});

features.slot_has_convenience.forEach((feature) => {
	formDictionary['features'][feature.id] = {
		status: false,
	};
});

const form = useForm(formDictionary);
</script>

<style>
.slot-form .vs__dropdown-option__content,
.slot-form .vs__selected {
	@apply !capitalize;
}
</style>
