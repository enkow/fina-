<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GameSeeder extends Seeder
{
	private Collection $activeCountries;
	public function setGameTranslations(string $gameName, array $translations): void
	{
		foreach ($this->activeCountries as $country) {
			foreach ($translations as $key => $value) {
				Game::where('name', $gameName)
					->first()
					->translations()
					->create([
						'country_id' => $country->id,
						'key' => $key,
						'value' => $value,
					]);
			}
		}
	}
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function run(): void
	{
		$this->activeCountries = Country::where('active', true)->get();
		Game::insert([
			[
				'name' => 'billiard',
				'icon' =>
					'<svg width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg"> <circle cx="13.5" cy="13.5" r="13" stroke="currentColor"/> <path d="M11.7094 10.793C10.6995 10.5159 9.65619 11.1307 9.38493 12.1601C9.22631 12.7633 9.37312 13.3734 9.72032 13.834C8.97108 14.06 8.35051 14.6627 8.13367 15.4859C7.80335 16.7443 8.53951 18.0422 9.77474 18.379C9.97513 18.4336 10.1764 18.4598 10.3746 18.4598C11.3989 18.4598 12.3384 17.7625 12.616 16.7078C12.8392 15.858 12.5751 14.9914 12.0001 14.414C12.4992 14.1828 12.8991 13.7403 13.0518 13.1603C13.1834 12.6615 13.1155 12.1404 12.862 11.6931C12.6089 11.2463 12.1992 10.9262 11.7094 10.793ZM11.8014 16.4857C11.5905 17.2869 10.7784 17.7638 9.99327 17.5494C9.20733 17.3346 8.73863 16.5089 8.94872 15.7084C9.12548 15.0374 9.72369 14.594 10.3759 14.594C10.502 14.594 10.6303 14.6107 10.7568 14.6451C11.5432 14.8599 12.0119 15.6857 11.8014 16.4857ZM12.2372 12.9382C12.087 13.51 11.5061 13.8499 10.945 13.6973C10.3835 13.544 10.0494 12.9541 10.1996 12.3827C10.3261 11.9032 10.7535 11.5861 11.2188 11.5861C11.3087 11.5861 11.4002 11.5982 11.4909 11.6227C11.7634 11.697 11.9908 11.8744 12.1313 12.1232C12.2726 12.3719 12.3102 12.6611 12.2372 12.9382Z" fill="currentColor"/> <path d="M13.3416 7.75568C11.5221 7.25387 9.62033 7.50735 7.9839 8.46973C6.32638 9.44413 5.14177 11.0252 4.64861 12.9203C3.63655 16.808 5.88721 20.8195 9.66547 21.8622C10.2759 22.0306 10.8956 22.114 11.5116 22.114C12.7316 22.114 13.9365 21.7874 15.0236 21.1477C16.6811 20.1729 17.8653 18.5927 18.3585 16.6976C19.371 12.8094 17.1203 8.79797 13.3416 7.75568ZM17.543 16.4776C17.1081 18.1502 16.0635 19.5443 14.6017 20.404C13.1615 21.2508 11.4871 21.4747 9.8861 21.033C6.55419 20.1136 4.57056 16.573 5.46408 13.1402C5.89903 11.4677 6.94357 10.0731 8.40535 9.21342C9.36257 8.6506 10.4227 8.36318 11.496 8.36318C12.0381 8.36318 12.5835 8.43622 13.1206 8.58487C16.4529 9.50385 18.4365 13.0449 17.543 16.4776Z" fill="currentColor"/> <path d="M23.0559 10.9418C22.0316 8.31757 20.5736 5.74151 16.8725 4.67259C16.6498 4.60814 16.4156 4.74004 16.3519 4.9686C16.2887 5.19673 16.4186 5.43432 16.6426 5.4992C19.8054 6.41302 21.1802 8.46279 22.2716 11.2588C22.3374 11.4272 22.496 11.5299 22.6639 11.5299C22.7158 11.5299 22.7686 11.52 22.8196 11.4994C23.036 11.4122 23.1415 11.1626 23.0559 10.9418Z" fill="currentColor"/> </svg>',
				'setting_icon_color' => 'gray',
			],
			[
				'name' => 'bowling',
				'icon' => '<svg width="29" height="29" viewBox="0 0 29 29" fill="#fff" xmlns="http://www.w3.org/2000/svg">
    <path d="M19.2722 10.4237C18.4326 10.4237 17.6237 10.5483 16.8552 10.7676C16.7243 10.3714 16.5881 9.97702 16.4465 9.5845C16.0582 8.49383 15.751 7.63198 15.7954 6.81772C15.8081 6.57167 15.9313 6.26808 16.0727 5.91691C16.2993 5.35639 16.5807 4.65858 16.5843 3.77498C16.5884 2.63719 16.1497 1.95705 15.7818 1.58684C15.3445 1.14777 14.7722 0.90625 14.1691 0.90625C13.1994 0.90625 12.1708 1.55648 11.879 2.95936C11.7032 2.35353 11.406 1.9362 11.1395 1.66795C10.6542 1.18039 10.0185 0.912594 9.34878 0.912594C8.34918 0.912594 7.2925 1.53881 6.8865 2.88006C6.73606 2.26381 6.44651 1.84694 6.18778 1.58684C5.75051 1.14777 5.17776 0.90625 4.57465 0.90625C3.43097 0.90625 2.20028 1.80706 2.20028 3.78631C2.20028 4.68259 2.52472 5.37497 2.78481 5.93186C2.94522 6.27352 3.08297 6.56895 3.09565 6.80367C3.1387 7.61023 2.81472 8.46573 2.40464 9.54916C2.01676 10.5741 1.57633 11.7355 1.28768 13.2376C0.761607 15.9672 0.518279 19.5025 2.34347 20.8415C2.83103 21.1999 3.58367 21.397 4.46228 21.397C4.78309 21.397 5.14695 21.3653 5.51897 21.2996C5.76139 22.2045 6.17962 22.9648 6.84934 23.4569C7.39309 23.8561 8.23545 24.0759 9.221 24.0759C9.93286 24.0759 10.8323 23.9504 11.6189 23.6409C13.1396 26.2985 15.9934 28.0938 19.2722 28.0938C24.1442 28.0938 28.0937 24.138 28.0937 19.2583C28.0937 14.379 24.1442 10.4237 19.2722 10.4237ZM14.1691 1.79891C14.5339 1.79891 14.8823 1.94708 15.1506 2.21714C15.5077 2.57602 15.6952 3.11388 15.693 3.77181C15.6907 4.48367 15.4546 5.0673 15.2467 5.5825C15.1755 5.75967 15.1071 5.93186 15.0495 6.10269H13.426C13.3528 5.9167 13.273 5.73337 13.1867 5.55305C12.9402 5.02742 12.686 4.48413 12.686 3.78677C12.686 2.41334 13.431 1.79891 14.1691 1.79891ZM15.1044 8.34702H13.3612C13.4727 7.95597 13.5502 7.57534 13.5751 7.19698H14.9082C14.929 7.57489 15.0002 7.95597 15.1044 8.34702ZM11.9629 4.89466C12.0802 5.28661 12.2379 5.63053 12.3788 5.93186C12.5392 6.27352 12.677 6.56895 12.6897 6.80367C12.7327 7.61023 12.4087 8.46573 11.9987 9.54916C11.9085 9.78705 11.8156 10.0354 11.7232 10.2914C11.357 9.25009 11.0915 8.39006 11.1364 7.56719C11.1527 7.27628 11.2936 6.92737 11.4563 6.52319C11.6402 6.0678 11.855 5.52994 11.9629 4.89466ZM9.34833 1.8057C9.77925 1.8057 10.1907 1.98061 10.5074 2.2987C10.9247 2.71875 11.1441 3.34452 11.1418 4.10848C11.1386 4.92955 10.8681 5.59836 10.6302 6.18833C10.5523 6.38181 10.4789 6.56986 10.4145 6.75609H8.438C8.35802 6.55358 8.27097 6.35393 8.177 6.15752C7.90784 5.58205 7.60334 4.93045 7.60334 4.1248C7.60334 2.52255 8.48014 1.8057 9.34833 1.8057ZM10.4752 9.30719H8.36731C8.49509 8.86267 8.58436 8.42994 8.6129 8.00083H10.2505C10.2736 8.42948 10.3556 8.86177 10.4752 9.30719ZM6.20137 6.81772C6.21451 6.57167 6.33731 6.26808 6.47914 5.91691C6.58879 5.64594 6.71068 5.34144 6.80856 5.00069C6.93589 5.59972 7.16879 6.10541 7.37044 6.53633C7.5544 6.931 7.7139 7.2713 7.72886 7.55042C7.77326 8.37284 7.48779 9.23469 7.09675 10.2796C7.01473 10.0403 6.93272 9.80834 6.85297 9.5845C6.46373 8.49383 6.15697 7.63244 6.20137 6.81772ZM3.09158 3.78631C3.09158 2.41289 3.83651 1.79891 4.5742 1.79891C4.93897 1.79891 5.28742 1.94708 5.55567 2.21714C5.91318 2.57602 6.10078 3.11388 6.09851 3.77181C6.09579 4.48367 5.86017 5.0673 5.65173 5.5825C5.58059 5.75967 5.51262 5.93186 5.45508 6.10269H3.83198C3.75877 5.9167 3.67897 5.73337 3.59273 5.55305C3.34578 5.02742 3.09158 4.48367 3.09158 3.78631ZM5.51036 8.34702H3.76673C3.8782 7.95597 3.95568 7.57534 3.98061 7.19698H5.31325C5.33454 7.57489 5.40614 7.95597 5.51036 8.34702ZM5.34089 20.4196C5.05587 20.4681 4.7627 20.5044 4.46228 20.5044C3.78214 20.5044 3.20168 20.3643 2.87045 20.1215C1.74036 19.2922 1.48887 16.907 2.16267 13.4066C2.43772 11.9806 2.86275 10.8573 3.23839 9.86589C3.29276 9.7227 3.34623 9.58133 3.39834 9.44177H5.85654C5.90729 9.58722 5.95986 9.73448 6.01333 9.88492C6.20318 10.4173 6.40618 10.9901 6.59378 11.6258C6.25529 12.5597 5.91726 13.6114 5.67348 14.8761C5.33318 16.6392 5.09847 18.6978 5.34089 20.4196ZM10.4503 19.2583C10.4503 20.5329 10.7249 21.7414 11.2098 22.8361C10.6343 23.0514 9.93104 23.1832 9.22054 23.1832C8.43483 23.1832 7.76239 23.0215 7.37587 22.7369C6.07133 21.7799 5.77679 19.0476 6.54756 15.0456C6.86112 13.4197 7.34551 12.141 7.77281 11.0123C7.83217 10.8564 7.88972 10.7028 7.94636 10.5515H10.869C10.9247 10.7105 10.9823 10.8718 11.0403 11.0354C11.3706 11.9625 11.7358 12.9956 12.0199 14.234C10.9965 15.7095 10.4488 17.4626 10.4503 19.2583ZM12.7205 13.3568C12.5674 12.7827 12.3943 12.2141 12.2017 11.6521C12.4047 10.9987 12.6258 10.4115 12.832 9.86589C12.8863 9.7227 12.9398 9.58133 12.9919 9.44177H15.4501C15.5009 9.58722 15.5534 9.73448 15.6069 9.88492C15.7383 10.2533 15.8765 10.6403 16.0111 11.0549C14.752 11.5592 13.626 12.3469 12.7205 13.3568ZM14.8601 17.3193C14.6844 17.3192 14.5104 17.2845 14.348 17.2171C14.1857 17.1497 14.0383 17.051 13.9141 16.9266C13.7899 16.8022 13.6915 16.6546 13.6243 16.4922C13.5572 16.3297 13.5228 16.1557 13.523 15.9799C13.5227 15.625 13.6635 15.2845 13.9142 15.0333C14.165 14.7821 14.5052 14.6408 14.8601 14.6405C15.2151 14.6408 15.5553 14.7821 15.8061 15.0333C16.0568 15.2845 16.1975 15.625 16.1973 15.9799C16.1975 16.3348 16.0568 16.6753 15.8061 16.9265C15.5553 17.1777 15.2151 17.319 14.8601 17.3193ZM19.0484 21.8384C18.8433 21.8382 18.6403 21.7976 18.451 21.719C18.2616 21.6403 18.0896 21.5252 17.9448 21.38C17.8 21.2349 17.6851 21.0627 17.6069 20.8731C17.5286 20.6836 17.4885 20.4806 17.4887 20.2755C17.4887 19.4137 18.1865 18.7132 19.0484 18.7132C19.9102 18.7132 20.6085 19.4137 20.6085 20.2755C20.6087 20.4806 20.5684 20.6837 20.4901 20.8732C20.4118 21.0627 20.297 21.2349 20.1521 21.3801C20.0072 21.5252 19.8352 21.6403 19.6458 21.719C19.4564 21.7976 19.2534 21.8382 19.0484 21.8384ZM20.3855 15.2735C20.0308 15.2731 19.6907 15.1319 19.4399 14.881C19.1892 14.63 19.0484 14.2897 19.0484 13.935C19.048 13.5799 19.1887 13.2393 19.4394 12.9879C19.6902 12.7366 20.0305 12.5951 20.3855 12.5946C20.5614 12.5948 20.7355 12.6296 20.8978 12.6971C21.0602 12.7645 21.2077 12.8633 21.332 12.9878C21.4562 13.1122 21.5546 13.2599 21.6218 13.4224C21.6889 13.585 21.7233 13.7591 21.7232 13.935C21.7232 14.2898 21.5823 14.6301 21.3314 14.8811C21.0806 15.1321 20.7404 15.2732 20.3855 15.2735Z"
          fill="currentColor"/>
</svg>',
				'setting_icon_color' => 'warning',
			],
			[
				'name' => 'numbered_tables',
				'icon' => '<svg width="25" height="21" viewBox="0 0 25 21" fill="white" xmlns="http://www.w3.org/2000/svg">
    <path d="M24.5807 5.0651C24.5807 2.73302 19.1674 0.835938 12.4974 0.835938C5.8274 0.835938 0.414062 2.73302 0.414062 5.0651C0.414062 7.25219 5.18698 9.06469 11.2891 9.2701V14.1276H7.66406L5.2474 20.1693H7.66406L9.11406 16.5443H15.8807L17.3307 20.1693H19.7474L17.3307 14.1276H13.7057V9.2701C19.8078 9.06469 24.5807 7.25219 24.5807 5.0651Z"
          fill="currentColor"/>
</svg>',
				'setting_icon_color' => 'info',
			],
			[
				'name' => 'unnumbered_tables',
				'icon' => '<svg width="25" height="21" viewBox="0 0 25 21" fill="white" xmlns="http://www.w3.org/2000/svg">
    <path d="M24.5807 5.0651C24.5807 2.73302 19.1674 0.835938 12.4974 0.835938C5.8274 0.835938 0.414062 2.73302 0.414062 5.0651C0.414062 7.25219 5.18698 9.06469 11.2891 9.2701V14.1276H7.66406L5.2474 20.1693H7.66406L9.11406 16.5443H15.8807L17.3307 20.1693H19.7474L17.3307 14.1276H13.7057V9.2701C19.8078 9.06469 24.5807 7.25219 24.5807 5.0651Z"
          fill="currentColor"/>
</svg>',
				'setting_icon_color' => 'info',
			],
		]);
		$this->setGameTranslations('billiard', [
			'game-name' => 'Bilard',
			'slot-singular-short' => 'Stół',
			'slot-number-value' => 'Stół nr: :value',
			'slot-singular' => 'Stół bilardowy',
			'slots-quantity' => 'Ilość stołów',
			'slot-plural' => 'Stoły bilardowe',
			'slot-plural-short' => 'Stoły',
			'slot-add' => 'Dodaj stół',
			'slot-help-content' =>
				'W tej sekcji możesz utworzyć stół bilardowy. Określić jego numer, wielkość i przypisać do niego odpowiedni cennik ( który wcześniej zdefiniowałeś )',
			'slot-mascot-type' => '11',
			'choose-slot' => 'Wybierz stół',
			'not-enough-vacant-slots' => 'Nie ma wystarczającej ilości wolnych stołów dla tej rezerwacji',
			'slots-no-pricelist-error' => 'Aby zarządzać stołami, musisz najpierw utworzyć cennik.',
			'pricelist-destroy-has-slots-error' =>
				'Nie można usunąć cennika, poniewać jest przypisany do istniejących stołów',
			'slot-destroy-has-reservations-error' =>
				'Nie można usunąć stołu, ponieważ istnieją przypisane do niego rezerwacje',
			'slot-unactive-has-reservations-error' =>
				'Nie można dezaktywować stołu, ponieważ istnieją przypisane do niego rezerwacje',
			'reservation-confirmed-customer-notification-greeting' => 'Potwierdzono Twoją grę w bilard!',
		]);
		$this->setGameTranslations('bowling', [
			'game-name' => 'Kręgle',
			'slot-singular' => 'tor bowlingowy',
			'slot-number-value' => 'Tor nr: :value',
			'slot-singular-short' => 'tor',
			'slots-quantity' => 'Ilość torów',
			'slot-plural' => 'Tory bowlingowe',
			'slot-plural-short' => 'Tory',
			'choose-slot' => 'Wybierz tor',
			'slot-add' => 'Dodaj tor',
			'slot-edit' => 'Edytuj tor',
			'slot-name' => 'Numer toru',
			'slot-help-content' =>
				'W tej sekcji możesz utworzyć tor bowlingowy Określić jego numer i przypisać do niego odpowiedni cennik (który wcześniej zdefi-niowałeś)',
			'slot-mascot-type' => '12',
			'successfully-stored' => 'Dodano nowy tor',
			'successfully-updated' => 'Zaktualizowano tor',
			'successfully-destroyed' => 'Usunięto tor',
			'not-enough-vacant-slots' => 'Nie ma wystarczającej ilości wolnych torów dla tej rezerwacji',
			'slots-no-pricelist-error' => 'Aby zarządzać torami, musisz najpierw utworzyć cennik.',
			'pricelist-destroy-has-slots-error' =>
				'Nie można usunąć cennika, poniewać jest przypisany do istniejących torów',
			'reservation-for-slot-header' => 'Rezerwacja na tor nr :slot_name',
			'slot-destroy-has-reservations-error' =>
				'Nie można usunąć toru, ponieważ istnieją przypisane do niego rezerwacje',
			'slot-unactive-has-reservations-error' =>
				'Nie można dezaktywować toru, ponieważ istnieją przypisane do niego rezerwacje',
			'reservation-confirmed-customer-notification-greeting' => 'Potwierdzono Twoją grę w kręgle!',
		]);
		$this->setGameTranslations('numbered_tables', [
			'game-name' => 'Stoliki numerowane',
			'slot-singular' => 'Stolik numerowany',
			'slot-number-value' => 'Stolik nr: :value',
			'slot-singular-short' => 'Stolik',
			'slots-quantity' => 'Ilość stolików',
			'slot-plural' => 'Stoliki numerowane',
			'slot-plural-short' => 'Stoliki',
			'slot-add' => 'Dodaj salę',
			'slot-edit' => 'Edytuj salę',
			'game-time' => 'Czas rezerwacji',
			'slot-help-content' =>
				'W tej sekcji możesz utworzyć salę i przypisać do niej odpowiednią liczbę stolików',
			'slot-mascot-type' => '3',
			'successfully-stored' => 'Dodano nową salę',
			'successfully-updated' => 'Zaktualizowano salę',
			'successfully-destroyed' => 'Usunięto salę',
			'not-enough-vacant-slots' => 'Nie ma wystarczającej ilości wolnych stolików dla tej rezerwacji',
			'pricelist-destroy-has-slots-error' =>
				'Nie można usunąć cennika, poniewać jest przypisany do istniejących sal',
			'slot-destroy-has-reservations-error' =>
				'Nie można usunąć sali, ponieważ istnieją przypisane do niego rezerwacje',
			'slot-unactive-has-reservations-error' =>
				'Nie można dezaktywować sali, ponieważ istnieją przypisane do niego rezerwacje',
			'reservation-confirmed-customer-notification-greeting' => 'Potwierdzono Twoją rezerwację!',
		]);
		$this->setGameTranslations('unnumbered_tables', [
			'game-name' => 'Stoliki zbiorcze',
			'slot_singular' => 'Stolik zbiorczy',
			'slot-singular-short' => 'Stolik',
			'slot-number-value' => 'Stolik nr: :value',
			'slot-plural' => 'Stoliki zbiorcze',
			'slot-plural-short' => 'Stoliki',
			'slot-add' => 'Dodaj salę',
			'slot-edit' => 'Edytuj salę',
			'game-time' => 'Czas rezerwacji',
			'slots-quantity' => 'Ilość osób',
			'slot-help-content' =>
				'W tej sekcji możesz zdefiniować szczegółowe informacje na temat Sali. Nazwę jak i pojemność tj ile osób może pomieścić sala w danym dniu.',
			'slot-mascot-type' => '19',
			'successfully-stored' => 'Dodano nową salę',
			'successfully-updated' => 'Zaktualizowano salę',
			'successfully-destroyed' => 'Usunięto salę',
			'not-enough-vacant-slots' => 'Nie ma wystarczającej ilości wolnych miejsc dla tej rezerwacji',
			'pricelist-destroy-has-slots-error' =>
				'Nie można usunąć cennika, poniewać jest przypisany do istniejących sal',
			'slot-destroy-has-reservations-error' =>
				'Nie można usunąć sali, ponieważ istnieją przypisane do niego rezerwacje',
			'slot-unactive-has-reservations-error' =>
				'Nie można dezaktywować sali, ponieważ istnieją przypisane do niego rezerwacje',
			'reservation-confirmed-customer-notification-greeting' => 'Potwierdzono Twoją rezerwację!',
		]);
	}
}
