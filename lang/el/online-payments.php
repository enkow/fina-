<?php

return [
	'help-section' =>
		'Σε αυτή την ενότητα μπορείτε να ρυθμίσετε τη σύνδεση με τον μεσίτη ηλεκτρονικών πληρωμών.',
	'invalid-method-type' => 'Μη έγκυρος τύπος μεθόδου πληρωμής',
	'already-connected' => 'Αυτή η μέθοδος πληρωμής είναι ήδη ενεργή',
	'general-error' => 'Εμφανίστηκε σφάλμα κατά τη δημιουργία μιας μεθόδου πληρωμής',
	'invoice-payment' => 'Πληρωμή για το τιμολόγιο #:id στο Bookgame.io',
	'reservation-payment' => 'Κράτηση :reservation_number σε :club_name',
	'payment-time-limit-notice' => 'Θυμηθείτε ότι έχετε 5 λεπτά για να πληρώσετε την κράτησή σας.',
	'stripe' => [
		'continue-connection' => 'Συνεχίστε να συνδέεστε με',
		'active' => [
			'title' => 'Οι πληρωμές Stripe είναι ενεργές',
			'desc' =>
				'Οι πελάτες σας μπορούν τώρα να πραγματοποιούν online πληρωμές χρησιμοποιώντας το Stripe',
		],
		'connecting' => [
			'title' => 'Η διαδικασία σύνδεσης με το Stripe έχει ξεκινήσει',
			'desc' =>
				'Αν δεν καταφέρατε να συνδεθείτε με το Stripe, πατήστε το παρακάτω κουμπί για να προσπαθήσετε ξανά.',
		],
		'not-connected' => [
			'title' => 'Αποδοχή online πληρωμών με το Stripe',
			'desc' =>
				'Η Stripe είναι ο παγκόσμιος ηγέτης στις online πληρωμές. Συνδέστε το λογαριασμό σας για να αρχίσετε να δέχεστε online πληρωμές.',
		],
		'login' => 'Σύνδεση για να',
		'disconnect' => 'Αποσύνδεση λογαριασμού',
		'connect' => 'Συνδέστε το λογαριασμό σας',
		'no-connection-yet' =>
			'Δεν έχουμε λάβει ακόμη επιβεβαίωση επιτυχούς σύνδεσης από την Stripe. Εάν ολοκληρώσατε επιτυχώς τη διαδικασία σύνδεσης, η μέθοδος θα ενεργοποιηθεί αυτόματα σε λίγα λεπτά.',
		'connected' => 'Επιτυχής σύνδεση με το Stripe',
	],
];
