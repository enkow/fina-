<?php

return [
	'plural' => 'Indstillinger',
	'account-settings' => 'Kontoindstillinger',
	'time' => 'Tid',
	'general-settings' => 'Generelle indstillinger',
	'done' => 'Færdig',
	'other' => 'Andet',
	'delete-account' => 'Slet konto',
	'minutes_value' => ':value minutter',
	'permanently-delete-your-account' => 'Uigenkaldeligt slette din konto',
	'no-permissions' => 'Du har ikke tilstrækkelige tilladelser til at redigere indstillingerne.',
	'delete-account-description' =>
		'Når du sletter din konto, vil alle dine ressourcer og data blive slettet permanent. Download venligst alle data eller oplysninger, du har brug for at beholde, før du sletter din konto.',
	'delete-account-confirmation' =>
		'Er du sikker på, at du vil slette din konto? Når du sletter din konto, vil alle dens ressourcer og data blive slettet permanent. Indtast din adgangskode for at bekræfte, at du vil slette din konto permanent.',
	'browser-sessions' => 'Browser-sessioner',
	'browser-sessions-description' => 'Se og log ud af dine aktive sessioner på andre browsere og enheder.',
	'browser-sessions-content' =>
		'Du kan logge ud af alle dine andre browsersessioner på alle dine enheder, hvis det er nødvendigt. Nogle af dine seneste sessioner er anført nedenfor, men denne liste er muligvis ikke udtømmende. Hvis du mener, at din konto er blevet kompromitteret, skal du opdatere din adgangskode.',
	'logout-other-browser-sessions' => 'Log ud fra alle enheder',
	'enter-password-to-logout-other-browser-sessions' =>
		'Indtast din adgangskode for at bekræfte, at du vil logge ud fra andre browsersessioner på alle dine enheder.',
	'this-device' => 'Denne enhed',
	'last-active' => 'Sidst-aktiv',
	'two-factor-authentication' => 'To-trins verifikation',
	'two-factor-authentication-description' =>
		'Tilføj ekstra sikkerhed til din konto med to-faktor-autentificering.',
	'two-factor-authentication-enabled' => 'To-faktor-autentificering er blevet aktiveret',
	'two-factor-authentication-finish-enabling' => 'Afslut aktivering af to-faktor-autentificering.',
	'two-factor-authentication-finish-enabling-description' =>
		'Når tofaktorgodkendelse er aktiveret, vil du blive bedt om et sikkert, tilfældigt token under godkendelsen. Dette token kan hentes fra Google Authenticator-appen på din telefon.',
	'two-factor-authentication-not-enabled' => 'To-faktor-autentificering er deaktiveret.',
	'two-factor-authentication-scan-qr' =>
		'For at afslutte aktiveringen af tofaktorgodkendelse skal du scanne QR-koden nedenfor ved hjælp af godkendelsesappen på din telefon eller indtaste konfigurationsnøglen og indtaste den genererede OTP-kode.',
	'two-factor-authentication-is-now-enabled' =>
		'To-faktor-godkendelse er nu aktiveret. Scan QR-koden nedenfor med din telefons godkendelsesapp, eller indtast konfigurationsnøglen.',
	'store-these-recovery-codes' =>
		'Opbevar disse gendannelseskoder i en sikker adgangskodeadministrator. De kan bruges til at få adgang til din konto igen, hvis du mister din to-faktor autentificeringsenhed.',
	'regenerate-recovery-codes' => 'Opdater genoprettelseskoder',
	'show-recovery-codes' => 'Vis genoprettelseskoder',
	'setup-key' => 'Installationskode',
	'update-password' => 'Skift adgangskode',
	'update-password-description' =>
		'Sørg for, at din konto bruger en lang, tilfældig adgangskode for at være sikker.',
	'current-password' => 'Nuværende adgangskode',
	'new-password' => 'Ny adgangskode',
	'confirm-password' => 'Bekræft adgangskode',
	'saved' => 'Gemt',
	'your-email-is-unverified' => 'Din e-mailadresse er ikke bekræftet',
	'profile-information' => 'Oplysninger om profilen',
	'profile-information-description' => 'Opdater din kontos profiloplysninger og e-mailadresse.',
	'email-is-unverified' => 'Din e-mailadresse er ikke blevet verificeret',
	'click-to-resend-the-verification-mail' => 'Klik her for at sende bekræftelsesmailen igen.',
	'new-verification-link-sent' => 'Et bekræftelseslink blev sendt til din e-mailadresse.',
	'select-a-new-photo' => 'Vælg et nyt foto',
	'remove-photo' => 'Fjern foto',
	'club-calendar' => 'Klubbens kalender',
	'sms-notifications' => 'SMS-beskeder',
	'sms-notifications-description' =>
		'Hvis du sætter denne parameter til "on", vil afkrydsningsfeltet "Send SMS-besked" være markeret med det samme, når du tilføjer en booking i kalenderen.',
	'email-notifications' => 'Meddelelser via e-mail',
	'email-notifications-description' =>
		'Hvis du sætter denne parameter til "on", vil afkrydsningsfeltet "Send e-mail-notifikation" være markeret med det samme, når du tilføjer en reservation i kalenderen.',
	'additional-reservation-time-status' => 'Ekstra tid lægges til hver reservation',
	'additional-reservation-time-status-description' =>
		'Hvis du indstiller denne parameter til "on", vil der hver gang blive tilføjet et tidsrum på 15 minutter eller 30 minutter til den tilføjede reservation (afhængigt af hvilken "Calendar Time Slot", der er indstillet). Det er nyttigt, hvis du f.eks. har brug for tid til at rydde op før den næste kunde, efter du har afsluttet en reservation.',
	'calendar-time-scale' => 'Kalenderens tidslinje',
	'calendar-time-scale-description' =>
		'Ved at indstille denne parameter angiver du, hvad tidsskalaen på klubbens kalender skal være.',
	'refund-time-limit' => 'Tilladt tid til afbestilling af reservation betalt online',
	'refund-time-limit-description' => 'Ved at indstille denne parameter bestemmer du, hvor mange timer før reservationsstart kunden kan annullere reservationen for at få refunderet det betalte beløb.
    Eksempel 1. Du indstiller denne parameter til 2 timer.  En kunde har foretaget en reservation til kl. 15.00. Kl. 14.00 vil kunden gerne annullere reservationen. Kunden vil se en besked om, at han kan annullere reservationen, men ikke vil modtage en refusion.
    Eksempel 2 Du indstiller denne parameter til 2 timer.  Kunden har foretaget en reservation til kl. 15.00. Kl. 12.00 vil kunden gerne annullere reservationen. Kunden vil se en besked om, at han kan annullere reservationen og vil modtage en refundering.
    HUSK - Når kunden foretager en gyldig afbestilling (eksempel 2), vil du modtage en e-mail om det.  Du kan administrere annullerede reservationer i sektionen Billing.',
	'additional-commission-percent' => 'Procentvis provision for bookinger betalt online',
	'additional-commission-percent-description' =>
		'Ved at indstille denne parameter bestemmer du, hvor mange procent værdien af en ordre, der er betalt online, skal stige med.',
	'additional-commission-fixed' => 'Provisionsbeløb for bookinger betalt online',
	'additional-commission-fixed-description' =>
		'Ved at indstille denne parameter angiver du, hvor meget værdien af en ordre, der er betalt online, skal øges med.',
	'widget-message' => 'Besked om booking-plugin\'et',
	'widget-message-description' => 'Følgende tekst vil blive vist under spilvalget på booking-plugin\'et',
	'reservation-max-advance-time' => 'Maksimal tid til at foretage en reservation',
	'reservation-max-advance-time-description' =>
		'Ved at indstille denne parameter bestemmer du, hvor mange dage i forvejen online-kunder kan foretage online-reservationer.',
	'reservation-max-advance-time-info' =>
		'Hvis du f.eks. indstiller parameteren til 30 dage, vil en kunde, der foretager en reservation den 1. januar, kunne foretage en reservation i intervallet 1.-30. januar.',
	'reservation-min-advance-time' => 'Minimumstid for at foretage en reservation',
	'reservation-min-advance-description' =>
		'Ved at indstille denne parameter bestemmer du, hvor mange timer før onlinekunder kan foretage reservationer.<br>Du kan indstille parameteren for hver ugedag separat.',
	'reservation-min-advance-info' =>
		'Hvis du for eksempel indstiller parameteren til 2 timer for mandag. Så vil en kunde, der gerne vil reservere mandag kl. 13.00, tidligst kunne reservere til kl. 15.00.',
	'full-hour-start-reservations-status' => 'Accepterer kun online-reservationer i hele åbningstiden',
	'full-hour-start-reservations-status-description' => 'Hvis du indstiller denne parameter, kan onlinekunder kun foretage reservationer i hele timer (f.eks. 15:00).<br>
Og kunderne vil kun kunne vælge hele timer af spilletid.',
	'reservation-number-on-calendar-status' => 'Vis bookingnummer i kalenderen',
	'reservation-number-on-calendar-status-description' =>
		'Hvis du aktiverer denne parameter, vises det individuelle reservationsnummer i kalenderen ved siden af hver reservation.',
	'reservation-notes-on-calendar-status' => 'Kommentarer til kalenderen',
	'reservation-notes-on-calendar-status-description' =>
		'Hvis du aktiverer dette parameter, kan tildelte klub- og kundekommentarer vises i kalenderen for hver booking.',
	'club-terms' => 'Fil med vilkår og betingelser',
	'club-terms-description' =>
		'Ved at tilføje dine vilkår og betingelser her vil du få onlinekunder, der foretager onlinebookinger, til at acceptere dine vilkår og betingelser.',
	'club-terms-no-file-alert' => 'Tjenesten har fundet en manglende fil med vilkår og betingelser.',
	'drop-terms-here' => 'Drop filen, eller klik her for at tilføje vilkår og betingelser',
	'reservation-types-description' =>
		'Dette er meget nyttigt, hvis du i visningen "Reservationer" hurtigt vil se reservationer af en bestemt type, f.eks. "Firmaarrangementer".',
	'reservation-types-bolder' => 'I dette afsnit kan du oprette en reservationstype og tildele den en farve.
    Når du opretter en reservation i kalenderen, kan du angive, hvilken type reservation der er tale om.
    I kalendervisningen vil reservationen blive markeret med den valgte farve.',
	'club-map' => 'Kort over klubben',
	'club-map-description' => 'Tilføj et klubkort til onlinekunder',
	'club-map-no-file-alert' => 'Tjenesten opdagede en manglende klubkortfil',
	'drop-map-here' => 'Smid en fil eller klik her for at tilføje et kort over klubben (png, jpg, gif)',
	'manager-mails' => 'Meddelelser via e-mail',
	'manager-mails-description' =>
		'Ved at indtaste din e-mailadresse her, vil du sørge for, at hver gang en onlinekunde foretager en reservation eller annullerer den - vil du modtage en ordentlig e-mailnotifikation om det.',
	'your-mail-notification-addresses' => 'Dine e-mailadresser til notifikationer',
	'add-new-email-address' => 'Indtast e-mailadresse',
	'offline-reservation-duration-limit' => 'Maksimal offline reservationsvarighed',
	'offline-reservation-duration-limit-description' =>
		'Ved at indstille denne parameter bestemmer du, hvor mange timer en offline-kunde maksimalt kan booke.',
	'offline-reservation-slot-limit' => 'Maksimalt antal offline-reservationsslots',
	'offline-reservation-slot-limit-description' =>
		'Ved at indstille denne parameter bestemmer du, hvor mange slots en offline-kunde maksimalt kan booke.',
	'offline-reservation-daily-limit' => 'Maksimalt antal offline-reservationer pr. dag',
	'offline-reservation-daily-limit-description' =>
		'Ved at indstille denne parameter bestemmer du, hvor mange reservationer en offline-kunde kan foretage om dagen.',
	'successfully-updated' => 'Ændringer af indstillinger er blevet gemt',
	'calendar_time_scale-full_hour_start_reservations_status-combination-error' =>
		'Værdien "60 minutter" er ikke tilladt, hvis indstillingen "Accepter kun online reservationer i hele åbningstiden" er deaktiveret.',
	'full_hour_start_reservations_status-calendar_time_scale-combination-error' =>
		'Deaktivering af indstillingen \\"Acceptér kun online reservationer ved fuld åbningstid\\" er ikke tilladt, hvis indstillingen \\"Kalendertidsskala\\" er indstillet til \\"60 minutter\\".',
];
