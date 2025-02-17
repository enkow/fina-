<?php

namespace App\Events;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerVerifiedEvent implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public CustomerResource $customer;
	public string $channel;

	public function __construct(Customer $customer)
	{
		$this->channel = $customer->widget_channel;
		$this->customer = new CustomerResource($customer);
	}

	public function broadcastOn(): array
	{
		return [$this->channel];
	}

	public function broadcastAs(): string
	{
		return 'customer-verified';
	}
}
