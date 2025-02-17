<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('admin.games.index'), label: 'Gry' },
			{ href: route('admin.games.edit', { game: game }), label: game.name },
		]">
		<ContentContainer>
			<div class="col-span-12 space-y-3">
				<Card>
					<template #header>
						<h2>Edytuj grę: {{ game.name }}</h2>
					</template>

					<form
						class="mt-4 grid grid-cols-4 gap-x-4 gap-y-5"
						@submit.prevent="editGameForm.post(route('admin.games.update', { game: game }))">
						<div class="input-group col-span-2">
							<InputLabel :value="capitalize($t('main.name'))" />
							<TextInput v-model="editGameForm.name" />
							<div v-if="editGameForm.errors.name" class="error">
								{{ editGameForm.errors.name }}
							</div>
						</div>

						<div class="col-span-2">
							<InputLabel value="Ikona" />
							<TextInput v-model="editGameForm.icon" />
							<div v-if="editGameForm.errors.icon" class="error">
								{{ editGameForm.errors.icon }}
							</div>
						</div>

						<div class="col-span-2">
							<InputLabel class="mb-2" value="Kolor ikonek w ustawieniach" />
							<select v-model="editGameForm.setting_icon_color" class="w-full">
								<option v-for="color in ['info', 'warning', 'danger', 'brand', 'gray']" :value="color">
									{{ color }}
								</option>
							</select>
						</div>

						<div class="col-span-2">
							<InputLabel value="Zdjęcie" />
							<ImageInput
								v-model="editGameForm.photo"
								placeholder="Kliknij, aby wybrać obraz"
								:error="editGameForm.errors.photo"
								:updated-url="game.photo ? `/images/game-images/${game.photo}` : ''" />
						</div>

						<div class="col-span-2">
							<InputLabel value="Obecna ikona" />
							<div
								class="flex h-9 w-9 items-center justify-center rounded-md bg-brand-base text-white"
								v-html="game.icon"></div>
						</div>

						<div class="col-span-2"></div>

						<div class="col-span-2 col-start-3">
							<div class="flex h-full w-full items-end">
								<Button class="w-full" type="submit">
									{{ capitalize($t('main.action.update')) }}
								</Button>
							</div>
						</div>
					</form>
				</Card>
				<Card>
					<template #header>
						<h2>Dodaj nową cechę</h2>
					</template>
					<form
						class="mt-4 flex items-start space-x-3"
						@submit.prevent="
							createGameFeatureForm.post(route('admin.games.features.store', { game: game }), {
								preserveScroll: true,
								preserveState: true,
							})
						">
						<div class="input-group w-2/4">
							<InputLabel value="Kod" />
							<TextInput v-model="createGameFeatureForm.code" />
							<div v-if="createGameFeatureForm.errors.code" class="error">
								{{ createGameFeatureForm.errors.code }}
							</div>
						</div>

						<div class="input-group w-1/4">
							<InputLabel value="Cecha" />
							<select v-model="createGameFeatureForm.featureType" class="w-full">
								<option v-for="featureType in featureTypes" :value="featureType">
									{{ featureType }}
								</option>
							</select>
							<div v-if="createGameFeatureForm.errors.featureType" class="error">
								{{ createGameFeatureForm.errors.featureType }}
							</div>
						</div>

						<div class="input-group w-1/4">
							<InputLabel value="Cecha" />
							<div class="flex h-full w-full items-end">
								<Button class="w-full" type="submit">
									{{ capitalize($t('main.action.add')) }}
								</Button>
							</div>
						</div>
					</form>
				</Card>
				<Card v-for="feature in features">
					<template #header>
						<div class="flex w-full items-center justify-between">
							<div>
								<h2>
									{{ feature.type + ' - ' + (feature.code === null ? 'Brak nazwy' : feature.code) }}
								</h2>
							</div>
							<div v-if="feature.isTaggableIfGameReservationExist || game.reservations_count === 0">
								<Button
									:href="
										route('admin.games.features.destroy', {
											game: game,
											feature: feature,
										})
									"
									as="button"
									class="danger"
									method="delete"
									preserve-scroll
									preserve-state
									type="link">
									Usuń
								</Button>
							</div>
							<div v-else class="w-40">
								Nie można usunąć tej cechy, ponieważ do gry przypisane są rezerwacje.
							</div>
						</div>
					</template>
					<div class="mt-10">
						<component :is="featureComponents[feature.type]" :feature="feature" />
					</div>
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
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { Feature, Game } from '@/Types/models';
import { useForm } from '@inertiajs/vue3';
import Card from '@/Components/Dashboard/Card.vue';
import InputLabel from '@/Components/Auth/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { useString } from '@/Composables/useString';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import BookSingularSlotByCapacity from '@/Components/Dashboard/Feature/BookSingularSlotByCapacity.vue';
import FixedReservationDuration from '@/Components/Dashboard/Feature/FixedReservationDuration.vue';
import FullDayReservations from '@/Components/Dashboard/Feature/FullDayReservations.vue';
import HasCustomViews from '@/Components/Dashboard/Feature/HasCustomViews.vue';
import HasPriceAnnouncementsSettings from '@/Components/Dashboard/Feature/HasPriceAnnouncementsSettings.vue';
import HasVisibleCalendarSlotsCountSetting from '@/Components/Dashboard/Feature/HasVisibleCalendarSlotsCountSetting.vue';
import PersonAsSlot from '@/Components/Dashboard/Feature/PersonAsSlot.vue';
import PricePerPerson from '@/Components/Dashboard/Feature/PricePerPerson.vue';
import HasMapSetting from '@/Components/Dashboard/Feature/HasMapSetting.vue';
import SlotHasConvenience from '@/Components/Dashboard/Feature/SlotHasConvenience.vue';
import SlotHasLounge from '@/Components/Dashboard/Feature/SlotHasLounge.vue';
import SlotHasSubslots from '@/Components/Dashboard/Feature/SlotHasParent.vue';
import SlotHasType from '@/Components/Dashboard/Feature/SlotHasType.vue';
import HasManagerEmailsSetting from '@/Components/Dashboard/Feature/HasManagerEmailsSetting.vue';
import { ShallowRef, shallowRef } from 'vue';
import HasOfflineReservationLimitsSettings from '@/Components/Dashboard/Feature/HasOfflineReservationLimitsSettings.vue';
import { useToast } from 'vue-toastification';
import SlotHasSubtype from '@/Components/Dashboard/Feature/SlotHasSubtype.vue';
import HasSlotPeopleLimitSettings from '@/Components/Dashboard/Feature/HasSlotPeopleLimitSettings.vue';
import HasWidgetSlotsSelection from '@/Components/Dashboard/Feature/HasWidgetSlotsSelection.vue';
import HasWidgetDurationLimitSetting from '@/Components/Dashboard/Feature/HasWidgetDurationLimitSetting.vue';
import HasWidgetDurationLimitMinimumSetting from '@/Components/Dashboard/Feature/HasWidgetDurationLimitMinimumSetting.vue';
import ImageInput from '@/Components/Dashboard/ImageInput.vue';
import HasGamePhotoSetting from '@/Components/Dashboard/Feature/HasGamePhotoSetting.vue';

const featureComponents: ShallowRef<Record<string, InstanceType<any>>> = shallowRef({
	book_singular_slot_by_capacity: BookSingularSlotByCapacity,
	fixed_reservation_duration: FixedReservationDuration,
	full_day_reservations: FullDayReservations,
	has_game_photo_setting: HasGamePhotoSetting,
	has_custom_views: HasCustomViews,
	has_manager_emails_setting: HasManagerEmailsSetting,
	has_map_setting: HasMapSetting,
	has_offline_reservation_limits_settings: HasOfflineReservationLimitsSettings,
	has_price_announcements_settings: HasPriceAnnouncementsSettings,
	has_slot_people_limit_settings: HasSlotPeopleLimitSettings,
	has_visible_calendar_slots_count_setting: HasVisibleCalendarSlotsCountSetting,
	person_as_slot: PersonAsSlot,
	price_per_person: PricePerPerson,
	show_has_subslots: SlotHasSubslots,
	has_widget_slots_selection: HasWidgetSlotsSelection,
	has_widget_duration_limit_setting: HasWidgetDurationLimitSetting,
	has_widget_duration_limit_minimum_setting: HasWidgetDurationLimitMinimumSetting,
	slot_has_convenience: SlotHasConvenience,
	slot_has_lounge: SlotHasLounge,
	slot_has_subtype: SlotHasSubtype,
	slot_has_type: SlotHasType,
});

const { capitalize } = useString();
const toast = useToast();

const props = defineProps<{
	game: Game;
	games: Game[];
	features: Feature[];
	featureTypes: Record<string, string>;
}>();

const editGameForm = useForm({
	game_id: props.game.id,
	name: props.game.name,
	description: props.game.description,
	icon: props.game.icon,
	setting_icon_color: props.game.setting_icon_color,
	photo: null,
});

const createGameFeatureForm = useForm({
	featureType: props.featureTypes.length ? props.featureTypes[0] : null,
	code: null,
});

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
</script>

<style>
.no-settings {
	@apply w-full text-center text-sm text-gray-6;
}
</style>
