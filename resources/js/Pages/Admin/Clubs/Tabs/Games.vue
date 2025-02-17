<template>
	<div class="flex flex-wrap space-y-3 md:space-y-0">
		<div class="w-full space-y-3 pr-5 sm:w-2/3">
			<p class="font-bold">Włączone gry</p>
			<div v-for="game in enabledGames" class="mb-3 flex w-full items-center justify-between">
				<div class="w-full border-t border-t-gray-2">
					<p class="mt-6 font-bold">{{ usePage().props.gameNames[game.id] }}</p>
					<div
						v-for="(country, country_id) in usePage().props.activeCountries"
						class="mt-3 flex w-full items-center">
						<div class="w-1/3">{{ $t(`country.${country}`) }}</div>
						<div
							v-if="
								form[game.id.toString()] &&
								typeof form[game.id.toString()]['names'][country_id.toString()] !== 'undefined'
							"
							class="w-2/3">
							<TextInput v-model="form[game.id.toString()]['names'][country_id.toString()]" />
						</div>
					</div>
					<div class="mt-2 space-y-3">
						<div class="space-y-1">
							<InputLabel value="Prowizja Bookgame (w groszach)" />
							<TextInput v-model="form[game.id.toString()]['fee_fixed']" />
							<div v-if="form.errors[`${game.id.toString()}.fee_fixed`]" class="error">
								{{ form.errors[`${game.id.toString()}.fee_fixed`] }}
							</div>
						</div>
						<div class="space-y-1">
							<InputLabel value="Prowizja Bookgame (w procentach)" />
							<TextInput v-model="form[game.id.toString()]['fee_percent']" />
							<div v-if="form.errors[`${game.id.toString()}.fee_percent`]" class="error">
								{{ form.errors[`${game.id.toString()}.fee_percent`] }}
							</div>
						</div>
						<div class="space-y-1">
							<InputLabel value="Czy widoczna na wtyczce" />
							<Checkbox
								:checked="form[game.id.toString()]['enabled_on_widget']"
								v-model="form[game.id.toString()]['enabled_on_widget']" />
							<div v-if="form.errors[`${game.id.toString()}.enabled_on_widget`]" class="error">
								{{ form.errors[`${game.id.toString()}.enabled_on_widget`] }}
							</div>
						</div>
					</div>
					<div class="mt-3 flex w-full justify-end">
						<Button type="button" @click="updateClubGamePivot(game.id)">Zapisz</Button>
					</div>
				</div>
				<div class="flex min-w-[150px] items-center justify-end justify-center space-x-2">
					<div>
						<Link
							:href="
								route('admin.clubs.move-game', {
									club: club,
									game: game,
									moveType: 'up',
								})
							"
							as="button"
							method="post"
							preserve-scroll
							preserve-state>
							<svg
								class="h-4 w-4"
								fill="none"
								stroke="currentColor"
								stroke-width="1.5"
								viewBox="0 0 24 24"
								xmlns="http://www.w3.org/2000/svg">
								<path
									d="M8.25 6.75L12 3m0 0l3.75 3.75M12 3v18"
									stroke-linecap="round"
									stroke-linejoin="round" />
							</svg>
						</Link>
					</div>
					<div>
						<Link
							:href="
								route('admin.clubs.move-game', {
									club: club,
									game: game,
									moveType: 'down',
								})
							"
							as="button"
							method="post"
							preserve-scroll
							preserve-state>
							<svg
								class="h-4 w-4"
								fill="none"
								stroke="currentColor"
								stroke-width="1.5"
								viewBox="0 0 24 24"
								xmlns="http://www.w3.org/2000/svg">
								<path
									d="M15.75 17.25L12 21m0 0l-3.75-3.75M12 21V3"
									stroke-linecap="round"
									stroke-linejoin="round" />
							</svg>
						</Link>
					</div>
					<div>
						<MinusButton
							preserve-scroll
							@click="
								showDialog(capitalize($t('main.are-you-sure')), () => {
									router.visit(
										route('admin.clubs.toggle-game', {
											club: club,
											game: game,
										}),
										{
											method: 'post',
											preserveScroll: true,
											preserveState: true,
										},
									);
								})
							" />
					</div>
				</div>
			</div>
		</div>
		<div class="w-full space-y-3 pr-5 md:w-1/3">
			<p class="font-bold">Wyłączone gry</p>
			<div v-for="game in disabledGames" class="mb-3 flex items-center justify-between">
				<div>
					{{ game.name }}
				</div>
				<div class="flex items-center justify-end space-x-2">
					<div>
						<p
							as="button"
							@click="
								router.post(
									route('admin.clubs.toggle-game', {
										club: club,
										game: game,
									}),
									{},
									{
										onSuccess: () => {
											setGameNamesTranslationsForm();
											router.reload();
										},
									},
								)
							">
							<PlusButton />
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<DecisionModal
		:showing="confirmationDialogShowing"
		@confirm="confirmDialog()"
		@decline="cancelDialog()"
		@close="confirmationDialogShowing = false">
		{{ dialogContent }}
	</DecisionModal>
</template>
<script lang="ts" setup>
import PlusButton from '@/Components/Dashboard/Buttons/PlusButton.vue';
import { Club, Game } from '@/Types/models';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import MinusButton from '@/Components/Dashboard/Buttons/MinusButton.vue';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useString } from '@/Composables/useString';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';

const props = defineProps<{
	club: Club;
	disabledGames: Game[];
	enabledGames: Game[];
	data: any;
}>();

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const { capitalize } = useString();

let clubGameCustomNamesFormObject: Record<number, Record<any, any>> = {};
let form: any = null;

function setGameNamesTranslationsForm() {
	clubGameCustomNamesFormObject = {};
	props.enabledGames.forEach((game) => {
		let customClubSingleGameNames: Record<string, string> = Object.values(props.club.games).find(
			(gameModel) => gameModel.id === game.id,
		).pivot['custom_names'];
		customClubSingleGameNames = JSON.parse(customClubSingleGameNames);
		clubGameCustomNamesFormObject[game.id] = {
			names: {},
			fee_percent: props.club.games.find((gameModel) => gameModel.id === game.id).pivot.fee_percent,
			fee_fixed: props.club.games.find((gameModel) => gameModel.id === game.id).pivot.fee_fixed,
			enabled_on_widget: !!props.club.games.find((gameModel) => gameModel.id === game.id).pivot
				.enabled_on_widget,
		};

		for (const [key, value] of Object.entries(usePage().props?.activeCountries ?? [])) {
			clubGameCustomNamesFormObject[game.id]['names'][key] = customClubSingleGameNames[key] ?? null;
		}
	});
	form = useForm(clubGameCustomNamesFormObject);
}

setGameNamesTranslationsForm();

function updateClubGamePivot(game_id: number) {
	form.post(
		route('admin.clubs.games.update', {
			club: props.club,
			game: game_id,
		}),
		{
			preserveState: true,
			preserveScroll: true,
		},
	);
}
</script>
