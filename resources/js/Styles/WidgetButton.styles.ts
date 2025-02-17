import { twJoin } from 'tailwind-merge';

export const BASE_STYLES =
    'flex items-center justify-center font-bold transition-colors disabled:cursor-not-allowed';

export const radiuses = {
    xs: 'rounded-xl',
    sm: 'rounded-2xl',
    md: 'rounded-3xl',
} as const;

export const variants = {
    filled: 'text-white bg-opacity-90 hover:bg-opacity-100 active:bg-opacity-80 disabled:bg-gray-2',
    outline: 'border-2',
} as const;

export const createColors = (variant: Variant) => ({
    green: twJoin(variant === 'filled' && 'bg-ui-green'),
    'dark-green': twJoin(
        variant === 'filled' && 'bg-ui-green',
        variant === 'outline' &&
            'border-ui-green text-ui-green hover:border-ui-green hover:text-ui-green',
    ),
});

type Color = keyof ReturnType<typeof createColors>;
type Radius = keyof typeof radiuses;
type Variant = keyof typeof variants;

export interface ButtonBaseProps {
    readonly color?: Color;
    readonly radius?: Radius;
    readonly variant?: Variant;
}
