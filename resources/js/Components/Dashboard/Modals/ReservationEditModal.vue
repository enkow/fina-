<template>
	<Transition>
		<div v-if="showing" class="fixed left-0 top-0 z-10 h-screen w-full bg-black/50 py-4.5 delay-100">
			<div
				class="relative top-1/2 m-auto max-h-screen max-w-228 -translate-y-1/2 transform overflow-y-auto rounded-md bg-white !pb-3.5 pb-10 pt-16 md:pt-5 2xl:!pb-5.5">
				<div class="px-4 lg:px-14">
					<div
						class="mb-4 w-full rounded-md bg-black px-3 py-2 text-white xs:px-8.25 sm:w-fit sm:min-w-80 sm:max-w-98">
						<p v-if="reservationNumber !== '0'" class="text-xl font-semibold">
							{{
								$t('reservation.reservation-number-value', {
									value: reservation.number || reservationNumber,
								})
							}}
						</p>
						<p v-else class="text-2xl font-semibold">
							{{ $t('reservation.new-reservation') }} -
							{{ usePage().props.gameTranslations[usePage().props.game.id]['game-name'] }}
						</p>
						<div
							v-if="reservationNumber !== '0'"
							class="grid w-full grid-cols-1 justify-between gap-x-5 pl-0.5 pt-1 text-xxs xs:grid-cols-2">
							<div class="capitalize">
								{{ $t('main.created') }}
								{{ dayjs(reservation.created_at).format('DD.MM.YYYY HH:mm') }}
							</div>
							<div v-if="reservation.extended?.creator_email && reservation.extended?.creator_email.length">
								{{ reservation.extended.creator_email }}
							</div>
						</div>
					</div>
					<div
						class="absolute right-8 top-6 cursor-pointer text-gray-3 transition hover:text-gray-7"
						@click="hide">
						<XIcon />
					</div>
				</div>

				<form @submit.prevent="submitForm">
					<div class="grid grid-cols-12 gap-x-5 gap-y-1 px-4 lg:px-14">
						<div class="col-span-4 -mb-4 space-y-2">
							<InputLabel :required="!form.anonymous_reservation" class="capitalize">
								{{ $t('main.first-name') }}
							</InputLabel>
							<TextInput
								v-model="form.customer.first_name"
								:disabled="form.anonymous_reservation"
								:readonly="isPaid" />
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
								:readonly="isPaid"
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
										class="-mt-[2.85rem] ml-4 w-[calc(100vw-32px)] space-y-2 rounded-md border border-brand-base bg-gray-10 py-2 text-sm md:w-[calc(25vh)] lg:ml-6 lg:w-[254px]">
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
								:readonly="isPaid"
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
										class="-mt-6 ml-4 w-[calc(100vw-32px)] space-y-2 rounded-md border border-brand-base bg-gray-10 py-2 text-sm md:w-[calc(25vh)] lg:ml-6 lg:w-[254px]">
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
								:readonly="isPaid"
								@click="popperShowingVariables['email'].value = !popperShowingVariables['email'].value" />
							<div :class="{ 'opacity-0': !form.errors?.['customer.email'] }" class="error">
								{{ form.errors?.['customer.email'] ?? '&nbsp' }}
							</div>
							<Popper :show="popperShowingVariables.email.value" class="w-full">
								<div class="hidden" />
								<template #content>
									<div
										:class="{
											'opacity-0': form.anonymous_reservation,
										}"
										class="-mt-12 ml-4 w-[calc(100vw-32px)] space-y-2 rounded-md border border-brand-base bg-gray-10 py-2 text-sm md:w-[calc(25vh)] lg:ml-6 lg:w-[254px]">
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
								:readonly="isPaid"
								class="w-full" />
							<div
								v-if="
									form.errors[`features.${features.reservation_slot_has_display_name[0].id}.display_name`]
								"
								class="error">
								{{
									form.errors[`features.${features.reservation_slot_has_display_name[0].id}.display_name.`]
								}}
							</div>
						</div>

						<div class="col-span-4 flex flex-wrap space-y-2">
							<InputLabel class="capitalize">
								{{ $t('tag.plural') }}
							</InputLabel>
							<Tagify
								:onChange="updateTags"
								:disabled="form.anonymous_reservation"
								:readonly="isPaid"
								class="tagify-condensed w-full"
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
									:checked="form.anonymous_reservation"
									:readonly="isPaid" />
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
					<hr class="-mt-2 mb-3" />
					<div
						v-if="true"
						class="grid grid-flow-row-dense grid-cols-12 gap-x-5 gap-y-4 px-4 lg:px-14"
						:class="{
							'gap-y-7': Object.keys(reservation.extended?.relatedReservations ?? {}).length === 0,
						}">
						<BilliardEdit
							v-if="
								form &&
								game.features.find((feature) => feature.type === 'has_custom_views')?.data?.custom_views?.[
									'reservations.create-form'
								] === 'Club/Reservations/CustomReservationForm/Billiard/Create'
							"
							:form="form"
							:reservation="reservation"
							:features="features"
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
							:is-timer-enabled="isTimerEnabled"
							:is-paid="isPaid"
							:bulbsOptions="bulbsOptions"
							:slotFeatures="slotFeatures"
							@update="updateForm"
							@price-changed-manually="priceChangedManually" />
						<BowlingEdit
							v-else-if="
								game.features.find((feature) => feature.type === 'has_custom_views')?.data?.custom_views?.[
									'reservations.create-form'
								] === 'Club/Reservations/CustomReservationForm/Bowling/Create'
							"
							:form="form"
							:reservation="reservation"
							:features="features"
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
							:is-timer-enabled="isTimerEnabled"
							:is-paid="isPaid"
							@update="updateForm"
							@price-changed-manually="priceChangedManually" />
						<template v-else>
							<div v-if="features.person_as_slot.length > 0" class="col-span-3 space-y-2">
								<InputLabel class="capitalize" required>
									{{ features.person_as_slot[0].translations['slots-quantity'] }}
								</InputLabel>
								<TextInput
									v-model="form.features[features.person_as_slot[0].id]['persons_count']"
									class="w-full"
									:disabled="isPaid"
									:readonly="isPaid" />
								<div
									v-if="form.errors[`features.${features.person_as_slot[0].id}.persons_count`]"
									class="error">
									{{ form.errors[`features.${features.person_as_slot[0].id}.persons_count`] }}
								</div>
							</div>
							<div class="col-span-3 space-y-2">
								<InputLabel class="capitalize" required>{{ $t('main.date') }}</InputLabel>
								<Datetimepicker
									v-model="form.start_at"
									:minutes-increment="clubSettings['calendarTimeScale']"
									:disabled="isPaid"
									:readonly="isPaid"
									placeholder=" " />
								<div v-if="form.errors.start_at" class="error">
									{{ form.errors.start_at }}
								</div>
								<div v-if="features.person_as_slot.length && form.errors.slot_ids" class="error">
									{{ form.errors.slot_ids }}
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

							<div
								v-if="features.person_as_slot.length === 0"
								:class="{
									'col-span-6': features.book_singular_slot_by_capacity.length > 0,
									'col-span-3': features.book_singular_slot_by_capacity.length === 0,
								}"
								class="space-y-2">
								<InputLabel class="capitalize" required>
									{{ usePage().props.gameTranslations[game.id]['slot-name'] }}
								</InputLabel>
								<SimpleSelect v-model="singleSlotId" :options="slotOptions" class="w-full" />
								<div v-if="form.errors.slot_ids" class="error">
									{{ form.errors.slot_id }}
								</div>
                <div v-if="form.errors.slot_id" class="error">
                  {{ form.errors.slot_id }}
                </div>
							</div>

							<div
								v-if="
									!clubSettings['fullDayReservationStatus'] && !clubSettings['fixedReservationDurationStatus']
								"
								class="col-span-3 space-y-2">
								<InputLabel class="capitalize" required>{{ $t('reservation.duration-time') }}</InputLabel>
								<SimpleSelect
									v-model="form.duration"
									:options="durationOptions"
									class="w-full"
									:disabled="isPaid"
									:readonly="isPaid" />
								<div v-if="form.errors.duration" class="error">
									{{ form.errors.duration }}
								</div>
							</div>
							<div class="col-span-12 grid grid-cols-12 gap-x-5">
								<div class="col-span-4 space-y-2">
									<InputLabel class="capitalize">{{ $t('reservation.reservation-type') }}</InputLabel>
									<SimpleSelect
										:placeholder="$t('reservation-type.no-reservation-type')"
										v-model="form.reservation_type_id"
										:options="reservationTypeOptions"
										class="w-full" />
									<div v-if="form.errors.reservation_type_id" class="error">
										{{ form.errors.reservation_type_id }}
									</div>
								</div>
								<div class="col-span-4 space-y-2">
									<InputLabel class="capitalize">{{ $t('reservation.discount-code') }}</InputLabel>
									<SimpleSelect
										:placeholder="$t('discount-code.no-discount-code')"
										v-model="form.discount_code_id"
										:disabled="form.club_reservation || isPaid"
										:options="discountCodeOptions"
										:readonly="isPaid"
										class="w-full" />
									<div v-if="form.errors.discount_code_id" class="error">
										{{ form.errors.discount_code_id }}
									</div>
								</div>
								<div class="col-span-4 space-y-2">
									<InputLabel class="capitalize">{{ $t('reservation.special-offer') }}</InputLabel>
									<SimpleSelect
										:placeholder="$t('special-offer.no-special-offer')"
										v-model="form.special_offer_id"
										:disabled="form.club_reservation || isPaid"
										:options="specialOfferOptions"
										:readonly="isPaid"
										class="w-full" />
									<div v-if="form.errors.special_offer_id" class="error">
										{{ form.errors.special_offer_id }}
									</div>
								</div>
							</div>

							<div
								v-if="features.price_per_person.length && clubSettings['pricePerPersonType']"
								class="col-span-3 space-y-2">
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
									:options="pricePerPersonOptions"
									v-model="form.features[features.price_per_person[0].id].person_count"
									:disable="isPaid"
									:readonly="isPaid"
									class="dropdown-fixed-height w-full" />
								<div
									v-if="form.errors[`features.${features.price_per_person[0].id}.person_count`]"
									class="error">
									{{ form.errors[`features.${features.price_per_person[0].id}.person_count`] }}
								</div>
							</div>

							<div class="col-span-3 space-y-2">
								<InputLabel class="capitalize" required>
									{{ $t('main.price') }} [{{ currencySymbols[usePage().props.club.country.currency] }}]
								</InputLabel>
								<TextInput
									v-model="form.price"
									:disabled="form.club_reservation"
									:disable="isPaid"
									:readonly="isPaid"
									@input="priceChangedManually" />
								<div v-if="form.errors.price" class="error">
									{{ form.errors.price }}
								</div>
							</div>

							<div class="col-span-3 space-y-2">
								<InputLabel>{{ capitalize($t('main.final-amount')) }}</InputLabel>
								<TextContainer class="col-span-2">
									{{ formatAmount(form.club_reservation ? 0 : finalPrice) }}
								</TextContainer>
							</div>
							<div
								class="col-span-6 space-y-2"
								v-if="form.status === 0 || reservation.payment_method_online == false">
								<InputLabel class="capitalize" required>{{ $t('reservation.payment-status') }}</InputLabel>
								<SimpleSelect
									v-model="form.status"
									:disabled="form.club_reservation || isPaid"
									:options="statusOptions"
									:readonly="isPaid"
									:placeholder="$t('reservation.payment-status')"
									class="w-full" />
							</div>
							<div class="col-span-3 flex flex-wrap items-center space-y-4">
								<div class="flex w-full items-center pt-4">
									<Checkbox
										id="clubReservation"
										v-model="form.club_reservation"
										:checked="form.club_reservation" />
									<InputLabel
										:value="$t('reservation.club-reservation')"
										class="-mt-0.5 ml-3 cursor-pointer"
										for="clubReservation" />
									<div v-if="form.errors.club_reservation" class="error">
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
									<div v-if="form.errors.occupied_status" class="error">
										{{ form.errors.occupied_status }}
									</div>
								</div>
							</div>
						</template>
					</div>
					<hr class="mb-3 mt-3" />
					<div
						class="grid w-full grid-cols-3 gap-x-5 gap-y-3 px-4 md:grid-cols-6 lg:px-14"
						:class="{
							'mt-5': Object.keys(reservation.extended?.relatedReservations ?? {}).length === 0,
						}">
						<div class="col-span-3 space-y-2">
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
                :disable="isPaid"
                :readonly="isPaid"
								class="h-15" />
							<div v-if="form.errors.customer_note" class="error">
								{{ form.errors.customer_note }}
							</div>
						</div>

						<div class="col-span-3 space-y-2">
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
							<div v-if="form.errors.club_note" class="error">
								{{ form.errors.club_note }}
							</div>
						</div>
					</div>
					<div class="mt-1 flex w-full items-center justify-end space-x-10 px-4 lg:px-14">
						<div
							v-if="
								features.person_as_slot.length === 0 &&
								Object.keys(reservation.extended.relatedReservations).length
							"
							class="flex items-center">
							<Checkbox
								id="applyToAllReservations"
								v-model="form.apply_to_all_reservations"
								:checked="form.apply_to_all_reservations" />
							<InputLabel
								:value="$t('reservation.apply-to-all-reservations')"
								class="ml-3 cursor-pointer !text-base"
								for="applyToAllReservations" />
						</div>
						<Button
							class="lg brand float-right !w-full !px-5 md:!w-[calc(33%-0.7rem)]"
							:class="{
								'mb-2 mt-3': Object.keys(reservation.extended?.relatedReservations ?? {}).length === 0,
							}"
							type="submit">
							{{ $t('reservation.update-reservation') }}
						</Button>
					</div>
					<div
						v-if="Object.keys(reservation.extended?.relatedReservations ?? {}).length ?? 0"
						class="mt-0 grid w-full grid-cols-3 items-center gap-x-5 px-4 lg:px-14">
						<div class="col-span-3 mb-1 text-lg font-normal">
							{{ $t('reservation.group-reservation') }}
						</div>
						<div class="space-y-1">
							<InputLabel>{{ capitalize($t('main.sum')) }}</InputLabel>
							<TextContainer>{{ formatAmount(totalPrice) }}</TextContainer>
						</div>
						<div class="space-y-1">
							<InputLabel>{{ capitalize($t('main.paid')) }}</InputLabel>
							<TextContainer>{{ formatAmount(totalPaid) }}</TextContainer>
						</div>
						<div class="space-y-1">
							<InputLabel>{{ capitalize($t('main.to-pay')) }}</InputLabel>
							<TextContainer>
								{{ formatAmount(totalPrice - totalPaid) }}
							</TextContainer>
						</div>
					</div>
				</form>
			</div>
		</div>
	</Transition>
</template>

<style scoped>
.scrollbar::-webkit-scrollbar-track {
	-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
	background-color: #f5f5f5;
}

.scrollbar::-webkit-scrollbar {
	width: 6px;
	background-color: #f5f5f5;
}

.scrollbar::-webkit-scrollbar-thumb {
	@apply bg-brand-base/70;
}
</style>

<script lang="ts" setup>
import XIcon from '@/Components/Dashboard/Icons/XIcon.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import Popper from 'vue3-popper';
import {
	Customer,
	DiscountCode,
	Feature,
	Game,
	Reservation,
	ReservationType,
	SelectOption,
	Slot,
	SpecialOffer,
} from '@/Types/models';
import { useForm, usePage } from '@inertiajs/vue3';
import { useString } from '@/Composables/useString';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import { computed, ComputedRef, onMounted, onUnmounted, Ref, ref, watch } from 'vue';
import { emptyReservation } from '@/Utils';
import axios from 'axios';
import dayjs from 'dayjs';
import CustomersSearchResults from '@/Pages/Club/Reservations/Partials/CustomersSearchResults.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import SimpleDatepicker from '@/Components/Dashboard/SimpleDatepicker.vue';
import Datetimepicker from '@/Components/Dashboard/Datetimepicker.vue';
import TextContainer from '@/Components/Dashboard/TextContainer.vue';
import { useNumber } from '@/Composables/useNumber';
import { debouncedRef, watchDebounced } from '@vueuse/core';
import { useCalendar } from '@/Composables/useCalendar';
import BowlingEdit from '@/Pages/Club/Reservations/CustomReservationForm/Bowling/Edit.vue';
import BilliardEdit from '@/Pages/Club/Reservations/CustomReservationForm/Billiard/Edit.vue';
import Tagify from '@/Components/Dashboard/Tagify.vue';
import { usePanelStore } from '@/Stores/panel';

const props = withDefaults(
	defineProps<{
		game: Game;
		reservationNumber: string | number;
		showing?: boolean;
	}>(),
	{
		showing: false,
	},
);

// modal controls
const emit = defineEmits<{
	(e: 'closeModal'): void;
	(e: 'updateReservation', reservationNumber: string): void;
}>();
const hide = () => {
	reservation.value = emptyReservation;
	reservationLoaded = false;
	hidden.value = true;
	emit('closeModal');
};

// composables
const { capitalize } = useString();
const { modelOptions, statusOptions } = useSelectOptions();
const { formatAmount } = useNumber();
const {
	getFormDictionary,
	getFormWithFilledCustomer,
	features,
	getFeaturesBySlot,
	clubSettings,
	currencySymbols,
	formatInputPrice,
	customerSearchResults,
	getCustomersResults,
	passFeatureKeysToFormDictionary,
	discountCodeOptionsComp,
	specialOfferOptionsComp,
	bulbsOptionsComp,
	fillReservationForm,
	getDurationOptions,
	reservationTypeOptionsComp,
	calculatePrice,
  showCommentsOnCalendarStatusesVisibility
} = useCalendar(props.game);

// main variables
const hidden = ref<boolean | undefined>(undefined);

// base form data
let duration: number = 60;
if (features.fixed_reservation_duration.length) {
	duration = parseInt(clubSettings['fullDayReservationStatus'] ? '1' : '0');
	duration = duration === 0 ? duration : 60;
	duration = duration - (duration % clubSettings['calendarTimeScale']);
}
let formDictionary: Record<string, any> = getFormDictionary({
	duration: 60,
	game_id: props.game.id,
	custom_price: false,
});

// put feature keys to form dictionary
formDictionary = passFeatureKeysToFormDictionary(formDictionary);

// form initialize
const isSubmiting = ref<boolean>(false); // variable preventing user from double sending a form
let form = useForm(formDictionary);

function submitForm(): void {
	isSubmiting.value = true;
	if (!props.reservationNumber) {
		return;
	}
	axios
		.post(
			route('club.reservations.update', {
				reservationNumber: props.reservationNumber,
			}),
			form,
		)
		.then(() => {
			isSubmiting.value = false;
			emit('updateReservation', props.reservationNumber);
			reservationLoaded = false;
			hidden.value = true;
		})
		.catch((response) => {
			form.errors = response.response.data.errors;
			Object.keys(form.errors).forEach((key) => {
				form.errors[key] = form.errors[key][0];
			});
		});
}

// Form models collections
const reservationTypes: ReservationType[] = usePage().props.club.reservation_types;
const specialOffers: SpecialOffer[] = usePage().props.club.special_offers;
const discountCodes: DiscountCode[] = usePage().props.club.discount_codes;
let slots = ref<Slot[]>([]);

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

// Form select options objects
const reservationTypeOptions: ComputedRef<SelectOption[]> = reservationTypeOptionsComp;
const specialOfferOptions: ComputedRef<SelectOption[]> = specialOfferOptionsComp(form);
const bulbsOptions: ComputedRef<SelectOption[]> = bulbsOptionsComp;

const parentSlotOptions = computed<SelectOption[]>(() => {
	let resultCollection: Slot[] = slots.value.filter((slot) => slot.slot_id === null);
	return modelOptions(resultCollection, 'id', 'name', false);
});

let slotPeopleMaxLimit =
	usePage().props.clubSettings[
		'slot_people_max_limit_' + (features.has_slot_people_limit_settings?.[0]?.id ?? 0)
	]?.value ?? 5;
let pricePerPersonOptions = ref<SelectOption[]>([]);
for (let i = 1; i <= slotPeopleMaxLimit; i++) {
	pricePerPersonOptions.value.push({ code: i, label: i.toString() });
}

function getSlotItemForDisplay(slot: Slot) {
	let resultItem: SelectOption = {
		code: slot.id,
		label: slot.name,
	};
	// if game has slot_has_lounge filter and club has this feature enabled add min-max person info
	// to slot display name
	if (features.slot_has_lounge.length && clubSettings['lounges_status']) {
		let pivotData: string = slot.features.find((feature: Feature) => feature.type === 'slot_has_lounge')
			?.pivot?.data as string;
		if (pivotData) {
			let slotLoungeFeaturePivotData = JSON.parse(pivotData);
			if (slotLoungeFeaturePivotData.status) {
				resultItem.label =
					resultItem.label +
					` (${slotLoungeFeaturePivotData.min}-${slotLoungeFeaturePivotData.max} ` +
					features.slot_has_lounge[0]?.translations['lounge-capacity-items'] +
					')';
			}
		}
	}
	let pivotData: string = slot.features.find(
		(feature: Feature) => feature.type === 'book_singular_slot_by_capacity',
	)?.pivot?.data as string;
	if (pivotData) {
		let slotCapacityFeaturePivotData = JSON.parse(pivotData);
		resultItem.label = resultItem.label + ` (${slotCapacityFeaturePivotData.capacity}-os)`;
	}
	return resultItem;
}

const slotOptions = computed<SelectOption[]>(() => {
	let slotsCollection: Slot[] = slots.value;
	if (features.slot_has_parent.length > 0) {
		slotsCollection = slotsCollection.filter(
			(slot: Slot) => slot.slot_id === form.features[features.slot_has_parent[0].id].parent_slot_id,
		);
	}

	if (reservation.value.status !== 0) {
		slotsCollection = slotsCollection.filter(
			(slot: Slot) => slot.pricelist_id == reservation.value.extended?.slot.pricelist_id,
		);
	}
	let result: SelectOption[] = [];
	Object.keys(slotsCollection).forEach((key: any) => {
		if (slotsCollection[key]['active'] === true) {
			result.push(getSlotItemForDisplay(slotsCollection[key]));
		}
	});

	return result;
});
const discountCodeOptions: ComputedRef<SelectOption[]> = discountCodeOptionsComp(form);
const durationOptions: SelectOption[] = getDurationOptions();

// loaders
const finalPrice = ref<string>('0.00');

// An indicator that allows you to calculate the final price after entering your own starting price by the club.
// It prevents the price reload loop.
let priceManuallyChangedIndicator = ref<number>();
let debouncedPriceManuallyChangedIndicator = debouncedRef<number>(priceManuallyChangedIndicator, 1000);

function priceChangedManually() {
	priceManuallyChangedIndicator.value = dayjs().unix();
	form.special_offer_id = null;
}

let preventSingleSlotIdReset = true;
let reservationLoaded = false;
const panel = usePanelStore();
watch(
	() => panel.currentShowingReservation,
	() => {
		reservationLoaded = false;
		if (panel.currentShowingReservation) {
			preventSingleSlotIdReset = true;

			let data: Record<string, any> = {};
			data['adjustStartAt'] = true;
			data['adjustDuration'] = true;
			data['price'] = formatInputPrice(
				(parseInt(panel.currentShowingReservation.extended?.price?.toString() ?? '0') / 100).toFixed(2),
			);
			data['status'] = JSON.stringify({
				status: panel.currentShowingReservation.status,
				payment_method_id: panel.currentShowingReservation.status
					? panel.currentShowingReservation.payment_method_id
					: null,
			});
			form = fillReservationForm(form, panel.currentShowingReservation, data);
			singleSlotId.value = panel.currentShowingReservation.slot_id;

			finalPrice.value = formatInputPrice(
				parseInt(panel.currentShowingReservation?.final_price?.toString()).toFixed(2),
			);
			// Load parent slot id
			checkValuesAvailabilities();
			setTimeout(() => {
				preventSingleSlotIdReset = false;
				hidden.value = hidden.value === undefined;
				reservationLoaded = true;
			}, 1000);
		}
	},
);
const reservation = computed<Reservation>(() => {
	return panel.currentShowingReservation ?? emptyReservation;
});

const isTimerEnabled = computed<boolean>(() => {
	return [2, 3].includes(reservation.value.timer_status);
});

const totalPrice = computed<number>(() => {
	return (
		(reservation.value.extended?.total_price ?? 0) -
		reservation.value.final_price +
		parseInt(finalPrice.value)
	);
});
const totalPaid = computed<number>(() => {
	let result: number = reservation.value.extended?.total_paid ?? 0;
	if (reservation.value.status === 0 && JSON.parse(form.status)['status'] === 1) {
		result += parseInt(finalPrice.value);
	} else if (reservation.value.status === 1 && JSON.parse(form.status)['status'] === 0) {
		result -= parseInt(finalPrice.value);
	}
	return result;
});

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
	watchDebounced(
		() => form.customer[key],
		async () => {
			if (popperShowingVariables[key].value) {
				customerSearch(form.customer[key], key);
			}
		},
		{ debounce: 500, maxWait: 1000 },
	);
});

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

	let slotIdOption: SelectOption[] = slotOptions.value.filter(
		(slotOption) => slotOption.code === singleSlotId.value,
	);
	if (!slotIdOption) {
		singleSlotId.value = slotOptions.value[0].code;
	}
}
if (features.person_as_slot?.length) {
	watch(
		() => form.features[features.person_as_slot[0].id].new_parent_slot_id,
		async () => {
			form.slot_ids = [null];
		},
	);
}

// if user is allowed to choose only one slot_id, we have to merge it into form.
// in form slot ids are stored in slot_ids key
const singleSlotId = ref<number | null>(null);
watch(singleSlotId, () => {
	form.slot_ids = [singleSlotId.value];
});

let selectedSingleSlotIdWithoutDisablingInput: number | null = null;
watch(
	() => form.status,
	async (newValue: string, oldValue: string) => {
		let oldPaymentStatus: number = parseInt(JSON.parse(oldValue).status);
		let newPaymentStatus: number = parseInt(JSON.parse(newValue).status);
		if (oldPaymentStatus === 0 && newPaymentStatus === 1) {
			selectedSingleSlotIdWithoutDisablingInput = singleSlotId.value;
			singleSlotId.value = parseInt(reservation.value.slot_id);
		} else if (oldPaymentStatus === 1 && newPaymentStatus === 0) {
			if (selectedSingleSlotIdWithoutDisablingInput) {
				singleSlotId.value = selectedSingleSlotIdWithoutDisablingInput;
			}
		}
	},
);

if (features.slot_has_parent.length) {
	watch(
		() => form.features[features.slot_has_parent[0].id].parent_slot_id,
		() => {
			loadSlots();
			if (!preventSingleSlotIdReset) {
				singleSlotId.value = null;
			}
		},
	);
}

// Protection against multiple reloading of the price. When a price change is required, we add <triggerer> 1 to the selected variable
const loadPriceTriggerer = ref(0);
watchDebounced(
	loadPriceTriggerer,
	() => {
		if (reservationLoaded) {
			form.custom_price = false;
			loadPrice();
		}
	},
	{ debounce: 300, maxWait: 800 },
);

const loadPriceTrueTriggerer = ref(0);
watchDebounced(
	loadPriceTrueTriggerer,
	() => {
		if (reservationLoaded) {
			form.custom_price = true;
			loadPrice(true);
		}
	},
	{ debounce: 300, maxWait: 800 },
);

// Variables whose change requires recalculation of the amount, omitting the initial amount entered in the form
const priceUpdateWatchVariables = [
	() => form.customer?.first_name,
	() => form.customer.phone,
	() => form.discount_code_id,
	() => form.special_offer_id,
	() => form.start_at,
	() => form.duration,
	() => form.slot_ids,
	() => form.club_reservation,
	() => form.apply_to_all_reservations,
	() => form.features[features.price_per_person[0]?.id ?? 0]?.person_count ?? ref(''),
	() => form.features[features.person_as_slot[0]?.id ?? 0]?.persons_count ?? ref(''),
];
priceUpdateWatchVariables.forEach((variable) => {
	watch(variable, () => {
		checkValuesAvailabilities();
	});
	watchDebounced(
		variable,
		() => {
			loadPriceTriggerer.value++;
		},
		{ debounce: 500, maxWait: 3000 },
	);
});

// Variables whose change requires recalculation of the amount, taking into account the initial amount entered in the form
const customPriceUpdateWatchVariables = [
	() => debouncedPriceManuallyChangedIndicator.value,
	() => form.features[features.price_per_person[0]?.id ?? 0]?.person_count ?? ref(''),
];

customPriceUpdateWatchVariables.forEach((variable) => {
	watchDebounced(
		variable,
		() => {
			loadPriceTrueTriggerer.value++;
			checkValuesAvailabilities();
		},
		{ debounce: 500, maxWait: 3000 },
	);
});

if (features.slot_has_parent?.length) {
	watch(
		() => form.features[features.slot_has_parent[0].id]['parent_slot_id'],
		() => {
			if (features.person_as_slot.length > 0) {
				form.features[features.person_as_slot[0].id].new_parent_slot_id =
					form.features[features.slot_has_parent[0].id].parent_slot_id;
			}
			if (form.slot_ids.length !== 1 || reservation.value.slot_id !== form.slot_ids[0]) {
				form.slot_ids = [null];
			}
			if (features.book_singular_slot_by_capacity.length) {
				loadSlots();
			}
			loadPriceTriggerer.value++;
		},
	);
}

// price recalculating using backend
async function loadPrice(customPrice: boolean = false): Promise<void> {
  if(form.status === 1 && reservation.value.payment_method_online) {
    form.custom_price = true;
    return;
  }
	isSubmiting.value = false;
	let price = 0;
	let result = 0;
	let setsPrice = 0;

	({ price, form, result, setsPrice} = await calculatePrice(form, customPrice, {
		reservation_number_id: props.reservationNumber,
	}));
	if (price && customPrice === form.custom_price && reservationLoaded) {
		finalPrice.value = result + setsPrice + reservation.value.club_commission_partial;
		form.price = formatInputPrice((price / 100).toFixed(2));
	}
}
function loadSlots() {
	if (hidden.value === false) {
		return;
	}
	let formTmp = JSON.parse(JSON.stringify(form));
	axios
		.get(
			route('club.games.slots.search', {
				game: props.game,
				vacant: !!features.book_singular_slot_by_capacity.length,
				start_at: form.start_at,
				duration: form.duration,
				features: form.features,
				include_slot_ids: preventSingleSlotIdReset ? [singleSlotId.value] : [],
				include_parent_slots: true,
			}),
		)
		.then(function (response: { data: Slot[] }) {
			slots.value = response.data;
			if (
				features.slot_has_parent.length > 0 &&
				form.features[features.slot_has_parent[0].id]['parent_slot_id'] === 0
			) {
				form.features[features.slot_has_parent[0].id]['parent_slot_id'] =
					parentSlotOptions.value?.[0]['code'];
			}
			checkValuesAvailabilities();
		});
}

// Variables whose change requires reloading the slots
const slotsUpdateWatchVariables = [() => form.start_at, () => form.duration];
if (features.person_as_slot?.length) {
	slotsUpdateWatchVariables.push(() => form.features[features.person_as_slot[0].id].new_parent_slot_id);
}
if (features.parent_slot?.length) {
	slotsUpdateWatchVariables.push(() => form.features[features.parent_slot[0].id].parent_slot_id);
}
slotsUpdateWatchVariables.forEach((variable) => {
	watch(variable, async () => {
		loadSlots();
	});
});

function updateForm(newForm) {
	form = newForm;
}

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

function updateTags(e: any): void {
	if (e.target?.value === '') {
		form.customer.tags = [];
	} else {
		form.customer.tags = JSON.parse(e.target?.value ?? '[]').map(function (value, index) {
			return value['value'];
		});
	}
}

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

const isPaid = computed(() => {
	return reservation.value.status !== 0;
});
</script>
