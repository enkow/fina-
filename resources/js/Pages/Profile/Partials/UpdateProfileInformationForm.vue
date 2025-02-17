<script setup>
import { capitalize, ref } from 'vue';
import ActionMessage from '@/Components/Auth/ActionMessage.vue';
import FormSection from '@/Components/Auth/FormSection.vue';
import InputError from '@/Components/Auth/InputError.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';

const props = defineProps({
	user: Object,
});

const form = useForm({
	_method: 'PUT',
	name: props.user.name,
	email: props.user.email,
	photo: null,
});

const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfileInformation = () => {
	if (photoInput.value) {
		form.photo = photoInput.value.files[0];
	}

	form.post(route('user-profile-information.update'), {
		errorBag: 'updateProfileInformation',
		preserveScroll: true,
		onSuccess: () => clearPhotoFileInput(),
	});
};

const sendEmailVerification = () => {
	verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
	photoInput.value.click();
};

const updatePhotoPreview = () => {
	const photo = photoInput.value.files[0];

	if (!photo) return;

	const reader = new FileReader();

	reader.onload = (e) => {
		photoPreview.value = e.target.result;
	};

	reader.readAsDataURL(photo);
};

const deletePhoto = () => {
	router.delete(route('current-user-photo.destroy'), {
		preserveScroll: true,
		onSuccess: () => {
			photoPreview.value = null;
			clearPhotoFileInput();
		},
	});
};

const clearPhotoFileInput = () => {
	if (photoInput.value?.value) {
		photoInput.value.value = null;
	}
};
</script>

<template>
	<FormSection @submitted="updateProfileInformation">
		<template #title>{{ $t('settings.profile-information') }}</template>

		<template #description>
			{{ $t('settings.profile-information-description') }}
		</template>

		<template #form>
			<!-- Profile Photo -->
			<div v-if="$page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-4">
				<!-- Profile Photo File Input -->
				<input ref="photoInput" class="hidden" type="file" @change="updatePhotoPreview" />

				<InputLabel :value="$t('set.photo')" for="photo" />

				<!-- Current Profile Photo -->
				<div v-show="!photoPreview" class="mt-2">
					<img :alt="user.name" :src="user.profile_photo_url" class="h-20 w-20 rounded-full object-cover" />
				</div>

				<!-- New Profile Photo Preview -->
				<div v-show="photoPreview" class="mt-2">
					<span
						:style="'background-image: url(\'' + photoPreview + '\');'"
						class="block h-20 w-20 rounded-full bg-cover bg-center bg-no-repeat" />
				</div>

				<Button class="grey mr-2 mt-2" type="button" @click.prevent="selectNewPhoto">
					{{ $t('settings.select-a-new-photo') }}
				</Button>

				<Button v-if="user.profile_photo_path" class="grey mt-2" type="button" @click.prevent="deletePhoto">
					{{ $t('settings.remove-photo') }}
				</Button>

				<InputError :message="form.errors.photo" class="mt-2" />
			</div>

			<!-- Email -->
			<div class="col-span-6 sm:col-span-4">
				<InputLabel :value="capitalize($t('main.email'))" for="email" />
				<TextInput id="email" v-model="form.email" class="mt-1 block w-full" type="email" />
				<InputError :message="form.errors.email" class="mt-2" />

				<div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
					<p class="mt-2 text-sm">
						{{ $t('settings.your-email-is-unverified') }}

						<Link
							:href="route('verification.send')"
							as="button"
							class="text-gray-600 underline hover:text-gray-900"
							method="post"
							@click.prevent="sendEmailVerification">
							{{ $t('settings.click-to-resend-the-verification-mail') }}
						</Link>
					</p>

					<div v-show="verificationLinkSent" class="mt-2 text-sm font-medium text-green-600">
						{{ $t('settings.new-verification-link-sent') }}
					</div>
				</div>
			</div>
		</template>

		<template #actions>
			<ActionMessage :on="form.recentlySuccessful" class="mr-3">{{ $t('settings.saved') }}.</ActionMessage>

			<Button
				:class="{ 'opacity-25': form.processing }"
				:disabled="form.processing"
				class="brand"
				type="submit">
				{{ capitalize($t('main.action.save')) }}
			</Button>
		</template>
	</FormSection>
</template>
