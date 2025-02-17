import { useWidgetStore } from '@/Stores/widget';

export const useWidgetAgreements = () => {
	const widgetStore = useWidgetStore();

	const filteredAgreements = widgetStore.agreements.filter(
		({ content_type, file, text }) =>
			(content_type === 0 && Boolean(file)) || (content_type === 1 && Boolean(text)),
	);

	return {
		generalTerms: filteredAgreements.find(({ type }) => type === 0),
		privacyPolicy: filteredAgreements.find(({ type }) => type === 1),
		marketingAgreement: filteredAgreements.find(({ type }) => type === 2),
	};
};
