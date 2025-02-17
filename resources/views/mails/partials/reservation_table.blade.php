@php($game = $reservationData['extended']['game'])
@php($slot = $reservationData['extended']['slot'])
@php($customer = $reservationData['extended']['customer'])
@php($club = $reservationData['extended']['slot']->pricelist->club)
@php($discountCode = $reservationData['extended']['discountCode'])
@php($specialOffer = $reservationData['extended']['specialOffer'])
@php($relatedReservations = $reservationData['extended']['relatedReservations'])
@php($features = $reservationData['extended']['features'])
@php($duration = $reservationData['extended']['duration'])
<table class="table" style="margin-top:10px !important; width:100% !important;">
    <tbody>
    <tr>
        <td>{{ __('reservation.place') }}</td>
        <td>
            {{ $club->name }}<br>
            {{ $club->address }}<br>
            {{ $club->postal_code }} {{ $club->city }}
        </td>
    </tr>
    <tr>
        <td>{{ __('customer.singular') }}</td>
        <td>{{ $reservationData['customer_name'] }}</td>
    </tr>
    <tr>
        <td>{{ __('reservation.number') }}</td>
        <td>{{ $reservationData['number'] }}</td>
    </tr>
    <tr>
        <td>{{ __('reservation.reservation-datetime') }}</td>
        <td>
            @if($game->hasFeature('full_day_reservations') && \App\Models\Setting::getClubGameSetting($club->id, 'full_day_reservations_status', $game->id)['value'])
                {{ explode(" ",\Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $reservationData['start_datetime'], "UTC")->timezone($club->country->timezone))[0]  }}
            @else
                {{ \Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $reservationData['start_datetime'], "UTC")->timezone($club->country->timezone)  }}
            @endif
        </td>
    </tr>
    <tr>
        <td>{{ __('reservation.payment-status') }}</td>
        <td>{{ $reservationData['extended']['status']->locale() }}</td>
    </tr>
    @if($discountCode)
        <tr>
            <td>{{ __('discount-code.discount-applied') }}</td>
            <td>
                {{ $discountCode->displayName }}
            </td>
        </tr>
    @endif
    <tr>
        <td>{{ in_array($game,[3,4], true) ? __('reservation.game-price') : ucfirst(__('main.price')) }}</td>
        <td>{{ number_format(($reservationData['extended']['reservation_price'] / 100),2,","," ")." ".$club->country->currency }}</td>
    </tr>
    @if($specialOffer)
        <tr>
            <td>{{ __('special-offer.singular') }}</td>
            <td>{{ $specialOffer->display_name }}</td>
        </tr>
    @endif
    @foreach($reservationData['sets'] as $set)
        <tr>
            <td>{{ $set['count'] . " x " . $set['name'] }}</td>
            <td>{{ number_format(($set['count'] * $set['price'] / 100),2,","," ")." ".$club->country->currency }}</td>
        </tr>
    @endforeach
    @if(count($reservationData['sets']))
        <tr>
            <td>{{ ucfirst(__('main.sum')) }}</td>
            <td>{{ number_format(($reservationData['extended']['reservation_final_price'] / 100),2,","," ")." ".$club->country->currency }}</td>
        </tr>
    @endif
    @if(!$game->hasFeature('person_as_slot') && !$game->hasFeature('book_singular_slot_by_capacity'))
        <tr>
            <td>{{ ucfirst(__('main.game')) }}</td>
            <td>{{ \App\Models\Translation::retrieveGameNames($club)[$slot->pricelist->game->id] }}</td>
        </tr>
    @endif
    @foreach($game->features->where('type','slot_has_convenience') as $convenience)
        @php($reservationFeature = $reservationData['extended']['features']->where('id',$convenience->id)->first())
        @if(!empty($reservationFeature))
            @php($reservationFeaturePivotDataArray = json_decode($reservationFeature->pivot->data, true, 512, JSON_THROW_ON_ERROR))
            @if($reservationFeaturePivotDataArray['status'] === true)
                <tr>
                    <td>{{ \App\Models\Translation::retrieve(countryId: $reservationData['country_id'] ?? $club->country_id, featureId: $convenience->id)['slot-with-convenience'] }}</td>
                    <td>{{ ucfirst(__('main.yes')) }}</td>
                </tr>
            @endif
        @endif
    @endforeach

    @if($game->hasFeature('slot_has_type') && $game->hasFeature('slot_has_subtype'))
        <tr>
            <td>{{ \App\Models\Translation::retrieve(countryId: $reservationData['country_id'] ?? $club->country_id, featureId: $game->features()->where('type','slot_has_type')->first()->id)['mail-label'] }}</td>
            <td>{{ json_decode($slot->features()->where('type','slot_has_type')->first()->pivot->data, true, 512, JSON_THROW_ON_ERROR)['name'] }}
                ({{ json_decode($slot->features()->where('type','slot_has_subtype')->first()->pivot->data, true, 512, JSON_THROW_ON_ERROR)['name'] }}
                )
            </td>
        </tr>
    @elseif($game->hasFeature('slot_has_type'))
        <tr>
            <td>{{ \App\Models\Translation::retrieve(countryId: $reservationData['country_id'] ?? $club->country_id, featureId: $game->features()->where('type','slot_has_type')->first()->id)['mail-label'] }}</td>
            <td>{{ json_decode($slot->features()->where('type','slot_has_type')->first()->pivot->data, true, 512, JSON_THROW_ON_ERROR)['name'] }}</td>
        </tr>
    @elseif($game->hasFeature('slot_has_subtype'))
        <tr>
            <td>{{ \App\Models\Translation::retrieve(countryId: $reservationData['country_id'] ?? $club->country_id, featureId: $game->features()->where('type','slot_has_subtype')->first()->id)['mail-label'] }}</td>
            <td>{{ json_decode($slot->features()->where('type','slot_has_subtype')->first()->pivot->data, true, 512, JSON_THROW_ON_ERROR)['name'] }}</td>
        </tr>
    @endif

    @if(!$game->hasFeature('person_as_slot') && !$game->hasFeature('book_singular_slot_by_capacity'))
        <tr>
            <td>{{ \App\Models\Translation::retrieve(countryId: $reservationData['country_id'] ?? $club->country_id,gameId: $game->id)['slots-quantity'] }}</td>
            <td>{{ 1 + count($relatedReservations) }}</td>
        </tr>
    @endif
    @if($game->hasFeature('price_per_person'))
        @php($pricePerPersonFeature = $reservationData['extended']['features']->where('type','price_per_person')->first())
        @if(!empty($pricePerPersonFeature))
            @php($personCount = json_decode($pricePerPersonFeature->pivot->data, true, 512, JSON_THROW_ON_ERROR)['person_count'])
            @foreach($relatedReservations as $relatedReservation)
                @php($personCount += json_decode(\App\Models\ReservationSlot::find($relatedReservation['id'])->features()->where('type','price_per_person')->first()->pivot->data, true, 512, JSON_THROW_ON_ERROR)['person_count'])
            @endforeach
            <tr>
                <td>{{ \App\Models\Translation::retrieve(countryId: $reservationData['country_id'] ?? $club->country_id, featureId: $game->features()->where('type','price_per_person')->first()->id)['person-count'] }}</td>
                <td>{{ $personCount }}</td>
            </tr>
        @endif
    @endif
    @if(!$game->hasFeature('full_day_reservations') || \App\Models\Setting::getClubGameSetting($club->id, 'fixed_reservation_duration_status', $game->id)['value'] === false || \App\Models\Setting::getClubGameSetting($club->id, 'fixed_reservation_duration_value', $game->id)['value'] !== 24)
        <tr>
            <td>{{ __('reservation.duration-time') }}</td>
            <td>
                {{ sprintf("%sh",(($duration - $duration % 60) / 60)) }}
                @if($duration % 60)
                    {{ sprintf("%smin", $duration % 60) }}
                @endif
            </td>
        </tr>
    @endif
    @if($game->hasFeature('slot_has_parent'))
        <tr>
            <td>{{ \App\Models\Translation::retrieve(countryId: $reservationData['country_id'] ?? $club->country_id, featureId: $game->features()->where('type','slot_has_parent')->first()->id)['parent-slot'] }}</td>
            <td>{{ $reservationData['parent_slot_name'] }}</td>
        </tr>
    @endif

    @if($reservationData['extended']['game']->hasFeature('person_as_slot'))
        <tr>
            <td>{{ \App\Models\Translation::retrieve(countryId: $reservationData['country_id'] ?? $club->country_id, featureId: $game->features()->where('type','person_as_slot')->first()->id)['slots-quantity'] }}</td>
            <td>{{ $reservationData['slots_count'] }}</td>
        </tr>
    @endif
    @if($game->hasFeature('book_singular_slot_by_capacity'))
        <tr>
            <td>{{ \App\Models\Translation::retrieve(countryId: $reservationData['country_id'] ?? $club->country_id, featureId: $game->features()->where('type','book_singular_slot_by_capacity')->first()->id)['widget-capacity-label'] }}</td>
            <td>
                {{ json_decode($slot->features()->where('type','book_singular_slot_by_capacity')->first()->pivot->data, true)['capacity'] }}
            </td>
        </tr>
    @endif
    @if(isset($reservationData['cancelation_reason']) && strlen($reservationData['cancelation_reason']))
        <tr>
            <td>{{ __('widget.cancelation-reason') }}</td>
            <td>{{ $reservationData['cancelation_reason'] }}</td>
        </tr>
    @endif
    @if(isset($reservationData['rate_service']))
        <tr>
            <td>{{ __('rate.service') }}</td>
            <td>{{ $reservationData['rate_service'] }}/5.00</td>
        </tr>
    @endif
    @if(isset($reservationData['rate_atmosphere']))
        <tr>
            <td>{{ __('rate.atmosphere') }}</td>
            <td>{{ $reservationData['rate_atmosphere'] }}/5.00</td>
        </tr>
    @endif
    @if(isset($reservationData['rate_staff']))
        <tr>
            <td>{{ __('rate.staff') }}</td>
            <td>{{ $reservationData['rate_staff'] }}/5.00</td>
        </tr>
    @endif
    @if(isset($reservationData['rate_final']))
        <tr>
            <td>{{ __('rate.final') }}</td>
            <td>{{ $reservationData['rate_final'] }}/5.00</td>
        </tr>
    @endif
    @if(isset($reservationData['rate_content']) && strlen($reservationData['rate_content']))
        <tr>
            <td>{{ ucfirst(__('rate.rate-content')) }}</td>
            <td>{{ $reservationData['rate_content'] }}</td>
        </tr>
    @endif
    </tbody>
</table>