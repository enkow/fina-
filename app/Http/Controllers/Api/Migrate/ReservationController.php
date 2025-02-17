<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Enums\ReservationSlotStatus;
use App\Events\CalendarDataChanged;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Enums\ReminderType;
use Illuminate\Support\Facades\DB;

use App\Models\ImportedModel;
use App\Models\Reservation;
use App\Models\Game;
use App\Models\Club;
use App\Models\Refund;
use App\Models\User;
use App\Models\Customer;
use App\Models\Slot;
use App\Models\DiscountCode;
use App\Models\Feature;
use App\Models\PaymentMethod;
use App\Models\ReservationNumber;
use App\Models\ReservationSlot;
use App\Models\ReservationType;
use App\Models\Set;
use App\Models\SpecialOffer;

class ReservationController extends Controller
{
	public function group(Request $request)
	{
		$result = [];
		foreach ($request->all() as $key => $reservation) {
			$fakeRequest = new Request();
			$fakeRequest->replace($reservation);
			$res = $this->store($fakeRequest);
			$result[$key] = $res->getOriginalContent();
		}
		return $result;
	}

	public function store(Request $request)
	{
		$reservationMorph = (new Reservation())->getMorphClass();
		$reservationSlotMorph = (new ReservationSlot())->getMorphClass();

		$importModels = ImportedModel::whereIn('old_id', [
			$request->input('group_id'),
			$request->input('club_id'),
			$request->input('customer_id'),
			$request->input('resource_id'),
			$request->input('table_id'),
			$request->input('code_id'),
			$request->input('kind_id'),
			$request->input('special_offer_id'),
			$request->input('canceler_id'),
			$request->input('refund.approver_id_manager'),
			$request->input('id'),
		])
			->whereIn('model_type', [
				$reservationMorph,
				$reservationSlotMorph,
				(new Club())->getMorphClass(),
				(new Customer())->getMorphClass(),
				(new Slot())->getMorphClass(),
				(new DiscountCode())->getMorphClass(),
				(new ReservationType())->getMorphClass(),
				(new SpecialOffer())->getMorphClass(),
				(new User())->getMorphClass(),
			])
			->withTrashed()
			->get();

		$importModel = $importModels
			->where('old_id', $request->input('group_id') ?? $request->input('id'))
			->where('model_type', $reservationMorph)
			->where('extra', $request->input('group_id') === null ? 'solo' : 'group')
			->first();

		if ($importModel && $request->input('group_id') === null) {
			return $this->update($request, $request->input('id'));
		}

		$importSlotModel = $importModels
			->where('old_id', $request->input('id'))
			->where('model_type', $reservationSlotMorph)
			->first();

		if ($importSlotModel) {
			return $this->update($request, $request->input('id'));
		}

		//START validate request

		$assignedClub = $importModels
			->where('old_id', $request->input('club_id'))
			->where('model_type', (new Club())->getMorphClass())
			->first();

		if (!$assignedClub) {
			return response(['status' => 'undefined club'], 400);
		}

		$assignedCustomer = $importModels
			->where('old_id', $request->input('customer_id'))
			->where('model_type', (new Customer())->getMorphClass())
			->first();

		if (!$assignedCustomer && $request->input('customer_id') !== null) {
			return response(['status' => 'undefined customer'], 400);
		}

		$game = Game::where('name', $request->input('game'))
			->with('features')
			->first();


        $durationParts = explode(":", $request->input('duration'));
        $duration = ((int)$durationParts[0]) * 60 + ((int)$durationParts[1]);
        if($game->hasFeature('fixed_reservation_duration')) {
            $startAt = Carbon::parse($request->input('start_at'), 'Europe/Warsaw')->setTimezone(
                config('app.timezone')
            );
            $clubClosingHour = Carbon::parse(explode(" ", $request->input('start_at'))[0]. " " . $assignedClub->model->getOpeningHoursForDate($startAt->format("Y-m-d"))['club_end'], 'Europe/Warsaw')->setTimezone(
                config('app.timezone')
            );
            $duration = $clubClosingHour->diffInMinutes($startAt);
        }

		$availableFeatures = [
			[
				'type' => 'fixed_reservation_duration',
				'value' => ['empty' => true],
			],
			[
				'type' => 'price_per_person',
				'value' => [
					'person_count' => (int) $request->input('person'),
				],
			],
			[
				'type' => 'parent_slot_has_online_status',
				'value' => ['empty' => true],
			],
			[
				'type' => 'reservation_slot_has_display_name',
				'value' => [
					'display_name' => $request->input('club_display'),
				],
			],
		];

		if (in_array($request->input('game'), ['unnumbered_tables'])) {
			$parentSlot = $importModels
				->where('old_id', $request->input('resource_id'))
				->where('model_type', (new Slot())->getMorphClass())
				->where('extra', 'hall')
				->first();

			$availableFeature[] = [
				'type' => 'slot_has_parent',
				'value' => ['parent_slot_id' => $parentSlot->id],
			];
			$availableFeature[] = [
				'type' => 'person_as_slot',
				'value' => [
					'new_parent_slot_id' => $parentSlot->id,
					'persons_count' => (int) $request->input('person'),
				],
			];
		}

		$features = [];

		foreach ($availableFeatures as $availableFeature) {
			if ($game->hasFeature($availableFeature['type'])) {
				$feature = $game->features->where('type', $availableFeature['type'])->first();
				$features[$feature->id] = $availableFeature['value'];
			}
		}

		$assignedSlot = null;
		if (in_array($request->input('game'), ['billiard', 'bowling'])) {
			$assignedSlot = $importModels
				->where('old_id', $request->input('resource_id'))
				->where('model_type', (new Slot())->getMorphClass())
				->where('extra', 'game')
				->first();
		} elseif ($request->input('game') === 'numbered_tables') {
			$assignedSlot = ImportedModel::join('slots', 'imported_models.model_id', '=', 'slots.id')
                ->where('slots.name', $request->input('table_no'))
				->where('old_id', $request->input('table_id'))
				->where('model_type', (new Slot())->getMorphClass())
				->where('extra', 'table')
				->first();
		} else {
			$assignedSlotIds = Reservation::getVacantSlotsIds(
				$game,
				$request->input('start_at'),
				$duration,
				null,
				null,
				(int) $request->input('person'),
				$features,
				false,
				[],
				$assignedClub->model
			);

			$assignedSlot = [];

			foreach ($assignedSlotIds as $assignedSlotId) {
				$assignedSlot[] = Slot::getSlot($assignedSlotId);
			}

			if (count($assignedSlot) === 0) {
				$assignedSlot = null;
			}
		}

		if (!$assignedSlot) {
			return response(['status' => 'undefined slot'], 400);
		}

		$assignedCode = null;
		if ($request->input('code_id') !== null) {
			$assignedCode = $importModels
				->where('old_id', $request->input('code_id'))
				->where('model_type', (new DiscountCode())->getMorphClass())
				->first();
		}

		$assignedKind = null;
		if ($request->input('kind_id') !== null) {
			$assignedKind = $importModels
				->where('old_id', $request->input('kind_id'))
				->where('model_type', (new ReservationType())->getMorphClass())
				->first();
		}

		$assignedSpecialOffer = null;
		if ($request->input('special_offer_id') !== null) {
			$assignedSpecialOffer = $importModels
				->where('old_id', $request->input('special_offer_id'))
				->where('model_type', (new SpecialOffer())->getMorphClass())
				->first();
		}

		$assignedCreator = null;
		if ($request->input('created_by_mail') !== null) {
			$assignedCreator = $assignedClub->model
				->users()
				->where('email', $request->input('created_by_mail'))
				->first();
		}

		$assignedCanceler = null;
		if ($request->input('canceler_id') !== null) {
			$assignedCanceler = $importModels
				->where('old_id', $request->input('canceler_id'))
				->where('model_type', (new User())->getMorphClass())
				->first();
		}

		//END validate request
		//START reservation import

		$data = $request->only(['source', 'customer_note', 'club_note', 'price', 'paid_at', 'created_at']);

		if ($request->input('customer_id') === null) {
			$data['unregistered_customer_data'] = json_decode(
				'{"id":null,"first_name":null,"last_name":null,"phone":null,"email":null,"locale":null}'
			);
		}

		$paymentMethodData = [
			0 => ['type' => 'code', 'value' => 'cash'],
			1 => ['type' => 'code', 'value' => 'cash'],
			2 => ['type' => 'code', 'value' => 'card'],
			3 => ['type' => 'code', 'value' => 'cashless'],
			4 => ['type' => 'code', 'value' => 'cash'],
			5 => ['type' => 'type', 'value' => 'stripe'],
			6 => ['type' => 'type', 'value' => 'stripe'],
		][$request->input('status')];

		$paymentMethod = PaymentMethod::where(
			$paymentMethodData['type'],
			'=',
			$paymentMethodData['value']
		)->first();

		$data['payment_method_id'] = $paymentMethod->id;

		if (!$importModel) {
			$data['customer_id'] = $assignedCustomer->model->id ?? null;
			$data['club_commission'] = 0;
			$data['app_commission'] = 0;
			$data['currency'] = 'PLN';
            if(strlen($data['club_note']) > 0) {
                $data['club_note'] .= "\n\n";
            }
            $data['club_note'] .= "Identyfikatory ze starego systemu:\n". now()->format("ymd")."-".$request->input('id');

			$data['show_customer_note_on_calendar'] = 0;
			$data['show_club_note_on_calendar'] = 0;

			$importedReservation = Reservation::withoutEvents(function () use ($game, $data) {
				return $game->reservations()->create($data);
			});

			$assignedReservation = ImportedModel::create([
				'model_type' => $reservationMorph,
				'model_id' => $importedReservation->id,
				'old_id' => $request->input('group_id') ?? $request->input('id'),
				'extra' => $request->input('group_id') === null ? 'solo' : 'group',
			]);
		} else {
            $data['club_note'] .= $importModel->model->club_note."\n".now()->format("ymd")."-".$request->input('id');
			$importedReservation = Reservation::withoutEvents(function () use ($importModel, $data) {
				$importModel->model->update($data);
			});

			$assignedReservation = $importModel;
		}

		//END reservation import
		//START refund import
		$refundData = $request->input('refund');
		$refund = null;
		if ($refundData) {
			$userRefund = null;
			if ($refundData['approver_id_manager']) {
				$userRefund =
					ImportedModel::where('old_id', $refundData['approver_id_manager'])
						->where('model_type', (new User())->getMorphClass())
						->first()->model ?? null;
			} elseif ($refundData['approver_id_admin']) {
				$userRefund = User::where('type', 'admin')->first();
			}

			$refund = Refund::create([
				'status' => $refundData['status'],
				'approver_id' => $userRefund->id ?? null,
				'approved_at' => Carbon::parse($refundData['approved_at']) ?? null,
				'price' => (int) $request->input('price'),
				'created_at' => $refundData['created_at'],
			]);
		}
		//END refund import
		//START reservation slot import

		$statusTranslated = [
			0 => ReservationSlotStatus::Pending,
			1 => ReservationSlotStatus::Confirmed,
			2 => ReservationSlotStatus::Confirmed,
			3 => ReservationSlotStatus::Confirmed,
			4 => ReservationSlotStatus::Confirmed,
			5 => Carbon::parse($request->input('created_at'))
				->addMinutes(10)
				->isPast()
				? ReservationSlotStatus::Expired
				: ReservationSlotStatus::Pending,
			6 => ReservationSlotStatus::Confirmed,
		][$request->input('status')];

		$dataSlot = $request->only([
			'created_at',
			'price',
			'special_offer_amount',
			'discount_code_amount',
			'cancelation_reason',
			'cancelation_type',
			'canceled_at',
		]);

		$dataSlot['reservation_id'] = $assignedReservation->model->id;
		$dataSlot['club_reservation'] = $request->input('status') == 4;
		$dataSlot['discount_code_id'] = $assignedCode?->model->id ?? null;
		$dataSlot['special_offer_id'] = $assignedSpecialOffer->model->id ?? null;
		$dataSlot['status'] = $statusTranslated;
		$dataSlot['start_at'] = Carbon::parse($request->input('start_at'), 'Europe/Warsaw')->setTimezone(
			config('app.timezone')
		);
		$dataSlot['end_at'] = Carbon::parse($request->input('start_at'), 'Europe/Warsaw')
			->addMinutes($duration)
			->setTimezone(config('app.timezone'));
		$dataSlot['reservation_type_id'] = $assignedKind?->model->id;
		$dataSlot['canceler_id'] = $assignedCanceler?->id;
		$dataSlot['refund_id'] = $refund->id ?? null;
		$dataSlot['final_price'] = $request->input('final_price');
		$dataSlot['creator_id'] = $assignedCreator?->id;

		$importedSlot = null;
		if (gettype($assignedSlot) === 'array') {
			$insertArray = [];
			foreach ($assignedSlot as $_assignedSlot) {
				$dataSlot['slot_id'] = $_assignedSlot->id ?? null;

				$insertSlots[] = $dataSlot;

				$importedSlot = $assignedReservation->model->reservationSlots()->create($dataSlot);

				$insertArray[] = [
					'model_type' => $reservationSlotMorph,
					'model_id' => $importedSlot->id,
					'old_id' => $request->input('id'),
				];
			}
			ImportedModel::insert($insertArray);
		} else {
			$dataSlot['slot_id'] = $assignedSlot->model->id ?? null;

			$importedSlot = $assignedReservation->model->reservationSlots()->create($dataSlot);
			ImportedModel::create([
				'model_type' => $reservationSlotMorph,
				'model_id' => $importedSlot->id,
				'old_id' => $request->input('id'),
			]);
		}

		$assignedReservation->model->reservationNumber()->create();

		//END reservation slot import

		if ($request->input('group_id')) {
			//calculation reservation price

			$price = 0;
			foreach ($assignedReservation->model->reservationSlots as $reservationSlot) {
				$price = $price + $reservationSlot->final_price;
			}
			$assignedReservation->model->price = $price;
			$importedReservation = Reservation::withoutEvents(function () use ($assignedReservation) {
				$assignedReservation->model->save();
			});
		}

		foreach ($features as $id => $value) {
			$importedSlot->features()->attach($id, ['data' => json_encode($value, JSON_THROW_ON_ERROR)]);
		}

		//Sets import
		$assignedSets = ImportedModel::whereIn('old_id', array_column($request->input('sets'), 'set_id'))
			->where('model_type', (new Set())->getMorphClass())
			->get();
		foreach ($request->input('sets') as $set) {
			$assignedSet = $assignedSets->where('old_id', $set['set_id'])->first();

			for ($i = 0; $i < (int) $set['count']; $i++) {
				$importedSlot->sets()->attach($assignedSet->model, [
					'price' => (int) $set['price'],
				]);
			}
		}

        $insertArrayReminders = [];
        $insertArrayReminders[] = [
            'remindable_type' => Reservation::class,
            'remindable_id' => $assignedReservation->model->id,
            'method' => 'email',
            'real' => 0,
            'type' => ReminderType::RatingRequest,
            'created_at' => now(),
            'updated_at' => now(),
        ];
		foreach (['sms', 'mail'] as $method) {
			$insertArrayReminders[] = [
				'remindable_type' => Reservation::class,
				'remindable_id' => $assignedReservation->model->id,
				'method' => $method,
				'real' => 0,
				'type' => ReminderType::NewReservation,
				'created_at' => now(),
				'updated_at' => now(),
			];
			if ($request->input('canceled_at')) {
				$insertArrayReminders[] = [
					'remindable_type' => Reservation::class,
					'remindable_id' => $assignedReservation->model->id,
					'method' => $method,
					'real' => 0,
					'type' => ReminderType::CancelReservation,
					'created_at' => now(),
					'updated_at' => now(),
				];
			}
		}
		DB::table('reminders')->insert($insertArrayReminders);

		//Reservation number import
		if (gettype($assignedSlot) === 'array') {
			$assignedReservation->model->reservationNumber()->create();
		} else {
			$importedSlot->reservationNumber()->create();
		}

		$assignedClub->model->flushCache();

        event(new CalendarDataChanged($assignedClub->model));

		return response(['status' => 'ok', 'action' => 'created'], 201);
	}

	public function update(Request $request, int $old_id)
	{
		$importReservationModel = ImportedModel::where(
			'old_id',
			$request->input('group_id') ?? $request->input('id')
		)
            ->when($request->input('group_id') === null, function ($query) {
                $query->where(function ($query) {
                    $query->where('extra', 'solo');
                    $query->orWhereNull('extra');
                });
            })
            ->when($request->input('group_id') !== null, function ($query) {
                $query->where('extra', 'group');
            })
			->where('extra', $request->input('group_id') === null ? 'solo' : 'group')
			->withTrashed()
			->first();

		if (!$importReservationModel && !$request->input('group_id') === null) {
			return $this->store($request, $request->input('id'));
		}

		$importReservationSlotModels = ImportedModel::where('old_id', $request->input('id'))
			->where('model_type', (new ReservationSlot())->getMorphClass())
			->withTrashed()
			->get();

        if ($request->input('game') === 'numbered_tables') {
            $importSlotModel = ImportedModel::join('slots', 'imported_models.model_id', '=', 'slots.id')
                ->where('slots.name', $request->input('table_no'))
                ->where('old_id', $request->input('table_id'))
                ->where('model_type', (new Slot())->getMorphClass())
                ->where('extra', 'table')
                ->first();
        }
        else {
            $importSlotModel = ImportedModel::where('old_id', $request->input('resource_id'))
                ->where('model_type', (new Slot())->getMorphClass())
                ->withTrashed()
                ->first();
        }

		if (!count($importReservationSlotModels)) {
			return $this->store($request, $request->input('id'));
		}
        if(count($importReservationSlotModels) > 0 && empty($importReservationModel)) {
            $importReservationModel = ImportedModel::where('model_id', $importReservationSlotModels[0]->model->reservation_id)
                ->where('model_type', (new Reservation())->getMorphClass())
                ->first();
        }

		//START validate request

		$assignedClub = ImportedModel::where('old_id', $request->input('club_id'))
			->where('model_type', (new Club())->getMorphClass())
			->first();

		if (!$assignedClub) {
			return response(['status' => 'undefined club'], 400);
		}

		$assignedCustomer = ImportedModel::where('old_id', $request->input('customer_id'))
			->where('model_type', (new Customer())->getMorphClass())
			->first();

		if (!$assignedCustomer && $request->input('customer_id') !== null) {
			return response(['status' => 'undefined customer'], 400);
		}

		$game = Game::where('name', $request->input('game'))->first();

		$countModelsToCreate = 0;
		if ($request->input('game') === 'unnumbered_tables') {
			if (count($importReservationSlotModels) !== (int) $request->input('person')) {
				$parentSlot = ImportedModel::where('old_id', $request->input('resource_id'))
					->where('model_type', (new Slot())->getMorphClass())
					->where('extra', 'hall')
					->withTrashed()
					->first();

				$featuresToChange = [
					'person_as_slot' => [
						'new_parent_slot_id' => $parentSlot->id,
						'persons_count' => (int) $request->input('person'),
					],
				];

				$featureModels = [];
				foreach ($featuresToChange as $featureToChange => $value) {
					$featureModels[] = $game
						->features()
						->where('type', $featureToChange['type'])
						->first();
				}

				foreach ($importReservationSlotModels as $_importSlotModel) {
					foreach ($featureModels as $featureModel) {
						$_importSlotModel->model
							->features()
							->updateExistingPivot($featureModel, $featuresToChange[$featureModel->type]);
					}
				}

				$personDifference = (int) $request->input('person') - count($importReservationSlotModels);

				if ($personDifference < 0) {
					for ($i = 0; $i < $personDifference * -1; $i++) {
						$importReservationSlotModels[$i]->model->delete();
						$importReservationSlotModels[$i]->delete();
					}
				} else {
					$countModelsToCreate = $personDifference;
				}
			}
		}

		$assignedCode = null;
		if ($request->input('code_id') !== null) {
			$assignedCode = ImportedModel::where('old_id', $request->input('code_id'))
				->where('model_type', (new DiscountCode())->getMorphClass())
				->first();
		}

		$assignedKind = null;
		if ($request->input('kind_id') !== null) {
			$assignedKind = ImportedModel::where('old_id', $request->input('kind_id'))
				->where('model_type', (new ReservationType())->getMorphClass())
				->first();
		}

		$assignedSpecialOffer = null;
		if ($request->input('special_offer_id') !== null) {
			$assignedSpecialOffer = ImportedModel::where('old_id', $request->input('special_offer_id'))
				->where('model_type', (new SpecialOffer())->getMorphClass())
				->first();
		}

		$assignedCreator = null;
		if ($request->input('created_by_mail') !== null) {
			$assignedCreator = $assignedClub->model
				->users()
				->where('email', $request->input('created_by_mail'))
				->first();
		}

		$assignedCanceler = null;
		if ($request->input('canceler_id') !== null) {
			$assignedCanceler = ImportedModel::where('old_id', $request->input('canceler_id'))
				->where('model_type', (new User())->getMorphClass())
				->first();
		}

		//END validate request
		//START reservation import

		$data = $request->only(['source', 'customer_note', 'club_note', 'price', 'paid_at', 'created_at']);
		$data['game_id'] = $game->id ?? 1;
		$data['customer_id'] = $assignedCustomer?->model->id ?? null;
        if(strlen($data['club_note']) > 1) {
            $data['club_note'] .= "\n\n";
        }
        $data['club_note'] .= "Identyfikatory ze starego systemu:";
        foreach($importReservationModel->model->reservationSlots as $reservationSlot) {
            $reservationSlotImportedModel = ImportedModel::where('model_id', $reservationSlot->id)
                ->where('model_type', (new ReservationSlot())->getMorphClass())
                ->first();
            $data['club_note'] .= "\n".$reservationSlotImportedModel->created_at->format("ymd")."-".$reservationSlotImportedModel->old_id;
        }

		if ($request->input('customer_id') === null) {
			$data['unregistered_customer_data'] = json_decode(
				'{"id":null,"first_name":null,"last_name":null,"phone":null,"email":null,"locale":null}'
			);
		}

		$paymentMethodData = [
			0 => ['type' => 'code', 'value' => 'cash'],
			1 => ['type' => 'code', 'value' => 'cash'],
			2 => ['type' => 'code', 'value' => 'card'],
			3 => ['type' => 'code', 'value' => 'cashless'],
			4 => ['type' => 'code', 'value' => 'cash'],
			5 => ['type' => 'type', 'value' => 'stripe'],
			6 => ['type' => 'type', 'value' => 'stripe'],
		][$request->input('status')];

		$paymentMethod = PaymentMethod::where(
			$paymentMethodData['type'],
			'=',
			$paymentMethodData['value']
		)->first();

		$data['payment_method_id'] = $paymentMethod->id;

        Reservation::withoutEvents(function () use ($importReservationModel, $data) {
            $importReservationModel->model->update($data);
        });

		//END reservation import
		//START refund import
		$refundData = $request->input('refund');
		$refund = null;
		if ($refundData) {
			$userRefund = null;
			if ($refundData['approver_id_manager']) {
				$userRefund =
					ImportedModel::where('old_id', $old_id)
						->where('model_type', (new User())->getMorphClass())
						->first()->model ?? null;
			} elseif ($refundData['approver_id_admin']) {
				$userRefund = User::where('type', 'admin')->first();
			}

			$refundData = [
				'status' => $refundData['status'],
				'approver_id' => $userRefund->id ?? null,
				'approved_at' => Carbon::parse($refundData['approved_at']) ?? null,
				'price' => (int) $request->input('price'),
			];

			if ($importReservationSlotModels[0]?->model?->refund_id) {
				$refund = Refund::find($importReservationSlotModels[0]?->model?->refund_id);

				Refund::withoutEvents(function () use ($refund, $refundData) {
					$refund->update($refundData);
				});
				$refund = $refund->first();
			} else {
				$refund = Refund::create($refundData);
			}
		}
		//END refund import
		//START reservation slot import

		$statusTranslated = [
			0 => ReservationSlotStatus::Pending,
			1 => ReservationSlotStatus::Confirmed,
			2 => ReservationSlotStatus::Confirmed,
			3 => ReservationSlotStatus::Confirmed,
			4 => ReservationSlotStatus::Confirmed,
			5 => Carbon::parse($request->input('created_at'))
				->addMinutes(10)
				->isPast()
				? ReservationSlotStatus::Expired
				: ReservationSlotStatus::Pending,
			6 => ReservationSlotStatus::Confirmed,
		][$request->input('status')];

		$dataSlot = $request->only([
			'created_at',
			'price',
			'special_offer_amount',
			'discount_code_amount',
			'cancelation_reason',
			'cancelation_type',
			'canceled_at',
		]);

        $durationParts = explode(":", $request->input('duration'));
        $duration = ((int)$durationParts[0]) * 60 + ((int)$durationParts[1]);
        $dataSlot['occupied_status'] = !($duration === 1 && $request->input('game') === 'numbered_tables');
        if($game->hasFeature('fixed_reservation_duration')) {
            $startAt = Carbon::parse($request->input('start_at'), 'Europe/Warsaw')->setTimezone(
                config('app.timezone')
            );
            $clubClosingHour = Carbon::parse(explode(" ", $request->input('start_at'))[0]. " " . $assignedClub->model->getOpeningHoursForDate($startAt->format("Y-m-d"))['club_end'], 'Europe/Warsaw')->setTimezone(
                config('app.timezone')
            );
            $duration = $clubClosingHour->diffInMinutes($startAt);
        }

		$dataSlot['reservation_id'] = $importReservationModel->model->id;
		$dataSlot['club_reservation'] = $request->input('status') == 4;
		$dataSlot['discount_code_id'] = $assignedCode?->model->id ?? null;
		$dataSlot['special_offer_id'] = $assignedSpecialOffer->model->id ?? null;
		$dataSlot['status'] = $statusTranslated;
        $dataSlot['start_at'] = Carbon::parse($request->input('start_at'), 'Europe/Warsaw')->setTimezone(
            config('app.timezone')
        );
        $dataSlot['end_at'] = Carbon::parse($request->input('start_at'), 'Europe/Warsaw')
            ->addMinutes($duration)
            ->setTimezone(config('app.timezone'));
		$dataSlot['reservation_type_id'] = $assignedKind?->model->id;
		$dataSlot['canceler_id'] = $assignedCanceler?->id;
		$dataSlot['refund_id'] = $refund->id ?? null;
		$dataSlot['slot_id'] = $importSlotModel->model->id ?? null;
		$dataSlot['final_price'] =
			$request->input('price') -
			($request->input('discount') ?? (0 + $request->input('additional_discount_value') ?? 0));
		$dataSlot['creator_id'] = $assignedCreator?->id;

		ReservationSlot::withoutEvents(function () use ($importReservationSlotModels, $dataSlot) {
			foreach ($importReservationSlotModels as $importSlotModel) {
				$importSlotModel->model->update($dataSlot);
			}
		});

		$parentSlot = ImportedModel::where('old_id', $request->input('resource_id'))
			->where('model_type', (new Slot())->getMorphClass())
			->where('extra', 'hall')
			->withTrashed()
			->first();

		//Update person count
		if ($game->hasFeature('price_per_person')) {
			ReservationSlot::withoutEvents(function () use ($importReservationSlotModels, $parentSlot, $request) {
				foreach ($importReservationSlotModels as $importSlotModel) {
					$featurePersons = $importSlotModel->model->game->features
						->where('type', 'price_per_person')
						->first();

					$importSlotModel->model->features()->detach($featurePersons->id);

					$importSlotModel->model->features()->attach($featurePersons->id, [
						'data' => json_encode(
							[
								'person_count' => (int) $request->input('person'),
							],
							JSON_THROW_ON_ERROR
						),
					]);
				}
			});
		}

		//Create reservation slot when we should add new slots
		if ($countModelsToCreate) {
			$availableFeatures = [
				[
					'type' => 'fixed_reservation_duration',
					'value' => ['empty' => true],
				],
				[
					'type' => 'slot_has_parent',
					'value' => ['parent_slot_id' => $parentSlot?->id],
				],
				[
					'type' => 'person_as_slot',
					'value' => [
						'new_parent_slot_id' => $parentSlot?->id,
						'persons_count' => (int) $request->input('person'),
					],
				],
				[
					'type' => 'price_per_person',
					'value' => [
						'person_count' => (int) $request->input('person'),
					],
				],
				[
					'type' => 'parent_slot_has_online_status',
					'value' => ['empty' => true],
				],
			];

			$features = [];

			foreach ($availableFeatures as $availableFeature) {
				$feature = $game->features->where('type', $availableFeature['type']);
				$features[$feature->id] = $availableFeature['value'];
			}

			$durationTime = Carbon::createFromFormat('H:i', $request->input('duration'));
			$duration = $durationTime->hour * 60 + $durationTime->minute;

			$assignedSlotIds = Reservation::getVacantSlotsIds(
				$game,
				$request->input('start_at'),
				$duration,
				null,
				null,
				(int) $countModelsToCreate,
				$features,
				false,
				[],
				$assignedClub->model
			);

			foreach ($assignedSlotIds as $assignedSlotId) {
				$_assignedSlot = Slot::find($assignedSlotId);
				$dataSlot['slot_id'] = $_assignedSlot->id ?? null;

				$importedSlot = $importReservationModel->model->reservationSlots()->create($dataSlot);

				ImportedModel::create([
					'model_type' => (new ReservationSlot())->getMorphClass(),
					'model_id' => $importedSlot->id,
					'old_id' => $request->input('id'),
				]);
			}
		}

		//END reservation slot import

		if ($request->input('group_id')) {
			//calculation reservation price

			$price = 0;
			foreach ($importReservationModel->model->reservationSlots as $reservationSlot) {
				$price = $price + $reservationSlot->final_price;
			}
			$importReservationModel->model->price = $price;
			Reservation::withoutEvents(function () use ($importReservationModel) {
				$importReservationModel->model->save();
			});
		}

		if (
			$request->input('customer_id') === null &&
			$game->hasFeature('reservation_slot_has_display_name')
		) {
			$importReservationSlotModels[0]->model
				->features()
				->updateExistingPivot(
					$game->getFeaturesByType('reservation_slot_has_display_name')->first(),
					[
						'data' => json_encode(
							[
								'display_name' => $request->input('club_display'),
							],
							JSON_THROW_ON_ERROR
						),
					]
				);
		}

		//Sets sync
		$importReservationSlotModels[0]->model->sets()->detach();
		foreach ($request->input('sets') as $set) {
			$assignedSet = ImportedModel::where('old_id', $set['set_id'])
				->where('model_type', (new Set())->getMorphClass())
				->first();

			for ($i = 0; $i < (int) $set['count']; $i++) {
				$importReservationSlotModels[0]->model->sets()->attach($assignedSet->model, [
					'price' => (int) $set['price'],
				]);
			}
		}

		//pseudo reminder
		foreach (['sms', 'mail'] as $method) {
			if ($request->input('canceled_at')) {
				$insertArrayReminders[] = [
					'remindable_type' => Reservation::class,
					'remindable_id' => $importReservationModel->model->id,
					'method' => $method,
					'real' => 0,
					'type' => ReminderType::CancelReservation,
					'created_at' => now(),
					'updated_at' => now(),
				];
			} else {
				$insertArrayReminders[] = [
					'remindable_type' => Reservation::class,
					'remindable_id' => $importReservationModel->model->id,
					'method' => $method,
					'real' => 0,
					'type' => ReminderType::UpdateReservation,
					'created_at' => now(),
					'updated_at' => now(),
				];
			}
		}

		DB::table('reminders')->insert($insertArrayReminders);

		$assignedClub->model->flushCache();

        event(new CalendarDataChanged($assignedClub->model));

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$importModel = ImportedModel::where('old_id', $request->input('group_id') ?? $request->input('id'))
			->where('model_type', (new Reservation())->getMorphClass())
			->where('extra', $request->input('group_id') === null ? 'solo' : 'group')
			->first();

		foreach ($importModel->model->reservationSlots as $reservationSlot) {
			$reservationSlot->delete();
		}

		$importModel->model->delete();
		$importModel->delete();
		$importModel->model->club->flushCache();

        event(new CalendarDataChanged($assignedClub->model));

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
