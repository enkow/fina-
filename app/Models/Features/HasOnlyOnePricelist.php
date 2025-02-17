<?php

namespace App\Models\Features;

use App\Models\Feature;
use Parental\HasParent;

class HasOnlyOnePricelist extends Feature
{
	use HasParent;

	public static string $name = 'has_only_one_pricelist';

	public array $conflictedFeatures = [HasOnlyOnePricelist::class];
}
