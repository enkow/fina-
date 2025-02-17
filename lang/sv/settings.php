<?php
return [
	'plural' => 'Inställningar',
	'account-settings' => 'Kontoinställningar',
	'time' => 'Tid',
	'general-settings' => 'Allmänna Inställningar',
	'done' => 'Gjort',
	'other' => 'Annat',
	'delete-account' => 'Ta bort konto',
	'minutes_value' => ':value minuter',
	'permanent-delete-your-account' => 'Ta bort ditt konto oåterkalleligt',
	'no-permissions' => 'Du har inte tillräckliga rättigheter för att redigera inställningarna.',
	'delete-account-description' =>
		'När du tar bort ditt konto kommer alla dina kontoresurser och data att raderas permanent. Ladda ner all data eller information som du behöver behålla innan du raderar ditt konto.',
	'delete-account-confirmation' =>
		'Är du säker på att du vill ta bort ditt konto? När du raderar ditt konto kommer alla dina tillgångar och data att raderas permanent. Ange ditt lösenord för att bekräfta att du vill radera ditt konto permanent.',

	'browser-sessions' => 'aršyklės sesijos',
	'browser-sessions-description' =>
		'Visa och logga ut dina aktiva sessioner i andra webbläsare och enheter.',
	'browser-sessions-content' =>
		'Du kan logga ut från alla dina andra webbläsarsessioner på alla dina enheter om det behövs. Några av dina senaste sessioner listas nedan, men listan kanske inte är fullständig. Om du tror att ditt konto har äventyrats bör du också uppdatera ditt lösenord.',
	'logout-other-browser-sessions' => 'Logga ut från alla enheter',
	'enter-password-to-logout-other-browser-sessions' =>
		'Ange ditt lösenord för att bekräfta att du vill logga ut från andra webbläsarsessioner på alla dina enheter.',
	'this-device' => 'Den här enheten',
	'last-active' => 'Senast aktiv',

	'two-factor-authentication' => 'Tvåstegsverifiering',
	'two-factor-authentication-description' =>
		'Lägg till ytterligare säkerhet för ditt konto med tvåfaktorsautentisering.',
	'two-factor-authentication-enabled' => 'Tvåfaktorsautentisering har aktiverats.',
	'two-factor-authentication-finish-enabling' => 'Avsluta aktiveringen av tvåfaktorsautentisering.',
	'two-factor-authentication-finish-enabling-description' =>
		'När tvåfaktorsautentisering är aktiverad kommer du att bli ombedd att ange en säker, slumpmässig token under autentiseringen. Denna token kan hämtas från Google Authenticator-appen på din telefon.',
	'two-factor-authentication-not-enabled' => 'Tvåfaktorsautentisering är inaktiverad.',
	'two-factor-authentication-scan-qr' =>
		'För att slutföra aktiveringen av tvåfaktorsautentisering, skanna QR-koden nedan med autentiseringsappen på din telefon, eller ange konfigurationsnyckeln och ange den genererade OTP-koden',
	'two-factor-authentication-is-now-enabled' =>
		"Tvåfaktorsautentisering är nu aktiverad. Skanna QR-koden nedan med telefonens autentiseringsapp eller ange din konfigurationsnyckel.'",
	'store-these-recovery-codes' =>
		'Spara dessa återställningskoder i en säker lösenordshanterare. De kan användas för att återfå åtkomst till ditt konto om du tappar bort din enhet för tvåfaktorsautentisering.',
	'regenerate-recovery-codes' => 'Uppdatera återställningskoder',
	'show-recovery-codes' => 'Visa återställningskoder',
	'setup-key' => 'Installationskod',

	'update-password' => 'Ändra lösenord',
	'update-password-description' =>
		'Se till att ditt konto använder ett långt, slumpmässigt lösenord för att vara säkert.',
	'current-password' => 'Aktuellt lösenord',
	'new-password' => 'Nytt lösenord',
	'confirm-password' => 'Bekräfta lösenord',
	'saved' => 'Sparat',

	'your-email-is-unverified' => 'Din e-postadress är inte verifierad.',

	'profile-information' => 'Profilinformation',
	'profile-information-description' => 'Uppdatera din profilinformation och e-postadress.',
	'email-is-unverified' => 'Din e-postadress har inte verifierats.',
	'click-to-resend-the-verification-mail' => 'Klicka här för att skicka verifieringsmailet igen.',
	'new-verification-link-sent' => 'En verifieringslänk har skickats till din e-postadress.',
	'select-a-new-photo' => 'Välj ett nytt foto',
	'remove-photo' => 'Ta bort foto',

	'club-kalender' => 'Klubbkalender',

	'sms-notifications' => 'SMS-aviseringar',
	'sms-notifications-description' =>
		'Om du sätter denna parameter till "on" när du lägger till en bokning i kalendern, kommer kryssrutan "Skicka SMS-notifiering" att bockas av omedelbart',

	'email-notifications' => 'E-postaviseringar',
	'email-notifications-description' =>
		'Om du anger denna parameter till "på" när du lägger till en bokning i kalendern, kommer kryssrutan "Skicka e-postavisering" att markeras direkt.',

	'additional-reservation-time-status' => 'Ytterligare tid läggs till varje bokning',
	'additional-reservation-time-status-description' =>
		'Om du anger denna parameter till "on" kommer en tidslucka på 15 minuter eller 30 minuter att läggas till den extra bokningen varje gång (beroende på vilken "Kalendertidslucka" som är inställd). Detta är användbart när du till exempel behöver tid att städa upp efter att en bokning är klar innan nästa kund.',

	'calendar-time-scale' => 'Kalenderns tidsskala',
	'calendar-time-scale-description' =>
		'Genom att ställa in denna parameter anger du vilken tidsskala som ska visas i klubbens kalender.',

	'refund-time-limit' => 'Tidsfrist för avbokning av bokningar som betalats online',
	'refund-time-limit-description' => 'Genom att ställa in denna parameter anger du hur många timmar före bokningens början kunden kan avboka bokningen för att få en återbetalning av det betalda beloppet.
    Exempel1. Du ställer in denna parameter till 2h. En kund har gjort en bokning till kl. 15.00. Kl. 14.00 vill kunden avboka bokningen. Kunden kommer att se ett meddelande om att de kan avbryta bokningen men inte kommer att få någon återbetalning.
    Exempel2 Du ställer in denna parameter på 2h. Kunden har gjort en bokning till 15:00. Klockan 12:00 vill kunden avboka bokningen. Kunden kommer att se ett meddelande om att de kan avboka bokningen och kommer att få en återbetalning.
    KOM IHÅG - När kunden gör en giltig avbokning (exempel 2) får du ett e-postmeddelande om detta. Du kan hantera avbokade bokningar i avsnittet Fakturering.',

	'additional-commission-percent' => 'Procentuell provision för bokningar som betalas online',
	'additional-commission-percent-description' =>
		'Genom att ställa in denna parameter anger du hur mycket värdet på en order som betalats online ska ökas med.',

	'additional-commission-fixed' => 'Kvotprovision för bokningar som betalas online',
	'additional-commission-fixed-description' =>
		'Genom att ställa in denna parameter anger du det belopp med vilket värdet på en order som betalats online ska ökas.',

	'widget-message' => 'Meddelande på bokningspluginet',
	'widget-message-description' =>
		'Följande text kommer att visas under urvalet av spel på bokningspluginet',

	'reservation-max-advance-time' => 'Maximal tid för att göra en bokning',
	'reservation-max-advance-time-description' =>
		'Genom att ställa in denna parameter anger du hur många dagar i förväg dina onlinekunder kommer att kunna göra bokningar online.',
	'reservation-max-advance-time-info' =>
		'Om du till exempel ställer in parametern på 30 dagar kommer en kund som gör en bokning den 1 januari - att kunna göra en bokning inom intervallen 1-30 januari',

	'reservation-min-advance-time' => 'Minimala tiden för att göra en bokning',
	'reservation-min-advance-description' =>
		'Genom att ställa in den här parametern anger du hur många timmar i förväg onlinekunder kan göra bokningar.<br>Du kan ställa in parametern för varje veckodag separat.',
	'reservation-min-advance-info' =>
		'Om du till exempel för måndag ställer in parametern till 2 tim. så kommer en kund som vill göra en bokning kl. 13.00 på måndagen att kunna boka tidigast kl. 15.00.',

	'full-hour-start-reservations-status' => 'Acceptera endast onlinebokningar vid hela timmar',
	'full-hour-start-reservations-status-description' => 'Denna inställning gör det möjligt för kunder att göra onlinebokningar endast på hela timmar (t.ex. 15:00).<br>
    Kunderna kommer endast att kunna välja hela speltimmar.',

	'reservation-number-on-calendar-status' => 'Visa bokningsnummer i kalendern',
	'reservation-number-on-calendar-status-description' =>
		'Om du aktiverar denna parameter kommer det individuella bokningsnumret att visas i kalendern bredvid varje bokning.',

	'reservation-notes-on-calendar-status' => 'Kommentarer till kalendern',
	'reservation-notes-on-calendar-status-description' =>
		'Genom att aktivera denna parameter kan tilldelade klubb- och kundkommentarer visas i kalendern för varje bokning.',

	'club-terms' => 'Fil med villkor och bestämmelser',
	'club-terms-description' =>
		'Genom att lägga till dina villkor här kommer du att göra onlinekunder som gör en onlinebokning måste acceptera dina villkor',
	'club-terms-no-file-alert' => 'Tjänsten har upptäckt att en villkorsfil saknas',
	'drop-terms-here' => 'Lägg till en fil eller klicka här för att lägga till regler och villkor',

	'reservation-types-description' =>
		'Detta är mycket användbart om du i vyn "Bokningar" snabbt vill se bokningar av en viss typ, t.ex. "Företagsevenemang"',
	'reservation-types-bolder' => 'I den här sektionen kan du skapa en bokningstyp och tilldela den en färg.
    När du skapar en bokning i kalendern kan du ange vilken typ av bokning det gäller.
    I kalendervyn kommer bokningen att markeras med den valda färgen.',

	'club-map' => 'Karta över klubben',
	'club-map-description' =>
		'Lägg till en karta över klubben, som kommer att vara synlig för kunder som gör onlinebokningar',
	'club-map-no-file-alert' => 'Tjänsten har upptäckt att filen för klubbkartan saknas.',
	'drop-map-here' =>
		'Dra över en fil eller klicka här för att lägga till en karta över klubben (png, jpg, gif)',

	'manager-mails' => 'E-post meddelanden',
	'manager-mails-description' =>
		'Genom att ange din e-postadress här säkerställer du att varje gång en onlinekund gör en bokning eller avbokar den - kommer du att få ett korrekt e-postmeddelande',
	'your-mail-notification-addresses' => 'Dina e-postadresser för aviseringar',
	'add-new-email-address' => 'Lägg till en ny e-postadress',

	'offline-reservation-duration-limit' => 'Maximal offline-reservationstid',
	'offline-reservation-duration-limit-description' =>
		'Genom att ställa in denna parameter anger du hur många timmar en offlinekund maximalt ska kunna boka.',

	'offline-reservation-slot-limit' => 'Maximalt antal platser för offline-bokning',
	'offline-reservation-slot-limit-description' =>
		'Genom att ställa in denna parameter - kommer du att ange hur många maximala slots en offline-kund kommer att kunna boka.',

	'offline-reservation-daily-limit' => 'Maximalt antal offline-reservationer per dag',
	'offline-reservation-daily-limit-description' =>
		'Genom att ställa in denna parameter anger du hur många dagliga bokningar en offlinekund ska kunna göra.',

	'successfully-updated' => 'Inställningsändringarna har sparats',

	'calendar_time_scale-full_hour_start_reservations_status-combination-error' =>
		'Värdet "60 minuter" är inte tillåtet om inställningen "Acceptera onlinebokningar endast vid fulla timmar" är avaktiverad.',
	'full_hour_start_reservations_status-calendar_time_scale-combination-error' =>
		'Inaktivering av inställningen \'Acceptera onlinebokningar endast vid fulla timmar\' är inte tillåtet om inställningen \'Kalendertidsskala\' är inställd på 60 minuter.',
];
