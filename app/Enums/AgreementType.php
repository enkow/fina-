<?php

namespace App\Enums;

enum AgreementType: int
{
	case GeneralTerms = 0;
	case PrivacyPolicy = 1;
	case MarketingAgreement = 2;
}
