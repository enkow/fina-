import { useWidgetStore } from '@/Stores/widget';
import { Game } from '@/Types/models';

export const useWidgetGamePhoto = (game: Game) => {
	const widgetStore = useWidgetStore();

	const feature = game.features?.find(({ type }) => type === 'has_game_photo_setting');

	const gamePhoto = widgetStore.settings[`game_photo_${feature?.id}`]?.value;
	const globalPhoto = game.photo;

	return gamePhoto
		? `/club-assets/game-photos/${gamePhoto}`
		: globalPhoto
		? `/images/game-images/${globalPhoto}`
		: null;
};
