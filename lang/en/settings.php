<?php
return [
	'plural' => 'Settings',
	'account-settings' => 'Account settings',
	'time' => 'Time',
	'general-settings' => 'General settings',
	'done' => 'Done',
	'other' => 'Other',
	'delete-account' => 'Delete account',
	'minutes_value' => ':value minutes',
	'permanently-delete-your-account' => 'Irreversibly delete your account',
	'no-permissions' => 'You do not have sufficient permissions to edit the settings.',
	'delete-account-description' =>
		'When you delete your account, all your account resources and data will be permanently deleted. Please download any data or information you need to keep before deleting your account.',
	'delete-account-confirmation' =>
		'Are you sure you want to delete your account? When you delete your account, all of its resources and data will be permanently deleted. Enter your password to confirm that you want to permanently delete your account.',

	'browser-sessions' => 'Browser sessions',
	'browser-sessions-description' => 'View and log out your active sessions on other browsers and devices.',
	'browser-sessions-content' =>
		'You can log out of all your other browser sessions on all your devices, if necessary. Some of your recent sessions are listed below, but this list may not be exhaustive. If you believe your account has been compromised, you should also update your password.',
	'logout-other-browser-sessions' => 'Logout from all devices',
	'enter-password-to-logout-other-browser-sessions' =>
		'Enter your password to confirm that you want to logout from other browser sessions on all your devices.',
	'this-device' => 'This-device',
	'last-active' => 'Last-active',

	'two-factor-authentication' => 'Two-step verification',
	'two-factor-authentication-description' =>
		'Add additional security to your account with two-factor authentication.',
	'two-factor-authentication-enabled' => 'Two-factor authentication has been enabled',
	'two-factor-authentication-finish-enabling' => 'Finish activating two-factor authentication.',
	'two-factor-authentication-finish-enabling-description' =>
		'When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication. This token can be retrieved from the Google Authenticator app on your phone.',
	'two-factor-authentication-not-enabled' => 'Two-factor authentication is disabled.',
	'two-factor-authentication-scan-qr' =>
		'To finish enabling two-factor authentication, scan the QR code below using the authentication app on your phone, or enter the configuration key and enter the generated OTP code.',
	'two-factor-authentication-is-now-enabled' =>
		"Two-factor authentication is now enabled. Scan the QR code below with your phone's authentication app or enter the configuration key.",
	'store-these-recovery-codes' =>
		'Store these recovery codes in a secure password manager. They can be used to regain access to your account if you lose your two-factor authentication device.',
	'regenerate-recovery-codes' => 'Refresh recovery codes',
	'show-recovery-codes' => 'show recovery codes',
	'setup-key' => 'Installation code',

	'update-password' => 'Change password',
	'update-password-description' => 'Make sure your account uses a long, random password to stay safe',
	'current-password' => 'Current password',
	'new-password' => 'New password',
	'confirm-password' => 'Confirm password',
	'saved' => 'Saved',

	'your-email-is-unverified' => 'Your email address is unverified',

	'profile-information' => 'Profile information',
	'profile-information-description' => 'Update your account\'s profile information and email address.',
	'email-is-unverified' => 'Your email address has not been verified',
	'click-to-resend-the-verification-mail' => 'Click here to resend the verification email.',
	'new-verification-link-sent' => 'A verification link was sent to your email address.',
	'select-a-new-photo' => 'Select a new photo',
	'remove-photo' => 'Remove photo',

	'club-calendar' => 'Club calendar',

	'sms-notifications' => 'SMS notifications',
	'sms-notifications-description' =>
		'If you set this parameter to "on" then when you add a booking on the calendar, the checkbox "Send SMS notification" will be checked right away',

	'email-notifications' => 'Email notifications',
	'email-notifications-description' =>
		'If you set this param to "on" then when adding a reservation on the calendar, the checkbox "Send email notification" will be checked right away.',

	'additional-reservation-time-status' => 'Additional time added to each reservation',
	'additional-reservation-time-status-description' =>
		'If you set this paramets to "on" then each time a time slot of 15min or 30 minutes will be added to the added reservation (depending on what "Calendar Time Slot" will be set). This is useful when, for example, after finishing a reservation you need time to clean up before the next customer.',

	'calendar-time-scale' => 'Calendar timeline',
	'calendar-time-scale-description' =>
		'By setting this parameter you specify what the time scale on the club\'s calendar will be.',

	'refund-time-limit' => 'Allowed time for cancellation of reservation paid online',
	'refund-time-limit-description' => 'By setting this parameter you will determine how many hours before the start of the reservation the customer can cancel the reservation to receive a refund of the amount paid.
    Example1. You set this parameter to 2h.  A customer has made a reservation for 3:00 p.m. At 2:00 p.m. the customer would like to cancel the reservation. The customer will see a message that he can cancel the reservation but will not receive a refund.
    Example2 You set this parameter to 2h.  The customer made a reservation for 3:00 p.m. At 12:00 p.m. the customer would like to cancel the reservation. The customer will see a message that he can cancel the reservation and will receive a refund.
    REMEMBER - When the customer makes a valid cancellation ( Example2) you will receive an email notification about it.  You can manage cancelled reservations in the Billing section',

	'additional-commission-percent' => 'Percentage commission for bookings paid online',
	'additional-commission-percent-description' =>
		'By setting this parameter, you will determine by how much percentage the value of an order paid online should increase.',

	'additional-commission-fixed' => 'Amount commission for bookings paid online',
	'additional-commission-fixed-description' =>
		'By setting this parameter you will specify by what amount the value of an order paid online is to be increased.',

	'widget-message' => 'Message on the booking plugin',
	'widget-message-description' =>
		'The following text will be displayed under the game selection on the booking plugin',

	'reservation-max-advance-time' => 'Maximum time to make a reservation',
	'reservation-max-advance-time-description' =>
		'By setting this paramets you will determine how many days ahead online customers will be able to make online reservations.',
	'reservation-max-advance-time-info' =>
		'For example, if you set the parameter to 30 days then a customer who will be making a reservation on January 1 - will be able to make a reservation in the range of January 1-30.',

	'reservation-min-advance-time' => 'Minimum time to make a reservation',
	'reservation-min-advance-description' =>
		'By setting this parameter you will determine how many hours before online customers can make reservations.<br>You can set the parameter for each day of the week separately.',
	'reservation-min-advance-info' =>
		'If, for example, for Monday you set the parameter to 2h. Then a customer who would like to make a reservation on Monday at 13:00 will be able to book for 15:00 at the earliest.',

	'full-hour-start-reservations-status' => 'Accept online reservations only at full hours',
	'full-hour-start-reservations-status-description' => 'Setting this parameter will allow online customers to make reservations only at full hours (e.g., 3:00 pm )<br>
And customers will only be able to select full hours of game time.',

	'reservation-number-on-calendar-status' => 'Show booking number on calendar',
	'reservation-number-on-calendar-status-description' =>
		'By enabling this parameter, its individual reservation number will appear on the calendar next to each reservation.',

	'reservation-notes-on-calendar-status' => 'Comments on the calendar',
	'reservation-notes-on-calendar-status-description' =>
		'By enabling this parameter, assigned club and customer comments will be allowed to appear on the calendar for each booking.',

	'club-terms' => 'Terms and conditions file',
	'club-terms-description' =>
		'By adding your terms and conditions here you will make online customers making online bookings have to accept your terms and conditions',
	'club-terms-no-file-alert' => 'The service has detected a missing terms and conditions file',
	'drop-terms-here' => 'Drop the file or click here to add terms and conditions',

	'reservation-types-description' =>
		'This is very useful if on the "Reservations" view you want to quickly see reservations of a specific type, e.g. "Corporate Events".',
	'reservation-types-bolder' => 'In this section you can create a reservation type and assign a color to it.
    When you create a reservation on the calendar, you will be able to specify the type of reservation.
    On the calendar view, the reservation will be marked with the selected color.',

	'club-map' => 'Club map',
	'club-map-description' => 'Add a club map for online customers',
	'club-map-no-file-alert' => 'The service detected a missing club map file',
	'drop-map-here' => 'Drop a file or click here to add a map of the club (png, jpg, gif)',

	'manager-mails' => 'Email notifications',
	'manager-mails-description' =>
		'By entering your email address here, you will cause that every time an online customer makes a reservation or cancels it - you will receive a proper email notification about it.',
	'your-mail-notification-addresses' => 'Your email addresses for notifications',
	'add-new-email-address' => 'Enter email address',

	'offline-reservation-duration-limit' => 'Maximum offline reservation duration',
	'offline-reservation-duration-limit-description' =>
		'By setting this parameter - you will determine how many maximum hours an offline customer will be able to book.',

	'offline-reservation-slot-limit' => 'Maximum number of offline reservation slots',
	'offline-reservation-slot-limit-description' =>
		'By setting this parameter - you will determine how many maximum slots an offline customer will be able to book.',

	'offline-reservation-daily-limit' => 'Maximum number of offline reservations per day',
	'offline-reservation-daily-limit-description' =>
		'By setting this parameter - you will determine how many reservations per day an offline customer will be able to make.',

	'successfully-updated' => 'Changes to settings have been saved',

	'calendar_time_scale-full_hour_start_reservations_status-combination-error' =>
		'The value "60 minutes" is not allowed if the setting "Accept online reservations only at full hours" is disabled.',
	'full_hour_start_reservations_status-calendar_time_scale-combination-error' =>
		'Disabling the setting of \"Accept online reservations only at full hours\" is not allowed if the setting of \"Calendar time scale\" is set to \"60 minutes\".',
];
