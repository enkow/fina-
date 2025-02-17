import type { ErrorBag, Errors, Page, PageProps } from '@inertiajs/inertia';

export interface DashboardPage extends Page<PageProps> {
	props: {
		errors: Errors & ErrorBag;
		auth: {
			user: {
				name: string;
			};
		};
		activeCountries: any;
		laravelVersion: string;
		phpVersion: string;
	};
}
