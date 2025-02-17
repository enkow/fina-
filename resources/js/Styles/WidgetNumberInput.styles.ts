export const WRAPPER_STYLES = 'relative mt-auto h-fit';

export const INPUT_STYLES =
	'moz-appearance-textfield h-16 w-full rounded-3xl border-0 border-none bg-ui-green/10 text-center text-ui-black focus:ring-2 focus:ring-ui-green [&::-webkit-inner-spin-button]:appearance-none [&::webkit-outer-spin-button]:appearance-none';

export const INPUT_COMPACT_STYLES = 'h-10 text-xs md:text-base';

export const BUTTON_STYLES =
	'absolute top-1/2 h-8 w-8 -translate-y-1/2 rounded-full text-2xl font-semibold text-ui-green transition-all hover:bg-ui-green/20 disabled:cursor-not-allowed disabled:opacity-50';

export const BUTTON_LEFT_STYLES = 'left-4.5';

export const BUTTON_RIGHT_STYLES = 'right-4.5';

export interface WidgetNumberInputBaseProps {
	readonly compact?: boolean;
	readonly step?: number;
	readonly modelValue: number;
	readonly min: number;
	readonly max: number;
}
