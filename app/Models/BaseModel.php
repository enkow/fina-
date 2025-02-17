<?php

namespace App\Models;

use App\Custom\Timezone;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
	protected function deletedAt(): Attribute
	{
		return Timezone::getClubLocalizedDatetimeAttribute();
	}

	protected function createdAt(): Attribute
	{
		return Timezone::getClubLocalizedDatetimeAttribute();
	}

	protected function updatedAt(): Attribute
	{
		return Timezone::getClubLocalizedDatetimeAttribute();
	}
}
