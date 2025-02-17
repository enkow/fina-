<?php

namespace App\Events;

use App\Models\Club;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BulbStatusChange implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	private string $channel;

	public function __construct(Club $club)
	{
		$this->channel = 'calendar' . $club->id;
	}

	public function broadcastOn(): string
	{
		return $this->channel;
	}

	public function broadcastAs(): string
	{
		return 'bulb-status-change';
	}
}
