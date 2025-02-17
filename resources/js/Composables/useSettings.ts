import { SettingEntity } from '@/Types/responses';

export function useSettings(settings: Record<string, SettingEntity> | undefined) {
	function getSettingsByFeatureType(featureSettingName: string): Array<any> {
		let settingsResult: Array<any> = [];
		for (const [name, setting] of Object.entries(settings ?? {})) {
			if (name.includes(`${featureSettingName}_`)) {
				settingsResult.push(setting);
			}
		}
		return settingsResult;
	}

	return { getSettingsByFeatureType };
}
