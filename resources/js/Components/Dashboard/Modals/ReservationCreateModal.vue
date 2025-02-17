<template>
	<Transition>
		<div v-if="showing && !hidden" class="fixed left-0 top-0 z-10 h-screen w-full bg-black/50 py-4.5">
			<div
				class="relative top-1/2 m-auto max-h-screen max-w-228 -translate-y-1/2 transform overflow-y-auto rounded-md bg-white pb-4 pt-4 2xl:pb-6 2xl:pt-6">
				<div class="px-4 lg:px-14">
					<div
						class="mb-4 w-full rounded-md bg-black px-3 py-3 text-white xs:px-8.25 sm:w-fit sm:min-w-80 2xl:mb-10">
						<p v-if="slotId" class="text-2xl font-semibold">
							{{
								usePage().props.gameTranslations[usePage().props.game.id][
									'reservation-for-slot-header'
								].replace(':slot_name', slots.find((slot) => slot.id === form.slot_ids[0])?.name ?? '')
							}}
						</p>
						<p v-else class="text-2xl font-semibold">
							{{ $t('reservation.new-reservation') }} -
							{{ usePage().props.gameTranslations[usePage().props.game.id]['game-name'] }}
						</p>
					</div>
					<div
						class="absolute right-8 top-6 cursor-pointer text-gray-3 transition hover:text-gray-7"
						@click="hide">
						<XIcon />
					</div>
				</div>

				<form @submit.prevent="submitForm">
					<div class="grid grid-cols-4 gap-x-5 px-4 md:grid-cols-12 lg:px-14">
						<div class="col-span-4 -mb-4 space-y-2">
							<InputLabel :required="!form.anonymous_reservation" class="capitalize">
								{{ $t('main.first-name') }}
							</InputLabel>
							<TextInput v-model="form.customer.first_name" :disabled="form.anonymous_reservation" />
							<div
								:class="{ 'opacity-0': !form.errors || !form.errors?.['customer.first_name'] }"
								class="error !mt-1">
								{{ form.errors['customer.first_name'] ?? '&nbsp;' }}
							</div>
						</div>

						<div
							class="col-span-4 -mb-4 space-y-2"
							@focusout="popperShowingVariables['last_name'].value = false">
							<InputLabel class="capitalize">{{ $t('main.last-name') }}</InputLabel>
							<TextInput
								v-model="form.customer.last_name"
								:disabled="form.anonymous_reservation"
								@click="
									popperShowingVariables['last_name'].value = !popperShowingVariables['last_name'].value
								" />
							<div
								:class="{ 'opacity-0': !form.errors || !form.errors?.['customer.last_name'] }"
								class="error !mt-1">
								{{ form.errors['customer.last_name'] ?? '&nbsp;' }}
							</div>
							<Popper :show="popperShowingVariables.last_name.value" class="w-full">
								<div class="hidden" />
								<template #content>
									<div
										class="-mt-12 ml-4 w-[calc(100vw-2rem)] space-y-2 rounded-md border border-brand-base bg-gray-10 py-2 text-sm md:w-[calc(25vh)] lg:ml-6 lg:w-[254px]">
										<CustomersSearchResults
											:customers="customerSearchResults.last_name"
											@fill="fillCustomer" />
									</div>
								</template>
							</Popper>
						</div>

						<div class="col-span-4 -mb-4 space-y-2" @focusout="popperShowingVariables['phone'].value = false">
							<InputLabel :required="!form.anonymous_reservation" class="capitalize">
								{{ $t('main.phone-number-short') }}
							</InputLabel>
							<TextInput
								v-model="form.customer.phone"
								:disabled="form.anonymous_reservation"
								@click="popperShowingVariables['phone'].value = !popperShowingVariables['phone'].value" />
							<div
								:class="{ 'opacity-0': !form.errors || !form.errors?.['customer.phone'] }"
								class="error !mt-1">
								{{ form.errors['customer.phone'] }}
							</div>
							<Popper :show="popperShowingVariables.phone.value" class="w-full">
								<div class="hidden" />
								<template #content>
									<div
										class="-mt-6 ml-4 w-[calc(100vw-2rem)] space-y-2 rounded-md border border-brand-base bg-gray-10 py-2 text-sm md:w-[calc(25vh)] lg:ml-6 lg:w-[254px]">
										<CustomersSearchResults :customers="customerSearchResults.phone" @fill="fillCustomer" />
									</div>
								</template>
							</Popper>
						</div>

						<div class="col-span-4 -mb-4 space-y-2" @focusout="popperShowingVariables['email'].value = false">
							<div class="flex items-center space-x-2">
								<InputLabel>
									{{ capitalize($t('main.email')) }}
								</InputLabel>
								<svg
									width="11"
									height="12"
									viewBox="0 0 11 12"
									fill="none"
									xmlns="http://www.w3.org/2000/svg"
									v-if="isCustomerWithEmailOnline">
									<path
										fill-rule="evenodd"
										clip-rule="evenodd"
										d="M1.47558 0H0.335938L0.603154 1.10855L2.13849 8.13563H2.14514V8.31411H9.3507V8.17665L10.5662 2.82181L10.7536 2.2171H1.95392L1.63798 0.769886L1.47558 0ZM9.3435 3.32564H2.19614L3.04362 7.20556H8.46276L9.3435 3.32564Z"
										fill="black" />
									<path
										d="M3.80386 11.0843C4.09787 11.0843 4.37983 10.9675 4.58772 10.7596C4.79561 10.5517 4.91241 10.2697 4.91241 9.97573C4.91241 9.68173 4.79561 9.39977 4.58772 9.19187C4.37983 8.98398 4.09787 8.86719 3.80386 8.86719C3.50986 8.86719 3.22789 8.98398 3.02 9.19187C2.81211 9.39977 2.69531 9.68173 2.69531 9.97573C2.69531 10.2697 2.81211 10.5517 3.02 10.7596C3.22789 10.9675 3.50986 11.0843 3.80386 11.0843ZM8.79232 9.97573C8.79232 10.2697 8.67553 10.5517 8.46764 10.7596C8.25975 10.9675 7.97778 11.0843 7.68378 11.0843C7.38977 11.0843 7.10781 10.9675 6.89992 10.7596C6.69202 10.5517 6.57523 10.2697 6.57523 9.97573C6.57523 9.68173 6.69202 9.39977 6.89992 9.19187C7.10781 8.98398 7.38977 8.86719 7.68378 8.86719C7.97778 8.86719 8.25975 8.98398 8.46764 9.19187C8.67553 9.39977 8.79232 9.68173 8.79232 9.97573Z"
										fill="black" />
								</svg>
							</div>
							<TextInput
								v-model="form.customer.email"
								:disabled="form.anonymous_reservation"
								@click="popperShowingVariables['email'].value = !popperShowingVariables['email'].value" />
							<div v-if="form.errors || form.errors['customer.email']" class="error !mt-1">
								{{ form.errors['customer.email'] ?? '&nbsp;' }}
							</div>
							<Popper :show="popperShowingVariables.email.value" class="w-full">
								<div class="hidden" />
								<template #content>
									<div
										:class="{
											'opacity-0': form.anonymous_reservation,
										}"
										class="-mt-12 ml-4 w-[calc(100vw-2rem)] space-y-2 rounded-md border border-brand-base bg-gray-10 py-2 text-sm md:w-[calc(25vh)] lg:ml-6 lg:w-[254px]">
										<CustomersSearchResults :customers="customerSearchResults.email" @fill="fillCustomer" />
									</div>
								</template>
							</Popper>
						</div>
						<div v-if="features.reservation_slot_has_display_name.length" class="col-span-4 space-y-2">
							<InputLabel class="capitalize">
								{{ features.reservation_slot_has_display_name[0].translations['display-name'] }}
							</InputLabel>
							<TextInput
								v-model="form.features[features.reservation_slot_has_display_name[0].id].display_name"
								class="w-full" />
							<div
								v-if="
									form.errors &&
									form.errors[`features.${features.reservation_slot_has_display_name[0].id}.display_name`]
								"
								class="error !mt-1">
								{{
									form.errors[`features.${features.reservation_slot_has_display_name[0].id}.display_name`] ??
									'&nbsp;'
								}}
							</div>
						</div>
						<div class="col-span-4 flex flex-wrap space-y-2">
							<InputLabel class="capitalize">
								{{ $t('tag.plural') }}
							</InputLabel>
							<Tagify
								:onChange="updateTags"
								class="tagify-condensed w-full"
								:disabled="form.anonymous_reservation"
								:settings="{
									whitelist:
										usePage().props.user.club.tags?.map(function (value, index) {
											return value['name'];
										}) ?? [],
									maxTags: 15,
									dropdown: {
										maxItems: 20,
										classname: 'customer-tags',
										enabled: 0,
										closeOnSelect: true,
									},
								}"
								mode="text"
								:value="form.customer.tags" />
							<div class="flex h-12 w-full pl-1">
								<Checkbox
									id="anonymousReservation"
									v-model="form.anonymous_reservation"
									:checked="form.anonymous_reservation" />
								<InputLabel
									:value="$t('reservation.anonymous-reservation')"
									class="ml-3 cursor-pointer"
									for="anonymousReservation" />
								<div v-if="form.errors && form.errors.anonymous_reservation" class="error">
									{{ form.errors.anonymous_reservation }}
								</div>
							</div>
						</div>
					</div>
					<hr class="-mt-2 mb-4 2xl:mb-5" />
					<div class="grid grid-flow-dense grid-cols-12 gap-x-5 gap-y-3 px-4 lg:px-14 2xl:gap-y-4">
						<BilliardCreate
							v-if="
								form &&
								game.features.find((feature) => feature.type === 'has_custom_views')?.data?.custom_views?.[
									'reservations.create-form'
								] === 'Club/Reservations/CustomReservationForm/Billiard/Create'
							"
							:form="form"
							:features="features"
							:slots-count-options="slotsCountOptions"
							:status-options="statusOptions"
							:club-settings="clubSettings"
							:parent-slot-options="parentSlotOptions"
							:reservation-type-options="reservationTypeOptions"
							:special-offer-options="specialOfferOptions"
							:discount-code-options="discountCodeOptions"
							:duration-options="durationOptions"
							:game="game"
							:final-price="finalPrice"
							:slot-options="slotOptions"
							:price-per-person-options="pricePerPersonOptions"
							:timer-init-allowed="timerInitAllowed"
							:bulbsOptions="bulbsOptions"
							:slotFeatures="slotFeatures"
							@update="updateForm"
							@price-changed-manually="priceChangedManually" />
						<BowlingCreate
							v-else-if="
								game.features.find((feature) => feature.type === 'has_custom_views')?.data?.custom_views?.[
									'reservations.create-form'
								] === 'Club/Reservations/CustomReservationForm/Bowling/Create'
							"
							:form="form"
							:features="features"
							:slots-count-options="slotsCountOptions"
							:status-options="statusOptions"
							:club-settings="clubSettings"
							:parent-slot-options="parentSlotOptions"
							:reservation-type-options="reservationTypeOptions"
							:special-offer-options="specialOfferOptions"
							:discount-code-options="discountCodeOptions"
							:duration-options="durationOptions"
							:game="game"
							:final-price="finalPrice"
							:slot-options="slotOptions"
							:price-per-person-options="pricePerPersonOptions"
							:timer-init-allowed="timerInitAllowed"
							:bulbsOptions="bulbsOptions"
							:slotFeatures="slotFeatures"
							@update="updateForm"
							@price-changed-manually="priceChangedManually" />
						<template v-else>
							<div v-if="features.person_as_slot.length" class="col-span-12 space-y-2 md:col-span-3">
								<InputLabel class="capitalize" required>
									{{ features.person_as_slot[0].translations['slots-quantity'] }}
								</InputLabel>
								<TextInput
									v-model="form.features[features.person_as_slot[0].id]['persons_count']"
									class="w-full" />
								<div
									v-if="form.errors && form.errors[`features.${features.person_as_slot[0].id}.persons_count`]"
									class="error">
									{{ form.errors[`features.${features.person_as_slot[0].id}.persons_count`] }}
								</div>
							</div>
							<div
								v-else-if="features.book_singular_slot_by_capacity.length"
								class="col-span-12 space-y-2 md:col-span-6">
								<InputLabel class="capitalize" required>
									{{ usePage().props.gameTranslations[game.id]['slot-plural'] }}
								</InputLabel>
								<div class="space-y-2">
									<div v-for="key in Object.keys(form.slot_ids)" class="flex items-center space-x-2">
										<div class="flex-grow">
											<SimpleSelect
												v-model="form.slot_ids[key]"
												:options="
													mergeOptions(
														slotOptions,
														slots.find((slot: Slot) => slot.id === form.slot_ids[key])
															? [
																	getSlotItemForDisplay(
																		slots.find((slot: Slot) => slot.id === form.slot_ids[key]),
																	),
															  ]
															: [],
													)
												"
												class="w-full" />
										</div>
										<div class="h-10 w-10">
											<TrashButton
												v-if="form.slot_ids.length > 1"
												class="!h-10 !w-10"
												@click="removeSlotIdFromSlotIds(key)" />
											<PlusButton
												v-if="form.slot_ids.length === 1 && canAddSlotIdToSlotIds"
												class="!h-10 !w-10"
												@click.prevent="addSlotIdFromSlotIds" />
										</div>
									</div>
									<div
										v-if="canAddSlotIdToSlotIds && form.slot_ids.length > 1"
										class="flex items-center space-x-2">
										<div class="flex-grow"></div>
										<div class="h-10 w-10">
											<PlusButton class="!h-10 !w-10" @click.prevent="addSlotIdFromSlotIds" />
										</div>
									</div>
								</div>
								<div
									v-for="errorKey in Object.keys(form.errors ?? {}).filter((errorKey) =>
										errorKey.includes('slot_ids.'),
									)"
									class="error">
									{{ form.errors[errorKey] }}
								</div>
							</div>
							<div
								class="col-span-12 space-y-2 md:col-span-3"
								v-if="
									features.book_singular_slot_by_capacity.length === 0 && features.person_as_slot.length === 0
								">
								<InputLabel class="capitalize" required>
									{{ usePage().props.gameTranslations[game.id]['slot-name'] }}
								</InputLabel>
								<SimpleSelect v-model="singleSlotId" :options="slotOptions" class="w-full" />
								<div v-if="form.errors.slot_ids" class="error">
									{{ form.errors.slot_ids }}
								</div>
							</div>
							<div
								class="col-span-12 space-y-2 md:col-span-3"
								v-if="
									features.book_singular_slot_by_capacity.length === 0 && features.person_as_slot.length === 0
								">
								<InputLabel class="capitalize" required>
									{{ usePage().props.gameTranslations[game.id]['slots-quantity'] }}
								</InputLabel>
								<SimpleSelect v-model="form.slots_count" :options="slotsCountOptions" />
								<div v-if="form.errors && form.errors.slots_count" class="error">
									{{ form.errors.slots_count }}
								</div>
							</div>
							<div class="col-span-12 space-y-2 md:col-span-3">
								<InputLabel class="capitalize" required>{{ $t('main.date') }}</InputLabel>
								<Datetimepicker
									v-model="form.start_at"
									:minutes-increment="clubSettings['calendarTimeScale']"
									placeholder=" " />
								<div v-if="form.errors && form.errors.start_at" class="error">
									{{ form.errors.start_at }}
								</div>
							</div>

							<div
								v-if="
									!clubSettings['fullDayReservationStatus'] && !clubSettings['fixedReservationDurationStatus']
								"
								class="col-span-12 space-y-2 md:col-span-3">
								<InputLabel class="capitalize" required>{{ $t('reservation.duration-time') }}</InputLabel>
								<SimpleSelect v-model="form.duration" :options="durationOptions" class="w-full" />
								<div v-if="form.errors && form.errors.duration" class="error">
									{{ form.errors.duration }}
								</div>
							</div>
							<div v-if="features.slot_has_parent.length" class="col-span-3 space-y-2">
								<InputLabel class="capitalize" required>
									{{ features.slot_has_parent[0].translations['parent-slot'] }}
								</InputLabel>
								<SimpleSelect
									v-model="form.features[features.slot_has_parent[0].id].parent_slot_id"
									:options="parentSlotOptions"
									class="w-full" />
							</div>

							<div class="col-span-12 grid grid-cols-12 gap-x-5 gap-y-3">
								<div class="col-span-12 space-y-2 md:col-span-4">
									<InputLabel class="capitalize">{{ $t('reservation.reservation-type') }}</InputLabel>
									<SimpleSelect
										v-model="form.reservation_type_id"
										:options="reservationTypeOptions"
										class="w-full" />
									<div v-if="form.errors && form.errors.reservation_type_id" class="error">
										{{ form.errors.reservation_type_id }}
									</div>
								</div>

								<div class="col-span-12 space-y-2 md:col-span-4">
									<InputLabel class="capitalize">{{ $t('reservation.discount-code') }}</InputLabel>
									<SimpleSelect
										v-model="form.discount_code_id"
										:disabled="form.club_reservation"
										:options="discountCodeOptions"
										class="w-full" />
									<div v-if="form.errors && form.errors.discount_code_id" class="error">
										{{ form.errors.discount_code_id }}
									</div>
								</div>

								<div class="col-span-12 space-y-2 md:col-span-4">
									<InputLabel class="capitalize">{{ $t('reservation.special-offer') }}</InputLabel>
									<SimpleSelect
										v-model="form.special_offer_id"
										:disabled="form.club_reservation"
										:options="specialOfferOptions"
										class="w-full" />
									<div v-if="form.errors && form.errors.special_offer_id" class="error">
										{{ form.errors.special_offer_id }}
									</div>
								</div>
							</div>
							<div
								v-if="features.price_per_person.length && clubSettings['pricePerPersonType']"
								class="col-span-12 space-y-2 md:col-span-3">
								<InputLabel required>
									{{
										usePage().props.clubSettings['price_per_person_type_' + features.price_per_person[0].id]
											.value === 1
											? features.price_per_person[0].translations['reservation-form-person-count-name'] +
											  ' ' +
											  formatAmount(
													usePage().props.clubSettings['price_per_person_' + features.price_per_person[0].id]
														.value,
											  ) +
											  '/' +
											  features.price_per_person[0].translations['person-short']
											: features.price_per_person[0].translations['person-count']
									}}
								</InputLabel>
								<SimpleSelect
									:searchable="true"
									:filterable="true"
									v-model="form.features[features.price_per_person[0].id].person_count"
									:options="pricePerPersonOptions"
									class="dropdown-fixed-height w-full" />
								<div
									v-if="
										form.errors && form.errors[`features.${features.price_per_person[0].id}.person_count`]
									"
									class="error">
									{{ form.errors[`features.${features.price_per_person[0].id}.person_count`] }}
								</div>
							</div>

							<div class="col-span-12 space-y-2 md:col-span-3">
								<InputLabel class="capitalize" required>
									{{ $t('main.price') }} [{{ currencySymbols[usePage().props.club.country.currency] }}]
								</InputLabel>
								<TextInput
									v-model="form.price"
									:disabled="form.club_reservation"
									@input="priceChangedManually" />
								<div v-if="form.errors && form.errors.price" class="error">
									{{ form.errors.price }}
								</div>
							</div>

							<div class="col-span-12 space-y-2 md:col-span-3">
								<InputLabel>{{ capitalize($t('main.final-amount')) }}</InputLabel>
								<TextContainer class="max-w-full">
									{{ formatAmount(form.club_reservation ? 0 : finalPrice) }}
								</TextContainer>
							</div>
							<div class="col-span-12 space-y-2 md:col-span-6">
								<InputLabel class="capitalize" required>{{ $t('reservation.payment-status') }}</InputLabel>
								<SimpleSelect
									v-model="form.status"
									:disabled="form.club_reservation"
									:options="statusOptions"
									:placeholder="$t('reservation.payment-status')"
									class="selected-pr-0 w-full" />
							</div>
							<div class="col-span-12 flex flex-wrap items-center space-y-4 md:col-span-3">
								<div class="flex w-full items-center pt-4">
									<Checkbox
										id="clubReservation"
										v-model="form.club_reservation"
										:checked="form.club_reservation" />
									<InputLabel
										:value="$t('reservation.club-reservation')"
										class="-mt-0.5 ml-3 cursor-pointer"
										for="clubReservation" />
									<div v-if="form.errors && form.errors.club_reservation" class="error">
										{{ form.errors.club_reservation }}
									</div>
								</div>
								<div
									v-if="features.reservation_slot_has_occupied_status.length > 0"
									class="flex w-full items-center">
									<Checkbox
										id="occupiedStatus"
										v-model="form.occupied_status"
										:checked="form.occupied_status" />
									<InputLabel
										:value="
											features.reservation_slot_has_occupied_status[0].translations['slot-occupied-status']
										"
										class="-mt-0.5 ml-3 cursor-pointer"
										for="occupiedStatus" />
									<div v-if="form.errors && form.errors.occupied_status" class="error">
										{{ form.errors.occupied_status }}
									</div>
								</div>
							</div>
						</template>
					</div>
					<hr class="mb-3 mt-4 2xl:mb-5 2xl:mt-4" />
					<div class="grid w-full grid-cols-6 gap-x-5 px-4 md:grid-cols-12 lg:px-14 2xl:gap-y-3">
						<div class="col-span-6 space-y-2">
							<div class="flex justify-between">
								<InputLabel>{{ $t('reservation.customer-note') }}</InputLabel>
								<div
									class="flex items-center space-x-2"
									v-if="showCommentsOnCalendarStatusesVisibility">
									<Checkbox
										id="show_customer_note_on_calendar"
										v-model="form.show_customer_note_on_calendar"
										:checked="form.show_customer_note_on_calendar" />
									<InputLabel for="show_customer_note_on_calendar">
										{{ $t('reservation.show-on-reservation') }}
									</InputLabel>
								</div>
							</div>
							<TextareaInput
								v-model="form.customer_note"
								:placeholder="capitalize($t('main.type-here'))"
								:value="form.customer_note"
								class="h-15" />
							<div v-if="form.errors && form.errors.customer_note" class="error">
								{{ form.errors.customer_note }}
							</div>
						</div>

						<div class="col-span-6 space-y-2">
							<div class="flex justify-between">
								<InputLabel>{{ $t('reservation.club-note') }}</InputLabel>
								<div
									class="flex items-center space-x-2"
									v-if="showCommentsOnCalendarStatusesVisibility">
									<Checkbox
										id="show_club_note_on_calendar"
										v-model="form.show_club_note_on_calendar"
										:checked="form.show_club_note_on_calendar" />
									<InputLabel for="show_club_note_on_calendar">
										{{ $t('reservation.show-on-reservation') }}
									</InputLabel>
								</div>
							</div>
							<TextareaInput
								v-model="form.club_note"
								:placeholder="capitalize($t('main.type-here'))"
								:value="form.club_note"
								class="h-15" />
							<div v-if="form.errors && form.errors.club_note" class="error">
								{{ form.errors.club_note }}
							</div>
						</div>
					</div>
					<div class="mt-3 flex w-full items-center justify-end space-x-10 px-4 lg:px-14 2xl:mt-6">
						<div class="flex items-center space-x-3" v-if="usePage().props.club.sms_notifications_offline">
							<Checkbox
								id="smsNotificationStatus"
								v-model="form.notification.sms"
								:checked="form.notification.sms"
								:disabled="(form.customer.phone?.length ?? 0) === 0" />
							<InputLabel for="smsNotificationStatus" :value="$t('reservation.send-sms-notification')" />
						</div>
						<div class="flex w-1/3 items-center space-x-3 pl-3.25">
							<Checkbox
								id="emailNotificationStatus"
								v-model="form.notification.mail"
								:checked="form.notification.mail"
								:disabled="(form.customer.email?.length ?? 0) === 0" />
							<InputLabel :value="$t('reservation.send-email-notification')" for="emailNotificationStatus" />
						</div>
					</div>

					<div class="mt-4 flex w-full items-center justify-end space-x-10 px-4 lg:px-14 2xl:mt-6">
						<Button
							:disabled="isSubmiting"
							class="lg brand float-right !w-full !px-7 md:!w-[calc(33%-0.7rem)]"
							type="submit">
							{{ $t('reservation.add-reservation') }}
						</Button>
					</div>
				</form>
			</div>
		</div>
	</Transition>
</template>
<script lang="ts" setup>
import {
	Customer,
	DiscountCode,
	Game,
	OpeningHours,
	OpeningHoursException,
	ReservationType,
	SelectOption,
	Slot,
	SpecialOffer,
} from '@/Types/models';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { useString } from '@/Composables/useString';
import { useNumber } from '@/Composables/useNumber';
import { computed, ComputedRef, nextTick, onMounted, Ref, ref, watch } from 'vue';
import dayjs from 'dayjs';
import XIcon from '@/Components/Dashboard/Icons/XIcon.vue';
import Popper from 'vue3-popper';
import CustomersSearchResults from '@/Pages/Club/Reservations/Partials/CustomersSearchResults.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import TextContainer from '@/Components/Dashboard/TextContainer.vue';
import { debouncedRef, refDebounced, watchDebounced } from '@vueuse/core';
import SimpleDatepicker from '@/Components/Dashboard/SimpleDatepicker.vue';
import Datetimepicker from '@/Components/Dashboard/Datetimepicker.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import InfoIcon from '@/Components/Dashboard/Icons/InfoIcon.vue';
import { useCalendar } from '@/Composables/useCalendar';
import axios from 'axios';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import PlusButton from '@/Components/Dashboard/Buttons/PlusButton.vue';
import TrashButton from '@/Components/Dashboard/Buttons/TrashButton.vue';
import { useQueryString } from '@/Composables/useQueryString';
import { wTrans } from 'laravel-vue-i18n';
import BowlingCreate from '@/Pages/Club/Reservations/CustomReservationForm/Bowling/Create.vue';
import BilliardCreate from '@/Pages/Club/Reservations/CustomReservationForm/Billiard/Create.vue';
import Tagify from '@/Components/Dashboard/Tagify.vue';
import { diffDates } from '@fullcalendar/core/internal';

const props = withDefaults(
	defineProps<{
		game: Game;
		showing?: boolean;
		slots: Slot[];
		startDatetime?: string;
		duration?: number;
		slotId?: number | null;
		parentSlotId?: number | null;
	}>(),
	{
		showing: false,
		slotId: null,
		parentSlotId: null,
		duration: null,
		startDatetime: null,
	},
);
// modal controls
const emit = defineEmits<{
	(e: 'closeModal'): void;
	(e: 'storeReservation', responseData: any): void;
}>();
const hide = () => {
	emit('closeModal');
};

// composables
const { capitalize } = useString();
const { formatAmount } = useNumber();
const { modelOptions, numberOptions, statusOptions } = useSelectOptions();
const {
	getFormDictionary,
	features,
	clubSettings,
	customerSearchResults,
	getFormWithFilledCustomer,
	passFeatureKeysToFormDictionary,
	currencySymbols,
	discountCodeOptionsComp,
	specialOfferOptionsComp,
	getCustomersResults,
	getDurationOptions,
	reservationTypeOptionsComp,
	getFeaturesBySlot,
	bulbsOptionsComp,
	formatInputPrice,
	calculatePrice,
	mergeOptions,
	getSlotItemForDisplay,
  showCommentsOnCalendarStatusesVisibility
} = useCalendar(props.game);
const { queryArray } = useQueryString();
// settings
const isSubmiting = ref<boolean>(false); // variable preventing user from double sending a form
let form = null;

function setEmptyForm(withoutPriceLoading: boolean = false) {
	isSubmiting.value = false;
	let formDictionary: Record<string, any> = getFormDictionary({
		'notification.mail': false,
		slots_count: features.book_singular_slot_by_capacity.length > 0 ? null : 1,
		start_at: dayjs(props.startDatetime).format('YYYY-MM-DD HH:mm'),
		game_id: props.game.id,
		status: JSON.stringify({ status: 0, payment_method_id: null }),
	});

	formDictionary = passFeatureKeysToFormDictionary(formDictionary);

	// form initialize
	if (formDictionary.hasOwnProperty('price')) {
		delete formDictionary['price'];
	}
	form = useForm(formDictionary);
	if (!withoutPriceLoading) {
		loadPriceTriggerer.value++;
	}
}

setEmptyForm(true);

// Form select options objects
//@ts-ignore
let clubProp = usePage().props.club;
const reservationTypes: ReservationType[] = clubProp.reservation_types;
const specialOffers: SpecialOffer[] = clubProp.special_offers;
const discountCodes: DiscountCode[] = clubProp.discount_codes;

const isCustomerWithEmailOnline = computed<boolean>(() => {
	if (form.customer.email === undefined || form.customer.email === null || form.customer.email.length === 0) {
		return false;
	}
	if (
		(customerSearchResults.value['email']?.length ?? 0) === 0 ||
		customerSearchResults.value['email'][0].email !== form.customer.email
	) {
		return false;
	}
	return customerSearchResults.value['email'][0]?.online ?? false;
});

let slots = ref<Slot[]>([]);
const durationOptions = computed<SelectOption[]>(() => {
	let openingHoursException = usePage().props.user.club.opening_hours_exceptions.find(
		(openingHoursException: OpeningHoursException) =>
			dayjs(openingHoursException.start_at).isBefore(dayjs(form.start_at).add(1, 'minute')) &&
			dayjs(openingHoursException.end_at).add(1, 'day').isAfter(dayjs(form.start_at).subtract(1, 'minute')),
	);
	let openingHours = usePage().props.user.club.opening_hours.find(
		(openingHours: OpeningHours) => openingHours.day === ((dayjs(form.start_at).day() + 6) % 7) + 1,
	);
	let clubStart = openingHoursException?.['club_start'] ?? openingHours?.['club_start'] ?? '10:00:00';
	let clubEnd = openingHoursException?.['club_end'] ?? openingHours?.['club_end'] ?? '15:00:00';

	let [clubStartHours, clubStartMinutes, clubStartSeconds] = clubStart.split(':');
	let [clubEndHours, clubEndMinutes, clubEndSeconds] = clubEnd.split(':');
	let clubStartMinutesSum: number = parseInt(clubStartHours) * 60 + parseInt(clubStartMinutes);
	let clubEndMinutesSum: number = parseInt(clubEndHours) * 60 + parseInt(clubEndMinutes);

	// if club closes after midnight
	if (clubEndMinutesSum < clubStartMinutesSum) {
		clubEndMinutesSum = clubEndMinutesSum + 24 * 60;
	}
	let result: number = clubEndMinutesSum - clubStartMinutesSum;

	return getDurationOptions(Math.min(result, 1440));
});
const reservationTypeOptions: ComputedRef<SelectOption[]> = reservationTypeOptionsComp;
const bulbsOptions: ComputedRef<SelectOption[]> = bulbsOptionsComp;
const discountCodeOptions: ComputedRef<SelectOption[]> = discountCodeOptionsComp(form);
const specialOfferOptions: ComputedRef<SelectOption[]> = specialOfferOptionsComp(form);

let slotPeopleMaxLimit =
	usePage().props.clubSettings[
		'slot_people_max_limit_' + (features.has_slot_people_limit_settings?.[0]?.id ?? 0)
	]?.value ?? 5;
let pricePerPersonOptions = computed<SelectOption[]>(() => {
	let result = [];

	let maxPersons = slotPeopleMaxLimit * form.slots_count;
	for (let i = 1; i <= maxPersons; i++) {
		result.push({ code: i, label: i.toString() });
	}

	if (features.has_slot_people_limit_settings.length && features.price_per_person.length) {
		form.features[features.price_per_person[0].id]['person_count'] = Math.min(
			form.features[features.price_per_person[0].id]['person_count'],
			maxPersons,
		);
	}

	return result;
});

const parentSlotOptions = computed<SelectOption[]>(() => {
	let resultCollection: Slot[] = slots.value.filter((slot) => slot.slot_id === null);
	if (
		resultCollection.length &&
		features.slot_has_parent.length > 0 &&
		form.features[features.slot_has_parent[0].id]['parent_slot_id'] === null
	) {
		form.features[features.slot_has_parent[0].id]['parent_slot_id'] = resultCollection[0].id;
	}
	return modelOptions(resultCollection, 'id', 'name', false);
});

const slotOptions = computed<SelectOption[]>(() => {
	let slotsCollection: Slot[] = slots.value;
	if (features.slot_has_parent.length > 0) {
		slotsCollection = slotsCollection.filter(
			(slot: Slot) => slot.slot_id === form.features[features.slot_has_parent[0].id].parent_slot_id,
		);
	}
	let result: SelectOption[] = [];
	Object.keys(slotsCollection).forEach((key: any) => {
		if (
			slotsCollection[key]['active'] === true &&
			(!form.slot_ids.includes(slotsCollection[key].id) || !features.book_singular_slot_by_capacity.length)
		) {
			result.push(getSlotItemForDisplay(slotsCollection[key]));
		}
	});
	return result;
});

const slotsCountOptions = computed<SelectOption[]>(() => {
	let result: number = slots.value.length;
	if (features.book_singular_slot_by_capacity.length === 0 && features.person_as_slot.length === 0) {
		let currentSlot: Slot | undefined = slots.value.find((slot: Slot) => slot.id === form.slot_ids[0]);
		let slotsWithTheSamePricelist: Slot[] = slots.value.filter(
			(slot: Slot) => slot.pricelist_id === currentSlot.pricelist_id,
		);
		result = slotsWithTheSamePricelist.length;
	}
	result = Math.max(result, 1);
	if (result < form.slots_count) {
		form.slots_count = result;
	}
	return numberOptions(1, result);
});

const finalPrice = ref<string>('0.00');
const canLoadPrice = computed<boolean>(() => {
  if(features.slot_has_parent.length === 1 && form.features[features.slot_has_parent[0].id]['parent_slot_id'] === null) {
    return false;
  }
  return true;
});

async function loadPrice(customPrice: boolean = false): Promise<void> {
	let price = 0;
	let result = 0;
	let setsPrice = 0;
  if(!canLoadPrice.value) {
    return;
  }
	({ price, form, result, setsPrice } = await calculatePrice(form, customPrice, {
		game_id: props.game.id,
	}));
	if (price) {
		form.price = formatInputPrice(price / 100);
	}
  if(result || setsPrice) {
    finalPrice.value = (result ?? 0).toFixed(2) + (setsPrice ?? 0).toFixed(2);
  }
}

let loadSlotsTriggerer = ref<number>(0);
watchDebounced(
	loadSlotsTriggerer,
	() => {
		if (props.showing) {
			loadSlots();
		}
	},
	{ debounce: 500, maxWait: 1000 },
);

function loadSlots(): void {
	let formTmp = JSON.parse(JSON.stringify(form));
	formTmp['game'] = props.game.id;
	if (features.book_singular_slot_by_capacity.length) {
		formTmp['vacant'] = true;
	}
	formTmp['include_parent_slots'] = true;
	formTmp['include_slot_ids'] = form.slot_ids;
	if (features.slot_has_parent.length > 0) {
		formTmp.features[features.slot_has_parent[0].id]['parent_slot_id'] = null;
	}
	axios.get(route('club.games.slots.search', formTmp)).then(function (response: { data: Slot[] }) {
		slots.value = response.data;
		// if(props.showing && slotOptions.value.length && !slotOptions.value.find((slotOption: Slot) => slotOption.code === singleSlotId.value)) {
		//   singleSlotId.value = null;
		// }
		if (
			features.slot_has_parent.length > 0 &&
			form.features[features.slot_has_parent[0].id]['parent_slot_id'] === 0
		) {
			form.features[features.slot_has_parent[0].id]['parent_slot_id'] = parentSlotOptions.value?.[0]['code'];
		}
	});
}

loadSlotsTriggerer.value++;

// BOOK_SINGULAR_SLOT_BY_CAPACITY feature start
function removeSlotIdFromSlotIds(key) {
	if (form.slot_ids.length > 1) {
		nextTick(() => {
			form.slot_ids.splice(key, 1);
		});
	}
}

function addSlotIdFromSlotIds(key) {
	nextTick(() => {
		form.slot_ids.push(null);
	});
}

const canAddSlotIdToSlotIds = computed<boolean>(() => {
	if (form.slot_ids.length > 10 || form.slot_ids.includes(null)) {
		return false;
	}
	return true;
});
// BOOK_SINGULAR_SLOT_BY_CAPACITY feature end

// An indicator that allows you to calculate the final price after entering your own starting price by the club.
// It prevents the price reload loop.
let priceManuallyChangedIndicator = ref<number>();
let debouncedPriceManuallyChangedIndicator = debouncedRef<number>(priceManuallyChangedIndicator, 1000);

function priceChangedManually() {
	priceManuallyChangedIndicator.value = dayjs().unix();
}

const popperShowingVariables: {
	[key: string]: Ref<boolean>;
} = {
	email: ref(false),
	phone: ref(false),
	last_name: ref(false),
};

// Customer autocompletion
function customerSearch(searchString: string, field: string): void {
	customerSearchResults.value[field] = getCustomersResults(searchString, field);
}

function fillCustomer(customer: Customer): void {
	let oldEmailAddress = form.customer.email;
	form = getFormWithFilledCustomer(form, customer);
	if (form.customer?.email && oldEmailAddress !== form.customer?.email) {
		customerSearchResults.value['email'] = customerSearch(form.customer?.email, 'email');
	}
}

type CustomerFieldKeys = 'last_name' | 'phone' | 'email';
(['last_name', 'phone', 'email'] as CustomerFieldKeys[]).forEach((key) => {
	watch(
		() => form.customer[key],
		async () => {
			customerSearch(form.customer[key], key);
		},
	);
});

function submitForm(): void {
	isSubmiting.value = true;
  let formClone = JSON.parse(JSON.stringify(form));
  formClone.price = parseFloat(formClone.price.replace(",","."));
  if(formClone.custom_price) {
    formClone.price = formClone.price * 100;
  }
	try {
    axios
        .post(route('club.games.reservations.store', { game: props.game }), formClone)
        .then((response) => {
          isSubmiting.value = false;
          emit('storeReservation', response.data);
          setEmptyForm();
        })
        .catch((error) => {
          isSubmiting.value = false;
          if (error.response) {
            form.errors = error.response.data.errors;
            Object.keys(form.errors ?? {}).forEach((key) => {
              form.errors[key] = form.errors[key][0];
            });
          }
        });
  }
  catch(e) {isSubmiting.value = false;}
}

// Check if current values are present in select options
// If not set default values or nulls
function checkValuesAvailabilities(): void {
	let reservationTypeOption: SelectOption[] = reservationTypeOptions.value.filter(
		(reservationTypeOption) => reservationTypeOption.code === form.reservation_type_id,
	);
	if (!reservationTypeOption) {
		form.reservation_type_id = null;
	}

	let specialOfferOption: SelectOption | undefined = specialOfferOptions.value.find(
		(specialOfferOption) => specialOfferOption.code === form.special_offer_id,
	);
	if (!specialOfferOption) {
		form.special_offer_id = null;
	}

	let discountCodeOption: SelectOption | undefined = discountCodeOptions.value.find(
		(discountCodeOption) => discountCodeOption.code === form.discount_code_id,
	);
	if (!discountCodeOption) {
		form.discount_code_id = null;
	}
}

watch(
	() => props.parentSlotId,
	() => {
		isSubmiting.value = false;
		form.features[features.slot_has_parent[0].id]['parent_slot_id'] = props.parentSlotId;
	},
);
watch(
	() => props.slotId,
	() => {
		isSubmiting.value = false;
		singleSlotId.value = props.slotId;
		checkValuesAvailabilities();
	},
);
watch(
	() => props.startDatetime,
	async () => {
		isSubmiting.value = false;
		form.price = 0;
		finalPrice.value = formatAmount(0);
		form.start_at = dayjs(props.startDatetime.split('+')[0]).format('YYYY-MM-DD HH:mm');
	},
);
watch(
	() => props.duration,
	async () => {
		form.duration = props.duration ?? 60;
	},
);

if (features.person_as_slot.length) {
	watch(
		() => form.features[features.person_as_slot[0].id]['persons_count'],
		() => {
			form.slots_count = form.features[features.person_as_slot[0].id]['persons_count'];
		},
	);
}
const priceUpdateWatchVariables = [
	() => form.start_at,
	() => form.duration,
	() => form.slots_count,
	() => form.club_reservation,
	() => form.customer.email,
	() => form.customer?.first_name,
	() => form.customer.last_name,
	() => form.customer.phone,
	() => form.discount_code_id,
	() => form.special_offer_id,
	() => form.features[features.price_per_person[0]?.id ?? 0]?.person_count ?? ref(''),
	() => form.features[features.person_as_slot[0]?.id ?? 0]?.persons_count ?? ref(''),
];

[0, 1, 2, 3, 4, 5, 6, 7, 8, 9].forEach((index) => {
	priceUpdateWatchVariables.push(() => form.slot_ids[index]);
});

priceUpdateWatchVariables.forEach((variable) => {
	watch(variable, () => {
		isSubmiting.value = false;
		loadPriceTriggerer.value++;
		checkValuesAvailabilities();
	});
});

const customPriceUpdateWatchVariables = [() => debouncedPriceManuallyChangedIndicator.value];

customPriceUpdateWatchVariables.forEach((variable) => {
	watch(variable, () => {
		isSubmiting.value = false;
		loadPriceTrueTriggerer.value++;
	});
});

const hidden = ref<boolean>(true);
watch(
	() => props.showing,
	() => {
		if (props.showing) {
			isSubmiting.value = false;
      form.price = formatInputPrice(0);
      finalPrice.value = formatAmount(0);
			loadPriceTriggerer.value++;
			setTimeout(() => {
				hidden.value = false;
			}, 500);
		} else {
			hidden.value = true;
		}
	},
);

const loadPriceTriggerer = ref(0);
watchDebounced(
	loadPriceTriggerer,
	() => {
		form.custom_price = false;
		loadPrice();
	},
	{ debounce: 200, maxWait: 500 },
);

const loadPriceTrueTriggerer = ref(0);
watchDebounced(
	loadPriceTrueTriggerer,
	() => {
		form.custom_price = true;
		loadPrice(true);
	},
	{ debounce: 200, maxWait: 500 },
);

let lastEmailNotificationStatus = usePage().props.clubSettings['email_notifications_status']['value'];
let doNotUpdateLastEmailNotificationStatus = false;
let lastSmsNotificationStatus = usePage().props.clubSettings['sms_notifications_status']['value'];
let doNotUpdateLastSmsNotificationStatus = false;

const singleSlotId = ref<null | number>(null);
watch(singleSlotId, () => {
	form.slot_ids = [singleSlotId.value];
});

watch(
	() => form.notification.sms,
	() => {
		if (!doNotUpdateLastSmsNotificationStatus) {
			lastSmsNotificationStatus = form.notification.sms;
		}
		doNotUpdateLastSmsNotificationStatus = false;
	},
);
watch(
	() => form.customer.phone,
	() => {
		doNotUpdateLastSmsNotificationStatus = true;
		if ((form.customer.phone?.length ?? 0) > 0) {
			form.notification.sms = lastSmsNotificationStatus;
		} else {
			form.notification.sms = false;
		}
	},
);

watch(
	() => form.notification.mail,
	() => {
		if (!doNotUpdateLastEmailNotificationStatus) {
			lastEmailNotificationStatus = form.notification.mail;
		}
		doNotUpdateLastEmailNotificationStatus = false;
	},
);
watch(
	() => form.customer.email,
	() => {
		doNotUpdateLastEmailNotificationStatus = true;
		if ((form.customer.email?.length ?? 0) > 0) {
			form.notification.mail = lastEmailNotificationStatus;
		} else {
			form.notification.mail = false;
		}
	},
);

watchDebounced(
	() => form.customer.email,
	() => {
		axios
			.get(
				route('club.customers.search', {
					search: form.customer.email,
				}),
			)
			.then((response) => {
				if (response.data.length && response.data[0].email === form.customer.email) {
					form.customer.tags = response.data[0].tags.map(function (value, index) {
						return value['name'];
					});
				} else {
					form.customer.tags = [];
				}
			});
	},
	{ debounce: 300, maxWait: 2000 },
);

watch(
	() => form.start_at,
	() => {
		if (!singleSlotId.value) {
			form.slot_ids = [null];
		}
		loadSlotsTriggerer.value++;
	},
);

watch(
	() => form.duration,
	() => {
		if (!props.slotId) {
			form.slot_ids = [null];
		}
		if (features.book_singular_slot_by_capacity.length) {
			loadSlotsTriggerer.value++;
		}
	},
);

if (features.slot_has_parent?.length) {
	watch(
		() => form.features[features.slot_has_parent[0].id]['parent_slot_id'],
		() => {
			if (features.person_as_slot.length > 0) {
				form.features[features.person_as_slot[0].id].new_parent_slot_id =
					form.features[features.slot_has_parent[0].id].parent_slot_id;
			}
			if (form.slot_ids.length !== 1 || props.slotId !== form.slot_ids[0]) {
				form.slot_ids = [null];
			}
      if(features.book_singular_slot_by_capacity.length && slotOptions.value.length) {
        form.slot_ids = [slotOptions.value[0].code];
      }
			if (features.book_singular_slot_by_capacity.length) {
				loadSlotsTriggerer.value++;
			}
			loadPriceTriggerer.value++;
		},
	);
}

onMounted(() => {
  loadSlots();
	isSubmiting.value = false;
});

function updateForm(newForm) {
	form = newForm;
}

function updateTags(e: any): void {
	if (e.target?.value === '') {
		form.customer.tags = [];
	} else {
		form.customer.tags = JSON.parse(e.target?.value ?? '[]').map(function (value, index) {
			return value['value'];
		});
	}
}

const timerInitAllowed = computed<boolean>(() => {
  if(!(features.has_timers?.length)) {
    return false;
  }
	if (clubSettings['timersStatus'] === false) {
		return false;
	}

	const now = dayjs();

	if (form.start_at === null) {
		return false;
	}

	const nowDiff = now.diff(form.start_at, 'minute');

	if (nowDiff >= -30 && nowDiff <= form.duration - 30) {
		return true;
	}

	return false;
});

const selectedSlot = computed(() => {
	if (form.slot_ids[0]) {
		return slots.value.find((slot) => slot.id === form.slot_ids[0]);
	}
	return null;
});

const slotFeatures = computed(() => {
	if (selectedSlot.value !== null) {
		return getFeaturesBySlot(selectedSlot.value);
	}
	return [];
});
</script>
